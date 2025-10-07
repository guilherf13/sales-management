<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
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
            'seller_id' => 'sometimes|required|exists:sellers,id',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'sale_date' => 'sometimes|required|date|before_or_equal:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'seller_id.required' => 'The seller field is required.',
            'seller_id.exists' => 'The selected seller does not exist.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'sale_date.required' => 'The sale date field is required.',
            'sale_date.date' => 'The sale date must be a valid date.',
            'sale_date.before_or_equal' => 'The sale date must be today or earlier.',
        ];
    }
}
