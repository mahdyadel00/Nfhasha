<?php

namespace App\Http\Requests\API\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreperiodicExaminationRequest extends FormRequest
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
            'vehicle_id'            => ['required', 'exists:user_vehicles,id'],
            'city_id'               => ['required', 'exists:cities,id'],
            'cy_periodic_id'        => ['required', 'exists:cy_periodics,id'],
            'pick_up_truck_id'      => ['required', 'exists:pick_up_trucks,id'],
            'from_lat'              => ['required', 'numeric'],
            'from_long'             => ['required', 'numeric'],
            'position'              => ['required', 'string'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $translatedErrors = collect($validator->errors()->toArray())
            ->mapWithKeys(function ($messages, $field) {
                return [
                    $field => collect($messages)
                        ->map(function ($message) {
                            return str_replace('today', 'اليوم', __($message));
                        })
                        ->toArray()
                ];
            })
            ->toArray();

        throw new HttpResponseException(
            apiResponse(422, __('validation.errors'), $translatedErrors)
        );
    }
}
