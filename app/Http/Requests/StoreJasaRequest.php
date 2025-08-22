<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJasaRequest extends FormRequest
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
            'kode' => 'required|unique:jasas,kode',
            'nama' => 'required',
            'deskripsi' => 'required',
            'harga' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'kode.required' => 'Kolom kode harus di isi.',
            'kode.unique' => 'Kode sudah digunakan.',
            'nama.required' => 'Kolom nama harus di isi.',
            'deskripsi.required' => 'Kolom deskripsi harus di isi.',
            'harga.required' => 'Kolom harga harus di isi.',
        ];
    }
}
