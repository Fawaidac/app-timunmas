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
            'nm_cust'   => 'required|string|max:50',
            'c_person'  => 'nullable|string|max:20',
            'alm_cust'  => 'nullable|string|max:65',
            'telp1'     => 'nullable|string|max:14',
            'telp2'     => 'nullable|string|max:14',
            'hp'        => 'nullable|string|max:14',
            'fax'       => 'nullable|string|max:14',
            'e_mail'    => 'nullable|email|max:50',
            'web_site'  => 'nullable|string|max:50',
            'kd_kat'    => 'nullable|string|max:2',
            'kd_wil'    => 'nullable|string|max:3',
            'kd_peg'    => 'nullable|string|max:9',
            'krd_limit' => 'nullable|numeric|min:0',
            'top_limit' => 'nullable|integer|min:0|max:999',
            'npwp'      => 'nullable|string|max:27',
            'nm_pkp'    => 'nullable|string|max:65',
            'alm_pkp'   => 'nullable|string|max:65',
            'bank1'     => 'nullable|string|max:50',
            'no_rek1'   => 'nullable|string|max:14',
            'bank2'     => 'nullable|string|max:50',
            'no_rek2'   => 'nullable|string|max:14',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'nm_cust.required'  => 'Nama customer wajib diisi.',
            'nm_cust.max'       => 'Nama customer maksimal 50 karakter.',
            'e_mail.email'      => 'Format email tidak valid.',
            'latitude.between'  => 'Nilai latitude harus di antara -90 dan 90.',
            'longitude.between' => 'Nilai longitude harus di antara -180 dan 180.',
        ];
    }
}
