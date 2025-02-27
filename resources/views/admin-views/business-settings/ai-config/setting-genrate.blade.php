<div class="row gy-3  form-system-language-form" id="generate-form">
            
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <form action="{{route('admin.ai-config.update-setting-generate')}}" method="post">
                        @csrf
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                
                                <h4 class="mb-0"> {{translate('setting generate')}}</h4>
                            </div>
                            
                        </div>
                        <div class="card-body">
                            
                            
                            <div class="form-group">
                                <div class="d-flex mb-2 gap-2 align-items-center">
                                    <label class="title-color font-weight-bold text-capitalize mb-0">{{translate('Default_generate')}}</label>
                                    
                                </div>
                                <input type="hidden" name="type" value="OpenAI">
                                <select name="default" class="form-control" >
                                    <option {{isset($default_generate) && $default_generate=="OpenAi" ? "selected" : "" }} value="OpenAi">OpenAi</option>
                                    <option {{isset($default_generate) && $default_generate=="DeepSeekAI" ? "selected" : "" }} value="DeepSeekAI">DeepSeekAI</option>
                                </select>
                            </div>
                            

                            
                            

                            <div class="d-flex justify-content-end flex-wrap gap-3">
                                <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary px-5 {{env('APP_MODE')!='demo'?'':'call-demo'}}">{{translate('save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            
            
        </div>