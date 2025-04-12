@php
use App\Models\Brand;

@endphp
<div class="category-container">
    <button class="scroll-left">&#10094;</button>
    <div class="category-wrapper">
        <ul class="w100 category-list ">
            @php($categoryIndex=0)
            @foreach($categories_menu as $category)
                @php($categoryIndex++)
                @if($categoryIndex < 20)
                
                    <li class="nav-item dropdown {{request()->is('/')?'active':''}}">
                    <a class="nav-link " href="{{route('products',['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}">
                            <span>{{  $category['name'] }}</span>
                        </a>
                        @if ($category->childes->count() > 0)
                        <div class="mega_menu_parent z-2">
                            <div class="row">
                                <div class="col-9">

                                    @if ($category->childes->count() > 0)
                                    <div class="row">
                                        @foreach ($category->childes as $sub_category)
                                            <div class="col-3">
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
                        @endif
                    </li>
                    
                @endif
            @endforeach
        </ul>
    </div>
    
    <button class="scroll-right">&#10095;</button> <!-- سهم التمرير لليمين -->

</div>