<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'keluarga_id',
        'nik',
        'email',
        'phone',
        'whatsapp',
        'alamat',
        'lindongan',
        'password',
        'role',
        'approval_status',
        'approved_at',
        'approval_notes',
        'approved_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function canAccessFilament(): bool
    {
        return in_array($this->role, ['super_admin', 'operator', 'verifikator', 'pimpinan'], true);
    }

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function approver()
    {
        return $this->belongsTo(self::class, 'approved_by');
    }
}
