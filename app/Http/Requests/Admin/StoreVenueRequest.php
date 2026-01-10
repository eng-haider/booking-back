<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
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
        return [
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'venue_type_id' => ['required', 'integer', 'exists:venue_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:venues,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
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
            'status' => ['nullable', 'string', 'in:active,inactive,suspended'],
            'is_featured' => ['nullable', 'boolean'],
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
            'provider_id.required' => 'Provider is required',
            'provider_id.exists' => 'Provider does not exist',
            'venue_type_id.required' => 'Venue type is required',
            'venue_type_id.exists' => 'Venue type does not exist',
            'name.required' => 'Venue name is required',
            'slug.regex' => 'Slug must only contain lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This slug is already taken',
            'address.required' => 'Address is required',
            'city.required' => 'City is required',
            'country.required' => 'Country is required',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180',
            'email.email' => 'Email must be a valid email address',
            'website.url' => 'Website must be a valid URL',
            'capacity.min' => 'Capacity must be at least 1',
            'price_per_hour.min' => 'Price per hour must be at least 0',
            'currency.max' => 'Currency code must be 3 characters',
            'status.in' => 'Status must be active, inactive, or suspended',
            'amenity_ids.*.exists' => 'One or more amenities do not exist',
        ];
    }
}
