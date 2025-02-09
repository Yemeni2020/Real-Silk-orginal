@extends('layouts.back-end.app')

@section('title', translate('category'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/brand-setup.png') }}" class="mb-1 mr-1" alt="">
                @if($category['position'] == 1)
                    {{ translate('sub') }}
                @elseif($category['position'] == 2)
                    {{ translate('sub_Sub') }}
                @endif
                {{ translate('category') }}
                {{ translate('update') }}
            </h2>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-body text-start">
                        <form action="{{ route('admin.category.update', [$category['id']]) }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <ul class="nav nav-tabs w-fit-content mb-4">
                                @foreach($languages as $lang)
                                    <li class="nav-item text-capitalize">
                                        <span
                                            class="nav-link form-system-language-tab cursor-pointer {{ $lang == $defaultLanguage? 'active':''}}"
                                            id="{{ $lang}}-link">
                                            {{ getLanguageName($lang).'('.strtoupper($lang).')'}}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="row">
                                <div
                                    class="{{ $category['parent_id']==0 || $category['position'] == 1 ? 'col-lg-6':'col-12' }}">
                                    @foreach($languages as $lang)
                                        <div>
                                                <?php
                                                if (count($category['translations'])) {
                                                    $translate = [];
                                                    foreach ($category['translations'] as $t) {
                                                        if ($t->locale == $lang && $t->key == "name") {
                                                            $translate[$lang]['name'] = $t->value;
                                                        }
                                                    }
                                                }
                                                ?>
                                                
                                            <div class="form-group {{ $lang != $defaultLanguage ? 'd-none':''}} form-system-language-form"
                                                id="{{ $lang}}-form">
                                                <label class="title-color">
                                                    {{ translate('category_Name') }} ({{strtoupper($lang) }})
                                                </label>
                                                <input type="text" name="name[]"
                                                       value="{{ $lang==$defaultLanguage?$category['name']:($translate[$lang]['name']??'') }}"
                                                       class="form-control"
                                                       placeholder="{{ translate('new_Category') }}" {{ $lang == $defaultLanguage? 'required':''}}>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang}}">
                                            <input type="hidden" name="id" value="{{ $category['id']}}">
                                        </div>
                                    @endforeach
                                                
                                    <div class="form-group">
                                        <label class="title-color" for="priority">{{ translate('priority') }}</label>
                                        <select class="form-control" name="priority" id="" required>
                                            @for ($index = 0; $index <= 10; $index++)
                                                <option
                                                    value="{{ $index }}" {{ $category['priority']==$index?'selected':''}}>{{ $index }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="from_part_2">
                                                    <label class="title-color">
                                                        {{ translate('Parent_Category')  }}
                                                    </label>
                                                    <select class="form-control" name="parent" id="a">
                                                        <option value="0"></option>
                                                        @foreach($category_main as $cat)
                                                            @if($category['id']!=$cat['id'])
                                                            <option <?php if ($category['parent_id']==$cat['id'] ) echo "selected"; ?> value="{{$cat['id']}}">{{$cat['name']}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                    @if($category['parent_id']==0 || ($category['position'] == 1 && theme_root_path() == 'theme_aster'))
                                        <div class="from_part_2">
                                            <label class="title-color">{{ translate('category_Logo') }}</label>
                                            <span class="text-info">({{ translate('ratio') }} 1:1)</span>
                                            <div class="custom-file text-left">
                                                <input type="file" name="image" id="category-image"
                                                       class="custom-file-input image-preview-before-upload"
                                                       data-preview="#viewer"
                                                       accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="custom-file-label"
                                                       for="category-image">{{ translate('choose_File') }}</label>
                                            </div>
                                        </div>
                                        <div class="from_part_2">
                                            <label class="title-color">{{ translate('category_Adv') }}</label>
                                            <span class="text-info">({{ translate('ratio') }} 1:2)</span>
                                            <div class="custom-file text-left">
                                                <input type="file" name="image-ad" id="category-image"
                                                       class="custom-file-input image-preview-before-upload"
                                                       data-preview="#viewer-ad"
                                                       accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="custom-file-label"
                                                       for="category-image">{{ translate('choose_File') }}</label>
                                            </div>
                                        </div>
                                        <div class="from_part_2">
                                            <label class="title-color">{{ translate('Show_Menu') }}</label>
                                            <div class="custom-file text-left">
                                            <input type="checkbox" name="show_menu" id="show_menu"   data-modal-id="toggle-status-modal"
                                                        data-toggle-id="show_menu"
                                                        <?php echo $category['menu']==1?"checked":"" ?>
                                                    class="switcher_input toggle-switch-message"
                                                        data-on-image="category-status-on.png"
                                                    data-off-image="category-status-off.png"
                                                    
                                                    >
                                                <label onclick="$('#show_menu').prop('checked', function(_, checked) { return !checked; });" class="switcher_control" style="cursor: pointer;"
                                                    ></label>
                                            </div>
                                        </div>
                                </div>
                                <div class="col-lg-6 mt-5 mt-lg-0 from_part_2">
                                    <div class="form-group">
                                        <div class="text-center mx-auto">
                                            <img class="upload-img-view"
                                                 id="viewer"
                                                 src="{{ getStorageImages(path: $category->icon_full_url , type: 'backend-basic') }}"
                                                 alt=""/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="text-center mx-auto">
                                            <img class="upload-img-view"
                                                 id="viewer-ad"
                                                 src="{{ getStorageImages(path: $category->adv_full_url , type: 'backend-basic') }}"
                                                 alt=""/>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($category['position'] == 2 || ($category['position'] == 1 && theme_root_path() != 'theme_aster'))
                                    <div class="d-flex justify-content-end gap-3">
                                        <button type="reset" id="reset" class="btn btn-secondary px-4">
                                            {{ translate('reset') }}
                                        </button>
                                        <button type="submit" class="btn btn--primary px-4">
                                            {{ translate('update') }}
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if($category['parent_id']==0 || ($category['position'] == 1 && theme_root_path() == 'theme_aster'))
                                <div class="d-flex justify-content-end gap-3">
                                    <button type="reset" id="reset"
                                            class="btn btn-secondary px-4">{{ translate('reset') }}</button>
                                    <button type="submit"
                                            class="btn btn--primary px-4">{{ translate('update') }}</button>
                                </div>
                            @endif
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
