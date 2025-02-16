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
            'service_id'                    => ['required', 'exists:express_services,id'],
            'vehicle_id'                    => ['required', 'exists:user_vehicles,id'],
            'city_id'                       => ['nullable', 'exists:cities,id'],
            'cy_periodic_id'                => ['nullable', 'exists:cy_periodics,id'],
            'pick_up_truck_id'              => ['nullable', 'exists:pick_up_trucks,id'],
            'from_lat'                      => ['nullable', 'numeric'],
            'from_long'                     => ['nullable', 'numeric'],
            'position'                      => ['nullable', 'string'],
            'inspection_side'               => ['nullable', 'in:all,front,back,sides,left'],
            'date'                          => ['nullable', 'date'],
            'time'                          => ['nullable', 'string'],
            //maintenance
            'maintenance_type'              => ['nullable', 'string'],
            'maintenance_description'       => ['nullable', 'string'],
            'address'                       => ['nullable', 'string'],
            'latitude'                      => ['nullable', 'string'],
            'longitude'                     => ['nullable', 'string'],
            'note'                          => ['nullable', 'string'],
            'image'
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
