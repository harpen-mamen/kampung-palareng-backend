<?php

namespace App\Console\Commands;

use App\Models\PengajuanBantuan;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BackfillPengajuanBantuanWhatsapp extends Command
{
    protected $signature = 'bantuan:backfill-whatsapp {--dry-run : Tampilkan hasil tanpa menyimpan perubahan}';

    protected $description = 'Mengisi user_id dan whatsapp_pemohon pada pengajuan bantuan lama dari data akun warga yang sudah registrasi.';

    public function handle(): int
    {
        $hasUserIdColumn = Schema::hasColumn('pengajuan_bantuan', 'user_id');
        $hasWhatsappColumn = Schema::hasColumn('pengajuan_bantuan', 'whatsapp_pemohon');

        if (! $hasUserIdColumn && ! $hasWhatsappColumn) {
            $this->error('Kolom user_id dan whatsapp_pemohon belum tersedia pada tabel pengajuan_bantuan.');

            return self::FAILURE;
        }

        $query = PengajuanBantuan::query()
            ->when($hasUserIdColumn, fn ($builder) => $builder->whereNull('user_id'))
            ->when($hasWhatsappColumn, function ($builder) use ($hasUserIdColumn) {
                if ($hasUserIdColumn) {
                    $builder->orWhereNull('whatsapp_pemohon')
                        ->orWhere('whatsapp_pemohon', '');

                    return;
                }

                $builder->whereNull('whatsapp_pemohon')
                    ->orWhere('whatsapp_pemohon', '');
            })
            ->orderBy('id');

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('Tidak ada pengajuan bantuan yang perlu di-backfill.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($rows as $pengajuan) {
            $user = $this->findRelatedWarga($pengajuan);

            if (! $user) {
                $this->warn("Lewati pengajuan #{$pengajuan->id}: akun warga terkait tidak ditemukan.");
                continue;
            }

            $payload = [];

            if ($hasUserIdColumn && empty($pengajuan->user_id)) {
                $payload['user_id'] = $user->id;
            }

            if ($hasWhatsappColumn && empty($pengajuan->whatsapp_pemohon) && $user->whatsapp) {
                $payload['whatsapp_pemohon'] = $user->whatsapp;
            }

            if ($payload === []) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("Dry run pengajuan #{$pengajuan->id}: " . json_encode($payload, JSON_UNESCAPED_UNICODE));
            } else {
                $pengajuan->update($payload);
            }

            $updated++;
        }

        $this->info(
            $this->option('dry-run')
                ? "Simulasi selesai. {$updated} pengajuan bantuan dapat diperbarui."
                : "Backfill selesai. {$updated} pengajuan bantuan berhasil diperbarui."
        );

        return self::SUCCESS;
    }

    private function findRelatedWarga(PengajuanBantuan $pengajuan): ?User
    {
        $baseQuery = User::query()
            ->where('role', 'warga')
            ->orderByRaw("CASE WHEN approval_status = 'disetujui' THEN 0 ELSE 1 END")
            ->latest('id');

        if (! empty($pengajuan->user_id)) {
            $user = (clone $baseQuery)->find($pengajuan->user_id);

            if ($user) {
                return $user;
            }
        }

        if (! empty($pengajuan->keluarga_id)) {
            $user = (clone $baseQuery)
                ->where('keluarga_id', $pengajuan->keluarga_id)
                ->first();

            if ($user) {
                return $user;
            }
        }

        return (clone $baseQuery)
            ->where('name', $pengajuan->nama_pemohon)
            ->where('lindongan', $pengajuan->lindongan)
            ->first();
    }
}
