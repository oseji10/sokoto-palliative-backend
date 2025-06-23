<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
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
            'amount' => 'required|numeric',
            'payment_method' => 'sometimes|string|in:CARD_PURCHASE,POS_TRANSFER,ANY',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'The amount is required',
            'amount.numeric' => 'The amount must be a valid number',
            'payment_method.in' => 'The selected payment method is invalid',
        ];
    }
}
