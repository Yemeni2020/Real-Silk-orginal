<div class="list-group w-100">
        @foreach($posts as $post)
            <a href="{{ route('posts.show', $post->slug) }}" class="list-group-item list-group-item-action mb-3 shadow-sm">
                <div class="d-flex align-items-center">
                    @if($post->thumbnail)
                        <img src="{{ getStorageImages(path: $post->thumbnail_full_url, type: 'product') }}" class="img-thumbnail me-3" style="width: 200px; height:200px;" alt="{{ $post->title }}">
                    @endif
                    <div>
                        <h5 class="mb-1">{{ $post->title }}</h5>
                        <p class="mb-1 text-muted small">{{ Str::limit(strip_tags($post->details), 100, '...') }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-4">
        @if ($posts instanceof \Illuminate\Pagination\Paginator || $posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $posts->withQueryString()->links() }}
        @endif

    </div>