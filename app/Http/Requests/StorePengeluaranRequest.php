<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranRequest extends FormRequest
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
            'deskripsi' => 'required',
            'sumber_dana' => 'required',
            'nominal' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'deskripsi.required' => 'Kolom deskripsi harus di isi.',
            'sumber_dana.required' => 'Kolom sumber dana harus di isi.',
            'nominal.required' => 'Kolom nominal harus di isi.',
        ];
    }
}
