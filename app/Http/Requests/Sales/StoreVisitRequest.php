<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'visit_date'  => 'required|date',
            'purpose'     => 'required|in:merchandising,collection,order',
            'notes'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer wajib dipilih.',
            'customer_id.exists'   => 'Customer tidak valid.',
            'visit_date.required'  => 'Tanggal kunjungan wajib diisi.',
            'visit_date.date'      => 'Format tanggal tidak valid.',
            'purpose.required'     => 'Tujuan kunjungan wajib dipilih.',
            'purpose.in'           => 'Tujuan kunjungan tidak valid.',
        ];
    }
}
