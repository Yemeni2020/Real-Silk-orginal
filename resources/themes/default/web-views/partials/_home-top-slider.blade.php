@if(count($main_banner) > 0)
@php
$categories;
@endphp
<section class="bg-transparent py-3">
    <div class="container position-relative">
        <div class="row no-gutters position-relative rtl">
            @if (isset($categories) && $categories->count() > 0 )
                <!-- <div class="col-xl-3 position-static d-none d-xl-block __top-slider-cate">
                    <div class="category-menu-wrap position-static">
                        <ul class="category-menu mt-0">
                            @foreach ($categories as $key=>$category)
                                <li>
                                    <a href="{{route('products',['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}">{{$category->name}}</a>
                                    @if ($category->childes->count() > 0)
                                        <div class="mega_menu z-2">
                                            @foreach ($category->childes as $sub_category)
                                                <div class="mega_menu_inner">
                                                    <h6><a href="{{route('products',['category_id'=> $sub_category['id'],'data_from'=>'category','page'=>1])}}">{{$sub_category->name}}</a></h6>
                                                    @if ($sub_category->childes->count() >0)
                                                        @foreach ($sub_category->childes as $sub_sub_category)
                                                            <div><a href="{{route('products',['category_id'=> $sub_sub_category['id'],'data_from'=>'category','page'=>1])}}">{{$sub_sub_category->name}}</a></div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                            <li class="text-center">
                                <a href="{{route('categories')}}" class="text-primary font-weight-bold justify-content-center text-capitalize">
                                    {{translate('view_all')}}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div> -->
            @endif

           
            @if($side_banner->count()>0)
            <div class="col-md-12 col-sm-12 col-xm-12 col-lg-9 col-xl-9 __top-slider-images">
            @else
            <div class="col-12 col-xl-12 __top-slider-images">
            @endif
                <div class="{{request()->cookie('direction', 'ltr') === "rtl" ? 'pr-xl-2' : 'pl-xl-2'}}">
                    <div class="owl-theme owl-carousel hero-slider">
                        @foreach($main_banner as $key=>$banner)
                            <a href="{{$banner['url']}}" class="d-block" target="_blank">
                                <img class="w-100 __slide-img" alt=""
                                    src="{{ getStorageImages(path: $banner->photo_full_url, type: 'banner') }}">
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>


             <div class="col-3 col-xl-3 d-none d-lg-block __top-slider-images">
                <div class="{{request()->cookie('direction', 'ltr') === "rtl" ? 'pr-xl-1' : 'pl-xl-1'}}">
                    <div class="owl-theme owl-carousel hero-slider">
                        @foreach($side_banner as $key=>$banner)
                            <a href="{{$banner['url']}}" class="d-block" target="_blank">
                                <img class="w-100 __slide-img" alt=""
                                    src="{{ getStorageImages(path: $banner->photo_full_url, type: 'banner') }}">
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
