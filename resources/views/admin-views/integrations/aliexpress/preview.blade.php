@extends('layouts.back-end.app')

@section('title', 'AliExpress Product Preview')

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h1 mb-0">AliExpress Product Preview</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.aliexpress.index') }}" class="btn btn-secondary">Back to AliExpress</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h4 class="mb-0">Create Preview</h4></div>
            <div class="card-body">
                <form action="{{ route('admin.aliexpress.preview.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label>AliExpress URL or Product ID</label>
                            <input type="text" name="product_input" class="form-control" required
                                   placeholder="https://www.aliexpress.com/item/1005012099875586.html or 1005012099875586">
                        </div>
                        <div class="col-md-3">
                            <label>Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">Auto first main category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} (#{{ $category->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn--primary w-100">Preview</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($preview)
            @if($policyResult['blocked'])
                <div class="alert alert-danger">Blocked: {{ implode(', ', $policyResult['block_reasons']) }}</div>
            @endif
            @if(!empty($policyResult['warnings']))
                <div class="alert alert-warning">Warnings: {{ implode(', ', $policyResult['warnings']) }}</div>
            @endif

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between gap-2">
                            <h4 class="mb-0">Preview #{{ $preview->id }}</h4>
                            <span class="badge badge-soft-{{ $preview->policy_status === 'blocked' ? 'danger' : 'success' }}">{{ $preview->policy_status }}</span>
                        </div>
                        <div class="card-body">
                            <h3>{{ $preview->title ?: '-' }}</h3>
                            <p><strong>Normalized title:</strong> {{ $preview->normalized_title ?: '-' }}</p>
                            <p><strong>AliExpress ID:</strong> {{ $preview->ali_express_product_id }}</p>
                            <p><strong>Supplier price:</strong> {{ $preview->supplier_price ?? 'n/a' }} {{ $preview->currency }}</p>
                            <p><strong>Shipping cost:</strong> {{ $preview->supplier_shipping_price ?? 'n/a' }}</p>
                            <p><strong>Final price:</strong> {{ $preview->final_price ?? 'n/a' }}</p>
                            <p><strong>Estimated profit:</strong> {{ $preview->estimated_profit ?? 'n/a' }}</p>
                            <p><strong>Availability:</strong> {{ $preview->availability_status ?: 'unknown' }}</p>
                            <p><strong>Status:</strong> {{ $preview->status }} @if($preview->message) - {{ $preview->message }} @endif</p>
                            @if($preview->supplier_url)
                                <a href="{{ $preview->supplier_url }}" target="_blank" rel="noopener">Open Supplier Product</a>
                            @endif
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header"><h4 class="mb-0">Variants</h4></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead><tr><th>SKU</th><th>Supplier Price</th><th>Stock</th><th>Properties</th></tr></thead>
                                <tbody>
                                @forelse((array) $preview->variants as $variant)
                                    <tr>
                                        <td>{{ $variant['sku_code'] ?? $variant['id'] ?? '-' }}</td>
                                        <td>{{ $variant['offer_sale_price'] ?? $variant['sku_price'] ?? '-' }}</td>
                                        <td>{{ $variant['stock'] ?? 0 }}</td>
                                        <td>{{ collect($variant['properties'] ?? [])->map(fn($p) => ($p['property_name'] ?? '') . ': ' . ($p['property_value'] ?? ''))->filter()->implode(', ') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">No variants returned.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h4 class="mb-0">Images</h4></div>
                        <div class="card-body d-flex flex-wrap gap-2">
                            @forelse(array_slice((array) $preview->images, 0, 8) as $image)
                                <img src="{{ str_starts_with($image, '//') ? 'https:' . $image : $image }}" style="width: 90px; height: 90px; object-fit: cover;" alt="">
                            @empty
                                <p class="text-muted mb-0">No images returned.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header"><h4 class="mb-0">Actions</h4></div>
                        <div class="card-body d-flex flex-column gap-2">
                            <form action="{{ route('admin.aliexpress.preview.import') }}" method="POST">
                                @csrf
                                <input type="hidden" name="preview_id" value="{{ $preview->id }}">
                                <button class="btn btn-outline-primary w-100">Import Only</button>
                            </form>

                            <form action="{{ route('admin.aliexpress.preview.publish') }}" method="POST">
                                @csrf
                                <input type="hidden" name="preview_id" value="{{ $preview->id }}">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" class="form-control">
                                        <option value="">Auto first main category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ (string) $selectedCategoryId === (string) $category->id ? 'selected' : '' }}>{{ $category->name }} (#{{ $category->id }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="btn btn--primary w-100" {{ $policyResult['blocked'] ? 'disabled' : '' }}>Publish Product</button>
                            </form>

                            <form action="{{ route('admin.aliexpress.preview.publish') }}" method="POST">
                                @csrf
                                <input type="hidden" name="preview_id" value="{{ $preview->id }}">
                                <input type="hidden" name="update_existing" value="1">
                                <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                                <button class="btn btn-outline-success w-100" {{ $policyResult['blocked'] ? 'disabled' : '' }}>Update Existing Product</button>
                            </form>

                            <form action="{{ route('admin.aliexpress.preview.skip') }}" method="POST">
                                @csrf
                                <input type="hidden" name="preview_id" value="{{ $preview->id }}">
                                <input type="hidden" name="message" value="Skipped or blocked by admin.">
                                <button class="btn btn-outline-danger w-100">Skip / Block</button>
                            </form>

                            @if($policyResult['blocked'])
                                <p class="text-danger mt-2 mb-0">Publishing is disabled because policy blocked this item.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @elseif($recentPreviews->count())
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Recent Previews</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead><tr><th>ID</th><th>AliExpress ID</th><th>Title</th><th>Policy</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                        @foreach($recentPreviews as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->ali_express_product_id }}</td>
                                <td style="max-width: 420px; white-space: normal;">{{ $item->title }}</td>
                                <td>{{ $item->policy_status }}</td>
                                <td>{{ $item->status }}</td>
                                <td><a href="{{ route('admin.aliexpress.preview.show', [$item->id]) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
