<?php

namespace App\Http\Controllers\Office\Service;

use App\Contracts\Repositories\AttributeRepositoryInterface;
use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\DealOfTheDayRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use App\Contracts\Repositories\FlashDealProductRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductSeoRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Enums\ViewPaths\Office\Service;
use App\Enums\WebConfigKey;
use App\Exports\ProductListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductAddRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Repositories\TranslationRepository;
use App\Services\ProductService;
use App\Traits\FileManagerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\OfficeService;
use App\Models\Product;

class ServiceController extends BaseController
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly AuthorRepositoryInterface                  $authorRepo,
        private readonly PublishingHouseRepositoryInterface         $publishingHouseRepo,
        private readonly DigitalProductAuthorRepositoryInterface    $digitalProductAuthorRepo,
        private readonly DigitalProductPublishingHouseRepository    $digitalProductPublishingHouseRepo,
        private readonly CategoryRepositoryInterface                $categoryRepo,
        private readonly BrandRepositoryInterface                   $brandRepo,
        private readonly ProductRepositoryInterface                 $productRepo,
        private readonly DigitalProductVariationRepositoryInterface $digitalProductVariationRepo,
        private readonly ProductSeoRepositoryInterface              $productSeoRepo,
        private readonly TranslationRepository                      $translationRepo,
        private readonly BusinessSettingRepositoryInterface         $businessSettingRepo,
        private readonly ColorRepositoryInterface                   $colorRepo,
        private readonly AttributeRepositoryInterface               $attributeRepo,
        private readonly ReviewRepositoryInterface                  $reviewRepo,
        private readonly CartRepositoryInterface                    $cartRepo,
        private readonly WishlistRepositoryInterface                $wishlistRepo,
        private readonly FlashDealProductRepositoryInterface        $flashDealProductRepo,
        private readonly DealOfTheDayRepositoryInterface            $dealOfTheDayRepo,
        private readonly VendorRepositoryInterface                  $vendorRepo,
        private readonly ProductService                             $productService,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|array|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     * Index function is the starting point of a controller
     */
    public function index(?Request $request, string|array $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {

        return $this->getListView(request: $request, type: $type);
    }

    public function getListView(Request $request, $type): View
    {
        $vendorId = auth('seller')->id();

        // جلب المنتجات التي من نوع "Service" مع علاقتها بالمكاتب
        $products = Product::with(['translations', 'offices'])->where('product_type', 'Service')->get();

        // جلب الخدمات التي تم تسجيلها مسبقًا للمكتب الحالي
        $registeredServices = OfficeService::where('office', $vendorId)->pluck('service')->toArray();

        return view(Service::INDEX[VIEW], compact('products', 'registeredServices'));
    }

    public function store(Request $request)
    {
        $officeId = auth('seller')->id(); // الحصول على معرف المكتب
        $selectedProducts = $request->input('products', []); // قائمة الخدمات المحددة، افتراضيًا فارغة

        // جلب جميع الخدمات الحالية لهذا المكتب
        $existingServices = OfficeService::where('office', $officeId)->pluck('service')->toArray();

        // **الخطوة 1: حذف الخدمات التي لم يتم تحديدها**
        $servicesToDelete = array_diff($existingServices, $selectedProducts);
        if (!empty($servicesToDelete)) {
            OfficeService::where('office', $officeId)
                ->whereIn('service', $servicesToDelete)
                ->delete();
        }

        // **الخطوة 2: إضافة الخدمات الجديدة فقط**
        $servicesToAdd = array_diff($selectedProducts, $existingServices);
        $insertData = [];
        foreach ($servicesToAdd as $productId) {
            $insertData[] = [
                'office' => $officeId,
                'service' => $productId,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            OfficeService::insert($insertData);
        }

        Toastr::success(translate('Saved_Your_Service'));
        return redirect()->back()->with('success', translate('Saved_Your_Service'));
    }

   
}
