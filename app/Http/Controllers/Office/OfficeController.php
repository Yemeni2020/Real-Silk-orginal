<?php

namespace App\Http\Controllers\Office;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Enums\ViewPaths\Office\Office;
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

class OfficeController extends BaseController
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
        
        $referral_code=$this->vendorService->generate_code(auth('seller')->id());
        $shop = $this->shopRepo->getFirstWhere(['seller_id' => auth('seller')->id()]);
        
        return view(Office::INDEX[VIEW], compact('shop'));
     }
     public function update(Request $request): RedirectResponse|null
    {
        // قائمة المدن المسموح بها لكل دولة
        $allowedCities = [
            'china' => [
                "beijing", "shanghai", "guangzhou", "shenzhen", "tianjin", "chongqing", "chengdu", "nanjing", "wuhan",
                "xi'an", "hangzhou", "shenyang", "qingdao", "dalian", "suzhou", "xiamen", "fuzhou", "ningbo", "zhengzhou",
                "changsha", "kunming", "harbin", "jinan", "hefei", "shijiazhuang", "urumqi", "nanchang", "guiyang", "changchun",
                "lanzhou", "haikou", "taiyuan", "nanning"
            ],
            'saudi' => [
                "riyadh", "jeddah", "mecca", "medina", "dammam", "khobar", "jubail", "tabuk", "hail", "al-ahsa",
                "abha", "khamis-mushait", "najran", "yanbu", "qassim", "al-khafji", "sakaka", "buraidah", "jazan",
                "arar", "hafar-al-batin", "al-lith", "rabigh", "al-bahah", "al-qurayyat"
            ]
        ];

        // ✅ **التحقق من أن الدولة والمدنية موجودة في القوائم المسموحة**
        $request->validate([
            'country' => 'required|in:china,saudi',
            'city' => [
                'required',
                function ($attribute, $value, $fail) use ($request, $allowedCities) {
                    $country = $request->input('country');
                    if (!isset($allowedCities[$country]) || !in_array(strtolower($value), $allowedCities[$country])) {
                        $fail(translate("The selected city is invalid."));
                    }
                },
            ],
        ]);
        
        // 🔹 تحديث بيانات المكتب
        $shop = $this->shopRepo->getFirstWhere(['seller_id' => auth('seller')->id()]);
        if ($shop) {
            
            $this->shopRepo->update($shop->id, [
                'country' => $request->input('country'),
                'city' => $request->input('city'),
            ]);

        }

        // 🔹 إرسال إشعار النجاح وإعادة التوجيه
        Toastr::success(translate('Updated_the_office'));
        return back();
    }


    
}
