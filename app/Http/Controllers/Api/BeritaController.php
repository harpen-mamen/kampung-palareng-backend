<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $query = Berita::query()
            ->when($request->filled('kategori'), fn ($builder) => $builder->where('kategori', $request->string('kategori')))
            ->latest();

        $berita = $query->paginate($request->integer('per_page', 10));
        $berita->getCollection()->transform(fn ($item) => $this->serializeBerita($item));

        return response()->json($berita);
    }

    public function publicIndex(Request $request)
    {
        $query = Berita::where('status_publish', 'publish')
            ->when($request->filled('kategori'), fn ($builder) => $builder->where('kategori', $request->string('kategori')))
            ->latest();

        $berita = $query->paginate($request->integer('per_page', 12));
        $berita->getCollection()->transform(fn ($item) => $this->serializeBerita($item));

        return response()->json($berita);
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $payload['slug'] = Str::slug($payload['judul']);
        $payload['gambar'] = $request->hasFile('gambar')
            ? $request->file('gambar')->store('berita', 'public')
            : null;

        $berita = Berita::create($payload);

        return response()->json($berita, 201);
    }

    public function show(Berita $berita)
    {
        return response()->json($this->serializeBerita($berita));
    }

    public function publicShow(string $slug)
    {
        return response()->json(
            $this->serializeBerita(
                Berita::where('slug', $slug)->where('status_publish', 'publish')->firstOrFail()
            )
        );
    }

    public function update(Request $request, Berita $berita)
    {
        $payload = $this->validatePayload($request);
        $payload['slug'] = Str::slug($payload['judul']);

        if ($request->hasFile('gambar')) {
            if ($berita->gambar) {
                Storage::disk('public')->delete($berita->gambar);
            }

            $payload['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        $berita->update($payload);

        return response()->json($berita);
    }

    public function destroy(Berita $berita)
    {
        if ($berita->gambar) {
            Storage::disk('public')->delete($berita->gambar);
        }

        $berita->delete();

        return response()->json(['message' => 'Berita berhasil dihapus.']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'ringkasan' => ['required', 'string'],
            'isi' => ['required', 'string'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'status_publish' => ['required', 'in:draft,publish'],
        ]);
    }

    private function serializeBerita(Berita $berita): array
    {
        return [
            'id' => $berita->id,
            'judul' => $berita->judul,
            'slug' => $berita->slug,
            'kategori' => $berita->kategori,
            'ringkasan' => $berita->ringkasan,
            'isi' => $berita->isi,
            'gambar' => $berita->gambar ? asset('storage/' . $berita->gambar) : null,
            'status_publish' => $berita->status_publish,
            'created_at' => $berita->created_at,
            'updated_at' => $berita->updated_at,
        ];
    }
}
