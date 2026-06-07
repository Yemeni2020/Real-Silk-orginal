@extends('layouts.back-end.app')

@section('title', 'AliExpress Catalog')

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h1 mb-0">AliExpress Catalog</h2>
                <p class="text-muted mb-0">Search AliExpress products, then preview, import, or publish through the existing validation workflow.</p>
            </div>
            <a href="{{ route('admin.aliexpress.preview') }}" class="btn btn-outline-primary">Import by URL / ID</a>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.aliexpress.catalog.search') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Keyword</label>
                            <input type="text" name="keyword" class="form-control" value="{{ $filters['keyword'] ?? '' }}" placeholder="dress, phone case, silk scarf">
                        </div>
                        <div class="col-md-3">
                            <label>AliExpress Category</label>
                            <select name="category_id" class="form-control" {{ ($categories['success'] ?? false) ? '' : 'disabled' }}>
                                <option value="">Any category</option>
                                @foreach(($categories['items'] ?? []) as $category)
                                    <option value="{{ $category['id'] }}" {{ (string) ($filters['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' }}>
                                        {{ $category['name'] }} (#{{ $category['id'] }})
                                    </option>
                                @endforeach
                            </select>
                            @if(!($categories['success'] ?? false))
                                <small class="text-muted">Category browsing is unavailable for this API account.</small>
                            @elseif($categories['fallback'] ?? false)
                                <small class="text-muted">Showing default AliExpress categories because live category API returned no list.</small>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label>Min Price</label>
                            <input type="number" step="0.01" min="0" name="min_price" class="form-control" value="{{ $filters['min_price'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label>Max Price</label>
                            <input type="number" step="0.01" min="0" name="max_price" class="form-control" value="{{ $filters['max_price'] ?? '' }}">
                        </div>
                        <div class="col-md-1">
                            <label>Page</label>
                            <input type="number" min="1" name="page" class="form-control" value="{{ $filters['page'] ?? 1 }}">
                        </div>
                        <div class="col-md-2">
                            <label>Min Rating</label>
                            <input type="number" step="0.1" min="0" max="5" name="min_rating" class="form-control" value="{{ $filters['min_rating'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label>Min Orders</label>
                            <input type="number" min="0" name="min_orders" class="form-control" value="{{ $filters['min_orders'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label>Ship To</label>
                            <input type="text" maxlength="2" name="ship_to_country" class="form-control" value="{{ $filters['ship_to_country'] ?? config('aliexpress.default_country') }}" placeholder="US">
                        </div>
                        <div class="col-md-2">
                            <label>Sort</label>
                            <select name="sort" class="form-control">
                                <option value="">Default</option>
                                @foreach(['relevance' => 'Relevance', 'price_asc' => 'Price low-high', 'price_desc' => 'Price high-low', 'orders_desc' => 'Orders', 'rating_desc' => 'Rating'] as $value => $label)
                                    <option value="{{ $value }}" {{ ($filters['sort'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Per Page</label>
                            <input type="number" min="1" max="50" name="per_page" class="form-control" value="{{ $filters['per_page'] ?? 20 }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn--primary">Search</button>
                            <a href="{{ route('admin.aliexpress.catalog.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(!($results['success'] ?? true))
            <div class="alert alert-warning">
                {{ $results['message'] ?? 'AliExpress catalog search is not available for this API account. You can still import by product URL or ID.' }}
            </div>
        @elseif(!empty($results['message']))
            <div class="alert alert-info">{{ $results['message'] }}</div>
        @endif

        @if(!empty($results['warnings']))
            <div class="alert alert-warning">
                <strong>API warnings:</strong> {{ implode(', ', array_slice((array) $results['warnings'], 0, 3)) }}
            </div>
        @endif

        @if(count($results['items'] ?? []))
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    Showing {{ $results['meta']['count'] ?? count($results['items']) }} result(s)
                    @if(!empty($results['meta']['total_results']))
                        of {{ $results['meta']['total_results'] }}
                    @endif
                </div>
                <div class="text-muted">Page {{ $results['meta']['current_page'] ?? 1 }}</div>
            </div>

            <div class="row g-3">
                @foreach($results['items'] as $item)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column gap-2">
                                <div class="d-flex gap-3">
                                    <div style="width: 92px; flex: 0 0 92px;">
                                        @if($item['image'])
                                            <img src="{{ $item['image'] }}" alt="" style="width: 92px; height: 92px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 92px; height: 92px;">No image</div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1" style="line-height: 1.35;">{{ $item['title'] ?: 'Untitled AliExpress product' }}</h5>
                                        <div class="small text-muted">AE #{{ $item['product_id'] }}</div>
                                        @if(!empty($item['warnings']))
                                            <span class="badge badge-soft-warning">Incomplete data</span>
                                        @endif
                                        <span class="badge badge-soft-{{ $item['is_available'] ? 'success' : 'danger' }}">{{ $item['is_available'] ? 'Available' : 'Unavailable' }}</span>
                                    </div>
                                </div>

                                <div class="row small text-muted mt-2">
                                    <div class="col-6"><strong>Price:</strong> {{ $item['price'] ?? 'n/a' }} {{ $item['currency'] }}</div>
                                    <div class="col-6"><strong>Shipping:</strong> {{ $item['shipping_price'] ?? 'n/a' }}</div>
                                    <div class="col-6"><strong>Rating:</strong> {{ $item['rating'] ?? 'n/a' }}</div>
                                    <div class="col-6"><strong>Orders:</strong> {{ $item['orders'] ?? 'n/a' }}</div>
                                    <div class="col-12"><strong>Category:</strong> {{ $item['category_name'] ?? $item['category_id'] ?? 'n/a' }}</div>
                                    <div class="col-12"><strong>Delivery:</strong> {{ $item['delivery_estimate'] ?? 'n/a' }}</div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-auto pt-2">
                                    <form method="POST" action="{{ route('admin.aliexpress.catalog.preview', [$item['product_id']]) }}">@csrf<button class="btn btn-sm btn-outline-primary">Preview</button></form>
                                    <form method="POST" action="{{ route('admin.aliexpress.catalog.import', [$item['product_id']]) }}">@csrf<button class="btn btn-sm btn-outline-success">Import Only</button></form>
                                    <form method="POST" action="{{ route('admin.aliexpress.catalog.publish', [$item['product_id']]) }}">@csrf<button class="btn btn-sm btn--primary">Publish</button></form>
                                    @if($item['product_url'])
                                        <a href="{{ $item['product_url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-light">Source</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $currentPage = (int) ($results['meta']['current_page'] ?? 1);
                $totalPages = $results['meta']['total_pages'] ?? null;
                $query = request()->query();
            @endphp
            <div class="d-flex justify-content-between mt-4">
                @if($currentPage > 1)
                    <a class="btn btn-outline-primary" href="{{ route('admin.aliexpress.catalog.search', array_merge($query, ['page' => $currentPage - 1])) }}">Previous</a>
                @else
                    <span></span>
                @endif
                @if(!$totalPages || $currentPage < $totalPages)
                    <a class="btn btn-outline-primary" href="{{ route('admin.aliexpress.catalog.search', array_merge($query, ['page' => $currentPage + 1])) }}">Next</a>
                @endif
            </div>
        @elseif(empty($results['message']) && ($results['success'] ?? true))
            <div class="card"><div class="card-body text-center text-muted py-5">No AliExpress products found for these filters.</div></div>
        @endif
    </div>
@endsection
