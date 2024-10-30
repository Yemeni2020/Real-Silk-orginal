<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\User;
use App\Rules\PhoneLength;
use App\Traits\ApiResponse;
use App\Traits\Notify;
use App\Traits\PasswordResetTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserAuthController extends Controller
{
    use ApiResponse,Notify,PasswordResetTrait;

    private function strongPassword()
    {
        return Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }

    public function register(Request $request)
    {
        $basic = basicControl();
        $phoneCode = $request->input('phone_code');
        $registerRules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'username' => 'required|string|alpha_dash|min:5|unique:users,username',
            'email' => 'required|string|email|unique:users,email',
            'password' => $basic->strong_password == 0 ?
                ['required', 'confirmed', 'min:6'] :
                ['required', 'confirmed', $this->strongPassword()],
            'password_confirmation' => 'required| min:6',
            'phone_code' => 'required|string',
            'phone' => ['required', 'numeric', 'unique:users,phone', new PhoneLength($phoneCode)],
            'country' => 'required|string',
            'country_code' => 'required|string',
        ];

        $message = [
            'password.letters' => 'password must be contain letters',
            'password.mixed' => 'password must be contain 1 uppercase and lowercase character',
            'password.symbols' => 'password must be contain symbols',
        ];

        $data = Validator::make($request->all(), $registerRules,$message);
        if ($data->fails()) {
            return response()->json($this->withError(collect($data->errors())->collapse()));
        }

        $sponsorId = null;
        if ($request->has('sponsor')) {
            $sponsor = User::where('username', $request->sponsor)
                ->where('email_verification', 1)
                ->where('sms_verification', 1)
                ->where('status', 1)
                ->first();
            $sponsorId = $sponsor ? $sponsor->id : null;
        }

        $user =  User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'phone_code' => $request->phone_code,
            'country' => $request->country,
            'country_code' => $request->country_code,
            'email_verification' => ($basic->email_verification) ? 0 : 1,
            'sms_verification' => ($basic->sms_verification) ? 0 : 1,
            'remember_token' => Str::random(10),
            'referral_id' => $sponsorId,
        ]);

        $this->sendWelcomeEmail($user);

        return response()->json($this->withSuccess($user->createToken('token')->plainTextToken));
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('username', 'password');

            $validator = Validator::make($credentials, [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $user = User::where('email', $credentials['username'])
                ->orWhere('username', $credentials['username'])
                ->first();


            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json($this->withError('credentials do not match'));
            }
            $data['message'] = 'User logged in successfully.';
            $data['token'] = $user->createToken($user->email)->plainTextToken;

            $this->loginNotify($user);

            return response()->json($this->withSuccess($data));
        }catch (\Exception $e){
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json($this->withSuccess('User is logged out successfully'));
    }

    public function getEmailForRecoverPass(Request $request)
    {
        return $this->resetLinkEmail($request, true);
    }

    public function getCodeForRecoverPass(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'code' => 'required',
            'email' => 'required|email',
        ]);
        if ($validateUser->fails()) {
            return response()->json(['status' => false, 'message' => $validateUser->errors()], 200);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Email does not exist on record'], 200);
        }
        if ($user->verify_code == $request->code && $user->updated_at > Carbon::now()->subMinutes(5)) {
            $user->verify_code = null;
            $resetToken = \Str::random(60);
            $user->reset_token = $resetToken;
            $user->token_expires_at = Carbon::now()->addMinutes(15);
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Code Matching',
                'reset_token' => $resetToken,
            ], 200);
        }
        return response()->json($this->withError('Invalid Code'));
    }

    public function updatePass(Request $request)
    {
        $basic = basicControl();
        $rules = [
            'email' => 'required|email|exists:users,email',
            'password' => $basic->strong_password == 0 ?
                ['required', 'confirmed', 'min:6'] :
                ['required', 'confirmed', $this->strongPassword()],
            'password_confirmation' => 'required| min:6',
            'reset_token' => 'required',
        ];
        $message = [
            'email.exists' => 'Email does not exist on record'
        ];
        $validateUser = Validator::make($request->all(), $rules, $message);
        if ($validateUser->fails()) {
            return response()->json($this->withError(collect($validateUser->errors())->collapse()));
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || $user->reset_token !== $request->reset_token || Carbon::now()->greaterThan($user->token_expires_at)) {
            return response()->json($this->withError('Invalid or expired token'));
        }

        $user->password = Hash::make($request->password);
        $user->reset_token = null;
        $user->token_expires_at = null;
        $user->save();

        return response()->json($this->withSuccess('Password Updated'));
    }

}
