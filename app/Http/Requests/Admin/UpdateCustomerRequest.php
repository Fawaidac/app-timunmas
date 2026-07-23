<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:150',
            'address'   => 'nullable|string',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:100',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama customer wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
        ];
    }
}
