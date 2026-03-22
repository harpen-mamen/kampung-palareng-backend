<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('keluarga')
            ->when($request->filled('role'), fn ($builder) => $builder->where('role', $request->string('role')))
            ->when($request->filled('lindongan'), fn ($builder) => $builder->where('lindongan', $request->string('lindongan')))
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');

                $builder->where(function ($inner) use ($search) {
                    $inner
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->latest();

        return response()->json($query->paginate($request->integer('per_page', 20)));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:super_admin,operator,verifikator,pimpinan,warga'],
            'nik' => ['nullable', 'string', 'max:32', 'unique:users,nik'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => [Rule::requiredIf(fn () => $request->input('role') === 'warga'), 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'lindongan' => [Rule::requiredIf(fn () => $request->input('role') === 'warga'), 'string', 'max:255'],
            'keluarga_id' => [Rule::requiredIf(fn () => $request->input('role') === 'warga'), 'exists:keluarga,id'],
            'approval_status' => ['nullable', 'in:menunggu_persetujuan,disetujui,ditolak'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $payload['password'] = Hash::make($payload['password']);
        $payload['approval_status'] = $payload['role'] === 'warga'
            ? ($payload['approval_status'] ?? 'disetujui')
            : ($payload['approval_status'] ?? 'disetujui');

        return response()->json(User::create($payload)->load('keluarga'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:super_admin,operator,verifikator,pimpinan,warga'],
            'nik' => ['nullable', 'string', 'max:32', 'unique:users,nik,' . $user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => [Rule::requiredIf(fn () => $request->input('role') === 'warga'), 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'lindongan' => [Rule::requiredIf(fn () => $request->input('role') === 'warga'), 'string', 'max:255'],
            'keluarga_id' => [Rule::requiredIf(fn () => $request->input('role') === 'warga'), 'exists:keluarga,id'],
            'approval_status' => ['nullable', 'in:menunggu_persetujuan,disetujui,ditolak'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        if (empty($payload['password'])) {
            unset($payload['password']);
        } else {
            $payload['password'] = Hash::make($payload['password']);
        }

        $user->update($payload);

        return response()->json($user->fresh('keluarga'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
