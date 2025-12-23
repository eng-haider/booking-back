<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
        return [
            'venue_id' => 'required|exists:venues,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'number_of_guests' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'special_requests' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'venue_id.required' => 'Venue is required',
            'venue_id.exists' => 'Selected venue does not exist',
            'booking_date.required' => 'Booking date is required',
            'booking_date.after_or_equal' => 'Booking date must be today or a future date',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in H:i format (e.g., 14:30)',
            'number_of_guests.min' => 'Number of guests must be at least 1',
        ];
    }
}
