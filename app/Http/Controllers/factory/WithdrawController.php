<?php

namespace App\Http\Controllers\factory;

use App\Contracts\Repositories\factoryWalletRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Enums\ViewPaths\factory\Withdraw;
use App\Http\Controllers\BaseController;
use App\Services\factoryWalletService;
use Illuminate\Database\Eloquent\Collection;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class WithdrawController extends BaseController
{
    /**
     * @param WithdrawRequestRepositoryInterface $withdrawRequestRepo
     * @param factoryWalletRepositoryInterface $factoryWalletRepo
     * @param factoryWalletService $factoryWalletService
     */
    public function __construct(
        private readonly WithdrawRequestRepositoryInterface $withdrawRequestRepo,
        private readonly factoryWalletRepositoryInterface $factoryWalletRepo,
        private readonly factoryWalletService $factoryWalletService,
    )
    {

    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable
    {
        return $this->getListView();
    }

    /**
     * @return View
     */
    public function getListView(): View
    {
        $factoryId = auth('seller')->id();
        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            filters: ['factoryId' => $factoryId],
            relations: ['seller'],
            dataLimit: getWebConfig('pagination_limit')
        );
        return view(Withdraw::INDEX[VIEW], compact('withdrawRequests'));
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getListByStatus(Request $request):JsonResponse
    {
        $factoryId = auth('seller')->id();
        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            filters: [
                'factoryId' => $factoryId,
                'status' => $request['status']
            ],
            relations: ['seller'],
            dataLimit: getWebConfig('pagination_limit')
        );
        return response()->json([
            'view' => view(Withdraw::INDEX[TABLE_VIEW], compact('withdrawRequests'))->render(),
            'count' => $withdrawRequests->count(),
        ], 200);
    }

    /**
     * @param string|int $id
     * @return RedirectResponse
     */
    public function closeWithdrawRequest(string|int $id):RedirectResponse
    {
        $withdrawRequest = $this->withdrawRequestRepo->getFirstWhere(params: ['id' => $id]);
        $wallet = $this->factoryWalletRepo->getFirstWhere(params: ['seller_id' => auth('seller')->id()]);
        if ($withdrawRequest['approved'] == 0) {
            $totalEarning = $wallet['total_earning'] + currencyConverter($withdrawRequest['amount']);
            $pendingWithdraw = $wallet['pending_withdraw'] - currencyConverter($withdrawRequest['amount']);
            $this->factoryWalletRepo->update(
                id: $wallet['id'],
                data: $this->factoryWalletService->getfactoryWalletData(
                    totalEarning: $totalEarning,
                    pendingWithdraw: $pendingWithdraw
                )
            );
            $this->withdrawRequestRepo->delete(['id' => $withdrawRequest['id']]);
            Toastr::success(message: translate('request_closed') . '!');
        } else {
            Toastr::error(message: translate('invalid_request'));
        }
        return redirect()->back();
    }

}
