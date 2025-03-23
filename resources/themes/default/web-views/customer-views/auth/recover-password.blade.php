@extends('layouts.front-end.app')

@section('title', translate('forgot_Password'))
@push('css_or_js')
<link href="{{theme_asset(path: 'public/assets/back-end/css/select2.min.css')}}" rel="stylesheet"/>
<link href="{{theme_asset(path: 'public/assets/back-end/css/croppie.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush
@section('content')
    <div class="container py-4 py-lg-5 my-4 rtl">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-start">
                <h2 class="h3 mb-4">{{ translate('forgot_your_password')}}</h2>
                <p class="font-size-md">
                    {{ translate('change_your_password_in_three_easy_steps.')}} {{ translate('this_helps_to_keep_your_new_password_secure.')}}
                </p>
                <ol class="list-unstyled font-size-md p-0">
                    <li>
                        <span class="text-primary mr-2">{{ translate('1')}}.</span>
                        {{ translate('use_your_registered_identity.')}}
                        {{ '('.translate('Phone').'/'.translate('Email').')' }}
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('2')}}.</span>
                        {{ translate('we_will_send_you_a_temporary_OTP_in_your_reference') }}.
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('3')}}.</span>
                        {{ translate('use_the_OTP_code_to_change_your_password_on_our_secure_website.')}}
                    </li>
                </ol>

                <div class="card py-2 mt-4">
                    <div class="col-sm-12">
                            <div class="mb-4">

                                <div class="d-flex gap-3">
                                    <input type="radio" id="EmailSelected" name="account_type" value="EmailSelected" class="d-none" checked>
                                    <label for="EmailSelected" class="btn btn--primary w-100" onclick="selectAccountType('EmailSelected')">
                                        {{translate("Email")}}
                                    </label>

                                    <input type="radio" id="PhoneSelected" name="account_type" value="PhoneSelected" class="d-none">
                                    <label for="PhoneSelected" class="btn btn-outline-primary w-100" onclick="selectAccountType('PhoneSelected')">
                                        {{translate("Phone")}}
                                    </label>
                                </div>
                            </div>
                        </div>
                    <form class="card-body needs-validation" id="EmailForm" action="{{route('customer.auth.forgot-password')}}"
                          method="post">
                        @csrf
                        <div class="form-group">
                            <label for="recover-email">
                                {{ translate('Email') }}
                            </label>
                            <input class="form-control clean-phone-input-value" id="email" type="text" name="identity" required
                                   placeholder="{{ translate('enter_your_email') }}">
                            <!-- <span class="fs-12 text-muted">* {{ translate('must_use_country_code_before_phone_number') }}</span> -->
                            <div class="invalid-feedback">
                                {{ translate('please_provide_valid_identity')}}
                            </div>
                        </div>
                        @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                            <div id="recaptcha-container-verify-token" class="my-2"></div>
                        @endif
                        <button class="btn btn--primary" type="submit">{{ translate('send_OTP')}}</button>
                    </form>


                    <form class="card-body needs-validation" style="display: none;" id="PhoneForm" action="{{route('customer.auth.forgot-password')}}"
                          method="post">
                        @csrf
                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="tel">
                                        {{translate('phone')}}
                                        <span class="text-danger">*</span>
                                        <span class="text-danger fs-12 phone-error"></span>
                                    </label>
                                    <div>
                                        <input id="telephone" class="form-control form-control-user phone-input-with-country-picker"
                                                type="tel"
                                                placeholder="{{ translate('enter_phone_number') }}" required>
                                        <input type="hidden" id="phone" class="country-picker-phone-number w-50" name="identity" readonly>
                                        <div class="invalid-feedback">
                                            {{ translate('please_provide_valid_identity')}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <input class="form-control clean-phone-input-value" type="number" name="identity" required
                                   placeholder="{{ translate('enter_your_phone_or_email') }}">
                            <span class="fs-12 text-muted">* {{ translate('must_use_country_code_before_phone_number') }}</span> -->
                            
                        </div>
                        @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                            <div id="recaptcha-container-verify-token" class="my-2"></div>
                        @endif
                        <button class="btn btn--primary" type="submit">{{ translate('send_OTP')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
<script>
    function selectAccountType(type) {
        document.getElementById('EmailSelected').checked = (type === 'EmailSelected');
        document.getElementById('PhoneSelected').checked = (type === 'PhoneSelected');

        if(type=="PhoneSelected"){
            $("#PhoneForm").show();
            $("#EmailForm").hide();
            $("#telephone").focus();

        }else{
            $("#PhoneForm").hide();
            $("#EmailForm").show();
            $("#email").focus();

        }
        // تحديث تنسيق الزر ليعكس التحديد
        document.querySelector('label[for="EmailSelected"]').classList.toggle('btn--primary', type === 'EmailSelected');
        document.querySelector('label[for="EmailSelected"]').classList.toggle('btn-outline-primary', type !== 'EmailSelected');

        document.querySelector('label[for="PhoneSelected"]').classList.toggle('btn--primary', type === 'PhoneSelected');
        document.querySelector('label[for="PhoneSelected"]').classList.toggle('btn-outline-primary', type !== 'PhoneSelected');
    }
</script>
<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/vendor-registration.js') }}"></script>
@endpush
