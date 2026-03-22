<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bantuan extends Model
{
    use HasFactory;

    protected $table = 'bantuan';

    protected $fillable = [
        'nama_bantuan',
        'kategori',
        'sumber',
        'periode',
        'status',
        'is_open_for_submission',
        'kuota',
        'deskripsi',
    ];

    protected $casts = [
        'is_open_for_submission' => 'boolean',
        'kuota' => 'integer',
    ];

    public function penerima()
    {
        return $this->hasMany(KeluargaBantuan::class);
    }

    public function pengajuan()
    {
        return $this->hasMany(PengajuanBantuan::class);
    }
}
