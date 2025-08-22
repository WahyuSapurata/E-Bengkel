<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubKategoriRequest extends FormRequest
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
            'kode' => 'required|unique:kategoris,kode',
            'nama_sub_kategori' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'kode.required' => 'Kolom kode harus di isi.',
            'kode.unique' => 'Kode sudah digunakan.',
            'nama_sub_kategori.required' => 'Kolom nama sub kategori harus di isi.',
        ];
    }
}
