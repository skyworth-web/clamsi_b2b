<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AIChatController extends Controller
{
    // Placeholder for local vector store (e.g., FAISS)
    protected $vectorStorePath = 'vector_store/chat_history.json';

    public function chat(Request $request)
    {
        $text = $request->input('message');
        $image = $request->file('image');
        $history = $request->input('history', []);
        $response = null;
        $detectedCategory = null;

        // --- OpenAI API integration ---
        $openaiApiKey = env('OPENAI_API_KEY');
        if (!$openaiApiKey) {
            return response()->json(['error' => 'OpenAI API key not set.'], 500);
        }

        if ($image) {
            // Save image temporarily
            $imagePath = $image->store('temp_images');
            $imageUrl = Storage::path($imagePath);
            // Read image as base64
            $imageData = base64_encode(file_get_contents($imageUrl));
            // Call OpenAI Vision API (GPT-4 Vision)
            $visionPrompt = 'Detect the most relevant product category for this image. Respond with only the category name.';
            $openaiResponse = Http::withToken($openaiApiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4-vision-preview',
                    'messages' => [
                        ['role' => 'system', 'content' => $visionPrompt],
                        [
                            'role' => 'user',
                            'content' => [
                                ["type" => "image_url", "image_url" => ["url" => "data:image/jpeg;base64,$imageData"]],
                            ],
                        ],
                    ],
                    'max_tokens' => 50,
                ]);
            if ($openaiResponse->successful()) {
                $result = $openaiResponse->json();
                $detectedCategory = $result['choices'][0]['message']['content'] ?? null;
                $response = 'Detected category: ' . ($detectedCategory ?: 'Unknown');
            } else {
                $response = 'Failed to detect category.';
            }
        } elseif ($text) {
            // Prepare chat history for OpenAI
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful assistant for product upload and categorization.'],
            ];
            if (is_array($history)) {
                foreach ($history as $entry) {
                    if (isset($entry['user'])) {
                        $messages[] = ['role' => 'user', 'content' => $entry['user']];
                    }
                    if (isset($entry['ai'])) {
                        $messages[] = ['role' => 'assistant', 'content' => $entry['ai']];
                    }
                }
            }
            $messages[] = ['role' => 'user', 'content' => $text];
            // Call OpenAI Chat API
            $openaiResponse = Http::withToken($openaiApiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => $messages,
                    'max_tokens' => 500,
                ]);
            if ($openaiResponse->successful()) {
                $result = $openaiResponse->json();
                $response = $result['choices'][0]['message']['content'] ?? 'No response.';
            } else {
                $response = 'Failed to get AI response.';
            }
        } else {
            return response()->json(['error' => 'No message or image provided.'], 400);
        }

        // --- Store chat history in local vector store (placeholder) ---
        // TODO: Integrate with FAISS or local vector store
        // For now, just append to a JSON file
        $chatHistory = [
            'user' => $text ?? '[image]',
            'ai' => $response,
            'category' => $detectedCategory,
            'timestamp' => now()->toDateTimeString(),
        ];
        $existing = [];
        if (Storage::exists($this->vectorStorePath)) {
            $existing = json_decode(Storage::get($this->vectorStorePath), true) ?? [];
        }
        $existing[] = $chatHistory;
        Storage::put($this->vectorStorePath, json_encode($existing));

        return response()->json([
            'response' => $response,
            'detected_category' => $detectedCategory,
            'history' => $existing,
        ]);
    }

    public function clearHistory(Request $request)
    {
        if (\Storage::exists($this->vectorStorePath)) {
            \Storage::delete($this->vectorStorePath);
        }
        return response()->json(['success' => true]);
    }
} 