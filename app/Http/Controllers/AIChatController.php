<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AIChatController extends Controller
{
    // Step 1: Start chat-based product upload
    public function chat(Request $request)
    {
        $history = $request->input('history', []);
        if (is_string($history)) {
            $decoded = json_decode($history, true);
            if (is_array($decoded)) {
                $history = $decoded;
            }
        }
        $userMessage = $request->input('message');
        $fileSummary = [];
        $productIds = [];
        $excelRows = 0;
        $categorizationResult = null;
        $uploadedImages = [];
        // Handle image upload
        if ($request->hasFile('images')) {
            // Log the current DB connection name
            try {
                $connectionName = DB::getDefaultConnection();
                Log::info('Current DB connection:', ['connection' => $connectionName]);
            } catch (\Exception $e) {
                Log::error('Error fetching DB connection: ' . $e->getMessage());
            }
            // Check if products table exists
            try {
                $tableExists = Schema::hasTable('products');
                Log::info('Products table exists:', ['exists' => $tableExists]);
            } catch (\Exception $e) {
                Log::error('Error checking products table existence: ' . $e->getMessage());
            }
            $images = $request->file('images');
            foreach ($images as $image) {
                // Ensure unique product name
                $maxAttempts = 5;
                $attempt = 0;
                do {
                    $uniqueName = 'Product_' . time() . '_' . Str::random(8);
                    $exists = Product::where('name', $uniqueName)->exists();
                    $attempt++;
                } while ($exists && $attempt < $maxAttempts);
                if ($exists) {
                    Log::error('Failed to generate unique product name after ' . $maxAttempts . ' attempts.');
                    continue; // Skip this image
                }
                $imagePath = $image->store('uploads/products');
                $product = Product::create([
                    'name' => json_encode($uniqueName),
                    'image' => $imagePath,
                    'status' => 0, // draft
                    'store_id' => 1, // default store
                    'seller_id' => auth()->id() ?? 1, // current user or default
                    'slug' => Str::slug($uniqueName),
                    'type' => 'simple_product',
                    'stock_type' => 'product_level',
                    'availability' => 'in_stock',
                    'is_returnable' => 1,
                    'is_cancelable' => 1,
                    'cod_allowed' => 1,
                ]);
                $productIds[] = $product->id;
                // Add uploaded image info for frontend
                $uploadedImages[] = [
                    'id' => $product->id,
                    'name' => $uniqueName,
                    'url' => asset('storage/' . $imagePath),
                ];
            }
            $fileSummary[] = count($images) . ' product images uploaded';
        }
        // Handle excel upload
        if ($request->hasFile('excel')) {
            $excel = $request->file('excel');
            // For now, just note that Excel was uploaded without processing
            $fileSummary[] = 'Excel file uploaded (processing not yet implemented)';
            // TODO: Implement Excel processing when Maatwebsite\Excel is properly installed
        }
        // --- AI Auto-categorization logic ---
        $autoCategorize = false;
        if ($userMessage) {
            $msg = strtolower($userMessage);
            if (strpos($msg, 'auto categorize') !== false || strpos($msg, 'auto-categorize') !== false || strpos($msg, 'categorize products') !== false || strpos($msg, 'start ai sorting') !== false) {
                $autoCategorize = true;
            }
        }
        if ($autoCategorize) {
            // Fetch products for this user/session (for demo, fetch all products with status=0 or 1)
            $products = Product::whereIn('status', [0, 1])->get(['id', 'name', 'image', 'description']);
            // Fetch categories
            $categories = Category::all(['id', 'name']);
            // Prepare product and category data for prompt
            $productList = [];
            foreach ($products as $p) {
                $name = $p->name;
                if (is_string($name)) {
                    $decoded = json_decode($name, true);
                    $name = is_array($decoded) ? ($decoded['en'] ?? reset($decoded)) : $name;
                }
                $productList[] = [
                    'id' => $p->id,
                    'name' => $name,
                    'description' => $p->description,
                ];
            }
            $categoryList = [];
            foreach ($categories as $c) {
                $name = $c->name;
                if (is_string($name)) {
                    $decoded = json_decode($name, true);
                    $name = is_array($decoded) ? ($decoded['en'] ?? reset($decoded)) : $name;
                }
                $categoryList[] = [
                    'id' => $c->id,
                    'name' => $name,
                ];
            }
            // Build prompt for ChatGPT
            $prompt = "You are an AI assistant for an e-commerce platform. Given a list of products and categories, assign each product to the most appropriate category and suggest a style tag.\n";
            $prompt .= "Return a JSON array: [{product_id, suggested_category_id, style_tag, reason}].\n";
            $prompt .= "Products: " . json_encode($productList) . "\n";
            $prompt .= "Categories: " . json_encode($categoryList) . "\n";
            $prompt .= "If you are unsure, pick the closest category.\n";
            // Call OpenAI API
            $openaiApiKey = env('OPENAI_API_KEY');
            $openaiResponse = \Http::withToken($openaiApiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                    ],
                    'max_tokens' => 600,
                    'response_format' => ['type' => 'json_object'],
                ]);
            $aiMessage = '';
            $nextStep = '';
            if ($openaiResponse->successful()) {
                $result = $openaiResponse->json();
                $content = $result['choices'][0]['message']['content'] ?? '';
                $json = json_decode($content, true);
                if (is_array($json) && isset($json[0]['product_id'])) {
                    $categorizationResult = $json;
                    $aiMessage = 'Here are the AI-categorized products. You can now sort or tag them as you wish.';
                    $nextStep = 'show_categorization';
                } else {
                    $aiMessage = $content;
                    $nextStep = '';
                }
            } else {
                $aiMessage = 'Sorry, there was an error contacting the AI assistant.';
                $nextStep = '';
            }
            // Append to history
            if ($userMessage) {
                $history[] = ['user' => $userMessage];
            }
            if ($aiMessage) {
                $history[] = ['ai' => $aiMessage];
            }
            $data = [];
            if ($categorizationResult) {
                $data['categorization'] = $categorizationResult;
            }
            if ($nextStep) {
                $data['next_step'] = $nextStep;
            }
            return response()->json([
                'response' => $aiMessage,
                'data' => $data,
                'history' => $history,
            ]);
        }
        // Build chat history string
        $historyStr = "";
        foreach ($history as $entry) {
            if (isset($entry['user'])) {
                $historyStr .= "User: " . $entry['user'] . "\n";
            }
            if (isset($entry['ai'])) {
                $historyStr .= "AI: " . $entry['ai'] . "\n";
            }
        }
        // Compose OpenAI prompt
        $prompt = "You are an AI assistant helping a supplier upload products to an e-commerce platform.\n";
        $prompt .= "Guide the user step by step through uploading product images, categorizing products, and uploading Excel files for pricing and stock.\n";
        $prompt .= "Always output a JSON object with two fields: 'message' (your reply to the user) and 'next_step' (the next action the user should take, e.g., 'upload_images', 'ask_auto_categorize', 'upload_excel', 'done').\n";
        $prompt .= "Here is the conversation so far:\n";
        $prompt .= $historyStr;
        if (!empty($fileSummary)) {
            $prompt .= "User just uploaded: " . implode('; ', $fileSummary) . "\n";
        }
        if ($userMessage) {
            $prompt .= "User message: $userMessage\n";
        }
        // Call OpenAI API
        $openaiApiKey = env('OPENAI_API_KEY');
        $openaiResponse = Http::withToken($openaiApiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                ],
                'max_tokens' => 400,
                'response_format' => ['type' => 'json_object'],
            ]);
        $aiMessage = '';
        $nextStep = '';
        if ($openaiResponse->successful()) {
            $result = $openaiResponse->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            $json = json_decode($content, true);
            if (is_array($json)) {
                $aiMessage = $json['message'] ?? '';
                $nextStep = $json['next_step'] ?? '';
            } else {
                $aiMessage = $content;
                $nextStep = '';
            }
        } else {
            $aiMessage = 'Sorry, there was an error contacting the AI assistant.';
            $nextStep = '';
        }
        // Append to history
        if ($userMessage) {
            $history[] = ['user' => $userMessage];
        }
        if ($aiMessage) {
            $history[] = ['ai' => $aiMessage];
        }
        $data = [];
        if (!empty($productIds)) {
            $data['product_ids'] = $productIds;
        }
        if (!empty($uploadedImages)) {
            $data['uploaded_images'] = $uploadedImages;
        }
        if ($excelRows) {
            $data['excel_rows'] = $excelRows;
        }
        if ($nextStep) {
            $data['next_step'] = $nextStep;
        }
        return response()->json([
            'response' => $aiMessage,
            'data' => $data,
            'history' => $history,
        ]);
    }

    public function clearHistory(Request $request)
    {
        // No file to clear, just return success
        return response()->json(['success' => true]);
    }
}