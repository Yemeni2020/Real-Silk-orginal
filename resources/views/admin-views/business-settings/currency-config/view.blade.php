@extends('layouts.back-end.app')

@section('title', translate('social_Login'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/3rd-party.png')}}" alt="">
                {{translate('3rd_party')}}
            </h2>
        </div>
        @include('admin-views.business-settings.third-party-inline-menu')
        <?php
            $socialLoginServices = json_decode($data['value'] ?? '{}', true);
            ?>
        <div class="row gy-3">
            <div class="col-lg-6">
                <div class="card overflow-hidden">
                    <form action="{{route('admin.currency-config.update')}}" method="post">
                        @csrf
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                <img width="16" src="{{dynamicAsset(path: 'public/assets/back-end/img')}}/hr-logo-exchangerate.png" alt="">
                                <h4 class="mb-0"> {{translate('exchange_rate')}}</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            
                            <div class="form-group">
                                <div class="d-flex mb-2 gap-2 align-items-center">
                                    <label class="title-color font-weight-bold text-capitalize mb-0">{{translate('store_Client_Secret_Key')}}</label>
                                    <span data-toggle="tooltip" data-title="{{translate('store_Client_Secret_Key')}}">
                                        <img width="16" class="svg" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" loading="lazy" alt="">
                                    </span>
                                </div>
                                <input type="text"  class="form-control form-ellipsis" name="client_secret" placeholder="{{translate('ex')}}:{{translate('client_secret_key')}}"
                                        value="{{$data??''}}">
                            </div>

                            <ul>
                                <li>
                                <span>Enter This Website <a href="https://www.exchangerate-api.com/">https://www.exchangerate-api.com/</a></span>
                                </li>
                                <li>
                                <span>Get The Key & Paste Here</span>
                                </li>
                            </ul>
                            

                            <div class="d-flex justify-content-end flex-wrap gap-3">
                                <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary px-5 {{env('APP_MODE')!='demo'?'':'call-demo'}}">{{translate('save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
