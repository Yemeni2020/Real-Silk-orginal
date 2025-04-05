@php use App\Enums\ViewPaths\Admin\FeaturesSection;use App\Enums\ViewPaths\Admin\Pages; @endphp

@extends('layouts.back-end.app')

@section('title', translate('contracts'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')


<div class="content container-fluid">

    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/Pages.png')}}" alt="">
            {{translate('pages')}}
        </h2>
    </div>
    @include('admin-views.business-settings.pages-inline-menu')
    <div class="row">
        <div class="col-md-12">
            <div class="inline-page-menu my-4">
                <ul class="list-unstyled">
                    <li class="{{ Request::is('admin/business-settings/'.Pages::CONTRACTS[URI].'/factory') ?'active':'' }}">
                        <a href="{{route('admin.business-settings.contracts',['type'=>'factory'])}}">{{translate('factory')}}</a>
                    </li>
                    <li class="{{ Request::is('admin/business-settings/'.Pages::CONTRACTS[URI].'/office') ?'active':'' }}">
                        <a href="{{route('admin.business-settings.contracts',['type'=>'office'])}}">{{translate('office')}}</a>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{translate($type.'_contract')}}</h5>
                    <a href="{{route('admin.business-settings.download_contract',['type'=>$type])}}" title="{{translate('download')}}" class="btn-outline-success text-success __action-btn btn-shadow rounded-full">
                            <i class="tio-download-to"></i>
                    </a>
                    <button type="button" id="show-contract-btn" class="btn btn-primary">
                        عرض العقد والموافقة
                    </button>
                </div>

                

                <form action="{{route('admin.business-settings.contracts',['type'=>$type])}}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control summernote" id="editor"
                                name="value">{{$contract?->value}}</textarea>
                                <input type="hidden" name="type" value="{{$type}}">
                        </div>
                        <div class="form-group">
                            <input class="form-control btn--primary" type="submit" value="{{translate('submit')}}" name="btn">
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div id="contract-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📜 {{translate("contract_show")}}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="contract-frame" src="{{route('admin.business-settings.View_contract',['type'=>$type])}}" width="100%" height="500px"></iframe>

                <div class="form-check mt-3">
                    <input type="checkbox" id="agree-checkbox" class="form-check-input">
                    
                </div>
            </div>
            <div class="modal-footer">
                
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                {{translate("cancel")}}
                </button>
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
    <script>
        document.getElementById("show-contract-btn").addEventListener("click", function() {
            // ✅ تحميل ملف PDF داخل النافذة المنبثقة
            
            document.getElementById("contract-frame").src = "{{route('admin.business-settings.View_contract',['type'=>$type])}}";
            $("#contract-modal").modal("show");
        });

        document.getElementById("agree-checkbox").addEventListener("change", function() {
            document.getElementById("agree-btn").disabled = !this.checked; // تفعيل زر الموافقة عند تحديد الصندوق
        });

        document.getElementById("agree-btn").addEventListener("click", function() {
            $("#contract-modal").modal("hide"); // إغلاق النافذة
            document.getElementById("submit-btn").disabled = false; // تفعيل زر التسجيل
        });
    </script>
@endpush
