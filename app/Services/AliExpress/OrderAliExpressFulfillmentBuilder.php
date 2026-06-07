<?php

namespace App\Services\AliExpress;

use App\Models\AliExpressProduct;
use App\Models\AliExpressOrderItemFulfillment;
use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class OrderAliExpressFulfillmentBuilder
{
    public function build(Order $order): array
    {
        $address = $order->shipping_address_data ?? $order->billing_address_data;

        return [
            'order_id' => $order->id,
            'customer' => [
                'name' => (string) ($address?->contact_person_name ?? ''),
                'phone' => (string) ($address?->phone ?? ''),
                'email' => (string) ($address?->email ?? ''),
            ],
            'address' => [
                'country' => (string) ($address?->country ?? ''),
                'state' => (string) ($address?->state ?? ''),
                'city' => (string) ($address?->city ?? ''),
                'zip' => (string) ($address?->zip ?? ''),
                'address_line_1' => (string) ($address?->address ?? ''),
                'address_line_2' => (string) ($address?->address1 ?? ''),
            ],
            'items' => $this->buildItems($order),
        ];
    }

    private function buildItems(Order $order): Collection
    {
        $itemMappings = AliExpressOrderItemFulfillment::query()
            ->where('order_id', $order->id)
            ->get()
            ->keyBy('order_detail_id');

        return collect($order->details ?? [])->map(function ($detail) use ($itemMappings) {
            $productCode = (string) ($detail?->productAllStatus?->code ?? '');
            $fallbackCode = (string) Arr::get((array) json_decode((string) $detail?->product_details, true), 'code', '');
            $code = $productCode !== '' ? $productCode : $fallbackCode;

            $aliExpressId = $this->extractAliExpressId($code);
            $aliExpressProduct = null;
            if ($aliExpressId !== null) {
                $aliExpressProduct = AliExpressProduct::query()
                    ->where('ali_express_product_id', $aliExpressId)
                    ->first();
            }

            $supplierUrl = (string) ($aliExpressProduct?->supplier_url ?? '');
            if ($supplierUrl === '' && $aliExpressId !== null) {
                $supplierUrl = 'https://www.aliexpress.com/item/' . $aliExpressId . '.html';
            }

            $mapping = $itemMappings->get($detail?->id);

            return [
                'order_detail_id' => $detail?->id,
                'name' => (string) ($detail?->productAllStatus?->name ?? Arr::get((array) json_decode((string) $detail?->product_details, true), 'name', '')),
                'qty' => (int) ($detail?->qty ?? 0),
                'variant' => (string) ($detail?->variant ?? ''),
                'code' => $code,
                'ali_express_product_id' => $aliExpressId,
                'supplier_url' => $supplierUrl,
                'search_url' => 'https://www.aliexpress.com/wholesale?SearchText=' . urlencode((string) ($detail?->productAllStatus?->name ?? '')),
                'mapping' => [
                    'status' => (string) ($mapping?->status ?? 'not_started'),
                    'supplier_order_id' => (string) ($mapping?->supplier_order_id ?? ''),
                    'supplier_line_id' => (string) ($mapping?->supplier_line_id ?? ''),
                    'supplier_order_url' => (string) ($mapping?->supplier_order_url ?? ''),
                    'supplier_paid_amount' => $mapping?->supplier_paid_amount,
                    'supplier_currency' => (string) ($mapping?->supplier_currency ?? ''),
                    'carrier' => (string) ($mapping?->supplier_carrier ?? $mapping?->carrier ?? ''),
                    'tracking_number' => (string) ($mapping?->supplier_tracking_number ?? $mapping?->tracking_number ?? ''),
                    'note' => (string) ($mapping?->notes ?? $mapping?->note ?? ''),
                    'last_error' => (string) ($mapping?->last_error ?? ''),
                    'placed_at' => $mapping?->placed_at,
                    'tracking_synced_at' => $mapping?->tracking_synced_at,
                ],
                'supplier_status' => (string) ($aliExpressProduct?->sync_status ?? ''),
            ];
        })->values();
    }

    private function extractAliExpressId(string $code): ?string
    {
        if (preg_match('/^AE-(\d{10,20})$/', trim($code), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
