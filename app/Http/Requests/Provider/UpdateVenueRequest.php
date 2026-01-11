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
     return true;
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


            // Schedule validation
            'schedules' => ['nullable', 'array'],
            'schedules.*.id' => ['nullable', 'integer', 'exists:schedules,id'],
            'schedules.*.day_of_week' => ['required_with:schedules', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['required_with:schedules', 'date_format:H:i'],
            'schedules.*.end_time' => ['required_with:schedules', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.is_available' => ['nullable', 'boolean'],
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
            
            // Schedule messages
            'schedules.*.day_of_week.between' => 'Day of week must be between 0 (Sunday) and 6 (Saturday)',
            'schedules.*.start_time.date_format' => 'Start time must be in HH:MM format',
            'schedules.*.end_time.date_format' => 'End time must be in HH:MM format',
            'schedules.*.end_time.after' => 'End time must be after start time',
        ];
    }
}
