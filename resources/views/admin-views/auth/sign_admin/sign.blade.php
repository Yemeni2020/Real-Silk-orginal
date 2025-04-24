
@section('content')

<div >
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📜 {{translate("contract_show")}}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <iframe id="contract-frame" src="{{route('admin.business-settings.View_contract',['type'=>$type])}}" width="100%" height="500px"></iframe>

                
            </div>
            <div class="modal-footer">
                <form action="{{route('admin.business-settings.sign_contracts')}}" id="save" method="post">
                    @csrf
                    <div class="form-check mt-3 w-100">
                        <button type="button" class="btn btn-danger form-control" id="clear-signature">{{translate('clear')}}</button>
                        <canvas id="signature-pad"  height="200" style="border: 1px solid #000;width: 100%;"></canvas>
                        <input type="hidden" id="signature-data" name="signature">
                        
                    </div>
                    <div style="display: block;" class="w-100">
                        <button type="submit" class="btn btn-primary" >
                            {{translate("Save")}}
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</div>
@endsection
