<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
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
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'venue_id' => ['sometimes', 'integer', 'exists:venues,id'],
            'resource_id' => ['sometimes', 'integer', 'exists:resources,id'],
            'booking_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i:s'],
            'end_time' => ['sometimes', 'date_format:H:i:s', 'after:start_time'],
            'total_price' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:pending,confirmed,completed,cancelled'],
            'notes' => ['nullable', 'string'],
            'special_requests' => ['nullable', 'string'],
            'cancellation_reason' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Customer does not exist',
            'user_id.exists' => 'User does not exist',
            'venue_id.exists' => 'Venue does not exist',
            'resource_id.exists' => 'Resource does not exist',
            'start_time.date_format' => 'Start time must be in HH:MM:SS format',
            'end_time.date_format' => 'End time must be in HH:MM:SS format',
            'end_time.after' => 'End time must be after start time',
            'total_price.min' => 'Total price must be at least 0',
            'status.in' => 'Status must be pending, confirmed, completed, or cancelled',
        ];
    }
}
