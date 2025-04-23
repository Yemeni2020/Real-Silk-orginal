<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ request()->cookie('direction', 'ltr') }}">
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
<body id="bdy" lang="{{app()->getLocale()}}" dir="{{ request()->cookie('direction', 'ltr') }}">
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
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-4">
                                        <label for="country" class="text-capitalize">{{ translate("Select_Country") }} <span class="text-danger">*</span></label>
                                        <select id="country" name="country" class="form-control" required>
                                            <option value="">{{ translate("Choose_Country") }}</option>
                                            <option value="china">{{ translate("China") }}</option>
                                            <option value="saudi" >{{ translate("Saudi Arabia") }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-4">
                                    <label for="city" class="text-capitalize">{{ translate("Select_city") }} <span class="text-danger">*</span></label>
                                    <select class="form-control" name="city" id="city" required></select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-4">
                                    <label for="number_cr" class="text-capitalize">{{translate('number_CR')}} <span class="text-danger">*</span></label>
                                    <input class="form-control" name="number_cr" id="number_cr" required/>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary form-control" name="submit" disabled="true" id="agree-btn">{{translate('submit')}} </button>

                        </form>
                        <div class="col-6">
                            
                            <a href="{{route('vendor.auth.logout')}}" class="btn btn-primary form-control">{{translate('logout')}}</a>

                        </div>
                        <div class="col-6">
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
                        </div>
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
<script>
const jsonPath = "{{ theme_asset(path: 'public/assets/front-end/json/country-cn.json') }}";
document.addEventListener("DOMContentLoaded", function () {
    const countrySelect = document.getElementById("country");
    const citySelect = document.getElementById("city");

    const selectedCountry = "china";
    const selectedCity = "";
    const currentLang = window.bdy.lang || 'en';

    fetch(jsonPath)
  .then(response => response.json())
  .then(data => {
    const countries = data.country;
    const currentLang = window.bdy.lang || 'en';
    // تعبئة قائمة الدول
    countrySelect.innerHTML = '<option value="">{{ __("Choose_Country") }}</option>';
    Object.entries(countries).forEach(([code, country]) => {
        console.log(country.country_code);
      const option = document.createElement("option");
      option.value = country.country_code;
      option.textContent = country.country_data[currentLang] || country.country_data.en;
      if (country.country_code === selectedCountry) {
        option.selected = true;
      }
      countrySelect.appendChild(option);
    });

    // تعبئة المدن إذا كانت الدولة محددة
    if (selectedCountry && countries[selectedCountry]) {
      updateCities(countries[selectedCountry].country_data.cities);
    }

    // عند تغيير الدولة
    countrySelect.addEventListener("change", () => {
      const code = countrySelect.value;
      if (countries[code]) {
        updateCities(countries[code].country_data.cities);
      }
    });

    // دالة تعبئة المدن
    function updateCities(citiesByLang) {
        const citiesEn = citiesByLang.en || [];
        const citiesCurrentLang = citiesByLang[currentLang] || citiesEn;

        citySelect.innerHTML = '<option value="">{{ __("Choose_City") }}</option>';

        citiesCurrentLang.forEach((city, index) => {
            if (!citiesEn[index]) return; // حماية من mismatch في الطول

            const option = document.createElement("option");

            // استخدام الاسم الإنجليزي في value (ثابت)
            option.value = citiesEn[index].toLowerCase().replace(/\s+/g, "-");

            // عرض الاسم حسب اللغة الحالية
            option.textContent = city;

            if (option.value === selectedCity) {
            option.selected = true;
            }

            citySelect.appendChild(option);
        });
        }
  })
  .catch(error => {
    console.error("خطأ في تحميل بيانات الدول والمدن:", error);
  });
});
</script>
</body>
</html>

