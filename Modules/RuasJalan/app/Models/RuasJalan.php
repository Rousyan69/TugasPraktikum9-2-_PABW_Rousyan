<?php

namespace Modules\RuasJalan\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\LaporanJalan\Models\LaporanJalan;

class RuasJalan extends Model
{
    protected $fillable = ['nama', 'lokasi', 'panjang', 'lebar', 'kondisi'];

    public function laporan()
    {
        return $this->hasMany(LaporanJalan::class);
    }
}
