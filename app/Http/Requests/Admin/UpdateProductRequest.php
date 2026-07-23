<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:150',
            'category'       => 'nullable|string|max:100',
            'price'          => 'required|numeric|min:0',
            'unit'           => 'required|string|max:20',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'stock_quantity' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Nama barang wajib diisi.',
            'price.required'          => 'Harga wajib diisi.',
            'price.numeric'           => 'Harga harus berupa angka.',
            'unit.required'           => 'Satuan wajib diisi.',
            'warehouse_id.required'   => 'Gudang wajib dipilih.',
            'warehouse_id.exists'     => 'Gudang yang dipilih tidak valid.',
            'stock_quantity.required' => 'Stok wajib diisi.',
            'stock_quantity.integer'  => 'Stok harus berupa angka bulat.',
        ];
    }
}
