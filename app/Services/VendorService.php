<?php

namespace App\Services;

use Illuminate\Http\Request; 
use App\Models\Seller;
use App\Models\ReferralVendors;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use App\Utils\Helpers;
use Illuminate\Support\Facades\DB;

class VendorService
{
    use FileManagerTrait;
    /**
     * @param string $email
     * @param string $password
     * @param string|bool|null $rememberToken
     * @return bool
     */
    public function isLoginSuccessful(string $email, string $password, string|null|bool $rememberToken): bool
    {
        if (auth('seller')->attempt(['email' => $email, 'password' => $password], $rememberToken)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function generate_code(int $vendorId):string{
        
            // $vandor["referral_code"];
            $seller = Seller::findOrFail($vendorId);
            if(empty($seller["referral_code"])){
                $referral_code =Helpers::generate_referer_code("seller");
                $seller->referral_code =$referral_code ;
                $seller->save();
                return $referral_code ;
            }
            return $seller->referral_code ;
    }

    public function create_referral_vendor(int $vendorId,$referral_code): void{
        
        // $vandor["referral_code"];
        if(!empty($referral_code)){
            $seller = Seller::where("referral_code",$referral_code)->first();
            if($seller!=null){
                $ReferralVendors=new ReferralVendors();
                $ReferralVendors->vendor =$vendorId ;
                $ReferralVendors->office =$seller->id ;
                $ReferralVendors->save();
            }
        }
    }
    public function Delete_referral_vendor(int $vendorId): void{
        
        // $vandor["referral_code"];
        if(!empty($vendorId)){
            $seller = ReferralVendors::where("vendor",$vendorId)->first();
            $seller->delete();
        }
    }

    public function get_referral_vendor(int $officeid, ?Request $request = null)
    {
        $query = Seller::findOrFail($officeid)
        ->referredVendors() // استخدام العلاقة مباشرةً بدلاً من `whereHas`
        ->leftJoin("seller_wallets", "seller_wallets.seller_id", "=", "sellers.id")
        ->leftJoin("shops", "shops.seller_id", "=", "sellers.id")
        ->select("sellers.*", "seller_wallets.referral_commission", "shops.name as shop_name");

        // إضافة البحث إذا كان موجودًا
        if ($request && $request->has('searchValue') && $request->searchValue != "all") {
            $search = $request->searchValue;
            $query->where(\DB::raw("CONCAT(sellers.f_name, ' ', sellers.l_name)"), "like", "%$search%")
                ->orWhere("shops.name", "like", "%$search%");
        }

        return $query->paginate(getWebConfig(name: 'pagination_limit'));
    }
    public function getInitialWalletData(int $vendorId): array
    {
        return [
            'seller_id' => $vendorId,
            'withdrawn' => 0,
            'commission_given' => 0,
            'total_earning' => 0,
            'pending_withdraw' => 0,
            'delivery_charge_earned' => 0,
            'collected_cash' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function logout(): void
    {
        auth()->guard('seller')->logout();
        session()->invalidate();
    }

    /**
     * @param object $request
     * @return array
     */
    public function getFreeDeliveryOverAmountData(object $request):array
    {
        return [
            'free_delivery_status' => $request['free_delivery_status'] == 'on' ? 1 : 0,
            'free_delivery_over_amount' => currencyConverter($request['free_delivery_over_amount'], 'usd'),
        ];
    }

    /**
     * @return array[minimum_order_amount: float|int]
     */
    public function getMinimumOrderAmount(object $request) :array
    {
        return [
            'minimum_order_amount' => currencyConverter($request['minimum_order_amount'], 'usd')
        ];
    }

    /**
     * @param object $request
     * @param object $vendor
     * @return array
     */
    public function getVendorDataForUpdate(object $request, object $vendor):array
    {
        $image = $request['image'] ? $this->update(dir: 'seller/', oldImage: $vendor['image'], format: 'webp', image: $request->file('image')) : $vendor['image'];
        return [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'phone' => $request['phone'],
            'image' => $image,
        ];
    }

    /**
     * @return array[password: string]
     */
    public function getVendorPasswordData(object $request):array
    {
        return [
            'password' => bcrypt($request['password']),
        ];
    }

    /**
     * @param object $request
     * @return array
     */
    public function getVendorBankInfoData(object $request):array
    {
        return [
            'bank_name' => $request['bank_name'],
            'branch' => $request['branch'],
            'holder_name' => $request['holder_name'],
            'account_no' => $request['account_no'],
        ];
    }
    public function getAddData(object $request):array
    {
        return [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
            'image' => $this->upload(dir: 'seller/', format: 'webp', image: $request->file('image')),
            'password' => bcrypt($request['password']),
            'referral_code' => Helpers::generate_referer_code("seller"),
            'status' => $request['status'] == 'approved' ? 'approved' : 'pending',
        ];
    }
}
