<?php

namespace App\Services\Web;

use App\Events\EmailVerificationEvent;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use App\Services\MailService;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmailSender;
use Illuminate\Support\Facades\Http;
use App\Traits\EmailNewTemp;

class CustomerAuthService
{
    public function getCustomerVerificationToken(): string
    {
        return (env('APP_MODE') == 'live') ? rand(100000, 999999) : 123456;
    }

    public function getCustomerLoginDataReset(): array
    {
        return [
            'login_hit_count' => 0,
            'is_temp_blocked' => 0,
            'temp_block_time' => null,
            'updated_at' => now()
        ];
    }

    public function checkRecaptchaValidation(array|object $request): bool|JsonResponse|RedirectResponse
    {
        $request->validate([
            'user_identity' => 'required',
            'password' => 'required'
        ]);

        // Recaptcha validation start
        $recaptcha = getWebConfig(name: 'recaptcha');
        if (isset($recaptcha) && $recaptcha['status'] == 1) {
            try {
                $request->validate([
                    'g-recaptcha-response' => [
                        function ($attribute, $value, $fail) {
                            $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
                            $response = $value;
                            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response;
                            $response = \file_get_contents($url);
                            $response = json_decode($response);
                            if (!$response->success) {
                                $fail(translate('ReCAPTCHA_Failed'));
                            }
                        },
                    ],
                ]);
            } catch (\Exception $exception) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => translate($exception->getMessage()),
                        'redirect_url' => ''
                    ]);
                } else {
                    Toastr::error(translate($exception->getMessage()));
                    return back();
                }
            }
        } else {
            if (strtolower($request['default_recaptcha_id_customer_login']) != strtolower(Session('default_recaptcha_id_customer_login'))) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => translate('Captcha_Failed.'),
                        'redirect_url' => ''
                    ]);
                } else {
                    Session::forget('default_recaptcha_id_customer_login');
                    Toastr::error(translate('captcha_failed'));
                    return back();
                }
            }
        }
        return true;
    }


    public function sendCustomerPhoneVerificationToken($phone, $token): array
    {
        $response = SMSModule::sendCentralizedSMS($phone, $token);
        return [
            'response' => $response,
            'status' => 'success',
            'message' => translate('please_check_your_SMS_for_OTP'),
        ];
    }

    public function sendCustomerEmailVerificationToken($user, $token): array
    {
        $emailServicesSmtp = getWebConfig(name: 'mail_config');
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
        }
        if ($emailServicesSmtp['status'] == 1 && $user['email']) {
            try {
                //$token = $this->getCustomerVerificationToken();
                $data = [
                    'userName' => $user['f_name'],
                    'subject' => translate('registration_Verification_Code'),
                    'title' => translate('registration_Verification_Code'),
                    'verificationCode' => $token,
                    'userType' => 'customer',
                    'templateName' => 'registration-verification',
                ];
                // $to = "cainalkhater@gmail.com";
                // $subject = "My subject";
                // $txt = "Hello world!";
                // $headers = "From: info@rsbaba.com";
                
                // mail($to,$subject,$txt,$headers);

                // $mailable = new Mailable();

                //     $mailable
                //         ->from('working123www@gmail.com')
                //         ->to('cainalkhater@gmail.com')
                //         ->subject('test subject')
                //         ->html('my first message');
        
                //     $result = Mail::send($mailable);
                // return $user['email'];
                
                // return [
                //     'status' => "error",
                //     'message' =>$user['email'],
                // ];
                // Mail::to("cainalkhater@gmail.com")->send(new TestEmailSender());

                $send=new EmailNewTemp();
                $send->sendingMail($user['email'],$data["userType"],$data["templateName"],$data);

                // Mail::raw("Your verification code is: $token", function ($message)  use ($user){
                //     $message->to($user['email'])
                //             ->subject("Verification Code: ");
                // });
                
                // event(new EmailVerificationEvent(email: $user['email'], data: $data));
                return [
                    'status' => 'success',
                    'message' => translate('check_your_email'),
                ];
            } catch (\Exception $exception) {
                return [
                    'status' => 'error',
                    'message' => translate('email_is_not_configured') . '. ' . translate('contact_with_the_administrator'),
                ];
            }
        } else {
            return [
                'status' => 'error',
                'message' => translate('email_failed'),
            ];
        }
    }

    public function getCustomerLoginPreviousRoute($previousUrl): string
    {
        $redirectUrl = "";
        $previousUrl = url()->previous();
        if (
            strpos($previousUrl, 'checkout-complete') !== false ||
            strpos($previousUrl, 'offline-payment-checkout-complete') !== false ||
            strpos($previousUrl, 'track-order') !== false
        ) {
            $redirectUrl = route('home');
        }
        return $redirectUrl;
    }

    public function getCustomerRegisterData(object|array $request, object|array|null $referUser)
    {
        return [
            'name' => $request['f_name'] . ' ' . $request['l_name'],
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'is_active' => 1,
            'password' => bcrypt($request['password']),
            'referral_code' => Helpers::generate_referer_code(),
            'referred_by' => $referUser ? $referUser['id'] : null,
        ];
    }

    // My Code 
    // public function sendOtp($number, $message)
    // {
    //     $response = Http::post($this->baseUrl, [
    //         'userName' => $this->username,
    //         'apiKey' => $this->apiKey,
    //         'numbers' => $number,
    //         'userSender' => $this->userSender,
    //         'msg' => $message,
    //         'msgEncoding' => 'UTF8'
    //     ]);

    //     return $response->json();
    // }


}
