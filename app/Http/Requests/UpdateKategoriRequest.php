<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
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
        $params = $this->route('params');
        return [
            'kode' => 'required|unique:kategoris,kode,' . $params . ',uuid',
            'nama_kategori' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'kode.required' => 'Kolom kode harus di isi.',
            'nama_kategori.required' => 'Kolom nama kategori harus di isi.',
        ];
    }
}
