<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'       => 'required|exists:customers,id',
            'order_date'        => 'required|date',
            'payment_type'      => 'required|in:cash,credit',
            'payment_term_days' => 'required_if:payment_type,credit|integer|min:1',
            'product_id'        => 'required|array|min:1',
            'product_id.*'      => 'required|exists:products,id',
            'quantity'          => 'required|array|min:1',
            'quantity.*'        => 'required|integer|min:1',
            'price'             => 'required|array|min:1',
            'price.*'           => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'       => 'Customer wajib dipilih.',
            'customer_id.exists'         => 'Customer tidak valid.',
            'order_date.required'        => 'Tanggal order wajib diisi.',
            'payment_type.required'      => 'Jenis pembayaran wajib dipilih.',
            'payment_type.in'            => 'Jenis pembayaran tidak valid.',
            'payment_term_days.required_if' => 'Tempo pembayaran wajib diisi untuk kredit.',
            'payment_term_days.integer'  => 'Tempo pembayaran harus berupa angka.',
            'product_id.required'        => 'Minimal harus ada 1 item produk.',
            'product_id.array'           => 'Format produk tidak valid.',
            'product_id.*.exists'        => 'Produk tidak valid.',
            'quantity.required'          => 'Qty wajib diisi.',
            'quantity.*.integer'         => 'Qty harus berupa angka.',
            'quantity.*.min'             => 'Qty minimal 1.',
            'price.required'             => 'Harga wajib diisi.',
            'price.*.numeric'            => 'Harga harus berupa angka.',
        ];
    }
}
