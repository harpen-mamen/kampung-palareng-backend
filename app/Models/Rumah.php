<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rumah extends Model
{
    use HasFactory;

    protected $table = 'rumah';

    protected $fillable = [
        'keluarga_id',
        'alamat_singkat',
        'lindongan',
        'latitude',
        'longitude',
        'foto_rumah',
        'kategori_rumah',
        'jumlah_penghuni',
        'catatan_petugas',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }
}
