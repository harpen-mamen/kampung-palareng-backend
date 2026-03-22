<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bantuan;
use Illuminate\Http\Request;

class BantuanController extends Controller
{
    public function index(Request $request)
    {
        $query = Bantuan::query()
            ->withCount([
                'pengajuan as total_pengajuan' => fn ($builder) => $builder->where('status_pengajuan', '!=', 'ditolak'),
            ])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->when($request->filled('kategori'), fn ($builder) => $builder->where('kategori', $request->string('kategori')))
            ->latest();

        $result = $query->paginate($request->integer('per_page', 10));
        $result->getCollection()->transform(fn (Bantuan $item) => $this->appendQuotaSummary($item));

        return response()->json($result);
    }

    public function publicIndex()
    {
        $items = Bantuan::query()
            ->withCount([
                'pengajuan as total_pengajuan' => fn ($builder) => $builder->where('status_pengajuan', '!=', 'ditolak'),
            ])
            ->where('status', 'aktif')
            ->where('is_open_for_submission', true)
            ->latest()
            ->get()
            ->map(fn (Bantuan $item) => $this->appendQuotaSummary($item))
            ->values();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $bantuan = Bantuan::create($this->validatePayload($request));

        return response()->json($bantuan, 201);
    }

    public function show(Bantuan $bantuan)
    {
        return response()->json($bantuan->load('penerima.keluarga'));
    }

    public function update(Request $request, Bantuan $bantuan)
    {
        $bantuan->update($this->validatePayload($request));

        return response()->json($bantuan);
    }

    public function destroy(Bantuan $bantuan)
    {
        $bantuan->delete();

        return response()->json(['message' => 'Data bantuan berhasil dihapus.']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'nama_bantuan' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'sumber' => ['required', 'string', 'max:255'],
            'periode' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:255'],
            'is_open_for_submission' => ['required', 'boolean'],
            'kuota' => ['nullable', 'integer', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }

    private function appendQuotaSummary(Bantuan $item): Bantuan
    {
        $totalPengajuan = (int) ($item->total_pengajuan ?? 0);
        $item->setAttribute('total_pengajuan', $totalPengajuan);
        $item->setAttribute(
            'remaining_quota',
            $item->kuota ? max($item->kuota - $totalPengajuan, 0) : null
        );

        return $item;
    }
}
