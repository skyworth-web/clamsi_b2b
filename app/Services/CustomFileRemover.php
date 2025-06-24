<?php

namespace App\Services;

use Spatie\MediaLibrary\Support\FileRemover\FileRemover as BaseFileRemover;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Spatie\MediaLibrary\MediaCollections\Filesystem as MediaFilesystem;

class CustomFileRemover implements BaseFileRemover
{
    protected MediaFilesystem $mediaFileSystem;
    protected FilesystemFactory $filesystem;

    public function __construct(MediaFilesystem $mediaFileSystem, FilesystemFactory $filesystem)
    {
        $this->mediaFileSystem = $mediaFileSystem;
        $this->filesystem = $filesystem;
    }

    public function removeAllFiles(Media $media): void
    {
        // Implement logic to remove all files relating to the provided media model
    }

    public function removeResponsiveImages(Media $media, string $conversionName): void
    {
        // Implement logic to remove responsive images relating to the provided media model and conversion
    }

    public function removeFile(string $path, string $disk): void
    {
       
         // Delete the file from the specified disk at the given path
         try {
            // Delete the file from the specified disk at the given path
            $this->filesystem->disk($disk)->delete($path);
        } catch (\Exception $e) {
            echo 'Failed to remove file: ' . $e->getMessage();
        }
    }
}
