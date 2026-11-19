<?php

namespace Modules\RuasJalan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RuasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return ['nama' => 'required|string|max:150', 'lokasi' => 'required|string|max:255', 'panjang' => 'required|numeric|min:0.01|max:99999999', 'lebar' => 'required|numeric|min:0.01|max:9999', 'kondisi' => 'required|in:Baik,Rusak Ringan,Rusak Berat'];
    }
}
