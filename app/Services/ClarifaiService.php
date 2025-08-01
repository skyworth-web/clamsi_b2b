<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClarifaiService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.clarifai.com/v2';

    public function __construct()
    {
        $this->apiKey = config('services.clarifai.api_key');
    }

    /**
     * Tag an image using Clarifai General model
     */
    public function tagImage($imageUrl)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/models/general-image-recognition-1/outputs', [
                'inputs' => [
                    [
                        'data' => [
                            'image' => [
                                'url' => $imageUrl
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->extractTags($data);
            }

            Log::error('Clarifai API error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Clarifai service error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tag an image using Clarifai Fashion model for clothing/apparel
     */
    public function tagFashionImage($imageUrl)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/models/apparel-classification/outputs', [
                'inputs' => [
                    [
                        'data' => [
                            'image' => [
                                'url' => $imageUrl
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->extractFashionTags($data);
            }

            Log::error('Clarifai Fashion API error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Clarifai Fashion service error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tag an image using Clarifai Color model
     */
    public function tagColorImage($imageUrl)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/models/color-recognition/outputs', [
                'inputs' => [
                    [
                        'data' => [
                            'image' => [
                                'url' => $imageUrl
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->extractColorTags($data);
            }

            Log::error('Clarifai Color API error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Clarifai Color service error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract tags from Clarifai General model response
     */
    protected function extractTags($data)
    {
        $tags = [];
        
        if (isset($data['outputs'][0]['data']['concepts'])) {
            foreach ($data['outputs'][0]['data']['concepts'] as $concept) {
                if ($concept['value'] > 0.7) { // Only include high confidence tags
                    $tags[] = [
                        'name' => $concept['name'],
                        'confidence' => $concept['value']
                    ];
                }
            }
        }

        return $tags;
    }

    /**
     * Extract fashion tags from Clarifai Fashion model response
     */
    protected function extractFashionTags($data)
    {
        $tags = [];
        
        if (isset($data['outputs'][0]['data']['concepts'])) {
            foreach ($data['outputs'][0]['data']['concepts'] as $concept) {
                if ($concept['value'] > 0.6) { // Lower threshold for fashion items
                    $tags[] = [
                        'name' => $concept['name'],
                        'confidence' => $concept['value'],
                        'type' => 'fashion'
                    ];
                }
            }
        }

        return $tags;
    }

    /**
     * Extract color tags from Clarifai Color model response
     */
    protected function extractColorTags($data)
    {
        $tags = [];
        
        if (isset($data['outputs'][0]['data']['colors'])) {
            foreach ($data['outputs'][0]['data']['colors'] as $color) {
                if ($color['value'] > 0.1) { // Include colors with >10% presence
                    $tags[] = [
                        'name' => $color['w3c']['name'],
                        'confidence' => $color['value'],
                        'type' => 'color',
                        'hex' => $color['w3c']['hex']
                    ];
                }
            }
        }

        return $tags;
    }

    /**
     * Get comprehensive tags for a product image
     */
    public function getProductTags($imageUrl)
    {
        $allTags = [];
        
        // Get general tags
        $generalTags = $this->tagImage($imageUrl);
        if ($generalTags) {
            $allTags = array_merge($allTags, $generalTags);
        }

        // Get fashion tags
        $fashionTags = $this->tagFashionImage($imageUrl);
        if ($fashionTags) {
            $allTags = array_merge($allTags, $fashionTags);
        }

        // Get color tags
        $colorTags = $this->tagColorImage($imageUrl);
        if ($colorTags) {
            $allTags = array_merge($allTags, $colorTags);
        }

        // Sort by confidence and remove duplicates
        $uniqueTags = [];
        $seen = [];
        
        foreach ($allTags as $tag) {
            $tagName = strtolower($tag['name']);
            if (!isset($seen[$tagName])) {
                $seen[$tagName] = true;
                $uniqueTags[] = $tag;
            }
        }

        // Sort by confidence (highest first)
        usort($uniqueTags, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        return $uniqueTags;
    }
} 