<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TapPaymentSetting;
use GuzzleHttp\Client;
use DateTime;
class TapPaymentSettingsController extends Controller
{


    public function update(Request $request)
    {
        $status=1;
        if(!isset($request->status))
            $status=0;

        // return $status;
        // print_r($request);
        // return null;
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
            'url_back' => 'required|string',
        ], [
            'key.required' => 'حقل المفتاح (Key) مطلوب.',
            'value.required' => 'حقل القيمة (Value) مطلوب.',
            'url_back.required' => 'حقل القيمة (url_back) مطلوب.',
        ]);
        // print("fdf");
        // return null;
        // تحديث الـ test socket
         // تحديث الـ test socket

            TapPaymentSetting::where('key', '!=', $request->key . '_socket')
                ->where('Type', 1) // إضافة شرط آخر على Type
                ->update([
                    'Type' => null // جعل Type فارغًا
                ]);

         TapPaymentSetting::updateOrCreate(
             //['key' => $request->key . '_socket'],  // الشروط لتحديد السجل
            ['method' => 'TAP'],  // الشروط لتحديد السجل
            [
                'key' => $request->key . '_socket',         // القيم التي سيتم تحديثها أو إضافتها
                'method' => "TAP",         // القيم التي سيتم تحديثها أو إضافتها
                'value' => $request->value,         // القيم التي سيتم تحديثها أو إضافتها
                'url_back' => $request->url_back,
                'Type' => $status
            ]
        );
        // return $request->key;


        // إعادة توجيه مع رسالة نجاح
        return redirect()->back()->with('success', 'تم تحديث إعدادات الـ socket بنجاح');
    }


    public function createPayment1(Request $request)
    {
        // جلب إعدادات Tap Payment من قاعدة البيانات
        $tapSettings = TapPaymentSetting::whereIn('key', ['test_socket', 'live_socket'])->get()->pluck('value', 'key');

        // بيانات العميل
        $customer = auth('customer')->user();
        // return $customer;
        // إعداد بيانات الدفع
        $data = [
            'amount' => $request->amount,
            'currency' => session('currency_code'),
            'customer_initiated' => 'true',
            'threeDSecure' => true,
            'save_card' => false,
            'statement_descriptor' => 'Order Payment',
            'metadata' => [
                'udf1' => 'order_id_' . 123,
            ],
            'reference' => [
                'transaction' => 'txn_' . 321,
                'order' => 'ord_' . 321
            ],
            'receipt' => [
                'email' => true,
                'sms' => true
            ],
            'customer' => [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => [
                    'country_code' => $request->country_code,
                    'number' => $request->phone
                ]
            ],
            'merchant' => [
                'id' => '1234',
            ],
            'source' => [
                'id' => 'src_card',
            ],
            'authorize_debit' => false,
            'auto' => [
                'type' => 'VOID',
                'time' => 100,
            ],
            'post' => [
                'url' => "http://localhost:8080/"//route('checkout-complete')
            ],
            'redirect' => [
                'url' => 'http://localhost:8080/'//route('checkout-complete')
            ]
        ];

        // إرسال طلب الدفع
        $client = new Client();
        $response = $client->post("https://api.tap.company/v2/authorize/", [
            'headers' => [
                'Authorization' => 'Bearer ' . $tapSettings['test_socket'], // التحقق من استخدام المفتاح الصحيح
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            'json' => $data
        ]);

        $responseBody = json_decode($response->getBody(), true);

        // إعادة التوجيه إلى صفحة الدفع
        return redirect($responseBody['transaction']['url']);
    }

    public function GetSource($tapSettings){
        // إعداد بيانات بطاقة الائتمان
        $cardData = [
            'number' => '4890222080101632', // رقم البطاقة
            'expiry_month' => '10',         // شهر انتهاء الصلاحية
            'expiry_year' => '28',          // سنة انتهاء الصلاحية
            'cvc' => '624'                   // CVC
        ];

        // إرسال الطلب لإنشاء مصدر الدفع
        $client = new Client();
        $response = $client->post("https://api.tap.company/v2/sources", [
            'headers' => [
                'Authorization' => 'Bearer ' . $tapSettings['test_socket'],
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            'json' => [
                'type' => 'card',
                'card' => $cardData
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);
        $sourceId = $responseBody['id']; // هذا هو source id الذي يمكنك استخدامه
        return $sourceId;
    }

    public function createPayment(Request $request)
    {
        // جلب إعدادات Tap Payment من قاعدة البيانات
        $tapSettings = TapPaymentSetting::whereIn('key', ['test_socket', 'live_socket'])->Where('method',"TAP")->get()->pluck('value', 'key');
            // تحديد القيمة التي يجب استخدامها (live أو test)
        $activeSettingKey = null;

        if (isset($tapSettings['live_socket'])) {
            $activeSettingKey = 'live_socket'; // إذا كان live مفعل
        } elseif (isset($tapSettings['test_socket'])) {
            $activeSettingKey = 'test_socket'; // إذا كان test مفعل
        }

        // التأكد من أن هناك إعداد مفعل
        if ($activeSettingKey === null) {
            return response()->json(['error' => 'No active payment setting found'], 500);
        }
        $dateStr = $request->dte;

        // تحويل النص إلى كائن DateTime
        $dateObject = DateTime::createFromFormat('m/Y', $dateStr);

        // استخراج الشهر والسنة
        $month = $dateObject->format('m'); // الشهر
        $year = $dateObject->format('y'); // السنة
        $number_card = str_replace(' ', '', $request->number_card);

        // return $number_card;
        // بيانات البطاقة
        $card = [
            'card' => [
                'number' =>  $number_card, //'4890222080101632'تأكد من إضافة الحقول اللازمة
                'expiry_month' =>$month, //'10',
                'expiry_year' => $year,//'28',
                'cvc' => $request->ccv,//'624',
            ]
        ];
    
        // التحقق من البطاقة
        $verificationResponse = $this->verifyCard($card, $tapSettings[$activeSettingKey]);
    
        if (isset($verificationResponse['error']) && $verificationResponse['error']) {
            // إذا فشل التحقق من البطاقة، أعد رسالة خطأ
            // return response()->json(['error' => 'Card verification failed', 'message' => $verificationResponse['message']], 400);
            // إذا فشل التحقق من البطاقة، أعد توجيه المستخدم إلى صفحة معينة مع رسالة خطأ
            return redirect()->route('checkout-payment')->with('error', $verificationResponse['message']);
        }
    
        // بيانات العميل
        $customer = auth('customer')->user();
    
        if (!$customer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // تحقق من أن جميع الخصائص المستخدمة مهيأة
        $firstName = $request->first_name ?? 'Guest';
        $lastName = $request->last_name ?? 'User';
        $email = $request->email ?? 'no-reply@example.com';
        // return $request->currency_code;
        // إعداد بيانات الدفع
        $data = [
            'amount' =>1, //$request->amount,
            'currency' =>trim($request->currency_code),
            'customer_initiated' => 'true',
            'threeDSecure' => true,
            'save_card' => false,
            'statement_descriptor' => 'Order Payment',
            'metadata' => [
                'udf1' => 'order_id_' . 123,
            ],
            'reference' => [
                'transaction' => 'txn_' . 321,
                'order' => 'ord_' . 321,
            ],
            'receipt' => [
                'email' => true,
                'sms' => true
            ],
            'customer' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => [
                    'country_code' => $request->country_code,
                    'number' => $request->phone
                ]
            ],
            'post' => [
                'url' => route('checkout-complete')
            ],
            'redirect' => [
                'url' => '#' // استبدل بـ route('payment.redirect') إذا كنت بحاجة إلى ذلك
            ],
            'source' => [
                'id' => $verificationResponse['id'], // استخدم الـ Token ID الذي حصلت عليه من التحقق
            ],
        ];
    
        // إرسال طلب الدفع
        try {
            $client = new Client();
            $response = $client->post("https://api.tap.company/v2/card/verify/", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tapSettings[$activeSettingKey], 
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                'json' => $data
            ]);
    
            $responseBody = json_decode($response->getBody(), true);
    
            return redirect($responseBody['transaction']['url']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment request failed', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function createPaymentAPI(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Payment successful',
            'transaction_url' => 'dssd'
        ]); 
        // جلب إعدادات Tap Payment من قاعدة البيانات
        $tapSettings = TapPaymentSetting::whereIn('key', ['test_socket', 'live_socket'])
                        ->Where('method', "TAP")->get()->pluck('value', 'key');
    
        // تحديد القيمة التي يجب استخدامها (live أو test)
        $activeSettingKey = $tapSettings['live_socket'] ?? $tapSettings['test_socket'];
    
        // التأكد من أن هناك إعداد مفعل
        if ($activeSettingKey === null) {
            return response()->json(['error' => 'No active payment setting found'], 500);
        }
    
        // معالجة تاريخ انتهاء البطاقة
        $dateStr = $request->dte;
        $dateObject = DateTime::createFromFormat('m/Y', $dateStr);
        $month = $dateObject->format('m');
        $year = $dateObject->format('y');
        $number_card = str_replace(' ', '', $request->number_card);
    
        // بيانات البطاقة
        $card = [
            'card' => [
                'number' => $number_card,
                'expiry_month' => $month,
                'expiry_year' => $year,
                'cvc' => $request->ccv,
            ]
        ];
    
        // التحقق من البطاقة
        $verificationResponse = $this->verifyCard($card, $tapSettings[$activeSettingKey]);
    
        if (isset($verificationResponse['error']) && $verificationResponse['error']) {
            return redirect()->route('checkout-payment')->with('error', $verificationResponse['message']);
        }
    
        // بيانات العميل
        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // إعداد بيانات الدفع
        $data = [
            'amount' => 1, // أو حسب قيمة $request->amount
            'currency' => trim($request->currency_code),
            'customer_initiated' => 'true',
            'threeDSecure' => true,
            'save_card' => false,
            'statement_descriptor' => 'Order Payment',
            'metadata' => [
                'udf1' => 'order_id_' . 123,
            ],
            'reference' => [
                'transaction' => 'txn_' . 321,
                'order' => 'ord_' . 321,
            ],
            'receipt' => [
                'email' => true,
                'sms' => true
            ],
            'customer' => [
                'first_name' => $request->first_name ?? 'Guest',
                'last_name' => $request->last_name ?? 'User',
                'email' => $request->email ?? 'no-reply@example.com',
                'phone' => [
                    'country_code' => $request->country_code,
                    'number' => $request->phone
                ]
            ],
            'post' => [
                'url' => route('checkout-complete')
            ],
            'redirect' => [
                'url' => route('payment.redirect')
            ],
            'source' => [
                'id' => $verificationResponse['id'],
            ],
        ];
    
        // إرسال طلب الدفع
        try {
            $client = new Client();
            $response = $client->post("https://api.tap.company/v2/charges", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tapSettings[$activeSettingKey],
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                'json' => $data
            ]);
    
            $responseBody = json_decode($response->getBody(), true);
    
            return response()->json([
                'status' => 'success',
                'message' => 'Payment successful',
                'transaction_url' => $responseBody['transaction']['url']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() // أو الرسالة المناسبة للخطأ
            ], 400);
        }
    }
    


    private function verifyCard($cardData, $apiKey)
    {
        $client = new \GuzzleHttp\Client();
    
        if (empty($cardData['card']['number']) || empty($cardData['card']['expiry_month']) || empty($cardData['card']['expiry_year']) || empty($cardData['card']['cvc'])) {
            return ['error' => true, 'message' => 'Card details are missing'];
        }
    
        try {
            $response = $client->post("https://api.tap.company/v2/tokens", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'card' => [
                        'number' => $cardData['card']['number'],
                        'exp_month' => $cardData['card']['expiry_month'],
                        'exp_year' => $cardData['card']['expiry_year'],
                        'cvc' => $cardData['card']['cvc'],
                    ],
                    'client_ip' => request()->ip(),
                ],
            ]);
    
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
    
    





    public function callback(Request $request)
    {
        // معالجة الدفع بعد العودة من Tap Payment
        // هنا يمكنك معالجة الرد وتحديث الحالة في النظام الخاص بك
        return view('payment.success'); // عرض رسالة نجاح
    }

    public function redirect(Request $request)
    {
        // معالجة الإعادة إلى الموقع بعد الدفع
        return view('payment.redirect'); // عرض صفحة إعادة التوجيه
    }
}
