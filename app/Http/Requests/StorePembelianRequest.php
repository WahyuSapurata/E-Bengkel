<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembelianRequest extends FormRequest
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
            'no_invoice' => 'required',
            'no_internal' => 'required',
            'uuid_suplayer' => 'required',
            'pembayaran' => 'required',
            'tanggal_transaksi' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'no_invoice.required' => 'Kolom no invoice harus di isi.',
            'no_internal.required' => 'Kolom no internal harus di isi.',
            'uuid_suplayer.required' => 'Kolom suplayer harus di isi.',
            'pembayaran.required' => 'Kolom pembayaran harus di isi.',
            'tanggal_transaksi.required' => 'Kolom tanggal transaksi harus di isi.',
        ];
    }
}
