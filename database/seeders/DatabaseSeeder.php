<?php

namespace Database\Seeders;

use App\Models\Bantuan;
use App\Models\Berita;
use App\Models\FasilitasUmum;
use App\Models\Keluarga;
use App\Models\KeluargaBantuan;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanSurat;
use App\Models\Pengumuman;
use App\Models\Rumah;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Wisata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::query()->delete();
        KeluargaBantuan::query()->delete();
        PengajuanBantuan::query()->delete();
        PengajuanSurat::query()->delete();
        Rumah::query()->delete();
        Bantuan::query()->delete();
        Berita::query()->delete();
        Pengumuman::query()->delete();
        Wisata::query()->delete();
        FasilitasUmum::query()->delete();
        SiteSetting::query()->delete();
        Keluarga::query()->delete();

        User::create([
            'name' => 'Super Admin Palareng',
            'email' => 'admin@palareng.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        User::create([
            'name' => 'Operator Kampung',
            'email' => 'operator@palareng.id',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        $keluarga = collect([
            ['kode_keluarga' => 'KPLR-001', 'nama_kepala_keluarga' => 'Yohanis Tamon', 'alamat' => 'Dusun pusat kampung dekat balai pertemuan', 'lindongan' => 'Lindongan 1', 'jumlah_anggota' => 5, 'status_ekonomi' => 'Menengah', 'pekerjaan_utama' => 'Nelayan', 'kategori_rumah' => 'Permanen', 'status_dtks' => true, 'catatan_petugas' => 'Keluarga aktif dalam kegiatan kampung.'],
            ['kode_keluarga' => 'KPLR-002', 'nama_kepala_keluarga' => 'Marta Pangemanan', 'alamat' => 'Jalur pesisir utara', 'lindongan' => 'Lindongan 2', 'jumlah_anggota' => 4, 'status_ekonomi' => 'Rentan', 'pekerjaan_utama' => 'Petani', 'kategori_rumah' => 'Semi permanen', 'status_dtks' => true, 'catatan_petugas' => 'Membutuhkan perbaikan atap rumah.'],
            ['kode_keluarga' => 'KPLR-003', 'nama_kepala_keluarga' => 'Adrianus Bawole', 'alamat' => 'Dekat jalan kebun kelapa', 'lindongan' => 'Lindongan 3', 'jumlah_anggota' => 6, 'status_ekonomi' => 'Menengah', 'pekerjaan_utama' => 'Pedagang', 'kategori_rumah' => 'Permanen', 'status_dtks' => false, 'catatan_petugas' => 'Usaha kios keluarga berkembang.'],
            ['kode_keluarga' => 'KPLR-004', 'nama_kepala_keluarga' => 'Ria Maramis', 'alamat' => 'Arah batas kampung timur', 'lindongan' => 'Lindongan 4', 'jumlah_anggota' => 3, 'status_ekonomi' => 'Pra sejahtera', 'pekerjaan_utama' => 'Ibu Rumah Tangga', 'kategori_rumah' => 'Kayu', 'status_dtks' => true, 'catatan_petugas' => 'Prioritas untuk program bantuan.'],
        ])->map(fn ($item) => Keluarga::create($item));

        collect([
            ['keluarga_id' => $keluarga[0]->id, 'alamat_singkat' => 'Lorong balai kampung', 'lindongan' => 'Lindongan 1', 'latitude' => 3.5824100, 'longitude' => 125.4941100, 'kategori_rumah' => 'Permanen', 'jumlah_penghuni' => 5, 'catatan_petugas' => 'Dekat balai kampung.'],
            ['keluarga_id' => $keluarga[1]->id, 'alamat_singkat' => 'Pesisir utara', 'lindongan' => 'Lindongan 2', 'latitude' => 3.5812600, 'longitude' => 125.4952200, 'kategori_rumah' => 'Semi permanen', 'jumlah_penghuni' => 4, 'catatan_petugas' => 'Perlu pemetaan drainase.'],
            ['keluarga_id' => $keluarga[2]->id, 'alamat_singkat' => 'Jalan kebun', 'lindongan' => 'Lindongan 3', 'latitude' => 3.5801800, 'longitude' => 125.4938000, 'kategori_rumah' => 'Permanen', 'jumlah_penghuni' => 6, 'catatan_petugas' => 'Akses kendaraan roda dua mudah.'],
            ['keluarga_id' => $keluarga[3]->id, 'alamat_singkat' => 'Batas timur', 'lindongan' => 'Lindongan 4', 'latitude' => 3.5794500, 'longitude' => 125.4960500, 'kategori_rumah' => 'Kayu', 'jumlah_penghuni' => 3, 'catatan_petugas' => 'Dekat lahan kebun keluarga.'],
        ])->each(fn ($item) => Rumah::create($item));

        $bantuan = collect([
            ['nama_bantuan' => 'BLT Dana Desa', 'kategori' => 'Tunai', 'sumber' => 'Dana Desa', 'periode' => '2026 Triwulan I', 'status' => 'aktif', 'deskripsi' => 'Bantuan langsung tunai untuk keluarga rentan.'],
            ['nama_bantuan' => 'Bantuan Pangan', 'kategori' => 'Sembako', 'sumber' => 'Pemerintah Kabupaten', 'periode' => '2026 Semester I', 'status' => 'aktif', 'deskripsi' => 'Distribusi paket pangan pokok.'],
            ['nama_bantuan' => 'Stimulan Perbaikan Rumah', 'kategori' => 'Perumahan', 'sumber' => 'Provinsi', 'periode' => '2026', 'status' => 'perencanaan', 'deskripsi' => 'Program stimulan peningkatan kelayakan rumah.'],
        ])->map(fn ($item) => Bantuan::create($item));

        KeluargaBantuan::create(['keluarga_id' => $keluarga[1]->id, 'bantuan_id' => $bantuan[0]->id, 'status_penerima' => 'aktif', 'tanggal_mulai' => '2026-01-10', 'catatan' => 'Penerima aktif triwulan pertama.']);
        KeluargaBantuan::create(['keluarga_id' => $keluarga[3]->id, 'bantuan_id' => $bantuan[1]->id, 'status_penerima' => 'aktif', 'tanggal_mulai' => '2026-02-03', 'catatan' => 'Distribusi tahap Februari.']);

        collect([
            ['judul' => 'Gotong Royong Pembersihan Lingkungan Kampung Palareng', 'kategori' => 'Kegiatan Kampung', 'ringkasan' => 'Warga bersama pemerintah kampung melakukan kerja bakti pembersihan lingkungan.', 'isi' => 'Kegiatan gotong royong dilaksanakan pada akhir pekan untuk menjaga kebersihan jalur utama, saluran air, dan area publik di Kampung Palareng.', 'status_publish' => 'publish'],
            ['judul' => 'Pendataan Rumah Warga untuk Pembaruan Peta Digital', 'kategori' => 'Pengumuman', 'ringkasan' => 'Operator kampung melakukan pembaruan data rumah dan titik koordinat.', 'isi' => 'Pemerintah kampung menginformasikan bahwa pembaruan data spasial dan rumah warga akan dilakukan secara bertahap pada seluruh lindongan.', 'status_publish' => 'publish'],
            ['judul' => 'Persiapan Musyawarah Kampung Tahun 2026', 'kategori' => 'Pemerintahan', 'ringkasan' => 'Agenda musyawarah kampung difokuskan pada layanan sosial dan infrastruktur.', 'isi' => 'Musyawarah kampung akan membahas prioritas penguatan layanan surat, bantuan, dan pembenahan data statistik internal.', 'status_publish' => 'draft'],
        ])->each(function ($item) {
            $item['slug'] = Str::slug($item['judul']);
            Berita::create($item);
        });

        collect([
            ['nama' => 'Pantai Batu Emas', 'lokasi' => 'Pesisir utara Kampung Palareng', 'deskripsi' => 'Hamparan pantai yang cocok untuk menikmati senja dan aktivitas perahu warga.', 'status_publish' => 'publish'],
            ['nama' => 'Titik Pandang Bukit Kelapa', 'lokasi' => 'Arah kebun lindongan 3', 'deskripsi' => 'Area pandang sederhana untuk melihat garis pantai dan permukiman kampung dari ketinggian.', 'status_publish' => 'publish'],
            ['nama' => 'Dermaga Kampung', 'lokasi' => 'Pusat kampung dekat balai pertemuan', 'deskripsi' => 'Titik wisata ringan untuk menikmati suasana pesisir dan pergerakan nelayan setiap pagi.', 'status_publish' => 'draft'],
        ])->each(fn ($item) => Wisata::create($item));

        Pengumuman::create(['judul' => 'Jadwal Pelayanan Surat Mingguan', 'isi' => 'Pelayanan surat dibuka setiap Senin sampai Kamis pukul 08.00-14.00 WITA.', 'status_publish' => 'publish']);
        Pengumuman::create(['judul' => 'Imbauan Pemutakhiran Data Keluarga', 'isi' => 'Warga diminta melaporkan perubahan data kepada operator kampung melalui kantor kampung.', 'status_publish' => 'publish']);

        PengajuanSurat::create(['nama_pemohon' => 'Marta Pangemanan', 'jenis_surat' => 'Surat Keterangan Domisili', 'alamat' => 'Jalur pesisir utara', 'lindongan' => 'Lindongan 2', 'status' => 'diperiksa', 'catatan_admin' => 'Dokumen sedang diverifikasi.']);
        PengajuanSurat::create(['nama_pemohon' => 'Ria Maramis', 'jenis_surat' => 'Surat Keterangan Usaha', 'alamat' => 'Arah batas kampung timur', 'lindongan' => 'Lindongan 4', 'status' => 'diajukan', 'catatan_admin' => null]);

        PengajuanBantuan::create(['nama_pemohon' => 'Ria Maramis', 'alamat' => 'Arah batas kampung timur', 'lindongan' => 'Lindongan 4', 'jenis_bantuan' => 'Stimulan Perbaikan Rumah', 'keterangan' => 'Memerlukan bantuan perbaikan dinding dan atap rumah.', 'status_pengajuan' => 'diverifikasi', 'catatan_admin' => 'Sudah masuk daftar verifikasi lapangan.', 'keluarga_id' => $keluarga[3]->id]);
        PengajuanBantuan::create(['nama_pemohon' => 'Yohanis Tamon', 'alamat' => 'Dusun pusat kampung dekat balai pertemuan', 'lindongan' => 'Lindongan 1', 'jenis_bantuan' => 'Bantuan Pangan', 'keterangan' => 'Pengajuan untuk kebutuhan keluarga lansia.', 'status_pengajuan' => 'diajukan', 'catatan_admin' => null, 'keluarga_id' => $keluarga[0]->id]);

        FasilitasUmum::create(['nama' => 'Kantor Kampung Palareng', 'kategori' => 'Pemerintahan', 'deskripsi' => 'Pusat layanan administrasi kampung.', 'latitude' => 3.5820000, 'longitude' => 125.4946000]);
        FasilitasUmum::create(['nama' => 'Balai Pertemuan Warga', 'kategori' => 'Sosial', 'deskripsi' => 'Lokasi rapat dan kegiatan masyarakat.', 'latitude' => 3.5817000, 'longitude' => 125.4940000]);

        SiteSetting::create([
            'id' => 1,
            'hero_badge' => 'Kabupaten Kepulauan Sangihe',
            'hero_title' => 'Website Resmi Kampung Palareng',
            'hero_description' => 'Portal resmi Kampung Palareng menghadirkan layanan surat, pengajuan bantuan, informasi kampung, dan peta digital yang tertata rapi untuk masyarakat maupun admin kampung.',
            'hero_primary_label' => 'Ajukan Surat',
            'hero_primary_url' => '/surat',
            'hero_secondary_label' => 'Lihat Peta Digital',
            'hero_secondary_url' => '/peta',
            'hero_panel_title' => 'Selayang Pandang',
            'hero_panel_description' => 'Website kampung ini disiapkan sebagai sarana informasi dan layanan publik berbasis digital, agar data kampung lebih mudah diakses, dipahami, dan dimanfaatkan dalam pembangunan.',
            'official_name' => 'Kapitalaung Kampung Palareng',
            'official_position' => 'Pemerintah Kampung Palareng',
            'official_message' => 'Dengan semangat pelayanan dan keterbukaan informasi, portal ini diharapkan menjadi wajah digital kampung yang rapi, informatif, dan bermanfaat bagi seluruh warga.',
            'hero_images' => [],
            'profile_title' => 'Profil Kampung Palareng',
            'profile_description' => 'Ringkasan sejarah, visi misi, pemerintahan, dan potensi Kampung Palareng.',
            'profile_history' => 'Kampung Palareng berkembang sebagai kawasan masyarakat pesisir dan kebun yang kuat dalam budaya gotong royong.',
            'profile_vision_mission' => 'Mewujudkan kampung yang tertib data, responsif layanan, dan kuat dalam pembangunan sosial.',
            'profile_potential' => 'Perikanan, pertanian, kebun kelapa, dan partisipasi sosial warga menjadi kekuatan utama kampung.',
            'government_structure' => [
                [
                    'position' => 'Kapitalaung',
                    'name' => 'Kapitalaung Kampung Palareng',
                    'photo' => null,
                ],
                [
                    'position' => 'Sekretaris Kampung',
                    'name' => 'Sekretaris Kampung Palareng',
                    'photo' => null,
                ],
            ],
        ]);
    }
}
