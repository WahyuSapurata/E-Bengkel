<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaketHematRequest extends FormRequest
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
            'uuid_produk' => 'required',
            'nama_paket' => 'required',
            'total_modal' => 'required',
            'profit' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'uuid_produk.required' => 'Kolom produk harus di isi.',
            'nama_paket.required' => 'Kolom nama paket harus di isi.',
            'total_modal.required' => 'Kolom total modal harus di isi.',
            'profit.required' => 'Kolom profit harus di isi.',
        ];
    }
}
