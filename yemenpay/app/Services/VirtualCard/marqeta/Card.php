<?php

namespace App\Services\VirtualCard\marqeta;

use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use DateTime;
use Exception;
use GuzzleHttp\Client;

class Card
{
    private static Client $client;
//    private static string $baseUrl = 'https://sandbox-api.marqeta.com/v3/';
    private static string $baseUrl = 'https://api.marqeta.com/v3/';
    private static string $accessKey;
    private static string $appKey;
    private static array $headers;

    public static function init()
    {
        try {
            $cardMethod = VirtualCardMethod::where('code', 'marqeta')->first();

            self::$client = new Client();
            self::$accessKey = $cardMethod->parameters?->secret_key;
            self::$appKey = $cardMethod->parameters?->public_key;

            self::$headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(self::$appKey . ':' . self::$accessKey),
            ];
            self::$client = new Client([
                'base_uri' => self::$baseUrl,
                'timeout' => 10.0,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function cardRequest($cardOrder, $operation)
    {
        return match ($operation) {
            'create' => self::createCardWorkflow($cardOrder),
            'block' => self::blockCard($cardOrder),
            'unblock' => self::unblockCard($cardOrder),
            'fundApprove' => self::fundAddCard($cardOrder),
            default => [
                'status' => 'error',
                'data' => 'Invalid operation'
            ],
        };
    }

    public static function createUser($cardOrder)
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $user = $cardOrder?->form_input;
            $userData = [
                "honorific" => "",
                "first_name" => $user->FirstName->field_value ?? "",
                "middle_name" => "",
                "last_name" => $user->LastName->field_value ?? "",
                "email" => $user->CustomerEmail->field_value ?? "",
                "address1" => $user->Line1->field_value ?? "",
                "address2" => $user->Line2->field_value ?? "",
                "city" => $user->City->field_value ?? "",
                "state" => $user->State->field_value ?? "",
                "country" => $user->Country->field_value ?? "",
                "postal_code" => "",
                "birth_date" => $user->DateOfBirth->field_value ?? "",
                "ssn" => "",
                "passport_number" => "",
                "passport_expiration_date" => "",
                "id_card_number" => "",
                "id_card_expiration_date" => "",
                "nationality" => "",
            ];

            $response = self::$client->post('users', [
                'headers' => self::$headers,
                'json' => $userData,
            ]);
            if ($response->getStatusCode() === 201) {
                $responseData = json_decode($response->getBody(), true);
                return $responseData;
            } else {
                return [
                    'status' => 'error',
                    'data' => 'Failed to create user. Status Code: ' . $response->getStatusCode()
                ];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => 'An unexpected error occurred: ' . $e->getMessage()
            ];
        }
    }

    public static function getCardProductToken()
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $response = self::$client->get('cardproducts', [
                'headers' => self::$headers,
            ]);
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);

