<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\BeritaController;
use App\Http\Controllers\Api\KeluargaBantuanController;
use App\Http\Controllers\Api\KeluargaController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PengajuanBantuanController;
use App\Http\Controllers\Api\PengajuanSuratController;
use App\Http\Controllers\Api\PengumumanController;
use App\Http\Controllers\Api\PetaController;
use App\Http\Controllers\Api\RumahController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WisataController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('berita', [BeritaController::class, 'publicIndex']);
    Route::get('berita/{slug}', [BeritaController::class, 'publicShow']);
    Route::get('wisata', [WisataController::class, 'publicIndex']);
    Route::get('pengumuman', [PengumumanController::class, 'publicIndex']);
    Route::get('hero', [SiteSettingController::class, 'publicHero']);
    Route::get('statistik', [StatistikController::class, 'publicStats']);
    Route::get('peta', [PetaController::class, 'publicMap']);
    Route::get('bantuan', [BantuanController::class, 'publicIndex']);
    Route::get('pengajuan-surat/{pengajuanSurat}', [PengajuanSuratController::class, 'show']);
    Route::get('pengajuan-bantuan/{pengajuanBantuan}', [PengajuanBantuanController::class, 'show']);
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::middleware('role:warga')->group(function () {
        Route::post('public/pengajuan-surat', [PengajuanSuratController::class, 'store']);
        Route::post('public/pengajuan-bantuan', [PengajuanBantuanController::class, 'store']);
    });
    Route::middleware('role:super_admin,operator,verifikator,pimpinan')->group(function () {
        Route::get('admin/warga-pending', [AuthController::class, 'pendingWarga']);
        Route::patch('admin/warga-pending/{user}/approval', [AuthController::class, 'updateWargaApproval']);
        Route::get('admin/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('admin/site-settings/hero', [SiteSettingController::class, 'adminHero']);
        Route::post('admin/site-settings/hero', [SiteSettingController::class, 'updateHero']);
        Route::get('admin/site-settings/surat', [SiteSettingController::class, 'adminSuratSettings']);
        Route::post('admin/site-settings/surat', [SiteSettingController::class, 'updateSuratSettings']);
        Route::get('admin/statistik', [StatistikController::class, 'adminStats']);
        Route::get('admin/peta', [PetaController::class, 'adminMap']);
        Route::get('admin/laporan/export', [LaporanController::class, 'export']);

        Route::apiResource('admin/keluarga', KeluargaController::class);
        Route::apiResource('admin/rumah', RumahController::class);
        Route::apiResource('admin/bantuan', BantuanController::class);
        Route::apiResource('admin/keluarga-bantuan', KeluargaBantuanController::class);
        Route::apiResource('admin/pengajuan-bantuan', PengajuanBantuanController::class);
        Route::patch('admin/pengajuan-bantuan/{pengajuanBantuan}/status', [PengajuanBantuanController::class, 'updateStatus']);
        Route::apiResource('admin/pengajuan-surat', PengajuanSuratController::class);
        Route::post('admin/pengajuan-surat/manual', [PengajuanSuratController::class, 'storeManual']);
        Route::patch('admin/pengajuan-surat/{pengajuanSurat}/status', [PengajuanSuratController::class, 'updateStatus']);
        Route::get('admin/pengajuan-surat/{pengajuanSurat}/document', [PengajuanSuratController::class, 'downloadDocument']);
        Route::apiResource('admin/berita', BeritaController::class);
        Route::post('admin/berita/{berita}/update', [BeritaController::class, 'update']);
        Route::post('admin/berita/{berita}/delete', [BeritaController::class, 'destroy']);
        Route::apiResource('admin/wisata', WisataController::class);
        Route::post('admin/wisata/{wisata}/update', [WisataController::class, 'update']);
        Route::post('admin/wisata/{wisata}/delete', [WisataController::class, 'destroy']);
        Route::apiResource('admin/pengumuman', PengumumanController::class);
        Route::middleware('role:super_admin')->apiResource('admin/pengguna', UserController::class);
    });
});
