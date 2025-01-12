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
                    
                    <hr>

                    @foreach($DetailsOrder as $data)

                    @if(in_array($data->type_faild,["select","checkbox","radios","text","date","email","number"]))
                    <h6>
                        {{$data->name_faild}}:
                    </h6>    
                    
                        <input type="text" class="form-control input" readonly value="{{json_decode($data->value)}}">

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
