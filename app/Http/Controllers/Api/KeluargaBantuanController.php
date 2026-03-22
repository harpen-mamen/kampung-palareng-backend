<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KeluargaBantuan;
use Illuminate\Http\Request;

class KeluargaBantuanController extends Controller
{
    public function index(Request $request)
    {
        $query = KeluargaBantuan::with(['keluarga', 'bantuan'])
            ->when($request->filled('bantuan_id'), fn ($builder) => $builder->where('bantuan_id', $request->integer('bantuan_id')))
            ->when($request->filled('lindongan'), function ($builder) use ($request) {
                $builder->whereHas('keluarga', fn ($inner) => $inner->where('lindongan', $request->string('lindongan')));
            })
            ->latest();

        return response()->json($query->paginate($request->integer('per_page', 10)));
    }

    public function store(Request $request)
    {
        $record = KeluargaBantuan::create($this->validatePayload($request));

        return response()->json($record->load(['keluarga', 'bantuan']), 201);
    }

    public function show(KeluargaBantuan $keluargaBantuan)
    {
        return response()->json($keluargaBantuan->load(['keluarga', 'bantuan']));
    }

    public function update(Request $request, KeluargaBantuan $keluargaBantuan)
    {
        $keluargaBantuan->update($this->validatePayload($request));

        return response()->json($keluargaBantuan->fresh(['keluarga', 'bantuan']));
    }

    public function destroy(KeluargaBantuan $keluargaBantuan)
    {
        $keluargaBantuan->delete();

        return response()->json(['message' => 'Relasi bantuan berhasil dihapus.']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'keluarga_id' => ['required', 'exists:keluarga,id'],
            'bantuan_id' => ['required', 'exists:bantuan,id'],
            'status_penerima' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string'],
        ]);
    }
}
