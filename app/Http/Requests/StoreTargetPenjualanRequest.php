<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTargetPenjualanRequest extends FormRequest
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
            'tanggal' => 'required',
            'target' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'tanggal.required' => 'Kolom tanggal harus di isi.',
            'target.required' => 'Kolom target harus di isi.',
        ];
    }
}
