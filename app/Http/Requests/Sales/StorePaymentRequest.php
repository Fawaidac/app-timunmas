<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method' => 'required|in:cash,transfer,giro,other',
            'amount_paid' => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'proof_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Invoice harus dipilih',
            'invoice_id.exists' => 'Invoice tidak ditemukan',
            'payment_method.required' => 'Metode pembayaran harus dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'amount_paid.required' => 'Nominal pembayaran harus diisi',
            'amount_paid.numeric' => 'Nominal pembayaran harus berupa angka',
            'amount_paid.min' => 'Nominal pembayaran minimal 0',
        ];
    }
}
