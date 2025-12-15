<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $customerId = $this->route('id');

        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^07[0-9]{9}$/',
                Rule::unique('customers', 'phone')->ignore($customerId),
            ],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'preferences' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'This email is already registered',
            'phone.regex' => 'Phone number must be in format: 07XXXXXXXXX (11 digits total starting with 07)',
            'phone.unique' => 'This phone number is already registered',
            'date_of_birth.before' => 'Date of birth must be before today',
            'gender.in' => 'Gender must be male, female, or other',
            'status.in' => 'Status must be active, inactive, or suspended',
        ];
    }
}
