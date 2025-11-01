<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWirehouseRequest extends FormRequest
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
            'uuid_user' => 'required',
            'tipe' => 'required',
            'lokasi' => 'required',
            'keterangan' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'uuid_user.required' => 'Kolom nama outlet harus di isi.',
            'tipe.required' => 'Kolom tipe harus di isi.',
            'lokasi.required' => 'Kolom lokasi harus di isi.',
            'keterangan.required' => 'Kolom keterangan harus di isi.',
        ];
    }
}
