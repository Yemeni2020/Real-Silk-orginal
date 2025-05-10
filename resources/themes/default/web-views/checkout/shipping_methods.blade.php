@php
use App\Models\TapPaymentSetting;
use App\Models\Currency;
use App\Models\ShippingMethod;
use App\Models\OptionsShipping;

$TapPayment = TapPaymentSetting::where("method",'TAP')->Where('Type',1)->get();
$MyFatorah = TapPaymentSetting::where("method",'MYFATOORAH')->Where('Type',1)->get();
//print_r($TapPayment);
$currencyModel = getWebConfig('currency_model');




@endphp
@php($shippingMethod=getWebConfig(name: 'shipping_method'))
@php($cart=\App\Models\Cart::whereHas('product', function ($query) {
                return $query->active();
            })->where(['customer_id' => (auth('customer')->check() ? auth('customer')->id() : session('guest_id'))])
            ->get()->groupBy('cart_group_id'))
@extends('layouts.front-end.app')

@section('title', translate('choose_Payment_Method'))

@push('css_or_js')
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/payment.css') }}">
<script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
<div class="container pb-5 mb-2 mb-md-4 rtl px-0 px-md-3 text-align-direction">
    <div class="row mx-max-md-0">
        <div class="col-md-12 mb-3 pt-3 px-max-md-0">
            <div class="feature_header px-3 px-md-0">
                <span>{{ translate('shipping_method')}}</span>
            </div>
        </div>
        <section class="col-lg-8 px-max-md-0">
            <div class="checkout_details">
                <div class="px-3 px-md-0">
                    @include('web-views.partials._checkout-steps',['step'=>3])
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive d-none d-lg-block">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table __cart-table">
                                <thead class="thead-light">
                                <tr>
                                    <th class="font-weight-bold __w-45">
                                        <div class="pl-3">
                                            {{ translate('product')}}
                                        </div>
                                    </th>
                                    <th class="font-weight-bold pl-0 __w-15p text-capitalize">{{ translate('unit_price')}}</th>

                                    <th class="font-weight-bold __w-15p text-end">
                                        <div class="pr-3">
                                            {{ translate('total')}}
                                        </div>
                                    </th>
                                </tr>
                                </thead>
                            </table>
                                
                                @foreach($cart as $group_key=>$group)
                                    <div class="card __card cart_information __cart-table mb-3">
                                            <?php
                                            $isPhysicalProductExist = false;
                                            $total_shipping_cost = 0;
                                            foreach ($group as $row) {
                                                if ($row->product_type == 'physical' && $row->is_checked) {
                                                    $isPhysicalProductExist = true;
                                                }
                                                if ($row->product_type == 'physical' && $row->is_checked && $row->shipping_type != "order_wise") {
                                                    $total_shipping_cost += $row->shipping_cost;
                                                }
                                            }

                                            ?>

                                        @foreach($group as $cart_key => $cartItem)
                                            @if ($shippingMethod=='inhouse_shipping')
                                                <?php
                                                    $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                                                    $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                                                ?>
                                            @else
                                                <?php
                                                    if ($cartItem->seller_is == 'admin') {
                                                        $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                                                        $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                                                    } else {
                                                        $seller_shipping = \App\Models\ShippingType::where('seller_id', $cartItem->seller_id)->first();
                                                        $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                                                    }
                                                ?>
                                            @endif

                                            @if($cart_key==0)
                                                <div
                                                    class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 px-12">
                                                    @php($verify_status = \App\Utils\OrderManager::verifyCartListMinimumOrderAmount($request, $group_key))
                                                    @php($Shipping_methods)
                                                    @if($cartItem->seller_is=='admin')
                                                        <div class="d-flex gap-2">
                                                            <div class="d-flex gap-3 align-items-center">
                                                                <a href="{{route('shopView',['id'=>0])}}"
                                                                    class="text-primary d-flex align-items-center gap-2 fs-16">
                                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                                                                    {{getWebConfig(name: 'company_name')}}
                                                                </a>
                                                            </div>
                                                            
                                                        </div>
                                                        <?php
                                                        $Shipping_methods = ShippingMethod::where(['status' => 1])->where(['creator_type' => 'admin'])->get();
                                                        ?>
                                                    @else
                                                        <?php
                                                            $shopIdentity = \App\Models\Shop::where(['seller_id'=>$cartItem['seller_id']])->first();
                                                        ?>
                                                        <div class="d-flex gap-2">
                                                            @if($shopIdentity)
                                                            <div class="d-flex gap-3 align-items-center">
                                                                <a href="{{ route('shopView',['id' => $shopIdentity->id]) }}"
                                                                    class="text-primary d-flex align-items-center gap-2 fs-16">
                                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                                                                        {{ $shopIdentity->name }}
                                                                </a>
                                                            </div>

                                                           <?php
                                                            $Shipping_methods=ShippingMethod::where(['status' => 1])->where(['creator_id' => $shopIdentity->id])->with(['optionsShipping.options'])->get();

                                                            if($Shipping_methods->count() == 0)
                                                                $Shipping_methods = ShippingMethod::where(['status' => 1])->where(['creator_type' => 'admin'])->with(['optionsShipping.options'])->get();
                                                            ?>

                                                            @else
                                                                <a href="javascript:"
                                                                class="text-primary d-flex align-items-center gap-2 fs-16">
                                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                                                                    <span class="text-danger">{{ translate('vendor_not_available') }}</span>
                                                                </a>
                                                            @endif
                                                        </div>
                                                        
                                                    @endif

                                                    @php($chosenShipping=\App\Models\CartShipping::where(['cart_group_id'=>$cartItem['cart_group_id']])->first())

                                                    <div class=" bg-white select-method-border rounded">

                                                        @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise') 
                                                            @if(isset($chosenShipping)==false)
                                                                @php($chosenShipping['shipping_method_id']=0)
                                                            @endif
                                                            @php($shippings=\App\Utils\Helpers::getShippingMethods($cartItem['seller_id'], $cartItem['seller_is']))
                                                            @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise')

                                                                <div class="d-sm-flex">
                                                                    @isset($chosenShipping['shipping_cost'])
                                                                        <div class="text-sm-nowrap mx-sm-2 mt-sm-2 mb-1">
                                                                            <span class="font-weight-bold">
                                                                                {{ translate('shipping_cost')}}
                                                                            </span>:
                                                                            <span>
                                                                            {!!webCurrencyConverter($chosenShipping['shipping_cost'])!!}
                                                                            </span>
                                                                        </div>
                                                                    @endisset

                                                                    @if(count($shippings) > 0)
                                                                        <div class="">
                                                                            <div class="dropdown">
                                                                                <a class="bg-white border select-method-border rounded py-2 text-dark d-flex flex-wrap align-items-center" href="javascript:" data-toggle="dropdown">
                                                                                        <?php
                                                                                        $shippingTitle = translate('choose_shipping_method');
                                                                                        foreach ($shippings as $shipping) {
                                                                                            if ($chosenShipping['shipping_method_id'] == $shipping['id']) {
                                                                                                $shippingTitle = ucfirst($shipping['title']) . ' ( ' . $shipping['duration'] . ' ) ' . webCurrencyConverter($shipping['cost']);
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    <div class="flex-middle flex-nowrap fw-semibold text-dark px-2 text-capitalize">
                                                                                        <i class="fa fa-truck"></i>
                                                                                        {{ translate('shipping_method') }} :
                                                                                    </div>
                                                                                    <span class="px-1 max-width-200px text-nowrap text-truncate">{!! $shippingTitle !!}</span>
                                                                                </a>
                                                                                <div class="dropdown-menu m-0 pb-0 w-100">
                                                                                    <ul class="list-unstyled mb-0">
                                                                                        @foreach($shippings as $shipping)
                                                                                            <li class="cursor-pointer text-dark px-3 py-1 setShippingIdFunctionCartDetails font-semi-bold fs-14"
                                                                                                data-id="{{$shipping['id']}}"
                                                                                                data-cart-group="{{$cartItem['cart_group_id']}}"
                                                                                            >
                                                                                            {!!ucfirst($shipping['title']).' ( '.$shipping['duration'].' ) '.webCurrencyConverter($shipping['cost'])!!}
                                                                                            </li>
                                                                                        @endforeach
                                                                                    </ul>
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-danger d-flex align-items-center gap-1 fs-14 font-semi-bold user-select-none" data-toggle="tooltip"
                                                                                    data-placement="top"
                                                                                    title="{{ translate('No_shipping_options_available_at_this_shop') }}, {{ translate('please_remove_all_items_from_this_shop') }}">
                                                                            <i class="czi-security-announcement"></i> {{ translate('shipping_Not_Available') }}
                                                                        </span>
                                                                    @endif

                                                                </div>
                                                            @endif
                                                        @else
                                                            @if ($isPhysicalProductExist && $shipping_type != 'order_wise')
                                                                <div class="">
                                                                    <span class="font-weight-bold">{{ translate('total_shipping_cost')}}</span>
                                                                    :
                                                                    <span>
                                                                    {{translate("We_will_connect_with_you_for_cost_shipping")}}
                                                                    @if(1 == 2)
                                                                    {!! webCurrencyConverter(amount: $total_shipping_cost)!!}
                                                                    @endif
                                                                    </span>
                                                                </div>
                                                            @elseif($isPhysicalProductExist && $shipping_type == 'order_wise' && $chosenShipping)
                                                                <div class="">
                                                                    <span class="font-weight-bold">{{ translate('total_shipping_cost')}}</span>
                                                                    :
                                                                    <span>
                                                                    {{translate("We_will_connect_with_you_for_cost_shipping")}}    
                                                                    @if(1 == 2)
                                                                    {!! webCurrencyConverter(amount: $chosenShipping->shipping_cost)!!}
                                                                    @endif
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endif

                                                        
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        @if($shipping_type == 'order_wise')
                                            @php($seller_id=$cartItem->seller_is=='admin'?0:$shopIdentity->id )
                                            <div class="row shipping-method-vue container" style="border:1px var(--web-primary) solid;" id="shipping-method-vue">
                                                <div class="col-12">
                                                    <div class="">
                                                        <h6 class="font-semibold d-inline-block fs-15 mb-2">{{translate('shipping_method')}}</h6>
                                                        <label  for="sorting">
                                                            <select onchange="show_options('{{$seller_id}}',this)" class="form-control custom-select filter-on-product-filter-change">
                                                                <option selected="" disabled="">Choose</option>
                                                                @foreach($Shipping_methods as $shipping_method)
                                                                    <option value="{{ $shipping_method->id }}">{{ $shipping_method->title }}</option>
                                                                @endforeach
                                                                
                                                            </select>
                                                        </label>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-12">
                                                    @foreach($Shipping_methods as $shipping_method)
                                                        <div class="_options{{ $seller_id }} row option_shipping" style="display: none;" id="options{{ $shipping_method->id }}_{{ $seller_id }}">
                                                            @foreach($shipping_method->optionsShipping as $sel)
                                                            <div>
                                                                <span>{{$sel["name"]}}</span>
                                                                <select class="form-control custom-select filter-on-product-filter-change" name="O_{{ $sel->id }}_{{ $seller_id }}" id="O_{{ $sel->id }}_{{ $seller_id }}">
                                                                    @foreach($sel->options as $option)
                                                                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                                
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="container">
                                                <div id="Api_shipping{{$seller_id}}">

                                                </div>
                                            </div>
                                        @endif
                                        <table
                                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table __cart-table">
                                            <tbody>
                                                <?php
                                                $isPhysicalProductExist = false;
                                                foreach ($group as $row) {
                                                    if ($row->product_type == 'physical' && $row->is_checked) {
                                                        $isPhysicalProductExist = true;
                                                    }
                                                }
                                                ?>
                                            @foreach($group as $cart_key=>$cartItem)
                                                @php($product = $cartItem->allProducts)

                                                <?php
                                                    $getProductCurrentStock = $product->current_stock;
                                                    if(!empty($product->variation)) {
                                                        foreach(json_decode($product->variation, true) as $productVariantSingle) {
                                                            if($productVariantSingle['type'] == $cartItem->variant) {
                                                                $getProductCurrentStock = $productVariantSingle['qty'];
                                                            }
                                                        }
                                                    }
                                                ?>

                                                <?php
                                                    $checkProductStatus = $cartItem->allProducts?->status ?? 0;
                                                    if($cartItem->seller_is == 'admin') {
                                                        $inhouseTemporaryClose = getWebConfig(name: 'temporary_close') ? getWebConfig(name: 'temporary_close')['status'] : 0;
                                                        $inhouseVacation = getWebConfig(name: 'vacation_add');
                                                        $vacationStartDate = $inhouseVacation['vacation_start_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_start_date'])) : null;
                                                        $vacationEndDate = $inhouseVacation['vacation_end_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_end_date'])) : null;
                                                        $vacationStatus = $inhouseVacation['status'] ?? 0;
                                                        if ($inhouseTemporaryClose || ($vacationStatus && (date('Y-m-d') >= $vacationStartDate) && (date('Y-m-d') <= $vacationEndDate))) {
                                                            $checkProductStatus = 0;
                                                        }
                                                    }else{
                                                        if (!isset($cartItem->allProducts->seller) || (isset($cartItem->allProducts->seller) && $cartItem->allProducts->seller->status != 'approved')) {
                                                            $checkProductStatus = 0;
                                                        }
                                                        if (!isset($cartItem->allProducts->seller->shop) || $cartItem->allProducts->seller->shop->temporary_close) {
                                                            $checkProductStatus = 0;
                                                        }
                                                        if(isset($cartItem->allProducts->seller->shop) && ($cartItem->allProducts->seller->shop->vacation_status && (date('Y-m-d') >= $cartItem->allProducts->seller->shop->vacation_start_date) && (date('Y-m-d') <= $cartItem->allProducts->seller->shop->vacation_end_date))) {
                                                            $checkProductStatus = 0;
                                                        }
                                                    }
                                                ?>

                                                <tr>
                                                    <td class="__w-45">
                                                        <div class="d-flex gap-3 align-items-center">

                                                            <div class="d-flex gap-3">
                                                                <div class="">
                                                                    <a href="{{ $checkProductStatus == 1 ? route('product', $cartItem['slug']) : 'javascript:'}}"
                                                                    class="position-relative overflow-hidden">
                                                                        <img class="rounded __img-62 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}"
                                                                            src="{{ getStorageImages(path: $cartItem?->product?->thumbnail_full_url, type: 'product') }}"
                                                                            alt="{{ translate('product') }}">
                                                                        @if ($checkProductStatus == 0)
                                                                            <span class="temporary-closed position-absolute text-center p-2">
                                                                                <span class="fs-12 font-weight-bolder">{{ translate('N/A') }}</span>
                                                                            </span>
                                                                        @endif
                                                                    </a>
                                                                </div>
                                                                <div class="d-flex flex-column gap-1">
                                                                    <div
                                                                        class="text-break __line-2 __w-18rem {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                                                        <a href="{{ $checkProductStatus == 1 ? route('product', $cartItem['slug']) : 'javascript:'}}">
                                                                            {{$cartItem['name']}}
                                                                        </a>
                                                                        @if(!empty($cartItem['variant']))
                                                                            <div>
                                                                                <span class="__text-12px">{{translate('variant')}} : {{$cartItem['variant']}}</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <div
                                                                        class="d-flex flex-wrap gap-2 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                                                        @foreach(json_decode($cartItem['variations'], true) as $key1 => $variation)
                                                                            <div class="">
                                                                                    <span class="__text-12px text-capitalize">
                                                                                        <span class="text-muted">{{$key1}} </span> : <span
                                                                                            class="fw-semibold">{{$variation}}</span>
                                                                                    </span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>

                                                                    @if ($product->product_type == 'physical' && $shipping_type != 'order_wise')
                                                                        <div
                                                                            class="d-flex flex-wrap gap-2 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                                                            <span class="fw-semibold">
                                                                                {{ translate('shipping_cost')}}
                                                                            </span>:
                                                                            <span>
                                                                                {{translate("We_will_connect_with_you_for_cost_shipping")}}
                                                                                @if(1==2)
                                                                                {!! webCurrencyConverter(amount: $cartItem['shipping_cost']) !!}
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    @endif

                                                                    @if($product->product_type == 'physical' && $getProductCurrentStock < $cartItem['quantity'])
                                                                        <div class="d-flex text-danger font-bold">
                                                                            <span>{{ translate('Out_Of_Stock') }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="{{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }} __w-15p">
                                                        <div class="text-center">
                                                            <div class="fw-semibold">
                                                                {!! webCurrencyConverter(amount: $cartItem['price']-$cartItem['discount']) !!}
                                                            </div>
                                                            <span class="text-nowrap fs-10">
                                                                    @if ($cartItem->tax_model === "exclude")
                                                                    ({{ translate('tax')}}
                                                                    : {!! webCurrencyConverter(amount: $cartItem['tax']*$cartItem['quantity'])!!}
                                                                    )
                                                                @else
                                                                    ({{ translate('tax_included')}})
                                                                @endif
                                                                </span>
                                                        </div>
                                                    </td>
                                                    <td class="__w-15p text-end {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                                        <div>
                                                        {!! webCurrencyConverter(amount: ($cartItem['price']-$cartItem['discount'])*$cartItem['quantity']) !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        

                                        @php($free_delivery_status = \App\Utils\OrderManager::free_delivery_order_amount($group[0]->cart_group_id))
                                        @if ($free_delivery_status['status'] && (session()->missing('coupon_type') || session('coupon_type') !='free_delivery'))
                                            <div class="free-delivery-area px-3 mb-3 mb-lg-2">
                                                <div class="d-flex align-items-center gap-8">
                                                    <img class="__w-30px"
                                                        src="{{ theme_asset(path: 'public/assets/front-end/img/icons/free-shipping.png') }}" alt="">
                                                    @if ($free_delivery_status['amount_need'] <= 0)
                                                        <span
                                                            class="text-muted fs-12 mt-1">{{ translate('you_Get_Free_Delivery_Bonus') }}</span>
                                                    @else
                                                        <span
                                                            class="need-for-free-delivery font-bold fs-12 mt-1 text-primary">{!! webCurrencyConverter(amount: $free_delivery_status['amount_need']) !!}</span>
                                                        <span
                                                            class="text-muted fs-12 mt-1">{{ translate('add_more_for_free_delivery') }}</span>
                                                    @endif
                                                </div>
                                                <div class="progress free-delivery-progress">
                                                    <div class="progress-bar" role="progressbar"
                                                        style="width: {{ $free_delivery_status['percentage'] }}%"
                                                        aria-valuenow="{{ $free_delivery_status['percentage'] }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                @endforeach
                        </div>
                    </div>
                </div>
                @foreach($cart as $group_key => $group)
                <div class="cart_information mb-3 pb-3 w-100 d-lg-none">
                        <?php
                        $isPhysicalProductExist = false;
                        $total_shipping_cost = 0;
                        foreach ($group as $row) {
                            if ($row->product_type == 'physical' && $row->is_checked) {
                                $isPhysicalProductExist = true;
                            }
                            if ($row->product_type == 'physical' && $row->is_checked && $row->shipping_type != "order_wise") {
                                $total_shipping_cost += $row->shipping_cost;
                            }
                        }

                        ?>

                    @foreach($group as $cart_key=>$cartItem)
                        @if ($shippingMethod=='inhouse_shipping')
                                <?php
                                $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                                $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                                ?>
                        @else
                                <?php
                                if ($cartItem->seller_is == 'admin') {
                                    $admin_shipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                                    $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                                } else {
                                    $seller_shipping = \App\Models\ShippingType::where('seller_id', $cartItem->seller_id)->first();
                                    $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                                }
                                ?>
                        @endif

                        @if($cart_key==0)
                            <div
                                class="card-header d-flex flex-column gap-2 border-0 justify-content-between px-12">
                                @php($verify_status = \App\Utils\OrderManager::verifyCartListMinimumOrderAmount($request, $group_key))
                                @if($cartItem->seller_is=='admin')
                                    <div class="d-flex gap-2">
                                        <div class="d-flex gap-3 align-items-center">
                                            <a href="{{route('shopView',['id'=>0])}}"
                                            class="text-primary d-flex align-items-center gap-2 fs-16">
                                                <img src="{{theme_asset(path: 'public/assets/front-end/img/cart-store.png')}}" alt="">
                                                {{getWebConfig(name: 'company_name')}}
                                            </a>
                                        </div>
                                        @if ($verify_status['minimum_order_amount'] > $verify_status['amount'])
                                            <span class="pl-1 text-danger pulse-button minimum-order-amount-message" data-toggle="tooltip"
                                                data-placement="bottom"
                                                data-title="{{ translate('minimum_Order_Amount') }} {!! webCurrencyConverter(amount: $verify_status['minimum_order_amount']) !!} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif"
                                                title="{{ translate('minimum_Order_Amount') }} {!! webCurrencyConverter(amount: $verify_status['minimum_order_amount']) !!} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif">
                                                    <i class="czi-security-announcement"></i>
                                                </span>
                                        @endif
                                    </div>
                                @else
                                    <?php
                                        $shopIdentity = \App\Models\Shop::where(['seller_id'=>$cartItem['seller_id']])->first();
                                    ?>
                                    <div class="d-flex gap-2">
                                        @if($shopIdentity)
                                            <div class="d-flex gap-3 align-items-center">
                                                <a href="{{ route('shopView',['id' => $shopIdentity->id]) }}"
                                                class="text-primary d-flex align-items-center gap-2 fs-16">
                                                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/cart-store.png') }}" alt="">
                                                    {{ $shopIdentity->name }}
                                                </a>
                                            </div>
                                            @if ($verify_status['minimum_order_amount'] > $verify_status['amount'])
                                                <span class="pl-1 text-danger pulse-button minimum-order-amount-message" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-title="{{ translate('minimum_Order_Amount') }} {!! webCurrencyConverter(amount: $verify_status['minimum_order_amount']) !!} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif"
                                                    title="{{ translate('minimum_Order_Amount') }} {!! webCurrencyConverter(amount: $verify_status['minimum_order_amount']) !!} {{ translate('for') }} @if($cartItem->seller_is=='admin') {{getWebConfig(name: 'company_name')}} @else {{ \App\Utils\get_shop_name($cartItem['seller_id']) }} @endif">
                                                    <i class="czi-security-announcement"></i>
                                                </span>
                                            @endif
                                        @else
                                            <a href="javascript:"
                                            class="text-primary d-flex align-items-center gap-2 fs-16">
                                                <img src="{{ theme_asset(path: 'public/assets/front-end/img/cart-store.png') }}" alt="">
                                                <span class="text-danger">{{ translate('vendor_not_available') }}</span>
                                            </a>
                                        @endif

                                    </div>
                                @endif

                                <div class=" bg-white select-method-border rounded">
                                    @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise')
                                        @php($chosenShipping=\App\Models\CartShipping::where(['cart_group_id'=>$cartItem['cart_group_id']])->first())
                                        @if(isset($chosenShipping)==false)
                                            @php($chosenShipping['shipping_method_id']=0)
                                        @endif
                                        @php( $shippings=\App\Utils\Helpers::getShippingMethods($cartItem['seller_id'],$cartItem['seller_is']))
                                        @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise')

                                            @if(count($shippings) > 0)
                                                <div class="d-sm-flex">
                                                    <select class="form-control fs-13 font-weight-bold text-capitalize border-aliceblue max-240px action-set-shipping-id"
                                                            data-product-id="{{ $cartItem['cart_group_id'] }}">
                                                        <option>{{ translate('choose_shipping_method')}}</option>
                                                        @foreach($shippings as $shipping)
                                                            <option
                                                                value="{{$shipping['id']}}" {{$chosenShipping['shipping_method_id']==$shipping['id']?'selected':''}}>
                                                                {{ translate('shipping_method')}}
                                                                : {!!$shipping['title'].' ( '.$shipping['duration'].' ) '!!} {{ webCurrencyConverter($shippingMethod['cost'])<=0?translate("We_will_connect_with_you_for_cost_shipping"):webCurrencyConverter($shippingMethod['cost']) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @else
                                                <span class="text-danger d-flex align-items-center gap-1 fs-14 font-semi-bold user-select-none" data-toggle="tooltip"
                                                    data-placement="top"
                                                    title="{{ translate('No_shipping_options_available_at_this_shop') }}, {{ translate('please_remove_all_items_from_this_shop') }}">
                                                            <i class="czi-security-announcement"></i> {{ translate('shipping_Not_Available') }}
                                                        </span>
                                            @endif

                                            @isset($chosenShipping['shipping_cost'])
                                                <div class="text-sm-nowrap mt-2 text-center fs-12">
                                                    <span class="font-weight-bold">{{ translate('shipping_cost')}}</span>
                                                    :<span>{!!webCurrencyConverter($chosenShipping['shipping_cost'])!!}</span>
                                                </div>
                                            @endisset
                                        @endif
                                    @else
                                        @if ($isPhysicalProductExist && $shipping_type != 'order_wise')
                                            <div class="text-sm-nowrap text-center fs-12">
                                                <span class="font-weight-bold">{{ translate('total_shipping_cost') }}</span> :
                                                
                                                <span>
                                                    {{translate("We_will_connect_with_you_for_cost_shipping")}}
                                                    @if(1==2)
                                                    {!! webCurrencyConverter(amount: $total_shipping_cost) !!}
                                                    @endif
                                                </span>
                                            </div>
                                        @elseif($isPhysicalProductExist && $shipping_type == 'order_wise' && $chosenShipping)
                                            <div class="text-sm-nowrap text-center fs-12">
                                                <span class="font-weight-bold">{{ translate('total_shipping_cost')}}</span> :
                                                <span>
                                                {{translate("We_will_connect_with_you_for_cost_shipping")}}
                                                @if(1==2)
                                                    {!! webCurrencyConverter(amount: isset($chosenShipping['shipping_cost']) ? $chosenShipping['shipping_cost'] : 0)!!}
                                                @endif
                                                </span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <?php
                        $isPhysicalProductExist = false;
                        foreach ($group as $row) {
                            if ($row->product_type == 'physical' && $row->is_checked) {
                                $isPhysicalProductExist = true;
                            }
                        }
                    ?>
                    @foreach($group as $cart_key=>$cartItem)
                        @php($product = $cartItem->allProducts)

                        <?php
                            $checkProductStatus = $cartItem->allProducts?->status ?? 0;
                            if($cartItem->seller_is == 'admin') {
                                $inhouseTemporaryClose = getWebConfig(name: 'temporary_close') ? getWebConfig(name: 'temporary_close')['status'] : 0;
                                $inhouseVacation = getWebConfig(name: 'vacation_add');
                                $vacationStartDate = $inhouseVacation['vacation_start_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_start_date'])) : null;
                                $vacationEndDate = $inhouseVacation['vacation_end_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_end_date'])) : null;
                                $vacationStatus = $inhouseVacation['status'] ?? 0;
                                if ($inhouseTemporaryClose || ($vacationStatus && (date('Y-m-d') >= $vacationStartDate) && (date('Y-m-d') <= $vacationEndDate))) {
                                    $checkProductStatus = 0;
                                }
                            }else{
                                if (!isset($cartItem->allProducts->seller) || (isset($cartItem->allProducts->seller) && $cartItem->allProducts->seller->status != 'approved')) {
                                    $checkProductStatus = 0;
                                }
                                if (!isset($cartItem->allProducts->seller->shop) || $cartItem->allProducts->seller->shop->temporary_close) {
                                    $checkProductStatus = 0;
                                }
                                if(isset($cartItem->allProducts->seller->shop) && ($cartItem->allProducts->seller->shop->vacation_status && (date('Y-m-d') >= $cartItem->allProducts->seller->shop->vacation_start_date) && (date('Y-m-d') <= $cartItem->allProducts->seller->shop->vacation_end_date))) {
                                    $checkProductStatus = 0;
                                }
                            }
                        ?>
                        <div
                            class="d-flex justify-content-between gap-3 p-3 fs-12  {{count($group)-1 == $cart_key ? '' :'border-bottom border-aliceblue'}}">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="">
                                        <a href="{{ $checkProductStatus == 1 ? route('product',$cartItem['slug']) : 'javascript:'}}"
                                        class="position-relative overflow-hidden">
                                            <img class="rounded __img-48 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}"
                                                src="{{ getStorageImages(path: $cartItem?->product?->thumbnail_full_url, type: 'product') }}"
                                                alt="{{ translate('product') }}">
                                            @if ($checkProductStatus == 0)
                                                <span class="temporary-closed position-absolute text-center p-2">
                                                    <span class="fs-12 font-weight-bolder">{{ translate('N/A') }}</span>
                                                </span>
                                            @endif
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column gap-1 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                        <div class="text-break __line-2">
                                            <a href="{{ $checkProductStatus == 1 ? route('product',$cartItem['slug']) : 'javascript:'}}">{{$cartItem['name']}}</a>
                                        </div>

                                        @if(!empty($cartItem['variant']))
                                            <div>
                                                <span class="__text-12px">{{translate('variant')}} : {{$cartItem['variant']}}</span>
                                            </div>
                                        @endif

                                        <div class="d-flex flex-wrap column-gap-2">
                                            @foreach(json_decode($cartItem['variations'],true) as $key1 =>$variation)
                                                <div class="">
                                                    <span class="__text-12px text-capitalize">
                                                    <span class="text-muted"> {{$key1}} </span> : <span
                                                            class="fw-semibold">{{$variation}}</span>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex flex-wrap column-gap-2">
                                            <div class="text-nowrap text-muted">{{ translate('unit_price')}} :</div>
                                            <div class="text-start d-flex gap-1 flex-wrap">
                                                <div
                                                    class="fw-semibold">{!! webCurrencyConverter(amount: $cartItem['price']-$cartItem['discount']) !!}</div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <div class="text-nowrap text-muted">{{ translate('total')}} :</div>
                                            <div class="font-semi-bold">
                                            {!! webCurrencyConverter(amount: ($cartItem['price']-$cartItem['discount'])*$cartItem['quantity']) !!}

                                            </div>
                                            <span class="text-nowrap fs-10 mt-1px">
                                                @if ($cartItem->tax_model === "exclude")
                                                    ({{ translate('tax')}}
                                                    : {!! webCurrencyConverter(amount: $cartItem['tax']*$cartItem['quantity'])!!}
                                                    )
                                                @else
                                                    ({{ translate('tax_included')}})
                                                @endif
                                            </span>
                                        </div>

                                        @if ($shipping_type != 'order_wise')
                                            <div class="d-flex flex-wrap gap-2 {{ $checkProductStatus == 0?'custom-cart-opacity-50':'' }}">
                                                <span class="text-muted"> {{ translate('shipping_cost')}}</span>:<span
                                                    class="font-semi-bold">{!! webCurrencyConverter(amount: $cartItem['shipping_cost']) !!}</span>
                                            </div>
                                        @endif

                                        @if($product->product_type == 'physical' && $getProductCurrentStock < $cartItem['quantity'])
                                            <div class="d-flex text-danger font-bold">
                                                <span>{{ translate('Out_Of_Stock') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                           
                        </div>
                    @endforeach

                    @php($free_delivery_status = \App\Utils\OrderManager::free_delivery_order_amount($group[0]->cart_group_id))
                    @if ($free_delivery_status['status'] && (session()->missing('coupon_type') || session('coupon_type') !='free_delivery'))
                        <div class="free-delivery-area px-3 mb-3 mb-lg-2">
                            <div class="d-flex align-items-center gap-8">
                                <img class="__w-30px"
                                    src="{{ theme_asset(path: 'public/assets/front-end/img/icons/free-shipping.png') }}" alt="">
                                @if ($free_delivery_status['amount_need'] <= 0)
                                    <span
                                        class="text-muted fs-12 mt-1">{{ translate('you_Get_Free_Delivery_Bonus') }}</span>
                                @else
                                    <span
                                        class="need-for-free-delivery font-bold fs-12 mt-1 text-primary">{!! webCurrencyConverter(amount: $free_delivery_status['amount_need']) !!}</span>
                                    <span class="text-muted fs-12 mt-1">{{ translate('add_more_for_free_delivery') }}</span>
                                @endif
                            </div>
                            <div class="progress free-delivery-progress">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $free_delivery_status['percentage'] }}%"
                                    aria-valuenow="{{ $free_delivery_status['percentage'] }}" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </section>
        @include('web-views.partials._order-summary')
    </div>
</div>
<div class="modal fade" id="Api_shipping" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" id="quick-Api_shipping">
      <!-- سيتم ملء هذا المحتوى ديناميكياً -->
    </div>
  </div>
</div>

<div id=""></div>
<span id="route-action-checkout-function" data-route="shipping_methods"></span>
<span id="route-checkout-shipping-method-api" data-route="{{route('checkout-shipping-method-api')}}"></span>
@endsection

@push('script')
<script src="{{ theme_asset(path: 'public/assets/front-end/js/shipping_method.js') }}"></script>
<script>

    function show_options(shop_id,$select){
        $("._options"+shop_id).css("display","none");
        $("#options"+$select.value+"_"+shop_id).css("display","block");
        getApi_shippingMethod(shop_id,$select);
    }
    
</script>
@endpush