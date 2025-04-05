
<div class="second-el d--none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4">{{translate('create_an_account')}}</h3>
                        <div class="border p-3 p-xl-4 rounded">
                        @if($isoffice)
                        <h4 class="mb-3">{{translate('office_information')}}</h4>
                        @else
                        <h4 class="mb-3">{{translate('vendor_information')}}</h4>
                        @endif
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-4">
                                        <label  for="f_name">{{translate('first_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="f_name" name="f_name" placeholder="{{translate('ex').': John'}}" required>
                                        <span error="f_name" class="text-danger fs-12"></span>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label  for="l_name">{{translate('last_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="l_name" name="l_name" placeholder="{{translate('ex').': Doe'}}" required>
                                        <span error="l_name" class="text-danger fs-12"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 ">
                                    <div class="d-flex flex-column gap-3 align-items-center upload-file-control">
                                        <div class="upload-file">
                                            <input type="file" class="upload-file__input" name="image" accept="image/*" required>
                                            <div class="upload-file__img">
                                                <div class="temp-img-box">
                                                    <div class="d-flex align-items-center flex-column gap-2">
                                                        <i class="tio-upload fs-30"></i>
                                                        <div class="fs-12 text-muted text-capitalize">{{translate('upload_file')}}</div>
                                                    </div>
                                                </div>
                                                <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                            </div>

                                        </div>

                                        <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                            <h6 class="text-uppercase mb-1 fs-14">{{translate('vendor_image')}} <span class="text-danger">*</span></h6>
                                            <div class="text-muted text-capitalize fs-12">{{translate('image_ratio').' '.'1:1'}}</div>
                                            <span error="image" class="text-danger fs-12"></span>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border p-3 p-xl-4 rounded mt-4">
                        @if($isoffice)
                        <h4 class="mb-3 text-capitalize">{{translate('office_details')}}</h4>
                        @else
                        <h4 class="mb-3 text-capitalize">{{translate('shop_information')}}</h4>
                        @endif
                            <div class="form-group mb-4">
                                <label for="store_name" class="text-capitalize">{{$isoffice?translate('office_name'):translate('shop_Name')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="shop_name"  name="shop_name" placeholder="{{$isoffice?translate('office_name'):translate('Ex: XYZ store')}}" required>
                                <span error="shop_name" class="text-danger fs-12"></span>

                            </div>
                            <div class="form-group mb-4">
                                <label for="store_address" class="text-capitalize">{{$isoffice?translate('office_address'):translate('shop_address')}} <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="shop_address" id="shop_address" rows="4" placeholder="{{$isoffice?translate('office_address'):translate('shop_address')}}" required></textarea>
                                <span error="shop_address" class="text-danger fs-12"></span>

                            </div>

                            <div class="border p-3 p-xl-4 rounded mb-4 ">
                                <div class="d-flex flex-column gap-3 align-items-center upload-file-control">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="logo" accept="image/*" required>
                                        <div class="upload-file__img">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-capitalize">{{translate('upload_file')}}</div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                        </div>

                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1 fs-14">{{translate('upload_logo')}}<span class="text-danger">*</span></h6>
                                        <div class="text-muted text-capitalize fs-12">{{translate('image_ratio').' '.'1:1'}}</div>
                                        <div class="text-muted text-capitalize fs-12">{{translate('Image Size : Max 2 MB')}}</div>
                                        <span error="logo" class="text-danger fs-12"></span>

                                    </div>
                                </div>
                            </div>

                            <div class="border p-3 p-xl-4 rounded ">
                                <div class="d-flex flex-column gap-3 align-items-center upload-file-control">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="banner" accept="image/*" required>
                                        <div class="upload-file__img style--two">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-capitalize">{{translate('upload_file')}}</div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1 fs-14">{{translate('upload_banner')}} <span class="text-danger">*</span></h6>
                                        <div class="text-muted text-capitalize fs-12">{{translate('image_ratio').' '.'2:1'}}</div>
                                        <div class="text-muted text-capitalize fs-12">{{translate('Image Size : Max 2 MB')}}</div>
                                        <span class="text-danger fs-12"error="banner"></span>

                                    </div>
                                </div>
                            </div>
                        </div>
                        @php($recaptcha = getWebConfig(name: 'recaptcha'))
                        @if(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div id="recaptcha-element-vendor-register" class="w-100 pt-2" data-type="image"></div>
                        @else
                            <div class="mt-2">
                                <div class="row py-2">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control __h-40" name="default_recaptcha_id_seller_regi" id="default-recaptcha-id-vendor-register" value=""
                                               placeholder="{{translate('enter_captcha_value')}}" autocomplete="off" required>
                                    </div>
                                    <div class="col-6 input-icons mb-2 w-100 rounded bg-white">
                                    <span class="d-flex align-items-center align-items-center get-vendor-regi-recaptcha-verify"
                                          data-link="{{ route('vendor.auth.recaptcha', ['tmp'=>':dummy-id']) }}">
                                        <img src="{{ route('vendor.auth.recaptcha', ['tmp'=>1]).'?captcha_session_id=vendorRecaptchaSessionKey' }}" alt="" class="rounded __h-40" id="default_recaptcha_id">
                                        <i class="tio-refresh position-relative cursor-pointer p-2"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <span error="g-recaptcha-response" class="text-danger fs-12"></span>
                        @php($vendors_must_sing_contract = getWebConfig('vendors_must_sing_contract') ?? 0)
                        <div class="d-flex justify-content-start mt-2">
                            <label class="custom-checkbox align-items-center">
                                
                                @if($vendors_must_sing_contract)    
                                <input type="checkbox" class="" disabled id="terms-checkbox" >
                                <span class="form-check-label">{{ translate('i_agree_with_the') }}
                                <a
                                        href="#" data-toggle="modal" id="show-contract-btn" data-target="#contract-modal" class="text-underline color-bs-primary-force">
                                        {{ translate('terms_&_conditions') }}
                                    </a>
                                @else
                                <input type="checkbox" class=""  id="terms-checkbox" >
                                <span class="form-check-label">{{ translate('i_agree_with_the') }}
                                <a
                                href="{{route('terms')}}" target="_blank"  class="text-underline color-bs-primary-force">
                                {{ translate('terms_&_conditions') }}
                                </a>
                                @endif
                                </span>
                            </label>
                        </div>
                        <div class="d-flex justify-content-end mb-2 gap-2">
                            <button type="button" class="btn btn-secondary back-to-main-page"> {{translate('back')}} </button>
                            <button type="button" class="btn btn--primary"  id="vendor-apply-submit" disabled="disabled"> {{translate('submit')}} </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






<div id="contract-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📜 {{translate("show_contract")}}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- ✅ عرض العقد كـ PDF داخل iframe -->
                <iframe id="contract-frame" src="{{route('vendor.auth.registration.contract',['type'=>$type])}}" width="100%" height="500px"></iframe>

                <!-- ✅ مربع الموافقة -->
                <div class="form-check mt-3">
                    <input type="checkbox" disabled id="agree-checkbox" class="form-check-input">
                    <span class="form-check-label">{{ translate('i_agree_with_the') }} <a
                            href="#" >
                            {{ translate('terms_&_conditions') }}
                        </a>
                    </span>
                </div>
                <div class="form-check mt-3">
                    <canvas id="signature-pad" width="400" height="200" style="border: 1px solid #000;"></canvas>
                    <button type="button" class="btn btn-danger" id="clear-signature">{{translate('clear')}}</button>
                    
                    <input type="hidden" id="signature-data" name="signature">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="save-signature">{{translate('save')}} </button>
                <button type="button" class="btn btn-secondary" id="closemodel" data-dismiss="modal">
                    {{translate("close")}}
                </button>
            </div>
        </div>
    </div>
</div>
