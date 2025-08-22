<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengirimanBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uuid_po_outlet' => 'required',
            'tanggal_kirim' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'uuid_po_outlet.required' => 'Kolom kode po outlet harus di isi.',
            'tanggal_kirim.required' => 'Kolom tanggal kirim harus di isi.',
        ];
    }
}
