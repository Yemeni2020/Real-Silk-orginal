@extends('layouts.back-end.app')

@section('title', 'AliExpress Fulfillment')

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h2 class="h1 mb-0">AliExpress Fulfillment - Order #{{ $order->id }}</h2>
            <a href="{{ route('admin.orders.details', [$order->id]) }}" class="btn btn-secondary">Back to Order</a>
        </div>

        @if(!empty($warnings))
            <div class="alert alert-warning">
                <strong>Fulfillment warnings:</strong> {{ implode(', ', $warnings) }}
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-3">Shipping Address (Copy to AliExpress)</h4>
                <div class="row g-2">
                    <div class="col-md-6"><strong>Name:</strong> {{ $fulfillment['customer']['name'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>Phone:</strong> {{ $fulfillment['customer']['phone'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>Email:</strong> {{ $fulfillment['customer']['email'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>Country:</strong> {{ $fulfillment['address']['country'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>State:</strong> {{ $fulfillment['address']['state'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>City:</strong> {{ $fulfillment['address']['city'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>ZIP:</strong> {{ $fulfillment['address']['zip'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>Address 1:</strong> {{ $fulfillment['address']['address_line_1'] ?: '-' }}</div>
                    <div class="col-md-6"><strong>Address 2:</strong> {{ $fulfillment['address']['address_line_2'] ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-3">Save Supplier Fulfillment (Phase 2)</h4>
                <form method="POST" action="{{ route('admin.orders.aliexpress-fulfillment.save', [$order->id]) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">AliExpress Supplier Order ID *</label>
                            <input type="text" name="supplier_order_id" class="form-control" required placeholder="e.g. 816734829173482">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tracking Number</label>
                            <input type="text" name="tracking_id" class="form-control" value="{{ $order->third_party_delivery_tracking_id }}" placeholder="e.g. LP00XXXXXXCN">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Delivery Service Name</label>
                            <input type="text" name="delivery_service_name" class="form-control" value="{{ $order->delivery_service_name }}" placeholder="AliExpress Standard Shipping">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save Fulfillment Info</button>
                    </div>
                </form>
                <div class="mt-3">
                    <strong>Current saved service:</strong> {{ $order->delivery_service_name ?: '-' }}<br>
                    <strong>Current saved tracking:</strong> {{ $order->third_party_delivery_tracking_id ?: '-' }}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">AliExpress Items</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Variant</th>
                            <th>AliExpress ID</th>
                            <th>Supplier Mapping (Phase 3)</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($fulfillment['items'] as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['name'] ?: '-' }}</td>
                                <td>{{ $item['qty'] }}</td>
                                <td>{{ $item['variant'] ?: '-' }}</td>
                                <td>{{ $item['ali_express_product_id'] ?: '-' }}</td>
                                <td style="min-width: 320px;">
                                    <form method="POST" action="{{ route('admin.orders.aliexpress-fulfillment.item-save', [$order->id]) }}">
                                        @csrf
                                        <input type="hidden" name="order_detail_id" value="{{ $item['order_detail_id'] }}">
                                        <div class="mb-2">
                                            <select name="status" class="form-control form-control-sm" required>
                                                @foreach(['not_started','supplier_order_pending','supplier_order_placed','supplier_paid','supplier_shipped','tracking_received','delivered','failed','cancelled','refunded'] as $status)
                                                    <option value="{{ $status }}" {{ $item['mapping']['status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="supplier_order_id" class="form-control form-control-sm"
                                                   placeholder="Supplier order ID"
                                                   value="{{ $item['mapping']['supplier_order_id'] }}">
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="supplier_line_id" class="form-control form-control-sm"
                                                   placeholder="Supplier line/sku ID"
                                                   value="{{ $item['mapping']['supplier_line_id'] }}">
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="supplier_order_url" class="form-control form-control-sm"
                                                   placeholder="Supplier order URL"
                                                   value="{{ $item['mapping']['supplier_order_url'] }}">
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-7"><input type="number" step="0.01" name="supplier_paid_amount" class="form-control form-control-sm" placeholder="Paid amount" value="{{ $item['mapping']['supplier_paid_amount'] }}"></div>
                                            <div class="col-5"><input type="text" name="supplier_currency" class="form-control form-control-sm" placeholder="Currency" value="{{ $item['mapping']['supplier_currency'] }}"></div>
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="carrier" class="form-control form-control-sm"
                                                   placeholder="Carrier"
                                                   value="{{ $item['mapping']['carrier'] }}">
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="tracking_number" class="form-control form-control-sm"
                                                   placeholder="Tracking number"
                                                   value="{{ $item['mapping']['tracking_number'] }}">
                                        </div>
                                        <div class="mb-2">
                                            <textarea name="note" rows="2" class="form-control form-control-sm"
                                                      placeholder="Internal note">{{ $item['mapping']['note'] }}</textarea>
                                        </div>
                                        @if($item['mapping']['last_error'])
                                            <div class="text-danger small mb-2">{{ $item['mapping']['last_error'] }}</div>
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-success">Save Item</button>
                                    </form>
                                </td>
                                <td class="d-flex flex-wrap gap-2">
                                    @if($item['supplier_url'])
                                        <a href="{{ $item['supplier_url'] }}" class="btn btn-sm btn-primary" target="_blank">
                                            Open Product
                                        </a>
                                    @endif
                                    <a href="{{ $item['search_url'] }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        Search Similar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    Step: open each product, choose matching variant, set quantity, paste shipping address, place order in AliExpress, then update tracking in your admin order.
                </div>
            </div>
        </div>
    </div>
@endsection
