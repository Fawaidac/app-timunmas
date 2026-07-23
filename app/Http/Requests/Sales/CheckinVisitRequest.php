<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class CheckinVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkin_latitude'  => 'required|numeric|between:-90,90',
            'checkin_longitude' => 'required|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'checkin_latitude.required'  => 'Koordinat GPS wajib diisi.',
            'checkin_latitude.numeric'   => 'Latitude harus berupa angka.',
            'checkin_latitude.between'   => 'Latitude tidak valid.',
            'checkin_longitude.required' => 'Koordinat GPS wajib diisi.',
            'checkin_longitude.numeric'  => 'Longitude harus berupa angka.',
            'checkin_longitude.between'  => 'Longitude tidak valid.',
        ];
    }
}
