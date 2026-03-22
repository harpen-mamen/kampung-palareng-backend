<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanBantuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_bantuan';

    protected $fillable = [
        'user_id',
        'nama_pemohon',
        'alamat',
        'lindongan',
        'whatsapp_pemohon',
        'bantuan_id',
        'jenis_bantuan',
        'keterangan',
        'lampiran',
        'status_pengajuan',
        'catatan_admin',
        'keluarga_id',
    ];

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function bantuan()
    {
        return $this->belongsTo(Bantuan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
