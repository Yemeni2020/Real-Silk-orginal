<?php
namespace App\Services;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;

class ContractsService{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly VendorRepositoryInterface $VendorRepo,
    ){}


    public function DownloadTemplate($type = "factory")
    {
        // **1️⃣ جلب بيانات العقد من الإعدادات**
        $contract = $this->businessSettingRepo->getFirstWhere(params: ['type' => "contract_$type"])?->value;

        // **2️⃣ تحميل عرض الـ View في متغير (HTML)**
        $html = \View::make("contract.contract", compact('contract'))->render();

        // **3️⃣ إنشاء كائن mPDF مع ضبط إعدادات الهوامش**
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 40, // هامش علوي
            'margin_bottom' => 50, // هامش سفلي
        ]);

        // **4️⃣ إضافة ترويسة (Header)**
        $mpdf->SetHTMLHeader('
            <div style="text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000;position:relative;">

                <img src="' . asset("public/assets/front-end/img/template/header.png") . '" width="100%" style="vertical-align: middle;position:absolute;">
                            <span>عقد الكتروني</span>

            </div>
        ');

        // **5️⃣ إضافة تذييل (Footer)**
        $mpdf->SetHTMLFooter('
            <div style="text-align: center; font-size: 12px; color: #555;position:relative;">
                
                صفحة {PAGENO} من {nbpg}
                <br>
                <span>حقوق النشر &copy; ' . date("Y") . ' - جميع الحقوق محفوظة</span>
                <img src="' . asset("public/assets/front-end/img/template/footer.png") . '" width="100%" style="vertical-align: middle;position:absolute;">
            </div>
        ');

        // **6️⃣ تحميل محتوى الـ HTML داخل mPDF**
        $mpdf->WriteHTML($html);

        // **7️⃣ عرض ملف PDF في المتصفح**
        $mpdf->Output("contract_$type.pdf", 'I'); // العرض في المتصفح مباشرة
        exit; // إنهاء التنفيذ بعد الإخراج
    }
    
    public function DownloadContract($vendorId)
    {
        // ✅ **التحقق من أن المستخدم الحالي هو نفس التاجر**
        $user =  auth('seller')->user();
        
        if (!$user || $user->id != $vendorId) {
            abort(403, "❌ You_don't_have_permmsion");
        }
    
        // ✅ **جلب بيانات البائع**
        $type = $user->type_account;
    
        // ✅ **تحديد المسار الصحيح**
        $filePath = storage_path("app/private/contracts/$type/contract_{$user->id}.pdf");
    
        // ✅ **التحقق من وجود العقد**
        if (!file_exists($filePath)) {
            abort(404, "❌ العقد غير موجود.");
        }
    
        // ✅ **إرجاع الملف كتنزيل محمي**
        return response()->download($filePath, "contract_{$user->id}.pdf", [
            'Content-Type' => 'application/pdf'
        ]);
    }

    public function SaveContract($vendor)
    {
        // ✅ **التحقق من وجود البائع**
        if (!$vendor) {
            abort(404, "البائع غير موجود");
        }

        $type = $vendor->type_account;
        $fullname = trim($vendor->f_name . " " . $vendor->l_name);

        $type=str_replace("fictory","factory",$type);
        // ✅ **جلب بيانات العقد**
        $contractData = $this->businessSettingRepo->getFirstWhere(params: ['type' => "contract_$type"]);
        if (!$contractData) {
            abort(404, "$type العقد غير متوفر");
        }

        $contract = $contractData->value;

        // ✅ **تحميل الـ View كـ HTML**
        $html = view("contract.contract", compact('contract', "fullname", "vendor"))->render();

        // ✅ **إنشاء ملف PDF باستخدام mPDF**
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 40,
            'margin_bottom' => 50,
        ]);

        // ✅ **إضافة ترويسة (Header)**
        $mpdf->SetHTMLHeader('
            <div style="text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000;">
                <img src="' . asset("public/assets/front-end/img/template/header.png") . '" width="100%" style="vertical-align: middle;">
                <span>عقد إلكتروني</span>
            </div>
        ');

        // ✅ **إضافة تذييل (Footer)**
        $mpdf->SetHTMLFooter('
            <div style="text-align: center; font-size: 12px; color: #555;">
                صفحة {PAGENO} من {nbpg}
                <br>
                <span>حقوق النشر &copy; ' . date("Y") . ' - جميع الحقوق محفوظة</span>
                <img src="' . asset("public/assets/front-end/img/template/footer.png") . '" width="100%" style="vertical-align: middle;">
            </div>
        ');

        // ✅ **إضافة محتوى العقد إلى ملف PDF**
        $mpdf->WriteHTML($html);

        // 🔹 **مسار حفظ العقد داخل `storage/app/public/contracts`**
        $storagePath = storage_path("app/private/contracts/$type");
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true); // ✅ إنشاء المجلد مع السماح بالوصول إليه
        }

        // ✅ **تحديد اسم الملف بناءً على `ID` البائع**
        $filePath = "$storagePath/contract_{$vendor->id}.pdf";

        // ✅ **حفظ العقد كملف PDF داخل `storage`**
        $mpdf->Output($filePath, "F");

        // ✅ **إرجاع رابط الوصول إلى العقد**
        return response()->json([
            'message' => 'تم حفظ العقد بنجاح',
            'contract_url' => asset("storage/contracts/$type/contract_{$vendor->id}.pdf")
        ]);
    }
}

?>