<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Pengumuman::latest()->paginate($request->integer('per_page', 10)));
    }

    public function publicIndex()
    {
        return response()->json(
            Pengumuman::where('status_publish', 'publish')->latest()->take(6)->get()
        );
    }

    public function store(Request $request)
    {
        $pengumuman = Pengumuman::create($this->validatePayload($request));

        return response()->json($pengumuman, 201);
    }

    public function show(Pengumuman $pengumuman)
    {
        return response()->json($pengumuman);
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $pengumuman->update($this->validatePayload($request));

        return response()->json($pengumuman);
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return response()->json(['message' => 'Pengumuman berhasil dihapus.']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'status_publish' => ['required', 'in:draft,publish'],
        ]);
    }
}
