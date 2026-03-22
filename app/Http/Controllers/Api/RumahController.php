<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rumah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RumahController extends Controller
{
    public function index(Request $request)
    {
        $query = Rumah::with('keluarga')
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');
                $builder->whereHas('keluarga', function ($inner) use ($search) {
                    $inner->where('nama_kepala_keluarga', 'like', "%{$search}%")
                        ->orWhere('kode_keluarga', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('lindongan'), fn ($builder) => $builder->where('lindongan', $request->string('lindongan')))
            ->latest();

        return response()->json($query->paginate($request->integer('per_page', 10)));
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $payload['foto_rumah'] = $request->hasFile('foto_rumah')
            ? $request->file('foto_rumah')->store('rumah', 'public')
            : null;

        $rumah = Rumah::create($payload);

        return response()->json($rumah->load('keluarga'), 201);
    }

    public function show(Rumah $rumah)
    {
        return response()->json($rumah->load('keluarga'));
    }

    public function update(Request $request, Rumah $rumah)
    {
        $payload = $this->validatePayload($request);

        if ($request->hasFile('foto_rumah')) {
            if ($rumah->foto_rumah) {
                Storage::disk('public')->delete($rumah->foto_rumah);
            }

            $payload['foto_rumah'] = $request->file('foto_rumah')->store('rumah', 'public');
        }

        $rumah->update($payload);

        return response()->json($rumah->fresh('keluarga'));
    }

    public function destroy(Rumah $rumah)
    {
        if ($rumah->foto_rumah) {
            Storage::disk('public')->delete($rumah->foto_rumah);
        }

        $rumah->delete();

        return response()->json(['message' => 'Data rumah berhasil dihapus.']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'keluarga_id' => ['required', 'exists:keluarga,id'],
            'alamat_singkat' => ['required', 'string', 'max:255'],
            'lindongan' => ['required', 'in:Lindongan 1,Lindongan 2,Lindongan 3,Lindongan 4'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'foto_rumah' => ['nullable', 'image', 'max:2048'],
            'kategori_rumah' => ['required', 'string', 'max:255'],
            'jumlah_penghuni' => ['required', 'integer', 'min:1'],
            'catatan_petugas' => ['nullable', 'string'],
        ]);
    }
}
