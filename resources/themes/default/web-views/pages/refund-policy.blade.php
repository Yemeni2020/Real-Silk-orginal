@extends('layouts.front-end.app')

@section('title',translate('refund_policy'))

@section('content')

    <div class="container py-5 rtl text-align-direction">
        <h2 class="text-center mb-3 headerTitle">{{translate('refund_policy')}}</h2>
        <div class="card __card">
            <div class="card-body text-justify">
                <?php 
                    $lang=session()->get("local");
                ?>
                <?php
                if($lang=="sa"){
                ?>
                    {!! $refundPolicy['content'] !!}
                    <?php
                }
                elseif ($lang=="en"){

               ?>
                <p>

Refund Policy

Refunds: Customers are entitled to request a refund if the products do not match the specifications or have defects, within 14 days of receiving the order. The products must be in their original condition and unused.

Refund Process: If the refund request is approved, the funds will be returned to the customer using the same payment method originally used.
                </p>
               <?php
                
                ?>
                <?php
                }
                elseif ($lang=="cn"){

                    ?>
                     <p>
     
                     退款政策

退款：客户有权在收到订单后的14天内申请退款，如果产品不符合规格或存在缺陷。产品必须保持原始状态且未被使用。

退款流程：如果退款申请被批准，款项将通过客户最初使用的相同支付方式退还给客户。
                     </p>
                    <?php
                     
                     ?>
                     <?php
                     }
                ?>
            </div>
        </div>
    </div>
@endsection
