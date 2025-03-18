@php
use App\Models\Brand;

@endphp

<div class="container">
    <div class="category-menu-wrap">
        <ul class="category-menu">
            @foreach ($categories as $key=>$category)
                <li class="has-sub-item">
                    <a href="{{route('products',['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}">{{$category->name}}</a>
                    
                    <div class="mega_menu_parent z-2">
                        <div class="row">
                            <div class="col-9">

                                @if ($category->childes->count() > 0)
                                <div class="mega_menu">
                                    @foreach ($category->childes as $sub_category)
                                        <div class="mega_menu_inner">
                                            <h6>
                                                <a href="{{route('products',['category_id'=> $sub_category['id'],'data_from'=>'category','page'=>1])}}">{{$sub_category->name}}</a>
                                            </h6>
                                            @if ($sub_category->childes->count() >0)
                                                @foreach ($sub_category->childes as $sub_sub_category)
                                                    <div>
                                                        <a href="{{route('products',['category_id'=> $sub_sub_category['id'],'data_from'=>'category','page'=>1])}}">{{$sub_sub_category->name}}</a>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endforeach

                                </div>
                                @endif
                            </div>
                            
                        
                            @if(isset($category->image_ad))
                                @if(!empty($category->image_ad))
                                
                                <div class="col-3">
                                    
                                    <img src="{{ getStorageImages(path: $category->adv_full_url, type: 'category') }}" alt="Category Image">
                                </div>
                                @endif
                            @endif
                            <?php
                                $brands_list = is_array($category['brands']) 
                                    ? $category['brands'] 
                                    : json_decode($category['brands'], true);

                                $brands_list = $brands_list ?? [];
                                $brands = Brand::whereIn('id', $brands_list)->get();
                            ?>
                            @if($brands->isNotEmpty())
                            <div class="col-12">


                                <div class="mt-sm-3 mb-3 brand-slider">
                                    <div class="owl-carousel owl-theme p-2 brands-slider">
                                        @foreach($brands as $brand)
                                            <div class="text-center">
                                                <a href="{{route('products',['brand_id'=> $brand['id'],'data_from'=>'brand','page'=>1])}}"
                                                class="__brand-item">
                                                    <img alt="{{ $brand->image_alt_text }}"
                                                        src="{{ getStorageImages(path: $brand->image_full_url, type: 'brand') }}">
                                                </a>
                                                <span>{{$brand->name}}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                </div>
                            @endif

                        </div>
                    </div>
                </li>
            @endforeach
            <li class="text-center">
                <a href="{{route('categories')}}" class="text-primary font-weight-bold justify-content-center">
                    {{ translate('View_All') }}
                </a>
            </li>
        </ul>
    </div>
</div>