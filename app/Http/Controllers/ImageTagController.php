<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleVisionService;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ImageTagController extends Controller
{
    protected $visionService;

    public function __construct(GoogleVisionService $visionService)
    {
        $this->visionService = $visionService;
    }

    /**
     * Tag a single image using Google Vision
     */
    public function tagImage(Request $request)
    {
        // Check if user is authenticated and has seller role (role_id = 4)
        if (!auth()->check()) {
            Log::error('ImageTag: User not authenticated');
            return response()->json(['error' => true, 'message' => 'User not authenticated.', 'redirect' => '/login'], 401);
        }
        
        $user = auth()->user();
        Log::info('ImageTag: User authenticated', ['user_id' => $user->id, 'role_id' => $user->role_id]);
        
        if ($user->role_id != 4) {
            Log::error('ImageTag: User does not have seller role', ['user_id' => $user->id, 'role_id' => $user->role_id]);
            return response()->json(['error' => true, 'message' => 'Access denied. Only sellers can access this feature.', 'redirect' => '/onboard'], 403);
        }

        $request->validate([
            'image_url' => 'required|url',
            'product_id' => 'nullable|exists:products,id'
        ]);

        try {
            $imageUrl = $request->input('image_url');
            $productId = $request->input('product_id');

            // Get tags from Google Vision
            $tags = $this->visionService->getProductTags($imageUrl);

            if (!$tags) {
                return response()->json([
                    'error' => true,
                    'message' => 'Failed to analyze image. Please try again.'
                ], 400);
            }

            // If product_id is provided, update the product with tags
            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    // Convert tags array to JSON string for storage
                    $tagsJson = json_encode($tags);
                    $product->update(['tags' => $tagsJson]);
                }
            }

            return response()->json([
                'success' => true,
                'tags' => $tags,
                'message' => 'Image tagged successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Image tagging error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while tagging the image.'
            ], 500);
        }
    }

    /**
     * Tag multiple images for a batch of products
     */
    public function tagBatchImages(Request $request)
    {
        // Check if user is authenticated and has seller role (role_id = 4)
        if (!auth()->check()) {
            Log::error('ImageTag batch: User not authenticated');
            return response()->json(['error' => true, 'message' => 'User not authenticated.', 'redirect' => '/login'], 401);
        }
        
        $user = auth()->user();
        Log::info('ImageTag batch: User authenticated', ['user_id' => $user->id, 'role_id' => $user->role_id]);
        
        if ($user->role_id != 4) {
            Log::error('ImageTag batch: User does not have seller role', ['user_id' => $user->id, 'role_id' => $user->role_id]);
            return response()->json(['error' => true, 'message' => 'Access denied. Only sellers can access this feature.', 'redirect' => '/onboard'], 403);
        }

        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $productIds = $request->input('product_ids');
        $results = [];

        try {
            foreach ($productIds as $productId) {
                $product = Product::find($productId);
                if (!$product || !$product->image) {
                    $results[$productId] = [
                        'success' => false,
                        'message' => 'Product not found or no image available'
                    ];
                    continue;
                }

                // Get the full image URL
                $imageUrl = asset('storage/' . $product->image);
                
                // Get tags from Google Vision
                $tags = $this->visionService->getProductTags($imageUrl);

                if ($tags) {
                    // Update product with tags
                    $tagsJson = json_encode($tags);
                    $product->update(['tags' => $tagsJson]);

                    $results[$productId] = [
                        'success' => true,
                        'tags' => $tags,
                        'message' => 'Image tagged successfully'
                    ];
                } else {
                    $results[$productId] = [
                        'success' => false,
                        'message' => 'Failed to analyze image'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => 'Batch tagging completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Batch image tagging error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while processing batch tagging.'
            ], 500);
        }
    }

    /**
     * Get tags for a specific product
     */
    public function getProductTags(Request $request, $productId)
    {
        // Check if user is authenticated and has seller role (role_id = 4)
        if (!auth()->check()) {
            Log::error('ImageTag getProductTags: User not authenticated');
            return response()->json(['error' => true, 'message' => 'User not authenticated.', 'redirect' => '/login'], 401);
        }
        
        $user = auth()->user();
        Log::info('ImageTag getProductTags: User authenticated', ['user_id' => $user->id, 'role_id' => $user->role_id]);
        
        if ($user->role_id != 4) {
            Log::error('ImageTag getProductTags: User does not have seller role', ['user_id' => $user->id, 'role_id' => $user->role_id]);
            return response()->json(['error' => true, 'message' => 'Access denied. Only sellers can access this feature.', 'redirect' => '/onboard'], 403);
        }

        try {
            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            $tags = [];
            if ($product->tags) {
                $tags = json_decode($product->tags, true) ?: [];
            }

            return response()->json([
                'success' => true,
                'product_id' => $productId,
                'tags' => $tags
            ]);

        } catch (\Exception $e) {
            Log::error('Get product tags error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while retrieving tags.'
            ], 500);
        }
    }

    /**
     * Update tags for a product manually
     */
    public function updateProductTags(Request $request, $productId)
    {
        // Check if user is authenticated and has seller role (role_id = 4)
        if (!auth()->check()) {
            Log::error('ImageTag updateProductTags: User not authenticated');
            return response()->json(['error' => true, 'message' => 'User not authenticated.', 'redirect' => '/login'], 401);
        }
        
        $user = auth()->user();
        Log::info('ImageTag updateProductTags: User authenticated', ['user_id' => $user->id, 'role_id' => $user->role_id]);
        
        if ($user->role_id != 4) {
            Log::error('ImageTag updateProductTags: User does not have seller role', ['user_id' => $user->id, 'role_id' => $user->role_id]);
            return response()->json(['error' => true, 'message' => 'Access denied. Only sellers can access this feature.', 'redirect' => '/onboard'], 403);
        }

        $request->validate([
            'tags' => 'required|array',
            'tags.*.name' => 'required|string',
            'tags.*.confidence' => 'nullable|numeric|between:0,1',
            'tags.*.type' => 'nullable|string'
        ]);

        try {
            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Product not found'
                ], 404);
            }

            // Update product with new tags
            $tagsJson = json_encode($request->input('tags'));
            $product->update(['tags' => $tagsJson]);

            return response()->json([
                'success' => true,
                'message' => 'Product tags updated successfully',
                'tags' => $request->input('tags')
            ]);

        } catch (\Exception $e) {
            Log::error('Update product tags error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating tags.'
            ], 500);
        }
    }
} 