<?php

namespace App\Traits;

use App\Models\Transaction;
use App\Models\UserWallet;
use App\Models\VirtualCardOrder;
use App\Services\VirtualCard\marqeta\Card as MarqetaCard;
use App\Services\VirtualCard\stripe\Card as StripeCard;
use App\Services\VirtualCard\strowallet\Card as StrowalletCard;
use Illuminate\Http\Request;

trait VirtualCardTrait
{
    use Notify, ManageWallet;

    public function buildValidationRules($virtualCardMethod): array
    {
        $rules = [];
        if ($virtualCardMethod->form_field) {
            foreach ($virtualCardMethod->form_field as $key => $cus) {
                $rules[$key] = [$cus->validation];
                if ($cus->type == 'file') {
                    $rules[$key] = array_merge($rules[$key], ['image', 'mimes:jpeg,jpg,png', 'max:2048']);
                } elseif ($cus->type == 'text') {
                    $rules[$key][] = 'max:191';
                } elseif ($cus->type == 'textarea') {
                    $rules[$key][] = 'max:300';
                }
            }
        }
        return $rules;
    }

    public function processFormFields(Request $request, $virtualCardMethod)
    {
        $reqFieldSpecification = [];
        $collectionSpecification = collect($request->all());
        if ($virtualCardMethod->form_field) {
            foreach ($collectionSpecification as $key => $value) {
                if (isset($virtualCardMethod->form_field->$key)) {
                    $field = $virtualCardMethod->form_field->$key;
                    if ($field->type == 'file' && $request->hasFile($key)) {
                        try {
                            $image = $request->file($key);
                            $location = config('filelocation.virtualCardOrder.path');
                            $filename = $this->fileUpload($image, $location, null, null, 'webp', 80);
                            $reqFieldSpecification[$key] = [
                                'field_name' => $key,
                                'field_value' => $filename,
                                'field_level' => $field->field_level,
                                'type' => $field->type,
                                'validation' => $field->validation,
                            ];
                        } catch (\Exception $e) {
                            if ($request->wantsJson() || $request->is('api/*')) {
                                return response()->json($this->withError('Image could not be uploaded'));
                            } else {
                                return back()->with('error', 'Image could not be uploaded.')->withInput();
                            }

                        }
                    } else {
                        $reqFieldSpecification[$key] = [
                            'field_name' => $key,
                            'field_value' => $value,
                            'field_level' => $field->field_level,
                            'type' => $field->type,
                            'validation' => $field->validation,
                        ];
                    }
                }
            }
        }
        return $reqFieldSpecification;
    }


    public function  checkUserBalance()
    {
        $basic = basicControl();
        $virtualCardCharge = $basic->v_card_charge;
        $wallet = UserWallet::where('user_id', auth()->id())
            ->where('default', 1)
            ->first();
        if (!$wallet) {
            return false;
        }
        $rate = $this->getCurrencyRate($wallet->currency_code, $basic->base_currency);
        $cardChargeByWallet = $virtualCardCharge * $rate;

        return $wallet->balance > $cardChargeByWallet;
    }

    public function chargePay($cardOrder)
    {
        $basic = basicControl();
        $baseCurrency = $basic->base_currency;
        $virtualCardCharge = $basic->v_card_charge;

        $wallet = UserWallet::where('user_id', auth()->id())
            ->where('default', 1)
            ->first();
        if (!$wallet) {
            return response()->json($this->withError('Wallet Not Found'));
        }
        $rate = $this->getCurrencyRate($wallet->currency_code, $basic->base_currency);
        $cardChargeByWallet = $virtualCardCharge * $rate;

        $wallet->decrement('balance', $cardChargeByWallet);

        $transaction = new Transaction();
        $transaction->user_id = auth()->id();
        $transaction->wallet_id = $wallet->id;
        $transaction->amount = $cardChargeByWallet;
        $transaction->base_amount = $virtualCardCharge;
        $transaction->charge = 0;
        $transaction->currency = $wallet->currency_code;
        $transaction->balance = $wallet->balance;
        $transaction->trx_type = '-';
        $transaction->trx_id = strRandom();
        $transaction->remarks = 'virtual card order';
        $cardOrder->transactional()->save($transaction);
        $cardOrder->charge = $virtualCardCharge;
        $cardOrder->charge_currency = $baseCurrency;
        $cardOrder->save();

        $user = auth()->user();
        $params = [
            'amount' => $virtualCardCharge,
            'currency' => $baseCurrency,
            'transaction' => $transaction->trx_id,
        ];
        $action = [
            "link" => "",
            "icon" => "fa fa-money-bill-alt text-white"
        ];
        $this->sendMailSms($user, 'VIRTUAL_CARD_APPLY', $params);
        $this->userPushNotification($user, 'VIRTUAL_CARD_APPLY', $params, $action);
        $this->userFirebasePushNotification($user, 'VIRTUAL_CARD_APPLY', $params);

        $params = [
            'username' => $user->username ?? null,
            'amount' => $virtualCardCharge,
            'currency' => $baseCurrency,
            'transaction' => $transaction->trx_id,
        ];
        $action = [
            "name" => $user->fullname() ?? null,
            "image" => $user->getImage() ?? null,
            "link" => route('admin.virtual.cardOrderDetail', $cardOrder->id),
            "icon" => "fa fa-money-bill-alt text-white"
        ];
        $firebaseAction = route('admin.virtual.cardOrderDetail', $cardOrder->id);
        $this->adminMail('ADMIN_VIRTUAL_CARD_APPLY', $params);
        $this->adminPushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $action);
        $this->adminFirebasePushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $firebaseAction);

        return 0;
    }


    protected function isStripeCard($card_id): bool
    {
        return str_starts_with($card_id, 'ic_');
    }

    protected function isMarqetaCard($card_id): bool
    {
        $card = VirtualCardOrder::query()->where('card_Id', $card_id)->first();
        if($card->cardMethod->code == 'marqeta'){
            return true;
        }
        return false;
    }

    protected function isStrowalletCard($card_id): bool
    {
        $card = VirtualCardOrder::query()->where('card_Id', $card_id)->first();
        if($card->cardMethod->code == 'strowallet'){
            return true;
        }
        return false;
    }

    public function updateTransaction($card_id): void
    {

        if ($this->isStripeCard($card_id)) {
            StripeCard::getTrx($card_id);
        }

        if ($this->isMarqetaCard($card_id)) {
            MarqetaCard::getTrx($card_id);
        }

        if ($this->isStrowalletCard($card_id)) {
            StrowalletCard::getTrx($card_id);
        }
    }

}
