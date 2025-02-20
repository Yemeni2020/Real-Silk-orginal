@extends('layouts.back-end.app')

@section('title', translate('privacy_policy'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/Pages.png')}}" width="20" alt="">
                {{translate('pages')}}
            </h2>
        </div>
        @include('admin-views.business-settings.pages-inline-menu')
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{translate('privacy_policy')}}</h5>
                    </div>
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
                        <form action="{{route('admin.business-settings.privacy-policy-update')}}" method="post">
                            @csrf

                            @foreach($languages as $language)
                                    <?php
                                    if (count($privacy_policy['translations'])) {
                                        $translate = [];
                                        foreach ($privacy_policy['translations'] as $translation) {
                                            if ($translation->locale == $language && $translation->key == "value") {
                                                $translate[$language]['value'] = $translation->value;
                                            }
                                            
                                        }
                                    }
                                    ?>
                                <div class="{{ $language != $curnnet_lang? 'd-none':''}} form-system-language-form" id="{{ $language}}-form">
                                    <div class="form-group">
                                        <textarea class="form-control summernote {{ $language == $curnnet_lang ? 'product-description-default-language' : '' }}" id="editor"
                                            name="value[]">{!! $translate[$language]['value']??$privacy_policy['value'] !!}</textarea>
                                    </div>

                                    <input type="hidden" name="lang[]" value="{{ $language}}">

                                    
                                </div>
                            @endforeach
                            <div class="form-group">
                                    <input class="form-control btn--primary" type="submit" value="{{translate('submit')}}" name="btn">
                                </div>
                        </form>
                    </div>

                    <!-- <form action="{{route('admin.business-settings.privacy-policy-update')}}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control summernote" id="editor" name="value">{{$privacy_policy->value}}</textarea>
                            </div>
                            <div class="form-group">
                                <input class="form-control btn--primary" type="submit" value="{{translate('submit')}}" name="btn">
                            </div>
                        </div>
                    </form> -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
    <script>
        'use strict';
        $(document).on('ready', function () {
            $('.summernote').summernote({
                'height': 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                ]
            });
        });
    </script>
@endpush

