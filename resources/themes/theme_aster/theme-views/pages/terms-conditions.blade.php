@extends('theme-views.layouts.app')

@section('title', translate('Terms_&_Conditions').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 pb-3">
        <div class="page-title overlay py-5 __opacity-half background-custom-fit"
             data-bg-img = {{getStorageImages(path: imagePathProcessing(imageData: (isset($pageTitleBanner['value']) ?json_decode($pageTitleBanner['value'])?->image : null),path: 'banner'),source: theme_asset('assets/img/media/page-title-bg.png'))}}>
        <div class="container">
                <h1 class="absolute-white text-center text-capitalize">{{ translate('terms_&_conditions') }}</h1>
            </div>
        </div>
        <div class="container">
            <div class="card my-4">
                <div class="card-body p-lg-4 text-dark page-paragraph">
                <?php 
                    $lang=session()->get("local");
                ?>
                <?php
                if($lang=="sa"){
                ?>
                    {!! $termsCondition !!}
                    <?php
                }
                elseif ($lang=="en"){

               ?>
                    <div class="real-silk-policies">
                        <h2>Real Silk Trading Platform Policies</h2>

                        <h3>1. Privacy Policy</h3>
                        <h4>Introduction:</h4>
                        <p>At the Real Silk trading platform, we are committed to protecting the privacy of all users, whether they are customers or manufacturers. This policy explains how personal data is collected, used, and protected, as well as the controls in place to safeguard this data in accordance with applicable regulations.</p>

                        <h4>Information Collection:</h4>
                        <p>We collect personal data from customers and manufacturers for business operations management purposes, including: name, contact information, shipping details, and product information.</p>

                        <h4>Data Usage:</h4>
                        <p>The data is used to facilitate the sales and shipping process between manufacturers and customers, improve our services, and ensure a smooth user experience on the platform.</p>

                        <h4>Data Sharing:</h4>
                        <p>We only share data with necessary parties (such as shipping companies or governmental entities) that need this information to provide services properly, in full compliance with local and international regulations.</p>

                        <h4>Information Protection:</h4>
                        <p>We take the necessary technical and organizational measures to protect personal information from breaches or unauthorized access, including the use of encryption technologies and security software.</p>

                        <h4>User Rights:</h4>
                        <p>Customers and manufacturers have the right to access, modify, or delete their personal data upon request, in accordance with data protection regulations in Saudi Arabia.</p>

                        <h3>2. Payment Policy</h3>
                        <h4>Accepted Payment Methods:</h4>
                        <p>The Real Silk trading platform accepts secure and authorized payment methods such as credit cards (Visa and MasterCard), bank transfers, and locally accepted payment methods in Saudi Arabia like "Mada".</p>

                        <h4>Payment Security:</h4>
                        <p>All financial transactions are protected using standard Payment Card Industry Data Security Standard (PCI DSS) protocols and data encryption technologies.</p>

                        <h4>Payment Terms:</h4>
                        <p>Customers must pay the dues when placing orders. If the payment method is a bank transfer, the payment must be confirmed before the products are shipped.</p>

                        <h3>3. Refund Policy</h3>
                        <h4>Refunds:</h4>
                        <p>Customers are entitled to request a refund if the products do not meet the specifications or have defects, within 14 days of receiving the order. The products must be in their original condition and unused.</p>

                        <h4>Refund Process:</h4>
                        <p>If the refund request is approved, the funds will be returned to the customer using the same payment method originally used.</p>

                        <h3>4. Shipping and Delivery Policy</h3>
                        <h4>Shipping Options:</h4>
                        <p>The Real Silk trading platform offers multiple shipping options from China to Saudi Arabia, including air and sea shipping according to the customer's needs.</p>

                        <h4>Estimated Delivery Time:</h4>
                        <p>Orders are delivered to customers within a period ranging from 7 to 14 days. In case of any delays, the customer will be informed of the reasons for the delay and the expected shipping time.</p>

                        <h4>Shipping Costs:</h4>
                        <p>Shipping costs are calculated based on the weight of the products and the delivery location. Shipping costs may be added to the final invoice or calculated separately as per prior agreement.</p>

                        <h3>5. Manufacturer Rights Protection Policy</h3>
                        <h4>Intellectual Property Protection:</h4>
                        <p>We are committed to protecting the intellectual property rights of our partner manufacturers, including protecting trademarks and products from counterfeiting or illegal use.</p>

                        <h4>Quality Assurance:</h4>
                        <p>Manufacturers must adhere to providing products that meet the agreed-upon specifications and quality. In the event of any product defects, the manufacturer is responsible for replacing the products or compensating the customers.</p>

                        <h3>6. Payment Policy for Manufacturers</h3>
                        <h4>Payment Terms:</h4>
                        <p>The Real Silk trading platform commits to paying dues to manufacturers only after the customer has received the product and verified its quality and compliance with the agreed-upon specifications.</p>

                        <h4>Payment Process:</h4>
                        <ul>
                            <li>After the products are shipped to the customer, the shipping status is monitored through the platform.</li>
                            <li>Once the customer confirms receipt of the product and verifies its quality and specifications, the payment is issued to the manufacturer.</li>
                            <li>In case of any issues with quality or specifications, payment is delayed until the issue is fully resolved.</li>
                        </ul>

                        <h4>Quality Verification Procedures:</h4>
                        <p>The Real Silk trading platform reserves the right to monitor and inspect product quality through customers after receipt. If the product is found to be non-compliant or defective, the manufacturer will be informed to address the issue before payment is completed.</p>

                        <h4>Delayed Payments:</h4>
                        <p>If there are delays in shipping or defects are discovered in the product, payment to the manufacturer will be postponed until the issue is resolved, either through product replacement or compensation to the customer.</p>

                        <h3>7. Product Entry Requirements to Saudi Arabia</h3>
                        <h4>Compliance Certificates:</h4>
                        <p>All manufacturers must ensure that the products exported to Saudi Arabia have compliance certificates from the Saudi Standards, Metrology and Quality Organization (SASO).</p>

                        <h4>Registration with SASO:</h4>
                        <p>Manufacturers must ensure registration with the Saudi Standards, Metrology and Quality Organization and provide the necessary documentation to ensure compliance with local quality and safety regulations.</p>

                        <h4>Invoices and Certificates of Origin:</h4>
                        <p>Manufacturers must provide accurate invoices and certificates of origin for each shipment to ensure the products' legal entry into Saudi Arabia.</p>

                        <h4>Packing and Packaging:</h4>
                        <p>Products must be packaged in a manner that protects them during shipping, taking into account packaging standards to keep products intact until they reach the customer.</p>

                        <h4>Compliance with Customs and Tax Regulations:</h4>
                        <p>Manufacturers must comply with Saudi customs regulations and cover all required taxes and customs duties to ensure the products' entry into the Kingdom without delay.</p>

                        <h3>8. Terms of Service for Manufacturers and Customers</h3>
                        <h4>Platform Use:</h4>
                        <p>All users agree to adhere to the platform's policies and terms of use as outlined in this document. Any illegal use or violation of the terms of service will result in account suspension or termination.</p>

                        <h4>Manufacturers' Obligations:</h4>
                        <p>Manufacturers must provide products that meet the specifications, ensure timely shipping, and comply with all supply and customs regulations.</p>

                        <h4>Dispute Resolution:</h4>
                        <p>In case of any dispute between parties, legal settlement or arbitration will be sought in accordance with the applicable laws in Saudi Arabia.</p>
                    </div>

               <?php
                
                ?>
                <?php
                }
                elseif ($lang=="cn"){

                    ?>
                    <div class="real-silk-policies">
                        <h2>Real Silk 贸易平台政策</h2>

                        <h3>1. 隐私政策</h3>
                        <h4>简介：</h4>
                        <p>在Real Silk贸易平台，我们承诺保护所有用户的隐私，无论是客户还是制造商。本政策说明了如何收集、使用和保护个人数据，以及根据适用法规保护这些数据的控制措施。</p>

                        <h4>信息收集：</h4>
                        <p>我们从客户和制造商处收集个人数据，以便管理业务操作，包括：姓名、联系方式、运输详细信息和产品信息。</p>

                        <h4>数据使用：</h4>
                        <p>数据用于促进制造商与客户之间的销售和运输流程，以改进我们的服务，并确保平台上的用户体验顺畅。</p>

                        <h4>数据共享：</h4>
                        <p>我们仅与必要方（如运输公司或政府机构）共享数据，这些方需要这些信息以正确提供服务，并完全遵守本地和国际法规。</p>

                        <h4>信息保护：</h4>
                        <p>我们采取必要的技术和组织措施，以保护个人信息免受入侵或未经授权的访问，包括使用加密技术和安全软件。</p>

                        <h4>用户权利：</h4>
                        <p>根据沙特阿拉伯的数据保护法规，客户和制造商有权访问、修改或删除其个人数据。</p>

                        <h3>2. 支付政策</h3>
                        <h4>接受的支付方式：</h4>
                        <p>Real Silk贸易平台接受安全和授权的支付方式，如信用卡（Visa和MasterCard）、银行转账，以及在沙特阿拉伯广泛接受的本地支付方式如“Mada”。</p>

                        <h4>支付安全：</h4>
                        <p>所有金融交易都使用标准的支付卡行业数据安全标准（PCI DSS）协议和数据加密技术进行保护。</p>

                        <h4>支付条件：</h4>
                        <p>客户在下订单时需支付应付款项。如果支付方式为银行转账，则需在产品发货前确认付款。</p>

                        <h3>3. 退款政策</h3>
                        <h4>退款：</h4>
                        <p>如果产品不符合规格或存在缺陷，客户有权在收到订单后14天内申请退款。退回的产品必须保持原始状态且未使用。</p>

                        <h4>退款流程：</h4>
                        <p>如果退款申请被批准，款项将通过客户最初使用的相同支付方式退还。</p>

                        <h3>4. 发货和配送政策</h3>
                        <h4>发货选项：</h4>
                        <p>Real Silk贸易平台提供从中国到沙特阿拉伯的多种发货选项，包括空运和海运，以满足客户的需求。</p>

                        <h4>预计送达时间：</h4>
                        <p>订单将在7到14天内送达客户。如有任何延迟，我们将通知客户延迟原因和预计的运输时间。</p>

                        <h4>运费：</h4>
                        <p>运费根据产品重量和送达地点计算。运费可加到最终发票中，也可以根据事先协议单独计算。</p>

                        <h3>5. 制造商权利保护政策</h3>
                        <h4>知识产权保护：</h4>
                        <p>我们致力于保护合作制造商的知识产权，包括保护商标和产品免受仿制或非法使用。</p>

                        <h4>质量保证：</h4>
                        <p>制造商必须提供符合商定规格和质量的产品。如果产品有任何缺陷，制造商需负责更换产品或补偿客户。</p>

                        <h3>6. 制造商支付政策</h3>
                        <h4>支付条件：</h4>
                        <p>Real Silk贸易平台承诺仅在客户收到产品并验证其质量和符合商定规格后向制造商支付款项。</p>

                        <h4>支付流程：</h4>
                        <ul>
                            <li>在产品发货给客户后，通过平台跟踪发货状态。</li>
                            <li>一旦客户确认收到产品并验证其质量和规格，平台将向制造商付款。</li>
                            <li>如果质量或规格出现问题，付款将延迟，直到问题完全解决。</li>
                        </ul>

                        <h4>质量验证程序：</h4>
                        <p>Real Silk贸易平台保留通过客户在收货后监控和检查产品质量的权利。如果发现产品不符合规格或存在缺陷，将通知制造商在完成付款前解决问题。</p>

                        <h4>延迟付款：</h4>
                        <p>如果发货延迟或发现产品存在缺陷，制造商的付款将推迟，直到问题解决，无论是通过更换产品还是对客户进行补偿。</p>

                        <h3>7. 产品进入沙特阿拉伯的要求</h3>
                        <h4>合规证书：</h4>
                        <p>所有制造商必须确保出口到沙特阿拉伯的产品拥有沙特标准、计量和质量组织（SASO）颁发的合规证书。</p>

                        <h4>在SASO的注册：</h4>
                        <p>制造商必须确保在沙特标准、计量和质量组织注册，并提供必要的文件以确保符合当地的质量和安全法规。</p>

                        <h4>发票和原产地证书：</h4>
                        <p>制造商必须为每批货物提供准确的发票和原产地证书，以确保产品合法进入沙特阿拉伯。</p>

                        <h4>包装和包裹：</h4>
                        <p>产品必须按照标准进行包装，以保护产品在运输过程中完好无损，确保到达客户时完好无损。</p>

                        <h4>遵守海关和税收规定：</h4>
                        <p>制造商必须遵守沙特阿拉伯的海关规定，并支付所有必要的税款和关税，以确保产品顺利进入沙特阿拉伯。</p>

                        <h3>8. 制造商和客户的服务条款</h3>
                        <h4>平台使用：</h4>
                        <p>所有用户同意遵守本文件中所述的平台政策和使用条款。任何非法使用或违反服务条款的行为将导致账户暂停或终止。</p>

                        <h4>制造商的义务：</h4>
                        <p>制造商必须提供符合规格的产品，确保按时发货，并遵守所有供应和海关规定。</p>

                        <h4>争议解决：</h4>
                        <p>如双方之间发生任何争议，将根据沙特阿拉伯适用的法律寻求法律解决或仲裁。</p>
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
        </div>
    </main>
@endsection
