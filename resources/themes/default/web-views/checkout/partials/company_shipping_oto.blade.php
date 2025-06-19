@if($rate['success'] && !empty($rate['deliveryCompany']))
        <div class="mb-4">
            <label class="form-label">{{ translate('select_shipping_company') }}</label>
            <div class="row">
                @foreach ($rate['deliveryCompany'] as $index => $company)
                <div class="col-lg-3 col-md-3 col-12 ">
                <img src="{{ $company['logo'] }}" alt="logo" style="height: 50px;display:block;">

                    <input type="radio"
                           id="shipping_{{ $index }}"
                           name="shipping_method{{$shop_id}}"
                           value="{{ json_encode($company) }}"
                           class="d-none"
                           @if($loop->first) checked @endif>

                    <label for="shipping_{{ $index }}"
                           class="btn btn-outline-primary text-start p-3 w-100 d-flex justify-content-between align-items-center shadow-sm @if($loop->first) active @endif"
                           onclick="selectShippingMethod({{ $index }},{{$shop_id}})">
                        <div>
                            <strong>{{ $company['deliveryCompanyName'] }}</strong><br>
                            <small>{{translate("service")}}: {{ $company['deliveryOptionName'] }}</small><br>
                            <small>{{translate("price")}}: {{ $company['price'] }} {{ $company['currency'] }}</small><br>
                            <small>{{translate("time")}}: {{ $company['avgDeliveryTime'] }}</small><br>
                            <small>{{translate("COD")}}: {{ $company['codCharge'] }} {{ $company['currency'] }}</small>
                        </div>
                    </label>

                </div>
                @endforeach
            </div>
        </div>
@else
    <div class="alert alert-warning">{{translate("we_not_found_shipping_company")}}.</div>
@endif
