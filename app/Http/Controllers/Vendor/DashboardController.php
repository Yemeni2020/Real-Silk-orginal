<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\SignatureRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\WithdrawalMethodRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Repositories\PhoneOrEmailVerificationRepository;
use App\Repositories\VendorRepository;
use App\Enums\OrderStatus;
use App\Enums\ViewPaths\Vendor\Dashboard;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\WithdrawRequest;
use App\Repositories\BrandRepository;
use App\Repositories\OrderTransactionRepository;
use App\Services\DashboardService;
use App\Services\ContractsService;
use App\Services\VendorWalletService;
use App\Services\WithdrawRequestService;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use App\Models\SellerWallet;
use App\Models\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailSellerVerificationMail;
use Illuminate\Support\Facades\Validator;

class DashboardController extends BaseController
{
    public function __construct(
        private readonly OrderTransactionRepository $orderTransactionRepo,
        private readonly ProductRepositoryInterface $productRepo,
        private readonly DeliveryManRepositoryInterface $deliveryManRepo,
        private readonly OrderRepositoryInterface $orderRepo,
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly SignatureRepositoryInterface $signatureRepo,
        private readonly BrandRepository $brandRepo,
        private readonly VendorWalletRepositoryInterface $vendorWalletRepo,
        private readonly VendorWalletService $vendorWalletService,
        private readonly WithdrawalMethodRepositoryInterface $withdrawalMethodRepo,
        private readonly WithdrawRequestRepositoryInterface $withdrawRequestRepo,
        private readonly WithdrawRequestService $withdrawRequestService,
        private readonly DashboardService $dashboardService,
        private readonly ContractsService $ContractsService,
        private readonly PhoneOrEmailVerificationRepository $EmailVerification,
        private readonly VendorRepository $vendorRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }

