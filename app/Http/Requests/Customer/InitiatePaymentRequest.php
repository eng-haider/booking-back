<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
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
            'return_url' => 'sometimes|url|max:500',
            'cancel_url' => 'sometimes|url|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'return_url.url' => 'The return URL must be a valid URL.',
            'return_url.max' => 'The return URL must not exceed 500 characters.',
            'cancel_url.url' => 'The cancel URL must be a valid URL.',
            'cancel_url.max' => 'The cancel URL must not exceed 500 characters.',
        ];
    }
}
