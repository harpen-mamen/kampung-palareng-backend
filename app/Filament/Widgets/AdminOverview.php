<?php

namespace App\Filament\Widgets;

use App\Models\Bantuan;
use App\Models\Berita;
use App\Models\Keluarga;
use App\Models\Pengumuman;
use App\Models\Rumah;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class AdminOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Data Keluarga', Keluarga::count())
                ->description('Keluarga terdaftar')
                ->color('primary'),
            Card::make('Data Rumah', Rumah::count())
                ->description('Titik rumah terdata')
                ->color('success'),
            Card::make('Program Bantuan', Bantuan::count())
                ->description('Daftar bantuan aktif & arsip')
                ->color('warning'),
            Card::make('Publikasi', Berita::count() + Pengumuman::count())
                ->description('Berita dan pengumuman')
                ->color('secondary'),
            Card::make('Pengguna Admin', User::whereIn('role', ['super_admin', 'operator', 'verifikator', 'pimpinan'])->count())
                ->description('Akun yang bisa masuk panel')
                ->color('primary'),
        ];
    }
}
