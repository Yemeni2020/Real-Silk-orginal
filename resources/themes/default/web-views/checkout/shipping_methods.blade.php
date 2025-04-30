@php
use App\Models\TapPaymentSetting;
use App\Models\Currency;

$TapPayment = TapPaymentSetting::where("method",'TAP')->Where('Type',1)->get();
$MyFatorah = TapPaymentSetting::where("method",'MYFATOORAH')->Where('Type',1)->get();
//print_r($TapPayment);
$currencyModel = getWebConfig('currency_model');


@endphp

@extends('layouts.front-end.app')

@section('title', translate('choose_Payment_Method'))

@push('css_or_js')
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/payment.css') }}">
<script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')


<span id="route-action-checkout-function" data-route="shipping_methods"></span>
@endsection

@push('script')
<script src="{{ theme_asset(path: 'public/assets/front-end/js/payment.js') }}"></script>
@endpush