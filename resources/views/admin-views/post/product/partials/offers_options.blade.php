<?php
// dump($offers);
$index=1 ;

?>
@if(count($offers) > 0)
    @foreach($offers as $offer)
    <div class="col-md-12" id="offer{{$index}}">
            <div class="row offers_form" >
                <div class="form-group col-md-3">
                    <label class="title-color">{{translate('qty_from')}}</label>
                    <div class="">
                        <input value="{{$offer['q_from']}}"  type="number" id="offer_from{{$index}}" class="form-control" name="offers_from[]"
                        placeholder=" 
                        {{translate('qty_from')}} 
        "  onchange="change_min_from({{$index}})">
                    </div>
                </div>
                
                <div class="form-group col-md-3">
                    <label class="title-color">{{translate('qty_to')}}</label>
                    <div class="row">
                        <div class="col-10" >
                            <?php
                                $hide="";
                                ?>
                            @if($offer['q_to']<0)
                                    <?php
                                    $hide="d-none";
                                    ?>
                            @endif

                            <input type="number" value="{{$offer['q_to']}}" onchange="change_min_to({{$index}})" id="offer_to{{$index}}" class="form-control {{$hide}}" name="offers_to[]"
                            placeholder=" 
                            {{translate('qty_to')}} 
            "  onchange="getUpdateSKUFunctionality()">
                        </div>
                        <div class="col-2">
                            <h2 class="btn btn-info" onclick="unlimitOffer({{$index}});"><strong>∞</strong></h2>

                        </div>
                    </div>
                </div>

                <div class="form-group col-md-3">
                    <label class="title-color">{{translate('Price_Unit')}}</label>
                    <div class="">
                        <input type="number" value="{{ usdToDefaultCurrency($offer['price_unit']) }}" class="form-control" name="offers_price[]"
                        placeholder=" 
                        {{translate('price')}} 
        "  onchange="getUpdateSKUFunctionality()">
                    </div>
                </div>
                
                <div class="form-group col-md-3">
                    <label class="title-color"></label>
                    <div class="">
                        <button type="button" onclick="$('#offer{{$index}}').remove()" class="btn btn-danger del-row" rownum='{{$index}}'>X</button>
                    </div>
                </div>

            </div>
        </div>
        <?php $index++;?>

    @endforeach

@endif