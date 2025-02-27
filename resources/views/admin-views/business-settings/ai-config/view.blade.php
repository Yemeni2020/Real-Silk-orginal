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
            <div class="px-4 pt-3 d-flex justify-content-between">
                    <ul class="nav nav-tabs w-fit-content mb-4">
                            <li class="nav-item">
                                <span class="nav-link text-capitalize form-system-language-tab active cursor-pointer"
                                      id="translate-link">{{translate('translate_ai')}}</span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link text-capitalize form-system-language-tab  cursor-pointer"
                                      id="generate-link">{{translate('generate_ai')}}</span>
                            </li>
                    </ul>
                 
                </div>
        <div class="row gy-3  form-system-language-form" id="translate-form">
            
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <form action="{{route('admin.ai-config.update-setting-translate')}}" method="post">
                        @csrf
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                
                                <h4 class="mb-0"> {{translate('setting Translate')}}</h4>
                            </div>
                            
                        </div>
                        <div class="card-body">
                            
                            
                            <div class="form-group">
                                <div class="d-flex mb-2 gap-2 align-items-center">
                                    <label class="title-color font-weight-bold text-capitalize mb-0">{{translate('Default_Translate')}}</label>
                                    
                                </div>
                                <input type="hidden" name="type" value="OpenAI">
                                <select name="default" class="form-control" >
                                    <option {{isset($defulte_translate) && $defulte_translate=="deepl" ? "selected" : "" }} value="deepl">deepl</option>
                                    <option {{isset($defulte_translate) && $defulte_translate=="OpenAi" ? "selected" : "" }} value="OpenAi">OpenAi</option>
                                    <option {{isset($defulte_translate) && $defulte_translate=="DeepSeekAI" ? "selected" : "" }} value="DeepSeekAI">DeepSeekAI</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="d-flex mb-2 gap-2 align-items-center">
                                    <label class="title-color font-weight-bold text-capitalize mb-0">{{translate('auto_translate_after_save')}}</label>
                                    <span data-toggle="tooltip" data-title="{{translate('Translate_After_save')}}">
                                        <img width="16" class="svg" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" loading="lazy" alt="">
                                    </span>
                                </div>
                                
                                <label class="switcher">
                                    <input class="switcher_input toggle-switch-message" type="checkbox" value="1"
                                        id="auto-id" name="auto_translate" {{isset($auto_translate) && $auto_translate==1?'checked':''}}
                                        data-modal-id = "toggle-modal"
                                        data-toggle-id = "auto-id"
                                        data-on-image = "social/whatsapp-on.png"
                                        data-off-image = "social/whatsapp-off.png"
                                        data-on-title = "{{translate('want_to_turn_ON_auto_trnslate').'?'}}"
                                        data-off-title = "{{translate('want_to_turn_OFF_auto_trnslate').'?'}}"
                                        data-on-message = "<p>{{translate('if_enabled,auto_translate_is_running')}}</p>"
                                        data-off-message = "<p>{{translate('if_enabled,auto_translate_is_disable')}}</p>">
                                    <span class="switcher_control"></span>
                                </label>
                            </div>

                            
                            

                            <div class="d-flex justify-content-end flex-wrap gap-3">
                                <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary px-5 {{env('APP_MODE')!='demo'?'':'call-demo'}}">{{translate('save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card overflow-hidden">
                    <form action="{{route('admin.ai-config.update')}}" method="post">
                        @csrf
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                
                                <h4 class="mb-0"> {{translate('OpenAI Translate')}}</h4>
                            </div>
                            <label class="switcher">
                                <input class="switcher_input toggle-switch-message" type="checkbox" value="1"
                                    id="openAI-id" name="status" {{isset($OpenAi['status']) && $OpenAi['status']==1?'checked':''}}
                                    data-modal-id = "toggle-modal"
                                    data-toggle-id = "openAI-id"
                                    data-on-image = "social/whatsapp-on.png"
                                    data-off-image = "social/whatsapp-off.png"
                                    data-on-title = "{{translate('want_to_turn_ON_openAI_trnslate').'?'}}"
                                    data-off-title = "{{translate('want_to_turn_OFF_openAI_trnslate').'?'}}"
                                    data-on-message = "<p>{{translate('if_enabled,openAi_translate_is_running')}}</p>"
                                    data-off-message = "<p>{{translate('if_enabled,openAi_translate_is_disable')}}</p>">
                                <span class="switcher_control"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            
                            
                            <div class="form-group">
                                <div class="d-flex mb-2 gap-2 align-items-center">
                                    <label class="title-color font-weight-bold text-capitalize mb-0">{{translate('store_Client_Secret_Key')}}</label>
                                    <span data-toggle="tooltip" data-title="{{translate('store_API_Key')}}">
                                        <img width="16" class="svg" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" loading="lazy" alt="">
                                    </span>
                                </div>
                                <input type="hidden" name="type" value="OpenAI">
                                <input type="text"  class="form-control form-ellipsis" name="client_secret" placeholder="{{translate('ex')}}:{{translate('client_secret_key')}}"
                                        value="{{isset($OpenAi['kay'])?$OpenAi['kay']:''}}">
                            </div>

                            <ul>
                                <li>
                                <span>Enter This Website <a href="https://platform.openai.com/docs/overview" target="_blank">https://platform.openai.com/docs/overview</a></span>
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

            <div class="col-lg-6">
                <div class="card overflow-hidden">
                    <form action="{{route('admin.ai-config.update')}}" method="post">
                        @csrf
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                
                                <h4 class="mb-0"> {{translate('DeepSeekAI Translate')}}</h4>
                            </div>
                            <label class="switcher">
                                <input class="switcher_input toggle-switch-message" type="checkbox" value="1"
                                    id="DeepSeekAI-id" name="status" {{isset($DeepSeekAI['status']) && $DeepSeekAI['status']==1?'checked':''}}
                                    data-modal-id = "toggle-modal"
                                    data-toggle-id = "DeepSeekAI-id"
                                    data-on-image = "social/whatsapp-on.png"
                                    data-off-image = "social/whatsapp-off.png"
                                    data-on-title = "{{translate('want_to_turn_ON_DeepSeekAI_trnslate').'?'}}"
                                    data-off-title = "{{translate('want_to_turn_OFF_DeepSeekAI_trnslate').'?'}}"
                                    data-on-message = "<p>{{translate('if_enabled,DeepSeekAI_translate_is_running')}}</p>"
                                    data-off-message = "<p>{{translate('if_enabled,DeepSeekAI_translate_is_disable')}}</p>">
                                <span class="switcher_control"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            
                            
                            <div class="form-group">
                                <div class="d-flex mb-2 gap-2 align-items-center">
                                    <label class="title-color font-weight-bold text-capitalize mb-0">{{translate('store_Client_Secret_Key')}}</label>
                                    <span data-toggle="tooltip" data-title="{{translate('store_API_Key')}}">
                                        <img width="16" class="svg" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" loading="lazy" alt="">
                                    </span>
                                </div>
                                <input type="hidden" name="type" value="DeepSeekAI">
                                <input type="text"  class="form-control form-ellipsis" name="client_secret" placeholder="{{translate('ex')}}:{{translate('client_secret_key')}}"
                                        value="{{isset($DeepSeekAI['kay'])?$DeepSeekAI['kay']:''}}">
                            </div>

                            <ul>
                                <li>
                                <span>Enter This Website <a href="https://platform.deepseek.com/api_keys" target="_blank">https://platform.deepseek.com/api_keys</a></span>
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

            <div class="col-lg-6">
                <div class="card overflow-hidden">
                    <form action="{{route('admin.ai-config.update')}}" method="post">
                        @csrf
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                
                                <h4 class="mb-0"> {{translate('deepl Translate')}}</h4>
                            </div>
                            <label class="switcher">
                                <input class="switcher_input toggle-switch-message" type="checkbox" value="1"
                                    id="deepl-id" name="status" {{isset($deepl['status']) && $deepl['status']==1?'checked':''}}
                                    data-modal-id = "toggle-modal"
                                    data-toggle-id = "deepl-id"
                                    data-on-image = "social/whatsapp-on.png"
                                    data-off-image = "social/whatsapp-off.png"
                                    data-on-title = "{{translate('want_to_turn_ON_deepl_trnslate').'?'}}"
                                    data-off-title = "{{translate('want_to_turn_OFF_deepl_trnslate').'?'}}"
                                    data-on-message = "<p>{{translate('if_enabled,deepl_translate_is_running')}}</p>"
                                    data-off-message = "<p>{{translate('if_enabled,deepl_translate_is_disable')}}</p>">
                                <span class="switcher_control"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            
                            
                            <div class="form-group">
                                <div class="d-flex mb-2 gap-2 align-items-center">
                                    <label class="title-color font-weight-bold text-capitalize mb-0">{{translate('store_Client_Secret_Key')}}</label>
                                    <span data-toggle="tooltip" data-title="{{translate('store_API_Key')}}">
                                        <img width="16" class="svg" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" loading="lazy" alt="">
                                    </span>
                                </div>
                                <input type="hidden" name="type" value="deepl">
                                <input type="text"  class="form-control form-ellipsis" name="client_secret" placeholder="{{translate('ex')}}:{{translate('client_secret_key')}}"
                                        value="{{isset($deepl['kay'])?$deepl['kay']:''}}">
                            </div>

                            <ul>
                                <li>
                                <span>Enter This Website <a href="https://www.deepl.com/en/your-account/keys" target="_blank">https://www.deepl.com/en/your-account/keys</a></span>
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
        @include("admin-views.business-settings.ai-config.setting-genrate")                    
    </div>
@endsection
