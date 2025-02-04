<?php

if (!function_exists('translate')) {

    function translate($key, $replace = [], $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return __($key, $replace, $locale);
    }
}

if (!function_exists('apiResponse')) {
    /**
     * Return a standardized API response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    function apiResponse($status, $message = '' , $data = null)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }
}

if (!function_exists('uploadImage')) {
    /**
     * Upload an image to the specified path.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $path
     * @param string $disk
     * @return string
     */
    function uploadImage($image, $path, $disk = 'public')
    {
        $filename = (string) Str::uuid() . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs($path, $filename, $disk);

        return $imagePath;
    }
}

if (!function_exists('deleteImage')) {
    /**
     * Delete an image from the specified path.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    function deleteImage($path, $disk = 'public')
    {
        return Storage::disk($disk)->delete($path);
    }
}
