<?php

namespace Modules\LaporanJalan\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\RuasJalan\Models\RuasJalan;

class LaporanJalan extends Model
{
    protected function casts(): array
    {
        return ['user_id' => 'integer', 'ruas_jalan_id' => 'integer'];
    }

    protected $fillable = ['ruas_jalan_id', 'user_id', 'judul', 'deskripsi', 'tanggal', 'status', 'foto'];

    public function ruas()
    {
        return $this->belongsTo(RuasJalan::class, 'ruas_jalan_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
