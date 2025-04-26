@extends('layouts.front-end.app')

@section('title', $post['name'])

@push('css_or_js')
    @include(VIEW_FILE_NAMES['post_seo_meta_content_partials'], ['metaContentData' => $post?->seoInfo, 'post' => $post])
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}"/>
@endpush

@section('content')
    <div class="__inline-23">
        <div class="container mt-4 rtl text-align-direction">
            <div class="row {{request()->cookie('direction', 'ltr') === "rtl" ? '__dir-rtl' : ''}}">
                <div class="col-lg-9 col-12">

                    <?php $guestCheckout = getWebConfig(name: 'guest_checkout'); ?>
                    <div class="row">
                        <div class="col-lg-5 col-md-4 col-12">
                            <div class="cz-product-gallery">
                                <div class="cz-preview">
                                    <div id="sync1" class="owl-carousel owl-theme product-thumbnail-slider">
                                        @if($post->images!=null && json_decode($post->images)>0)
                                                @foreach ($post->images_full_url as $key => $photo)
                                                    <div
                                                        class="product-preview-item d-flex align-items-center justify-content-center {{$key==0?'active':''}}"
                                                        id="image{{$key}}">
                                                        <img class="cz-image-zoom img-responsive w-100"
                                                             src="{{ getStorageImages($photo, type: 'product') }}"
                                                             data-zoom="{{ getStorageImages(path: $photo, type: 'product') }}"
                                                             alt="{{ translate('product') }}" width="">
                                                        <div class="cz-image-zoom-pane"></div>
                                                    </div>
                                                @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div class="cz">
                                    <div class="table-responsive __max-h-515px" data-simplebar>
                                        <div class="d-flex">
                                            <div id="sync2" class="owl-carousel owl-theme product-thumb-slider">
                                                @if($post->images!=null && json_decode($post->images)>0)
                                                    @if(json_decode($post->colors) && count($post->color_images_full_url)>0)
                                                        @foreach ($post->color_images_full_url as $key => $photo)
                                                            @if($photo['color'] != null)
                                                                <div class="">
                                                                    <a class="product-preview-thumb color-variants-preview-box-{{ $photo['color'] }} {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                       id="preview-img{{$photo['color']}}"
                                                                       href="#image{{$photo['color']}}">
                                                                        <img alt="{{ translate('product') }}"
                                                                             src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}">
                                                                    </a>
                                                                </div>
                                                            @else
                                                                <div class="">
                                                                    <a class="product-preview-thumb {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                       id="preview-img{{$key}}" href="#image{{$key}}">
                                                                        <img alt="{{ translate('product') }}"
                                                                             src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}">
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        @foreach ($post->images_full_url as $key => $photo)
                                                            <div class="">
                                                                <a class="product-preview-thumb {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                   id="preview-img{{$key}}" href="#image{{$key}}">
                                                                    <img alt="{{ translate('product') }}"
                                                                         src="{{ getStorageImages(path: $photo, type: 'product') }}">
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 col-md-8 col-12 mt-md-0 mt-sm-3 web-direction">
                            <div class="details __h-100">
                                <h1 class="mb-2 __inline-24">{{$post->name}}</h1>
            
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mt-4 rtl col-12 text-align-direction">
                            <div class="row">
                                <div class="col-12">
                                    <div>
                                        <div
                                            class="px-4 pb-3 mb-3 mr-0 mr-md-2 bg-white __review-overview __rounded-10 pt-3">
                                            <ul class="nav nav-tabs nav--tabs d-flex justify-content-center mt-3"
                                                role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link __inline-27 active " href="#overview"
                                                       data-toggle="tab" role="tab">
                                                        {{translate('overview')}}
                                                    </a>
                                                </li>
                                                
                                            </ul>
                                            <div class="tab-content px-lg-3">
                                                <div class="tab-pane fade show active text-justify" id="overview"
                                                     role="tabpanel">
                                                    <div class="row pt-2 specification">

                                                        @if($post->video_url != null && (str_contains($post->video_url, "youtube.com/embed/")))
                                                            <div class="col-12 mb-4">
                                                                <iframe width="420" height="315"
                                                                        src="{{$post->video_url}}">
                                                                </iframe>
                                                            </div>
                                                        @endif
                                                        
                                                            @if ($post['details']  )
                                                                <div class="text-body col-lg-12 col-md-12 overflow-scroll fs-13 text-justify details-text-justify rich-editor-html-content">
                                                                    {!! $post['details'] !!}
                                                                </div>
                                                            @endif
                                                        <hr>
                                                    </div>
                                                        @if (!$post['details'] && ($post->video_url == null || !(str_contains($post->video_url, "youtube.com/embed/"))) &&1!=2 )
                                                            <div>
                                                                <div class="text-center text-capitalize py-5">
                                                                    <img class="mw-90"
                                                                        src="{{theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg')}}"
                                                                        alt="">
                                                                    <p class="text-capitalize mt-2">
                                                                        <small>{{translate('product_details_not_found')}}
                                                                            !</small>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
            </div>
        </div>


        @if (count($relatedPosts)>0)
            <div class="container rtl text-align-direction">
                <div class="card __card border-0">
                    <div class="card-body">
                        

                        <div class="row g-3 mt-1 w-100">
                            @include('web-views.posts.posts-card', ['posts' => $relatedPosts])

                    
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade rtl text-align-direction" id="show-modal-view" tabindex="-1" role="dialog" aria-labelledby="show-modal-image"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body flex justify-content-center">
                        <button class="btn btn-default __inline-33 dir-end-minus-7px"
                                data-dismiss="modal">
                            <i class="fa fa-close"></i>
                        </button>
                        <img class="element-center" id="attachment-view" src="" alt="">
                    </div>
                </div>
            </div>
        </div>

    </div>




    <span id="route-review-list-product" data-url="{{ route('review-list-product') }}"></span>
    <span id="products-details-page-data" data-id="{{ $post['id'] }}"></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/product-details.js') }}"></script>
    <script type="text/javascript" async="async"
            src="https://platform-api.sharethis.com/js/sharethis.js#property=5f55f75bde227f0012147049&product=sticky-share-buttons"></script>
@endpush
