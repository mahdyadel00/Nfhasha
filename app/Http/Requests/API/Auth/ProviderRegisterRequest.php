<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProviderRegisterRequest extends FormRequest
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
            'name'                              => 'required|string|max:255',
            'phone'                             => ['required','string','regex:/^5\d{8}$/','unique:users,phone'],
            'email'                             => 'required|email|unique:users,email',
            'password'                          => ['required'],
            'city_id'                           => 'required|exists:cities,id',
            'district_id'                       => 'required|exists:districts,id',
            'location'                          => 'required|string|max:255',
            'type'                              => 'required|in:center,individual',
            'mechanical'                        => 'required|boolean',
            'plumber'                           => 'required|boolean',
            'electrical'                        => 'required|boolean',
            'puncture'                          => 'required|boolean',
            'tow_truck'                         => 'required|boolean',
            'battery'                           => 'required|boolean',
            'fuel'                              => 'required|boolean',
            'pickup'                            => 'required|boolean',
            'open_locks'                        => 'required|boolean',
            'periodic_inspections'              => 'required|boolean',
            'comprehensive_inspections'         => 'required|boolean',
            'maintenance'                       => 'required|boolean',
            'car_reservations'                  => 'required|boolean',
            'wenchId'                           => 'nullable|exists:pick_up_trucks,id',
            'truck_barriers_from'               => 'nullable|date_format:Y-m-d H:i:s',
            'truck_barriers_to'                 => 'nullable|date_format:Y-m-d H:i:s',
            'home_service'                      => 'required|boolean',
            'commercial_register'               => 'requiredif:type,center|file',
            'owner_identity'                    => 'required|file',
            'general_license'                   => 'required|file',
            'municipal_license'                 => 'requiredif:type,center|file',
            'longitude'                         => 'required|numeric',
            'latitude'                          => 'required|numeric',
            // 'fcm_token'                      => 'nullable|string'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        $translatedErrors = [];
        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $translatedErrors[$field][] = __($message);
            }
        }

        throw new HttpResponseException(apiResponse(
            422,
            __('validation.errors'),
            $translatedErrors
        ));
    }

}
