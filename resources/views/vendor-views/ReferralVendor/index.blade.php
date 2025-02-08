
@extends('layouts.back-end.app-seller')

@section('title', translate('Referral_Vendor'))

@section('content')
    <div class="content container-fluid ">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{translate('Referral_Vendor')}}
            </h2>
        </div>
        <!-- MyCode -->
        <div class="card mb-3 container">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex gap-2">
                            <label class="title-color" for="referral_code">{{translate("referral_code")}}</label>
                        </div>
                        <input type="text" ondblclick="navigator.clipboard.writeText(this.value);toastr.success('Copid')" readonly value="{{$referral_code}}" id="referral_code" class="form-control">
                    </div>
                    <div class="col-6">
                        <div class="d-flex gap-2">
                            <label class="title-color" for="referral_url">{{translate("referral_url")}}</label>
                        </div>
                        <input type="text" ondblclick="navigator.clipboard.writeText(this.value);toastr.success('Copid')" readonly value="{{route('vendor.auth.registration.index').'/'.$referral_code }}" id="referral_url" class="form-control">
                    </div>
                    <div class="col-6">
                        <div class="d-flex gap-2">
                            <label class="title-color" for="referral_url">{{translate("referral_url")}}</label>
                        </div>
                        <a type="text" href="{{route('vendor.auth.registration.index').'/'.$referral_code }}" id="referral_url" class="form-control" target="tblink">{{route('vendor.auth.registration.index').'/'.$referral_code }}</a>
                    </div>
                </div>
            </div>





            <div class=" ">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-body h-100 justify-content-center">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="d-flex flex-column align-items-start">
                                    <h3 class="mb-1 fz-24">{{count($sellers)}}</h3>
                                    <div class="text-capitalize mb-0">{{translate('Count_Sellers_referral')}}</div>
                                </div>
                                <div>
                                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-body h-100 justify-content-center">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="d-flex flex-column align-items-start">
                                    <h3 class="mb-1 fz-24">{{usdToDefaultCurrency(amount:$sum_referral)}}</h3>
                                    <div class="text-capitalize mb-0">{{translate('total_referral_Commission')}}</div>
                                </div>
                                <div>
                                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/tcg.png')}}" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- EndMyCode -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/shop-info.png')}}" alt="">
                {{translate('vendors')}}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ url()->current() }}" method="GET">
                    <div class="input-group input-group-custom input-group-merge">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="tio-search"></i>
                            </div>
                        </div>
                        <input id="datatableSearch_" type="search" name="searchValue"
                                class="form-control"
                                placeholder="{{ translate('search_by_vendor_Name_or_shop_name') }}"
                                aria-label="Search orders"
                                value="{{ request('searchValue') }}">
                        <input type="hidden" value="{{ request('status') }}" name="status">
                        <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                    </div>
                </form>
                <table id="datatable"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th class="text-capitalize">{{ translate('seller_name') }}</th>
                        <th class="text-capitalize">{{ translate('shop_name') }}</th>
                        <th class="text-center text-capitalize">{{ translate('status') }}</th>
                        <th class="text-center text-capitalize">{{ translate('Sellers_referral') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sellers as $fictory)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th> 
                            <td style="white-space: normal; word-wrap: break-word; max-width: 200px;">
                                <a href="#"
                                    class="media align-items-center gap-2">

                                    <span class="media-body title-color hover-c1">
                                    {{ $fictory->f_name??"" }} {{ $fictory->l_name??"" }}
                                </span>
                                </a>
                            </td>
                            <td style="white-space: normal; word-wrap: break-word; max-width: 200px;">
                                <a href="#"
                                    class="media align-items-center gap-2">

                                    <span class="media-body title-color hover-c1">
                                    {{ $fictory->shop_name??"" }}
                                </span>
                                </a>
                            </td>
                            <td class="text-center">
                                {{ translate($fictory->status??"") }}
                            </td>
                            <td class="text-center">
                                {{usdToDefaultCurrency(amount:$fictory->referral_commission??0)}}
                            </td>
                            
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {{ $sellers->links() }}
                        </div>
                </div>
            </div>
        </div>



        
    </div>
@endsection
