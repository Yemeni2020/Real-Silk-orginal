<?php
use App\Models\Product;
use App\Models\User;

?>
@extends('layouts.back-end.app')
@section('title', translate('order_List'))

@section('content')
    <div class="content container-fluid">
        <div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <h2 class="h1 mb-0">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/all-orders.png')}}" class="mb-1 mr-1" alt="">
                    
                    {{translate('orders_service')}}
                </h2>
                <span class="badge badge-soft-dark radius-50 fz-14">{{$orders->total()}}</span>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="px-3 py-4 light-bg">
                        <div class="row g-2 align-items-center flex-grow-1">
                            <div class="col-md-4">
                                <h5 class="text-capitalize d-flex gap-1">
                                    {{translate('order_list')}}
                                    <span class="badge badge-soft-dark radius-50 fz-12">{{$orders->total()}}</span>
                                </h5>
                            </div>
                            <div class="col-md-8 d-flex gap-3 flex-wrap flex-sm-nowrap justify-content-md-end">
                                <form action="" method="GET">
                                    <div class="input-group input-group-custom input-group-merge">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                            placeholder="{{translate('search_by_Order_ID')}}" aria-label="Search by Order ID" value="{{ $searchValue }}">
                                        <button type="submit" class="btn btn--primary input-group-text">{{translate('search')}}</button>
                                    </div>
                                </form>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-outline--primary" data-toggle="dropdown">
                                        <i class="tio-download-to"></i>
                                        {{translate('export')}}
                                        <i class="tio-chevron-down"></i>
                                    </button>

                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive datatable-custom">
                        <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('order_ID')}}</th>
                                    <th class="text-capitalize">{{translate('service')}}</th>
                                    <th class="text-capitalize">{{translate('customer')}}</th>
                                    <th class="text-capitalize">{{translate('order_date')}}</th>

                                    
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach($orders as $key=>$order)

                                <tr class="status-{{$order['order_status']}} class-all">
                                    <td class="">
                                        {{$orders->firstItem()+$key}}
                                    </td>
                                    <td >
                                        <a class="title-color" href="{{route('admin.orders.servicedetails',['id'=>$order['id']])}}">{{$order['id']}} {!! $order->order_type == 'POS' ? '<span class="text--primary">(POS)</span>' : '' !!}</a>
                                    </td>
                                    <td>
                                        <?php
                                        $Product=Product::find($order->item);
                                        ?>
                                        {{isset($Product)?$Product->name:''}}
                                    </td>
                                    <td>
                                        <?php
                                        $User = User::find($order->customer);
                                        ?>
                                        {{ isset($User) ? $User->email : $order->customer }}
                                        </td>
                                    <td>
                                        <div>{{date('d M Y',strtotime($order['created_at']))}},</div>
                                        <div>{{ date("h:i A",strtotime($order['created_at'])) }}</div>
                                    </td>

                                        
                                    

                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a class="btn btn-outline--primary square-btn btn-sm mr-1" title="{{translate('view')}}"
                                                href="{{route('admin.orders.servicedetails',['id'=>$order['id']])}}">
                                                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/eye.svg')}}" class="svg" alt="">
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="d-flex justify-content-lg-end">
                            {!! $orders->links() !!}
                        </div>
                    </div>
                    @if(count($orders) == 0)
                        @include('layouts.back-end._empty-state',['text'=>'no_order_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
            <div class="js-nav-scroller hs-nav-scroller-horizontal d-none">
                <span class="hs-nav-scroller-arrow-prev d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-left"></i>
                    </a>
                </span>

                <span class="hs-nav-scroller-arrow-next d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-right"></i>
                    </a>
                </span>
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">{{translate('order_list')}}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <span id="message-date-range-text" data-text="{{ translate("invalid_date_range") }}"></span>
    <span id="js-data-example-ajax-url" data-url="{{ route('admin.orders.customers') }}"></span>
@endsection

@push('script_2')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/order.js')}}"></script>
@endpush
