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
    //     return $this->user()?->role === 'provider' && $this->user()->provider !== null;
    // }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:255', 'unique:venues,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0'],
            'booking_duration_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'schedules' => ['nullable', 'array', 'min:1', 'max:7'],
            'schedules.*.day_of_week' => ['required_with:schedules', 'integer', 'min:0', 'max:6'],
            'schedules.*.open_time' => ['required_unless:schedules.*.is_closed,true', 'date_format:H:i'],
            'schedules.*.close_time' => ['required_unless:schedules.*.is_closed,true', 'date_format:H:i', 'after:schedules.*.open_time'],
            'schedules.*.is_closed' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug must only contain lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This slug is already taken',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category does not exist',
            'base_price.min' => 'Base price must be at least 0',
            'currency.max' => 'Currency code must be 3 characters',
            'buffer_minutes.min' => 'Buffer minutes must be at least 0',
            'booking_duration_hours.min' => 'Booking duration must be at least 1 hour',
            'booking_duration_hours.max' => 'Booking duration cannot exceed 24 hours',
            'amenity_ids.*.exists' => 'One or more amenities do not exist',
            'photos.max' => 'Maximum 10 photos allowed',
            'photos.*.image' => 'File must be an image',
            'photos.*.max' => 'Photo size must not exceed 5MB',
            'schedules.*.day_of_week.required_with' => 'Day of week is required for each schedule',
            'schedules.*.day_of_week.min' => 'Day of week must be between 0 (Sunday) and 6 (Saturday)',
            'schedules.*.day_of_week.max' => 'Day of week must be between 0 (Sunday) and 6 (Saturday)',
            'schedules.*.open_time.required_unless' => 'Open time is required unless the day is marked as closed',
            'schedules.*.close_time.required_unless' => 'Close time is required unless the day is marked as closed',
            'schedules.*.close_time.after' => 'Close time must be after open time',
        ];
    }
}