                if (isset($responseData['data']) && count($responseData['data']) > 0) {
                    $cardProductToken = $responseData['data'][0]['token'];
                    return $cardProductToken;
                } else {
                    return [
                        'status' => 'error',
                        'data' => 'No card products found.'
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'data' => 'Failed to retrieve card products. Status Code: ' . $response->getStatusCode()
                ];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => 'An unexpected error occurred: ' . $e->getMessage()
            ];
        }
    }

    public static function createCard($userToken, $cardProductToken)
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $cardData = [
                "user_token" => $userToken,
                "card_product_token" => $cardProductToken,
            ];
            $response = self::$client->post('cards', [
                'query' => [
                    'show_cvv_number' => 'true',
                    'show_pan' => 'true',
                ],
                'headers' => self::$headers,
                'json' => $cardData,
            ]);

            if ($response->getStatusCode() === 201) {
                $responseData = json_decode($response->getBody(), true);
                return $responseData;
            } else {
                throw new Exception("Failed to create card. Status Code: " . $response->getStatusCode());
            }
        } catch (\GuzzleHttp\Exception\RequestException|\Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }


    public static function createCardWorkflow($cardOrder): array
    {
        try {
            $createdUser = self::createUser($cardOrder);

            if (!isset($createdUser['token'])) {
                throw new Exception('User creation failed. User token not found.');
            }
            $userToken = $createdUser['token'];

            $cardProductToken = self::getCardProductToken();
            if (empty($cardProductToken)) {
                throw new Exception('Failed to retrieve card product token.');
            }
            $card = self::createCard($userToken, $cardProductToken);
            if (empty($card)) {
                throw new Exception('Card creation failed.');
            }
            $card = (object)$card;

            // $card = self::showCardPan($cardToken = "6865be0a-f8d9-4ac8-ac68-1b617757940d");
            // $card = self::simulateAuthorization($card->token, $card->barcode, 10);

            $preprocessedData = self::preprocessCardData($card, $createdUser ?? null);

            $expirationTime = $card->expiration_time;
            $expiryDate = (new DateTime($expirationTime))->format('Y-m-d');
            return [
                'status' => 'success',
                'name_on_card' => $preprocessedData['name_on_card'] ?? null,
                'card_id' => $card->token,
                'brand' => $card->brand ?? null,
                'expiry_date' => $expiryDate,
                'cvv' => $card->cvv_number ?? null,
                'card_number' => $card->pan ?? null,
                'balance' => $card->balance ?? null,
                'data' => $preprocessedData,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage(),
            ];
        }
    }

    public static function updateCardStatus($cardOrder, $status)
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $cardToken = $cardOrder->card_Id;
            $reasonCode = $cardOrder->reason;

            $allowedStates = ['ACTIVE', 'SUSPENDED', 'TERMINATED', 'UNSUPPORTED', 'UNACTIVATED', 'LIMITED'];
            if (!in_array($status, $allowedStates)) {
                throw new Exception("Invalid status: $status. Allowed values are: " . implode(", ", $allowedStates));
            }
            $transitionData = [
                'card_token' => $cardToken,
                'channel' => 'API',
                'state' => $status,
                'reason_code' => $reasonCode,
            ];

            $response = self::$client->post("cardtransitions", [
                'headers' => self::$headers,
                'json' => $transitionData,
            ]);
            if ($response->getStatusCode() === 201) {
                $responseData = json_decode($response->getBody(), true);
                return [
                    'status' => $responseData['state'] === $status ? 'success' : 'error',
                    'data' => $responseData['state'] === $status ? ucfirst($status) . ' successfully' : 'Card status update failed',
                ];
            } else {
                throw new Exception("Failed to update card status. Status Code: " . $response->getStatusCode());
            }
        } catch (\GuzzleHttp\Exception\RequestException|Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }

    public static function blockCard($cardOrder): array
    {
        return self::updateCardStatus($cardOrder, 'SUSPENDED');
    }

    public static function unblockCard($cardOrder): array
    {
        return self::updateCardStatus($cardOrder, 'ACTIVE');
    }

    public static function fundAddCard($cardOrder)
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $cardToken = $cardOrder->card_Id;
            $amount = $cardOrder->fund_amount;
            $barcode = $cardOrder->card_info->barcode->field_value;
            $simulationData = [
                "card_token" => $cardToken,
                "amount" => $amount,
                "mid" => $barcode,
            ];

            $response = self::$client->post('simulate/authorization', [
                'headers' => self::$headers,
                'json' => $simulationData,
            ]);

            if ($response->getStatusCode() === 201) {
                $responseData = json_decode($response->getBody(), true);

                $cardOrder->balance += $amount;
                $balance = $responseData['transaction']['gpa']['ledger_balance'] ?? $cardOrder->balance;
                return [
                    'status' => 'success',
                    'balance' => $balance,
                    'data' => 'Funds successfully added to card balance',
                ];
            } else {
                throw new Exception("Failed to simulate authorization. Status Code: " . $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage(),
            ];
        }
    }

    public static function getTrx($card_id)
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $cardOrder = VirtualCardOrder::where('card_id', $card_id)->firstOrFail();
            $response = self::$client->get('transactions', [
                'headers' => self::$headers,
                'query' => [
                    'card_token' => $card_id,
                    'count' => 10,
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            $transactions = $responseData['data'];
            foreach ($transactions as $transaction) {
                $transaction = (object)$transaction;
                $formattedData = [
                    'card_id' => $transaction->identifier,
                    'amount' => abs($transaction->amount),
                    'balance' => $transaction->gpa['ledger_balance'],
                    'type' => $transaction->type,
                    'currency' => strtoupper($transaction->currency_code),
                    'date' => $transaction->issuer_received_time,
                    'narration' => $transaction->gpa_order['funding_source_token'],
                ];
                VirtualCardTransaction::updateOrCreate(
                    ['user_id' => auth()->id(), 'card_id' => $card_id, 'trx_id' => $transaction->token],
                    [
                        'card_order_id' => $cardOrder->id,
                        'data' => $formattedData,
                        'amount' => abs($transaction->amount),
                        'currency' => strtoupper($transaction->currency_code),
                    ]
                );
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retrieve transactions: ' . $e->getMessage());
        }
    }


    public static function preprocessCardData($virtualCard, $user): array
    {
        $user = (object)$user;

        $expiration = $virtualCard->expiration;
        $expMonth = substr($expiration, 0, 2);
        $expYear = substr($expiration, 2, 2);

        $nameOnCard = isset($user) ? ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') : null;

        return [
            'card_token' => $virtualCard->token,
            'user_token' => $virtualCard->user_token,
            'card_product_token' => $virtualCard->card_product_token,
            'card_number' => $virtualCard->pan ?? null,
            'last4_digit_of_card' => $virtualCard->last_four ?? null,
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
            'expiry_date' => $expiration,
            'cvc' => $virtualCard->cvv_number ?? null,
            'barcode' => $virtualCard->barcode ?? null,
            'name_on_card' => $nameOnCard,
            'email' => $user->email ?? null,
            'country' => $user->country ?? null,
            'state' => $user->state ?? null,
            'city' => $user->city ?? null,
            'postal_code' => $user->postal_code ?? null,
            'address1' => $user->address1 ?? null,
            'address2' => $user->address2 ?? null,
            'status' => $virtualCard->state,
        ];
    }


    /*extra if needed*/
    public static function showCardPan($cardToken)
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $response = self::$client->get("cards/{$cardToken}/showpan", [
                'query' => [
                    'show_cvv_number' => 'true',
                ],
                'headers' => self::$headers,
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new Exception("Failed to retrieve card PAN. Status Code: " . $response->getStatusCode());
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            echo "HTTP Request Error: " . $e->getMessage();
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

}
