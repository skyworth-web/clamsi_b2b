<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client;
use Illuminate\Support\Facades\DB;

class GoogleVisionService
{
    protected $baseUrl = 'https://vision.googleapis.com/v1/images:annotate';
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();
    }

    /**
     * Get Google OAuth2 access token using service account
     */
    protected function getAccessToken()
    {
        // Fetch the file name from the settings table
        $file_name = DB::table('settings')
            ->where('variable', 'service_account_file')
            ->value('value');
        $file_path = storage_path('app/public/' . $file_name);
        if (!file_exists($file_path)) {
            throw new \Exception('Service account file not found.');
        }
        $client = new Client();
        $client->setAuthConfig($file_path);
        $client->setScopes(['https://www.googleapis.com/auth/cloud-platform']);
        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];
        return $accessToken;
    }

    /**
     * Tag an image using Google Vision API
     */
    public function tagImage($imageUrl)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'requests' => [[
                    'image' => [
                        'source' => [
                            'imageUri' => $imageUrl
                        ]
                    ],
                    'features' => [
                        ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                        ['type' => 'WEB_DETECTION', 'maxResults' => 10],
                    ]
                ]]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->extractTags($data);
            }

            Log::error('Google Vision API error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Google Vision service error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract tags from Google Vision API response
     */
    protected function extractTags($data)
    {
        $tags = [];
        $responses = $data['responses'][0] ?? [];

        // LABEL_DETECTION
        if (!empty($responses['labelAnnotations'])) {
            foreach ($responses['labelAnnotations'] as $label) {
                if ($label['score'] > 0.6) {
                    $tags[] = [
                        'name' => $label['description'],
                        'confidence' => $label['score'],
                        'type' => $this->categorizeTag($label['description'])
                    ];
                }
            }
        }
        // WEB_DETECTION (webEntities)
        if (!empty($responses['webDetection']['webEntities'])) {
            foreach ($responses['webDetection']['webEntities'] as $entity) {
                if (!empty($entity['description']) && ($entity['score'] ?? 0) > 0.6) {
                    $tags[] = [
                        'name' => $entity['description'],
                        'confidence' => $entity['score'] ?? 0.7,
                        'type' => $this->categorizeTag($entity['description'])
                    ];
                }
            }
        }
        // Remove duplicates by name
        $tags = collect($tags)->unique('name')->values()->all();
        return $tags;
    }

    /**
     * Get comprehensive tags for a product image
     */
    public function getProductTags($imageUrl)
    {
        $tags = $this->tagImage($imageUrl);
        if ($tags && !empty($tags)) {
            // Prioritize fashion-related tags
            $fashionTags = [];
            $otherTags = [];
            foreach ($tags as $tag) {
                if ($this->isFashionRelated($tag['name'])) {
                    $tag['type'] = 'fashion';
                    $fashionTags[] = $tag;
                } else {
                    $tag['type'] = $this->categorizeTag($tag['name']);
                    $otherTags[] = $tag;
                }
            }
            $allTags = array_merge($fashionTags, $otherTags);
            usort($allTags, function($a, $b) {
                return $b['confidence'] <=> $a['confidence'];
            });
            return $allTags;
        }
        return [];
    }

    /**
     * Check if a tag is fashion-related
     */
    protected function isFashionRelated($tagName)
    {
        $tagName = strtolower($tagName);
        $fashionTerms = [
            'clothing', 'apparel', 'shirt', 'pants', 'dress', 'shoes', 'bag', 'accessory',
            'fashion', 'style', 'outfit', 'wear', 'garment', 'jacket', 'coat', 'sweater',
            'blouse', 't-shirt', 'jeans', 'skirt', 'shorts', 'suit', 'tie', 'scarf',
            'hat', 'cap', 'sunglasses', 'watch', 'jewelry', 'necklace', 'earrings',
            'bracelet', 'ring', 'belt', 'wallet', 'purse', 'handbag', 'backpack',
            'sneakers', 'boots', 'sandals', 'heels', 'flats', 'socks', 'underwear',
            'lingerie', 'swimwear', 'activewear', 'sportswear', 'casual', 'formal',
            'business', 'evening', 'party', 'wedding', 'bridal', 'maternity',
            'children', 'kids', 'baby', 'men', 'women', 'unisex', 'vintage',
            'modern', 'classic', 'trendy', 'designer', 'brand', 'logo'
        ];
        foreach ($fashionTerms as $term) {
            if (strpos($tagName, $term) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Categorize a tag based on its name
     */
    protected function categorizeTag($tagName)
    {
        $tagName = strtolower($tagName);
        $fashionTerms = ['clothing', 'apparel', 'shirt', 'pants', 'dress', 'shoes', 'bag', 'accessory', 'fashion', 'style', 'outfit', 'wear', 'garment'];
        foreach ($fashionTerms as $term) {
            if (strpos($tagName, $term) !== false) {
                return 'fashion';
            }
        }
        $colorTerms = ['red', 'blue', 'green', 'yellow', 'black', 'white', 'pink', 'purple', 'orange', 'brown', 'gray', 'grey', 'navy', 'beige', 'cream'];
        foreach ($colorTerms as $term) {
            if (strpos($tagName, $term) !== false) {
                return 'color';
            }
        }
        return 'general';
    }
} 