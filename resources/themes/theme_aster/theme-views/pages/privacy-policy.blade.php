@extends('theme-views.layouts.app')

@section('title', translate('privacy_Policy').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 pb-3">
        <div class="page-title overlay py-5 __opacity-half background-custom-fit"
             data-bg-img = {{getStorageImages(path: imagePathProcessing(imageData: (isset($pageTitleBanner['value']) ?json_decode($pageTitleBanner['value'])?->image : null),path: 'banner'),source: theme_asset('assets/img/media/page-title-bg.png'))}}>
        <div class="container">
                <h1 class="absolute-white text-center text-capitalize">{{translate('privacy_policy')}}</h1>
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
                    {!! $privacyPolicy !!}
                    <?php
                }
                elseif ($lang=="en"){

               ?>
<div class="privacy-policy">
    <h2>Privacy Policy</h2>
    <h3>Introduction:</h3>
    <p>At the Real Silk trading platform, we are committed to protecting the privacy of all users, whether they are customers or manufacturers. This policy outlines how personal data is collected, used, and protected, as well as the controls in place to safeguard this data in accordance with applicable regulations.</p>

    <h3>Information Collection:</h3>
    <p>We collect personal data from customers and manufacturers for the purpose of managing business operations, including: name, contact information, shipping details, and product information.</p>

    <h3>Data Usage:</h3>
    <p>The data is used to facilitate the sales and shipping process between manufacturers and customers, to improve our services, and to ensure a smooth user experience on the platform.</p>

    <h3>Data Sharing:</h3>
    <p>We share data only with necessary parties (such as shipping companies or government entities) that require this information to provide services properly, in full compliance with local and international regulations.</p>

    <h3>Information Protection:</h3>
    <p>We take the necessary technical and organizational measures to protect personal information from breaches or unauthorized access, including the use of encryption technologies and security software.</p>

    <h3>User Rights:</h3>
    <p>Customers and manufacturers have the right to access, modify, or delete their personal data upon request, in accordance with data protection regulations applicable in Saudi Arabia.</p>
</div>

               <?php
                
                ?>
                <?php
                }
                elseif ($lang=="cn"){

                    ?>
<div class="privacy-policy">
    <h2>隐私政策</h2>
    <h3>简介：</h3>
    <p>在Real Silk贸易平台，我们承诺保护所有用户的隐私，无论是客户还是制造商。本政策说明了如何收集、使用和保护个人数据，以及根据适用法规保护这些数据的控制措施。</p>

    <h3>信息收集：</h3>
    <p>我们从客户和制造商处收集个人数据，以便管理业务操作，包括：姓名、联系方式、运输详细信息和产品信息。</p>

    <h3>数据使用：</h3>
    <p>数据用于促进制造商与客户之间的销售和运输流程，以改进我们的服务，并确保平台上的用户体验顺畅。</p>

    <h3>数据共享：</h3>
    <p>我们仅与必要方（如运输公司或政府机构）共享数据，这些方需要这些信息以正确提供服务，并完全遵守本地和国际法规。</p>

    <h3>信息保护：</h3>
    <p>我们采取必要的技术和组织措施，以保护个人信息免受入侵或未经授权的访问，包括使用加密技术和安全软件。</p>

    <h3>用户权利：</h3>
    <p>客户和制造商有权根据沙特阿拉伯适用的数据保护法规，访问、修改或删除其个人数据。</p>
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
