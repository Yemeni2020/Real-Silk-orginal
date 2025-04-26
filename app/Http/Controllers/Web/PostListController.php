<?php

namespace App\Http\Controllers\Web;

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
use App\Models\CategoryPost;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Translation;
use App\Models\Wishlist;
use App\Utils\PostManager;
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
use App\Services\PostService;

class PostListController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function posts(Request $request)
    {
        $themeName = theme_root_path();
        
        return match ($themeName) {
            'default' => self::default_theme($request),
        };
    }

    public function default_theme(Request $request): View|JsonResponse|Redirector|RedirectResponse
    {
        $filters = $request->only('search', 'category_id');
        $posts = $this->postService->listPosts($filters,perPage:1); // 10 بوستات بكل صفحة
        $categories = CategoryPost::all(); // لو عندك تصنيفات جاهزة للعرض

        if(request()->ajax()){
            return response()->json([
                'view' => view('web-views.posts.posts-card', compact('posts'))->render(),
                'total_posts' => count($posts),
            ]);
        }
        
        return view(VIEW_FILE_NAMES['posts_view_page'], compact('posts','categories','filters'));
    }

}
