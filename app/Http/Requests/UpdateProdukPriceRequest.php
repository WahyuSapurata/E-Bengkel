<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProdukPriceRequest extends FormRequest
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
            'qty' => 'required',
            'harga_jual' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'qty.required' => 'Kolom qty harus di isi.',
            'harga_jual.required' => 'Kolom harga jual harus di isi.',
        ];
    }
}
