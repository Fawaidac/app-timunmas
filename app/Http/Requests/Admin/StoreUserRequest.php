<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // nm_user = username login (disimpan ke NM_USER di MST_PENGGUNA)
            'nm_user'  => 'required|string|max:50|unique:MST_PENGGUNA,NM_USER',
            'role'     => 'required|in:admin,sales',
            // kd_peg wajib kalau role = sales (link ke tabel PEGAWAI)
            'kd_peg'   => 'required_if:role,sales|nullable|string|max:10|exists:PEGAWAI,KD_PEG',
            'password' => 'required|string|min:4|max:32|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'nm_user.required'      => 'Username wajib diisi.',
            'nm_user.unique'        => 'Username sudah dipakai akun lain.',
            'nm_user.max'           => 'Username maksimal 50 karakter.',
            'role.required'         => 'Role wajib dipilih.',
            'role.in'               => 'Role harus admin atau sales.',
            'kd_peg.required_if'    => 'Kode Pegawai wajib dipilih untuk role Sales.',
            'kd_peg.exists'         => 'Kode Pegawai tidak ditemukan di tabel PEGAWAI.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 4 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ];
    }
}
