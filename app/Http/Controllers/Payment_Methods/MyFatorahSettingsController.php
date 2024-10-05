<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TapPaymentSetting;
use GuzzleHttp\Client;
use App\Http\Controllers\Payment_Methods\MyFatoorah\API\Payment\MyFatoorahPayment;
use DateTime;
class MyFatorahSettingsController extends Controller
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
            ['method' => 'MYFATOORAH'],  // الشروط لتحديد السجل
            [
                'key' => $request->key . '_socket',         // القيم التي سيتم تحديثها أو إضافتها
                'method' => "MYFATOORAH",         // القيم التي سيتم تحديثها أو إضافتها
                'value' => $request->value,         // القيم التي سيتم تحديثها أو إضافتها
                'url_back' => $request->url_back,
                'Type' => $status
            ]
        );
        // return $request->key;


        // إعادة توجيه مع رسالة نجاح
        return redirect()->back()->with('success', 'تم تحديث إعدادات الـ socket بنجاح');
    }


    public function createPayment(Request $request)
    {
        $currency=session('currency_code');
        switch (session('currency_code')) {
            case 'KWD':
                $currency="KWT";
                break;
            case 'SAR':
                $currency="SAU";
                break;
            case 'BHD':
                $currency="BHR";
                break;
            case 'AED':
                $currency="ARE";
                break;
            case 'QAR':
                $currency="QAT";
                break;
            case 'OMN':
                $currency="OMR";
                break;
            case 'JOD':
                $currency="JOR";
                break;
            case 'EGP':
                $currency="EGY";
                break;
        }
        // return 0;

        if($currency != "KWT" && $currency != "SAU" && $currency != "BHR" && $currency != "ARE" && $currency != "QAT" && $currency != "OMN" && $currency != "JOR" && $currency != "EGY"){
            return redirect()->route('checkout-payment')->with('error', "طريقة الدفع هذه لاتدعم هذه العملة ".session('currency_code'));
        }
        $tapSettings = TapPaymentSetting::Where('method',"MYFATOORAH")->get()->first();
        // إعدادات MyFatoorah
        $IsTest = $tapSettings["key"]=="test_socket"?true:false;
        
        // $config = [
        //     "apiKey" => $tapSettings["value"], // يُفضّل وضع مفتاح الـ API في ملف .env
        //     "isTest" => $IsTest,  // يمكنك تغيير هذا إلى false في حالة الإنتاج
        //     "vcCode" => 'SAU',  // رمز الدولة
        // ];
    
        // تهيئة MyFatoorah
        // $MyFatoorahPayment = new MyFatoorahPayment();
        echo session('currency_code');
        // return 0;
        $config = [
            'apiKey' => $tapSettings["value"],
            'vcCode' => str_replace("SAR","SAU",session('currency_code')),
            'isTest' => $IsTest,
        ];
        
        $paymentMethodId = 0; //to be redirect to MyFatoorah invoice page
        //$paymentMethodId = 1; //to be redirect to Knet payment page if you are using test API token key
        $postFields      = [
            'InvoiceValue' => $request->amount,
            'CustomerName' => $request->first_name." ".$request->last_name,
            'DisplayCurrencyIso' => session('currency_code'),
            'MobileCountryCode'  => $request->country_code,
            'CustomerMobile'     => $request->phone,
            'CustomerEmail'      => $request->email,
            'CallBackUrl'        =>  "https://google.com",
            'ErrorUrl'           =>  "https://github.com/",//'http://localhost:8080/Error', //or 'https://example.com/error.php'
            'Language'           => 'ar', //or 'ar'
        ];
        
        try {
            $mfObj = new MyFatoorahPayment($config);
            $data  = $mfObj->getInvoiceURL($postFields, $paymentMethodId);
            // echo $data->invoiceId;
            // dump($data);
            $invoiceId   = $data["invoiceId"];
            $paymentLink = $data["invoiceURL"];
        

            echo "Click on <a href='$paymentLink' target='_blank'>$paymentLink</a> to pay with invoiceID $invoiceId.";
            return redirect($data["invoiceURL"]);
        } catch (Exception $ex) {
            echo $ex->getMessage();
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
