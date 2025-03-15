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
            <div class="table-responsive">
                <table
                    style="text-align: {{request()->cookie('direction', 'ltr') === "rtl" ? 'right' : 'left'}};"
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                    <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('office_name')}}</th>
                        <th>{{translate('name')}}</th>
                        <th>{{translate('contact_info')}}</th>
                        <th>{{translate('status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($officesList as $key=>$seller)
                        <tr>
                            <td>{{$key}}</td>
                            <td>
                                <div class="d-flex align-items-center gap-10 w-max-content">
                                    <img width="50"
                                    class="avatar rounded-circle object-fit-cover" src="{{ getStorageImages(path: $seller?->shop?->image_full_url, type: 'backend-basic') }}"
                                        alt="">
                                    <div>
                                        <a class="title-color" href="{{ route('admin.offices.view', ['id' => $seller->id]) }}">{{ $seller->shop ? Str::limit($seller->shop->name, 20) : translate('shop_not_found')}}</a>
                                        <br>
                                        <span class="text-danger">
                                            @if($seller->shop && $seller->shop->temporary_close)
                                                {{ translate('temporary_closed') }}
                                            @elseif($seller->shop && $seller->shop->vacation_status && $current_date >= date('Y-m-d', strtotime($seller->shop->vacation_start_date)) && $current_date <= date('Y-m-d', strtotime($seller->shop->vacation_end_date)))
                                                {{ translate('on_vacation') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a title="{{translate('view')}}"
                                    class="title-color"
                                    href="{{route('admin.offices.view',$seller->id)}}">
                                    {{$seller->f_name}} {{$seller->l_name}}
                                </a>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <strong><a class="title-color hover-c1" href="mailto:{{$seller->email}}">{{$seller->email}}</a></strong>
                                </div>
                                <a class="title-color hover-c1" href="tel:{{$seller->phone}}">{{$seller->phone}}</a>
                            </td>
                            <td>
                                {!! $seller->status=='approved'?'<label class="badge badge-success">'.translate('active').'</label>':'<label class="badge badge-danger">'.translate('inactive').'</label>' !!}
                            </td>
                            
                            
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a title="{{translate('view')}}"
                                        class="btn btn-outline-info btn-sm square-btn"
                                        href="{{route('admin.offices.view',$seller->id)}}">
                                        <i class="tio-invisible"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
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
