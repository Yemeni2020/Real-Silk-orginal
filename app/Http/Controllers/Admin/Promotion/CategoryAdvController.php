<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Contracts\Repositories\AdvCategoryRepositoryInterface;
use App\Contracts\Repositories\AdvRepositoryInterface;
use App\Enums\ViewPaths\Admin\Adv;
use App\Http\Controllers\BaseController;
use App\Services\AdvCategoryService;
use App\Traits\FileManagerTrait;
use Illuminate\Contracts\View\View;
use App\Http\Requests\Admin\AdvCategoryAddRequest;
use Brian2694\Toastr\Facades\Toastr;
use App\Contracts\Repositories\TranslationRepositoryInterface;

use Illuminate\Http\Request;

class CategoryAdvController extends BaseController
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly AdvCategoryRepositoryInterface   $categoryRepo,
        private readonly AdvRepositoryInterface           $AdvRepo,

        private readonly AdvCategoryService       $categoryService,
        
        private readonly TranslationRepositoryInterface     $translationRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $categories = $this->categoryRepo->getListWhere(orderBy: ['id'=>'desc'], searchValue: $request->get('searchValue'), dataLimit: getWebConfig(name: 'pagination_limit'));

        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        return view(Adv::LISTCATEGORY[VIEW],compact('languages','defaultLanguage','categories'));
    }

    
   public function store(AdvCategoryAddRequest $request){
        
       $data= $this->categoryService->getAddData($request);
       $savedCategory = $this->categoryRepo->add($data);
       $this->translationRepo->add(request:$request, model:'App\Models\AdvCategory', id:$savedCategory->id);
       Toastr::success(translate('category_added_successfully'));
        return back();
   }

   public function getUpdateView(Request $request,$id){

        $category=$this->categoryRepo->getFirstWhere(["id"=>$id]);
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        return view(Adv::UPDATECATEGORY[VIEW],compact('languages','defaultLanguage','category'));
    }

    public function update(AdvCategoryAddRequest $request,$id){
        $category = $this->categoryRepo->getFirstWhere(params:['id'=>$request['id']]);
        $data= $this->categoryService->getUpdateData($request,$category);
        
        $this->categoryRepo->update($id,$data);
        $this->translationRepo->update(request:$request, model:'App\Models\AdvCategory', id:$request['id']);
        Toastr::success(translate('category_added_successfully'));
        return back();
    }
    public function delete(Request $request) 
    {
        $category=$this->categoryRepo->getFirstWhere(params:['id'=>$request['id']]);

        // dump($category);
        // return null;

        $this->categoryService->deleteImages($category);
        $this->categoryRepo->delete(params: ['id'=>$request['id']]);
        $this->AdvRepo->delete(params: ['category'=>$request['id']]);
        Toastr::success(translate('deleted_successfully'));
        return back();
    }
}
