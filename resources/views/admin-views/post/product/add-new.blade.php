@php

$curnnet_lang = getDefaultLanguage();
$OpenAi=getWebConfig("OpenAi_translate");
$DeepSeekAI=getWebConfig("DeepSeekAI_translate");
$deepl=getWebConfig("deepl_translate");
$defulte_translate=getWebConfig("defulte_translate");
$auto_translate=getWebConfig("auto_translate");

@endphp
@extends('layouts.back-end.app')

@section('title', translate('product_Add'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 d-flex gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('add_New_Product') }}
            </h2>
        </div>

        <form class="product-form text-start" action="{{ route('admin.products.store') }}" method="POST"
              enctype="multipart/form-data" id="product_form">
            @csrf
            <div class="card">
                
                <div class="px-4 pt-3 d-flex justify-content-between">
                    <ul class="nav nav-tabs w-fit-content mb-4">
                        @foreach ($languages as $lang)
                            <li class="nav-item">
                                <span class="nav-link text-capitalize form-system-language-tab {{ $lang == $curnnet_lang ? 'active' : '' }} cursor-pointer"
                                      id="{{ $lang }}-link">{{ getLanguageName($lang) . '(' . strtoupper($lang) . ')' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a class="btn btn--primary btn-sm text-capitalize h-100" href="{{route('admin.products.product-gallery') }}">
                        {{translate('add_info_from_gallery')}}
                    </a>
                </div>

                <div class="card-body">
                    

                    @foreach ($languages as $lang)
                        <div class="{{ $lang != $curnnet_lang ? 'd-none' : '' }} form-system-language-form"
                             id="{{ $lang }}-form">
                            <div class="form-group">
                                <label class="title-color"
                                       for="{{ $lang }}_name">{{ translate('product_name') }}
                                    ({{ strtoupper($lang) }})
                                    @if($lang == $curnnet_lang)
                                        <span class="input-required-icon">*</span>
                                    @endif
                                </label>
                                @if($lang != $curnnet_lang)
                                        <button class="btn btn-primary translate_ai" type="button" data-lang="{{$lang}}"  >{{translate('translate_ai')}}</button>
                                @endif
                                
                                <input type="text"  {{ $lang == $curnnet_lang ? 'required' : '' }} name="name[]"
                                       id="{{ $lang }}_name" class="form-control  {{ $lang == $curnnet_lang ? 'product-title-default-language' : '' }} " placeholder="{{ translate('new_Product') }}">
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                            <div class="form-group pt-2">
                                <label class="title-color" for="{{ $lang }}_description">
                                    {{ translate('description') }} ({{ strtoupper($lang) }})
                                    @if($lang == $curnnet_lang)
                                        <span class="input-required-icon">*</span>
                                    @endif
                                </label>
                                <textarea id="{{ $lang }}_description" class="summernote {{ $lang == $curnnet_lang ? 'product-description-default-language' : '' }}" name="description[]">{{ old('details') }}</textarea>
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
                                                title="{{ translate('add_your_product’s_thumbnail_in') }} JPG, PNG or JPEG {{ translate('format_within') }} 2MB">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                    alt="">
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom_upload_input">
                                        <input type="file" name="image" class="custom-upload-input-file action-upload-color-image" id=""
                                                data-imgpreview="pre_img_viewer"
                                                accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                        <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                            <i class="tio-delete"></i>
                                        </span>

                                        <div class="img_area_with_preview position-absolute z-index-2">
                                            <img id="pre_img_viewer" class="h-auto aspect-1 bg-white d-none"
                                                    src="dummy" alt="">
                                        </div>
                                        <div
                                            class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                            <div
                                                class="d-flex flex-column justify-content-center align-items-center">
                                                <img alt="" class="w-75"
                                                        src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                                <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-muted mt-2 fz-12">
                                        {{ translate('image_format') }} : {{ "Jpg, png, jpeg, webp," }}
                                        <br>
                                        {{ translate('image_size') }} : {{ translate('max') }} {{ "2 MB" }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="color_image_column item-2">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                        <div>
                                            <label for="name"
                                                   class="title-color text-capitalize font-weight-bold mb-0">{{ translate('colour_wise_product_image') }}</label>
                                            <span
                                                class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Product Image'] }}</span>
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                  title="{{ translate('add_color-wise_product_images_here') }}.">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                     alt="">
                                            </span>
                                        </div>

                                    </div>
                                    <p class="text-muted">{{ translate('must_upload_colour_wise_images_first._Colour_is_shown_in_the_image_section_top_right') }}
                                        . </p>

                                    <div id="color-wise-image-section" class="row g-2"></div>
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
                                <div class="row g-2 mt-1" id="additional_Image_Section">
                                    <div class="col-sm-12 col-md-4">
                                        <div class="custom_upload_input position-relative border-dashed-2">
                                            <input type="file" name="images[]" class="custom-upload-input-file action-add-more-image"
                                                   data-index="1" data-imgpreview="additional_Image_1"
                                                   accept=".jpg, .png, .webp, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                   data-target-section="#additional_Image_Section"
                                            >

                                            <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                                <i class="tio-delete"></i>
                                            </span>

                                            <div class="img_area_with_preview position-absolute z-index-2 border-0">
                                                <img id="additional_Image_1" class="h-auto aspect-1 bg-white d-none "
                                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" alt="">
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

                    <div class="item-1 digital-product-sections-show">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <div>
                                            <label for="name" class="title-color text-capitalize font-weight-bold mb-0">{{ translate('Product_Preview_File') }}</label>
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                  title="{{ translate('upload_a_suitable_file_for_a_short_product_preview.') }} {{ translate('this_preview_will_be_common_for_all_variations.') }}">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-muted">{{ translate('Upload_a_short_preview') }}.</p>
                                </div>
                                <div class="image-uploader">
                                    <input type="file" name="preview_file" class="image-uploader__zip" id="input-file">
                                    <div class="image-uploader__zip-preview">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" class="mx-auto" width="50" alt="">
                                        <div class="image-uploader__title line--limit-2">
                                            {{ translate('Upload_File') }}
                                        </div>
                                    </div>
                                    <span class="btn btn-outline-danger btn-sm square-btn collapse zip-remove-btn">
                                        <i class="tio-delete"></i>
                                    </span>
                                </div>
                                <p class="text-muted mt-2 fz-12">
                                    {{ translate('Format') }} : {{ " pdf, mp4, mp3" }}
                                    <br>
                                    {{ translate('image_size') }} : {{ translate('max') }} {{ "10 MB" }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('post_video') }}</h4>
                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                              title="{{ translate('add_the_YouTube_video_link_here._Only_the_YouTube-embedded_link_is_supported') }}.">
                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="title-color mb-0">
                            {{ translate('youtube_video_link') }}
                        </label>
                        <span class="text-info"> ({{ translate('optional_please_provide_embed_link_not_direct_link') }}.)</span>
                    </div>
                    <input type="text" name="video_url"
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
                                <input type="text" name="meta_title" placeholder="{{ translate('meta_Title') }}"
                                       class="form-control" id="meta_title">
                            </div>
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate('meta_kay_words') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          data-placement="top"
                                          title="{{ translate('add_the_products_title_name_taglines_etc_here').' '.translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </label>
                                <input type="text" name="meta_title" placeholder="{{ translate('meta_Title') }}"
                                       class="form-control" id="meta_title">
                            </div>
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate('meta_Description') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          data-placement="top"
                                          title="{{ translate('write_a_short_description_of_the_InHouse_shops_product').' '.translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 160 ]">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </label>
                                <textarea rows="4" type="text" name="meta_description" id="meta_description" class="form-control"></textarea>
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
                                                   class="custom-upload-input-file meta-img action-upload-color-image"
                                                   data-imgpreview="pre_meta_image_viewer"
                                                   id="meta_image_input"
                                                   accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                <i class="tio-delete"></i>
                                            </span>

                                            <div class="img_area_with_preview position-absolute z-index-2 d-flex align-items-center justify-content-center">
                                                <img id="pre_meta_image_viewer" class="h-auto bg-white onerror-add-class-d-none pre-meta-image-viewer" alt=""
                                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg-dummy') }}">
                                            </div>
                                            <div
                                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center overflow-hidden">
                                                <div
                                                    class="d-flex flex-column justify-content-center align-items-center">
                                                    <img alt="" class="w-75"
                                                         src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                                    <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    @include('admin-views.product.partials._seo-section')
                </div>
            </div>

            <div class="row justify-content-end gap-3 mt-3 mx-1">
                <button type="reset" class="btn btn-secondary px-5">{{ translate('reset') }}</button>
                <button type="button" class="btn btn--primary px-5 product-add-requirements-check">
                    {{ translate('submit') }}
                </button>
            </div>
        </form>
    </div>
    @include('admin-views.product.partials.FormServiceModel')

    <span id="route-admin-products-sku-combination" data-url="{{ route('admin.products.sku-combination') }}"></span>
    <span id="route-admin-products-sku-translate-ai" data-url="{{ route('admin.products.translate-ai') }}"></span>
    <span id="route-admin-products-digital-variation-combination" data-url="{{ route('admin.products.digital-variation-combination') }}"></span>
    <span id="image-path-of-product-upload-icon" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"></span>
    <span id="image-path-of-product-upload-icon-two" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"></span>
    <span id="message-enter-choice-values" data-text="{{ translate('enter_choice_values') }}"></span>
    <span id="message-upload-image" data-text="{{ translate('upload_Image') }}"></span>
    <span id="message-file-size-too-big" data-text="{{ translate('file_size_too_big') }}"></span>
    <span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }}"></span>
    <span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
    <span id="message-no-word" data-text="{{ translate('no') }}"></span>
    <span id="message-want-to-add-or-update-this-product" data-text="{{ translate('want_to_add_this_product') }}"></span>
    <span id="message-please-only-input-png-or-jpg" data-text="{{ translate('please_only_input_png_or_jpg_type_file') }}"></span>
    <span id="message-product-added-successfully" data-text="{{ translate('product_added_successfully') }}"></span>
    <span id="message-discount-will-not-larger-then-variant-price" data-text="{{ translate('the_discount_price_will_not_larger_then_Variant_Price') }}"></span>
    <span id="system-currency-code" data-value="{{ getCurrencySymbol(currencyCode: getCurrencyCode()) }}"></span>
    <span id="system-session-direction" data-value="{{ request()->cookie('direction', 'ltr') }}"></span>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/product-add-update.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/product-add-colors-img.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/formservice.js') }}"></script>
    <script>
         
        // document.getElementById('addItemSelectButton').addEventListener('click',addItemSelectButton2());
    </script>

@endpush
