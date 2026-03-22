<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeluargaBantuan extends Model
{
    use HasFactory;

    protected $table = 'keluarga_bantuan';

    protected $fillable = [
        'keluarga_id',
        'bantuan_id',
        'status_penerima',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function bantuan()
    {
        return $this->belongsTo(Bantuan::class);
    }
}
