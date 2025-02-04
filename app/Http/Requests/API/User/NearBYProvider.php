<?php

namespace App\Http\Requests\API\User;

use Illuminate\Foundation\Http\FormRequest;

class NearBYProvider extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude'      => ['required', 'numeric'],
            'longitude'     => ['required', 'numeric'],
        ];
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required'     => 'Latitude is required.',
            'latitude.numeric'      => 'Latitude must be a number.',
            'longitude.required'    => 'Longitude is required.',
            'longitude.numeric'     => 'Longitude must be a number.',
        ];
    }
}
