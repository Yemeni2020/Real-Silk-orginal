<?php

namespace App\Http\Controllers\VueApi;

use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\ProductCompareRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductTagRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\SellerRepositoryInterface;
use App\Contracts\Repositories\TagRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Models\FormItem;
use App\Models\Product;
use App\Models\ProductTag;
use App\Models\Review;
use App\Models\Seller;
use App\Models\Tag;
use App\Models\Wishlist;
use App\Models\OrderService;
use App\Models\DetailsOrderService;
use App\Repositories\DealOfTheDayRepository;
use App\Repositories\WishlistRepository;
use App\Services\ProductService;
use App\Traits\ProductTrait;
use App\Utils\Helpers;
use App\Utils\ProductManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Mail\SubmitServicetedMail; // تأكد من إنشاء هذا الملف
use Illuminate\Support\Facades\Mail;

class ProductDetailsController extends Controller
{
    use ProductTrait;

    public function getDetails(Request $request,$id){

        $product = Product::select("details","id")->findOrFail($id);
        return response()->json($product?->details);

    }


}
