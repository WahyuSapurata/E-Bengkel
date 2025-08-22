<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuplayerRequest extends FormRequest
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
            'kode' => 'required|unique:suplayers,kode,' . $params . ',uuid',
            'nama' => 'required',
            'alamat' => 'required',
            'telepon' => 'required',
            'kota' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'kode.required' => 'Kolom kode harus di isi.',
            'kode.unique' => 'Kode sudah digunakan.',
            'nama.required' => 'Kolom nama harus di isi.',
            'alamat.required' => 'Kolom alamat harus di isi.',
            'telepon.required' => 'Kolom telepon harus di isi.',
            'kota.required' => 'Kolom kota harus di isi.',
        ];
    }
}
