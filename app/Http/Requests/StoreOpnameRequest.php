<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpnameRequest extends FormRequest
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
            'stock' => 'required',
            'uuid_wirehouse' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'stock.required' => 'Kolom stock harus di isi.',
            'uuid_wirehouse.required' => 'Kolom wirehouse harus di isi.',
        ];
    }
}
