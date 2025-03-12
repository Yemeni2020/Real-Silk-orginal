<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{{ csrf_token() }}">

    <title>{{translate('Email_verification')}}</title>

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
                <h2 class="h3 mb-4">{{translate('Email_verification').'?'}}</h2>
                <p class="font-size-md">{{translate('follow_steps')}}</p>
                <ol class="list-unstyled font-size-md">
                    <li><span class="text-primary mr-2">1.</span>{{translate('fill_in_your_email_address_below').'.'}}</li>
                    <li>
                        <span class="text-primary mr-2">2.</span>{{translate('we_will_send_email you a temporary code').'.'}}
                    </li>
                    <li>
                        <span class="text-primary mr-2">3.</span>{{translate('use_the_code_to_Email_verification_on_our_secure_website').'.'}}
                    </li>
                </ol>
                

                    
                <!-- My Code -->

                <div class="card py-2 mt-4" id="c-email" >
                    


                    
                    
                    <form class="card-body needs-validation" id="seller-registration-step1" action="{{route('vendor.dashboard.confirm_email')}}"
                            method="post">
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    
                                    <label for="recover-email">{{translate('enter_your_email_address')}}</label>
                                    <input class="form-control" type="email" value="{{auth('seller')->user()->email}}" name="email" id="email" required>
                                    


                                    <div class="invalid-feedback mail-error">{{translate('please_provide_valid_email_address.')}}</div>
                                </div>
                            </div>

                        </div>
                        <!-- My Code -->
                        <input type="hidden" name="verificationBy" value="email">
                        <!-- End My Code -->
                        <button class="btn btn-primary proceed-to-next-btn" type="button">{{translate('Confirm_Email')}}</button>
                    </form>

                    <div class="row">
                        <div class="topbar-text dropdown col-6 disable-autohide  __language-bar text-capitalize">
                            <a class="topbar-link dropdown-toggle" href="#" data-toggle="dropdown">
                                @foreach(json_decode($language['value'],true) as $data)
                                    @if($data['code'] == getDefaultLanguage())
                                        <img class="mr-2" width="20"
                                                src="{{theme_asset(path: 'public/assets/front-end/img/flags/'.$data['code'].'.png')}}"
                                                alt="{{$data['name']}}">
                                        {{$data['name']}}
                                    @endif
                                @endforeach
                            </a>
                            <ul class="text-align-direction dropdown-menu dropdown-menu-{{request()->cookie('direction', 'ltr') === "rtl" ? 'right' : 'left'}}">
                                @foreach(json_decode($language['value'],true) as $key =>$data)
                                    @if($data['status']==1)
                                        <li class="change-language" data-action="{{route('change-language')}}" data-language-code="{{$data['code']}}">
                                            <a class="dropdown-item pb-1" href="javascript:">
                                                <img class="mr-2"
                                                        width="20"
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/flags/'.$data['code'].'.png')}}"
                                                        alt="{{$data['name']}}"/>
                                                <span class="text-capitalize">{{$data['name']}}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-6">
                        <a href="{{route('vendor.auth.logout')}}">{{translate('logout')}}</a>

                        </div>
                    </div>
                </div>
                <!-- End My Code -->

            </div>
        </div>
    </div>
    <div class="modal fade verification-modal" id="verification-modal" tabindex="-1" aria-labelledby="verification-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close">
                        <i class="tio-clear"></i>
                    </button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
                        <img src="{{theme_asset(path: 'public/assets/front-end/img/verification.png')}}" width="70" class="mb-3" alt="">

                        <h5 class="modal-title">{{ translate('Enter Verification Code') }}</h5>

                        <p class="text-muted">{{ translate('Please enter the verification code sent to your email.') }}</p>

                        <!-- إدخال رمز التحقق -->
                        <input type="text" id="verification-code" class="form-control text-center" placeholder="{{ translate('Enter Code') }}" maxlength="6" style="max-width: 200px;">
                        <label for="verification-code" id="error-verification-code"></label>
                        
                        <button id="confirm-code" data-url="{{route('vendor.dashboard.confirmMail')}}" class="btn btn-primary mt-3">
                            {{ translate('confirm') }}
                        </button>
                        <!-- زر إعادة إرسال الرمز -->
                        <button id="resend-code" onclick="$('.proceed-to-next-btn').click();"  class="btn btn-info mt-3">
                            {{ translate('Resend Code') }}
                        </button>
                        

                        <!-- رسالة نجاح بعد إرسال الرمز -->
                        <div id="verification-message" class="text-success mt-2" style="display: none;">
                            {{ translate('A new verification code has been sent.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade password-reset-successfully-modal" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close"><i class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
                        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/password-reset.png')}}" width="70" class="mb-3 mb-20" alt="">
                        <h5 class="modal-title">{{translate('password_reset_successfully')}}</h5>
                        <div class="text-center">{{translate('a_password_reset_mail_has_sent_to_your_email').'. '.translate('please_check_your_email').'.'}}</div>
                    </div>
                </div>
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

<script>
    function verificationBy(select){

        var sel=select.value;
        // alert(sel);
        $("#c-email").toggle();
        $("#c-phone").toggle();
    }
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

