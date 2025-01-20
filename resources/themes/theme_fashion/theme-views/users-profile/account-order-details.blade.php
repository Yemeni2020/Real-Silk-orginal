@extends('theme-views.layouts.app')

@section('title', translate('my_order_details').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
    <section class="user-profile-section section-gap pt-0">
        <div class="container">
            @include('theme-views.partials._profile-aside')
            <div class="card bg-section border-0 bg-body">
                <div class="card-body p-lg-4">
                    @include('theme-views.partials._order-details-head')
                    <div class="mt-4 card border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                @php($digital_product = false)
                                @foreach ($order->details as $key=>$detail)
                                    @if(isset($detail->product->digital_product_type))
                                        @php($digital_product = $detail->product->product_type == 'digital' ? true : false)
                                        @if($digital_product == true)
                                            @break
                                        @else
                                            @continue
                                        @endif
                                    @endif
                                @endforeach
                                <table class="table align-middle __table ">
                                    <thead class="table-light">
                                    <tr>
                                        <th class="border-0 text-capitalize">{{translate('product_details')}}</th>
                                        <th class="border-0 text-center">{{translate('qty')}}</th>
                                        <th class="border-0 text-end text-capitalize">{{translate('unit_price')}}</th>
                                        <th class="border-0 text-end">{{translate('discount')}}</th>
                                        <th class="border-0 text-end" {{ ($order->order_type == 'default_type' && $order->order_status=='delivered') ? 'colspan="2"':'' }}>{{translate('total')}}</th>
                                        @if($order->order_type == "POS" || $order->order_type == 'default_type' && ($order->order_status=='delivered' || ($order->payment_status == 'paid' && $digital_product)))
                                            <th class="border-0 text-center">{{translate('Action')}}</th>
                                        @elseif($order->order_type != 'default_type' && $order->order_status=='delivered')
                                            <th class="border-0 text-center"></th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($order->details as $key=>$detail)
                                        @php($product=json_decode($detail->product_details,true))
                                        @if($product)
                                            <tr>
                                                <td>
                                                    <div class="cart-product">
                                                        <label class="form-check">
                                                            <img loading="lazy" alt="{{ translate('product') }}"
                                                                 src="{{ getStorageImages(path: $detail?->productAllStatus?->thumbnail_full_url, type: 'product') }}">
                                                        </label>
                                                        <div class="cont">
                                                            <a href="{{route('product',[$product['slug']])}}"
                                                               class="name text-title">{{isset($product['name']) ? Str::limit($product['name'],40) : ''}}</a>
                                                            <div class="d-flex column-gap-1">
                                                                <span>{{ translate('Price')}}</span> <span>:</span>
                                                                <strong>{{ \App\Utils\currency_converter($detail->price) }} </strong>
                                                            </div>

                                                            @if($detail->variant)
                                                                <span>{{translate('variant')}}: </span>
                                                                <strong>{{$detail->variant}} </strong>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{$detail->qty}}</td>
                                                <td class="text-end">{{\App\Utils\currency_converter($detail->price)}}</td>
                                                <td class="text-end">
                                                    -{{\App\Utils\currency_converter($detail->discount)}}</td>
                                                <td class="text-end">{{\App\Utils\currency_converter(($detail->qty*$detail->price)-$detail->discount)}}</td>
                                                @php($order_details_date = $detail->created_at)
                                                @php($length = $order_details_date->diffInDays($current_date))

                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        @if($detail?->product && $order->payment_status == 'paid' && $detail?->product?->digital_product_type == 'ready_product')
                                                            <a href="javascript:"
                                                               data-link="{{ route('digital-product-download', $detail->id) }}"
                                                               class="btn btn-base rounded-pill mb-1 digital_product_download_link"
                                                               data-toggle="tooltip" data-placement="bottom"
                                                               title="{{translate('download')}}">
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                        @elseif($detail?->product && $order->payment_status == 'paid' && $detail?->product?->digital_product_type == 'ready_after_sell')
                                                            @if($detail->digital_file_after_sell)
                                                                <a href="javascript:"
                                                                   data-link="{{ route('digital-product-download', $detail->id) }}"
                                                                   class="btn btn-base rounded-pill mb-1 digital_product_download_link"
                                                                   data-toggle="tooltip" data-placement="bottom"
                                                                   title="{{translate('download')}}">
                                                                    <i class="bi bi-download"></i>
                                                                </a>
                                                            @else
                                                                <span class="btn btn-base rounded-pill mb-1"
                                                                      data-bs-toggle="tooltip"
                                                                      data-bs-placement="bottom"
                                                                      title="Product not uploaded yet">
                                                                    <i class="bi bi-download"></i>
                                                                </span>
                                                            @endif
                                                        @endif

                                                        @if($order->order_type == 'default_type')
                                                            @if($order->order_status=='delivered')
                                                                <button class="btn btn-base rounded-pill text-nowrap"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#reviewModal{{$detail->id}}">
                                                                    @if (isset($detail->reviewData))
                                                                        {{ translate('Update_Review') }}
                                                                    @else
                                                                        {{ translate('review') }}
                                                                    @endif
                                                                </button>
                                                                @include('theme-views.layouts.partials.modal._review',['id'=>$detail->id,'order_details'=>$detail,])
                                                                @if($detail->refund_request !=0)
                                                                    <a class="btn __btn-outline btn-outline-base rounded-pill text-nowrap"
                                                                       href="{{route('refund-details',[$detail->id])}}">{{translate('refund_details')}}</a>
                                                                @endif
                                                                @if( $length <= $refund_day_limit && $detail->refund_request == 0)
                                                                    <button
                                                                        class="btn __btn-outline btn-outline-base rounded-pill"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#refundModal{{$detail->id}}">{{translate('refund')}}</button>
                                                                    @include('theme-views.layouts.partials.modal._refund',['id'=>$detail->id,'order_details'=>$detail,'order'=>$order,'product'=>$product])
                                                                @endif
                                                            @endif
                                                        @else
                                                            <label
                                                                class="btn badge-soft-base rounded-pill ">{{translate('pos_order')}}</label>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @php($orderTotalPriceSummary = \App\Utils\OrderManager::getOrderTotalPriceSummary(order: $order))
                            <div class="row justify-content-end mt-2">
                                <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">
                                    <div class="d-flex flex-column gap-3 text-dark mx-2">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div>{{translate('item')}}</div>
                                            <div>
                                                {{ $orderTotalPriceSummary['totalItemQuantity'] }}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div>{{translate('subtotal')}}</div>
                                            <div>
                                                {{ webCurrencyConverter(amount:  $orderTotalPriceSummary['subTotal']) }}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div>{{translate('tax_fee')}}</div>
                                            <div>
                                                {{ webCurrencyConverter(amount:  $orderTotalPriceSummary['taxTotal']) }}
                                            </div>
                                        </div>
                                        @if($order->order_type == 'default_type' && $order?->is_shipping_free == 0)
                                            <div
                                                class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                <div>{{translate('shipping_fee')}}</div>
                                                <div>
                                                    {{ webCurrencyConverter(amount:  $orderTotalPriceSummary['shippingTotal']) }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div class="text-capitalize">{{translate('coupon_discount')}}</div>
                                            <div>
                                                - {{ webCurrencyConverter(amount:  $orderTotalPriceSummary['couponDiscount']) }}
                                            </div>
                                        </div>
                                        @if($order->order_type != 'default_type')
                                            <div
                                                class="d-flex flex-wrap justify-content-between align-`item`s-center gap-2">
                                                <div class="text-capitalize">{{translate('extra_discount')}}</div>
                                                <div>
                                                    - {{ webCurrencyConverter(amount:  $orderTotalPriceSummary['extraDiscount']) }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <h4>{{translate('total')}}</h4>
                                            <h2 class="text-base">
                                                {{ webCurrencyConverter(amount:  $orderTotalPriceSummary['totalAmount']) }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script src="{{ theme_asset('assets/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        "use strict";
        $(function () {
            $(".coba").spartanMultiImagePicker({
                fieldName: 'fileUpload[]',
                maxCount: 5,
                rowHeight: '150px',
                groupClassName: 'col-md-4',
                placeholderImage: {
                    image: '{{ theme_asset('assets/img/image-place-holder-4_1.png') }}',
                    width: '100%'
                },
                dropFileLabel: "{{ translate('drop_here') }}",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{translate('input_png_or_jpg')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{translate('file_size_too_big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        $(function () {
            $(".coba_refund").spartanMultiImagePicker({
                fieldName: 'images[]',
                maxCount: 5,
                rowHeight: '150px',
                groupClassName: 'col-md-4',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ theme_asset('assets/img/image-place-holder-4_1.png') }}',
                    width: '100%'
                },
                dropFileLabel: "{{translate('drop_here')}}",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{translate('input_png_or_jpg')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{translate('file_size_too_big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        $('.digital_product_download_link').on('click', function () {
            let link = $(this).data('link');
            digital_product_download(link);
        });

        function digital_product_download(link) {
            $.ajax({
                type: "GET",
                url: link,
                responseType: 'blob',
                beforeSend: function () {
                    $("#loading").addClass("d-grid");
                },
                success: function (data) {
                    if (data.status == 1 && data.file_path) {
                        downloadFileUsingFileUrl(data.file_path);
                    } else if (data.status == 0) {
                        data.message ? toastr.error(data.message) : toastr.error('{{translate('download_failed')}}');
                    }
                },
                error: function () {

                },
                complete: function () {
                    $("#loading").removeClass("d-grid");
                },
            });
        }
    </script>
@endpush
