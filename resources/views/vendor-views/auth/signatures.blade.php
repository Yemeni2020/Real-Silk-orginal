<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{{ csrf_token() }}">

    <title>{{translate('Sign the contract')}}</title>

    <link rel="shortcut icon" href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">

    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/custom.css')}}">
</head>
<body>
<main id="content" role="main" class="main">
    <div class="row">
        <div class="col-12 position-fixed z-9999 mt-10rem">
            <div id="loading" class="d--none">
                <div id="loader"></div>
            </div>
        </div>
    </div>
    <div class="position-fixed top-0 right-0 left-0 bg-img-hero __h-32rem">
        <figure class="position-absolute right-0 bottom-0 left-0">
            <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1921 273">
                <polygon fill="#fff" points="0,273 1921,273 1921,0 "/>
            </svg>
        </figure>
    </div>
    <div class="container py-5 py-sm-7">
        @php($ecommerceLogo=getWebConfig('company_web_logo'))
        <a class="d-flex justify-content-center mb-5" href="javascript:">
            <img class="z-index-2 __w-8rem" src="{{ getStorageImages(path:$ecommerceLogo,type: 'backend-logo') }}" alt="{{translate('logo')}}">
        </a>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <h2 class="h3 mb-4">{{translate('Sign the contract').'?'}}</h2>
                <p class="font-size-md">{{translate('follow_steps')}}</p>
                <ol class="list-unstyled font-size-md">
                    <li><span class="text-primary mr-2">1.</span>{{translate('Sign_the_contract_in_the_box_at_the_bottom_of_the_page').'.'}}</li>
                    <li>
                        <span class="text-primary mr-2">2.</span>{{translate('Then click the save button to save your signature').'.'}}
                    </li>
                    <li>
                        <span class="text-primary mr-2">3.</span>{{translate('Then submit your signature').'.'}}
                    </li>
                </ol>
                

                    
                <!-- My Code -->
                
                <div class="card">
                    <iframe id="contract-frame" src="{{route('vendor.auth.registration.contract',['type'=>$seller?->type_account,'fullname'=>$seller->f_name.' '.$seller->l_name])}}" width="100%" height="500px"></iframe>

                    <!-- ✅ مربع الموافقة -->
                    <div class="form-check mt-3">
                        <input type="checkbox" disabled id="agree-checkbox" class="form-check-input">
                        <span class="form-check-label">{{ translate('i_agree_with_the') }} <a
                                href="#" >
                                {{ translate('terms_&_conditions') }}
                            </a>
                        </span>

                    </div>
                    <div class="row">
                        <div class="col-6">
                            <canvas id="signature-pad" width="400" height="200" style="border: 1px solid #000;"></canvas>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <button type="button" class="btn btn-success col-4" id="save-signature">{{translate('save')}} </button>
    
                                <button type="button" class="btn btn-danger col-4" id="clear-signature">{{translate('clear')}}</button>

                            </div>

                        </div>

                    </div>

                    <div class="container">

                        <form class="row" method="post" action="{{route('vendor.dashboard.signatures')}}">
                            @csrf
                            <input type="hidden" id="signature-data" name="signature">
    
                            <button type="submit" class="btn btn-primary form-control" name="submit" disabled="true" id="agree-btn">{{translate('submit')}} </button>

                        </form>

                    </div>
                </div>
                <!-- End My Code -->

            </div>
        </div>
    </div>
 
</main>
<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/app-script.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/confirm_email.js')}}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/custom.js') }}"></script>
<script src="{{ theme_asset(path: 'public/js/signature_pad.umd.min.js') }}"></script>

<script>
    
document.getElementById("agree-checkbox").addEventListener("change", function() {
        document.getElementById("agree-btn").disabled = !this.checked;
        this.disabled=true;
        if (!$(this).is(':checked')) {
            $('#vendor-apply-submit').attr('disabled');
        }
    });


var canvas = document.getElementById("signature-pad");
var signaturePad = new SignaturePad(canvas);

document.getElementById("save-signature").addEventListener("click", function () {
    if (!signaturePad.isEmpty()) {
        document.getElementById("signature-data").value = signaturePad.toDataURL();
        document.getElementById("agree-checkbox").checked = true;
        document.getElementById("agree-checkbox").disabled = false;
        document.getElementById("agree-btn").disabled = !document.getElementById("agree-checkbox").checked;

        $('#vendor-apply-submit').removeAttr('disabled');
        $("#closemodel").click();
    }
});
document.getElementById("clear-signature").addEventListener("click", function () {
    signaturePad.clear()
    document.getElementById("signature-data").value = null;
    document.getElementById("agree-checkbox").checked = false;
    document.getElementById("agree-checkbox").disabled = true;
    document.getElementById("agree-btn").disabled = !document.getElementById("agree-checkbox").checked;

    $('#vendor-apply-submit').attr('disabled');

});
</script>
{!! Toastr::message() !!}
@if ($errors->any())
    <script>
        "use strict";
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
</body>
</html>

