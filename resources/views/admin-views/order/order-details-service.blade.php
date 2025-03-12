@extends('layouts.back-end.app')
@section('title', translate('order_Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
          href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
@endpush

@section('content')

    
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    @if(isset($product))
                        <h1>{{translate('Form_details_for ')}}{{$product->name}}</h1>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{route('admin.orders.servicedetails',['id'=>$order['id']])}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="title-color">
                                {{ translate('product_type') }}
                                <span class="input-required-icon">*</span>
                            </label>
                            <select name="status" id="status" class="form-control" required>
                                <option @if($order->status=="pending") selected @endif value="pending" selected>{{ translate('pending') }}</option>
                                <option @if($order->status=="completed") selected @endif value="completed">{{ translate('completed') }}</option>
                                <option @if($order->status=="failed") selected @endif value="failed">{{ translate('failed') }}</option>
                                <option @if($order->status=="canceled") selected @endif value="canceled">{{ translate('canceled') }}</option>
                            </select>
                        </div>
                        <div class="form-group pt-2">
                            <label class="title-color" for="en_description">
                                {{ translate('description') }} 
                                
                            </label>
                            <textarea style="width: 100%;" id="en_description" class="summernote  product-description-default-language" name="description">{{$order->note}}</textarea>
                        </div>
                        <button class="btn btn--primary px-5 ">
                            {{ translate('submit') }}
                        </button>
                    </form>
                </div>
            </div>
            <div class="card">
                
                <div class="card-body">
                    
                    <hr>
                    
                    @foreach($DetailsOrder as $data)
                    <?php
                        // $value=is_array($data->value)?json_decode($data->value):$data->value;
                    ?>
                    @if(in_array($data->type_faild,["select","checkbox","radios","text","date","email","number"]))
                    <h6>
                        {{$data->name_faild}}:
                    </h6>    
                    
                        <input type="text" class="form-control input" readonly value="{{$data->value}}">

                    <hr>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-4">
            @if(isset($product))
            <div class="card">

                <div class="card-header">
                    <h1>{{translate('Service ')}}</h1>

                    <h2> {{$product->name}}</h2>

                    <img class="avatar avatar-60 rounded img-fit" src="{{ getStorageImages(path:$product?->thumbnail_full_url, type: 'backend-product') }}" alt="{{translate('image_Description')}}">
                            
                        
                </div>
                <div class="card-body">
                    
                    {!! $product->details !!}
                </div>
            </div>
            @endif
            @if(isset($User))

            <div class="card">

                <div class="card-header">
                    <h1>{{translate('Customer_details ')}}</h1>

                            
                        
                </div>
                <div class="card-body">
                    
                    <p>
                        {{translate("Customer Email") }} : {{ $User->email}}

                    </p>
                    <p>
                        {{translate("Customer Name") }} : {{ $User->f_name.$User->l_name}}
                    </p>
                </div>
            </div>
            @endif

        </div>

    </div>

@endsection


@push('script_2')
    @if(getWebConfig('map_api_status') ==1 )
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=mapCallBackFunction&loading=async&libraries=places&v=3.56"
            defer>
        </script>
    @endif
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/order.js')}}"></script>
@endpush
