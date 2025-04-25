<?php

namespace App\Http\Controllers\Admin\Post;

use App\Contracts\Repositories\CategoryPostRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Category as CategoryExport;
use App\Enums\ViewPaths\Admin\CategoryPost;
use App\Exports\CategoryListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CategoryPostAddRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Services\CategoryPostService;
use App\Services\ProductService;
use App\Traits\PaginatorTrait;
use App\Models\Brand;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryController extends BaseController
{
    use PaginatorTrait;

    public function __construct(
        private readonly CategoryPostRepositoryInterface        $categoryRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getAddView($request);
    }

    public function getAddView(Request $request): View
    {
        $categories = $this->categoryRepo->getListWhere(orderBy: ['id'=>'desc'], searchValue: $request->get('searchValue'), dataLimit: getWebConfig(name: 'pagination_limit'));
        $languages = getWebConfig(name: 'pnc_language') ?? null;

        $defaultLanguage = $languages[0];
        return view(CategoryPost::LIST[VIEW], [
            'categories' => $categories,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
        ]);
    }
    public function getUpdateView(string|int $id): View|RedirectResponse|null
    {
        $category = $this->categoryRepo->getFirstWhere(params:['id'=>$id], relations: ['translations']);
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        
        $brands=Brand::all();
        $defaultLanguage = $languages[0];
        return view(CategoryPost::UPDATE[VIEW], [
            'category' => $category,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
        ]);
    }
    public function add(CategoryPostAddRequest $request, CategoryPostService $categoryService): RedirectResponse|null
    {
        $dataArray = $categoryService->getAddData(request:$request);
        
        $savedCategory = $this->categoryRepo->add(data:$dataArray);
        $this->translationRepo->add(request:$request, model:'App\Models\CategoryPost', id:$savedCategory->id);
        Toastr::success(translate('category_added_successfully'));
        return back();
    }

    public function update(CategoryPostAddRequest $request, CategoryPostService $categoryService): RedirectResponse|null
    {
        $category = $this->categoryRepo->getFirstWhere(params:['id'=>$request['id']]);
        $dataArray = $categoryService->getUpdateData(request:$request, data: $category);

        
        $this->categoryRepo->update(id:$request['id'], data:$dataArray);
        $this->translationRepo->update(request:$request, model:'App\Models\CategoryPost', id:$request['id']);

        Toastr::success(translate('category_updated_successfully'));
        return back();
    }

    
    public function delete(Request $request, CategoryPostService $categoryService): RedirectResponse
    {
        $this->categoryRepo->delete(params: ['id'=>$request['id']]);
        Toastr::success(translate('deleted_successfully'));
        return redirect()->back();
    }
}
