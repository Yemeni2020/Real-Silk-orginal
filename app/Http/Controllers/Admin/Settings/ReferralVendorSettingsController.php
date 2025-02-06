<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\ReferralVendorSettings;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\BusinessSettingService;
use App\Traits\FileManagerTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class ReferralVendorSettingsController extends Controller
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly BusinessSettingService $businessSettingService,
    )
    {
    }

    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $sellers=Seller::all();
        return view(ReferralVendorSettings::VIEW[VIEW],compact('sellers'));
    }

    public function getVendorsList(Request $request){
        $sellers = Seller::all();
        
        return view(ReferralVendorSettings::List[VIEW], compact('sellers'));
    }

    public function update(Request $request){
        $this->businessSettingRepo->updateOrInsert(type: 'sales_commission_referral', value: $request->get('rate', 0));
        clearWebConfigCacheKeys();
        Toastr::success(translate('successfully_updated'));
        return redirect()->back();

    }

}
