<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bantuan;
use App\Models\PengajuanBantuan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PengajuanBantuanController extends Controller
{
    public function index(Request $request)
    {
        $relations = ['keluarga', 'bantuan'];
        if (Schema::hasColumn('pengajuan_bantuan', 'user_id')) {
            $relations[] = 'user';
        }

        $query = PengajuanBantuan::with($relations)
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');

                $builder->where(function ($inner) use ($search) {
                    $inner
                        ->where('nama_pemohon', 'like', '%' . $search . '%')
                        ->orWhere('jenis_bantuan', 'like', '%' . $search . '%');

                    if (Schema::hasColumn('pengajuan_bantuan', 'whatsapp_pemohon')) {
                        $inner->orWhere('whatsapp_pemohon', 'like', '%' . $search . '%');
                    }
                });
            })
            ->when($request->filled('status_pengajuan'), fn ($builder) => $builder->where('status_pengajuan', $request->string('status_pengajuan')))
            ->when($request->filled('lindongan'), fn ($builder) => $builder->where('lindongan', $request->string('lindongan')))
            ->when($request->boolean('archived_only'), fn ($builder) => $builder->whereIn('status_pengajuan', ['disetujui', 'ditolak', 'selesai']))
            ->latest();

        return response()->json($query->paginate($request->integer('per_page', 10)));
    }

    public function store(Request $request)
    {
        $relations = ['keluarga', 'bantuan'];
        if (Schema::hasColumn('pengajuan_bantuan', 'user_id')) {
            $relations[] = 'user';
        }

        $user = $request->user();
        $payload = $this->validatePayload($request, false);
        $bantuan = Bantuan::query()->findOrFail($payload['bantuan_id']);

        if ($user->approval_status !== 'disetujui') {
            throw ValidationException::withMessages([
                'bantuan_id' => 'Akun masyarakat harus disetujui admin sebelum mengajukan bantuan.',
            ]);
        }

        if (! $user->whatsapp) {
            throw ValidationException::withMessages([
                'whatsapp' => 'Nomor WhatsApp akun masyarakat wajib diisi terlebih dahulu sebelum mengajukan bantuan.',
            ]);
        }

        if ($bantuan->status !== 'aktif' || ! $bantuan->is_open_for_submission) {
            throw ValidationException::withMessages([
                'bantuan_id' => 'Jenis bantuan ini belum dibuka untuk pengajuan masyarakat.',
            ]);
        }

        $activeSubmissionCount = PengajuanBantuan::query()
            ->where('bantuan_id', $bantuan->id)
            ->where('status_pengajuan', '!=', 'ditolak')
            ->count();

        if ($bantuan->kuota && $activeSubmissionCount >= $bantuan->kuota) {
            throw ValidationException::withMessages([
                'bantuan_id' => 'Kuota bantuan ini sudah terpenuhi.',
            ]);
        }

        if ($user->keluarga_id) {
            $hasExistingSubmission = PengajuanBantuan::query()
                ->where('bantuan_id', $bantuan->id)
                ->where('keluarga_id', $user->keluarga_id)
                ->whereNotIn('status_pengajuan', ['ditolak', 'selesai'])
                ->exists();

            if ($hasExistingSubmission) {
                throw ValidationException::withMessages([
                    'bantuan_id' => 'Keluarga ini sudah memiliki pengajuan aktif untuk jenis bantuan tersebut.',
                ]);
            }
        }

        if (Schema::hasColumn('pengajuan_bantuan', 'user_id')) {
            $payload['user_id'] = $user->id;
        }
        $payload['nama_pemohon'] = $user->name;
        $payload['alamat'] = $user->alamat ?? $payload['alamat'];
        $payload['lindongan'] = $user->lindongan ?? $payload['lindongan'];
        if (Schema::hasColumn('pengajuan_bantuan', 'whatsapp_pemohon')) {
            $payload['whatsapp_pemohon'] = $user->whatsapp;
        }
        $payload['bantuan_id'] = $bantuan->id;
        $payload['jenis_bantuan'] = $bantuan->nama_bantuan;
        $payload['keluarga_id'] = $user->keluarga_id ?? $payload['keluarga_id'] ?? null;
        $payload['lampiran'] = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('pengajuan-bantuan', 'public')
            : null;

        $pengajuan = PengajuanBantuan::create($payload);

        return response()->json($pengajuan->fresh($relations), 201);
    }

    public function show(PengajuanBantuan $pengajuanBantuan)
    {
        $relations = ['keluarga', 'bantuan'];
        if (Schema::hasColumn('pengajuan_bantuan', 'user_id')) {
            $relations[] = 'user';
        }

        return response()->json($pengajuanBantuan->load($relations));
    }

    public function update(Request $request, PengajuanBantuan $pengajuanBantuan)
    {
        $payload = $this->validatePayload($request, true);

        if ($request->hasFile('lampiran')) {
            if ($pengajuanBantuan->lampiran) {
                Storage::disk('public')->delete($pengajuanBantuan->lampiran);
            }

            $payload['lampiran'] = $request->file('lampiran')->store('pengajuan-bantuan', 'public');
        }

        $pengajuanBantuan->update($payload);

        $relations = ['keluarga', 'bantuan'];
        if (Schema::hasColumn('pengajuan_bantuan', 'user_id')) {
            $relations[] = 'user';
        }

        return response()->json($pengajuanBantuan->fresh($relations));
    }

    public function destroy(PengajuanBantuan $pengajuanBantuan)
    {
        if ($pengajuanBantuan->lampiran) {
            Storage::disk('public')->delete($pengajuanBantuan->lampiran);
        }

        $pengajuanBantuan->delete();

        return response()->json(['message' => 'Pengajuan bantuan berhasil dihapus.']);
    }

    public function updateStatus(Request $request, PengajuanBantuan $pengajuanBantuan)
    {
        $payload = $request->validate([
            'status_pengajuan' => ['required', 'in:diajukan,diverifikasi,diproses,disetujui,ditolak,selesai'],
            'catatan_admin' => ['nullable', 'string'],
            'keluarga_id' => ['nullable', 'exists:keluarga,id'],
        ]);

        if (Schema::hasColumn('pengajuan_bantuan', 'whatsapp_pemohon') && empty($pengajuanBantuan->whatsapp_pemohon)) {
            $resolvedWhatsapp = $this->resolveWhatsappPemohon(
                $payload['keluarga_id'] ?? $pengajuanBantuan->keluarga_id,
                $pengajuanBantuan->nama_pemohon,
                $pengajuanBantuan->lindongan,
                Schema::hasColumn('pengajuan_bantuan', 'user_id') ? $pengajuanBantuan->user_id : null
            );

            if ($resolvedWhatsapp) {
                $payload['whatsapp_pemohon'] = $resolvedWhatsapp;
            }
        }

        $pengajuanBantuan->update($payload);

        $relations = ['keluarga', 'bantuan'];
        if (Schema::hasColumn('pengajuan_bantuan', 'user_id')) {
            $relations[] = 'user';
        }

        return response()->json($pengajuanBantuan->fresh($relations));
    }

    private function validatePayload(Request $request, bool $includeStatus): array
    {
        $rules = [
            'alamat' => ['nullable', 'string'],
            'lindongan' => ['nullable', 'in:Lindongan 1,Lindongan 2,Lindongan 3,Lindongan 4'],
            'bantuan_id' => ['required', 'exists:bantuan,id'],
            'jenis_bantuan' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'lampiran' => ['nullable', 'file', 'max:2048'],
            'catatan_admin' => ['nullable', 'string'],
            'keluarga_id' => ['nullable', 'exists:keluarga,id'],
        ];

        if ($includeStatus) {
            $rules['status_pengajuan'] = ['required', 'in:diajukan,diverifikasi,diproses,disetujui,ditolak,selesai'];
        }

        return $request->validate($rules);
    }

    private function resolveWhatsappPemohon(?int $keluargaId, string $namaPemohon, string $lindongan, ?int $userId = null): ?string
    {
        $query = User::query()->where('role', 'warga');

        if ($userId) {
            $user = (clone $query)->find($userId);

            if ($user?->whatsapp || $user?->phone) {
                return $user->whatsapp ?: $user->phone;
            }
        }

        if ($keluargaId) {
            $user = (clone $query)
                ->where('keluarga_id', $keluargaId)
                ->orderByRaw("CASE WHEN approval_status = 'disetujui' THEN 0 ELSE 1 END")
                ->first();

            if ($user?->whatsapp || $user?->phone) {
                return $user->whatsapp ?: $user->phone;
            }
        }

        $fallbackUser = (clone $query)
            ->where('name', $namaPemohon)
            ->where('lindongan', $lindongan)
            ->orderByRaw("CASE WHEN approval_status = 'disetujui' THEN 0 ELSE 1 END")
            ->first();

        return $fallbackUser?->whatsapp ?: $fallbackUser?->phone;
    }
}
