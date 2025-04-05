<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Services\ContractsService;
use App\Enums\ViewPaths\Admin\Pages;
use App\Http\Controllers\BaseController;
use App\Models\BusinessSetting;
use App\Http\Requests\Admin\AboutUsRequest;
use App\Http\Requests\Admin\PageUpdateRequest;
use App\Http\Requests\Admin\PrivacyPolicyRequest;
use App\Http\Requests\Admin\TermsConditionRequest;
use App\Http\Requests\Admin\ContractRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PagesController extends BaseController
{

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly TranslationRepositoryInterface $translationRepo,
        private readonly ContractsService $ContractsService,
    ){}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        // $lang=isset($request["lang"])?$request["lang"]:"en";
        return $this->getTermsConditionView();
    }

    
   
    public function getTermsConditionView(): View
    {
        $curnnet_lang = getDefaultLanguage();
        $languages = getWebConfig(name: 'pnc_language') ?? null;

        $terms_condition = $this->businessSettingRepo->getFirstWhere(params: ['type'=>'terms_condition']);
        return view(Pages::TERMS_CONDITION[VIEW], compact('terms_condition','curnnet_lang','languages'));
    }

    public function updateTermsCondition(TermsConditionRequest $request): RedirectResponse|null
    {
        // dump($request["lang"]);
        // return null;
        $businessSetting = BusinessSetting::where("type","terms_condition")->firstOrFail();
    
        $this->translationRepo->CreateOrUpdate(request: $request, model: 'App\Models\BusinessSetting', id: $businessSetting->id);


        $value=$request['value'][array_search('en', $request['lang'])];
        $this->businessSettingRepo->updateWhere(params: ['type'=>'terms_condition'], data: ['value' => $value]);
        clearWebConfigCacheKeys();
        Toastr::success(translate('Terms_and_Condition_Updated_successfully'));
        return back();
    }


    public function getcontract($type="factory"): View
    {
        $curnnet_lang = getDefaultLanguage();
        $languages = getWebConfig(name: 'pnc_language') ?? null;

        $contract = $this->businessSettingRepo->getFirstWhere(params: ['type'=>"contract_$type"]);
        return view(Pages::CONTRACTS[VIEW], compact('contract','curnnet_lang','languages','type'));
    }
    public function updateContract(TermsConditionRequest $request,$type): RedirectResponse|null
    {
        $value=$request['value'];
        if($type=="office" || $type=="factory"){
            $this->businessSettingRepo->updateOrInsert(type: "contract_$type", value: $value);
            clearWebConfigCacheKeys();
            Toastr::success(translate("contract_Updated_successfully"));
            



            
        }else
            Toastr::error(translate('Cannot_saved'));

        return back();
        
    }

    public function DownloadTemplate($type = "factory")
    {
        $this->ContractsService->DownloadTemplate($type);// إنهاء التنفيذ بعد الإخراج
    }

    public function ViewTemplate(Request $request,$type = "factory")
    {
        $fullname="";
        if($request->has("fullname"))
            $fullname = $request->query('fullname', translate("not_selected"));
        $contract = $this->businessSettingRepo->getFirstWhere(params: ['type' => "contract_$type"])?->value;
        return view("contract.contract", compact('contract','fullname'));
        
    }

    public function getPrivacyPolicyView(): View
    {
        $curnnet_lang = getDefaultLanguage();
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $privacy_policy = $this->businessSettingRepo->getFirstWhere(params: ['type'=>'privacy_policy']);
        return view(Pages::PRIVACY_POLICY[VIEW], compact('privacy_policy','curnnet_lang','languages'));
    }

    public function updatePrivacyPolicy(PrivacyPolicyRequest $request): RedirectResponse
    {
        $businessSetting = BusinessSetting::where("type","privacy_policy")->firstOrFail();
        $this->translationRepo->CreateOrUpdate(request: $request, model: 'App\Models\BusinessSetting', id: $businessSetting->id);

        $value=$request['value'][array_search('en', $request['lang'])];


        $this->businessSettingRepo->updateWhere(params: ['type'=>'privacy_policy'], data: ['value' => $value]);
        Toastr::success(translate('Privacy_policy_Updated_successfully'));
        return back();
    }


    public function getPageView($page): View|RedirectResponse|null
    {

        $curnnet_lang = getDefaultLanguage();
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $pages = ['refund-policy', 'return-policy', 'cancellation-policy', 'shipping-policy'];
        if (in_array($page, $pages)) {
            $data = $this->businessSettingRepo->getFirstWhere(params: ['type' => $page]);
            // dump($data);
            if(!isset($data)){
                $this->businessSettingRepo->add(data: ['type' => $page, 'value' => '{"status":"1","content":""}']);
            }
            // return null;

            return view(Pages::VIEW[VIEW], compact('page', 'data','curnnet_lang','languages'));
        }
        Toastr::error(translate('invalid_page'));
        return back();
    }

    public function updatePage(PageUpdateRequest $request, $page): RedirectResponse
    {

        $businessSetting = BusinessSetting::where("type",$page)->firstOrFail();
        $this->translationRepo->CreateOrUpdate(request: $request, model: 'App\Models\BusinessSetting', id: $businessSetting->id);

        $value=$request['value'][array_search('en', $request['lang'])];



        $pages = ['refund-policy', 'return-policy', 'cancellation-policy', 'shipping-policy'];
        if (in_array($page, $pages)) {
            $value = json_encode(['status' => $request->get('status', 0), 'content' => $value]);
            $this->businessSettingRepo->updateOrInsert(type: $page, value: $value);
            Toastr::success(translate('updated_successfully'));
        } else {
            Toastr::error(translate('invalid_page'));
        }
        return back();
    }

    public function getAboutUsView(): View
    {
        $curnnet_lang = getDefaultLanguage();
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'about_us']);
        return view(Pages::ABOUT_US[VIEW], compact('pageData','curnnet_lang','languages'));
    }

    public function updateAboutUs(AboutUsRequest $request): RedirectResponse
    {
        $businessSetting = BusinessSetting::where("type",'about_us')->firstOrFail();
        $this->translationRepo->CreateOrUpdate(request: $request, model: 'App\Models\BusinessSetting', id: $businessSetting->id);

        $value=$request['value'][array_search('en', $request['lang'])];
        $this->businessSettingRepo->updateWhere(params: ['type'=>'about_us'], data: ['value' => $value]);
        Toastr::success(translate('about_us_updated_successfully'));
        return back();
    }


}
