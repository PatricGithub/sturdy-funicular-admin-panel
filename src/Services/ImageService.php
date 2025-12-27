<?php

namespace WebWizr\AdminPanel\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    protected int $maxWidth = 1920;
    protected int $quality = 80;

    /**
     * Compress and store an uploaded image
     */
    public function compressAndStore(UploadedFile $file, string $directory = 'blog'): string
    {
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.jpg';
        $path = $directory . '/' . $filename;

        // Read the image using Intervention Image
        $image = Image::read($file);

        // Resize if larger than max width (maintaining aspect ratio)
        if ($image->width() > $this->maxWidth) {
            $image->scale(width: $this->maxWidth);
        }

        // Encode with quality setting
        $encoded = $image->toJpeg($this->quality);

        // Store the compressed image
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Compress an existing image file
     */
    public function compress(string $path): bool
    {
        $fullPath = Storage::disk('public')->path($path);

        if (!file_exists($fullPath)) {
            return false;
        }

        try {
            $image = Image::read($fullPath);

            // Resize if larger than max width
            if ($image->width() > $this->maxWidth) {
                $image->scale(width: $this->maxWidth);
            }

            // Save with compression
            $image->toJpeg($this->quality)->save($fullPath);

            return true;
        } catch (\Exception $e) {
            \Log::error('Image compression failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set the maximum width
     */
    public function setMaxWidth(int $width): self
    {
        $this->maxWidth = $width;
        return $this;
    }

    /**
     * Set the quality (1-100)
     */
    public function setQuality(int $quality): self
    {
        $this->quality = max(1, min(100, $quality));
        return $this;
    }

    /**
     * Delete an image
     */
    public function delete(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }
}
