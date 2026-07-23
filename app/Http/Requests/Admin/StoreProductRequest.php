<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku'            => 'required|string|max:50|unique:products,sku',
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
            'sku.required'            => 'Kode SKU wajib diisi.',
            'sku.unique'              => 'Kode SKU sudah dipakai, gunakan kode lain.',
            'name.required'           => 'Nama barang wajib diisi.',
            'price.required'          => 'Harga wajib diisi.',
            'price.numeric'           => 'Harga harus berupa angka.',
            'unit.required'           => 'Satuan wajib diisi.',
            'warehouse_id.required'   => 'Gudang wajib dipilih.',
            'warehouse_id.exists'     => 'Gudang yang dipilih tidak valid.',
            'stock_quantity.required' => 'Stok awal wajib diisi.',
            'stock_quantity.integer'  => 'Stok harus berupa angka bulat.',
        ];
    }
}
