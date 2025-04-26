<?php

namespace App\Http\Controllers\Admin\Post;

use App\Contracts\Repositories\AttributeRepositoryInterface;
use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\CategoryPostRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\DealOfTheDayRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use App\Contracts\Repositories\FlashDealProductRepositoryInterface;
use App\Contracts\Repositories\PostRepositoryInterface;
use App\Contracts\Repositories\PostSeoRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Enums\ViewPaths\Admin\Post;
use App\Enums\WebConfigKey;
use App\Events\ProductRequestStatusUpdateEvent;
use App\Exports\ProductListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ProductDenyRequest;
use App\Http\Requests\PostAddRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Services\PostService;
use App\Services\OpenAIService;
use App\Services\DeepSeekAIService;
use App\Traits\FileManagerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\ProductOffer;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\error;

class PostController extends BaseController
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly AuthorRepositoryInterface                  $authorRepo,
        private readonly DigitalProductAuthorRepositoryInterface    $digitalProductAuthorRepo,
        private readonly DigitalProductPublishingHouseRepository    $digitalProductPublishingHouseRepo,
        private readonly PublishingHouseRepositoryInterface         $publishingHouseRepo,
        private readonly CategoryPostRepositoryInterface                $categoryRepo,
        private readonly BrandRepositoryInterface                   $brandRepo,
        private readonly PostRepositoryInterface                 $postRepo,
        private readonly DigitalProductVariationRepositoryInterface $digitalProductVariationRepo,
        private readonly PostSeoRepositoryInterface              $postSeoRepo,
        private readonly VendorRepositoryInterface                  $sellerRepo,
        private readonly ColorRepositoryInterface                   $colorRepo,
        private readonly AttributeRepositoryInterface               $attributeRepo,
        private readonly TranslationRepositoryInterface             $translationRepo,
        private readonly CartRepositoryInterface                    $cartRepo,
        private readonly WishlistRepositoryInterface                $wishlistRepo,
        private readonly FlashDealProductRepositoryInterface        $flashDealProductRepo,
        private readonly DealOfTheDayRepositoryInterface            $dealOfTheDayRepo,
        private readonly ReviewRepositoryInterface                  $reviewRepo,
        private readonly BannerRepositoryInterface                  $bannerRepo,
        private readonly PostService                                $postService,
        private readonly OpenAIService                              $OpenAIService,
        private readonly DeepSeekAIService                          $DeepSeekAIService,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View|null
    {
        return $this->getListView(request: $request, type: ($type == 'vendor' ? 'seller' : 'in_house'));
    }

    public function getAddView(): View
    {

        $categories = $this->categoryRepo->getListWhere( dataLimit: 'all');

        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
     
        return view(Post::ADD[VIEW], compact( 'languages', 'defaultLanguage','categories'));
    }

    public function add(PostAddRequest $request, PostService $service): JsonResponse|RedirectResponse|null
    {
        
        // dump($request);
        // $service->BuildForm($request,33);

        // return null;
        if ($request->ajax()) {
            return response()->json([], 200);
        }

        // echo $request->description[0];
       
        // echo count($request["item"]["Name"]);
        // dump($request->product_type);
        $dataArray = $service->getAddProductData(request: $request, addedBy: 'admin');


        // dump($request);
        // return null;
        
        $savedProduct = $this->postRepo->add(data: $dataArray);

        $this->translationRepo->add(request: $request, model: 'App\Models\Post', id: $savedProduct->id);
        
        $this->postSeoRepo->add(data: $service->getProductSEOData(request: $request, post: $savedProduct, action: 'add'));

        Toastr::success(translate('product_added_successfully'));
        return redirect()->route('admin.post.list', ['in_house']);
    }

    public function updateProductAuthorAndPublishingHouse(object|array $request, object|array $product): void
    {
        if ($request['product_type'] == 'digital') {
            if ($request->has('authors')) {
                $authorIds = [];
                foreach ($request['authors'] as $author) {
                    $authorId = $this->authorRepo->updateOrCreate(params: ['name' => $author], value: ['name' => $author]);
                    $authorIds[] = $authorId?->id;
                }

                foreach ($authorIds as $author) {
                    $productAuthorData = ['author_id' => $author, 'product_id' => $product->id];
                    $this->digitalProductAuthorRepo->updateOrCreate(params: $productAuthorData, value: $productAuthorData);
                }

                $this->digitalProductAuthorRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['author_id' => $authorIds]);
            } else {
                $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            }

            if ($request->has('publishing_house')) {
                $publishingHouseIds = [];
                foreach ($request['publishing_house'] as $publishingHouse) {
                    $publishingHouseId = $this->publishingHouseRepo->updateOrCreate(params: ['name' => $publishingHouse], value: ['name' => $publishingHouse]);
                    $publishingHouseIds[] = $publishingHouseId?->id;
                }

                foreach ($publishingHouseIds as $publishingHouse) {
                    $publishingHouseData = ['publishing_house_id' => $publishingHouse, 'product_id' => $product->id];
                    $this->digitalProductPublishingHouseRepo->updateOrCreate(params: $publishingHouseData, value: $publishingHouseData);
                }
                $this->digitalProductPublishingHouseRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['publishing_house_id' => $publishingHouseIds]);
            } else {
                $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
            }
        } else {
            $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
        }
    }

    public function getListView(Request $request, string $type): View|null
    {
        $filters = [
            'category_id' => $request['category_id'],
        ];

        if($request['status']=="3"){
            $filters['request_status'] = 1;
            $filters['status'] = 1;
        }
        elseif($request['status']=="4"){
            $filters['request_status'] = 1;
            $filters['status'] = 0;
        }else{
            $filters['request_status'] = $request['status'];
        }
        $posts = $this->postRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: $filters, dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        // dump($posts);
        // return null;
        $categories = $this->categoryRepo->getListWhere(dataLimit: 'all');
        return view(Post::LIST[VIEW], compact('posts', 
            'categories', 'filters', 'type'));
    }

    public function getUpdateView(string|int $id): View|RedirectResponse
    {
        $post = $this->postRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id], relations: ['translations', 'seoInfo']);
        if (!$post) {
            Toastr::error(translate('product_not_found') . '!');
            return redirect()->route('admin.products.list', ['in_house']);
        }

        $categories = $this->categoryRepo->getListWhere( dataLimit: 'all');
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];

        $failds=null;
        if($post->product_type=="Service"){
            $failds=$this->postService->getfaildservice($post->id);
        }

        return view(Post::UPDATE[VIEW], compact('post', 'categories', 'languages', 'defaultLanguage'));
    }

    public function update(PostUpdateRequest $request, PostService $service, string|int $id): JsonResponse|RedirectResponse|null
    {
        // dump($request);
        // $service->UpdateForm($request,$id);

        if ($request->ajax()) {
            return response()->json([], 200);
        }

        
        $post = $this->postRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id], relations: [ 'seoInfo']);
        $dataArray = $service->getUpdateProductData(request: $request, product: $post, updateBy: 'admin');

        // dump($dataArray);
        // return null;
        $this->postRepo->update(id: $id, data: $dataArray);
        $this->translationRepo->update(request: $request, model: 'App\Models\Post', id: $id);
        
        
        $seodata=$service->getProductSEOData(request: $request, post: $post, action: 'update');

        // dump($seodata);
        // return null;
        $this->postSeoRepo->updateOrInsert(
            params: ['post_id' => $post['id']],
            data: $seodata
        );

        Toastr::success(translate('post_updated_successfully'));
        return redirect()->route(Post::VIEW[ROUTE], ['addedBy' => 'admin', 'id' => $post['id']]);
    }


    public function getView(string $addedBy, string|int $id): View|RedirectResponse
    {
        $productActive = $this->postRepo->getFirstWhere(params: ['id' => $id], relations: [ 'seoInfo']);

        

        $relations = ['category', 'seoInfo'];
        $product = $this->postRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id], relations: $relations);

        $reviews = $this->reviewRepo->getListWhere(filters: ['product_id' => ['product_id' => $id], 'whereNull' => ['column' => 'delivery_man_id']], relations: ['customer', 'reply'], dataLimit: getWebConfig(name: 'pagination_limit'));
        return view(Post::VIEW[VIEW], compact('product'));
    }



    public function deleteImage(Request $request, PostService $service): RedirectResponse
    {
        $this->deleteFile(filePath: '/post/' . $request['image']);
        $product = $this->postRepo->getFirstWhere(params: ['id' => $request['id']]);

        if (count(json_decode($product['images'])) < 2) {
            Toastr::warning(translate('you_can_not_delete_all_images'));
            return back();
        }

        $imageProcessing = $service->deleteImage(request: $request, product: $product);

        $updateData = [
            'images' => json_encode($imageProcessing['images']),
            'color_image' => json_encode($imageProcessing['color_images']),
        ];
        $this->postRepo->update(id: $request['id'], data: $updateData);

        Toastr::success(translate('post_image_removed_successfully'));
        return back();
    }

    public function getCategories(Request $request, PostService $service): JsonResponse
    {
        $parentId = $request['parent_id'];
        $filter = ['parent_id' => $parentId];
        $categories = $this->categoryRepo->getListWhere(filters: $filter, dataLimit: 'all');
        $dropdown = $service->getCategoryDropdown(request: $request, categories: $categories);

        $childCategories = '';
        if (count($categories) == 1) {
            $subCategories = $this->categoryRepo->getListWhere(filters: ['parent_id' => $categories[0]['id']], dataLimit: 'all');
            $childCategories = $service->getCategoryDropdown(request: $request, categories: $subCategories);
        }

        return response()->json([
            'select_tag' => $dropdown,
            'sub_categories' => count($categories) == 1 ? $childCategories : '',
        ]);
    }




    public function delete(string|int $id, PostService $service): RedirectResponse
    {
        $product = $this->postRepo->getFirstWhere(params: ['id' => $id]);

        if ($product) {
            $this->translationRepo->delete(model: 'App\Models\Post', id: $id);
            $service->deleteImages(product: $product);
            $this->postRepo->delete(params: ['id' => $id]);

            Toastr::success(translate('product_removed_successfully'));
        } else {
            Toastr::error(translate('invalid_product'));
        }

        return back();
    }








    public function approveStatus(Request $request): JsonResponse
    {
        $product = $this->postRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $request['id']]);
        $dataArray = [
            'request_status' => ($product['request_status'] == 0) ? 1 : 0
        ];
        $this->postRepo->update(id: $request['id'], data: $dataArray);
        $vendor = $this->sellerRepo->getFirstWhere(params: ['id' => $product['user_id']]);
        if ($vendor['cm_firebase_token']) {
            ProductRequestStatusUpdateEvent::dispatch('product_request_approved_message', 'seller', $vendor['app_language'] ?? getDefaultLanguage(), $vendor['cm_firebase_token']);
        }
        return response()->json(['message' => translate('product_request_approved') . '.']);
    }

    public function getSearchedProductsView(Request $request): JsonResponse
    {
        $searchValue = $request['searchValue'] ?? null;
        $products = $this->postRepo->getListWhere(
            searchValue: $searchValue,
            filters: [
                'added_by' => 'in_house',
                'status' => 1,
                'category_id' => $request['category_id'],
                'code' => $request['name'],
            ],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        return response()->json([
            'count' => $products->count(),
            'result' => view(Product::SEARCH[VIEW], compact('products'))->render(),
        ]);
    }

    public function getProductGalleryView(Request $request): View
    {
        $searchValue = $request['searchValue'];
        $filters = [
            'added_by' => $request['vendor_id'] == 'in_house' ? 'in_house' : '',
            'searchValue' => $searchValue,
            'request_status' => 1,
            'product_search_type' => 'product_gallery',
            'seller_id' => $request['vendor_id'] == 'in_house' ? '' : $request['vendor_id'],
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
        ];
        $products = $this->postRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: $filters, dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        $products->map(function ($product) {
            if ($product->product_type == 'physical' && count(json_decode($product->choice_options)) > 0 || count(json_decode($product->colors)) > 0) {
                $colorName = [];
                $colorsCollection = collect(json_decode($product->colors));
                $colorsCollection->map(function ($color) use (&$colorName) {
                    $colorName[] = $this->colorRepo->getFirstWhere(['code' => $color])->name;
                });
                $product['colorsName'] = $colorName;
            }
        });
        $vendors = $this->sellerRepo->getListWhere(filters: ['status' => 'approved'], relations: ['shop'], dataLimit: 'all');
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        return view(Product::PRODUCT_GALLERY[VIEW], compact('products', 'vendors', 'brands', 'categories', 'searchValue'));
    }

    public function getStockLimitStatus(Request $request, string $type): JsonResponse
    {
        $filters = [
            'added_by' => $type,
            'product_type' => 'physical',
            'request_status' => $request['status'],
        ];
        $products = $this->postRepo->getStockLimitListWhere(filters: $filters, dataLimit: 'all');
        if ($products->count() == 1) {
            $product = $products->first();
            $thumbnail = getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product');
            return response()->json(['status' => 'one_product', 'product_count' => 1, 'product' => $product, 'thumbnail' => $thumbnail]);
        } else {
            return response()->json(['status' => 'multiple_product', 'product_count' => $products->count()]);
        }

    }

    public function getMultipleProductDetailsView(Request $request): JsonResponse
    {
        $selectedProducts = $this->postRepo->getListWhere(
            filters: [
                'productIds' => $request['productIds'],
            ],
            dataLimit: 'all'
        );
        return response()->json([
            'result' => view(Product::MULTIPLE_PRODUCT_DETAILS[VIEW], compact('selectedProducts'))->render(),
        ]);
    }

    public function deletePreviewFile(Request $request): JsonResponse
    {
        $product = $this->postRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $request['product_id']]);
        $this->postService->deletePreviewFile(product: $product);
        $this->postRepo->update(id: $request['product_id'], data: ['preview_file' => null]);
        return response()->json([
            'status' => 1,
            'message' => translate('Preview_file_deleted')
        ]);
    }


    public function translate_ai(Request $request)
    {
        // البيانات المرسلة عبر Ajax
        $productName = $request->input('product_name');
        $translate_ai = $request->input('translate_ai');
        $targetLanguage = $request->input('target_language', 'en'); // اللغة الافتراضية هي الإنجليزية

        $targetLanguage=match ($targetLanguage) {
            "sa" => "ar",
            "cn" => "zh",
            default => $targetLanguage,
        };
        // مفتاح API الخاص بك
        $apiKey = 'sk-5cfe868f367b47ae8c732cddb3a7d497';

        // 1. ترجمة اسم المنتج
        // $translatedName = $this->OpenAIService->translateText($productName, $targetLanguage,"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");
        // $translatedName=$this->translateText($productName, $targetLanguage);
        // if($request->has("description")){
        //     $translatedDescription=$this->postService->translate($translate_ai,$request->input('description'), $targetLanguage);

        // }else{
            $translatedDescription='';
        // }

        if(isset($productName)){
            $translatedName=$this->postService->translate($translate_ai,$productName, $targetLanguage);

        }else{
            $translatedName='';
        }
        // 2. إنشاء وصف للمنتج
        // $description = $this->OpenAIService->generateDescription($translatedName, "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");
        // $description = $this->generateDescription($translatedName, $apiKey);

        // إرجاع النتيجة كـ JSON
        if(isset($translatedName["error"])){
            return response()->json(["msg" => $translatedName["error"]." : ".$translate_ai], 400); // 400 لتحديد أنه خطأ
        }else{

            return response()->json([
                'productName' => $translatedName,
                'description' => $translatedDescription,
                'targetLanguage' => $targetLanguage,
            ]);
        }
    }


   

    
    
}
