<?php

namespace App\Http\Controllers\factory;

use App\Contracts\Repositories\factoryRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Enums\ViewPaths\factory\Shop;
use App\Http\Requests\factory\ShopRequest;
use App\Http\Requests\factory\ShopVacationRequest;
use App\Http\Controllers\BaseController;
use App\Services\ShopService;
use App\Services\factoryService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopController extends BaseController
{
    public function __construct(
        private readonly factoryRepositoryInterface $factoryRepo,
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly ShopService $shopService,
        private readonly factoryService $factoryService,
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
        return $this->getView(request:$request , type:$type);
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|null
     */
    public function getView(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable
    {
        $shop = $this->shopRepo->getFirstWhere(['seller_id' => auth('seller')->id()]);
        $factory = $this->factoryRepo->getFirstWhere(params: ['id' => auth('seller')->id()]);
        if (!isset($shop)) {
            $this->shopRepo->add($this->shopService->getShopDataForAdd(factory: $factory));
            $shop = $this->shopRepo->getFirstWhere(['seller_id' => auth('seller')->id()]);
        }

        $minimumOrderAmountStatus = getWebConfig(name: 'minimum_order_amount_status');
        $minimumOrderAmountByfactory = getWebConfig(name: 'minimum_order_amount_by_seller');
        $freeDeliveryStatus = getWebConfig(name: 'free_delivery_status');
        $freeDeliveryResponsibility = getWebConfig(name: 'free_delivery_responsibility');
        if ($request['pagetype'] == 'order_settings' && (($minimumOrderAmountStatus && $minimumOrderAmountByfactory) || ($freeDeliveryStatus && $freeDeliveryResponsibility == 'seller'))) {
            return view(Shop::ORDER_SETTINGS[VIEW], compact('factory', 'minimumOrderAmountStatus', 'minimumOrderAmountByfactory', 'freeDeliveryStatus', 'freeDeliveryResponsibility'));
        }
        return view(Shop::INDEX[VIEW], compact('shop','minimumOrderAmountStatus','minimumOrderAmountByfactory','freeDeliveryStatus','freeDeliveryResponsibility'));
     }

    /**
     * @param string|int $id
     * @return View
     */
    public function getUpdateView(string|int $id):View
     {
         $shop = $this->shopRepo->getFirstWhere(['id' => $id]);
         return view(Shop::UPDATE[VIEW],compact('shop'));
     }

    /**
     * @param ShopRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function update(ShopRequest $request, string|int $id):RedirectResponse
     {
         $shop = $this->shopRepo->getFirstWhere(['id' => $id]);
         $this->shopRepo->update(id: $id, data: $this->shopService->getShopDataForUpdate(request: $request, shop: $shop));
         Toastr::info(translate('Shop_updated_successfully'));
         return redirect()->route(Shop::INDEX[ROUTE]);
     }

    /**
     * @param ShopVacationRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function updateVacation(ShopVacationRequest $request , string|int $id):RedirectResponse
     {
         $this->shopRepo->update(id: $id, data: $this->shopService->getVacationData(request: $request));
         Toastr::success(translate('Vacation_mode_updated_successfully'));
         return redirect()->back();
     }

    /**
     * @param Request $request
     * @param string|int $id
     * @return JsonResponse
     */
    public function closeShopTemporary(Request $request, string|int $id):JsonResponse
     {
         $this->shopRepo->update(id: $id, data: ['temporary_close' => $request->get(key: 'status', default: 0)]);
         return response()->json([
             'status' => true,
             'message' => $request['status'] ? translate("temporary_close_active_successfully") : translate("temporary_close_inactive_successfully"),
         ], status:200);
     }

    /**
     * @param Request $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function updateOrderSettings(Request $request, string|int $id):RedirectResponse
     {
         if ($request->has('minimum_order_amount')) {
             $this->factoryRepo->update(id: $id, data: $this->factoryService->getMinimumOrderAmount(request: $request));
         }
         if ($request->has('free_delivery_over_amount')) {
             $this->factoryRepo->update(
                 id: $id,
                 data: $this->factoryService->getFreeDeliveryOverAmountData(
                     request: $request
                 )
             );
         }
         return redirect()->back();
     }
}
