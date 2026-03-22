<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    use HasFactory;

    protected $table = 'keluarga';

    protected $fillable = [
        'kode_keluarga',
        'nama_kepala_keluarga',
        'alamat',
        'lindongan',
        'jumlah_anggota',
        'status_ekonomi',
        'pekerjaan_utama',
        'kategori_rumah',
        'status_dtks',
        'catatan_petugas',
    ];

    protected $casts = [
        'status_dtks' => 'boolean',
    ];

    public function rumah()
    {
        return $this->hasOne(Rumah::class);
    }

    public function bantuan()
    {
        return $this->hasMany(KeluargaBantuan::class);
    }

    public function pengajuanBantuan()
    {
        return $this->hasMany(PengajuanBantuan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
