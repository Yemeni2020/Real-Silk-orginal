@extends('theme-views.layouts.app')

@section('title', translate('About_Us').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 pb-3">
        <div class="page-title overlay py-5 __opacity-half background-custom-fit"
             data-bg-img = {{getStorageImages(path: imagePathProcessing(imageData: (isset($pageTitleBanner['value']) ?json_decode($pageTitleBanner['value'])?->image : null),path: 'banner'),source: theme_asset('assets/img/media/page-title-bg.png'))}}>
            <div class="container">
                <h1 class="absolute-white text-center text-capitalize">{{translate('about_our_company')}}</h1>
            </div>
        </div>
        <div class="container">
            <div class="card my-4">
                <div class="card-body p-lg-4 text-dark page-paragraph">
                    

                    @if(!empty($aboutUs))
                    <div class="for-padding text-justify">
                        
                        <?php 
                            $lang=getDefaultLanguage();
                        ?>
                        <?php
                        if($lang=="sa"){
                        ?>
                        <div class="about-us">
                            <h2>من نحن</h2>
                            <p>مرحبًا بكم في منصة ريل سيلك، المنصة الرائدة في مجال الجملة التي تربط بين مجموعة متنوعة من المصنعين في الصين وعملائنا الكرام في المملكة العربية السعودية. هدفنا هو توفير منتجات عالية الجودة بأفضل الأسعار، مما يمكن عملاءنا من تحقيق النجاح في مشاريعهم التجارية.</p>

                            <h3>رؤيتنا</h3>
                            <p>رؤيتنا هي أن نصبح الخيار الأول في مجال التجارة بالجملة في المنطقة من خلال تقديم تجربة تسوق متميزة وموثوقة، تتيح لعملائنا الوصول إلى مجموعة واسعة من المنتجات المتنوعة.</p>

                            <h3>مهمتنا</h3>
                            <p>نحن ملتزمون بـ:</p>
                            <ul>
                                <li>تقديم منتجات عالية الجودة تلبي أعلى معايير الصناعة.</li>
                                <li>توفير خدمة عملاء ممتازة لدعم عملائنا في كل خطوة من رحلتهم.</li>
                                <li>ضمان عمليات شحن سريعة وآمنة، لجعل عملية الاستيراد من الصين إلى المملكة العربية السعودية سلسة.</li>
                            </ul>

                            <h3>قيمنا</h3>
                            <p>في ريل سيلك، نتمسك بالقيم الأساسية التي توجه كل جانب من أعمالنا:</p>
                            <ul>
                                <li><strong>الجودة:</strong> نؤمن بأن الجودة هي الأساس، ولهذا نتعاون مع أفضل المصنعين لضمان أن منتجاتنا تلبي توقعات عملائنا.</li>
                                <li><strong>الموثوقية:</strong> نحن ملتزمون بتقديم خدمات موثوقة تضمن رضا العملاء.</li>
                                <li><strong>الابتكار:</strong> نسعى باستمرار لتقديم حلول جديدة ومبتكرة لتعزيز تجربة عملائنا.</li>
                            </ul>

                            <h3>فريقنا</h3>
                            <p>يتألف فريق ريل سيلك من محترفين ذوي خبرة عالية في مجالات التجارة، والخدمات اللوجستية، وخدمة العملاء. نحن هنا لدعمكم ومساعدتكم في تحقيق أهدافكم التجارية.</p>

                            <h3>اتصل بنا</h3>
                            <p>إذا كان لديكم أي أسئلة أو استفسارات، فلا تترددوا في التواصل معنا. نحن هنا لخدمتكم!</p>

                            <p>شكرًا لاختياركم ريل سيلك. نتطلع إلى تلبية احتياجاتكم التجارية وتقديم أفضل الحلول لكم ولعملائكم.</p>
                        </div>

                                            
                                            <?php
                                        }
                                        elseif ($lang=="en"){

                                    ?>
                                        {!! $aboutUs !!}
                                    <?php
                                        
                                        ?>
                                        <?php
                                        }
                                        elseif ($lang=="cn"){

                                            ?>
                                            <div class="about-us">
                            <h2>关于我们</h2>
                            <p>欢迎来到Real Silk，领先的批发平台，连接中国各种制造商与我们在沙特阿拉伯的尊贵客户。我们的目标是以最优的价格提供最高质量的产品，帮助我们的客户在商业项目中取得成功。</p>

                            <h3>我们的愿景</h3>
                            <p>我们的愿景是通过提供卓越且可靠的购物体验，成为该地区批发贸易的首选平台，使我们的客户能够访问种类繁多的多样化产品。</p>

                            <h3>我们的使命</h3>
                            <p>我们致力于：</p>
                            <ul>
                                <li>提供符合最高行业标准的高质量产品。</li>
                                <li>提供卓越的客户服务，以支持我们的客户在每一步的旅程中。</li>
                                <li>确保快速且安全的运输流程，使从中国到沙特阿拉伯的进口过程变得顺畅无比。</li>
                            </ul>

                            <h3>我们的价值观</h3>
                            <p>在Real Silk，我们秉持着指导我们业务各个方面的核心价值观：</p>
                            <ul>
                                <li><strong>质量：</strong>我们相信质量是根本，因此我们与顶尖制造商合作，以确保我们的产品符合客户的期望。</li>
                                <li><strong>可靠性：</strong>我们致力于提供可靠的服务，以确保客户满意。</li>
                                <li><strong>创新：</strong>我们不断努力提供新的创新解决方案，以提升客户体验。</li>
                            </ul>

                            <h3>我们的团队</h3>
                            <p>Real Silk的团队由在贸易、物流和客户服务领域拥有丰富经验的专业人士组成。我们随时为您提供支持，帮助您实现您的商业目标。</p>

                            <h3>联系我们</h3>
                            <p>如果您有任何问题或疑问，请不要犹豫与我们联系。我们随时为您提供帮助！</p>

                            <p>感谢您选择Real Silk。我们期待满足您的业务需求，为您和您的客户提供最佳解决方案。</p>
                        </div>

                                            <?php
                                            
                                            ?>
                                            <?php
                                            }
                                        ?>
                                    </div>
                                @else
                                    <div class="d-flex flex-column justify-content-center align-items-center gap-3">
                                        <img src="{{ dynamicStorage(path: 'public/assets/front-end/img/empty-icons/empty-about-us.svg') }}"
                                            alt="{{ translate('brand') }}" class="img-fluid" width="100">
                                        <h5 class="text-muted fs-14 font-semi-bold text-center">{{ translate('there_is_no_about_us') }}</h5>
                                    </div>
                                @endif
                </div>
            </div>
        </div>
    </main>
@endsection
