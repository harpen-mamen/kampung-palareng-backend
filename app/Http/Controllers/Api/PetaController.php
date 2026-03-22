<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bantuan;
use App\Models\FasilitasUmum;
use App\Models\Keluarga;
use App\Models\Rumah;
use Illuminate\Http\Request;

class PetaController extends Controller
{
    public function publicMap(Request $request)
    {
        return response()->json([
            'markers' => $this->baseMapQuery($request)->get()->map(fn ($item) => $this->serializeRumah($item)),
            'layers' => $this->geoJsonLayers(),
            'fasilitas_umum' => FasilitasUmum::all(),
            'filters' => $this->filterOptions(),
        ]);
    }

    public function adminMap(Request $request)
    {
        return response()->json([
            'markers' => $this->baseMapQuery($request)->get()->map(fn ($item) => $this->serializeRumah($item)),
            'layers' => $this->geoJsonLayers(),
            'fasilitas_umum' => FasilitasUmum::all(),
            'filters' => $this->filterOptions(),
        ]);
    }

    private function baseMapQuery(Request $request)
    {
        return Rumah::with(['keluarga', 'keluarga.bantuan.bantuan'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');
                $builder->whereHas('keluarga', function ($inner) use ($search) {
                    $inner->where('nama_kepala_keluarga', 'like', "%{$search}%")
                        ->orWhere('kode_keluarga', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('lindongan'), fn ($builder) => $builder->where('lindongan', $request->string('lindongan')))
            ->when($request->filled('status_ekonomi'), function ($builder) use ($request) {
                $builder->whereHas('keluarga', fn ($inner) => $inner->where('status_ekonomi', $request->string('status_ekonomi')));
            })
            ->when($request->filled('pekerjaan_utama'), function ($builder) use ($request) {
                $builder->whereHas('keluarga', fn ($inner) => $inner->where('pekerjaan_utama', $request->string('pekerjaan_utama')));
            })
            ->when($request->filled('penerima_bantuan'), function ($builder) use ($request) {
                $isRecipient = in_array((string) $request->string('penerima_bantuan'), ['1', 'ya', 'true'], true);

                if ($isRecipient) {
                    $builder->whereHas('keluarga.bantuan');
                } else {
                    $builder->whereDoesntHave('keluarga.bantuan');
                }
            })
            ->when($request->filled('bantuan_id'), function ($builder) use ($request) {
                $bantuanId = $request->integer('bantuan_id');
                $builder->whereHas('keluarga.bantuan', fn ($inner) => $inner->where('bantuan_id', $bantuanId));
            })
            ->when($request->filled('status_dtks'), function ($builder) use ($request) {
                $statusDtks = in_array((string) $request->string('status_dtks'), ['1', 'ya', 'true'], true);
                $builder->whereHas('keluarga', fn ($inner) => $inner->where('status_dtks', $statusDtks));
            });
    }

    private function serializeRumah(Rumah $item): array
    {
        return [
            'id' => $item->id,
            'nama_kepala_keluarga' => $item->keluarga?->nama_kepala_keluarga,
            'jumlah_penghuni' => $item->jumlah_penghuni,
            'lindongan' => $item->lindongan,
            'latitude' => $item->latitude,
            'longitude' => $item->longitude,
            'foto_rumah' => $item->foto_rumah ? asset('storage/' . $item->foto_rumah) : null,
            'alamat_singkat' => $item->alamat_singkat,
            'kategori_rumah' => $item->kategori_rumah,
            'catatan_petugas' => $item->catatan_petugas,
            'keluarga' => [
                'id' => $item->keluarga?->id,
                'kode_keluarga' => $item->keluarga?->kode_keluarga,
                'nama_kepala_keluarga' => $item->keluarga?->nama_kepala_keluarga,
                'alamat' => $item->keluarga?->alamat,
                'lindongan' => $item->keluarga?->lindongan,
                'jumlah_anggota' => $item->keluarga?->jumlah_anggota,
                'status_ekonomi' => $item->keluarga?->status_ekonomi,
                'pekerjaan_utama' => $item->keluarga?->pekerjaan_utama,
                'kategori_rumah' => $item->keluarga?->kategori_rumah,
                'status_dtks' => $item->keluarga?->status_dtks,
                'catatan_petugas' => $item->keluarga?->catatan_petugas,
                'bantuan' => $item->keluarga?->bantuan?->map(fn ($b) => [
                    'id' => $b->bantuan_id,
                    'status_penerima' => $b->status_penerima,
                    'nama_bantuan' => $b->bantuan?->nama_bantuan,
                ])->values(),
            ],
        ];
    }

    private function filterOptions(): array
    {
        return [
            'lindongan' => Rumah::query()
                ->select('lindongan')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->distinct()
                ->orderBy('lindongan')
                ->pluck('lindongan')
                ->values(),
            'pekerjaan_utama' => Keluarga::query()
                ->select('pekerjaan_utama')
                ->distinct()
                ->orderBy('pekerjaan_utama')
                ->pluck('pekerjaan_utama')
                ->values(),
            'status_ekonomi' => Keluarga::query()
                ->select('status_ekonomi')
                ->distinct()
                ->orderBy('status_ekonomi')
                ->pluck('status_ekonomi')
                ->values(),
            'bantuan' => Bantuan::query()
                ->select('id', 'nama_bantuan')
                ->orderBy('nama_bantuan')
                ->get(),
        ];
    }

    private function geoJsonLayers(): array
    {
        return [
            'batas_kampung' => ['type' => 'FeatureCollection', 'features' => []],
            'batas_lindongan' => ['type' => 'FeatureCollection', 'features' => []],
            'jalan' => ['type' => 'FeatureCollection', 'features' => []],
        ];
    }
}
