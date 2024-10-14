<?php

namespace App\Http\Controllers\factory;

use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Enums\ViewPaths\factory\Profile;
use App\Http\Controllers\BaseController;
use App\Http\Requests\factory\factoryBankInfoRequest;
use App\Http\Requests\factory\factoryPasswordRequest;
use App\Http\Requests\factory\factoryRequest;
use App\Repositories\factoryRepository;
use App\Services\factoryService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ProfileController extends BaseController
{
    /**
     * @param factoryRepository $factoryRepo
     * @param factoryService $factoryService
     * @param ShopRepositoryInterface $shopRepo
     */
    public function __construct(
        private readonly factoryRepository $factoryRepo,
        private readonly factoryService $factoryService,
        private readonly ShopRepositoryInterface $shopRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return \Illuminate\Contracts\View\View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): \Illuminate\Contracts\View\View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
       return $this->getListView();
    }
    /**
     * @return View
     */
    public function getListView():View
    {
        $factory = $this->factoryRepo->getFirstWhere(['id'=>auth('seller')->id()]);
        return view(Profile::INDEX[VIEW],compact('factory'));
    }

    /**
     * @param string|int $id
     * @return View|RedirectResponse
     */
    public function getUpdateView(string|int $id):View|RedirectResponse
    {
        if (auth('seller')->id() != $id) {
            Toastr::warning(translate('you_can_not_change_others_profile'));
            return redirect()->back();
        }
        $factory = $this->factoryRepo->getFirstWhere(['id'=>auth('seller')->id()]);
        $shopBanner = $this->shopRepo->getFirstWhere(['seller_id'=>auth('seller')->id()])->banner;
        return view(Profile::UPDATE[VIEW],compact('factory','shopBanner'));
    }

    /**
     * @param factoryRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function update(factoryRequest $request, string|int $id):RedirectResponse
    {

        $factory = $this->factoryRepo->getFirstWhere(['id'=>$id]);
        $this->factoryRepo->update(id:$id,data: $this->factoryService->getfactoryDataForUpdate(request:$request,factory:$factory));
        Toastr::success(translate('profile_updated_successfully'));
        return redirect()->back();
    }

    /**
     * @param factoryPasswordRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function updatePassword(factoryPasswordRequest $request , string|int $id):RedirectResponse
    {
        $this->factoryRepo->update(id:$id,data:$this->factoryService->getfactoryPasswordData(request:$request));
        Toastr::success(translate('password_updated_successfully'));
        return redirect()->back();
    }

    /**
     * @param string|int $id
     * @return View|RedirectResponse
     */
    public function getBankInfoUpdateView(string|int $id):View|RedirectResponse
    {
        $factoryId = auth('seller')->id();
        if ($factoryId != $id) {
            Toastr::warning(translate('you_can_not_change_others_info'));
            return redirect()->back();
        }
        $factory = $this->factoryRepo->getFirstWhere(['id' => $factoryId]);
        return view(Profile::BANK_INFO_UPDATE[VIEW],compact('factory'));
    }

    /**
     * @param factoryBankInfoRequest $request
     * @param string|int $id
     * @return RedirectResponse
     */
    public function updateBankInfo(factoryBankInfoRequest $request , string|int $id):RedirectResponse
    {
        $factory = $this->factoryRepo->getFirstWhere(['id' => $id]);
        $this->factoryRepo->update(id: $factory['id'], data: $this->factoryService->getfactoryBankInfoData(request: $request));
        Toastr::success(translate('successfully_updated').'!!');
        return redirect()->route(Profile::INDEX[ROUTE]);
    }


}
