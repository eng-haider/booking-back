<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class RegisterProviderRequest extends FormRequest
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
            // User Information
            'phone' => ['required', 'string', 'unique:users,phone', 'regex:/^07[0-9]{9}$/'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            
            // Provider Information
            'provider_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'governorate_id' => ['required', 'integer', 'exists:governorates,id'],
            'address' => ['required', 'string', 'max:500'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'license_number' => ['nullable', 'string', 'max:100', 'unique:providers,license_number'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must be in format: 07XXXXXXXXX (11 digits total starting with 07)',
            'phone.unique' => 'This phone number is already registered',
            'email.unique' => 'This email is already registered',
            'governorate_id.required' => 'Governorate is required',
            'governorate_id.exists' => 'Selected governorate does not exist',
            'license_number.unique' => 'This license number is already registered',
        ];
    }
}
