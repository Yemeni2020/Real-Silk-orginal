@extends('layouts.back-end.app')

@section('title', translate('category'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/brand-setup.png') }}" class="mb-1 mr-1" alt="">
                
                {{ translate('Adv') }}
                {{ translate('update') }}
            </h2>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-body text-start">
                    <form action="{{ route('admin.adver.update',[$Adv['id']]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <ul class="nav nav-tabs w-fit-content mb-4">
                                @foreach($languages as $lang)
                                    <li class="nav-item text-capitalize">
                                        <span
                                            class="nav-link form-system-language-tab cursor-pointer {{ $lang == $defaultLanguage? 'active':''}}"
                                            id="{{ $lang}}-link">
                                            {{ucfirst(getLanguageName($lang)).'('.strtoupper($lang).')'}}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div>
                                        @foreach($languages as $lang)
                                        <div>
                                                <?php
                                                if (count($Adv['translations'])) {
                                                    $translate = [];
                                                    foreach ($Adv['translations'] as $t) {
                                                        if ($t->locale == $lang && $t->key == "name") {
                                                            $translate[$lang]['name'] = $t->value;
                                                        }
                                                    }
                                                }
                                                ?>
                                                
                                            <div class="form-group {{ $lang != $defaultLanguage ? 'd-none':''}} form-system-language-form"
                                                id="{{ $lang}}-form">
                                                <label class="title-color">
                                                    {{ translate('adv_Name') }} ({{strtoupper($lang) }})
                                                </label>
                                                <input type="text" name="name[]"
                                                       value="{{ $lang==$defaultLanguage?$Adv['title']:($translate[$lang]['name']??'') }}"
                                                       class="form-control"
                                                       placeholder="{{ translate('new_Adv') }}" {{ $lang == $defaultLanguage? 'required':''}}>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang}}">
                                            <input type="hidden" name="id" value="{{ $Adv['id']}}">
                                        </div>
                                        @endforeach
                                        <input name="position" value="0" class="d-none">
                                    </div>
                                    <div class="form-group">
                                        <label class="title-color" for="priority">{{ translate('priority') }}
                                            <span>
                                            <i class="tio-info-outined" data-toggle="tooltip" data-placement="top"
                                               title="{{ translate('the_lowest_number_will_get_the_highest_priority') }}"></i>
                                            </span>
                                        </label>

                                        <select class="form-control" name="priority" id="" required>
                                            <option disabled selected>{{ translate('set_Priority') }}</option>
                                            @for ($i = 0; $i <= 10; $i++)
                                                <option value="{{ $i}}" {{ $Adv['priority']==$i?'selected':''}}>{{ $i}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="name" class="title-color">
                                            {{ translate('category') }}
                                            <span class="input-required-icon">*</span>
                                        </label>
                                        <select class="js-select2-custom form-control action-get-request-onchange" name="category_id"
                                                data-url-prefix="{{ url('/admin/products/get-categories?parent_id=') }}"
                                                data-element-id="sub-category-select"
                                                data-element-type="select"
                                                required>
                                            <option value="{{ old('category_id') }}" selected
                                                    disabled>{{ translate('select_category') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category['id'] }}"
                                                    {{ $Adv['id'] == $category['id'] ? 'selected' : '' }}>
                                                    {{ $category['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="title-color">
                                            {{ translate('Link') }}
                                            <span class="input-required-icon">*</span>
                                        </label>
                                        <input type="text" name="_link" value="{{$Adv['link']}}"  class="form-control" id="_link">
                                    </div>
                                    <div class="form-group">
                                        <label class="title-color">{{ translate('Show_Menu') }}</label>


                                        <label class="switcher mx-auto">
                                                <input type="checkbox" class="switcher_input toggle-switch-message"
                                                       name="status"
                                                       id="show_menu" data-modal-id="toggle-status-modal"
                                                        data-toggle-id="show_menu"
                                                        <?php echo $Adv['status']==1?"checked":"" ?>
                                                    class="switcher_input toggle-switch-message"
                                                        data-on-image="category-status-on.png"
                                                    data-off-image="category-status-off.png" value="1">
                                                <span class="switcher_control"></span>
                                            </label>
                                    </div>

                                    <div class="from_part_2">
                                        <label class="title-color">{{ translate('category_Logo') }}</label>
                                        <span class="text-info"><span class="text-danger">*</span> {{ THEME_RATIO[theme_root_path()]['Category Image'] }}</span>
                                        <div class="custom-file text-left">
                                            <input type="file" name="image" id="category-image"
                                                   class="custom-file-input image-preview-before-upload"
                                                   data-preview="#viewer"
                                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                   >
                                            <label class="custom-file-label"
                                                   for="category-image">{{ translate('choose_File') }}</label>
                                        </div>
                                    </div>
                                    
                                    
                                    
                                </div>
                                <div class="col-lg-6 mt-5 mt-lg-0 from_part_2">
                                    <div class="form-group">
                                        <div class="text-center mx-auto">
                                            <img class="upload-img-view"
                                                 id="viewer"
                                                 src="{{ getStorageImages(path: $Adv->icon_full_url , type: 'backend-basic') }}"
                                                 alt=""/>
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <button type="reset" id="reset"
                                        class="btn btn-secondary">{{ translate('reset') }}</button>
                                <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/products-management.js') }}"></script>
@endpush
