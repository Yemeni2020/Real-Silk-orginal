<?php

namespace App\Services;

use App\Enums\ViewPaths\Admin\AddonSetup;
use App\Traits\FileManagerTrait;
use App\Traits\SettingsTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DeepSeekAIService
{
    use SettingsTrait;
    use FileManagerTrait;

    
    public function translateText($text, $targetLanguage, $apiKey)
    {
        $apiUrl = "https://api.deepseek.com/v1/chat/completions"; // استخدم Chat API

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json'
            ])->post($apiUrl, [
                'model' => 'deepseek-chat', // تأكد من استخدام الموديل الصحيح
                'messages' => [
                    ['role' => 'system', 'content' => "You are a translation assistant. Translate the user's input into $targetLanguage."],
                    ['role' => 'user', 'content' => $text]
                ],
                'temperature' => 0.5, // تحكم في دقة الترجمة
                'max_tokens' => 100
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? $text;
            } else {
                \Log::error("DeepSeek Translation Error: " . $response->body());
                return ["error" => "❌ 1 DEEPSEEK AI API Error: " . $response->body()];
            }
        } catch (\Exception $e) {
            \Log::error("DeepSeek Translation Exception: " . $e->getMessage());
            return ["error" => "❌ 2 Exception: " . $e->getMessage()];
        }
    }


    // دالة إنشاء وصف المنتج باستخدام DeepSeek API
    public function generateDescription($productName, $apiKey, $targetLanguage = "en")
    {
        $apiUrl = "https://api.deepseek.com/v1/generate-text";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json'
            ])->post($apiUrl, [
                "model" => "deepseek-13b",
                "messages" => [
                    ["role" => "system", "content" => "You are a professional product content writer."],
                    ["role" => "user", "content" => "Generate a high-quality product description for '{$productName}' in {$targetLanguage}."]
                ],
                "temperature" => 0.7
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? "⚠ Description could not be generated.";
            } else {
                \Log::error("DeepSeek Description Error: " . $response->body());
                return "❌ API Error: " . $response->body();
            }
        } catch (\Exception $e) {
            \Log::error("DeepSeek Description Exception: " . $e->getMessage());
            return "❌ Exception: " . $e->getMessage();
        }
    }
}
