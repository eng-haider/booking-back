<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return $this->user()?->role === 'owner' && $this->user()->provider !== null;
    // }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:venues,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Venue name is required',
            'slug.regex' => 'Slug must only contain lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This slug is already taken',
            'category_id.exists' => 'Selected category does not exist',
            'base_price.min' => 'Base price must be at least 0',
            'currency.max' => 'Currency code must be 3 characters',
            'buffer_minutes.min' => 'Buffer minutes must be at least 0',
            'amenity_ids.*.exists' => 'One or more amenities do not exist',
            'photos.max' => 'Maximum 10 photos allowed',
            'photos.*.image' => 'File must be an image',
            'photos.*.max' => 'Photo size must not exceed 5MB',
        ];
    }
}
