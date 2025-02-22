<?php

namespace App\Http\Requests\API\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'letters_ar'                        => ['nullable', 'string', 'size:3', 'regex:/^[\p{Arabic}]{3}$/u'],
            'letters_en'                        => ['nullable', 'string', 'size:3', 'regex:/^[a-zA-Z]{3}$/'],
            'numbers_ar'                        => ['nullable', 'string', 'size:4', 'regex:/^[٠-٩]{4}$/u'],
            'numbers_en'                        => ['nullable', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
            'vehicle_type_id'                   => ['nullable', 'string', 'exists:vehicle_types,id'],
            'vehicle_model_id'                  => ['nullable', 'string', 'exists:vehicle_models,id'],
            'vehicle_manufacture_year_id'       => ['nullable', 'string', 'exists:vehicle_manufacture_years,id'],
            'vehicle_brand_id'                  => ['nullable', 'string', 'exists:vehicle_brands,id'],
            'checkup_date'                      => ['nullable', 'date', 'after_or_equal:today'],
            'images'                            => ['nullable', 'array' , 'min:1', 'max:5'],
            'images.*'                          => ['file', 'mimes:jpeg,png,jpg,gif,svg'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        $translatedErrors = [];
        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $translatedErrors[$field][] = str_replace('today', 'اليوم', __($message));
            }
        }

        throw new HttpResponseException(apiResponse(
            422,
            __('validation.errors'),
            $translatedErrors
        ));
    }
}
