@extends('layouts.front-end.app')

@section('title',translate('cancellation_policy'))

@section('content')
    <div class="container py-5 rtl text-align-direction">
        <h2 class="text-center mb-3 headerTitle">{{ translate('cancellation_policy') }}</h2>
        <div class="card __card">
            <div class="card-body text-justify">
               
                <?php 
                    $lang=session()->get("local");
                ?>
                <?php
                if($lang=="sa"){
                ?>
                     {!! $cancellationPolicy['content'] !!}
                    <?php
                }
                elseif ($lang=="en"){

               ?>
<div class="cancellation-policy">
    <h2>Cancellation Policy</h2>
    <h3>1. Order Cancellation Conditions:</h3>
    <p>Customers can request order cancellation within 24 hours of order confirmation. After 24 hours, the order will be considered confirmed and cannot be canceled.</p>

    <h3>2. Cancellation Procedures:</h3>
    <p>Customers must submit a cancellation request through the available communication channels on the platform. Please provide the order number and reason for cancellation to allow us to process the request quickly.</p>

    <h3>3. Refund Processing:</h3>
    <p>Once the cancellation request is approved, the paid amount will be refunded to the customer’s original payment method within 3-5 business days.</p>

    <h3>4. Exceptional Cases:</h3>
    <p>The order cannot be canceled in the following cases:</p>
    <ul>
        <li>If the order has been shipped or delivered.</li>
        <li>If the product is custom-made or designed according to the customer's preferences.</li>
    </ul>

    <h3>5. Platform Rights:</h3>
    <p>The Real Silk trading platform reserves the right to refuse any cancellation request under any circumstances, especially if the customer does not adhere to the specified cancellation policy.</p>
</div>

               <?php
                
                ?>
                <?php
                }
                elseif ($lang=="cn"){

                    ?>
<div class="cancellation-policy">
    <h2>取消政策</h2>
    <h3>1. 订单取消条件：</h3>
    <p>客户可以在订单确认后24小时内申请取消订单。超过24小时后，订单将被视为已确认且无法取消。</p>

    <h3>2. 取消流程：</h3>
    <p>客户必须通过平台提供的联系方式提交取消申请。请提供订单编号和取消原因，以便我们快速处理申请。</p>

    <h3>3. 退款处理：</h3>
    <p>一旦取消申请被批准，支付金额将在3-5个工作日内退还到客户的原支付方式。</p>

    <h3>4. 特殊情况：</h3>
    <p>在以下情况下，订单无法取消：</p>
    <ul>
        <li>如果订单已发货或已送达。</li>
        <li>如果产品是根据客户的要求定制或设计的。</li>
    </ul>

    <h3>5. 平台权利：</h3>
    <p>Real Silk贸易平台保留在任何情况下拒绝任何取消申请的权利，特别是当客户未遵守指定的取消政策时。</p>
</div>

                    <?php
                     
                     ?>
                     <?php
                     }
                ?>
            </div>
        </div>
    </div>
@endsection
