<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'    => 'required|string|max:50|unique:customers,code',
            'name'    => 'required|string|max:150',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'  => 'Kode customer wajib diisi.',
            'code.unique'    => 'Kode customer sudah dipakai.',
            'name.required'  => 'Nama customer wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ];
    }
}
