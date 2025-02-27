<?php

namespace App\Services;

use App\Enums\ViewPaths\Admin\AddonSetup;
use App\Traits\FileManagerTrait;
use App\Traits\SettingsTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OpenAIService
{
    use SettingsTrait;
    use FileManagerTrait;

    
    public function translateText($text, $targetLanguage, $apiKey)
    {
        
        $apiUrl = "https://api.openai.com/v1/chat/completions";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json'
            ])->post($apiUrl, [
                "model" => "gpt-4",
                "messages" => [
                    ["role" => "system", "content" => "اعتبر نفسك خبير في ترجمة اسماء المنتجات الخاصة بمنصتي وقم باعادة صياغتها بشكل مفهوم وصحيح للعميل."],
                    ["role" => "user", "content" => "Translate the following product into {$targetLanguage}: \"{$text}\""]
                    // ["role" => "system", "content" => "You are a professional translator."],
                    // ["role" => "user", "content" => "Translate the following text into {$targetLanguage}: \"{$text}\""]
                ],
                "temperature" => 0.3
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? $text;
            } else {
                \Log::error("OpenAI Translation Error: " . $response->body());
                return ["error"=>"❌ OPEN AI API Error: " . $response->body()];
            }
        } catch (\Exception $e) {
            \Log::error("OpenAI Translation Exception: " . $e->getMessage());
            return ["error"=>"❌ OpenAI 2 Exception: " . $e->getMessage()];
        }
    }

    // دالة إنشاء وصف المنتج باستخدام OpenAI API
    public function generateDescription($productName, $apiKey, $targetLanguage = "en")
    {
        $apiUrl = "https://api.openai.com/v1/chat/completions";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json'
            ])->post($apiUrl, [
                "model" => "gpt-4",
                "messages" => [
                    ["role" => "system", "content" => "You are a professional product content writer."],
                    ["role" => "user", "content" => "Generate a high-quality product description for '{$productName}' in {$targetLanguage}."]
                ],
                "temperature" => 0.7
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? "⚠ Description could not be generated.";
            } else {
                \Log::error("OpenAI Description Error: " . $response->body());
                return "❌ API Error: " . $response->body();
            }
        } catch (\Exception $e) {
            \Log::error("OpenAI Description Exception: " . $e->getMessage());
            return "❌ Exception: " . $e->getMessage();
        }
    }
}
