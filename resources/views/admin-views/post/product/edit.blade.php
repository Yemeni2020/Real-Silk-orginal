@php

$curnnet_lang = getDefaultLanguage();
$OpenAi=getWebConfig("OpenAi_translate");
$DeepSeekAI=getWebConfig("DeepSeekAI_translate");
$deepl=getWebConfig("deepl_translate");
$defulte_translate=getWebConfig("defulte_translate");
$auto_translate=getWebConfig("auto_translate");

@endphp
@extends('layouts.back-end.app')

@section('title', translate(request('product-gallery')==1 ?'product_Add' : 'product_Edit'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate(request('product-gallery')==1 ?'product_Add' : 'product_Edit') }}
            </h2>
        </div>

        <form class="product-form text-start" action="{{ request('product-gallery')==1? route('admin.products.add') : route('admin.post.update',$post->id) }}" method="post"
              enctype="multipart/form-data" id="product_form">
            @csrf

            <div class="card">
                <div class="px-4 pt-3">
                    <ul class="nav nav-tabs w-fit-content mb-4">
                        @foreach($languages as $language)
                            <li class="nav-item text-capitalize">
                                <a class="nav-link form-system-language-tab  {{ $language == $curnnet_lang? 'active':''}}" href="#"
                                   id="{{ $language}}-link">{{getLanguageName($language).'('.strtoupper($language).')'}}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-body">
                    @foreach($languages as $language)
                            <?php
                            if (count($post['translations'])) {
                                $translate = [];
                                foreach ($post['translations'] as $translation) {
                                    if ($translation->locale == $language && $translation->key == "name") {
                                        $translate[$language]['name'] = $translation->value;
                                    }
                                    if ($translation->locale == $language && $translation->key == "description") {
                                        $translate[$language]['description'] = $translation->value;
                                    }
                                }
                            }
                            ?>
                        <div class="{{ $language != $curnnet_lang? 'd-none':''}} form-system-language-form" id="{{ $language}}-form">
                            <div class="form-group">
                                <label class="title-color" for="{{ $language}}_name">
                                    {{ translate('product_name') }}
                                    ({{strtoupper($language) }})

                                    @if($language == $curnnet_lang)
                                        <span class="input-required-icon">*</span>
                                    @endif
                                </label>
                                @if($language != $curnnet_lang)
                                        <button class="btn btn-primary translate_ai" type="button" data-lang="{{$language}}"  >{{translate('translate_ai')}}</button>
                                @endif
                                <input type="text" {{ $language == $curnnet_lang? 'required':''}} name="name[]"
                                       id="{{ $language}}_name"
                                       value="{{ $translate[$language]['name']??$post['name']}}"
                                       class="form-control {{ $language == $curnnet_lang ? 'product-title-default-language' : '' }}" placeholder="{{ translate('new_Product') }}" required>
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $language}}">
                            <div class="form-group pt-4">
                                <label class="title-color">{{ translate('description') }}
                                    ({{strtoupper($language) }})
                                    @if($language == $curnnet_lang)
                                        <span class="input-required-icon">*</span>
                                    @endif
                                </label>
                                <textarea id="{{ $language }}_description" name="description[]" class="summernote {{ $language == $curnnet_lang ? 'product-description-default-language' : '' }}"
                                >{!! $translate[$language]['description']??$post['details'] !!}</textarea>
                            </div>
                        </div>
                    @endforeach

                    <div class="row">
                        <div class="col-6">
                            <label class="title-color"
                                    >{{ translate('Translate_using') }}
                                
                            </label>
                            <select name="translate_ai" class="form-control" style="display: inline-block;width:auto;" id="translate_ai">
                                @if(!empty($deepl) && isset($deepl['kay']) && (!empty($deepl['status']) && $deepl['status'] == 1))
                                    <option {{isset($defulte_translate) && $defulte_translate=="deepl" ? "selected" : "" }} value="deepl"><?php echo translate('deepl') ?></option>
                                @endif
                                @if(!empty($OpenAi) && isset($OpenAi['kay']) && (!empty($OpenAi['status']) && $OpenAi['status'] == 1))
                                    <option {{isset($defulte_translate) && $defulte_translate=="OpenAi" ? "selected" : "" }} value="OpenAi"><?php echo translate('OpenAI') ?></option>
                                @endif
                                @if(!empty($DeepSeekAI) && isset($DeepSeekAI['kay']) && (!empty($DeepSeekAI['status']) && $DeepSeekAI['status'] == 1))
                                    <option {{isset($defulte_translate) && $defulte_translate=="DeepSeekAI" ? "selected" : "" }} value="DeepSeekAI">{{ translate('DeepSeekAI') }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('general_setup') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label for="name" class="title-color">
                                    {{ translate('category') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <select class="js-example-basic-multiple js-states js-example-responsive form-control action-get-request-onchange"
                                    name="category_id"
                                    id="category_id"
                                    data-url-prefix="{{ url('/admin/products/get-categories?parent_id=') }}"
                                    data-element-id="sub-category-select"
                                    data-element-type="select">
                                    <option value="0" selected disabled>---{{ translate('select') }}---</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category['id']}}" {{ $category->id==$post['category_id'] ? 'selected' : ''}}>{{ $category['defaultName']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <div class="mt-3 rest-part">
                <div class="product-image-wrapper">
                    <div class="item-1">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                    <div>
                                        <label for="name" class="title-color text-capitalize font-weight-bold mb-0">
                                            {{ translate('product_thumbnail') }}
                                            <span class="input-required-icon">*</span>
                                        </label>
                                        <span
                                            class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Product Image'] }}</span>
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                              title="{{ translate('add_your_products_thumbnail_in') }} JPG, PNG or JPEG {{ translate('format_within') }} 2MB">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom_upload_input">
                                        <input type="file" name="image" class="custom-upload-input-file action-upload-color-image" id=""
                                               data-imgpreview="pre_img_viewer"
                                               accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                        @if ($post->thumbnail_full_url['path'])
                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-flex">
                                                <i class="tio-delete"></i>
                                            </span>
                                        @else
                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                <i class="tio-delete"></i>
                                            </span>
                                        @endif

                                        <div class="img_area_with_preview position-absolute z-index-2">
                                            <img id="pre_img_viewer" class="h-auto aspect-1 bg-white onerror-add-class-d-none" alt=""
                                                 src="{{ getStorageImages(path: $post->thumbnail_full_url, type:'backend-product') }}">
                                        </div>
                                        <div
                                            class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                            <div class="d-flex flex-column justify-content-center align-items-center">
                                                <img alt=""
                                                    src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                    class="w-75">
                                                <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-muted mt-2">{{ translate('image_format') }} : {{ "Jpg, png, jpeg, webp " }}<br>
                                        {{ translate('image_size') }} : {{ translate('max') }} {{ "2 MB" }}</p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="item-2 color_image_column d-none">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <label for="name"
                                           class="title-color text-capitalize font-weight-bold mb-0">{{ translate('colour_wise_product_image') }}</label>
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_color-wise_product_images_here') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>
                                <p class="text-muted">{{ translate('must_upload_colour_wise_images_first._Colour_is_shown_in_the_image_section_top_right.') }} </p>

                                <div id="color-wise-image-area" class="row g-2 mb-4">
                                    <div class="col-12">
                                        <div class="row g-2" id="color_wise_existing_image"></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row g-2" id="color-wise-image-section"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="additional_image_column item-2">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <div>
                                        <label for="name"
                                               class="title-color text-capitalize font-weight-bold mb-0">{{ translate('upload_additional_image') }}<span class="input-required-icon">*</span></label>
                                        <span
                                            class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Product Image'] }}</span>
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                              title="{{ translate('upload_any_additional_images_for_this_product_from_here') }}.">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>

                                </div>
                                <p class="text-muted">{{ translate('upload_additional_product_images') }}</p>

                                <div class="coba-area">

                                    <div class="row g-2" id="additional_Image_Section">
                                    @foreach ($post->images_full_url as $key => $photo)
                                                @php($unique_id = rand(1111,9999))
                                                <div class="col-sm-12 col-md-4" id="addition-image-section-{{$key}}">
                                                    <div
                                                        class="custom_upload_input custom-upload-input-file-area position-relative border-dashed-2">
                                                        @if(request('product-gallery'))
                                                            <button class="delete_file_input_css btn btn-outline-danger btn-sm square-btn remove-addition-image-for-product-gallery" data-section-remove-id="addition-image-section-{{$key}}">
                                                                <i class="tio-delete"></i>
                                                            </button>
                                                        @else
                                                        <a class="delete_file_input_css btn btn-outline-danger btn-sm square-btn"
                                                           href="{{ route('admin.post.delete-image',['id'=>$post['id'],'name'=>$photo['key']]) }}">
                                                            <i class="tio-delete"></i>
                                                        </a>
                                                        @endif

                                                        <div
                                                            class="img_area_with_preview position-absolute z-index-2 border-0">
                                                            <img id="additional_Image_{{ $unique_id }}" alt=""
                                                                 class="h-auto aspect-1 bg-white onerror-add-class-d-none"
                                                                 src="{{ getStorageImages(path: $photo, type:'backend-product') }}">
                                                                 
                                                            @if(request('product-gallery'))
                                                                <input type="text" name="existing_images[]" value="{{$photo['key']}}" hidden>
                                                            @endif
                                                        </div>
                                                        <div
                                                            class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                            <div
                                                                class="d-flex flex-column justify-content-center align-items-center">
                                                                <img alt=""
                                                                    src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                                    class="w-75">
                                                                <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        <div class="col-sm-12 col-md-4">
                                            <div class="custom_upload_input position-relative border-dashed-2">
                                                <input type="file" name="images[]" class="custom-upload-input-file action-add-more-image"
                                                       data-index="1" data-imgpreview="additional_Image_1"
                                                       accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                       data-target-section="#additional_Image_Section">

                                                <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                                    <i class="tio-delete"></i>
                                                </span>

                                                <div class="img_area_with_preview position-absolute z-index-2 border-0">
                                                    <img id="additional_Image_1" class="h-auto aspect-1 bg-white d-none" alt=""
                                                         src="">
                                                </div>
                                                <div
                                                    class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                    <div
                                                        class="d-flex flex-column justify-content-center align-items-center">
                                                        <img alt=""
                                                            src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                            class="w-75">
                                                        <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
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
                <input type="hidden" id="color_image" value="{{ json_encode($post->color_images_full_url) }}">
                <input type="hidden" id="images" value="{{ json_encode($post->images_full_url) }}">
                <input type="hidden" id="product_id" name="product_id" value="{{ $post->id }}">
                <input type="hidden" id="remove_url" value="{{ route('admin.products.delete-image') }}">
            </div>

            



            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('product_video') }}</h4>
                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                              title="{{ translate('add_the_YouTube_video_link_here._Only_the_YouTube-embedded_link_is_supported') }}.">
                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="title-color mb-0">{{ translate('youtube_video_link') }}</label>
                        <span class="text-info"> ( {{ translate('optional_please_provide_embed_link_not_direct_link') }}. )</span>
                    </div>
                    <input type="text" value="{{ $post['video_url']}}" name="video_url"
                           placeholder="{{ translate('ex').': https://www.youtube.com/embed/5R06LRdUCSE' }}"
                           class="form-control" required>
                </div>
            </div>

            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">
                            {{ translate('seo_section') }}
                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                  data-placement="top"
                                  title="{{ translate('add_meta_titles_descriptions_and_images_for_products').', '.translate('this_will_help_more_people_to_find_them_on_search_engines_and_see_the_right_details_while_sharing_on_other_social_platforms') }}">
                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                            </span>
                        </h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate('meta_Title') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          data-placement="top"
                                          title="{{ translate('add_the_products_title_name_taglines_etc_here').' '.translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </label>
                                <input type="text" name="meta_title" value="{{ $post?->seoInfo?->title ?? $post->meta_title}}" placeholder=""
                                       class="form-control">
                            </div>
                            
                            <!-- KayWords -->
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate('meta_kay_words') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                        data-placement="top"
                                        title="{{ translate('add_the_posts_title_name_taglines_etc_here').' '.translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_posts_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </label>

                                <input type="text" id="meta_keywords_input" class="form-control" placeholder="{{ translate('meta_keywords') }}">
                                <small id="meta_keywords_limit" class="text-muted"></small>

                                <!-- hidden input فيه القيم الحقيقية -->
                                <input type="hidden" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $post?->meta_keywords) }}">

                                <div id="meta_keywords_tags" class="mt-2" style="display: flex; flex-wrap: wrap; gap: 5px;">
                                </div>
                            </div>
                            <!-- End Input KayWords -->

                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate('meta_Description') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          data-placement="top"
                                          @if($post['added_by'] == 'admin')
                                            title="{{ translate('write_a_short_description_of_the_InHouse_shops_product').' '.translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]"
                                          @else
                                            title="{{ translate('write_a_short_description_of_this_shop_product').' '.translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]"
                                          @endif
                                    >
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </label>

                                <textarea rows="4" type="text" name="meta_description" id="meta_description"
                                          class="form-control">{{ $post?->seoInfo?->description ??  $post->meta_description}}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex justify-content-center">
                                <div class="form-group w-100">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <label class="title-color" for="meta_Image">
                                                {{ translate('meta_Image') }}
                                            </label>
                                            <span
                                                class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Meta Thumbnail'] }}</span>
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                  title="{{ translate('add_Meta_Image_in') }} JPG, PNG or JPEG {{ translate('format_within') }} 2MB, {{ translate('which_will_be_shown_in_search_engine_results') }}.">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                     alt="">
                                            </span>
                                        </div>

                                    </div>

                                    <div>
                                        <div class="custom_upload_input">
                                            <input type="file" name="meta_image"
                                                   class="custom-upload-input-file meta-img action-upload-color-image" id=""
                                                   data-imgpreview="pre_meta_image_viewer"
                                                   accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                            @if($post?->seoInfo?->image_full_url['path'] || $post->meta_image_full_url['path'])
                                                <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-flex">
                                                    <i class="tio-delete"></i>
                                                </span>
                                            @else
                                                <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                    <i class="tio-delete"></i>
                                                </span>
                                            @endif

                                            <div class="img_area_with_preview position-absolute z-index-2 d-flex">
                                                <img id="pre_meta_image_viewer" class="h-auto aspect-1 bg-white onerror-add-class-d-none" alt=""
                                                     src="{{ getStorageImages(path: $post?->seoInfo?->image_full_url['path'] ? $post?->seoInfo?->image_full_url : $post->meta_image_full_url, type: 'backend-banner') }}">
                                            </div>
                                            <div
                                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                <div
                                                    class="d-flex flex-column justify-content-center align-items-center">
                                                    <img alt=""
                                                        src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                        class="w-75">
                                                    <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('admin-views.post.product.partials._seo-update-section')

                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn--primary px-5 product-add-requirements-check">
                    @if($post->request_status == 2)
                        {{ translate('update_&_Publish') }}
                    @else
                        {{ translate(request('product-gallery') ? 'submit' : 'update') }}
                    @endif
                </button>
            </div>
            @if(request('product-gallery'))
                <input hidden name="existing_thumbnail" value="{{$post->thumbnail_full_url['key']}}">
                <input hidden name="existing_meta_image" value="{{$post?->seoInfo?->image_full_url['key'] ?? $post->meta_image_full_url['key']}}">
            @endif
        </form>
    </div>
    <span id="route-admin-products-sku-combination" data-url="{{ route('admin.products.sku-combination') }}"></span>
    <span id="route-admin-products-digital-variation-combination" data-url="{{ route('admin.products.digital-variation-combination') }}"></span>
    <span id="route-admin-products-digital-variation-file-delete" data-url="{{ route('admin.products.digital-variation-file-delete') }}"></span>
    <span id="image-path-of-product-upload-icon" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"></span>
    <span id="image-path-of-product-upload-icon-two" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"></span>
    <span id="route-admin-products-sku-translate-ai" data-url="{{ route('admin.products.translate-ai') }}"></span>

    <span id="message-enter-choice-values" data-text="{{ translate('enter_choice_values') }}"></span>
    <span id="message-upload-image" data-text="{{ translate('upload_Image') }}"></span>
    <span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }}"></span>
    <span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
    <span id="message-no-word" data-text="{{ translate('no') }}"></span>
    <span id="message-want-to-add-or-update-this-product" data-text="{{ translate('want_to_update_this_product') }}"></span>
    <span id="message-please-only-input-png-or-jpg" data-text="{{ translate('please_only_input_png_or_jpg_type_file') }}"></span>
    <span id="message-product-added-successfully" data-text="{{ translate('product_added_successfully') }}"></span>
    <span id="message-discount-will-not-larger-then-variant-price" data-text="{{ translate('the_discount_price_will_not_larger_then_Variant_Price') }}"></span>
    <span id="system-currency-code" data-value="{{ getCurrencySymbol(currencyCode: getCurrencyCode()) }}"></span>
    <span id="system-session-direction" data-value="{{ request()->cookie('direction', 'ltr') }}"></span>
    <span id="message-file-size-too-big" data-text="{{ translate('file_size_too_big') }}"></span>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/product-add-update.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/formservice.js') }}"></script>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('meta_keywords_input');
    const hiddenInput = document.getElementById('meta_keywords');
    const tagsContainer = document.getElementById('meta_keywords_tags');
    const limitInfo = document.getElementById('meta_keywords_limit');
    let keywords = [];

    // ✅ قراءة الكلمات القديمة الموجودة بالفعل
    const oldKeywords = hiddenInput.value;
    if (oldKeywords) {
        keywords = oldKeywords.split(',').map(k => k.trim()).filter(k => k.length > 0);
    }

    function renderTags() {
        tagsContainer.innerHTML = '';
        keywords.forEach((word, index) => {
            const tag = document.createElement('span');
            tag.className = 'badge badge-primary';
            tag.style.cursor = 'pointer';
            tag.innerText = word;
            tag.onclick = function () {
                keywords.splice(index, 1);
                renderTags();
            };
            tagsContainer.appendChild(tag);
        });
        hiddenInput.value = keywords.join(',');
        limitInfo.textContent = `عدد الكلمات: ${keywords.length}/20`;
    }

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = input.value.trim();
            if (value && keywords.length < 20 && !keywords.includes(value)) {
                keywords.push(value);
                input.value = '';
                renderTags();
            }
        }
    });

    renderTags();
});
</script>

    <script>
        "use strict";

        let imageCount = {{15-count(json_decode($post->images)) }};
        let thumbnail = '{{ productImagePath('thumbnail').'/'.$post->thumbnail ?? dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}';
        $(function () {
            if (imageCount > 0) {
              
            $("#thumbnail").spartanMultiImagePicker({
                fieldName: 'image',
                maxCount: 1,
                rowHeight: 'auto',
                groupClassName: 'col-12',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ productImagePath('thumbnail').'/'. $post->thumbnail ?? dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}',
                    width: '100%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function () {
                    toastr.error(messagePleaseOnlyInputPNGOrJPG, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function () {
                    toastr.error(messageFileSizeTooBig, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });

        });

        setTimeout(function () {
            $('.call-update-sku').on('change', function () {
                getUpdateSKUFunctionality();
            });
        }, 2000)

        function colorWiseImageFunctionality(t) {
            let colors = t.val();
            let color_image = $('#color_image').val() ? $.parseJSON($('#color_image').val()) : [];

            let images = $.parseJSON($('#images').val());
            let product_id = $('#product_id').val();
            let remove_url = $('#remove_url').val();

            let color_image_value = $.map(color_image, function (item) {
                return item.color;
            });

            $('#color_wise_existing_image').html('')
            $('#color-wise-image-section').html('')

            $.each(colors, function (key, value) {
                let value_id = value.replace('#', '');
                let in_array_image = $.inArray(value_id, color_image_value);
                let input_image_name = "color_image_" + value_id;
                @if(request('product-gallery'))
                    $.each(color_image, function (color_key, color_value) {
                    if ((in_array_image !== -1) && (color_value['color'] === value_id)) {
                        let image_name = color_value['image_name'];
                        let exist_image_html = `
                            <div class="col-6 col-md-4 col-xl-4 color-image-`+color_value['color']+`">
                                <div class="position-relative p-2 border-dashed-2">
                                    <div class="upload--icon-btns d-flex gap-2 position-absolute z-index-2 p-2" >
                                        <button type="button" class="btn btn-square text-white btn-sm" style="background: #${color_value['color']}"><i class="tio-done"></i></button>
                                        <button class="btn btn-outline-danger btn-sm square-btn remove-color-image-for-product-gallery" data-color="`+color_value['color']+`"><i class="tio-delete"></i></button>
                                    </div>
                                    <img class="w-100" height="auto"
                                        onerror="this.src='{{ dynamicAsset(path: 'public/assets/front-end/img/image-place-holder.png') }}'"
                                        src="${image_name['path']}"
                                        alt="Product image">
                                        <input type="text" name="color_image_`+color_value['color']+`[]" value="`+image_name['key']+`" hidden>
                                </div>
                            </div>`;
                        $('#color_wise_existing_image').append(exist_image_html)
                    }
                });
                @else
                    $.each(color_image, function (color_key, color_value) {
                    if ((in_array_image !== -1) && (color_value['color'] === value_id)) {
                        let image_name = color_value['image_name'];
                        let exist_image_html = `
                            <div class="col-6 col-md-4 col-xl-4">
                                <div class="position-relative p-2 border-dashed-2">
                                    <div class="upload--icon-btns d-flex gap-2 position-absolute z-index-2 p-2" >
                                        <button type="button" class="btn btn-square text-white btn-sm" style="background: #${color_value['color']}"><i class="tio-done"></i></button>
                                        <a href="` + remove_url + `?id=` + product_id + `&name=` + image_name['key'] + `&color=` + color_value['color'] + `"
                                    class="btn btn-outline-danger btn-sm square-btn"><i class="tio-delete"></i></a>
                                    </div>
                                    <img class="w-100" height="auto"
                                        onerror="this.src='{{ dynamicAsset(path: 'public/assets/front-end/img/image-place-holder.png') }}'"
                                        src="${image_name['path']}"
                                        alt="Product image">
                                </div>
                            </div>`;
                        $('#color_wise_existing_image').append(exist_image_html)
                    }
                });
                @endif
            });

            $.each(colors, function (key, value) {
                let value_id = value.replace('#', '');
                let in_array_image = $.inArray(value_id, color_image_value);
                let input_image_name = "color_image_" + value_id;

                if (in_array_image === -1) {
                    let html = `<div class='col-6 col-md-4 col-xl-4'>
                                    <div class="position-relative p-2 border-dashed-2">
                                        <label style='border-radius: 3px; cursor: pointer; text-align: center; overflow: hidden; position : relative; display: flex; align-items: center; margin: auto; justify-content: center; flex-direction: column;'>
                                        <span class="upload--icon" style="background: ${value}">
                                        <i class="tio-edit"></i>
                                            <input type="file" name="` + input_image_name + `" id="` + value_id + `" class="d-none" accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required="">
                                        </span>

                                        <div class="h-100 top-0 aspect-1 w-100 d-flex align-content-center justify-content-center overflow-hidden">
                                            <div class="d-flex flex-column justify-content-center align-items-center">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" class="w-75">
                                                <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                            </div>
                                        </div>
                                    </label>
                                    </div>
                                    </div>`;
                    $('#color-wise-image-section').append(html)

                    $("#color-wise-image-section input[type='file']").each(function () {

                        let thisElement = $(this).closest('label');

                        function proPicURL(input) {
                            if (input.files && input.files[0]) {
                                let uploadedFile = new FileReader();
                                uploadedFile.onload = function (e) {
                                    thisElement.find('img').attr('src', e.target.result);
                                    thisElement.fadeIn(300);
                                    thisElement.find('h3').hide();
                                };
                                uploadedFile.readAsDataURL(input.files[0]);
                            }
                        }

                        $(this).on("change", function () {
                            proPicURL(this);
                        });
                    });
                }
            });
        }

        $(document).on('click', '.remove-color-image-for-product-gallery', function(event) {
            event.preventDefault();
            let value_id = $(this).data('color');
            let value = '#'+value_id;
            let color = "color_image_" + value_id;
            let html =  `<div class="position-relative p-2 border-dashed-2">
                            <label style='border-radius: 3px; cursor: pointer; text-align: center; overflow: hidden; position : relative; display: flex; align-items: center; margin: auto; justify-content: center; flex-direction: column;'>
                                <span class="upload--icon" style="background: ${value}">
                                <i class="tio-edit"></i>
                                    <input type="file" name="` + color + `" id="` + value_id + `" class="d-none" accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required="">
                                </span>

                                <div class="h-100 top-0 aspect-1 w-100 d-flex align-content-center justify-content-center overflow-hidden">
                                    <div class="d-flex flex-column justify-content-center align-items-center">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" class="w-75">
                                        <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                    </div>
                                </div>
                            </label>
                        </div>`;
            $('.color-image-'+value_id).empty().append(html);
            $("#color-wise-image-area input[type='file']").each(function () {

                let thisElement = $(this).closest('label');

                function proPicURL(input) {
                    if (input.files && input.files[0]) {
                        let uploadedFile = new FileReader();
                        uploadedFile.onload = function (e) {
                            thisElement.find('img').attr('src', e.target.result);
                            thisElement.fadeIn(300);
                            thisElement.find('h3').hide();
                        };
                        uploadedFile.readAsDataURL(input.files[0]);
                    }
                }

                $(this).on("change", function () {
                    proPicURL(this);
                });
            });
        })
        $('.remove-addition-image-for-product-gallery').on('click',function (){
            $('#'+$(this).data('section-remove-id')).remove();
        })

        $(document).ready(function () {
            setTimeout(function () {
                let category = $("#category_id").val();
                let sub_category = $("#sub-category-select").attr("data-id");
                let sub_sub_category = $("#sub-sub-category-select").attr("data-id");
                getRequestFunctionality('{{ route('admin.products.get-categories') }}?parent_id=' + category + '&sub_category=' + sub_category, 'sub-category-select', 'select');
                getRequestFunctionality('{{ route('admin.products.get-categories') }}?parent_id=' + sub_category + '&sub_category=' + sub_sub_category, 'sub-sub-category-select', 'select');
            }, 100)
        });
        updateProductQuantity();

    </script>
@endpush
