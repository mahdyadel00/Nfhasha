<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPassword extends FormRequest
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
            'phone'         => 'required|regex:/^05\d{8}$/|exists:users,phone',
            'password'      => 'required|min:8|confirmed',
            'otp'           => 'required|numeric',
        ];
    }


    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.exists'          => __('messages.user_not_found' , ['attribute' => 'phone']),
            'phone.regex'           => __('messages.invalid_phone' , ['attribute' => 'phone']),
            'password.min'          => __('messages.password_min' , ['attribute' => 'password']),
            'password.confirmed'    => __('messages.password_confirmed' , ['attribute' => 'password']),
            'otp.required'          => __('messages.otp_required'),
            'otp.numeric'           => __('messages.otp_numeric'),
        ];
    }
}
