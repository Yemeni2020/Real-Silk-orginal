<?php

use App\Models\BusinessSetting;
use App\Models\LoginSetup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getWebConfig')) {
    function getWebConfig($name): string|object|array|null
    {
        $config = null;
        if (in_array($name, getWebConfigCacheKeys()) && Cache::has($name)) {
            $config = Cache::get($name);
        } else {
            $data = BusinessSetting::where(['type' => $name])->first();
            $config = isset($data) ? setWebConfigCache($name, $data) : $config;
        }
        return $config;
    }
}

if (!function_exists('clearWebConfigCacheKeys')) {
    function clearWebConfigCacheKeys(): bool
    {
        $cacheKeys = getWebConfigCacheKeys();
        $allConfig = BusinessSetting::whereIn('type', $cacheKeys)->get();

        foreach ($cacheKeys as $cacheKey) {
            Cache::forget($cacheKey);
        }
        foreach ($allConfig as $item) {
            setWebConfigCache($item['type'], $item);
        }
        return true;
    }

    function setWebConfigCache($name, $data)
    {
        $cacheKeys = getWebConfigCacheKeys();
        $arrayOfCompaniesValue = ['company_web_logo', 'company_mobile_logo', 'company_footer_logo', 'company_fav_icon', 'loader_gif'];
        $arrayOfBanner = ['shop_banner', 'offer_banner', 'bottom_banner'];
        $mergeArray = array_merge($arrayOfCompaniesValue, $arrayOfBanner);

        $config = json_decode($data['value'], true);
        if (in_array($name, $mergeArray)) {
            $folderName = in_array($name, $arrayOfCompaniesValue) ? 'company' : 'shop';
            $value = isset($config['image_name']) ? $config : ['image_name' => $data['value'], 'storage' => 'public'];
            $config = storageLink($folderName, $value['image_name'], $value['storage']);
        }

        if (is_null($config)) {
            $config = $data['value'];
        }

        if (in_array($name, $cacheKeys)) {
            Cache::put($name, $config, now()->addMinutes(30));
        }
        return $config;
    }
}

if (!function_exists('getWebConfigCacheKeys')) {
    function getWebConfigCacheKeys(): string|object|array|null
    {
        return [
            'currency_model', 'currency_symbol_position', 'system_default_currency', 'language',
            'company_name', 'decimal_point_settings', 'product_brand', 'company_email',
            'business_mode', 'storage_connection_type', 'company_web_logo', 'digital_product', 'storage_connection_type', 'recaptcha',
            'language', 'pagination_limit', 'company_phone', 'stock_limit',
        ];
    }
}

if (!function_exists('storageDataProcessing')) {
    function storageDataProcessing($name, $value)
    {
        $arrayOfCompaniesValue = ['company_web_logo', 'company_mobile_logo', 'company_footer_logo', 'company_fav_icon', 'loader_gif'];
        if (in_array($name, $arrayOfCompaniesValue)) {
            if (!is_array($value)) {
                return storageLink('company', $value, 'public');
            } else {
                return storageLink('company', $value['image_name'], $value['storage']);
            }
        } else {
            return $value;
        }
    }
}

if (!function_exists('imagePathProcessing')) {
    function imagePathProcessing($imageData, $path): array|string|null
    {
        if ($imageData) {
            $imageData = is_string($imageData) ? $imageData : (array)$imageData;
            $imageArray = [
                'image_name' => is_array($imageData) ? $imageData['image_name'] : $imageData,
                'storage' => $imageData['storage'] ?? 'public',
            ];
            return storageLink($path, $imageArray['image_name'], $imageArray['storage']);
        }
        return null;
    }
}

if (!function_exists('storageLink')) {
    function storageLink($path, $data, $type): string|array
    {
        if ($type == 's3' && config('filesystems.disks.default') == 's3') {
            $fullPath = ltrim($path . '/' . $data, '/');
            if (fileCheck(disk: 's3', path: $fullPath) && !empty($data)) {
                return [
                    'key' => $data,
                    'path' => Storage::disk('s3')->url($fullPath),
                    'status' => 200,
                ];
            }
        } else {
            if (fileCheck(disk: 'public', path: $path . '/' . $data) && !empty($data)) {

                $resultPath = asset('storage/app/public/' . $path . '/' . $data);
                if (DOMAIN_POINTED_DIRECTORY == 'public') {
                    $resultPath = asset('storage/' . $path . '/' . $data);
                }

                return [
                    'key' => $data,
                    'path' => $resultPath,
                    'status' => 200,
                ];
            }
        }
        return [
            'key' => $data,
            'path' => null,
            'status' => 404,
        ];
    }
}


if (!function_exists('storageLinkForGallery')) {
    function storageLinkForGallery($path, $type): string|null
    {
        if ($type == 's3' && config('filesystems.disks.default') == 's3') {
            $fullPath = ltrim($path, '/');
            if (fileCheck(disk: 's3', path: $fullPath)) {
                return Storage::disk('s3')->url($fullPath);
            }
        } else {
            if (fileCheck(disk: 'public', path: $path)) {
                if (DOMAIN_POINTED_DIRECTORY == 'public') {
                    $result = str_replace('storage/app/public', 'storage', 'storage/app/public/' . $path);
                } else {
                    $result = 'storage/app/public/' . $path;
                }
                return asset($result);
            }
        }
        return null;
    }
}

if (!function_exists('fileCheck')) {
    function fileCheck($disk, $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }
}


if (!function_exists('getLoginConfig')) {
    function getLoginConfig($key): string|object|array|null
    {
        $data = LoginSetup::where(['key' => $key])->first();
        return isset($data) ? json_decode($data['value'], true) : $data;
    }
}
