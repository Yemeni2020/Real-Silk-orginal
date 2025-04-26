@extends('layouts.back-end.app')

@section('title', translate('product_Preview'))

@section('content')
    <div class="content container-fluid text-start">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-10 mb-3">
            <div class="">
                <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                    <img src="{{ asset('public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                    {{ translate('product_details') }}
                </h2>
            </div>
        </div>
        <div class="card card-top-bg-element">
            <div class="card-body">
                <div>
                    <div class="media flex-nowrap flex-column flex-sm-row gap-3 flex-grow-1 align-items-center align-items-md-start">
                        <div class="d-flex flex-column align-items-center __min-w-165px">
                            <a class="aspect-1 float-left overflow-hidden"
                               href="{{ getStorageImages(path: $product->thumbnail_full_url,type: 'backend-product') }}"
                               data-lightbox="product-gallery-{{ $product['id'] }}">
                                <img class="avatar avatar-170 rounded object-fit-cover"
                                     src="{{ getStorageImages(path: $product->thumbnail_full_url,type: 'backend-product') }}"
                                     alt="">
                            </a>
                            
                        </div>

                        <div class="d-block flex-grow-1 w-max-md-100">
                            @php($languages = getWebConfig(name:'pnc_language'))
                            @php($defaultLanguage = 'en')
                            @php($defaultLanguage = $languages[0])
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <ul class="nav nav-tabs w-fit-content mb-2">
                                    @foreach($languages as $language)
                                        <li class="nav-item text-capitalize">
                                            <a class="nav-link lang-link {{$language == $defaultLanguage? 'active':''}}"
                                            href="javascript:"
                                            id="{{$language}}-link">{{ getLanguageName($language).'('.strtoupper($language).')' }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                @if($product['added_by'] == 'seller' && ($product['request_status'] == 0 || $product['request_status'] == 1))
                                    <div class="d-flex justify-content-sm-end flex-wrap gap-2 pb-4">
                                        <div>
                                            <button class="btn btn-danger p-2 px-3" data-toggle="modal" data-target="#publishNoteModal">
                                                {{ translate('reject') }}
                                            </button>
                                        </div>
                                        <div>
                                            @if($product['request_status'] == 0)
                                                <button class="btn btn-success p-2 px-3 update-status"
                                                   data-id="{{ $product['id'] }}"
                                                   data-redirect-route="{{route('admin.products.list',['vendor', 'status' => $product['request_status']])}}"
                                                   data-message ="{{translate('want_to_approve_this_product_request_request').'?'}}" data-status="1">
                                                    {{ translate('approve') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if($product['added_by'] == 'seller' && ($product['request_status'] == 2))
                                    <div class="d-flex justify-content-sm-end flex-wrap gap-2 pb-4">
                                        <div>
                                            <span>{{translate('status').' : '}}</span>
                                            <span class="__badge badge badge-soft-danger">{{translate('rejected')}}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap align-items-center flex-sm-nowrap justify-content-between gap-3 min-h-50">
                                <div class="d-flex flex-wrap gap-2 align-items-center">

                                    @if ($product->product_type == 'physical' && !empty($product->color_image) && count($product->color_images_full_url)>0)
                                        @foreach ($product->color_images_full_url as $colorImageKey => $photo)
                                            <div class="{{$colorImageKey > 4 ? 'd-none' : ''}}">
                                                <a class="aspect-1 float-left overflow-hidden d-block border rounded-lg position-relative"
                                                       href="{{ getStorageImages(path: $photo['image_name'], type: 'backend-product') }}"
                                                       data-lightbox="product-gallery-{{ $product['id'] }}">
                                                    <img width="50"  class="img-fit max-50" alt=""
                                                         src="{{ getStorageImages(path: $photo['image_name'], type: 'backend-product') }}">
                                                    @if($colorImageKey > 3)
                                                        <div class="extra-images">
                                                            <span class="extra-image-count">
                                                                +{{ (count($product->color_images_full_url) - $colorImageKey) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        @foreach ($product->images_full_url as $imageKey => $photo)
                                            <div class="{{$imageKey > 4 ? 'd-none' : ''}}">
                                                <a class="aspect-1 float-left overflow-hidden d-block border rounded-lg position-relative {{$imageKey > 4 ? 'd-none' : ''}}"
                                                   href="{{ getStorageImages(path: $photo, type: 'backend-product') }}"
                                                   data-lightbox="product-gallery-{{ $product['id'] }}">
                                                    <img width="50"  class="img-fit max-50" alt=""
                                                         src="{{ getStorageImages(path: $photo, type: 'backend-product') }}">
                                                    @if($imageKey > 4)
                                                        <div class="extra-images">
                                                            <span class="extra-image-count">
                                                                +{{ (count($product->images_full_url) - $imageKey) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </a>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                            </div>
                            <div class="d-block mt-2">
                                @foreach($languages as $language)
                                        <?php
                                        if (count($product['translations'])) {
                                            $translate = [];
                                            foreach ($product['translations'] as $translation) {
                                                if ($translation->locale == $language && $translation->key == "name") {
                                                    $translate[$language]['name'] = $translation->value;
                                                }
                                                if ($translation->locale == $language && $translation->key == "description") {
                                                    $translate[$language]['description'] = $translation->value;
                                                }
                                            }
                                        }
                                        ?>
                                    <div class="{{ $language != 'en'? 'd-none':''}} lang-form" id="{{ $language}}-form">
                                        <div class="d-flex">
                                            <h2 class="mb-2 pb-1 text-gulf-blue">{{ $translate[$language]['name'] ?? $product['name'] }}</h2>
                                            <a class="btn btn-outline--primary btn-sm square-btn mx-2 w-auto h-25"
                                               title="{{ translate('edit') }}"
                                               href="{{ route('admin.post.update', [$product['id']]) }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                        </div>
                                        <div class="">
                                            <label class="text-gulf-blue font-weight-bold">{{ translate('description').' : ' }}</label>
                                            <div class="rich-editor-html-content">
                                                {!! $translate[$language]['description'] ?? $product['details'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-3 flex-wrap">
                    <div class="border p-3 mobile-w-100 w-170">
                        <div class="d-flex flex-column mb-1">
                            <h6 class="font-weight-normal text-capitalize">{{ translate('total_sold') }} :</h6>
                            <h3 class="text-primary fs-18">{{ $product['qtySum'] }}</h3>
                        </div>
                        <div class="d-flex flex-column">
                            <h6 class="font-weight-normal text-capitalize">{{ translate('total_sold_amount') }} :</h6>
                            <h3 class="text-primary fs-18">
                                {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: ($product['priceSum'] - $product['discountSum']))) }}
                            </h3>
                        </div>
                    </div>

                    <div class="row gy-3 flex-grow-1">
                        <div class="col-sm-6 col-xl-4">
                            <h4 class="mb-3 text-capitalize">{{ translate('general_information') }}</h4>

                            <div class="pair-list">
                               

                                <div>
                                    <span class="key text-nowrap">{{ translate('category') }}</span>
                                    <span>:</span>
                                    <span class="value">
                                        {{isset($product->category) ? $product->category->default_name : translate('category_not_found') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-2 mt-3">
            

                <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg--primary--light">
                        <h5 class="card-title text-capitalize">{{translate('product_SEO_&_meta_data')}}</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <h6 class="mb-3 text-capitalize">
                                {{ $product?->seoInfo?->title ?? ( $product->meta_title ?? translate('meta_title_not_found').' '.'!')}}
                            </h6>
                        </div>
                        <p class="text-capitalize">
                            {{ $product?->seoInfo?->description ?? ($product->meta_description ?? translate('meta_description_not_found').' '.'!')}}
                        </p>
                        @if($product?->seoInfo?->image_full_url['path'] || $product->meta_image_full_url['path'])
                            <div class="d-flex flex-wrap gap-2">
                                <a class="aspect-1 float-left overflow-hidden"
                                   href="{{ getStorageImages(path: $product?->seoInfo?->image_full_url['path'] ? $product?->seoInfo?->image_full_url : $product->meta_image_full_url,type: 'backend-basic') }}"
                                   data-lightbox="meta-thumbnail">
                                    <img class="max-width-100px"
                                         src="{{ getStorageImages(path: $product?->seoInfo?->image_full_url['path'] ? $product?->seoInfo?->image_full_url : $product->meta_image_full_url,type: 'backend-basic') }}" alt="{{translate('meta_image')}}">
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg--primary--light">
                        <h5 class="card-title text-capitalize">{{translate('product_video')}}</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <h6 class="mb-3 text-capitalize">
                                {{$product['video_provider'].' '.translate('video_link')}}
                            </h6>
                        </div>
                        @if($product['video_url'])
                            <a href="{{ (str_contains($product->video_url, "https://") || str_contains($product->video_url, "http://")) ? $product['video_url'] : "javascript:"}}" target="_blank"
                               class="text-primary {{(str_contains($product->video_url, "https://") || str_contains($product->video_url, "http://"))?'' : 'cursor-default' }}">
                                {{$product['video_url']}}
                            </a>
                        @else
                            <span>{{ translate('no_data_to_show').' '.'!'}}</span>
                        @endif
                    </div>
                </div>
            </div>
            @if ($product->denied_note && $product['request_status'] == 2)
                <div class="col-md-12">
                    <div class="card h-100">
                        <div class="card-header bg--primary--light">
                            <h5 class="card-title text-capitalize">{{translate('reject_reason')}}</h5>
                        </div>
                        <div class="card-body">
                            <div>
                                {{ $product->denied_note}}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <div class="modal fade" id="publishNoteModal" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('rejected_note') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="form-group" action="{{ route('admin.products.deny', ['id'=>$product['id']]) }}"
                      method="post" id="product-status-denied">
                    @csrf
                    <div class="modal-body">
                        <textarea class="form-control text-area-max-min" name="denied_note" rows="3"></textarea>
                        <span id="denied-note-word-count">{{translate('0/100')}}</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('close') }}
                        </button>
                        <button type="button" class="btn btn--primary form-submit"
                                data-redirect-route="{{route('admin.products.list',['vendor','status' => $product['request_status']])}}"
                                data-form-id="product-status-denied">{{ translate('submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <span id="get-update-status-route" data-action="{{ route('admin.products.approve-status')}}"></span>
@endsection
@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/product-view.js') }}"></script>
@endpush
