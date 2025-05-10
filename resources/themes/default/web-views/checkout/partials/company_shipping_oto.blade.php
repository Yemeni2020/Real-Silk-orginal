@if($rate['success'] && !empty($rate['deliveryCompany']))
    <form>
        @foreach ($rate['deliveryCompany'] as $index => $company)
            <div class="form-check mb-3 border p-3 rounded shadow-sm">
                <input class="form-check-input" type="radio"
                       name="shipping_method"
                       id="shipping_{{ $index }}"
                       value="{{ json_encode($company) }}">

                <label class="form-check-label d-flex align-items-center justify-content-between w-100"
                       for="shipping_{{ $index }}">
                    <div>
                        <strong>{{ $company['deliveryCompanyName'] }}</strong><br>
                        خدمة: {{ $company['deliveryOptionName'] }}<br>
                        السعر: {{ $company['price'] }} {{ $company['currency'] }}<br>
                        زمن التوصيل: {{ $company['avgDeliveryTime'] }}<br>
                        COD: {{ $company['codCharge'] }} {{ $company['currency'] }}
                    </div>
                    <div>
                        <img src="{{ $company['logo'] }}" alt="logo" style="height: 40px;">
                    </div>
                </label>
            </div>
        @endforeach
    </form>
@else
    <p>لم يتم العثور على شركات شحن لهذا المتجر.</p>
@endif
