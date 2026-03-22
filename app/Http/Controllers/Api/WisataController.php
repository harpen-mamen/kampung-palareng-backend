<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wisata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WisataController extends Controller
{
    public function index(Request $request)
    {
        $wisata = Wisata::query()
            ->latest()
            ->paginate($request->integer('per_page', 10));

        $wisata->getCollection()->transform(fn ($item) => $this->serializeWisata($item));

        return response()->json($wisata);
    }

    public function publicIndex()
    {
        return response()->json(
            Wisata::query()
                ->where('status_publish', 'publish')
                ->latest()
                ->get()
                ->map(fn ($item) => $this->serializeWisata($item))
        );
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $payload['image'] = $request->hasFile('image')
            ? $request->file('image')->store('wisata', 'public')
            : null;

        $wisata = Wisata::create($payload);

        return response()->json($this->serializeWisata($wisata), 201);
    }

    public function show(Wisata $wisata)
    {
        return response()->json($this->serializeWisata($wisata));
    }

    public function update(Request $request, Wisata $wisata)
    {
        $payload = $this->validatePayload($request);

        if ($request->hasFile('image')) {
            if ($wisata->image) {
                Storage::disk('public')->delete($wisata->image);
            }

            $payload['image'] = $request->file('image')->store('wisata', 'public');
        }

        $wisata->update($payload);

        return response()->json($this->serializeWisata($wisata->fresh()));
    }

    public function destroy(Wisata $wisata)
    {
        if ($wisata->image) {
            Storage::disk('public')->delete($wisata->image);
        }

        $wisata->delete();

        return response()->json(['message' => 'Wisata berhasil dihapus.']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'status_publish' => ['required', 'in:draft,publish'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function serializeWisata(Wisata $wisata): array
    {
        return [
            'id' => $wisata->id,
            'nama' => $wisata->nama,
            'lokasi' => $wisata->lokasi,
            'deskripsi' => $wisata->deskripsi,
            'image' => $wisata->image ? asset('storage/' . $wisata->image) : null,
            'status_publish' => $wisata->status_publish,
            'created_at' => $wisata->created_at,
            'updated_at' => $wisata->updated_at,
        ];
    }
}
