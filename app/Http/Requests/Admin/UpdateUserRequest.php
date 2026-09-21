<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // route param 'user' = NO_USER
        $noUser = $this->route('user');

        return [
            'nm_user'  => 'required|string|max:50|unique:MST_PENGGUNA,NM_USER,' . $noUser . ',NO_USER',
            'role'     => 'required|in:admin,sales',
            'kd_peg'   => 'required_if:role,sales|nullable|string|max:10|exists:PEGAWAI,KD_PEG',
            'password' => 'nullable|string|min:4|max:32|confirmed',
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
            'password.min'          => 'Password minimal 4 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ];
    }
}
