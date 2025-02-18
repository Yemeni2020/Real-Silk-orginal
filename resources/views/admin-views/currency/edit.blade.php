@extends('layouts.back-end.app')

@section('title', translate('update_Currency'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/coupon_setup.png')}}" alt="">
                {{translate('currency_update')}}
            </h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="tio-money"></i>
                            {{translate('update_Currency')}}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.currency.update',[$currency['id']])}}" method="post"
                              style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                            @csrf
                            <div class="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="title-color text-capitalize">{{translate('currency_name').':'}}</label>
                                        <input type="text" name="name"
                                               placeholder="{{translate('currency_name')}}"
                                               class="form-control" id="name"
                                               value="{{$currency['name']}}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="title-color text-capitalize">{{translate('currency_symbol').':'}}</label>
                                        <input type="text" name="symbol"
                                               placeholder="{{translate('currency_symbol')}}"
                                               class="form-control" id="symbol"
                                               value="{{$currency['symbol']}}">
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="title-color text-capitalize">{{translate('currency_code').':'}} </label>
                                        <input type="text" name="code"
                                               placeholder="{{translate('currency_code')}}"
                                               class="form-control" id="code"
                                               value="{{$currency['code']}}">
                                    </div>
                                    @if($currencyModel=='multi_currency')
                                        <div class="col-md-6 mb-3">
                                            <label class="title-color">{{translate('exchange_rate').':'}}</label>
                                            <input type="number" min="0" max="1000000"
                                                   name="exchange_rate" step="0.00000001"
                                                   placeholder="{{translate('exchange_Rate')}}"
                                                   class="form-control" id="exchange_rate"
                                                   value="{{$currency['exchange_rate']}}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="title-color">{{translate('Language').':'}}</label>
                                            @php
                                                $languages = getWebConfig(name: 'pnc_language') ?? null;
                                            @endphp
                                            <select class="select2-selection custom-select" name="lang" >
                                                <option value="">{{translate('Select Language')}}</option>
                                                
                                                @foreach($languages as $lang)
                                                    <option @if($currency['language']==$lang) selected @endif value="{{$lang}}">{{$lang}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-6 col-lg-4 col-xl-3">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <label for="exchange_rate"
                                                        class="title-color mb-0">{{translate('type_change_value')}}</label>
                                                    <i class="tio-info-outined" data-toggle="tooltip"
                                                    title="{{translate('based_on_your_region_set_the_exchange_rate_of_the_currency_you_want_to_add')}}"></i>
                                                </div>
                                                <select class="select2-selection custom-select" name="auto_change" >
                                                    <option value="0" @if(!$currency['auto_change']) selected @endif>{{translate('static_Value')}}</option>
                                                    <option value="1" @if($currency['auto_change']) selected @endif>{{translate('dynamic_Value')}}</option>
                                                    
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-10 justify-content-end">
                                <button type="submit" id="add" class="btn btn--primary">{{translate('update')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
