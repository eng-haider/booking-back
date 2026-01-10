<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'super_admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $venueId = $this->route('id');

        return [
            'provider_id' => ['sometimes', 'integer', 'exists:providers,id'],
            'venue_type_id' => ['sometimes', 'integer', 'exists:venue_types,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('venues', 'slug')->ignore($venueId),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ],
            'description' => ['nullable', 'string'],
            'address' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'price_per_hour' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'opening_hours' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'is_featured' => ['nullable', 'boolean'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
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
            'provider_id.exists' => 'Provider does not exist',
            'venue_type_id.exists' => 'Venue type does not exist',
            'slug.regex' => 'Slug must only contain lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This slug is already taken',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180',
            'email.email' => 'Email must be a valid email address',
            'website.url' => 'Website must be a valid URL',
            'capacity.min' => 'Capacity must be at least 1',
            'price_per_hour.min' => 'Price per hour must be at least 0',
            'currency.max' => 'Currency code must be 3 characters',
            'status.in' => 'Status must be active, inactive, or suspended',
            'rating.between' => 'Rating must be between 0 and 5',
            'amenity_ids.*.exists' => 'One or more amenities do not exist',
        ];
    }
}
