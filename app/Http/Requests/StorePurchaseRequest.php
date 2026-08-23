<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => 'required|string|unique:purchases,invoice_number',
            'order_date' => 'required|date',
            'suppliers' => 'required|exists:suppliers,id',
            'grand_total' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'delivery_cost' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',

            'producst' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:product,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.purchase_price' => 'required|numeric|min:0',
            'products.*.expire_days' => 'required|date',
            'products.*.total_price' => 'required|numeric|min:0',
        ];
    }
}
