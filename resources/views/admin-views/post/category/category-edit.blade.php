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
                        <form action="{{ route('admin.post.category.update', [$category['id']]) }}" method="POST"
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
                                                
                                    

                                
                                </div>
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
