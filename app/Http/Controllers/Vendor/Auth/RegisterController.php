<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\EmailTemplatesRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\SignatureRepositoryInterface;
use App\Enums\ViewPaths\Vendor\Auth;
use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\VendorAddRequest;
use App\Http\Requests\Vendor\VendorCheckRequest;
use App\Repositories\VendorRegistrationReasonRepository;
use App\Repositories\PhoneOrEmailVerificationRepository;
use App\Services\ShopService;
use App\Services\VendorService;
use App\Services\ContractsService;
use App\Traits\EmailTemplateTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailSellerVerificationMail;

class RegisterController extends BaseController
{
    use EmailTemplateTrait;
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly VendorWalletRepositoryInterface $vendorWalletRepo,
        private readonly SignatureRepositoryInterface $signatureRepo,
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly VendorService $vendorService,
        private readonly ShopService $shopService,
        private readonly ContractsService $ContractsService,
        private readonly EmailTemplatesRepositoryInterface $emailTemplatesRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly HelpTopicRepositoryInterface $helpTopicRepo,
        private readonly VendorRegistrationReasonRepository $vendorRegistrationReasonRepo,
        private readonly PhoneOrEmailVerificationRepository $EmailVerification,

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
        $type=$isoffice?"office":"factory";
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
        return view(VIEW_FILE_NAMES[Auth::VENDOR_REGISTRATION[VIEW]],compact('vendorRegistrationHeader','vendorRegistrationReasons','sellWithUs','downloadVendorApp','helpTopics','businessProcess','businessProcessStep',"referral_code","isoffice",'type'));
    }
    public function ViewContract(Request $request,$type = "factory")
    {
        $fullname="";
        $type=str_replace("fictory","factory",$type);

        $lang=getDefaultLanguage();
        $contractModel = $this->businessSettingRepo->getFirstWhere(params: ['type' => "contract_$type"]);
        $contract = $contractModel?->translations()
    ->where('locale', getDefaultLanguage())
    ->where('key', 'value') // إذا كان المفتاح المخزن في الترجمة بهذا الاسم
    ->first()?->value ??$contractModel?->value;
        $shopName="";
        $number_cr="";
        $country="";
        $city="";
        $address="";
        if($request->has("fullname"))
            $fullname = $request->query('fullname', translate("not_selected"));
        if($request->has("shopName"))
            $shopName = $request->query('shopName', translate("not_selected"));
        if($request->has("number_cr"))
            $number_cr = $request->query('number_cr', translate("not_selected"));
        if($request->has("country"))
            $country = $request->query('country', translate("not_selected"));
        if($request->has("city"))
            $city = $request->query('city', translate("not_selected"));
        if($request->has("address"))
            $address = $request->query('address', translate("not_selected"));

        if(auth("seller")->user() && !$request->has("shopName")){
            $vendor=auth("seller")->user();
            return view("contract.contract", compact('contract','fullname','vendor','lang'));
        }else{
            return view("contract.contract", compact('contract','fullname',"shopName","number_cr","country","city","address",'lang'));
        }
        
    }
    public function add(VendorAddRequest $request): JsonResponse
    {
        

        $vendor = $this->vendorRepo->add(data: $this->vendorService->getAddData($request));
        if(!empty($request->referral_code))
            $this->vendorService->create_referral_vendor($vendor->id,$request->referral_code);
        $this->shopRepo->add($this->shopService->getAddShopDataForRegistration(request: $request, vendorId: $vendor['id']));
        $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: $vendor['id']));
        $vendorId=$vendor['id'];


        //signature
        if(getWebConfig('vendors_must_sing_contract') && $request->has('signature')){

            $signatureBase64 = $request->input('signature'); // ✅ استلام التوقيع كنص
           
            $this->signatureRepo->add([
                'seller' => $vendorId,
                'signature_path' => $signatureBase64, // ✅ حفظ المسار فقط
                'contract_path' => 'app/private/contracts/'.$vendor->type_account.'/contract_' . $vendor->id . '.pdf'
            ]);
            // $this->ContractsService->DownloadContract($vendor);
            $this->ContractsService->SaveContract($vendor);


            $this->vendorRepo->update($vendor->id,["signatures"=>true]);
            //End signature
        }

        $data = [
            'vendorName' => $request['f_name'],
            'status' => 'pending',
            'subject' => translate('Vendor_Registration_Successfully_Completed'),
            'title' => translate('Vendor_Registration_Successfully_Completed'),
            'userType' => 'vendor',
            'templateName' => 'registration',
        ];
        event(new VendorRegistrationEvent(email: $request['email'], data: $data));
        $route = route('vendor.auth.login');
        if($request['account_type'] == 'office')
            $route = route('office.auth.login');
        return response()->json([
                'redirectRoute' => $route
            ]
        );
    }
    public function checkEmailPhone(VendorCheckRequest $request){


        // $token=mt_rand(100000, 999999);
        // $this->EmailVerification->updateOrCreate(["phone_or_email"=>$request["email"]],['token'=>$token]);

        // $data = [
        //     'subject' => 'Confirm your Email',
        //     'token' => $token,
            
        // ];
        // try {
        //     Mail::to($request["email"])->send(new EmailSellerVerificationMail($data));
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => $e->getMessage(),
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
        return response()->json([
            'status' => true,
            'message' => 'البريد الإلكتروني ورقم الهاتف متاحان للتسجيل.'
        ], 200);
    }
    public function confirmEmail(Request $request){


        $_email= $this->EmailVerification->getFirstWhere(["phone_or_email"=>$request["email"]]);

        if($_email->token==$request["token"]){
            return response()->json([
                'status' => true,
                'message' =>  translate("Email_Is_Conifrmed")
            ], 200);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => translate("The_token_is_invalid")
            ], 201);
        }

        
    }
    
    
}
