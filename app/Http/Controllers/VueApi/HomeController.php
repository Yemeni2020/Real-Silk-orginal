<?php

namespace App\Http\Controllers\VueApi;

use App\Models\Shop;
use App\Traits\EmailTemplateTrait;
use App\Traits\InHouseTrait;
use App\Utils\BrandManager;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DealOfTheDay;
use App\Models\MostDemanded;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Review;
use App\Utils\ProductManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller{

    public function __construct(
        private Product      $product,
        private Order        $order,
        private OrderDetail  $order_details,
        private Category     $category,
        private Seller       $seller,
        private Review       $review,
        private DealOfTheDay $deal_of_the_day,
        private Banner       $banner,
        private MostDemanded $most_demanded,
    )
    {
    }
    public function getHomeCategories(){
        $homeCategories = Category::where('home_status', true)->select("id","name")->limit(12)->priority()->get();
        $homeCategories->map(function ($data) {
            $id = '"' . $data['id'] . '"';
            $homeCategoriesProducts = Product::active()
                ->withCount('reviews')
                ->where('category_ids', 'like', "%{$id}%")->select("id","name","discount","discount_type","product_type","slug","current_stock","unit_price","thumbnail","added_by","min_qty")->limit(12);
            $data['products'] = ProductManager::getPriorityWiseCategoryWiseProductsQuery(query: $homeCategoriesProducts, dataLimit: 25);
        });
        return response()->json($homeCategories);

    }
}