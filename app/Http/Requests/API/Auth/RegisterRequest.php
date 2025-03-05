<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterRequest extends FormRequest
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
        $rules = [
            'name'                  => 'required|string|max:255',
            'phone'                 => ['required','string','regex:/^5\d{8}$/','unique:users,phone'],
            'password'              => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'invitation_code'       => 'nullable|exists:users,invitation_code',
            'longitude'             => 'required|numeric',
            'latitude'              => 'required|numeric',
            'address'               => 'required|max:255',
            // 'fcm_token'             => 'nullable|string'
        ];


        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     */
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
