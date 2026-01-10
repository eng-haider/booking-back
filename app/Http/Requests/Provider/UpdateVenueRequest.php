<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'owner' && $this->user()->provider !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $venueId = $this->route('id');

        return [
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('venues', 'slug')->ignore($venueId),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ],
            'description' => ['nullable', 'string'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'string', 'in:active,disabled'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'Category does not exist',
            'slug.regex' => 'Slug must only contain lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This slug is already taken',
            'base_price.min' => 'Base price must be at least 0',
            'currency.max' => 'Currency code must be 3 characters',
            'buffer_minutes.min' => 'Buffer minutes must be at least 0',
            'status.in' => 'Status must be active or disabled',
            'amenity_ids.*.exists' => 'One or more amenities do not exist',
        ];
    }
}
