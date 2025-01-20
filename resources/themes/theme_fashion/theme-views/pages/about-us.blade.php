@extends('theme-views.layouts.app')

@section('title', translate('about_us').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')

<main class="main-content d-flex flex-column gap-3 pb-3">
    <div class="page_title_overlay py-5">
        <img loading="lazy" class="bg--img" alt="{{ translate('about_us') }}"
             src="{{getStorageImages(path: imagePathProcessing(imageData: (isset($pageTitleBanner['value']) ?json_decode($pageTitleBanner['value'])?->image : null),path: 'banner'),source: theme_asset('assets/img/page-title-bg.png'))}} ">

        <div class="container">
            <h1 class="text-center text-capitalize">{{translate('about_our_company')}}</h1>
        </div>
    </div>

    <div class="container">
        <div class="card my-4">
            <div class="card-body p-lg-5 text-dark page-paragraph">
                {!! $aboutUs !!}
            </div>
        </div>
    </div>
</main>

@endsection
