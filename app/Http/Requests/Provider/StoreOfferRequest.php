<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'venue_id' => 'required|exists:venues,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('discount_type') === 'percentage' && $value > 100) {
                        $fail('The discount value cannot exceed 100% for percentage discounts.');
                    }
                },
            ],
            'min_booking_hours' => 'nullable|integer|min:1',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'terms_and_conditions' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'venue_id.required' => 'The venue field is required.',
            'venue_id.exists' => 'The selected venue does not exist.',
            'title.required' => 'The offer title is required.',
            'discount_type.required' => 'Please specify the discount type.',
            'discount_type.in' => 'The discount type must be either percentage or fixed.',
            'discount_value.required' => 'The discount value is required.',
            'discount_value.min' => 'The discount value must be at least 0.',
            'start_date.required' => 'The start date is required.',
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'end_date.required' => 'The end date is required.',
            'end_date.after' => 'The end date must be after the start date.',
        ];
    }
}
