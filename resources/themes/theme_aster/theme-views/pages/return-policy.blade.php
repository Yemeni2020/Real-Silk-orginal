@extends('theme-views.layouts.app')

@section('title', translate('Return_Policy').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
<main class="main-content d-flex flex-column gap-3 pb-3">
    <div class="page-title overlay py-5 __opacity-half background-custom-fit"
         data-bg-img = {{getStorageImages(path: imagePathProcessing(imageData: (isset($pageTitleBanner['value']) ?json_decode($pageTitleBanner['value'])?->image : null),path: 'banner'),source: theme_asset('assets/img/media/page-title-bg.png'))}}>
    <div class="container">
            <h1 class="absolute-white text-center text-capitalize">{{translate('return_policy')}}</h1>
        </div>
    </div>
    <div class="container">
        <div class="card my-4">
            <div class="card-body p-lg-4 text-dark page-paragraph">
            <div class="card-body text-justify">
            <?php 
                    $lang=getDefaultLanguage();
                ?>
                <?php
                if($lang=="sa"){
                ?>
                    {!! $returnPolicy['content'] !!}
                    <?php
                }
                elseif ($lang=="en"){

               ?>
<div class="return-policy">
    <h2>Return Policy</h2>
    <h3>1. Return Conditions:</h3>
    <p>Customers can return products within 14 days from the date of receiving the order if the products do not meet the specifications or have manufacturing defects.</p>

    <h3>2. Return Procedures:</h3>
    <p>Customers must submit a return request through the available communication channels on the platform, specifying the order number and the reason for the return.</p>
    <p>After reviewing and approving the request, the customer will be guided on how to return the product.</p>

    <h3>3. Product Condition:</h3>
    <p>The returned products must be in their original condition and unused, and must include all original packaging, including accessories and manuals.</p>

    <h3>4. Refund Process:</h3>
    <p>After receiving and inspecting the returned product, the refund will be processed within 5-7 business days. The refund will be issued to the original payment method used by the customer.</p>

    <h3>5. Shipping Costs:</h3>
    <p>If the return is due to a defect in the product or an error on our part, the shipping costs will be covered.</p>
    <p>If the return reasons are not related to a product issue, the customer will be responsible for the shipping costs for returning the product.</p>

    <h3>6. Exceptional Cases:</h3>
    <p>Returns will not be accepted in the following cases:</p>
    <ul>
        <li>If the products are custom-made or designed according to the customer's request.</li>
        <li>If the products are not in their original condition or are damaged due to usage.</li>
    </ul>

    <h3>7. Platform Rights:</h3>
    <p>The Real Silk trading platform reserves the right to modify this return policy at any time, with notification to customers about the improvements or changes.</p>
</div>
               <?php
                
                ?>
                <?php
                }
                elseif ($lang=="cn"){

                    ?>
<div class="return-policy">
    <h2>退货政策</h2>
    <h3>1. 退货条件：</h3>
    <p>如果产品不符合规格或存在制造缺陷，客户可以在收到订单后14天内退货。</p>

    <h3>2. 退货流程：</h3>
    <p>客户必须通过平台提供的联系方式提交退货申请，并说明订单编号和退货原因。</p>
    <p>在审核并批准申请后，将指导客户如何退回产品。</p>

    <h3>3. 产品状态：</h3>
    <p>退回的产品必须保持原始状态且未使用，并包含所有原包装，包括配件和手册。</p>

    <h3>4. 退款流程：</h3>
    <p>在收到并检查退回的产品后，退款将在5-7个工作日内处理。退款将退还到客户使用的原支付方式。</p>

    <h3>5. 运费：</h3>
    <p>如果退货是由于产品缺陷或我们的错误，运费将由我们承担。</p>
    <p>如果退货原因与产品问题无关，客户将负责退回产品的运费。</p>

    <h3>6. 特殊情况：</h3>
    <p>在以下情况下，不接受退货：</p>
    <ul>
        <li>如果产品是根据客户的要求定制或设计的。</li>
        <li>如果产品不是原始状态或由于使用而损坏。</li>
    </ul>

    <h3>7. 平台权利：</h3>
    <p>Real Silk贸易平台保留随时修改此退货政策的权利，并会通知客户有关的改进或变更。</p>
</div>
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