    /**
     * @return View
     */
    public function getView():View|null
    {
        $vendorId = auth('seller')->id();
        $topSell = $this->productRepo->getTopSellList(
            filters:[
                'added_by'=>'seller',
                'seller_id'=>$vendorId,
                'request_status' =>1
            ],
            relations: ['orderDetails']
        )->take(DASHBOARD_TOP_SELL_DATA_LIMIT);
        $topRatedProducts = $this->productRepo->getTopRatedList(
            filters: [
                'user_id' => $vendorId,
                'added_by' => 'seller',
                'request_status' => 1
            ],
            relations: ['reviews'],
        )->take(DASHBOARD_DATA_LIMIT);
        $topRatedDeliveryMan = $this->deliveryManRepo->getTopRatedList(
            orderBy: ['delivered_orders_count' => 'desc'],
            filters: [
                'seller_id' => $vendorId
            ],
            whereHasFilters: [
                'seller_is' => 'seller',
                'seller_id' => $vendorId
            ],
            relations: ['deliveredOrders'],
        )->take(DASHBOARD_DATA_LIMIT);


        // جلب جميع التجار المحالين لهذا التاجر
        $referredVendorIds = Seller::findOrFail($vendorId)->referredVendors()->pluck('sellers.id');


        // التحقق مما إذا كان هناك سجلات في `seller_wallet`
        $total_referral = SellerWallet::whereIn('seller_id', $referredVendorIds)->sum('referral_commission');



        $from = now()->startOfYear()->format('Y-m-d');
        $to = now()->endOfYear()->format('Y-m-d');
        $range = range(1,12);
        $vendorEarning = $this->getVendorEarning(from: $from, to: $to, range: $range, type: 'month');
        $commissionEarn = $this->getAdminCommission(from: $from ,to: $to,range: $range,type:'month');
        $vendorWallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $vendorId]);
        $label = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $dateType = 'yearEarn';
        $dashboardData = [
            'orderStatus' => $this->getOrderStatusArray(type: 'overall'),
                'customers'=> $this->customerRepo->getList(dataLimit: 'all')->count(),
            'products'=> $this->productRepo->getListWhere(filters: ['seller_id'=>$vendorId,'added_by'=>'seller'])->count(),
            'orders'=> $this->orderRepo->getListWhere(filters: ['seller_id'=>$vendorId,'seller_is'=>'seller'])->count(),
            'brands'=> $this->brandRepo->getListWhere(dataLimit: 'all')->count(),
            'topSell' => $topSell,
            'topRatedProducts' => $topRatedProducts,
            'topRatedDeliveryMan' => $topRatedDeliveryMan,
            'total_referral' => $total_referral ?? 0,
            'count_referral' => $referredVendorIds->count() ?? 0,
            'totalEarning' => $vendorWallet->total_earning ?? 0,
            'withdrawn' => $vendorWallet->withdrawn ?? 0,
            'pendingWithdraw' => $vendorWallet->pending_withdraw ?? 0,
            'adminCommission' => $vendorWallet->commission_given ?? 0,
            'deliveryManChargeEarned' => $vendorWallet->delivery_charge_earned ?? 0,
            'collectedCash' => $vendorWallet->collected_cash ?? 0,
            'collectedTotalTax' => $vendorWallet->total_tax_collected ?? 0,
        ];
        $withdrawalMethods = $this->withdrawalMethodRepo->getListWhere(filters:['is_active'=>1],dataLimit:'all');
        return view(Dashboard::INDEX[VIEW],compact('dashboardData','vendorEarning','commissionEarn','withdrawalMethods','dateType','label'));
    }

    /**
     * @param string $type
     * @return JsonResponse
     */
    public function getOrderStatus(string $type):JsonResponse
    {
        $orderStatus = $this->getOrderStatusArray($type);
        return response()->json([
            'view' => view(Dashboard::ORDER_STATUS[VIEW], compact('orderStatus'))->render()
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEarningStatistics(Request $request):JsonResponse
    {
        $dateType = $request['type'];
        $dateTypeArray = $this->dashboardService->getDateTypeData(dateType: $dateType);
        $from = $dateTypeArray['from'];
        $to = $dateTypeArray['to'];
        $type = $dateTypeArray['type'];
        $range = $dateTypeArray['range'];
        $vendorEarning = $this->getVendorEarning(from: $from, to: $to, range: $range, type: $type);
        $commissionEarn = $this->getAdminCommission(from: $from, to: $to, range: $range, type: $type);
        $vendorEarning = array_values($vendorEarning);
        $commissionEarn = array_values($commissionEarn);
        $label = $dateTypeArray['keyRange'] ?? [];
        return response()->json([
            'view' => view(Dashboard::EARNING_STATISTICS[VIEW], compact('vendorEarning','commissionEarn','label','dateType'))->render(),
        ]);
    }

    /**
     * @param WithdrawRequest $request
     * @return RedirectResponse
     */
    public function getWithdrawRequest(WithdrawRequest $request):RedirectResponse
    {
        $vendorId = auth('seller')->id();
        $withdrawMethod = $this->withdrawalMethodRepo->getFirstWhere(params:['id'=>$request['withdraw_method']]);
        $wallet = $this->vendorWalletRepo->getFirstWhere(params:['seller_id'=> auth('seller')->id()]);
        if (($wallet['total_earning']) >= currencyConverter($request['amount']) && $request['amount'] > 1) {
            $this->withdrawRequestRepo->add($this->withdrawRequestService->getWithdrawRequestData(
                withdrawMethod:$withdrawMethod,
                request:$request,
                addedBy: 'vendor',
                vendorId: $vendorId
            ));
            $totalEarning = $wallet['total_earning'] - currencyConverter($request['amount']);
            $pendingWithdraw = $wallet['pending_withdraw'] + currencyConverter($request['amount']);
            $this->vendorWalletRepo->update(
                id:$wallet['id'],
                data: $this->vendorWalletService->getVendorWalletData(totalEarning:$totalEarning,pendingWithdraw:$pendingWithdraw)
            );
            Toastr::success(translate('withdraw_request_has_been_sent'));
        }else{
            Toastr::error(translate('invalid_request').'!');
        }
        return redirect()->back();
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getOrderStatusArray(string $type) :array
    {
        $vendorId = auth('seller')->id();
        $status = OrderStatus::LIST;
        $statusWiseOrders = [];
        foreach ($status as $key) {
            $count = $this->orderRepo->getListWhereDate(
                filters: [
                    'seller_is' => 'seller',
                    'seller_id' => $vendorId,
                    'order_status' => $key
                ],
                dateType: $type == 'overall' ? 'overall' : ($type == 'today' ? 'today' : 'thisMonth'),
            )->count();
            $statusWiseOrders[$key] = $count;
        }
        return $statusWiseOrders;
    }

    /**
     * @param string|Carbon $from
     * @param string|Carbon $to
     * @param array $range
     * @param string $type
     * @return array
     */
    protected function getVendorEarning(string|Carbon $from, string|Carbon $to, array $range, string $type): array
    {
        $vendorId = auth('seller')->id();
        $vendorEarnings = $this->orderTransactionRepo->getListWhereBetween(
            filters: [
                'seller_is' => 'seller',
                'seller_id' => $vendorId,
                'status' => 'disburse',
            ],
            selectColumn: 'seller_amount',
            whereBetween: 'created_at',
            whereBetweenFilters: [$from, $to],
            groupBy:  $type,
        );
        return $this->dashboardService->getDateWiseAmount(range: $range, type: $type, amountArray: $vendorEarnings);
    }

    /**
     * @param string|Carbon $from
     * @param string|Carbon $to
     * @param array $range
     * @param string $type
     * @return array
     */
    protected function getAdminCommission(string|Carbon $from, string|Carbon $to, array $range, string $type ):array
    {;
        $vendorId = auth('seller')->id();
        $commissionGiven = $this->orderTransactionRepo->getListWhereBetween(
            filters:  [
                'seller_is'=>'seller',
                'seller_id'=>$vendorId,
                'status'=>'disburse',
            ],
            selectColumn:  'admin_commission',
            whereBetween: 'created_at',
            whereBetweenFilters: [$from, $to],
            groupBy:  $type,
        );
        return $this->dashboardService->getDateWiseAmount(range: $range,type: $type,amountArray: $commissionGiven);;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getMethodList(Request $request):JsonResponse
    {
        $method = $this->withdrawalMethodRepo->getFirstWhere(params:['id'=> $request['method_id'],'is_active'=>1]);
        return response()->json(['content'=>$method], 200);
    }

    public function signatures(Request $request) {

        $seller=auth("seller")->user();
        if(!getWebConfig('vendors_must_sing_contract') ?? 0)
            return redirect(route("vendor.dashboard.index"));

        return view(Dashboard::SIGNATURES_EMAIL[VIEW],compact("seller"));
    
    }
    public function post_signatures(Request $request) {

        $seller = auth("seller")->user();

       
        $validator = Validator::make($request->all(), [
            'signature' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ], [
            'signature.required' => translate("You_must_enter_your_signature_in_box"),
            'signature.starts_with' => translate("Invalid_signature_format"),
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first('signature'));
            return back()->withErrors($validator)->withInput();
        }
        
        $signatureBase64 = $request->input('signature'); // ✅ استلام التوقيع كنص
        
        $this->signatureRepo->add([
            'seller' => $seller->id,
            'signature_path' => $signatureBase64, // ✅ حفظ المسار فقط
            'contract_path' => 'app/private/contracts/'.$seller->type_account.'/contract_' . $seller->id . '.pdf'
        ]);
        
        // ✅ إذا تم التوقيع بنجاح، حفظ العقد
        $this->ContractsService->SaveContract($seller);
        Seller::where("id", auth("seller")->id())->update(["email" => $request["email"],"signatures"=>true]);
        
        Toastr::success(translate('Signature saved and contract generated successfully'));
        return redirect(route("vendor.dashboard.index"));
        
    }
    public function confirm_email(Request $request)
    {

        
        return view(Dashboard::CONIRM_EMAIL[VIEW]);
        // $this->vendorService->logout();
        // Toastr::success(translate('logged_out_successfully').'.');
        // return redirect()->route('vendor.auth.confirm');
    }

  
    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'], // يجب أن يكون البريد صحيحًا وغير فارغ
        ]);
        $ex = Seller::where("id", "<>", auth("seller")->id())->where("email", $request["email"])->exists();

        if (!$ex) {
            $token = mt_rand(100000, 999999);
            $this->EmailVerification->updateOrCreate(
                ["phone_or_email" => $request["email"]],
                ['token' => $token]
            );

            $data = [
                'subject' => 'Confirm your Email',
                'token' => $token,
            ];
            Mail::to($request["email"])->send(new EmailSellerVerificationMail($data));

            return response()->json([
                'status' => true,
                'message' => 'البريد الإلكتروني ورقم الهاتف متاحان للتسجيل.'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'errors' => [
                    'email' => [translate("This_Email_Is_Exists")]
                ]
            ], 422); // ✅ تعديل كود الخطأ ليكون 422
        }
    }
    
    public function confirmMail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'], // يجب أن يكون البريد صحيحًا وغير فارغ
        ]);
        $_email= $this->EmailVerification->getFirstWhere(["phone_or_email"=>$request["email"]]);
        if($_email->token==$request["token"]){
            Seller::where("id", auth("seller")->id())->update(["email" => $request["email"],"verification"=>true]);
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
