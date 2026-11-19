<?php

namespace Modules\LaporanJalan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['ruas_jalan_id' => 'required|exists:ruas_jalans,id', 'judul' => 'required|string|max:150', 'deskripsi' => 'required|string|max:5000', 'tanggal' => 'required|date|before_or_equal:today', 'status' => $this->user()->isAdmin() ? 'required|in:Diajukan,Diproses,Selesai' : 'prohibited', 'foto' => [$this->isMethod('POST') ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048']];
    }
}
