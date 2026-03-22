<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\KeluargaBantuan;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanSurat;
use App\Models\Rumah;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'jumlah_keluarga' => Keluarga::count(),
            'jumlah_rumah' => Rumah::count(),
            'jumlah_pengajuan_surat' => PengajuanSurat::count(),
            'jumlah_pengajuan_bantuan' => PengajuanBantuan::count(),
            'jumlah_penerima_bantuan' => KeluargaBantuan::count(),
            'statistik_per_lindongan' => Keluarga::select('lindongan', DB::raw('count(*) as total'))
                ->groupBy('lindongan')
                ->orderBy('lindongan')
                ->get(),
        ]);
    }
}
