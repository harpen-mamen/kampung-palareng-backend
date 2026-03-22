<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\KeluargaBantuan;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanSurat;
use App\Models\Rumah;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function publicStats()
    {
        $jumlahKeluarga = Keluarga::count();
        $jumlahPenduduk = Keluarga::sum('jumlah_anggota');
        $jumlahRumah = Rumah::count();
        $jumlahPenerimaBantuan = KeluargaBantuan::count();
        $jumlahDtks = Keluarga::where('status_dtks', true)->count();
        $jumlahNelayan = Keluarga::where('pekerjaan_utama', 'like', '%nelayan%')->count();

        return response()->json([
            'jumlah_keluarga' => $jumlahKeluarga,
            'jumlah_penduduk' => $jumlahPenduduk,
            'jumlah_rumah' => $jumlahRumah,
            'jumlah_penerima_bantuan' => $jumlahPenerimaBantuan,
            'jumlah_dtks' => $jumlahDtks,
            'jumlah_nelayan' => $jumlahNelayan,
            'per_lindongan' => Keluarga::select('lindongan', DB::raw('count(*) as total_keluarga'), DB::raw('sum(jumlah_anggota) as total_penduduk'))
                ->groupBy('lindongan')
                ->orderBy('lindongan')
                ->get(),
            'komposisi_pekerjaan' => Keluarga::select('pekerjaan_utama as label', DB::raw('count(*) as total'))
                ->groupBy('pekerjaan_utama')
                ->orderByDesc('total')
                ->get(),
            'komposisi_status_ekonomi' => Keluarga::select('status_ekonomi as label', DB::raw('count(*) as total'))
                ->groupBy('status_ekonomi')
                ->orderByDesc('total')
                ->get(),
            'komposisi_kategori_rumah' => Rumah::select('kategori_rumah as label', DB::raw('count(*) as total'))
                ->groupBy('kategori_rumah')
                ->orderByDesc('total')
                ->get(),
            'penerima_bantuan_per_jenis' => KeluargaBantuan::select('bantuan.nama_bantuan as label', DB::raw('count(*) as total'))
                ->join('bantuan', 'bantuan.id', '=', 'keluarga_bantuan.bantuan_id')
                ->groupBy('bantuan.nama_bantuan')
                ->orderByDesc('total')
                ->get(),
            'komposisi_dtks' => collect([
                ['label' => 'DTKS', 'total' => $jumlahDtks],
                ['label' => 'Non-DTKS', 'total' => max($jumlahKeluarga - $jumlahDtks, 0)],
            ]),
        ]);
    }

    public function adminStats()
    {
        return response()->json([
            'keluarga_per_lindongan' => Keluarga::select('lindongan', DB::raw('count(*) as total'))
                ->groupBy('lindongan')
                ->orderBy('lindongan')
                ->get(),
            'rumah_per_kategori' => Rumah::select('kategori_rumah', DB::raw('count(*) as total'))
                ->groupBy('kategori_rumah')
                ->get(),
            'dtks' => [
                'dtks' => Keluarga::where('status_dtks', true)->count(),
                'non_dtks' => Keluarga::where('status_dtks', false)->count(),
            ],
            'penerima_per_jenis' => KeluargaBantuan::select('bantuan.nama_bantuan', DB::raw('count(*) as total'))
                ->join('bantuan', 'bantuan.id', '=', 'keluarga_bantuan.bantuan_id')
                ->groupBy('bantuan.nama_bantuan')
                ->get(),
            'pengajuan_surat' => PengajuanSurat::select('status', DB::raw('count(*) as total'))->groupBy('status')->get(),
            'pengajuan_bantuan' => PengajuanBantuan::select('status_pengajuan', DB::raw('count(*) as total'))->groupBy('status_pengajuan')->get(),
        ]);
    }
}
