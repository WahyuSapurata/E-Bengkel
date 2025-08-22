<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePoPusatRequest extends FormRequest
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
            'uuid_suplayer' => 'required',
            'tanggal_transaksi' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'uuid_suplayer.required' => 'Kolom suplayer harus di isi.',
            'tanggal_transaksi.required' => 'Kolom tanggal transaksi harus di isi.',
        ];
    }
}
