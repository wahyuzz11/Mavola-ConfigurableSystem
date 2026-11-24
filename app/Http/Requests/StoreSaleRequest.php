<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
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
            'invoice_number' => ['required','string','unique:invoice_number'],
            'sale_date' => ['required','date'],
            'payment_method' => ['required', Rule::in(['S-PAY-01','S-PAY-02','S-PAY-03'])],
            'cogs_method' => ['required','string'],
            'global_discount' => ['nullable','numeric','min:0'],
            'total_discount' => ['nullable','numeric','min:0'],
            'discount_cashback' => ['nullable','numeric','min:0'],
            'delivery_cost' => ['nullable','numeric','min:0'], 


            'products' => ['required','array','min:1'],
            'producst.*.product_id' => ['required','exists:products,id'],
            'products.*.quantity' => ['required','integer','min:1'],
            'products.*.price' => ['required','numeric','min:0'],
            'products.*.subtotal' => ['required','numeric','min:0'],
            'producst.*.cogs_sale' => ['required','numeric','min:0'],
        ];

            
    }



}
