<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        // Handle image upload
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $image) {
                $imagePath = $image->store('uploads/products');
                $uniqueName = 'Product_' . time() . '_' . Str::random(8);
                $product = Product::create([
                    'name' => $uniqueName,
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