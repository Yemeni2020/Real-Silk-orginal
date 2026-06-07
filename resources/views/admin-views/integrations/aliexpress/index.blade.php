@extends('layouts.back-end.app')

@section('title', 'AliExpress Import')

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                    <i class="tio-link"></i>
                    AliExpress Import & Publish
                </h2>
                <p class="mb-0 text-muted">Import from AliExpress URL/ID, preview safety checks, then publish to store products.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.aliexpress.catalog.index') }}" class="btn btn-outline-primary">Browse Catalog</a>
                <a href="{{ route('admin.aliexpress.reconnect') }}" class="btn btn--primary">Reconnect AliExpress</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <h4 class="mb-1">Connection</h4>
                    <span class="badge badge-soft-{{ ($connection['connected'] ?? false) ? 'success' : 'danger' }}">
                        {{ $connection['status'] ?? 'disconnected' }}
                    </span>
                    <p class="mb-0 mt-2 text-muted">{{ $connection['message'] ?? 'AliExpress is disconnected.' }}</p>
                    @if(!empty($connection['connected_at']))
                        <small class="text-muted">Connected: {{ $connection['connected_at'] }}</small>
                    @endif
                </div>
                @if(!empty($connection['last_auth_error']))
                    <div class="alert alert-warning mb-0">Last auth error: {{ $connection['last_auth_error'] }}</div>
                @endif
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h4 class="mb-0">Single Import Preview</h4></div>
                    <div class="card-body">
                        <form action="{{ route('admin.aliexpress.preview.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>AliExpress URL or Product ID</label>
                                <input type="text" name="product_input" class="form-control" required
                                       placeholder="https://www.aliexpress.com/item/1005012099875586.html or 1005012099875586">
                            </div>
                            <div class="form-group">
                                <label>Category (optional)</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Auto first main category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} (#{{ $category->id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn--primary">Create Preview</button>
                        </form>
                        <hr>
                        <form action="{{ route('admin.aliexpress.import-publish') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Quick Import + Publish</label>
                                <input type="text" name="product_input" class="form-control" required placeholder="AliExpress URL or Product ID">
                            </div>
                            <div class="form-group">
                                <select name="category_id" class="form-control">
                                    <option value="">Auto first main category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} (#{{ $category->id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-outline--primary">Import + Publish Now</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h4 class="mb-0">Bulk Queue Import</h4></div>
                    <div class="card-body">
                        <form action="{{ route('admin.aliexpress.queue-bulk') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Category (optional)</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Auto first main category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} (#{{ $category->id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>One URL/ID per line</label>
                                <textarea name="bulk_inputs" class="form-control" rows="8" required
                                          placeholder="1005012099875586&#10;https://www.aliexpress.com/item/1005009876543210.html"></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline--primary">Queue Bulk Import</button>
                        </form>
                        <p class="text-muted mt-2 mb-0">Run queue worker to process jobs: <code>php artisan queue:work --tries=1</code></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex flex-wrap justify-content-between gap-2">
                <h4 class="mb-0">Queue Dashboard</h4>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.aliexpress.retry-failed') }}">@csrf<button class="btn btn-sm btn-outline-primary">Retry Failed</button></form>
                    <form method="POST" action="{{ route('admin.aliexpress.cancel-pending') }}">@csrf<button class="btn btn-sm btn-outline-danger">Cancel Pending</button></form>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach(['pending','processing','success','failed','blocked','cancelled'] as $status)
                        <span class="badge badge-soft-info">{{ $status }}: {{ $queueCounts[$status] ?? 0 }}</span>
                    @endforeach
                </div>
                @if($batches->count())
                    <h5>Recent Batches</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Batch</th><th>Total</th><th>Completed</th><th>Progress</th><th>Last Error</th></tr></thead>
                            <tbody>
                            @foreach($batches as $batch)
                                @php($progress = $batch->total > 0 ? round(($batch->completed / $batch->total) * 100) : 0)
                                <tr>
                                    <td>{{ $batch->batch_id }}</td>
                                    <td>{{ $batch->total }}</td>
                                    <td>{{ $batch->completed }}</td>
                                    <td>{{ $progress }}%</td>
                                    <td style="max-width: 300px; white-space: normal;">{{ $batch->last_error ?: '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>ID</th><th>Batch</th><th>Input</th><th>AliExpress ID</th><th>Status</th><th>Attempts</th><th>Store Product</th><th>Message</th><th>Updated</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($queueItems as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->batch_id ?? '-' }}</td>
                            <td style="max-width: 260px; white-space: normal;">{{ $item->source_input }}</td>
                            <td>{{ $item->ali_express_product_id ?? '-' }}</td>
                            <td><span class="badge badge-soft-{{ $item->status === 'success' ? 'success' : ($item->status === 'failed' ? 'danger' : ($item->status === 'blocked' ? 'warning' : 'info')) }}">{{ $item->status }}</span></td>
                            <td>{{ $item->attempts ?? 0 }}</td>
                            <td>{{ $item->store_product_id ?? '-' }}</td>
                            <td style="max-width: 300px; white-space: normal;">{{ $item->error_message ?: ($item->message ?? '-') }}</td>
                            <td>{{ $item->updated_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4">No queue items yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">{{ $queueItems->links() }}</div>
        </div>
    </div>
@endsection
