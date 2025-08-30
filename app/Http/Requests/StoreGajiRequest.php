<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGajiRequest extends FormRequest
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
            'uuid_karyawan' => 'required',
            'tanggal' => 'required',
            'jumlah' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'uuid_karyawan.required' => 'Kolom karyawan harus di isi.',
            'tanggal.required' => 'Kolom tanggal harus di isi.',
            'jumlah.required' => 'Kolom jumlah harus di isi.',
        ];
    }
}
