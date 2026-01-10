<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
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
        $providerId = $this->route('id');

        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('providers', 'slug')->ignore($providerId),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ],
            'description' => ['nullable', 'string'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'regex:/^07[0-9]{9}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'settings' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'User does not exist',
            'email.email' => 'Email must be a valid email address',
            'phone.regex' => 'Phone number must be in format: 07XXXXXXXXX (11 digits total starting with 07)',
            'slug.regex' => 'Slug must only contain lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This slug is already taken',
            'website.url' => 'Website must be a valid URL',
            'status.in' => 'Status must be active, inactive, or suspended',
        ];
    }
}
