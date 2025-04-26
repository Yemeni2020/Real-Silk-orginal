@extends('layouts.front-end.app')
@section('title',translate('posts'))


@push('css_or_js')
<meta property="og:image" content="{{$web_config['web_logo']['path']}}" />
<meta property="og:title" content="Products of {{$web_config['name']}} " />
<meta property="og:url" content="{{env('APP_URL')}}">
<meta property="og:description"
    content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">

<meta property="twitter:card" content="{{$web_config['web_logo']['path']}}" />
<meta property="twitter:title" content="posts of {{$web_config['name']}}" />
<meta property="twitter:url" content="{{env('APP_URL')}}">
<meta property="twitter:description"
    content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)),0,160) }}">
@endpush

@section('content')
<div class="container py-3" dir="{{request()->cookie('direction', 'ltr')}}">
    <div class="search-page-header">
        <div>
            <h5 class="font-semibold mb-1 text-capitalize">
                {{ translate('posts') }} {{ isset($data['brand_name']) ? '('.$data['brand_name'].')' : ''}}
            </h5>
            <div><span class="view-page-item-count">{{$posts->total()}}</span> {{translate('items_found')}}</div>
        </div>
        
    </div>

</div>
<div class="container">

    <form method="GET" action="{{ route('posts') }}" id="form-filter-post" class="mb-4">
        <div class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="{{ translate('Search posts...') }}"
                    value="{{ old('search', $filters['search'] ?? '') }}">
            </div>

            <div class="col-md-5">
                <select name="category_id" id="category_id" class="form-select custom-select filter-on-post-filter-change">
                    <option value="">{{ translate('All Categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (isset($filters['category_id']) && $filters['category_id'] == $category->id) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">{{ translate('Filter') }}</button>
            </div>
        </div>
    </form>
    <div id="ajax-posts">
        @include('web-views.posts.posts-card', ['posts' => $posts])

    </div>
</div>
@endsection

@push('script')
<script src="{{ theme_asset(path: 'public/assets/front-end/js/product-view.js') }}"></script>
@endpush