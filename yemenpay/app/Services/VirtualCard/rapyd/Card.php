<?php

namespace App\Services\VirtualCard\rapyd;

use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use Exception;

require_once ("utilities.php");

class Card
{
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

    public static function createCardWorkflow($cardOrder)
    {
        $wltContId = self::createWallet($cardOrder);
        $body = [
            "ewallet_contact" => $wltContId,
            "card_program" => "cardprog_7bb936dcdb94d4e0c7a6c2f8389f50cd" //$cardProgId
        ];
        try {
            $card = make_request('post', '/v1/issuing/cards', $body);

            $card = (object) $card;
            $preprocessedData = self::preprocessCardData($card);

            $exp_month = str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT);
            $exp_year = $card->expiration_year;
            $expiry_date = $exp_year . '-' . $exp_month . '-01';

            return [
                'status' => 'success',
                'name_on_card' => $preprocessedData['name_on_card'] ?? null,
                'card_id' => $card->card_id,
                'brand' => $card->brand ?? null,
                'expiry_date' => $expiry_date,
                'cvv' => $card->cvv ?? null,
                'card_number' => $card->card_number ?? null,
                'balance' => $card->balance ?? 0,
                'data' => $preprocessedData,
            ];
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }

    public static function createWallet($cardOrder)
    {
        $user = $cardOrder?->form_input;
        $firstName = $user->FirstName->field_value ?? "";
        $lastName = $user->LastName->field_value ?? "";
        $fullName = $user->FirstName->field_value. ' ' .$user->LastName->field_value ?? "";
        $email = $user->CustomerEmail->field_value ?? "";
        $phone = $user->PhoneNumber->field_value ?? "";
        $country = $user->Country->field_value ?? "";
        $dob = \Carbon\Carbon::parse($user->DateOfBirth->field_value)->format('m/d/Y');
        $ref_id = sprintf('%s-%s-%06d',
            $user->FirstName->field_value,
            $user->LastName->field_value,
            strRandomNum(6)
        );
        $body = [
            "first_name" => $firstName,
            "last_name" => $lastName,
            "email" => $email,
            "ewallet_reference_id" => $ref_id ?? "",
            "metadata" => [
                "merchant_defined" => true
            ],
            "phone_number" => $phone,
            "type" => "person",
            "contact" => [
                "phone_number" => $phone,
                "email" => $email,
                "first_name" => $firstName,
                "last_name" => $lastName,
                "mothers_name" => "",
                "contact_type" => "personal",
                "address" => [
                    "name" => $fullName,
                    "line_1" => $user->Line1->field_value ?? "",
                    "line_2" => $user->Line2->field_value ?? "",
                    "line_3" => "",
                    "city" => $user->City->field_value ?? "",
                    "state" => $user->State->field_value ?? "",
                    "country" => $country,
                    "zip" => $user->PostalCode->field_value ?? "",
                    "phone_number" => $user->PhoneNumber->field_value ?? "",
                    "metadata" => [],
                    "canton" => "",
                    "district" => $user->City->field_value ?? ""
                ],
                "identification_type" => "PA",
                "identification_number" => $user->PassportId->field_value ?? "",
                "date_of_birth" => $dob,
                "country" => $country,
                "nationality" => $country,
                "metadata" => [
                    "merchant_defined" => true
                ]
            ]
        ];

        try {
            $object = make_request('post', '/v1/ewallets', $body);
            $data = $object["data"];
            $contId = $data['contacts']['data'][0]['id'];
            return $contId;

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }


    public static function preprocessCardData($virtualCard): array
    {
        $user = $virtualCard?->ewallet_contact;
        $user = (object) $user;
        $nameOnCard = isset($user) ? ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') : null;

        return [
            'id' => $virtualCard->id,
            'card_id' => $virtualCard->card_id,
            'ewallet' => $virtualCard->ewallet,
            'ewallet_contact_id`' => $user->id,
            'address_id' => $user->address?->id ?? null,
            'card_program' => $virtualCard->card_program,
            'card_number' => $virtualCard->card_number ?? null,
            'name_on_card' => $nameOnCard,
            'cvc' => $virtualCard->cvv ?? null,
            'exp_month' => $virtualCard->expiration_month,
            'exp_year' => $virtualCard->expiration_year,
            'bin' => $virtualCard->bin ?? null,
            'phone_number`' => $user->phone_number,
            'email' => $user->email ?? null,
            'country' => $user->country_iso_alpha_2 ?? null,
            'nationality' => $user->nationality ?? null,
            'identification_type' => $user->identification_type ?? null,
            'identification_number' => $user->identification_number ?? null,
            'date_of_birth' => $user->date_of_birth ?? null,
            'status' => $virtualCard->status,
        ];
    }

    public static function updateCardStatus($cardOrder,$status)
    {
        //$card = "card_f8dd3aa099444e1c80cc5a06de38b165";
        $card = $cardOrder?->card_id;
        $body = [
            "card" => $card,
            "status" => $status
        ];
        try {
            $object = make_request('post', '/v1/issuing/cards/status', $body);

            $status = $object["status"]["status"] ?? null;
            $cardStatus = $object["data"]["status"] ?? null;
            return [
                'status' => $status === "SUCCESS" ? 'success' : 'error',
                'data' => $cardStatus === "ACT" ? 'Activated successfully' : 'Blocked successfully',
            ];

        } catch(Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }

    public static function blockCard($cardOrder): array
    {
        return self::updateCardStatus($cardOrder, 'block');
    }

    public static function unblockCard($cardOrder): array
    {
        return self::updateCardStatus($cardOrder, 'unblock');
    }

    public static function getTrx($card_id)
    {
        $min_amount= 5;
        $url = "/v1/issuing/cards/$card_id/transactions?min_amount=$min_amount";

        try {
            $cardOrder = VirtualCardOrder::where('card_id', $card_id)->firstOrFail();

            $responseData = make_request('get', $url);

            $transactions = $responseData['data'];
            foreach ($transactions as $transaction) {
                $transaction = (object)$transaction;
                $formattedData = [
                    'card_id' => $card_id,
                    'amount' => abs($transaction->amount),
                    'currency' => strtoupper($transaction->currency),
                    'balance' => $transaction->gpa['ledger_balance'],
                    'type' => $transaction->issuing_txn_type,
                    'last4' => $transaction->last4,
                    'date' => $transaction->created_at,
                    'wallet_transaction_id' => $transaction->wallet_transaction_id,
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

        } catch(Exception $e) {
            return 0;
        }

    }

    public static function fundAddCard($cardOrder): array
    {
        $e_wallet_id = $cardOrder->card_info->ewallet->field_value;
        $amount = $cardOrder->fund_amount;
        $body = [
            'ewallet' => $e_wallet_id,
            'amount' => $amount,
            'currency' => 'USD'
        ];
        try {
            $response = make_request('post', '/v1/account/deposit', $body);
            dd($response);

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



    /*extra if needed*/
    public static function issueCard()
    {
        $body = [
            "ewallet_contact" => "cont_f9df2ca263ae8bae59a40bb9030ad43c",
            "card_program" => "cardprog_6b61095fc35633cf2c9c20754c12187d"
        ];
        try {
            $object = make_request('post', '/v1/issuing/cards', $body);
            dd($object);
        } catch(Exception $e) {
            return "Error => " . $e->getMessage();
        }
    }
    public static function createAddress()
    {
        $body = [
            "name" => "John Doe",
            "line_1" => "123 State Street",
            "line_2" => "Apt. 34",
            "line_3" => "",
            "city" => "Anytown",
            "district" => "",
            "canton" => "",
            "state" => "NY",
            "country" => "US",
            "zip" => "12345",
            "phone_number" => "12125559999",
            "metadata" => array(
                "merchant_defined" => true
            )
        ];
        try {
            $object = make_request('post', '/v1/addresses', $body);
            dd($object);
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }
    public static function createCustomer()
    {
        $body = [
            "business_vat_id" => "123456789",
            "email" => "johndoe@rapyd.net",
            "ewallet" => "ewallet_d2d8305e7d4bb2d65f75788131f0358f",
            "invoice_prefix" => "JD-",
            "name" => "John Doe",
            "phone_number" => "+14155559993",
            "metadata" => array(
                "merchant_defined" => true
            )
        ];
        try {
            $object = make_request('post', '/v1/customers', $body);
            dd($object);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }
    public static function getCountry()
    {
        try {
            $object = make_request('get', '/v1/data/countries');
            dd($object);
        } catch(\Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }



}
