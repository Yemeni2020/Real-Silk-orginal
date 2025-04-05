@extends('layouts.back-end.app-seller')

@section('title', translate("Select_Your_country"))

@section('content')

<div class="card">
    <div class="card-header">
        <h1>{{translate("Select_Your_country")}}</h1>
    </div>
    <div class="card-body">
        <form action="{{ route('office.info.update') }}" method="POST">
            @csrf
            <div class="row">
                <!-- قائمة الدول -->
                <div class="col-md-6">
                    <label for="country">{{ translate("Select_Country") }}</label>
                    <select id="country" name="country" class="form-control" required>
                        <option value="">{{ translate("Choose_Country") }}</option>
                        <option value="china" @if($shop->country=="china") selected @endif>{{ translate("China") }}</option>
                        <option value="saudi" @if($shop->country=="saudi") selected @endif>{{ translate("Saudi Arabia") }}</option>
                    </select>
                </div>

                <!-- قائمة المدن -->
                <div class="col-md-6">
                    <label for="city">{{ translate("Select_City") }}</label>
                    <select id="city" name="city" class="form-control" required>
                        <option value="">{{ translate("Choose_City") }}</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">{{ translate('Save') }}</button>
        </form>
    </div>
    <div class="row">
        <div class="col-8">
            <button class="btn btn-primary" data-toggle="modal" data-target="#contract-modal">{{translate('show_contract')}}</button>
        </div>
    </div>
</div>
<div id="contract-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📜 {{translate("show_contract")}}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- ✅ عرض العقد كـ PDF داخل iframe -->
                <iframe id="contract-frame" src="{{route('vendor.auth.registration.contract',['type'=>'factory','fullname'=>$shop->seller->f_name.' '.$shop->seller->l_name])}}" width="100%" height="500px"></iframe>


            </div>
            <div class="modal-footer">
                <a href="{{route('vendor.shop.contract.download',[$shop->seller_id])}}" class="btn btn-success" >{{translate('download')}} </a>
                <button type="button" class="btn btn-secondary" id="closemodel" data-dismiss="modal">
                    {{translate("close")}}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- JavaScript لتحديث المدن تلقائيًا وتحديد المدينة المحفوظة -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const countrySelect = document.getElementById("country");
        const citySelect = document.getElementById("city");
        const selectedCity = "{{ $shop->city ?? '' }}"; // جلب المدينة المحددة مسبقًا

        // قائمة المدن لكل دولة
        const cities = {
            china: [
                "Beijing", "Shanghai", "Guangzhou", "Shenzhen", "Tianjin", "Chongqing", "Chengdu", "Nanjing", "Wuhan",
                "Xi'an", "Hangzhou", "Shenyang", "Qingdao", "Dalian", "Suzhou", "Xiamen", "Fuzhou", "Ningbo", "Zhengzhou",
                "Changsha", "Kunming", "Harbin", "Jinan", "Hefei", "Shijiazhuang", "Urumqi", "Nanchang", "Guiyang", "Changchun",
                "Lanzhou", "Haikou", "Taiyuan", "Nanning"
            ],
            saudi: [
                "Riyadh", "Jeddah", "Mecca", "Medina", "Dammam", "Khobar", "Jubail", "Tabuk", "Hail", "Al Ahsa",
                "Abha", "Khamis Mushait", "Najran", "Yanbu", "Qassim", "Al Khafji", "Sakaka", "Buraidah", "Jazan",
                "Arar", "Hafar Al Batin", "Al Lith", "Rabigh", "Al Bahah", "Al Qurayyat"
            ]
        };

        // تحديث قائمة المدن وتحديد المدينة الحالية
        function updateCities() {
            const selectedCountry = countrySelect.value;
            citySelect.innerHTML = '<option value="">{{ translate("Choose_City") }}</option>'; // إعادة تعيين القائمة

            if (cities[selectedCountry]) {
                cities[selectedCountry].forEach(city => {
                    const option = document.createElement("option");
                    option.value = city.toLowerCase().replace(/\s/g, "-"); // استخدام اسم المدينة كـ value
                    option.textContent = city;
                    if (option.value === selectedCity.toLowerCase()) {
                        option.selected = true; // تحديد المدينة الحالية
                    }
                    citySelect.appendChild(option);
                });
            }
        }

        // استدعاء تحديث المدن عند تغيير الدولة
        countrySelect.addEventListener("change", updateCities);

        // تحميل القائمة عند تحميل الصفحة
        updateCities();
    });
</script>

@endsection
