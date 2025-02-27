<?php

namespace App\Repositories;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Translation;
use App\Services\ProductService;

class TranslationRepository implements TranslationRepositoryInterface
{
    public function __construct(
       private readonly Translation $translation,
       private readonly ProductService $ProductService,
    )
    {
    }

    public function add(object $request, string $model, int|string $id): bool
    {
        foreach ($request->lang as $index => $key) {
            foreach (['name','description','title','value'] as $type){
                if (isset($request[$type][$index]) && $key != 'en') {
                    
                    $value=$request[$type][$index];

                    $this->translation->insert(
                        [
                            'translationable_type' => $model,
                            'translationable_id' => $id,
                            'locale' => $key,
                            'key' => $type,
                            'value' => $value
                        ]
                    );
                }elseif(isset($request[$type]) && $key != 'en' ){
                    $auto_translate = getWebConfig("auto_translate");
                    $curnnet_lang = session()->get("local");

                    $value=$request[$type][array_search($curnnet_lang, $request['lang'])];
                    if ($auto_translate == 1) {
                        $name=$request[$type][array_search($curnnet_lang, $request['lang'])];
                        // التحقق من وجود اللغات داخل الطلب
                        $value = $this->ProductService->translate($request["translate_ai"], $name, $key);

                    }
                    $this->translation->insert(
                        [
                            'translationable_type' => $model,
                            'translationable_id' => $id,
                            'locale' => $key,
                            'key' => $type,
                            'value' => $value
                        ]
                    );
                }
            }
        }
        return true;
    }
    public function CreateOrUpdate(object $request, string $model, int|string $id): bool
    {
        foreach ($request->lang as $index => $key) {
            foreach (['name','description','title','value'] as $type){
                if (isset($request[$type][$index]) && $key != 'en') {
                    $value=$request[$type][$index];
                    if(empty(trim($value))){
                        $auto_translate = getWebConfig("auto_translate");
                        $curnnet_lang = session()->get("local");
    
                        $value=$request[$type][array_search($curnnet_lang, $request['lang'])];
                        if ($auto_translate == 1) {
                            $name=$request[$type][array_search($curnnet_lang, $request['lang'])];
                            // التحقق من وجود اللغات داخل الطلب
                            $value = $this->ProductService->translate($request["translate_ai"], $name, $key);
    
                        }
                    }
                    if(isset($value["error"])){
                        $value=$request[$type][$index];
                    }
                    $this->translation->updateOrCreate(
                        [
                            'translationable_type' => $model,
                            'translationable_id' => $id,
                            'locale' => $key,
                            'key' => $type,
                        ],
                        [
                            'value' => $value, // القيمة الجديدة
                        ]
                    );
                }elseif(isset($request[$type]) && $key != 'en' ){
                    $auto_translate = getWebConfig("auto_translate");
                    $curnnet_lang = session()->get("local");

                    $value=$request[$type][array_search($curnnet_lang, $request['lang'])];
                    if ($auto_translate == 1) {
                        $name=$request[$type][array_search($curnnet_lang, $request['lang'])];
                        // التحقق من وجود اللغات داخل الطلب
                        $value = $this->ProductService->translate($request["translate_ai"], $name, $key);

                    }
                    if(!isset($value["error"])){
                        $this->translation->updateOrCreate(
                            [
                                'translationable_type' => $model,
                                'translationable_id' => $id,
                                'locale' => $key,
                                'key' => $type,
                            ],
                            [
                                'value' => $value, // القيمة الجديدة
                            ]
                        );
                    }
                }
            }
        }
        return true;
    }

    public function update(object $request, string $model, int|string $id): bool
    {
        foreach ($request->lang as $index => $key) {
            foreach (['name','description','title'] as $type){
                if (isset($request[$type][$index]) && $key != 'en') {
                    $value=$request[$type][$index];
                    if(empty(trim($value))){
                        $auto_translate = getWebConfig("auto_translate");
                        $curnnet_lang = session()->get("local");
    
                        $value=$request[$type][array_search($curnnet_lang, $request['lang'])];
                        if ($auto_translate == 1) {
                            $name=$request[$type][array_search($curnnet_lang, $request['lang'])];
                            // التحقق من وجود اللغات داخل الطلب
                            $value = $this->ProductService->translate($request["translate_ai"], $name, $key);
    
                        }
                    }
                    if(isset($value["error"])){
                        $value=$request[$type][$index];
                    }

                    $this->translation->updateOrInsert(
                        [
                            'translationable_type' => $model,
                            'translationable_id' => $id,
                            'locale' => $key,
                            'key' => $type
                        ],
                        [
                            'value' => $value
                        ]
                    );
                    
                }elseif(isset($request[$type]) && $key != 'en' ){
                    $auto_translate = getWebConfig("auto_translate");
                    $curnnet_lang = session()->get("local");

                    $value=$request[$type][array_search($curnnet_lang, $request['lang'])];
                    if ($auto_translate == 1) {
                        $name=$request[$type][array_search($curnnet_lang, $request['lang'])];
                        // التحقق من وجود اللغات داخل الطلب
                        $value = $this->ProductService->translate($request["translate_ai"], $name, $key);

                    }

                    if(isset($value["error"])==false){

                        $this->translation->updateOrInsert(
                            [
                                'translationable_type' => $model,
                                'translationable_id' => $id,
                                'locale' => $key,
                                'key' => $type
                            ],
                            [
                                'value' => $value
                            ]
                        );
                    }
                }
            }
        }
        return true;
    }
    public function updateData(string $model, string $id, string $lang, string $key, string $value):bool
    {
        $this->translation->updateOrInsert(
            [
                'translationable_type' => $model,
                'translationable_id' => $id,
                'locale' => $lang,
                'key' => $key
            ],
            [
                'value' => $value
            ]
        );
        return true;
    }
    public function delete(string $model, int|string $id): bool
    {
        $this->translation->where('translationable_type',$model)->where('translationable_id',$id)->delete();
        return true;
    }
}
