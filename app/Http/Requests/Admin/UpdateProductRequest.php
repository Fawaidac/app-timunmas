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
            'name'           => 'required|string|max:50',
            'kd_jns_brg'     => 'nullable|string|max:50',
            'kd_suppl'       => 'nullable|string|max:9',
            'price'          => 'required|numeric|min:0',
            'harga_bl'       => 'nullable|numeric|min:0',
            'harga_jl2'      => 'nullable|numeric|min:0',
            'harga_jl3'      => 'nullable|numeric|min:0',
            'unit'           => 'required|string|max:5',
            'satuan2'        => 'nullable|string|max:5',
            'kapasitas2'     => 'nullable|numeric|min:0',
            'satuan3'        => 'nullable|string|max:5',
            'kapasitas3'     => 'nullable|numeric|min:0',
            'stok_min'       => 'nullable|numeric|min:0',
            'rak'            => 'nullable|string|max:20',
            'berat'          => 'nullable|numeric|min:0',
            'sts_aktif'      => 'nullable|string|max:8',
            'warehouse_id'   => 'nullable|string|max:50',
            'stock_quantity' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama barang wajib diisi.',
            'name.max'       => 'Nama barang maksimal 50 karakter.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.numeric'  => 'Harga jual harus berupa angka.',
            'unit.required'  => 'Satuan utama wajib diisi.',
        ];
    }
}
