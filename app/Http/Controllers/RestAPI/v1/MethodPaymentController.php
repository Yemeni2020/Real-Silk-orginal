<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Utils\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\TapPaymentSetting;

class MethodPaymentController extends Controller
{

    public function GetMethod()
    {
        //columns For this Url /api/v1/MethodPayment
        //method Enm{TAP,MYFATOORAH} // Chose What is Method Payment If TapPayment Is Tap If MyFatoorah Payment Chose
        //url_back string // Is Url To Back Is Optional
        //value text // Is The Key To Connect API
        //key Enum{'test_socket','live_socket'} // IF The Mode Is Test Or live
        //Status string // Check If This Payment Is Active In Admin Dashboard

        // جلب كل السجلات
        $settings = TapPaymentSetting::all();
    
        // تعديل الاسم من 'Type' إلى 'Status'
        $settings = $settings->map(function ($setting) {
            $setting->Status = $setting->Type;
            unset($setting->Type); // حذف العمود القديم
            return $setting;
        });
    
        // إرجاع النتيجة كـ JSON
        return response()->json($settings);
    }

}