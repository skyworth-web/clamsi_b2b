<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;


class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        // Get the media collection name
        $collectionName = $media->collection_name;

        // Your custom logic to generate the path
        $customPath = $collectionName . '/';

        return $customPath;
    }

    public function removeFile(string $path, string $disk): void
    {
        // Delete the file from the specified disk at the given path
        try {
            // Delete the file from the specified disk at the given path
            Storage::disk($disk)->delete($path);
            echo 'File removed successfully.';
        } catch (\Exception $e) {
            echo 'Failed to remove file: ' . $e->getMessage();
        }
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . '/conversions';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . '/responsive';
    }
}
