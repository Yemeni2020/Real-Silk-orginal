<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Enums\ViewPaths\Vendor\ReferralVendor;
use App\Enums\ViewPaths\Vendor\Shop;
use App\Http\Requests\Vendor\ShopRequest;
use App\Http\Requests\Vendor\ShopVacationRequest;
use App\Http\Controllers\BaseController;
use App\Services\ShopService;
use App\Services\VendorService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ReferralVendorController extends BaseController
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly ShopService $shopService,
        private readonly VendorService $vendorService,
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
            $referral_code = $this->vendorService->generate_code(auth('seller')->id());

            // التأكد من أن `get_referral_vendor` يعيد Paginator
            $sellers = $this->vendorService->get_referral_vendor(auth('seller')->id(), $request);

            // حساب إجمالي العمولات
            $sum_referral = $sellers->sum("referral_commission");

            return view(ReferralVendor::INDEX[VIEW], compact("referral_code", "sellers", "sum_referral"));
        }

    
}
