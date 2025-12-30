<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Models\ShortenedUrl;
use Illuminate\Support\Str;

class ShortenUrlController extends Controller
{
    public function shortenUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
        ], [
            'url.required' => 'The URL field is required.',
            'url.url' => 'Please provide a valid URL.',
            'url.max' => 'The URL must not exceed 2048 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $originalUrl = $request->url;
            $userId = $request->user()->id;

            // Check if URL already exists for this user
            $existingUrl = ShortenedUrl::where('user_id', $userId)
                ->where('original_url', $originalUrl)
                ->first();

            if ($existingUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'This URL has already been shortened',
                    'errors' => [
                        'url' => ['A shortened URL for this address already exists.']
                    ],
                    'data' => [
                        'short_code' => $existingUrl->short_code,
                        'short_url' => URL::to('/api/redirect/' . $existingUrl->short_code),
                        'original_url' => $existingUrl->original_url
                    ]
                ], 409); // 409 Conflict
            }

            // Generate unique short code
            $shortCode = $this->generateUniqueShortCode();

            // Create shortened URL
            $shortenedUrl = ShortenedUrl::create([
                'user_id' => $userId,
                'original_url' => $originalUrl,
                'short_code' => $shortCode,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'URL shortened successfully',
                'data' => [
                    'id' => $shortenedUrl->id,
                    'original_url' => $shortenedUrl->original_url,
                    'short_code' => $shortenedUrl->short_code,
                    'short_url' => URL::to('/api/redirect/' . $shortenedUrl->short_code),
                    'created_at' => $shortenedUrl->created_at->toISOString()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to shorten URL. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


     public function redirect($shortCode)
    {
        try {
            $shortenedUrl = ShortenedUrl::where('short_code', $shortCode)->first();

            if (!$shortenedUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Short URL not found',
                    'errors' => [
                        'short_code' => ['The provided short code does not exist.']
                    ]
                ], 404);
            }

            // Redirect to original URL
            return redirect($shortenedUrl->original_url);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to redirect. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    private function generateUniqueShortCode($length = 8)
    {
        do {
            $shortCode = Str::random($length);
        } while (ShortenedUrl::where('short_code', $shortCode)->exists());

        return $shortCode;
    }
}
