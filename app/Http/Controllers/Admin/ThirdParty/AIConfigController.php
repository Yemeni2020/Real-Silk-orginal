<?php

namespace App\Http\Controllers\Admin\ThirdParty;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\AIConfig;
use App\Http\Controllers\BaseController;
use App\Services\SocialLoginService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AIConfigController extends BaseController
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
    public function index(Request|null $request, string $type = null): View|null
    {
        return $this->getView();
    }

    public function getView(): View|null
    {
        $OpenAi = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'OpenAi_translate']);
        $OpenAi=isset($OpenAi["value"])?json_decode($OpenAi["value"],true):[];

        $DeepSeekAI =   $this->businessSettingRepo->getFirstWhere(params: ['type' => 'DeepSeekAI_translate']);
        $DeepSeekAI =   isset($DeepSeekAI["value"])?json_decode($DeepSeekAI["value"],true):[];

        $deepl  =   $this->businessSettingRepo->getFirstWhere(params: ['type' => 'deepl_translate']);
        $deepl  =   isset($deepl["value"])?json_decode($deepl["value"],true):[];

        $defulte_translate  =   $this->businessSettingRepo->getFirstWhere(params: ['type' => 'defulte_translate']);
        $defulte_translate  =   isset($defulte_translate["value"])?json_decode($defulte_translate["value"],true):[];
        $auto_translate  =   $this->businessSettingRepo->getFirstWhere(params: ['type' => 'auto_translate']);
        $auto_translate  =   isset($auto_translate["value"])?json_decode($auto_translate["value"],true):[];

        $generate_ai_setting  =   $this->businessSettingRepo->getFirstWhere(params: ['type' => 'generate_ai_setting']);
        $generate_ai_setting  =isset($generate_ai_setting["value"])?json_decode($generate_ai_setting["value"],true):[];

        $default_generate=isset($generate_ai_setting["default"])?$generate_ai_setting["default"]:"";
        return view(AIConfig::VIEW[VIEW], compact('OpenAi','DeepSeekAI','deepl','defulte_translate','auto_translate','default_generate'));
    }

    public function update(Request $request): RedirectResponse|null
    {

        
        
        if($request->has("client_secret") || $request->has("type")){
            $key = $request->client_secret;
            $status=$request->status??false;
            $type = $request->type;

            $value=[];
            $value["kay"]=$key;
            $value["status"]=$status;
            $value=json_encode($value);
            $this->businessSettingRepo->updateOrInsert(type:$type.'_translate', value:$value);

            Toastr::success(translate('Ai translate updated successfully.'));
            return back();
        }
        else{
            Toastr::error(translate('Cannot save for data is wrang.'));

        }

        // ✅ إذا كان API صحيحًا، نقوم بحفظه في قاعدة البيانات

        
    }
    public function updateSettingTranslate(Request $request): RedirectResponse|null
    {

        
        
        if($request->has("default") || $request->has("auto_translate")){
            $default = $request->default;
            $auto = $request->auto_translate;
            
            $this->businessSettingRepo->updateOrInsert(type:'defulte_translate', value:$default);
            $this->businessSettingRepo->updateOrInsert(type:'auto_translate', value:$auto);

            Toastr::success(translate('Ai setting translate updated successfully.'));
            return back();
        }
        else{
            Toastr::error(translate('Cannot save for data is wrang.'));

        }

        // ✅ إذا كان API صحيحًا، نقوم بحفظه في قاعدة البيانات

        
    }
    
    public function updateSettingGenerate(Request $request): RedirectResponse|null
    {

        
        
        if($request->has("default") ){
            $default = $request->default;
            $value=[];
            $value["default"]=$default;
            $value=json_encode($value);
            $this->businessSettingRepo->updateOrInsert(type:'generate_ai_setting', value:$value);

            Toastr::success(translate('Ai setting translate updated successfully.'));
            return back();
        }
        else{
            Toastr::error(translate('Cannot save for data is wrang.'));

        }

        // ✅ إذا كان API صحيحًا، نقوم بحفظه في قاعدة البيانات

        
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
