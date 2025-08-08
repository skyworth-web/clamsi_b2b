<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    /**
     * Get tags from OpenAI for a given image URL
     */
    public function getImageTags($imageUrl)
    {
        $prompt = "Given this product image, generate two lists:\n1. Aesthetic Style Tags (e.g., Minimalist, Floral, Modern, etc.)\n2. Visual Qualities (e.g., Polished Gold Finish, Glossy, Symmetrical, etc.)\nReturn each as a plain list. If you can't see the image, say so.";

        // Download image and encode as base64
        $imageData = @file_get_contents($imageUrl);
        if ($imageData === false) {
            return [
                'aesthetic_styles' => [],
                'visual_qualities' => [],
                'error' => 'Could not download image for OpenAI analysis.',
            ];
        }
        // Try to detect mime type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($imageData) ?: 'image/jpeg';
        $base64 = base64_encode($imageData);

        $messages = [
            ["role" => "system", "content" => "You are a helpful assistant for product image analysis."],
            [
                "role" => "user",
                "content" => [
                    ["type" => "text", "text" => $prompt],
                    ["type" => "image_url", "image_url" => ["url" => "data:$mime;base64,$base64"]]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => $messages,
            'max_tokens' => 512,
        ]);

        if ($response->failed()) {
            return [
                'aesthetic_styles' => [],
                'visual_qualities' => [],
                'error' => $response->body(),
            ];
        }

        $content = $response->json('choices.0.message.content');
        // Parse the content into two lists
        $aesthetic = [];
        $visual = [];
        if ($content) {
            $lines = preg_split('/\r?\n/', $content);
            $current = null;
            foreach ($lines as $line) {
                $line = trim($line);
                if (stripos($line, 'Aesthetic Style Tags') !== false) {
                    $current = 'aesthetic';
                    continue;
                }
                if (stripos($line, 'Visual Qualities') !== false) {
                    $current = 'visual';
                    continue;
                }
                if ($current && preg_match('/^[\-*\d.]+\s*(.+)$/', $line, $m)) {
                    if ($current === 'aesthetic') $aesthetic[] = $m[1];
                    if ($current === 'visual') $visual[] = $m[1];
                }
            }
        }
        return [
            'aesthetic_styles' => $aesthetic,
            'visual_qualities' => $visual,
        ];
    }
} 