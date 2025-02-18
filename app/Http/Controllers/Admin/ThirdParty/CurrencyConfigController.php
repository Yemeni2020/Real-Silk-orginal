<?php

namespace App\Http\Controllers\Admin\ThirdParty;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\CurrencyConfig;
use App\Http\Controllers\BaseController;
use App\Services\SocialLoginService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyConfigController extends BaseController
{

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ){}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $data = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'Currency_exchangerate']);
        if(isset($data))
            $data=$data->value??"";
        return view(CurrencyConfig::VIEW[VIEW], compact('data'));
    }

    public function update(Request $request): RedirectResponse|null
    {
        $key = $request->client_secret;
        
        $req_url = "https://v6.exchangerate-api.com/v6/$key/latest/USD";

        $response_json = @file_get_contents($req_url);

        // التحقق مما إذا كان `file_get_contents` قد فشل في جلب البيانات
        if (!$response_json) {
            Toastr::error(translate('Failed to connect to the API. Please check your credentials.'));
            return back();
        }

        // تحويل البيانات إلى JSON
        $response_data = json_decode($response_json, true);

        // التحقق مما إذا كانت الاستجابة تحتوي على خطأ
        if (!isset($response_data['result']) || $response_data['result'] !== "success") {
            Toastr::error(translate('Invalid API Key. Please enter a valid key.'));
            return back();
        }

        // ✅ إذا كان API صحيحًا، نقوم بحفظه في قاعدة البيانات

        $this->businessSettingRepo->updateOrInsert(type:'Currency_exchangerate', value:$key);

        Toastr::success(translate('Currency exchangerate credentials updated successfully.'));
        return back();
    }

    public function updateAppleLogin($service, Request $request, SocialLoginService $socialLoginService): RedirectResponse
    {
        $appleLogin = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'apple_login']);
        $credentialArray = $socialLoginService->getAppleData(request: $request, appleLogin: $appleLogin, service: $service);
        $this->businessSettingRepo->updateWhere(params: ['type'=>'apple_login'], data: ['value' => $credentialArray]);
        Toastr::success(translate('credential_updated_'.$service));
        return back();
    }

}
