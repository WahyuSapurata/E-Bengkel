<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataPenggunaRequest extends FormRequest
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
            'nama' => 'required',
            'username' => 'required',
            'password_hash' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Kolom nama harus di isi.',
            'username.required' => 'Kolom username harus di isi.',
            'password_hash.required' => 'Kolom password harus di isi.',
        ];
    }
}
