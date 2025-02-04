<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgetPassword extends FormRequest
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
            'phone'                 => 'required|regex:/^05\d{8}$/|exists:users,phone',
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
            'phone.required'        =>  __('messages.phone_required' , ['attribute' => __('messages.phone')]),
            'phone.regex'           =>  __('messages.phone_invalid' , ['attribute' => __('messages.phone')]),
            'phone.unique'          =>  __('messages.phone_unique' , ['attribute' => __('messages.phone')]),
        ];
    }
}
