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
            'customer_id'       => 'required|exists:CUSTOMER,KD_CUST',
            'order_date'        => 'required|date',
            'product_id'        => 'required|array|min:1',
            'product_id.*'      => 'required|exists:BARANG,KD_BRG',
            'unit'              => 'nullable|array',
            'unit.*'            => 'nullable|string|max:10',
            'sat_ke'            => 'nullable|array',
            'sat_ke.*'          => 'nullable|integer|min:1|max:4',
            'kapasitas'         => 'nullable|array',
            'kapasitas.*'       => 'nullable|numeric|min:0.01',
            'quantity'          => 'required|array|min:1',
            'quantity.*'        => 'required|numeric|min:0.01',
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
