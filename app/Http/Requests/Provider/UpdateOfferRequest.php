<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferRequest extends FormRequest
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
            'venue_id' => 'sometimes|exists:venues,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => [
                'sometimes',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $discountType = $this->input('discount_type') ?? $this->route('offer')?->discount_type;
                    if ($discountType === 'percentage' && $value > 100) {
                        $fail('The discount value cannot exceed 100% for percentage discounts.');
                    }
                },
            ],
            'min_booking_hours' => 'nullable|integer|min:1',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'sometimes|date',
            'end_date' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) {
                    $startDate = $this->input('start_date') ?? $this->route('offer')?->start_date;
                    if ($startDate && $value <= $startDate) {
                        $fail('The end date must be after the start date.');
                    }
                },
            ],
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
            'venue_id.exists' => 'The selected venue does not exist.',
            'discount_type.in' => 'The discount type must be either percentage or fixed.',
            'discount_value.min' => 'The discount value must be at least 0.',
        ];
    }
}
