<?php

namespace App\Http\Requests\API\Provider;

use Illuminate\Foundation\Http\FormRequest;

class NearbyProvidersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // يمكنك تعديل هذا حسب متطلبات الأذونات
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'], // خط العرض بين -90 و90
            'longitude' => ['required', 'numeric', 'between:-180,180'], // خط الطول بين -180 و180
            'distance' => ['nullable', 'numeric', 'min:1', 'max:1000'], // المسافة بين 1 و1000 كم
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'latitude.required' => __('messages.latitude_required'),
            'latitude.numeric' => __('messages.latitude_numeric'),
            'latitude.between' => __('messages.latitude_between'),
            'longitude.required' => __('messages.longitude_required'),
            'longitude.numeric' => __('messages.longitude_numeric'),
            'longitude.between' => __('messages.longitude_between'),
            'distance.numeric' => __('messages.distance_numeric'),
            'distance.min' => __('messages.distance_min'),
            'distance.max' => __('messages.distance_max'),
        ];
    }
}