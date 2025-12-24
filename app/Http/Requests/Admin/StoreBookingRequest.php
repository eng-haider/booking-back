<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'resource_id' => ['nullable', 'integer', 'exists:resources,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i:s'],
            // end_time is now calculated automatically based on venue's booking_duration_hours
            'number_of_guests' => ['nullable', 'integer', 'min:1'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'in:pending,confirmed,completed,cancelled'],
            'notes' => ['nullable', 'string'],
            'special_requests' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required',
            'customer_id.exists' => 'Customer does not exist',
            'user_id.exists' => 'User does not exist',
            'venue_id.required' => 'Venue is required',
            'venue_id.exists' => 'Venue does not exist',
            'resource_id.exists' => 'Resource does not exist',
            'booking_date.required' => 'Booking date is required',
            'booking_date.after_or_equal' => 'Booking date must be today or in the future',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM:SS format',
            'number_of_guests.min' => 'Number of guests must be at least 1',
            'total_price.min' => 'Total price must be at least 0',
            'status.in' => 'Status must be pending, confirmed, completed, or cancelled',
        ];
    }
}
