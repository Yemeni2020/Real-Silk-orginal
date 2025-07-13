namespace App\Services;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OtoShippingService
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.oto.api_key');
        $this->baseUrl = config('services.oto.base_url');
    }

    public function createShipment(Order $order): ?array
    {
        try {
            $payload = [
                'recipient_name' => $order->shipping_address_data->name ?? 'اسم غير معروف',
                'recipient_phone' => $order->shipping_address_data->phone ?? '0000000000',
                'address' => $order->shipping_address_data->address,
                'city' => $order->shipping_address_data->city,
                'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                'amount' => $order->order_amount,
                'order_reference' => $order->id,
                'items' => $this->getOrderItems($order),
            ];

            $response = $this->client->post("{$this->baseUrl}/api/v1/shipments", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody(), true);

            // Save tracking number to order
            $order->update([
                'tracking_number' => $data['tracking_number'] ?? null,
                'oto_order_id' => $data['order_id'] ?? null,
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error('Oto API error: ' . $e->getMessage());
            return null;
        }
    }

    protected function getOrderItems(Order $order): array
    {
        return $order->details->map(function ($item) {
            return [
                'name' => $item->product->name,
                'sku' => $item->product->code,
                'quantity' => $item->quantity,
                'weight' => $item->product->weight ?? 1,
            ];
        })->toArray();
    }
}