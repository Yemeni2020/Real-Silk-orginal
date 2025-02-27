<?php

namespace App\Http\Controllers\Office\Auth;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\EmailTemplatesRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Enums\ViewPaths\Vendor\Auth;
use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\VendorAddRequest;
use App\Http\Requests\Vendor\VendorCheckRequest;
use App\Repositories\VendorRegistrationReasonRepository;
use App\Services\ShopService;
use App\Services\VendorService;
use App\Traits\EmailTemplateTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Brian2694\Toastr\Facades\Toastr;

class RegisterController extends BaseController
{
    use EmailTemplateTrait;
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly VendorWalletRepositoryInterface $vendorWalletRepo,
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly VendorService $vendorService,
        private readonly ShopService $shopService,
        private readonly EmailTemplatesRepositoryInterface $emailTemplatesRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly HelpTopicRepositoryInterface $helpTopicRepo,
        private readonly VendorRegistrationReasonRepository $vendorRegistrationReasonRepo,

    )
    {
    }

    public function index(?Request $request,$referral_code=null, string $type = null): View|Collection|LengthAwarePaginator|callable|RedirectResponse
    {
        return $this->getView($referral_code);
    }
    public function getView($referral_code=null):View|RedirectResponse
    {
        $isoffice=false;
        if(isset($_GET["office"]))
            $isoffice=true;
        
        // dump($isoffice);
        // return null;
        $businessMode = getWebConfig(name:'business_mode');
        $vendorRegistration = getWebConfig(name:'seller_registration');
        if((isset($businessMode) && $businessMode=='single') || (isset($vendorRegistration) && $vendorRegistration==0))
        {
            Toastr::warning(translate('access_denied').'!!');
            return redirect('/');
        }
        $vendorRegistrationHeader = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'vendor_registration_header'])['value']);
        $vendorRegistrationReasons = $this->vendorRegistrationReasonRepo->getListWhere(orderBy: ['priority' => 'desc'], filters: ['status' => 1], dataLimit: 'all');
        $sellWithUs = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'vendor_registration_sell_with_us'])['value']);
        $downloadVendorApp = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'download_vendor_app'])['value']);
        $businessProcess = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'business_process_main_section'])['value']);
        $businessProcessStep = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'business_process_step'])['value']);
        $helpTopics = $this->helpTopicRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['type' => 'vendor_registration', 'status' => '1'],
            dataLimit: 'all');
        return view(VIEW_FILE_NAMES[Auth::VENDOR_REGISTRATION[VIEW]],compact('vendorRegistrationHeader','vendorRegistrationReasons','sellWithUs','downloadVendorApp','helpTopics','businessProcess','businessProcessStep',"referral_code","isoffice"));
    }

    public function add(VendorAddRequest $request): JsonResponse
    {
        
        $vendor = $this->vendorRepo->add(data: $this->vendorService->getAddData($request));
        if(!empty($request->referral_code))
            $this->vendorService->create_referral_vendor($vendor->id,$request->referral_code);
        $this->shopRepo->add($this->shopService->getAddShopDataForRegistration(request: $request, vendorId: $vendor['id']));
        $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: $vendor['id']));

        $data = [
            'vendorName' => $request['f_name'],
            'status' => 'pending',
            'subject' => translate('Vendor_Registration_Successfully_Completed'),
            'title' => translate('Vendor_Registration_Successfully_Completed'),
            'userType' => 'vendor',
            'templateName' => 'registration',
        ];
        event(new VendorRegistrationEvent(email: $request['email'], data: $data));
        return response()->json([
                'redirectRoute' => route('vendor.auth.login')
            ]
        );
    }
    public function checkEmailPhone(VendorCheckRequest $request){
        // $request->validate(
        //     ['email'=>'required|unique:sellers',
        //     'phone'=>'required|unique:sellers|max:20',]
        // ,['email.required' => translate('The_email_field_is_required'),
        //     'email.unique' => translate('The_email_has_already_been_taken'),
        //     'phone.required' => translate('The_phone_field_is_required'),
        //     'phone.unique' => translate('The_phone_number_has_already_been_taken'),
        // ]);

        return response()->json([
            'status' => true,
            'message' => 'البريد الإلكتروني ورقم الهاتف متاحان للتسجيل.'
        ], 200);
    }
}
