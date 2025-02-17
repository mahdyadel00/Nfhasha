<?php

namespace App\Http\Requests\API\Provider;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateProfileRequest extends FormRequest
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
        $user = auth('sanctum')->user();
        return [
            'name'                      => 'required|string|max:255',
            'phone'                     => 'required|regex:/^05\d{8}$/|unique:users,phone,'.$user->id,
            'email'                     => 'nullable|email|unique:users,email,'.$user->id,
            'city_id'                   => 'nullable|exists:cities,id',
            'district_id'               => 'nullable|exists:districts,id',
            'type'                      => 'required|in:individual,center',
            'mechanical'                => 'required|boolean',
            'plumber'                   => 'required|boolean',
            'electrical'                => 'required|boolean',
            'puncture'                  => 'required|boolean',
            'battery'                   => 'required|boolean',
            'pickup'                    => 'required|boolean',
            'open_locks'                => 'required|boolean',
            'full_examination'          => 'required|boolean',
            'periodic_examination'      => 'required|boolean',
            'profile_picture'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'address'                   => 'nullable|max:255',
            'truck_barriers'            => 'required|boolean',
            'pick_up_truck_id'          => 'nullable|exists:pick_up_trucks,id',
            'truck_barriers_from'       => 'nullable|date_format:Y-m-d H:i:s',
            'truck_barriers_to'         => 'nullable|date_format:Y-m-d H:i:s',
            'home_service'              => 'required|boolean',
            'commercial_register'       => 'requiredif:type,center|file',
            'municipal_license'         => 'requiredif:type,center|file',
            'owner_identity'            => 'nullable|file',
            'general_license'           => 'nullable|file',
            'longitude'                 => 'nullable|numeric',
            'latitude'                  => 'nullable|numeric',
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
