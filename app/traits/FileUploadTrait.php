<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait FileUploadTrait {

    function uploadFile(Request $request, string $inputName, ?string $oldPath = null, string $path = '/uploads' ) {
        if($request->hasFile($inputName)) {
            $file = $request->{$inputName};
            $ext = $file->getClientOriginalExtension();
            $fileName = 'media_'.uniqid().'.'.$ext;

            $file->move(public_path($path), $fileName);

            return $path.'/'.$fileName;
        }

        return null;
    }
/*
    function uploadVoice(Request $request, string $inputName, ?string $oldPath = null, string $path = '/uploads/voice-messages' ) {
        if($request->hasFile($inputName)) {
            $file = $request->{$inputName};
            $ext = $file->getClientOriginalExtension();
            $fileName = 'voice_'.uniqid().'.'.$ext;

            $file->move(public_path($path), $fileName);

            return $path.'/'.$fileName;
        }

        return null;
    }
        */
        function uploadVoice(Request $request, string $inputName, ?string $oldPath = null, string $path = 'uploads/voice-messages'): ?string
        {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);

                // Get file extension or default to 'mp3'
                $ext = $file->getClientOriginalExtension() ?: 'ogg';
                $fileName = 'voice_' . uniqid() . '.' . $ext;

                // Ensure the destination directory exists
                $destinationPath = public_path($path);
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Safely delete the old file if it exists
                if ($oldPath) {
                    $fullOldPath = public_path($oldPath);
                    if (file_exists($fullOldPath) && is_file($fullOldPath)) {
                        unlink($fullOldPath);
                    }
                }

                try {
                    // Move the new file to the destination directory
                    $file->move($destinationPath, $fileName);
                } catch (\Exception $e) {
                    logger('File upload error: ' . $e->getMessage());
                    return null;
                }

                return $path . '/' . $fileName;
            }

            return null; // No file was uploaded
        }


}
