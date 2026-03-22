<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nik' => ['required', 'string', 'max:32', 'unique:users,nik'],
            'nama_keluarga' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'whatsapp' => ['required', 'string', 'max:50'],
            'lindongan' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $keluarga = Keluarga::query()
            ->where('nama_kepala_keluarga', $payload['nama_keluarga'])
            ->where('lindongan', $payload['lindongan'])
            ->first();

        if (! $keluarga) {
            return response()->json([
                'message' => 'Nama keluarga tidak ditemukan atau tidak sesuai dengan lindongan yang dipilih.',
            ], 422);
        }

        $user = User::create([
            'name' => $payload['name'],
            'keluarga_id' => $keluarga->id,
            'nik' => $payload['nik'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'whatsapp' => $payload['whatsapp'],
            'alamat' => $keluarga->alamat,
            'lindongan' => $keluarga->lindongan,
            'password' => Hash::make($payload['password']),
            'role' => 'warga',
            'approval_status' => 'menunggu_persetujuan',
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil dikirim dan menunggu persetujuan admin.',
            'user' => $user->load('keluarga'),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password tidak valid.'], 422);
        }

        if ($user->role === 'warga' && $user->approval_status !== 'disetujui') {
            return response()->json([
                'message' => 'Akun Anda belum disetujui admin. Silakan tunggu konfirmasi.',
            ], 403);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $user->createToken('portal-kampung')->plainTextToken,
            'user' => $user->load('keluarga'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()?->load('keluarga'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function pendingWarga(Request $request): JsonResponse
    {
        $rows = User::query()
            ->with('keluarga')
            ->where('role', 'warga')
            ->where('approval_status', 'menunggu_persetujuan')
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($rows);
    }

    public function updateWargaApproval(Request $request, User $user): JsonResponse
    {
        $payload = $request->validate([
            'approval_status' => ['required', 'in:menunggu_persetujuan,disetujui,ditolak'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        if ($user->role !== 'warga') {
            return response()->json(['message' => 'Persetujuan hanya berlaku untuk akun warga.'], 422);
        }

        $user->update([
            'approval_status' => $payload['approval_status'],
            'approval_notes' => $payload['approval_notes'] ?? null,
            'approved_at' => $payload['approval_status'] === 'disetujui' ? now() : null,
            'approved_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Status persetujuan warga berhasil diperbarui.',
            'user' => $user->fresh('keluarga', 'approver'),
        ]);
    }
}
