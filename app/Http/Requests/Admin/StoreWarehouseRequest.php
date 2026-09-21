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
            'name'      => 'required|string|max:50|unique:GUDANG,NM_GUDANG',
            'code'      => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama gudang wajib diisi.',
            'name.unique'   => 'Nama / kode gudang sudah terdaftar.',
            'name.max'      => 'Nama gudang maksimal 50 karakter.',
        ];
    }
}
