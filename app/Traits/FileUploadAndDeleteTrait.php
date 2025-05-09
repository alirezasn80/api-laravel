<?php

namespace App\Traits;


use Illuminate\Http\Request;

trait FileUploadAndDeleteTrait
{
    public function uploadImage(Request $request, string $inputName, string $path = 'uploads'): ?string
    {
        if (!$request->hasFile($inputName)) {
            return null;
        }

        $image = $request->file($inputName);

        $extension = $image->getClientOriginalExtension();
        $imageName = 'media_' . uniqid('', true) . '.' . $extension;

        $destination = public_path($path);

        // اطمینان از وجود پوشه
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $image->move($destination, $imageName);

        $relativePath = trim($path, '/') . '/' . $imageName;
        return asset($relativePath); // آدرس کامل با base URL
    }

    public function removeImage(string $url): bool
    {
        $baseUrl = asset('');
        $baseUrl = rtrim($baseUrl, '/');


        $relativePath = str_replace($baseUrl, '', $url);
        $relativePath = ltrim($relativePath, '/');

        $fullPath = public_path($relativePath);

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }


}
