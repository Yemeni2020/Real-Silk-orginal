
@if ($categories->count() > 0 )
    <section class="pb-4 rtl">
        <div class="container">
            <div>
                @if(1==2)
                <!-- <div class="mt-sm-3 mb-3 brand-slider">
                    <div class="owl-carousel owl-theme p-2 brands-slider">
                        @foreach($categories as $category)
                            <div class="text-center __m-5px __cate-item ">
                                <a href="{{route('products',['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}"
                                   class="__brand-item" >
                                   <div class="__img">
                                        <img alt="{{ $category->name }}"
                                                src="{{ getStorageImages(path:$category->icon_full_url, type: 'category') }}">
                                                
                                    </div>
                                    <p class="text-center fs-13 font-semibold mt-2" style="position: absolute;background: var(--web-primary) !important;color: white;bottom: -15%;border-radius:5px;">{{Str::limit($category->name, 12)}}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div> -->
                @endif
                

                <div class="card __shadow h-100 max-md-shadow-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="categories-title m-0">
                                <span class="font-semibold">{{ translate('categories')}}</span>
                            </div>
                            <div>
                                <!-- <a class="text-capitalize view-all-text web-text-primary"
                                   href="{{route('categories')}}">{{ translate('view_all')}}
                                    <i class="czi-arrow-{{request()->cookie('direction', 'ltr') === "rtl" ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}}"></i>
                                </a> -->
                            </div>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="position-relative">
                                <!-- أزرار التنقل -->
                                <button class="btn-scroll-left" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); z-index: 1; background: #fff; border: 1px solid #ddd; border-radius: 50%; width: 40px; height: 40px;">
                                    <i class="czi-arrow-left"></i>
                                </button>
                                <button class="btn-scroll-right" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); z-index: 1; background: #fff; border: 1px solid #ddd; border-radius: 50%; width: 40px; height: 40px;">
                                    <i class="czi-arrow-right"></i>
                                </button>

                                <!-- الشريط -->
                                <div class="category-scroll-container d-flex align-items-center overflow-hidden" style="white-space: nowrap; overflow-x: auto; scroll-behavior: smooth;">
                                    
                                    @foreach($categories as $key => $category)
                                        <div class="text-center d-inline-block mx-2 __cate-item" style="width: 100px;">
                                            <a href="{{route('products',['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}">
                                                <div class="__img" style="border-radius: 50%; overflow: hidden; width: 80px; height: 80px; margin: auto;">
                                                    <img loading="lazy" alt="{{ $category->name }}"
                                                        src="{{ getStorageImages(path:$category->icon_full_url, type: 'category') }}"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <p class="text-center fs-13 font-semibold mt-2 wrapped-text" style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;">{{$category->name}}</p>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                

                <div class="card d-none __shadow h-100 max-md-shadow-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="categories-title m-0">
                                <span class="font-semibold">{{ translate('categories')}}</span>
                            </div>
                            <div>
                                <!-- <a class="text-capitalize view-all-text web-text-primary"
                                   href="{{route('categories')}}">{{ translate('view_all')}}
                                    <i class="czi-arrow-{{request()->cookie('direction', 'ltr') === "rtl" ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}}"></i>
                                </a> -->
                            </div>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="row mt-3">
                                @foreach($categories as $key => $category)
                                    @if ($key<10)
                                        <div class="text-center __m-5px __cate-item">
                                            <a href="{{route('products',['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}">
                                                <div class="__img">
                                                    <img alt="{{ $category->name }}"
                                                         src="{{ getStorageImages(path:$category->icon_full_url, type: 'category') }}">
                                                         
                                                </div>
                                                <p class="text-center fs-13 font-semibold mt-2">{{Str::limit($category->name, 12)}}</p>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="d-md-none">
                            <div class="owl-theme owl-carousel categories--slider mt-3">
                                @foreach($categories as $key => $category)
                                    @if ($key<10)
                                        <div class="text-center m-0 __cate-item w-100">
                                            <a href="{{route('products',['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}">
                                                <div class="__img mw-100 h-auto">
                                                    <img alt="{{ $category->name }}"
                                                         src="{{ getStorageImages(path: $category->icon_full_url, type: 'category') }}">
                                                </div>
                                                <p class="text-center small mt-2">{{Str::limit($category->name, 12)}}</p>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scrollContainer = document.querySelector('.category-scroll-container');
    const scrollLeftButton = document.querySelector('.btn-scroll-left');
    const scrollRightButton = document.querySelector('.btn-scroll-right');

    scrollLeftButton.addEventListener('click', () => {
        scrollContainer.scrollBy({
            left: -200, // تحريك للخلف
            behavior: 'smooth',
        });
    });

    scrollRightButton.addEventListener('click', () => {
        scrollContainer.scrollBy({
            left: 200, // تحريك للأمام
            behavior: 'smooth',
        });
    });
});


</script>

@endpush