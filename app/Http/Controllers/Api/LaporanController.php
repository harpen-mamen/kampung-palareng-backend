<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bantuan;
use App\Models\Keluarga;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanSurat;
use App\Models\Rumah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LaporanController extends Controller
{
    public function export(Request $request)
    {
        $payload = $request->validate([
            'dataset' => ['required', 'in:keluarga,rumah,bantuan,surat,pengajuan_bantuan'],
            'format' => ['required', 'in:excel,pdf'],
            'lindongan' => ['nullable', 'in:Lindongan 1,Lindongan 2,Lindongan 3,Lindongan 4'],
        ]);

        $rows = $this->dataset($payload['dataset'], $payload['lindongan'] ?? null);

        if ($payload['format'] === 'pdf') {
            $pdf = Pdf::loadView('reports.table', [
                'title' => 'Laporan ' . str_replace('_', ' ', ucfirst($payload['dataset'])),
                'rows' => $rows,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($payload['dataset'] . '.pdf');
        }

        $html = view('reports.excel', [
            'title' => 'Laporan ' . str_replace('_', ' ', ucfirst($payload['dataset'])),
            'rows' => $rows,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $payload['dataset'] . '.xls"',
        ]);
    }

    private function dataset(string $dataset, ?string $lindongan): Collection
    {
        return match ($dataset) {
            'keluarga' => Keluarga::query()->when($lindongan, fn ($builder) => $builder->where('lindongan', $lindongan))->get(),
            'rumah' => Rumah::with('keluarga')->when($lindongan, fn ($builder) => $builder->where('lindongan', $lindongan))->get(),
            'bantuan' => Bantuan::all(),
            'surat' => PengajuanSurat::query()->when($lindongan, fn ($builder) => $builder->where('lindongan', $lindongan))->get(),
            default => PengajuanBantuan::query()->when($lindongan, fn ($builder) => $builder->where('lindongan', $lindongan))->get(),
        };
    }
}
