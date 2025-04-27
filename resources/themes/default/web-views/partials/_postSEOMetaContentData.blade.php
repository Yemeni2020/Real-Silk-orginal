@if(isset($post) && isset($metaContentData))
    @if($metaContentData?->title)
        <meta property="title" content="{{ $metaContentData?->title }}">
        <meta property="og:title" content="{{ $metaContentData?->title }}">
        <meta property="twitter:title" content="{{ $metaContentData?->title }}">
    @else
        <meta property="title" content="{{ $post?->name }}">
        <meta property="og:title" content="{{ $post?->name }}">
        <meta property="twitter:title" content="{{ $post?->name }}">
    @endif

    @if($metaContentData?->description)
        <meta name="description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
        @if(1 == 2)
        <meta property="description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
        @endif

        <meta property="og:description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
        <meta property="twitter:description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
    @else
        <meta property="og:description" content="@foreach(explode(' ',$post['name']) as $keyword) {{$keyword.' , '}} @endforeach">
        <meta property="twitter:description" content="@foreach(explode(' ',$post['name']) as $keyword) {{$keyword.' , '}} @endforeach">
    @endif

    @if(1 == 2)
    <meta property="keywords" content="@foreach(explode(' ',$post['name']) as $keyword) {{$keyword.' , '}} @endforeach">
    @endif
    @if($metaContentData?->meta_keywords)

    <meta name="keywords" content="{{$metaContentData?->meta_keywords}}">
    @endif


    @if($post->added_by == 'seller')
        <meta property="author" content="{{ $post->seller->shop?$post->seller->shop->name:$post->seller->f_name}}">
    @elseif($post->added_by == 'admin')
        <meta property="author" content="{{$web_config['name']->value}}">
    @endif

    <meta property="og:image" content="{{ $metaContentData?->image_full_url['path'] }}">
    <meta property="twitter:image" content="{{ $metaContentData?->image_full_url['path'] }}">

    <meta property="og:url" content="{{ route('product', [$post->slug]) }}">
    <meta property="twitter:url" content="{{ route('product', [$post->slug]) }}">

    @if($metaContentData?->index != 'noindex')
        <meta property="robots" content="index">
    @endif

    @if($metaContentData?->no_follow || $metaContentData?->no_image_index || $metaContentData?->no_archive || $metaContentData?->no_snippet)
        <meta property="robots" content="{{ ($metaContentData?->no_follow ? 'nofollow' : '') . ($metaContentData?->no_image_index ? ' noimageindex' : '') . ($metaContentData?->no_archive ? ' noarchive' : '') . ($metaContentData?->no_snippet ? ' nosnippet' : '') }}">
    @endif

    @if($metaContentData?->meta_max_snippet)
        <meta property="robots" content="max-snippet{{ $metaContentData?->max_snippet_value ? ': ' . $metaContentData?->max_snippet_value : '' }}">
    @endif

    @if($metaContentData?->max_video_preview)
        <meta property="robots" content="max-video-preview{{ $metaContentData?->max_video_preview_value ? ': ' . $metaContentData?->max_video_preview_value : '' }}">
    @endif

    @if($metaContentData?->max_image_preview)
        <meta property="robots" content="max-image-preview{{ $metaContentData?->max_image_preview_value ? ': ' . $metaContentData?->max_image_preview_value : '' }}">
    @endif
@endif
