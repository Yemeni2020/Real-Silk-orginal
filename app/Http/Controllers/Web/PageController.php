<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class PageController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface   $businessSettingRepo,
        private readonly HelpTopicRepositoryInterface         $helpTopicRepo,
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
    )
    {
    }

    public function getAboutUsView(): View|null
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'about-us']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        // 
        $lang=session()->get("local");
        $value=getWebConfig(name: 'about_us');

        if($lang!="en"){
            $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'about_us']);

            
            $value = $pageData['translations']->where("locale",$lang)->firstOrFail()->value;
        }



        // dump($pageData['translations']->where("locale",$lang));
        // return null;
        $aboutUs = $value;
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_about_us'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['about_us'], compact('aboutUs', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getContactView(): View
    {

        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'contacts']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $recaptcha = getWebConfig(name: 'recaptcha');
        return view(VIEW_FILE_NAMES['contacts'], compact('recaptcha', 'robotsMetaContentData'));
    }

    public function getHelpTopicView(): View
    {
        echo session()->get("local");
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'helpTopic']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $helps = $this->helpTopicRepo->getListWhere(orderBy: ['id' => 'desc'], filters: ['status' => 1, 'type' => 'default','lang'=>session()->get("local")], dataLimit: 'all');
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_faq_page'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['faq'], compact('helps', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getRefundPolicyView(): View|RedirectResponse|null
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'refund-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $lang=session()->get("local");
        $value=getWebConfig(name: 'refund-policy');

        $status=$value['status'];
        $value=$value['content'];
        if($lang!="en"){
            $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'refund-policy']);

            
            if ($pageData['translations']->where("locale", $lang)->isNotEmpty())
                $value = $pageData['translations']->where("locale",$lang)->firstOrFail()->value;
        }
        $refundPolicy = $value;
        // dump($refundPolicy);
        // return null;
        if (!$status) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_refund_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['refund_policy_page'], compact('refundPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getReturnPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'return-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $lang=session()->get("local");
        $value=getWebConfig(name: 'refund-policy');

        $status=$value['status'];
        $value=$value['content'];
        if($lang!="en"){
            $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'refund-policy']);

            
            if ($pageData['translations']->where("locale", $lang)->isNotEmpty())
                $value = $pageData['translations']->where("locale",$lang)->firstOrFail()->value;
        }
        $returnPolicy = $value;
        // $returnPolicy = getWebConfig(name: 'return-policy');
        if (!$status) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_return_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['return_policy_page'], compact('returnPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getPrivacyPolicyView(): View|null
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'privacy-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $lang=session()->get("local");
        $value=getWebConfig(name: 'privacy_policy');

        
        
        if($lang!="en"){
            $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'privacy_policy']);


            if ($pageData['translations']->where("locale", $lang)->isNotEmpty())
                $value = $pageData['translations']->where("locale",$lang)->firstOrFail()->value;
        }
        $privacyPolicy = $value;

        // $privacyPolicy = getWebConfig(name: 'privacy_policy');
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_privacy_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['privacy_policy_page'], compact('privacyPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getCancellationPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'cancellation-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        
        $lang=session()->get("local");
        $value=getWebConfig(name: 'cancellation-policy');

        $status=$value['status'];
        $value=$value['content'];
        if($lang!="en"){
            $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'cancellation-policy']);


            if ($pageData['translations']->where("locale", $lang)->isNotEmpty())
                $value = $pageData['translations']->where("locale",$lang)->firstOrFail()->value;
        }
        $cancellationPolicy = $value;


        // $cancellationPolicy = getWebConfig(name: 'cancellation-policy');
        if (!$status) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_cancellation_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['cancellation_policy_page'], compact('cancellationPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getShippingPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'shipping-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $lang=session()->get("local");
        $value=getWebConfig(name: 'shipping-policy');
        $status=$value['status'];
        $value=$value['content'];

        if($lang!="en"){
            $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'shipping-policy']);


            if ($pageData['translations']->where("locale", $lang)->isNotEmpty())
                    $value = $pageData['translations']->where("locale",$lang)?->firstOrFail()?->value??$value;
        }
        $shippingPolicy = $value;

        // $shippingPolicy = getWebConfig(name: 'shipping-policy');
        if (!$status) {
            return redirect()->route('home');
        }
        
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_shipping_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['shipping_policy_page'], compact('shippingPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getTermsAndConditionView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'terms']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $lang=session()->get("local");
        $value=getWebConfig(name: 'terms_condition');

        
        
        if($lang!="en"){
            $pageData = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'terms_condition']);


            if ($pageData['translations']->where("locale", $lang)->isNotEmpty())
                $value = $pageData['translations']->where("locale",$lang)->firstOrFail()->value;
        }
        $termsCondition = $value;
        // $termsCondition = getWebConfig(name: 'terms_condition');
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_terms_conditions'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['terms_conditions_page'], compact('termsCondition', 'pageTitleBanner', 'robotsMetaContentData'));
    }

}
