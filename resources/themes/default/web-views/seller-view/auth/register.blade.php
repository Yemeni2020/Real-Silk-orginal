@extends('layouts.front-end.app')

@section('title', translate('vendor_Apply'))

@push('css_or_js')
<link href="{{theme_asset(path: 'public/assets/back-end/css/select2.min.css')}}" rel="stylesheet"/>
<link href="{{theme_asset(path: 'public/assets/back-end/css/croppie.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush


@section('content')
    <div id="seller-registration-step1" class="d-none" action="{{route('vendor.auth.registration.check')}}"></div>
    <form id="seller-registration" action="{{route('vendor.auth.registration.index')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="py-5">
            <div class="first-el">
                <section>
                    <div class="container">
                        <div class="create-an-account p-3 p-sm-4">
                            <img src="{{theme_asset('assets/img/media/form-bg.png')}}" alt="" class="create-an-accout-bg-img">
                            <div class="row">
                                @include('web-views.seller-view.auth.partial.header')
                                <div class="col-lg-8">
                                    <div class="bg-white p-3 p-sm-4 rounded">
                                        <h4 class="mb-4 text text-capitalize">{{translate('create_an_account')}}</h4>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-4">
                                                    <label for="email">
                                                        {{translate('email')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 mail-error"></span>
                                                    </label>
                                                    <input class="form-control" type="email" id="email"  name="email" placeholder="{{translate('Ex: example@gmail.com')}}" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-4">
                                                    <label for="tel">
                                                        {{translate('phone')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 phone-error"></span>
                                                    </label>
                                                    <div>
                                                        <input class="form-control form-control-user phone-input-with-country-picker"
                                                                type="tel"
                                                                placeholder="{{ translate('enter_phone_number') }}" required>
                                                        <input type="hidden" id="phone" class="country-picker-phone-number w-50" name="phone" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-4">
                                                    <label for="password">
                                                        {{translate('password')}}
                                                        <span class="text-danger fs-12 password-error"></span>
                                                    </label>
                                                    <div class="password-toggle rtl">
                                                        <input class="form-control text-align-direction password-check" name="password" type="password" id="password"
                                                               placeholder="{{ translate('minimum_8_characters_long') }}" required>
                                                        <label class="password-toggle-btn">
                                                            <input class="custom-control-input" type="checkbox"><i
                                                                class="tio-hidden password-toggle-indicator"></i><span
                                                                class="sr-only">{{ translate('show_password') }} </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-4">
                                                    <label for="password" class="text-capitalize">
                                                        {{translate('confirm_password')}}
                                                        <span class="text-danger fs-12 confirm-password-error"></span>
                                                    </label>
                                                    <div class="password-toggle rtl">
                                                        <input class="form-control text-align-direction" name="confirm_password" type="password" id="confirm_password"
                                                            placeholder="{{ translate('confirm_password') }}" required>
                                                        <label class="password-toggle-btn">
                                                            <input class="custom-control-input " type="checkbox"><i
                                                                class="tio-hidden password-toggle-indicator"></i><span
                                                                class="sr-only">{{ translate('show_password') }} </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(!$isoffice)
                                            <div class="col-sm-12" id="_referral_code">
                                                <div class="mb-4">
                                                    <label for="referral_code" class="text-capitalize">
                                                        {{translate('referral_code')}}
                                                        <span class="text-danger fs-12 confirm-referral_code-error"></span>
                                                    </label>
                                                    <div class="referral_code-toggle rtl">
                                                        <input class="form-control text-align-direction" name="referral_code"  value="{{$referral_code}}" type="referral_code" id="referral_code"
                                                            placeholder="{{ translate('referral_code') }}" required>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($isoffice)
                                            <input type="radio" id="office" name="account_type" value="office" class="d-none" checked>
                                            @else
                                            <input type="radio" id="fictory" name="account_type" value="fictory" class="d-none" checked>


                                            <!-- <div class="col-sm-12">
                                                <div class="mb-4">
                                                    <label class="text-capitalize">
                                                        {{translate('Type_account')}}
                                                        <span class="text-danger fs-12 confirm-referral_code-error"></span>
                                                    </label>

                                                    <div class="d-flex gap-3">
                                                        <input type="radio" id="fictory" name="account_type" value="fictory" class="d-none" checked>
                                                        <label for="fictory" class="btn btn--primary w-100" onclick="selectAccountType('fictory')">
                                                            {{translate("vendor")}}
                                                        </label>

                                                        <input type="radio" id="office" name="account_type" value="office" class="d-none">
                                                        <label for="office" class="btn btn-outline-primary w-100" onclick="selectAccountType('office')">
                                                            {{translate("Office")}}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div> -->
                                            @endif
                                            <div class="col-12">
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn--primary proceed-to-next-btn text-capitalize" >{{translate('proceed_to_next')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                @include('web-views.seller-view.auth.partial.why-with-us')
                @include('web-views.seller-view.auth.partial.business-process')
                @include('web-views.seller-view.auth.partial.download-app')
                @include('web-views.seller-view.auth.partial.faq')
            </div>
            @include('web-views.seller-view.auth.partial.vendor-information-form')
        </div>
    </form>

    
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
                        <label for="verification-code" class="text-danger" id="error-verification-code"></label>
                        
                        <button id="confirm-code" data-url="{{route('vendor.auth.registration.confirmEmail')}}" class="btn btn-primary mt-3">
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

    


    <div class="modal fade registration-success-modal" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close"><i class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
                        <img src="{{theme_asset(path: 'public/assets/front-end/img/congratulations.png')}}" width="70" class="mb-3 mb-20" alt="">
                        <h5 class="modal-title">{{translate('congratulations')}}</h5>
                        <div class="text-center">{{translate('your_registration_is_successful').', '.translate('please-wait_for_admin_approval').'.'.translate(' you’ll_get_a_mail_soon')}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <span id="get-confirm-and-cancel-button-text" data-sure ="{{translate('are_you_sure').'?'}}"
      data-message="{{!$isoffice?translate('want_to_apply_as_a_vendor'):translate('want_to_apply_as_a_office').'?'}}"
      data-confirm="{{translate('yes')}}" data-cancel="{{translate('no')}}"></span>
    <span id="proceed-to-next-validation-message"
          data-mail-error="{{translate('please_enter_your_email').'.'}}"
          data-phone-error="{{translate('please_enter_your_phone_number').'.'}}"
          data-valid-mail="{{translate('please_enter_a_valid_email_address').'.'}}"
          data-enter-password="{{translate('please_enter_your_password').'.'}}"
          data-enter-confirm-password="{{translate('please_enter_your_confirm_password').'.'}}"
          data-password-not-match="{{translate('passwords_do_not_match').'.'}}"
    >
    </span>
@endsection

@push('script')
<script>
    function selectAccountType(type) {
        document.getElementById('fictory').checked = (type === 'fictory');
        document.getElementById('office').checked = (type === 'office');

        if(type=="office"){
            $("#_referral_code").hide();
            $("#referral_code").val("");
        }else{
            $("#_referral_code").show();
        }
        // تحديث تنسيق الزر ليعكس التحديد
        document.querySelector('label[for="fictory"]').classList.toggle('btn--primary', type === 'fictory');
        document.querySelector('label[for="fictory"]').classList.toggle('btn-outline-primary', type !== 'fictory');

        document.querySelector('label[for="office"]').classList.toggle('btn--primary', type === 'office');
        document.querySelector('label[for="office"]').classList.toggle('btn-outline-primary', type !== 'office');
    }
</script>
@if($web_config['recaptcha']['status'] == '1')
    <script type="text/javascript">
        "use strict";
            var onloadCallback = function () {
                let reg_id = grecaptcha.render('recaptcha-element-vendor-register', {'sitekey': '{{ $web_config['recaptcha']['site_key'] }}'});
                $('#recaptcha-element-vendor-register').attr('data-reg-id', reg_id);
            };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
@endif
<script>
    $('#vendor-apply-submit').on('click', function(){
        @if($web_config['recaptcha']['status'] == '1')
        var response = grecaptcha.getResponse($('#recaptcha-element-vendor-register').attr('data-reg-id'));
        if (response.length === 0) {
            toastr.error("{{translate('please_check_the_recaptcha')}}");
        }else{
            submitRegistration();
        }
        @else
        if ($('#default-recaptcha-id-vendor-register').val() != '') {
            submitRegistration();
        } else {
            toastr.error("{{translate('please_check_the_recaptcha')}}");
        }
        @endif
    });
    
</script>
<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/vendor-registration.js') }}"></script>
<script src="{{ theme_asset(path: 'public/js/signature_pad.umd.min.js') }}"></script>

<script>

document.getElementById("show-contract-btn").addEventListener("click", function() {
    var _fullname = encodeURIComponent(window.f_name.value + " " + window.l_name.value);

    // تكوين الرابط وإضافة الاسم الكامل كـ query parameter
    var contractUrl = "{{ route('vendor.auth.registration.contract', ['type' => $type]) }}" + "?fullname=" + _fullname;

    // تحديث iframe لعرض العقد مع الاسم
    document.getElementById("contract-frame").src = contractUrl;
});
document.getElementById("agree-checkbox").addEventListener("change", function() {
        document.getElementById("terms-checkbox").checked = this.checked;
        document.getElementById("agree-btn").disabled = !this.checked;
        this.disabled=true;
        if (!$(this).is(':checked')) {
            $('#vendor-apply-submit').attr('disabled');
        }
    });
    document.getElementById("terms-checkbox").addEventListener("change", function() {
        document.getElementById("agree-checkbox").checked = this.checked;
        document.getElementById("agree-btn").disabled = !this.checked;
        if (!$(this).is(':checked')) {
            $('#vendor-apply-submit').attr('disabled');
        }
    });

var canvas = document.getElementById("signature-pad");
var signaturePad = new SignaturePad(canvas);

document.getElementById("save-signature").addEventListener("click", function () {
    if (!signaturePad.isEmpty()) {
        document.getElementById("signature-data").value = signaturePad.toDataURL();
        document.getElementById("terms-checkbox").checked = true;
        document.getElementById("agree-checkbox").checked = true;
        document.getElementById("terms-checkbox").disabled = false;
        document.getElementById("agree-checkbox").disabled = false;
        $('#vendor-apply-submit').removeAttr('disabled');
        $("#closemodel").click();
    }
});
document.getElementById("clear-signature").addEventListener("click", function () {
    signaturePad.clear()
    document.getElementById("terms-checkbox").checked = false;
    document.getElementById("agree-checkbox").checked = false;
    document.getElementById("terms-checkbox").disabled = true;
    document.getElementById("agree-checkbox").disabled = true;
    $('#vendor-apply-submit').attr('disabled');

});


    document.getElementById("agree-btn").addEventListener("click", function() {
        $("#contract-modal").modal("hide"); // إغلاق النافذة
        document.getElementById("submit-btn").disabled = false; // تفعيل زر التسجيل
    });
    </script>
@endpush
