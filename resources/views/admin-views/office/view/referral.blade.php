@extends('layouts.back-end.app')

@section('title', $seller?->shop->name ?? translate("shop_name_not_found"))

@section('content')
    <div class="content container-fluid">
    <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex gap-2 align-items-center">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('office_details')}}
            </h2>
        </div>
        <div class="flex-between d-sm-flex row align-items-center justify-content-between mb-2 mx-1">
            <div>
                @if ($seller->status=="pending")
                    <div class="mt-4 pr-2">
                        <div class="flex-between">
                            <div class="mx-1"><h4><i class="tio-shop-outlined"></i></h4></div>
                            <div><h4>{{translate('office_request_for_open_a_shop.')}}</h4></div>
                        </div>
                        <div class="text-center">
                            <form class="d-inline-block" action="{{route('admin.offices.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit"
                                        class="btn btn--primary btn-sm">{{translate('approve')}}</button>
                            </form>
                            <form class="d-inline-block" action="{{route('admin.offices.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit"
                                        class="btn btn-danger btn-sm">{{translate('reject')}}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="page-header">
            <div class="flex-between row mx-1">
                <div>
                    <h1 class="page-header-title">{{ $seller?->shop->name ?? translate("shop_Name")." : ".translate("update_Please") }}</h1>
                </div>
            </div>
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <ul class="nav nav-tabs flex-wrap page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link "
                           href="{{ route('admin.offices.view',$seller->id) }}">{{translate('shop')}}</a>
                    </li>
                    
                    <!-- <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.offices.view',['id'=>$seller->id, 'tab'=>'setting']) }}">{{translate('setting')}}</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.offices.view',['id'=>$seller->id, 'tab'=>'transaction']) }}">{{translate('transaction')}}</a>
                    </li>
                    
                    <li class="nav-item">
                            <a class="nav-link active"
                               href="{{ route('admin.offices.referral',['id'=>$seller['id'], 'tab'=>'referral']) }}">{{translate('referral')}}</a>
                    </li>
                </ul>
            </div>
        </div>
        @if($office->count()>0)
            <div class="card mb-2">
                <div class="card-body">
                <h3 class="text-capitalize d-flex gap-2">{{translate("referredBy")}}</h3>
                    <table id="datatable"
                                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('Shop_Name') }}</th>
                                <th class="text-center">{{ translate('office_name') }}</th>
                                <th class="text-center">{{ translate('contact_info') }}</th>
                                <th class="text-center">{{ translate('Status') }}</th>
                                <th class="text-center">{{ translate('Total products') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody >
                            @foreach($office as $_seller)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $_seller->shops()->first()->name }}</td>
                                    <td class="text-center">{{ $_seller->f_name }} {{$_seller->l_name}}</td>
                                    <td class="text-center"><p>{{ $_seller->email }}</p><p>{{ $_seller->phone }}</p></td>
                                    <td class="text-center">{{ translate($_seller->status) }}</td>
                                    <td class="text-center">{{ $_seller->product()->count() }}</td>
                                    <td class="text-center">
                                        <a title="{{translate('view')}}"
                                            class="btn btn-outline-info btn-sm square-btn"
                                            href="{{route('admin.offices.view',$_seller->id)}}">
                                            <i class="tio-invisible"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach                    
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        <div class="card mt-3">
            <div class="card-body">

                
                <form action="route('admin.offices.referral',['id'=>$seller['id'], 'tab'=>'referral']" method="post">
                    @csrf
                    <span class="text-capitalize d-flex gap-2">{{translate("Add referral for Seller")}}</span>
                    <select name="Seller" id="Seller" class="js-select2-custom form-control">
                        <option value="">{{translate("Select seller")}}</option>
                        @foreach($sellers as $seller)
                            <option value="{{$seller->id}}" 
                                    data-email="{{$seller->email}}"
                                    data-phone="{{$seller->phone}}">
                                {{$seller->f_name}} {{$seller->l_name}}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary ext-capitalize d-flex gap-2">{{translate("Add_Referred")}}</button>
                </form>

                <table id="datatable"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('Shop_Name') }}</th>
                            <th class="text-center">{{ translate('office_name') }}</th>
                            <th class="text-center">{{ translate('contact_info') }}</th>
                            <th class="text-center">{{ translate('Status') }}</th>
                            <th class="text-center">{{ translate('Total products') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody >
                        @foreach($referredVendors as $_seller)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $_seller->shops()->first()->name }}</td>
                                <td class="text-center">{{ $_seller->f_name }} {{$_seller->l_name}}</td>
                                <td class="text-center"><p>{{ $_seller->email }}</p><p>{{ $_seller->phone }}</p></td>
                                <td class="text-center">{{translate($_seller->status)}}</td>
                                <td class="text-center">{{ $_seller->product()->count() }}</td>
                                <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">

                                            <a title="{{translate('view')}}"
                                                class="btn btn-outline-info btn-sm square-btn"
                                                href="{{route('admin.offices.view',$_seller->id)}}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                            <span class="btn btn-outline-danger btn-sm square-btn delete-data"
                                                    title="{{ translate('delete') }}"
                                            data-id="product-{{ $_seller['id']}}">
                                                <i class="tio-delete"></i>
                                            </span>
                                        </div>

                                            <form action="{{ route('admin.offices.referral',['id'=>$_seller['id'], 'tab'=>'referral']) }}"
                                              method="POST" id="product-{{ $_seller['id']}}">
                                            @csrf @method('PUT')
                                        </form>

                                </td>
                            </tr>
                        @endforeach                    
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('script')
<script>
        $(document).ready(function () {
            $("#Seller").select2({
                templateResult: formatSeller, // تصميم العناصر في القائمة
                templateSelection: formatSellerSelection, // تصميم العنصر المختار
                escapeMarkup: function (markup) { return markup; }, // السماح باستخدام HTML
                matcher: matchCustom // البحث في الاسم + البريد + الهاتف
            });

            function formatSeller(seller) {
                if (!seller.id) {
                    return seller.text;
                }

                var email = $(seller.element).data("email");
                var phone = $(seller.element).data("phone");

                return `
                    <div style="display: flex; flex-direction: column;">
                        <strong>${seller.text}</strong>
                        <small style="color: gray;">${email}</small>
                        <small style="color: gray;">${phone}</small>
                    </div>
                `;
            }

            function formatSellerSelection(seller) {
                if (!seller.id) {
                    return seller.text;
                }
                return seller.text; // عرض الاسم فقط بعد الاختيار
            }

            function matchCustom(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }

                if (typeof data.text === 'undefined') {
                    return null;
                }

                var term = params.term.toLowerCase();
                var name = data.text.toLowerCase();
                var email = $(data.element).data("email") ? $(data.element).data("email").toLowerCase() : '';
                var phone = $(data.element).data("phone") ? $(data.element).data("phone").toLowerCase() : '';

                if (name.includes(term) || email.includes(term) || phone.includes(term)) {
                    return data;
                }

                return null;
            }
        });
    </script>
@endpush
