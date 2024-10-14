<?php

namespace App\Http\Controllers\factory;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\factoryWalletRepositoryInterface;
use App\Contracts\Repositories\WithdrawalMethodRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Enums\OrderStatus;
use App\Enums\ViewPaths\factory\Dashboard;
use App\Http\Controllers\BaseController;
use App\Http\Requests\factory\WithdrawRequest;
use App\Repositories\BrandRepository;
use App\Repositories\OrderTransactionRepository;
use App\Services\factoryWalletService;
use App\Services\WithdrawRequestService;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends BaseController
{
    public function __construct(
        private readonly OrderTransactionRepository $orderTransactionRepo,
        private readonly ProductRepositoryInterface $productRepo,
        private readonly DeliveryManRepositoryInterface $deliveryManRepo,
        private readonly OrderRepositoryInterface $orderRepo,
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly BrandRepository $brandRepo,
        private readonly factoryWalletRepositoryInterface $factoryWalletRepo,
        private readonly factoryWalletService $factoryWalletService,
        private readonly WithdrawalMethodRepositoryInterface $withdrawalMethodRepo,
        private readonly WithdrawRequestRepositoryInterface $withdrawRequestRepo,
        private readonly WithdrawRequestService $withdrawRequestService,
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
    public function getView():View
    {
        $factoryId = auth('seller')->id();
        $topSell = $this->productRepo->getTopSellList(
            filters:[
                'added_by'=>'seller',
                'seller_id'=>$factoryId,
                'request_status' =>1
            ],
            relations: ['orderDetails']
        )->take(DASHBOARD_DATA_LIMIT);
        $topRatedProducts = $this->productRepo->getTopRatedList(
            filters:[
                'user_id'=>$factoryId,
                'added_by'=>'seller',
                'request_status' =>1
            ],
            relations: ['reviews'],
        )->take(DASHBOARD_DATA_LIMIT);
        $topRatedDeliveryMan = $this->deliveryManRepo->getTopRatedList(
            filters: [
                'seller_id'=>$factoryId
            ],
            whereHasFilters:[
                'seller_is'=>'seller',
                'seller_id'=>$factoryId
            ],
            relations: ['orders'],
        )->take(DASHBOARD_DATA_LIMIT);

        $from = now()->startOfYear()->format('Y-m-d');
        $to = now()->endOfYear()->format('Y-m-d');
        $range = range(1,12);
        $factoryEarningArray = $this->getfactoryEarning(from:$from ,to: $to,range: $range,type:'month');
        $commissionGivenToAdminArray = $this->getAdminCommission(from: $from ,to: $to,range: $range,type:'month');
        $factoryWallet = $this->factoryWalletRepo->getFirstWhere(params: ['seller_id'=>$factoryId]);
        $dashboardData = [
            'orderStatus' => $this->getOrderStatusArray(type: 'overall'),
            'customers'=> $this->customerRepo->getList()->count(),
            'products'=> $this->productRepo->getListWhere(filters: ['seller_id'=>$factoryId,'added_by'=>'seller'])->count(),
            'orders'=> $this->orderRepo->getListWhere(filters: ['seller_id'=>$factoryId,'seller_is'=>'seller'])->count(),
            'brands'=> $this->brandRepo->getListWhere(dataLimit: 'all')->count(),
            'topSell' => $topSell,
            'topRatedProducts' => $topRatedProducts,
            'topRatedDeliveryMan' => $topRatedDeliveryMan,
            'totalEarning' => $factoryWallet->total_earning ?? 0,
            'withdrawn' => $factoryWallet->withdrawn ?? 0,
            'pendingWithdraw' => $factoryWallet->pending_withdraw ?? 0,
            'adminCommission' => $factoryWallet->commission_given ?? 0,
            'deliveryManChargeEarned' => $factoryWallet->delivery_charge_earned ?? 0,
            'collectedCash' => $factoryWallet->collected_cash ?? 0,
            'collectedTotalTax' => $factoryWallet->total_tax_collected ?? 0,
        ];

        $withdrawalMethods = $this->withdrawalMethodRepo->getListWhere(filters:['is_active'=>1],dataLimit:'all');
        return view(Dashboard::INDEX[VIEW],compact('dashboardData','factoryEarningArray','commissionGivenToAdminArray','withdrawalMethods'));
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
        $from = null; $to = null; $type = null; $range = null;
        if ($dateType == 'yearEarn') {
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');
            $range = range(1, 12);
            $type = 'month';
            $keyRange = ["Jan", "Feb", "Mar", "April", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        } elseif ($dateType == 'MonthEarn') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $endRange = date('d', strtotime($to));
            $range = range(1, $endRange);
            $type = 'day';
            $keyRange = $range;
        } elseif ($dateType == 'WeekEarn') {
            $from = Carbon::now()->startOfWeek()->format('Y-m-d');
            $to = Carbon::now()->endOfWeek()->format('Y-m-d');
            $startRange = date('d', strtotime($from));
            $endRange = date('d', strtotime($to));
            $range = range($startRange, $endRange);
            $type = 'day';
            $keyRange = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        }

        $factoryEarningArray = $this->getfactoryEarning(from: $from, to: $to, range: $range, type: $type);
        $commissionGivenToAdminArray = $this->getAdminCommission(from: $from, to: $to, range: $range, type: $type);
        $dashboardData = [
            'label' => $keyRange ?? [],
            'factoryEarningArray' => array_values($factoryEarningArray),
            'commissionGivenToAdminArray' => array_values($commissionGivenToAdminArray),
        ];
        return response()->json($dashboardData);
    }

    /**
     * @param WithdrawRequest $request
     * @return RedirectResponse
     */
    public function getWithdrawRequest(WithdrawRequest $request):RedirectResponse
    {
        $factoryId = auth('seller')->id();
        $withdrawMethod = $this->withdrawalMethodRepo->getFirstWhere(params:['id'=>$request['withdraw_method']]);
        $wallet = $this->factoryWalletRepo->getFirstWhere(params:['seller_id'=> auth('seller')->id()]);
        if (($wallet['total_earning']) >= currencyConverter($request['amount']) && $request['amount'] > 1) {
            $this->withdrawRequestRepo->add($this->withdrawRequestService->getWithdrawRequestData(
                withdrawMethod:$withdrawMethod,
                request:$request,
                addedBy: 'factory',
                factoryId: $factoryId
            ));
            $totalEarning = $wallet['total_earning'] - currencyConverter($request['amount']);
            $pendingWithdraw = $wallet['pending_withdraw'] + currencyConverter($request['amount']);
            $this->factoryWalletRepo->update(
                id:$wallet['id'],
                data: $this->factoryWalletService->getfactoryWalletData(totalEarning:$totalEarning,pendingWithdraw:$pendingWithdraw)
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
        $factoryId = auth('seller')->id();
        $status = OrderStatus::LIST;
        $statusWiseOrders = [];
        foreach ($status as $key) {
            $count = $this->orderRepo->getListWhereDate(
                filters: [
                    'seller_is' => 'seller',
                    'seller_id' => $factoryId,
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
    protected function getfactoryEarning(string|Carbon $from, string|Carbon $to, array $range, string $type):array
    {
        $factoryId = auth('seller')->id();
        $factoryEarnings = $this->orderTransactionRepo->getListWhereBetween(
            filters:  [
                'seller_is'=>'seller',
                'seller_id'=>$factoryId,
                'status'=>'disburse'
            ],
            selectColumn:  'seller_amount',
            whereBetween: 'created_at',
            whereBetweenFilters: [$from, $to],
        );
        $factoryEarningArray = [];
        foreach ($range as $value){
            $factoryEarnings->map(function ($earning)use($type,$range,&$factoryEarningArray,$value){
                $factoryEarningArray[$value] = $earning[$type]== $value? $earning['sums'] : 0;
            });
        }
        return $factoryEarningArray;
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
        $factoryId = auth('seller')->id();
        $commissionGiven = $this->orderTransactionRepo->getListWhereBetween(
            filters:  [
                'seller_is'=>'seller',
                'seller_id'=>$factoryId,
                'status'=>'disburse'
            ],
            selectColumn:  'admin_commission',
            whereBetween: 'created_at',
            whereBetweenFilters: [$from, $to],
        );
        $commissionGivenToAdminArray = [];
        foreach ($range as $value){
            $commissionGiven->map(function ($earning)use($type,$range,&$commissionGivenToAdminArray,$value){
                $commissionGivenToAdminArray[$value] = $earning[$type]== $value? $earning['sums'] : 0;
            });
        }
        return $commissionGivenToAdminArray;
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
}
