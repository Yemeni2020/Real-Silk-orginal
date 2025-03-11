@php
    use App\Enums\DemoConstant;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{{ csrf_token() }}">
    <title>{{translate('office_Login')}}</title>
    <link rel="shortcut icon" href="{{ getStorageImages(path:getWebConfig(name: 'company_fav_icon')) }}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/toastr.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/style.css')}}">
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
        @php($companyWebLogo = getWebConfig(name: 'company_web_logo'))
        <a class="d-flex justify-content-center mb-5" href="{{ route('home') }}">
            <img class="z-index-2" height="40"
                 src="{{getStorageImages(path: $companyWebLogo,type: 'backend-logo')}}"
                 alt="{{translate('logo')}}">
        </a>
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card card-lg mb-5">
                    <div class="card-body">
                        
                        <!-- My Code For Add Language -->
                        <div class="topbar-text dropdown disable-autohide  __language-bar text-capitalize">
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
                        <!-- End My Code For Add Language -->

                        <form action="{{route('office.auth.login')}}" method="post" id="vendor-login-form">
                            @csrf
                            <div class="text-center">
                                <div class="mb-5">
                                    <h1 class="display-4">{{translate('sign_in')}}</h1>
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">{{translate('welcome_back_to_office_login')}}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="js-form-message form-group">
                                <label class="input-label" for="signingVendorEmail">{{translate('your_email')}}</label>
                                <input type="email" class="form-control form-control-lg" name="email"
                                       id="signingVendorEmail"
                                       tabindex="1" placeholder="email@address.com" aria-label="email@address.com"
                                       required data-msg="Please enter a valid email address.">
                            </div>
                            <div class="js-form-message form-group">
                                <label class="input-label" for="signingVendorPassword" tabindex="0">
                                        <span class="d-flex justify-content-between align-items-center">
                                          {{translate('password')}}
                                                <a href="{{route('office.auth.forgot-password.index')}}">
                                                    {{translate('forgot_password')}}
                                                </a>
                                        </span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <input type="password" class="js-toggle-password form-control form-control-lg"
                                           name="password" id="signingVendorPassword"
                                           placeholder="8+ characters required"
                                           aria-label="8+ characters required" required
                                           data-msg="Your password is invalid. Please try again."
                                           data-hs-toggle-password-options='{
                                                         "target": "#changePassTarget",
                                                "defaultClass": "tio-hidden-outlined",
                                                "showClass": "tio-visible-outlined",
                                                "classChangeTarget": "#changePassIcon"
                                                }'>
                                    <div id="changePassTarget" class="input-group-append">
                                        <a class="input-group-text" href="javascript:">
                                            <i id="changePassIcon" class="tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="termsCheckbox"
                                           name="remember">
                                    <label class="custom-control-label text-muted user-select-none" for="termsCheckbox">
                                        {{translate('remember_me')}}
                                    </label>
                                </div>
                            </div>
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                <div id="recaptcha_element" class="w-100" data-type="image"></div>
                                <br/>
                            @else
                                <div class="row py-2">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control form-control-lg form-control-focus-none" name="vendorRecaptchaKey" value=""
                                               id="vendor-login-recaptcha-input"
                                               placeholder="{{translate('enter_captcha_value')}}" autocomplete="off">
                                    </div>
                                    <div class="col-6 input-icons bg-white rounded">
                                        <a class="get-login-recaptcha-verify cursor-pointer get-session-recaptcha-auto-fill"
                                               data-link="{{ URL('/vendor/auth/recaptcha') }}"
                                               data-session="{{ 'vendorRecaptchaSessionKey' }}"
                                               data-input="#vendor-login-recaptcha-input"
                                            >
                                            <img src="{{ URL('/vendor/auth/recaptcha/1?captcha_session_id=vendorRecaptchaSessionKey') }}"
                                                alt="" class="input-field w-90 h-75 p-0 rounded" id="default_recaptcha_id">
                                            <i class="tio-refresh icon"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            <button type="button" class="btn btn-lg btn-block btn--primary submit-login-form">{{translate('login')}}</button>
                        </form>
                    </div>
                    @if(env('APP_MODE')=='demo')
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-10">
                                    <span id="vendor-email" data-email="{{ DemoConstant::VENDOR['email'] }}">{{translate('email')}} : {{ DemoConstant::VENDOR['email'] }}</span><br>
                                    <span id="vendor-password" data-password="{{ DemoConstant::VENDOR['password'] }}">{{translate('password')}} : {{ DemoConstant::VENDOR['password'] }}</span>
                                </div>
                                <div class="col-2">
                                    <button class="btn btn--primary" id="copyLoginInfo"><i class="tio-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="vendor-suspend suspended-message d-none">
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/warning.png')}}" alt="">
        <div class="cont">
            <h6>{{translate('warning')}}</h6>
            <div>
                {{translate('your_account_has_been_suspended').', '.translate('please_contact_with')}} <a href="{{route('contacts')}}">{{translate('help_and_support')}}</a>
            </div>
        </div>
        <button class="p-2 m-0 border-0 outlie-0 shadow-none bg-transparent clear-alter-message">
            <i class="tio-clear"></i>
        </button>
    </div>
    <div class="vendor-suspend pending-message d-none">
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/warning.png')}}" alt="">
        <div class="cont">
            <h6>{{translate('warning')}}</h6>
            <div>
                {{translate('your_account_is_not_approved_yet').', '.translate('please_wait_or_contact_with')}} <a href="{{route('contacts')}}">{{translate('help_and_support')}}</a>
            </div>
        </div>
        <button class="p-2 m-0 border-0 outlie-0 shadow-none bg-transparent clear-alter-message">
            <i class="tio-clear"></i>
        </button>
    </div>
</main>
<span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha') }}"></span>
<span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>

<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/login.js')}}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/custom.js') }}"></script>

{!! Toastr::message() !!}
@if(isset($recaptcha) && $recaptcha['status'] == 1)
    <script type="text/javascript">
        "use strict";
        var onloadCallback = function () {
            grecaptcha.render('recaptcha_element', {
                'sitekey': '{{ getWebConfig(name: 'recaptcha')['site_key'] }}'
            });
        };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
@endif

</body>
</html>

