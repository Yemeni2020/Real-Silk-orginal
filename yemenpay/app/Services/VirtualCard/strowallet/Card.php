<?php

namespace App\Services\VirtualCard\strowallet;

use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;

require_once('vendor/autoload.php');

class Card
{
    private static Client $client;
    private static string $baseUrl = 'https://strowallet.com/api/';
    private static string $publicKey;
    private static array $headers;

    public static function init()
    {
        try {
            $cardMethod = VirtualCardMethod::where('code', 'strowallet')->first();

            self::$client = new Client();
            self::$publicKey = $cardMethod->parameters?->public_key;
            self::$headers = [
                'Accept' => 'application/json',
                'content-type' => 'application/json',
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

    public static function createCardWorkflow($cardOrder): array
    {
        try {
            $createdUser = self::createCustomer($cardOrder);
            if (!$createdUser['success']) {
                throw new Exception($createdUser['data']);
            }
            $customerData = $createdUser['data'];

            $card = self::createCard($customerData);
            if ($card['status'] === 'error') {
                throw new Exception('Card creation failed.');
            }

            $card = (object)$card['data'];
            $userData = (object)$createdUser['data'];
            $getCardDetails = self::fetchCardDetail($card->card_id);
            if ($getCardDetails['status'] === 'error') {
                throw new Exception('Card Fetch failed.');
            }

            $card = (object)$getCardDetails['data']['card_detail'];
            $preprocessedData = self::preprocessCardData($card, $userData);

            return [
                'status' => 'success',
                'card_id' => $card->card_id,
                'name_on_card' => $preprocessedData['name_on_card'] ?? null,
                'card_number' => $card->card_number ?? null,
                'brand' => $card->card_brand ?? null,
                'cvv' => $card->cvv ?? null,
                'expiry_date' => $preprocessedData['expiry_date'] ?? null,
                'balance' => $card->balance ?? 0,
                'data' => $preprocessedData,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage(),
            ];
        }
    }

    public static function createCustomer($cardOrder)
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }

            $user = $cardOrder->form_input;
            $dob = Carbon::parse($user->DateOfBirth->field_value)->format('m/d/Y');
            $data = [
                'public_key' => self::$publicKey,
                'houseNumber' => $user->HouseNumber->field_value,
                'firstName' => $user->FirstName->field_value,
                'lastName' => $user->LastName->field_value,
                'idNumber' => $user->IdNumber->field_value,
                'customerEmail' => $user->CustomerEmail->field_value,
                'phoneNumber' => $user->PhoneNumber->field_value,
                'state' => $user->State->field_value,
                'zipCode' => $user->ZipCode->field_value,
                'city' => $user->City->field_value,
                'country' => $user->Country->field_value,
                'idType' => $user->IdType->field_value,
                'line1' => $user->Line1->field_value,
                'idImage' => $user->IdImage->field_value,
                'userPhoto' => $user->UserPhoto->field_value,
                'dateOfBirth' => $dob,
            ];

            $url = "bitvcard/create-user/?" . http_build_query($data);
            $response = self::$client->request('POST', $url, [
                'headers' => self::$headers,
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => $responseBody['success'],
                'data' => $responseBody['success'] ? $responseBody['response'] : $responseBody['message'],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => $e->getMessage()
            ];
        }

    }

    public static function createCard($customerData, $amount = 0): array
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $userEmail = $customerData['customerEmail'];
            $userFullName = $customerData['firstName'] . ' ' . $customerData['lastName'];
            $cardData = [
                'public_key' => self::$publicKey,
                'name_on_card' => $userFullName,
                'card_type' => 'visa',
                'amount' => $amount,
                'customerEmail' => $userEmail,
                // 'mode' => 'sandbox',
            ];
            $response = self::$client->request('POST', 'bitvcard/create-card/', [
                'body' => json_encode($cardData),
                'headers' => self::$headers,
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            return [
                'status' => $responseBody['success'] ? 'success' : 'error',
                'data' => $responseBody['success'] ? $responseBody['response'] : $responseBody['message'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage(),
            ];
        }
    }

    public static function fetchCardDetail(string $cardId): array
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }

            $requestData = [
                'public_key' => self::$publicKey,
                'card_id' => $cardId,
                // 'mode' => 'sandbox',
            ];

            $response = self::$client->post('bitvcard/fetch-card-detail/', [
                'json' => $requestData,
                'headers' => self::$headers,
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            return [
                'status' => $responseBody['success'] ? 'success' : 'error',
                'data' => $responseBody['success'] ? $responseBody['response'] : $responseBody['message'],
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
            $cardId = $cardOrder->card_Id;
            $allowedStates = ['freeze', 'unfreeze'];
            if (!in_array($status, $allowedStates)) {
                throw new Exception("Invalid status: $status. Allowed values are: " . implode(", ", $allowedStates));
            }

            $requestData = [
                'action' => $status,
                'card_id' => $cardId,
                'public_key' => self::$publicKey,
            ];
            $queryString = http_build_query($requestData);

            $response = self::$client->post('bitvcard/action/status/?' . $queryString, [
                'headers' => self::$headers,
            ]);

            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);

                $message = $responseData['message'] ?? $responseData['error'] ?? 'Card status updated';
                if (is_array($message)) {
                    $message = implode(', ', $message);
                }
                return [
                    'status' => isset($responseData['error']) ? 'error' : 'success',
                    'data' => $message,
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
        return self::updateCardStatus($cardOrder, 'freeze');
    }

    public static function unblockCard($cardOrder): array
    {
        return self::updateCardStatus($cardOrder, 'unfreeze');
    }

    public static function fundAddCard($cardOrder): array
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $amount = $cardOrder->fund_amount;
            $requestData = [
                'public_key' => self::$publicKey,
                'card_id' => $cardOrder->card_Id,
                'amount' => $amount,
                // 'mode' => !env('IS_DEMO') ? 'sandbox' : '',
            ];

            $response = self::$client->post('bitvcard/fund-card/', [
                'json' => $requestData,
                'headers' => self::$headers,
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            $cardOrder->balance += $amount;
            $balance = $cardOrder->balance;
            return [
                'status' => 'success',
                'balance' => $balance,
                'data' => 'Funds successfully added to card balance',
            ];
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

            $requestData = [
                'card_id' => $cardOrder->card_Id,
                'public_key' => self::$publicKey,
                // 'mode' => !env('IS_DEMO') ? 'sandbox' : '',
            ];

            $response = self::$client->post('bitvcard/card-transactions/', [
                'json' => $requestData,
                'headers' => self::$headers,
            ]);

            $responseData = json_decode($response->getBody(), true);
            $transactions = $responseData['response']['card_transactions'];

            foreach ($transactions as $transaction) {
                $transaction = (object)$transaction;
                $formattedData = [
                    'card_id' => $transaction->cardId,
                    'amount' => abs($transaction->amount),
                    'centAmount' => abs($transaction->centAmount),
                    'type' => $transaction->type,
                    'currency' => strtoupper($transaction->currency),
                    'date' => $transaction->createdAt,
                    'narration' => $transaction->narrative,
                ];
                VirtualCardTransaction::updateOrCreate(
                    ['user_id' => auth()->id(), 'card_id' => $card_id, 'trx_id' => $transaction->id],
                    [
                        'card_order_id' => $cardOrder->id,
                        'data' => $formattedData,
                        'amount' => abs($transaction->amount),
                        'currency' => strtoupper($transaction->currency),
                    ]
                );
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retrieve transactions: ' . $e->getMessage());
        }
    }


    public static function preprocessCardData($card, $user): array
    {
        list($expMonth, $expYear) = explode('/', $card->expiry);
        $expYear = '20' . $expYear;
        $expiry_date = "{$expYear}-{$expMonth}-01";

        return [
            'card_id' => $card->card_id,
            'card_user_id' => $card->card_user_id,
            'card_name' => $card->card_name ?? null,
            'customer_id' => $card->customer_id,
            'customer_email' => $card->customer_email,
            'card_type' => $card->card_type ?? null,
            'card_brand' => $card->card_brand ?? null,
            'name_on_card' => $card->card_holder_name,
            'card_number' => $card->card_number ?? null,
            'cvc' => $card->cvv ?? null,
            'last4_digit_of_card' => $card->last4 ?? null,
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
            'expiry_date' => $expiry_date,
            'reference' => $card->reference ?? null,
            'card_issue_date' => $card->card_created_date,
            'first_mame' => $user->firstName,
            'last_name' => $user->lastName,
            'date_of_birth' => $user->dateOfBirth,
            'country' => $user->country ?? null,
            'state' => $user->state ?? null,
            'city' => $user->city ?? null,
            'postal_code' => $user->zipCode ?? null,
            'address1' => $user->line1 ?? null,
            'status' => strtoupper($card->card_status),
        ];
    }


    /*extra if needed*/
    public static function getCustomer()
    {
        try {
            if (self::init() === false) {
                throw new Exception('Initialization failed: Card method not found.');
            }
            $customerId = "32703c5d-b1e6-4f8b-973b-77959bc5ba2a";
            $queryParams = [
                'public_key' => self::$publicKey,
                'customerId' => $customerId
            ];

            $url = "bitvcard/getcardholder/?" . http_build_query($queryParams);
            $response = self::$client->request('GET', $url, [
                'headers' => self::$headers,
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            return [
                'success' => $responseBody['success'],
                'data' => $responseBody['response'],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => $e->getMessage()
            ];
        }
    }


}
