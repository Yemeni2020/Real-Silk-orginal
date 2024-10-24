@extends('theme-views.layouts.app')

@section('title', translate('Refund_Policy').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 pb-3">
        <div class="page-title overlay py-5 __opacity-half background-custom-fit"
             data-bg-img = {{getStorageImages(path: imagePathProcessing(imageData: (isset($pageTitleBanner['value']) ?json_decode($pageTitleBanner['value'])?->image : null),path: 'banner'),source: theme_asset('assets/img/media/page-title-bg.png'))}}>
        <div class="container">
                <h1 class="absolute-white text-center text-capitalize">{{translate('refund_policy')}}</h1>
            </div>
        </div>
        <div class="container">
            <div class="card my-4">
                <div class="card-body p-lg-4 text-dark page-paragraph">
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
        </div>
    </main>
@endsection
