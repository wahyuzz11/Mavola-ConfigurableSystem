<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_number' => ['required','string','unique:purchase,invoice_number'],
            'order_date' => ['required','date'],
            'suppliers' => ['required','exists:suppliers,id.'],
            'grand_total' => ['required','numeric','min:0'],
            'payment_method' => ['required', Rule::in(['P-PAY-01','P-PAY-02','P-PAY-03'])],
            'delivery_cost' => ['nullable','numeric','min:0'],

            'due_date' => ['required_if:payment_method,P-PAY-03','nullable','date','after_or_equal:order_date'],

            'products' => ['required','array','min:1'],
            'producst.*.product_id' => ['required','exists:products,id'],
            'products.*.quantity' => ['required','integer','min:1'],
            'products.*.purchase_price' => ['required','numeric','min:0'],
            'products.*.expire_days' => ['nullable','integer','min:0'],
            'products.*.total_price' => ['required','numeric','min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.required_if' => 'Tanggal jatuh tempo wajib diisi untuk pembayaran hutang.',
        ];
    }   
}
