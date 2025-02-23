<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    public function authorize(): bool
    {
        return !auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone'         => ['required','string','regex:/^5\d{8}$/','exists:users,phone'],
            'password'      => 'required|string|min:6',
            'longitude'     => 'required|numeric',
            'latitude'      => 'required|numeric',
            'fcm_token'     => 'nullable|string'
        ];
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
