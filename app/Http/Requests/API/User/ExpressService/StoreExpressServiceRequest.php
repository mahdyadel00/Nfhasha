<?php

namespace App\Http\Requests\API\User\ExpressService;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpressServiceRequest extends FormRequest
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
            'express_service_id'        => ['required', 'integer', 'exists:express_services,id'],
            'user_vehicle_id'           => ['nullable', 'integer', 'exists:user_vehicles,id'],
            'from_latitude'             => ['required', 'numeric'],
            'from_longitude'            => ['required', 'numeric'],
            'to_latitude'               => ['nullable', 'numeric'],
            'to_longitude'              => ['nullable', 'numeric'],
            'type_battery'              => ['nullable', 'string'],
            'battery_image'             => ['nullable', 'image'],
            'notes'                     => ['nullable', 'string'],
            'amount'                    => ['nullable', 'numeric'],
            'address'                   => ['nullable', 'string'],
            'distanition'               => ['nullable', 'string'],
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
            'express_service_id.required'       => __('validation.required', ['attribute' => 'express service']),
            'express_service_id.integer'        => __('validation.integer', ['attribute' => 'express service']),
            'express_service_id.exists'         => __('validation.exists', ['attribute' => 'express service']),
            'user_vehicle_id.required'          => __('validation.required', ['attribute' => 'user vehicle']),
            'user_vehicle_id.integer'           => __('validation.integer', ['attribute' => 'user vehicle']),
            'user_vehicle_id.exists'            => __('validation.exists', ['attribute' => 'user vehicle']),
            'from_latitude.required'            => __('validation.required', ['attribute' => 'from latitude']),
            'from_latitude.numeric'             => __('validation.numeric', ['attribute' => 'from latitude']),
            'from_longitude.required'           => __('validation.required', ['attribute' => 'from longitude']),
            'from_longitude.numeric'            => __('validation.numeric', ['attribute' => 'from longitude']),
            'to_latitude.numeric'               => __('validation.numeric', ['attribute' => 'to latitude']),
            'to_longitude.numeric'              => __('validation.numeric', ['attribute' => 'to longitude']),
            'type_battery.string'               => __('validation.string', ['attribute' => 'type battery']),
            'battery_image.image'               => __('validation.image', ['attribute' => 'battery image']),
            'notes.string'                      => __('validation.string', ['attribute' => 'notes']),
            'amount.required'                   => __('validation.required', ['attribute' => 'amount']),
            'amount.numeric'                    => __('validation.numeric', ['attribute' => 'amount']),
        ];
    }
}
