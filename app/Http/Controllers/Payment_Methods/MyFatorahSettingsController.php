<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\PaymentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TapPaymentSetting;
use App\Traits\Processor;
use GuzzleHttp\Client;
use App\Http\Controllers\Payment_Methods\MyFatoorah\API\Payment\MyFatoorahPayment;
use App\Http\Controllers\Payment_Methods\MyFatoorah\API\Payment\MyFatoorahPaymentStatus;
use DateTime;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Cart;
use Illuminate\Support\Facades\Http;
use App\Models\Currency;


class MyFatorahSettingsController extends Controller
{
    use Processor;
    private $config_values;
    private $base_url;
    
    private PaymentRequest $payment;
    public function __construct(private readonly CustomerRepositoryInterface $customerRepo,PaymentRequest $payment)
    {
        $config = $this->payment_config('my_fatorah', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        if($config){
            $this->base_url = ($config->mode == 'test') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        }
        $this->payment = $payment;

        // $this->apiContext = new ApiContext(
        //     new OAuthTokenCredential(
        //         config('ARIdut7MnX3WrG8BOFcqmIK-5as93UTICro6VZa8tfeUXygr4JWYYEzmvxf0mnrNTEkp0yOarKSnpR9Z'),
        //         config('EGaNDgIvIKNyJa9bUt5UETsLqJJ-hYS73qYAMuO7H9f9r_EikRROmmHPb-hEoSdJG7ERZlkO9OWQBC-V')
        //     )
        // );

        // $this->apiContext->setConfig([
        //     'mode' => config('sandbox')
        // ]);
    }
    private function format_curncy($currency){
        switch ($currency) {
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
        return $currency;
    }

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

            // TapPaymentSetting::where('key', '!=', $request->key . '_socket')
            //     ->where('Type', 1) // إضافة شرط آخر على Type
            //     ->update([
            //         'Type' => null // جعل Type فارغًا
            //     ]);

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


    public function createPaymentWaleet($amount,$customer)
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
            Toastr::error(translate('طريقة الدفع هذه لاتدعم هذه العملة '));
            return back();
            //return redirect()->route('wallet')->with('error', "طريقة الدفع هذه لاتدعم هذه العملة ".session('currency_code'));
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
            'InvoiceValue' => $amount,
            'CustomerName' => $customer->f_name . ' ' . $customer->l_name,
            'DisplayCurrencyIso' => session('currency_code'),
            'MobileCountryCode'  => "+966",
            'CustomerMobile'     => $customer->phone,
            'CustomerEmail'      => $customer['email'],
            'CallBackUrl'        =>  "http://127.0.0.1:8080/wallet",
            'ErrorUrl'           =>  "http://127.0.0.1:8080/wallet?Err='Faild Payment'",//'http://localhost:8080/Error', //or 'https://example.com/error.php'
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



    public function createPayment(Request $request)
    {

            


        
        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        $customer = $this->customerRepo->getFirstWhere(params: ['id' => $data->payer_id]) ?? 0;


        
        // dump($customer );
        // $data=Cart::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        // echo substr($customer->phone, 0,strlen($customer->phone) - 9);

        // return null;
        
        $currency=$data["currency_code"];
        $currency=$this->format_curncy(session('currency_code'));
        // return 0;
        
        // echo $currency;
        $rate = Currency::where(['code' => $data["currency_code"]])->first()->exchange_rate ;
        $main_amount=($data->payment_amount + 0.006)/$rate;
        $rate2 = Currency::where(['code' => session('currency_code')])->first()->exchange_rate;

        // dump($data);
        $local_amount=$main_amount*$rate2;
        // echo $local_amount;
        // return null;
        if($currency != "KWT" && $currency != "SAU" && $currency != "BHR" && $currency != "ARE" && $currency != "QAT" && $currency != "OMN" && $currency != "JOR" && $currency != "EGY"){
            Toastr::error(translate("this_method_payment_don't_support_this_currency (".session('currency_code').")"));
            return back();
        }
        // ✅ التحقق من صحة رقم الهاتف
        $mobileCountryCode = strlen($customer->phone) > 9 ? substr($customer->phone, 0, strlen($customer->phone) - 9) : "+966";
        $customerMobile = strlen($customer->phone) > 9 ? substr($customer->phone, (strlen($customer->phone) - 9)) : $customer->phone;

        if (!is_numeric($customerMobile) || strlen($customerMobile) < 4) {
            Toastr::error(translate("Invalid phone number. Please update your profile."));
            return back();
        }
        $IsTest = $this->config_values->mode=="test"?true:false;//$tapSettings["key"]=="test_socket"?true:false;
        

        $config = [
            'apiKey' => $this->config_values->api_kay,//$tapSettings["value"],
            'vcCode' => $currency,
            'isTest' => $IsTest,
        ];
        
        $paymentMethodId = 0; //to be redirect to MyFatoorah invoice page
        $postFields      = [
            'InvoiceValue' => $local_amount,
            'CustomerName' => $customer->f_name." ".$customer->l_name,
            'DisplayCurrencyIso' => session('currency_code'),
            'MobileCountryCode'  => strlen($customer->phone)>9?substr($customer->phone, 0,strlen($customer->phone) - 9):"+966",
            'CustomerMobile'     => strlen($customer->phone)>9?substr($customer->phone, (strlen($customer->phone)-9)):$customer->phone,
            'CustomerEmail'      => $customer->email,
            'CallBackUrl'        =>  route('my_fatorah.success',['payment_id' => $request->payment_id,'currency'=>$currency]),
            'ErrorUrl'           =>  route('my_fatorah.error',['payment_id' => $request->payment_id]),//'http://localhost:8080/Error', //or 'https://example.com/error.php'
            'Language'           => 'ar', //or 'ar'
        ];
        
        try {
            $mfObj = new MyFatoorahPayment($config);
            $data  = $mfObj->getInvoiceURL($postFields, $paymentMethodId);

            // return null;
            if(isset($data['Error'])){
                Toastr::error(translate("Your data is incomplete. Please complete the data in your profile."));
                return back();
            }else{
                // echo $data->invoiceId;
                // dump($data);
                $invoiceId   = $data["invoiceId"];
                $paymentLink = $data["invoiceURL"];
                // echo $paymentLink;
                // $statusLink = MyFatoorahPayment::getPaymentStatusLink($paymentLink, $invoiceId);
                // dump($postFields);
                // return null;
    
                
                echo "Click on <a href='$paymentLink' target='_blank'>$paymentLink</a> to pay with invoiceID $invoiceId.";
                return redirect($data["invoiceURL"]);

            }
        } catch (Exception $ex) {
            Toastr::error(translate("Your data is incomplete. Please complete the data in your profile."));
            return back();
            echo $ex->getMessage();
        }
           
        
    }

    public function success(Request $request)
    {
        
        $IsTest = $this->config_values->mode=="test"?true:false;//$tapSettings["key"]=="test_socket"?true:false;

        $config = [
            'apiKey' => $this->config_values->api_kay,//$tapSettings["value"],
            'vcCode' => $request->currency,
            'isTest' => $IsTest,
        ];
        $KeyType = 'PaymentId';
        $mfObj = new MyFatoorahPaymentStatus($config);
        $Transaction  = $mfObj->getPaymentStatus($request->paymentId,$KeyType);
        // $data = $this->payment::where(['id' => $request['payment_id']])->first();
        // dump($this->getTransactionId($request->id));
        // return $request;
        if(isset($Transaction)||$Transaction->InvoiceStatus=="Paid"){
            $TransactionId=$Transaction->focusTransaction->TransactionId;
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'my_fatorah',
                'is_paid' => 1,
                'transaction_id' => $TransactionId,

            ]);
            $data = $this->payment::where(['id' => $request->payment_id])->first();
            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }
            // dump($data);
            
            // return null;

            return $this->payment_response($data,'success');
            
            

        }else{
            $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
            if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
            return $this->payment_response($payment_data,'fail');
            // redirect(route('my_fatorah.error',['payment_id' => $request->payment_id]));
        }
        
    }
    
    public function getTransactionId($paymentId)
    {
        
        // إعدادات MyFatoorah
        $apiKey = $this->config_values->api_kay;
        $isTest = $this->config_values->mode=="test"?true:false;
        
        // URL للتحقق من حالة الدفع
        $url = $isTest 
            ? "https://apitest.myfatoorah.com/v2/GetPaymentStatus" 
            : "https://api.myfatoorah.com/v2/GetPaymentStatus";
    
        // إعداد بيانات الطلب
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];
        $postFields = [
            'KeyType' => 'InvoiceId',  // نوع المفتاح هو InvoiceId
            'Key' => $paymentId,       // معرف الدفع
        ];
    
        // إرسال الطلب للتحقق من حالة الدفع
        $response = Http::withHeaders($headers)->post($url, $postFields);
    
        // التحقق من حالة الاستجابة
        if ($response->successful()) {
            $data = $response->json();
            // return null;
            if (isset($data['Data']['InvoiceStatus']) && $data['Data']['InvoiceStatus'] === 'Paid') {
                $transactionId = $data['Data']['TransactionId'];
                return response()->json(['success' => true, 'transaction_id' => $transactionId, 'message' => 'تمت عملية الدفع بنجاح']);
            } else {
                return response()->json(['success' => false, 'message' => 'فشلت عملية الدفع أو لا تزال قيد الانتظار']);
            }
        } else {
            dump($response);
            return null;
            // في حالة وجود خطأ في الاستجابة
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء التحقق من حالة الدفع']);
        }
    }

    // public function success(Request $request)
    // {
    //     // Stripe::setApiKey($this->config_values->api_key);
    //     // $session = Session::retrieve($request->get('session_id'));

    //     // if ($session->payment_status == 'paid' && $session->status == 'complete') {

    //     //     $this->payment::where(['id' => $request['payment_id']])->update([
    //     //         'payment_method' => 'stripe',
    //     //         'is_paid' => 1,
    //     //         'transaction_id' => $session->payment_intent,
    //     //     ]);

    //     //     $data = $this->payment::where(['id' => $request['payment_id']])->first();

    //     //     if (isset($data) && function_exists($data->success_hook)) {
    //     //         call_user_func($data->success_hook, $data);
    //     //     }

    //     //     return $this->payment_response($data,'success');
    //     // }
    //     // $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
    //     // if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
    //     //     call_user_func($payment_data->failure_hook, $payment_data);
    //     // }
    //     return $this->payment_response($payment_data,'fail');
    // }

    
    public function callback(Request $request)
    {
        // معالجة الدفع بعد العودة من Tap Payment
        // هنا يمكنك معالجة الرد وتحديث الحالة في النظام الخاص بك
        return view('payment.success'); // عرض رسالة نجاح
    }

    public function error(Request $request)
    {
        Toastr::error('Payment failed');
        return redirect(route('checkout-details'));
        // return "Faild Pay";
        // معالجة الإعادة إلى الموقع بعد الدفع
        // return view('payment.redirect'); // عرض صفحة إعادة التوجيه
    }
}
