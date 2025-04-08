<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\AdvRepositoryInterface;
use App\Contracts\Repositories\AdvCategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Enums\ViewPaths\Admin\Adv;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\AdvAddRequest;
use App\Http\Requests\Admin\AdvUpdateRequest;
use App\Services\AdvService;
use App\Traits\FileManagerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Contracts\Repositories\TranslationRepositoryInterface;

class AdvController extends BaseController
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly BannerRepositoryInterface        $bannerRepo,
        private readonly AdvRepositoryInterface           $AdvRepo,
        private readonly AdvCategoryRepositoryInterface   $categoryRepo,

        private readonly ShopRepositoryInterface          $shopRepo,
        private readonly BrandRepositoryInterface         $brandRepo,
        private readonly ProductRepositoryInterface       $productRepo,
        private readonly AdvService       $AdvService,
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
        $Adv = $this->AdvRepo->getListWhere(orderBy: ['id'=>'desc'], searchValue: $request->get('searchValue'), dataLimit: getWebConfig(name: 'pagination_limit'));

        $categories=$this->categoryRepo->getListWhere(orderBy: ['id'=>'desc'], dataLimit: 'all');
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        return view(Adv::LIST[VIEW],  compact('languages','defaultLanguage','Adv','categories'));
    }
    public function store(AdvAddRequest $request){
        
        $data= $this->AdvService->getAddData($request);
        $savedCategory = $this->AdvRepo->add($data);
        $this->translationRepo->add(request:$request, model:'App\Models\Adv', id:$savedCategory->id);
        Toastr::success(translate('category_added_successfully'));
        return back();
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $data = [
            'status' => $request->get('home_status', 0),
        ];
        $this->AdvRepo->update(id: $request['id'], data:$data);
        return response()->json(['success' => 1,], 200);
    }

    public function getUpdateView(Request $request,$id){

        $Adv=$this->AdvRepo->getFirstWhere(["id"=>$id]);
        $categories=$this->categoryRepo->getListWhere(orderBy: ['id'=>'desc'], dataLimit: 'all');

        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        return view(Adv::UPDATE[VIEW],compact('languages','defaultLanguage','Adv','categories'));
    }

    public function update(AdvUpdateRequest $request,$id){
        $AdvRepo = $this->AdvRepo->getFirstWhere(params:['id'=>$request['id']]);
        $data= $this->AdvService->getUpdateData($request,$AdvRepo);
        
        $this->AdvRepo->update($id,$data);
        $this->translationRepo->update(request:$request, model:'App\Models\Adv', id:$request['id']);
        Toastr::success(translate('category_added_successfully'));
        return back();
    }

    public function delete(Request $request): RedirectResponse
    {
        
        $this->AdvRepo->delete(params: ['id'=>$request['id']]);
        Toastr::success(translate('deleted_successfully'));
        return redirect()->back();
    }
   
}
