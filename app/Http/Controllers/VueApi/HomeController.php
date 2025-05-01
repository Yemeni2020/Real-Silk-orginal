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
use App\Models\OptionsShipping;
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
                ->where('category_ids', 'like', "%{$id}%")->select("id","name","discount","discount_type","product_type","slug","current_stock","unit_price","thumbnail","added_by","minimum_order_qty")->limit(12);
            $data['products'] = ProductManager::getPriorityWiseCategoryWiseProductsQuery(query: $homeCategoriesProducts, dataLimit: 25);
        });
        return response()->json($homeCategories);

    }
    public function getMenuData()
    {
        $categories = Category::with(['childes.childes'])
            ->where('position', 0)
            ->get()
            ->map(function ($category) {
                $brandsList = is_array($category->brands)
                    ? $category->brands
                    : json_decode($category->brands, true);

                // نضيف البيانات الجديدة على شكل مصفوفة وليس Object
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image_ad' => $category->image_ad,
                    'adv_full_url' => $category->adv_full_url,
                    'childes' => $category->childes->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'childes' => $child->childes->map(function ($sub) {
                                return [
                                    'id' => $sub->id,
                                    'name' => $sub->name
                                ];
                            })
                        ];
                    }),
                    'brands' => Brand::whereIn('id', $brandsList ?? [])->get()->map(function ($brand) {
                        return [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'image_full_url' => $brand->image_full_url,
                            'image_alt_text' => $brand->image_alt_text
                        ];
                    })
                ];
            });

        return response()->json($categories);
    }

    public function getOptionsShippingMethod($id){
        $OptionsShipping = OptionsShipping::where("shipping_method",$id)->with("options")->get();
        return response()->json($OptionsShipping);

    }
}