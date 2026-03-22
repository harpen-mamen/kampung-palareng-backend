<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use Illuminate\Http\Request;

class KeluargaController extends Controller
{
    public function index(Request $request)
    {
        $query = Keluarga::with(['rumah', 'bantuan.bantuan'])
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');
                $builder->where(function ($inner) use ($search) {
                    $inner->where('kode_keluarga', 'like', "%{$search}%")
                        ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('lindongan'), fn ($builder) => $builder->where('lindongan', $request->string('lindongan')))
            ->latest();

        return response()->json($query->paginate($request->integer('per_page', 10)));
    }

    public function store(Request $request)
    {
        $keluarga = Keluarga::create($this->validatePayload($request));

        return response()->json($keluarga, 201);
    }

    public function show(Keluarga $keluarga)
    {
        return response()->json($keluarga->load(['rumah', 'bantuan.bantuan']));
    }

    public function update(Request $request, Keluarga $keluarga)
    {
        $keluarga->update($this->validatePayload($request, $keluarga->id));

        return response()->json($keluarga->fresh(['rumah', 'bantuan.bantuan']));
    }

    public function destroy(Keluarga $keluarga)
    {
        $keluarga->delete();

        return response()->json(['message' => 'Data keluarga berhasil dihapus.']);
    }

    private function validatePayload(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'kode_keluarga' => ['required', 'string', 'max:255', 'unique:keluarga,kode_keluarga,' . $id],
            'nama_kepala_keluarga' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'lindongan' => ['required', 'in:Lindongan 1,Lindongan 2,Lindongan 3,Lindongan 4'],
            'jumlah_anggota' => ['required', 'integer', 'min:1'],
            'status_ekonomi' => ['required', 'string', 'max:255'],
            'pekerjaan_utama' => ['required', 'string', 'max:255'],
            'kategori_rumah' => ['required', 'string', 'max:255'],
            'status_dtks' => ['required', 'boolean'],
            'catatan_petugas' => ['nullable', 'string'],
        ]);
    }
}
