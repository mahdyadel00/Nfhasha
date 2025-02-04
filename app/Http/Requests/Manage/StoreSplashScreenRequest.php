<?php

namespace App\Http\Requests\Manage;

use Illuminate\Foundation\Http\FormRequest;

class StoreSplashScreenRequest extends FormRequest
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
            'image' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'en' => ['required', 'array'],
            'en.title' => ['required', 'string', 'max:255'],
            'en.description' => ['required', 'string', 'max:255'],
            'ar' => ['required', 'array'],
            'ar.title' => ['required', 'string', 'max:255'],
            'ar.description' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
