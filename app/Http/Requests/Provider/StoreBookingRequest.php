<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return $this->user()?->role === 'owner' || $this->user()?->role === 'provider';
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i:s'],
            // end_time is calculated automatically based on venue's booking_duration_hours
            'number_of_guests' => ['nullable', 'integer', 'min:1'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
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
            'customer_id.exists' => 'Customer does not exist',
            'venue_id.required' => 'Venue is required',
            'venue_id.exists' => 'Venue does not exist',
            'booking_date.required' => 'Booking date is required',
            'booking_date.after_or_equal' => 'Booking date must be today or in the future',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM:SS format',
            'number_of_guests.min' => 'Number of guests must be at least 1',
            'total_price.min' => 'Total price must be at least 0',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify that the venue belongs to the authenticated provider
            $user = $this->user();
            $venueId = $this->input('venue_id');
            
            if ($user && $user->provider && $venueId) {
                $venue = \App\Models\Venue::where('id', $venueId)
                    ->where('provider_id', $user->provider->id)
                    ->first();
                
                if (!$venue) {
                    $validator->errors()->add('venue_id', 'This venue does not belong to you.');
                }
            }
        });
    }
}
