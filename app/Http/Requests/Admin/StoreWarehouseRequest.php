<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'      => 'required|string|max:50|unique:warehouses,code',
            'name'      => 'required|string|max:100',
            'address'   => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode gudang wajib diisi.',
            'code.unique'   => 'Kode gudang sudah dipakai.',
            'name.required' => 'Nama gudang wajib diisi.',
        ];
    }
}
