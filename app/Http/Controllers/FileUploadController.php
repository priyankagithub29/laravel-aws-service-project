<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{
    /**
     * Securely upload a file to S3.
     */
    public function upload(Request $request)
    {
        // 1. Validate the incoming request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // 2. Get the file from request
            $file = $request->file('file');
            // 3. Generate a unique file name
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'uploads/' . $fileName;

            // --- TEMPORARY LOCAL SECURITY & LOGIC TEST ---
            // Comment out the S3 call for just one second to see if your route works!
            
            Storage::disk('s3')->put(
                $filePath, 
                fopen($file->getRealPath(), 'r+'), 
                'private'
            );
            

            // Return a clean JSON response instead of using dd()
            return response()->json([
                'message' => 'Local logic test passed! File intercepted successfully.',
                'mock_file_path' => $filePath,
                'client_original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a temporary secure download link for a private S3 file.
     */
    public function getDownloadUrl(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string'
        ]);

        $filePath = $request->file_path;

        // 1. Check if the file actually exists on S3
        if (!Storage::disk('s3')->exists($filePath)) {
            return response()->json(['error' => 'File not found on S3 storage.'], 404);
        }

        // 2. Security Best Practice: Create a temporary signed URL that expires in 15 minutes
        $temporaryUrl = Storage::disk('s3')->temporaryUrl(
            $filePath, 
            now()->addMinutes(15)
        );

        return response()->json([
            'download_url' => $temporaryUrl,
            'expires_at' => now()->addMinutes(15)->toIso8601String()
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}