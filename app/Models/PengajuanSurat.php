<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanSurat extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_surat';

    protected $fillable = [
        'nama_pemohon',
        'jenis_surat',
        'keperluan',
        'alamat',
        'lindongan',
        'whatsapp_pemohon',
        'lampiran',
        'status',
        'catatan_admin',
        'nomor_urut_surat',
        'nomor_surat',
        'tanggal_surat',
        'disetujui_at',
        'approved_by',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'isi_surat',
        'file_surat',
        'arsip_surat_at',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'disetujui_at' => 'datetime',
        'arsip_surat_at' => 'datetime',
    ];

    protected $appends = [
        'file_surat_url',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFileSuratUrlAttribute()
    {
        return $this->file_surat ? asset('storage/' . $this->file_surat) : null;
    }
}
