<?php
namespace App\Http\Controllers\VueApi;

use App\Models\Author;
use App\Models\BusinessSetting;
use App\Models\DigitalProductAuthor;
use App\Models\DigitalProductPublishingHouse;
use App\Models\PublishingHouse;
use App\Utils\BrandManager;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\Shop;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Translation;
use App\Models\Wishlist;
use App\Utils\ProductManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductListController extends Controller
{

    public function getProductCategories(Request $request): JsonResponse
    {
        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting2();


        return response()->json($categories);
    }
}
