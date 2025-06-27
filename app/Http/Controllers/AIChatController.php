<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    // Step 1: Start chat-based product upload
    public function chat(Request $request)
    {
        $step = $request->input('step', 'start');
        $history = $request->input('history', []);
        if (is_string($history)) {
            $decoded = json_decode($history, true);
            if (is_array($decoded)) {
                $history = $decoded;
            }
        }
        $response = null;
        $data = [];


        Log::debug('AIChatController@chat called', [
            'step' => $step,
            'history' => $history,
            'request_data' => $request->all(),
        ]);

        switch ($step) {
            case 'start':
                Log::debug('Step: start');
                $response = "Welcome! Ready to upload products. Please upload your product images.";
                $data['next_step'] = 'upload_images';
                break;
            case 'upload_images':
                Log::debug('Step: upload_images', ['has_images' => $request->hasFile('images')]);
                if ($request->hasFile('images')) {
                    $images = $request->file('images');
                    $productIds = [];
                    foreach ($images as $image) {
                        $imagePath = $image->store('uploads/products');
                        $product = Product::create([
                            'name' => 'Untitled Product',
                            'image' => $imagePath,
                            'status' => 0, // draft
                        ]);
                        $productIds[] = $product->id;
                        Log::debug('Product created from image', ['product_id' => $product->id, 'image_path' => $imagePath]);
                    }
                    $response = "Great! Images uploaded. Would you like me to automatically categorize these products using AI? (Yes/No)";
                    $data['product_ids'] = $productIds;
                    $data['next_step'] = 'ask_auto_categorize';
                } else {
                    $response = "No images uploaded. Please upload product images.";
                    $data['next_step'] = 'upload_images';
                }
                break;
            case 'ask_auto_categorize':
                $autoCategorize = strtolower($request->input('auto_categorize', 'no'));
                $productIds = $request->input('product_ids', []);
                Log::debug('Step: ask_auto_categorize', ['auto_categorize' => $autoCategorize, 'product_ids' => $productIds]);
                if ($autoCategorize === 'yes') {
                    $response = "Perfect. What Master Category do these products belong to?";
                    $data['product_ids'] = $productIds;
                    $data['next_step'] = 'ask_master_category';
                } else {
                    $response = "Okay, please specify the Master Category and Subcategory for each product.";
                    $data['product_ids'] = $productIds;
                    $data['next_step'] = 'manual_category';
                }
                break;
            case 'ask_master_category':
                $masterCategory = $request->input('master_category');
                $productIds = $request->input('product_ids', []);
                Log::debug('Step: ask_master_category', ['master_category' => $masterCategory, 'product_ids' => $productIds]);
                $category = Category::where('name', $masterCategory)->where('parent_id', 0)->first();
                if (!$category) {
                    $category = Category::create([
                        'name' => $masterCategory,
                        'parent_id' => 0,
                        'slug' => Str::slug($masterCategory),
                        'status' => 1,
                    ]);
                    Log::debug('Master category created', ['category_id' => $category->id]);
                }
                $subcategories = Category::where('parent_id', $category->id)->pluck('name')->toArray();
                Log::debug('Subcategories found', ['subcategories' => $subcategories]);
                if (empty($subcategories)) {
                    $response = "No subcategories found under $masterCategory. Please add subcategories first.";
                    $data['next_step'] = 'ask_master_category';
                } else {
                    $response = "Found subcategories: " . implode(', ', $subcategories) . ". Categorizing products using AI...";
                    // AI categorization for each product
                    $openaiApiKey = env('OPENAI_API_KEY');
                    $aiResults = [];
                    foreach ($productIds as $pid) {
                        $product = Product::find($pid);
                        if (!$product) continue;
                        $imageUrl = Storage::path($product->image);
                        $imageData = base64_encode(file_get_contents($imageUrl));
                        $aiPrompt = "Given these subcategories: [" . implode(', ', $subcategories) . "] and the product image, pick the best subcategory. Respond with only the subcategory name.";
                        Log::debug('Sending image to OpenAI for categorization', ['product_id' => $pid, 'prompt' => $aiPrompt]);
                        $openaiResponse = Http::withToken($openaiApiKey)
                            ->timeout(60)
                            ->post('https://api.openai.com/v1/chat/completions', [
                                'model' => 'gpt-4o',
                                'messages' => [
                                    ['role' => 'system', 'content' => $aiPrompt],
                                    [
                                        'role' => 'user',
                                        'content' => [
                                            ["type" => "image_url", "image_url" => ["url" => "data:image/jpeg;base64,$imageData"]],
                                        ],
                                    ],
                                ],
                                'max_tokens' => 50,
                            ]);
                        $subcategory = null;
                        if ($openaiResponse->successful()) {
                            $result = $openaiResponse->json();
                            $subcategory = $result['choices'][0]['message']['content'] ?? null;
                            Log::debug('OpenAI response', ['result' => $result, 'subcategory' => $subcategory]);
                        } else {
                            Log::error('OpenAI API call failed', ['response' => $openaiResponse->body()]);
                        }
                        // Find or create subcategory
                        $subcatModel = null;
                        if ($subcategory) {
                            $subcatModel = Category::firstOrCreate([
                                'name' => $subcategory,
                                'parent_id' => $category->id
                            ], [
                                'slug' => Str::slug($subcategory),
                                'status' => 1,
                            ]);
                            $product->category_id = $subcatModel->id;
                            $product->save();
                            Log::debug('Product categorized', ['product_id' => $pid, 'subcategory_id' => $subcatModel->id]);
                        }
                        $aiResults[] = [
                            'product_id' => $pid,
                            'subcategory' => $subcategory,
                        ];
                    }
                    $response .= "\nAI categorization complete. Would you like to group these products under a date-based category (e.g., June 2025)? (Yes/No)";
                    $data['product_ids'] = $productIds;
                    $data['master_category_id'] = $category->id;
                    $data['ai_results'] = $aiResults;
                    $data['next_step'] = 'ask_group_by_date';
                }
                break;
            case 'ask_group_by_date':
                $groupByDate = strtolower($request->input('group_by_date', 'no'));
                $productIds = $request->input('product_ids', []);
                $masterCategoryId = $request->input('master_category_id');
                Log::debug('Step: ask_group_by_date', ['group_by_date' => $groupByDate, 'product_ids' => $productIds, 'master_category_id' => $masterCategoryId]);
                if ($groupByDate === 'yes') {
                    $dateGroup = $request->input('date_group', date('F Y'));
                    $dateCategory = Category::firstOrCreate([
                        'name' => $dateGroup,
                        'parent_id' => $masterCategoryId
                    ], [
                        'slug' => Str::slug($dateGroup),
                        'status' => 1,
                    ]);
                    foreach ($productIds as $pid) {
                        $product = Product::find($pid);
                        if ($product) {
                            $product->category_id = $dateCategory->id;
                            $product->save();
                            Log::debug('Product grouped by date', ['product_id' => $pid, 'date_category_id' => $dateCategory->id]);
                        }
                    }
                    $response = "Products grouped under $dateGroup. Now let's add pricing, stock, and details. Please upload your Excel file.";
                    $data['next_step'] = 'upload_excel';
                } else {
                    $response = "Okay, skipping date-based grouping. Please upload your Excel file for pricing, stock, and details.";
                    $data['next_step'] = 'upload_excel';
                }
                break;
            case 'upload_excel':
                Log::debug('Step: upload_excel', ['has_excel' => $request->hasFile('excel')]);
                if ($request->hasFile('excel')) {
                    $excel = $request->file('excel');
                    $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $excel);
                    $updated = 0;
                    if (!empty($rows) && isset($rows[0])) {
                        foreach ($rows[0] as $row) {
                            // Expecting: image_name, price, stock, description, sku (optional), color (optional), size (optional)
                            $imageName = $row['image_name'] ?? $row[0] ?? null;
                            $sku = $row['sku'] ?? $row[4] ?? null;
                            if (!$imageName && !$sku) continue;
                            $query = Product::query();
                            if ($sku) {
                                $query->where('sku', $sku);
                            } else {
                                $query->where('image', 'like', "%$imageName%");
                            }
                            $product = $query->first();
                            if ($product) {
                                if (isset($row['price'])) $product->price = $row['price'];
                                if (isset($row['stock'])) $product->stock = $row['stock'];
                                if (isset($row['description'])) $product->description = $row['description'];
                                if (isset($row['color'])) $product->color = $row['color'];
                                if (isset($row['size'])) $product->size = $row['size'];
                                $product->save();
                                $updated++;
                                Log::debug('Product updated from Excel', ['product_id' => $product->id, 'row' => $row]);
                            } else {
                                Log::warning('Product not found for Excel row', ['row' => $row]);
                            }
                        }
                    }
                    $response = "Excel file processed. $updated products updated. ✅ All products have been categorized and updated. Upload complete!";
                    $data['next_step'] = 'done';
                } else {
                    $response = "No Excel file uploaded. Please upload your Excel file.";
                    $data['next_step'] = 'upload_excel';
                }
                break;
            default:
                Log::warning('Unknown step in chat', ['step' => $step]);
                $response = "Unknown step. Please start again.";
                $data['next_step'] = 'start';
        }

        Log::debug('AIChatController@chat response', [
            'response' => $response,
            'data' => $data,
            'history' => $history,
        ]);

        // Append the latest user message and AI response to history
        $userMessage = $request->input('message');
        if ($userMessage) {
            $history[] = ['user' => $userMessage];
        }
        if ($response) {
            $history[] = ['ai' => $response];
        }

        return response()->json([
            'response' => $response,
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