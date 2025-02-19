<?php

namespace App\Services;

use App\Models\Seller;
use App\Traits\FileManagerTrait;
use App\Utils\Helpers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ShopService
{
    use FileManagerTrait;
    /**
     * @param object $vendor
     * @return array
     */
    public function getShopDataForAdd(object $vendor):array
    {
        if(empty($vandor["referral_code"])){
            // $vandor["referral_code"];
            $referral_code =Helpers::generate_referer_code("seller");

            $seller = Seller::findOrFail($vendor['id']);
            $seller->referral_code =$referral_code ;
            $seller->save();
            $vandor["referral_code"]=$referral_code ;
        }
        return [
            'seller_id' =>$vendor['id'],
            'name' => $vendor['f_name'],
            'address' => '',
            'contact' => $vendor['phone'],
            'referral_code' => $vandor["referral_code"],
            'image' => 'def.png',
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * @return array[name: mixed, address: mixed, contact: mixed, image: bool|mixed, banner: bool|mixed, bottomBanner: bool|mixed, offerBanner: bool|mixed]
     */
    public function getShopDataForUpdate(object $request , object $shop):array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $image = $request['image'] ? $this->update(dir:'shop/', oldImage: $shop['image'], format: 'webp',image:  $request->file('image')) : $shop['image'];
        $banner = $request['banner'] ? $this->update(dir: 'shop/banner/',oldImage:  $shop['banner'], format: 'webp',image:  $request->file('banner')): $shop['banner'];
        $bottomBanner = $request['bottom_banner'] ? $this->update(dir: 'shop/banner/', oldImage: $shop['bottom_banner'], format: 'webp', image: $request->file('bottom_banner')) : $shop['bottom_banner'];
        $offerBanner = $request['offer_banner'] ? $this->update(dir: 'shop/banner/', oldImage: $shop['offer_banner'], format: 'webp',image:  $request->file('offer_banner')) : $shop['offer_banner'];
        return [
            'name'=>$request['name'],
            'address'=>$request['address'],
            'contact'=>$request['contact'],
            'image'=> $image,
            'image_storage_type' => $request->has('image') ? $storage : $shop['image_storage_type'],
            'banner'=> $banner,
            'banner_storage_type'=> $request->has('banner') ? $storage : $shop['banner_storage_type'],
            'bottom_banner'=> $bottomBanner,
            'bottom_banner_storage_type'=> $request->has('bottom_banner') ? $storage : $shop['bottom_banner_storage_type'],
            'offer_banner'=> $offerBanner,
            'offer_banner_storage_type'=> $request->has('offer_banner') ? $storage : $shop['offer_banner_storage_type'],
        ];
    }

    /**
     * @return array[vacation_status: int, vacation_start_date: mixed, vacation_end_date: mixed, vacation_note: mixed]
     */
    public function getVacationData(object $request):array
    {
        return [
            'vacation_status' => $request['vacation_status'] == 'on' ? 1 : 0,
            'vacation_start_date' => $request['vacation_start_date'],
            'vacation_end_date' => $request['vacation_end_date'],
            'vacation_note' => $request['vacation_note'],
        ];
    }
    public function getAddShopDataForRegistration(object $request,int $vendorId):array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        return [
            'seller_id' => $vendorId,
            'name' => $request['shop_name'],
            'slug' => Str::slug($request['shop_name'], '-') . '-' . Str::random(6),
            'address'=>$request['shop_address'],
            'contact' => $request['phone'],
            'image' =>$request->has('logo')? $this->upload(dir: 'shop/', format: 'webp', image: $request->file('logo')):"",
            'image_storage_type' => $request->has('logo') ? $storage : null,
            'banner' =>$request->has('banner')? $this->upload(dir: 'shop/banner/', format: 'webp', image: $request->file('banner')):"",
            'banner_storage_type' =>$request->has('banner') ? $storage : null,
            'bottom_banner' =>$request->has('bottom_banner') ? $this->upload(dir: 'shop/banner/', format: 'webp', image: $request->file('bottom_banner')):"",
            'bottom_banner_storage_type' =>$request->has('banner') ? $storage : null,
        ];
    }

}
