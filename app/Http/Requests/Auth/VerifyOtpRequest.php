<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^07[0-9]{9}$/'],
            'code' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must be in format: 07XXXXXXXXX (11 digits total starting with 07)',
        ];
    }
}
