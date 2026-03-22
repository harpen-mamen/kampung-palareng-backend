<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function publicHero()
    {
        return response()->json($this->transform($this->resolveSetting()));
    }

    public function adminHero()
    {
        return response()->json($this->transform($this->resolveSetting()));
    }

    public function adminSuratSettings()
    {
        $setting = $this->resolveSetting();

        return response()->json([
            'surat_templates' => $setting->surat_templates ?: $this->defaultSuratTemplates(),
            'surat_numbering' => $setting->surat_numbering ?: $this->defaultSuratNumbering(),
        ]);
    }

    public function updateSuratSettings(Request $request)
    {
        $setting = $this->resolveSetting();

        $payload = $request->validate([
            'surat_templates' => ['required', 'array'],
            'surat_numbering' => ['required', 'array'],
        ]);

        $setting->update([
            'surat_templates' => $payload['surat_templates'],
            'surat_numbering' => $payload['surat_numbering'],
        ]);

        return response()->json([
            'message' => 'Template surat berhasil diperbarui.',
            'surat_templates' => $setting->fresh()->surat_templates,
            'surat_numbering' => $setting->fresh()->surat_numbering,
        ]);
    }

    public function updateHero(Request $request)
    {
        $setting = $this->resolveSetting();

        $payload = $request->validate([
            'hero_badge' => ['required', 'string', 'max:255'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string'],
            'hero_primary_label' => ['required', 'string', 'max:255'],
            'hero_primary_url' => ['required', 'string', 'max:255'],
            'hero_secondary_label' => ['required', 'string', 'max:255'],
            'hero_secondary_url' => ['required', 'string', 'max:255'],
            'hero_panel_title' => ['required', 'string', 'max:255'],
            'hero_panel_description' => ['nullable', 'string'],
            'official_name' => ['required', 'string', 'max:255'],
            'official_position' => ['required', 'string', 'max:255'],
            'official_message' => ['nullable', 'string'],
            'hero_image' => ['nullable', 'image', 'max:2048'],
            'hero_images' => ['nullable', 'array', 'max:8'],
            'hero_images.*' => ['image', 'max:3072'],
            'hero_sections' => ['nullable', 'string'],
            'hero_section_images' => ['nullable', 'array', 'max:12'],
            'hero_section_images.*' => ['nullable', 'image', 'max:3072'],
            'official_photo' => ['nullable', 'image', 'max:2048'],
            'profile_title' => ['required', 'string', 'max:255'],
            'profile_description' => ['nullable', 'string'],
            'profile_history' => ['nullable', 'string'],
            'profile_vision_mission' => ['nullable', 'string'],
            'profile_potential' => ['nullable', 'string'],
            'profile_image' => ['nullable', 'image', 'max:3072'],
            'government_structure' => ['nullable', 'string'],
            'government_structure_photos' => ['nullable', 'array', 'max:20'],
            'government_structure_photos.*' => ['nullable', 'image', 'max:3072'],
        ]);

        if ($request->hasFile('hero_images')) {
            foreach (($setting->hero_images ?? []) as $path) {
                Storage::disk('public')->delete($path);
            }

            if ($setting->hero_image) {
                Storage::disk('public')->delete($setting->hero_image);
            }

            $storedImages = collect($request->file('hero_images'))
                ->map(fn ($file) => $file->store('site-settings', 'public'))
                ->values()
                ->all();

            $payload['hero_images'] = $storedImages;
            $payload['hero_image'] = $storedImages[0] ?? null;
        }

        if ($request->hasFile('hero_image')) {
            if ($setting->hero_image) {
                Storage::disk('public')->delete($setting->hero_image);
            }

            $payload['hero_image'] = $request->file('hero_image')->store('site-settings', 'public');
            $payload['hero_images'] = [$payload['hero_image']];
        }

        $payload['hero_sections'] = $this->prepareHeroSections($request, $setting);

        if (! empty($payload['hero_sections'])) {
            $payload['hero_badge'] = $payload['hero_sections'][0]['badge'] ?? $payload['hero_badge'];
            $payload['hero_title'] = $payload['hero_sections'][0]['title'] ?? $payload['hero_title'];
            $payload['hero_description'] = $payload['hero_sections'][0]['description'] ?? $payload['hero_description'];
            $payload['hero_primary_label'] = $payload['hero_sections'][0]['primary_label'] ?? $payload['hero_primary_label'];
            $payload['hero_primary_url'] = $payload['hero_sections'][0]['primary_url'] ?? $payload['hero_primary_url'];
            $payload['hero_secondary_label'] = $payload['hero_sections'][0]['secondary_label'] ?? $payload['hero_secondary_label'];
            $payload['hero_secondary_url'] = $payload['hero_sections'][0]['secondary_url'] ?? $payload['hero_secondary_url'];

            $sectionImages = collect($payload['hero_sections'])
                ->pluck('image')
                ->filter()
                ->values()
                ->all();

            $payload['hero_images'] = $sectionImages;
            $payload['hero_image'] = $sectionImages[0] ?? ($payload['hero_image'] ?? $setting->hero_image);
        }

        if ($request->hasFile('official_photo')) {
            if ($setting->official_photo) {
                Storage::disk('public')->delete($setting->official_photo);
            }

            $payload['official_photo'] = $request->file('official_photo')->store('site-settings', 'public');
        }

        if ($request->hasFile('profile_image')) {
            if ($setting->profile_image) {
                Storage::disk('public')->delete($setting->profile_image);
            }

            $payload['profile_image'] = $request->file('profile_image')->store('site-settings', 'public');
        }

        $payload['government_structure'] = $this->prepareGovernmentStructure($request, $setting);

        $setting->update($payload);

        return response()->json($this->transform($setting->fresh()));
    }

    private function resolveSetting(): SiteSetting
    {
        return SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'hero_badge' => 'Kabupaten Kepulauan Sangihe',
                'hero_title' => 'Website Resmi Kampung Palareng',
                'hero_description' => 'Satu pintu informasi kampung untuk masyarakat, operator, verifikator, dan pimpinan dengan fokus pada layanan yang cepat, rapi, dan transparan.',
                'hero_primary_label' => 'Ajukan Surat',
                'hero_primary_url' => '/surat',
                'hero_secondary_label' => 'Buka Peta Digital',
                'hero_secondary_url' => '/peta',
                'hero_panel_title' => 'Selayang Pandang',
                'hero_panel_description' => 'Website kampung ini hadir sebagai sarana informasi dan layanan publik berbasis digital yang mendukung transparansi, akuntabilitas, serta efektivitas tata kelola pemerintahan kampung.',
                'official_name' => 'Kapitalaung Kampung Palareng',
                'official_position' => 'Pemerintah Kampung Palareng',
                'official_message' => 'Portal ini menjadi ruang bersama untuk menghadirkan layanan kampung yang lebih terbuka, informatif, dan berbasis data.',
                'hero_images' => [],
                'hero_sections' => [
                    [
                        'badge' => 'Kabupaten Kepulauan Sangihe',
                        'title' => 'Website Resmi Kampung Palareng',
                        'description' => 'Satu pintu informasi kampung untuk masyarakat, operator, verifikator, dan pimpinan dengan fokus pada layanan yang cepat, rapi, dan transparan.',
                        'primary_label' => 'Ajukan Surat',
                        'primary_url' => '/surat',
                        'secondary_label' => 'Buka Peta Digital',
                        'secondary_url' => '/peta',
                        'image' => null,
                    ],
                ],
                'profile_title' => 'Profil Kampung Palareng',
                'profile_description' => 'Ringkasan sejarah, visi misi, pemerintahan, dan potensi Kampung Palareng.',
                'profile_history' => 'Kampung Palareng tumbuh sebagai kawasan pesisir dan kebun yang kuat dalam budaya gotong royong serta kehidupan sosial masyarakat.',
                'profile_vision_mission' => 'Mewujudkan kampung yang tertib data, responsif layanan, dan kuat dalam pembangunan sosial berbasis partisipasi warga.',
                'profile_potential' => 'Perikanan, pertanian, kebun kelapa, serta semangat kebersamaan warga menjadi kekuatan utama Kampung Palareng.',
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
                'surat_templates' => $this->defaultSuratTemplates(),
                'surat_numbering' => $this->defaultSuratNumbering(),
            ]
        );
    }

    private function transform(SiteSetting $setting): array
    {
        $data = $setting->toArray();
        $data['hero_image'] = $setting->hero_image ? asset('storage/' . $setting->hero_image) : null;
        $data['hero_images'] = collect($setting->hero_images ?: [])
            ->map(fn ($path) => asset('storage/' . $path))
            ->values()
            ->all();
        $data['hero_sections'] = collect($setting->hero_sections ?: [])
            ->map(function ($item) {
                $imagePath = $item['image'] ?? null;

                return [
                    'badge' => $item['badge'] ?? '',
                    'title' => $item['title'] ?? '',
                    'description' => $item['description'] ?? '',
                    'primary_label' => $item['primary_label'] ?? '',
                    'primary_url' => $item['primary_url'] ?? '',
                    'secondary_label' => $item['secondary_label'] ?? '',
                    'secondary_url' => $item['secondary_url'] ?? '',
                    'image' => $imagePath ? asset('storage/' . $imagePath) : null,
                    'image_path' => $imagePath,
                ];
            })
            ->values()
            ->all();

        if (empty($data['hero_images']) && $data['hero_image']) {
            $data['hero_images'] = [$data['hero_image']];
        }

        if (empty($data['hero_sections'])) {
            $data['hero_sections'] = [[
                'badge' => $setting->hero_badge,
                'title' => $setting->hero_title,
                'description' => $setting->hero_description,
                'primary_label' => $setting->hero_primary_label,
                'primary_url' => $setting->hero_primary_url,
                'secondary_label' => $setting->hero_secondary_label,
                'secondary_url' => $setting->hero_secondary_url,
                'image' => $data['hero_image'],
                'image_path' => $setting->hero_image,
            ]];
        }

        $data['official_photo'] = $setting->official_photo ? asset('storage/' . $setting->official_photo) : null;
        $data['profile_image'] = $setting->profile_image ? asset('storage/' . $setting->profile_image) : null;
        $data['government_structure'] = collect($setting->government_structure ?: [])
            ->map(function ($item) {
                $photoPath = $item['photo'] ?? null;

                return [
                    'position' => $item['position'] ?? '',
                    'name' => $item['name'] ?? '',
                    'photo' => $photoPath ? asset('storage/' . $photoPath) : null,
                    'photo_path' => $photoPath,
                ];
            })
            ->values()
            ->all();

        return $data;
    }

    private function prepareGovernmentStructure(Request $request, SiteSetting $setting): array
    {
        $decoded = json_decode($request->input('government_structure', '[]'), true);

        if (! is_array($decoded)) {
            $decoded = [];
        }

        $existing = collect($setting->government_structure ?: [])->values();
        $files = collect($request->file('government_structure_photos', []));

        $prepared = collect($decoded)
            ->map(function ($item, $index) use ($existing, $files) {
                $current = $existing->get($index, []);
                $photoPath = $item['photo_path'] ?? ($current['photo'] ?? null);
                $uploadedFile = $files->get($index);

                if ($uploadedFile) {
                    if ($photoPath) {
                        Storage::disk('public')->delete($photoPath);
                    }

                    $photoPath = $uploadedFile->store('site-settings/structure', 'public');
                }

                return [
                    'position' => $item['position'] ?? '',
                    'name' => $item['name'] ?? '',
                    'photo' => $photoPath,
                ];
            })
            ->filter(fn ($item) => filled($item['position']) || filled($item['name']) || filled($item['photo']))
            ->values();

        $retainedPaths = $prepared->pluck('photo')->filter()->values()->all();

        $existing->pluck('photo')
            ->filter()
            ->reject(fn ($path) => in_array($path, $retainedPaths, true))
            ->each(fn ($path) => Storage::disk('public')->delete($path));

        return $prepared->all();
    }

    private function prepareHeroSections(Request $request, SiteSetting $setting): array
    {
        $decoded = json_decode($request->input('hero_sections', '[]'), true);

        if (! is_array($decoded) || empty($decoded)) {
            $decoded = [[
                'badge' => $request->input('hero_badge'),
                'title' => $request->input('hero_title'),
                'description' => $request->input('hero_description'),
                'primary_label' => $request->input('hero_primary_label'),
                'primary_url' => $request->input('hero_primary_url'),
                'secondary_label' => $request->input('hero_secondary_label'),
                'secondary_url' => $request->input('hero_secondary_url'),
                'image_path' => $setting->hero_image,
            ]];
        }

        $existing = collect($setting->hero_sections ?: [])->values();
        $files = collect($request->file('hero_section_images', []));

        $prepared = collect($decoded)
            ->map(function ($item, $index) use ($existing, $files) {
                $current = $existing->get($index, []);
                $imagePath = $item['image_path'] ?? ($current['image'] ?? null);
                $uploadedFile = $files->get($index);

                if ($uploadedFile) {
                    if ($imagePath) {
                        Storage::disk('public')->delete($imagePath);
                    }

                    $imagePath = $uploadedFile->store('site-settings/hero-sections', 'public');
                }

                return [
                    'badge' => $item['badge'] ?? '',
                    'title' => $item['title'] ?? '',
                    'description' => $item['description'] ?? '',
                    'primary_label' => $item['primary_label'] ?? '',
                    'primary_url' => $item['primary_url'] ?? '',
                    'secondary_label' => $item['secondary_label'] ?? '',
                    'secondary_url' => $item['secondary_url'] ?? '',
                    'image' => $imagePath,
                ];
            })
            ->filter(fn ($item) => filled($item['title']) || filled($item['description']) || filled($item['image']))
            ->values();

        $retainedPaths = $prepared->pluck('image')->filter()->values()->all();

        $existing->pluck('image')
            ->filter()
            ->reject(fn ($path) => in_array($path, $retainedPaths, true))
            ->each(fn ($path) => Storage::disk('public')->delete($path));

        return $prepared->all();
    }

    private function defaultSuratTemplates(): array
    {
        return [
            'Surat Keterangan Domisili' => 'Bahwa nama tersebut benar berdomisili di Kampung Palareng dan berdasarkan pengetahuan serta data administrasi pemerintah kampung, yang bersangkutan tercatat sebagai warga dalam wilayah administrasi kampung. Surat keterangan ini diberikan {purpose}.',
            'Surat Keterangan Usaha' => 'Bahwa nama tersebut benar merupakan warga Kampung Palareng yang menjalankan kegiatan usaha secara mandiri di wilayah kampung sesuai keterangan yang diketahui pemerintah kampung. Surat keterangan ini diberikan {purpose}.',
            'Surat Keterangan Tidak Mampu' => 'Bahwa berdasarkan data kampung dan pengetahuan pemerintah kampung, yang bersangkutan termasuk warga yang memerlukan dukungan administrasi sosial. Surat keterangan ini diberikan {purpose}.',
            'Surat Pengantar SKCK' => 'Bahwa nama tersebut benar merupakan warga Kampung Palareng dan yang bersangkutan berkelakuan baik sepanjang pengetahuan pemerintah kampung. Surat pengantar ini diberikan {purpose} sebagai kelengkapan administrasi pengurusan SKCK.',
            'Surat Keterangan Penghasilan Orang Tua' => 'Bahwa berdasarkan keterangan yang diberikan kepada pemerintah kampung, penghasilan orang tua/wali yang bersangkutan diketahui sesuai dengan kondisi ekonomi keluarga yang tercatat dalam administrasi kampung. Surat keterangan ini diberikan {purpose}.',
            'Surat Keterangan Belum Menikah' => 'Bahwa berdasarkan data administrasi kampung dan keterangan yang diketahui pemerintah kampung, yang bersangkutan sampai saat surat ini diterbitkan berstatus belum menikah. Surat keterangan ini diberikan {purpose}.',
            'Surat Keterangan Kelahiran' => 'Bahwa berdasarkan keterangan keluarga dan data yang dilaporkan kepada pemerintah kampung, telah terjadi kelahiran sebagaimana dilaporkan oleh pemohon. Surat keterangan ini diberikan {purpose}.',
            'Surat Keterangan Kematian' => 'Bahwa berdasarkan laporan keluarga dan keterangan yang diketahui pemerintah kampung, telah terjadi peristiwa kematian sebagaimana dilaporkan kepada pemerintah kampung. Surat keterangan ini diberikan {purpose}.',
            'Surat Keterangan Ahli Waris' => 'Bahwa berdasarkan keterangan keluarga, saksi-saksi, dan data yang diketahui pemerintah kampung, nama yang diajukan tercatat sebagai pihak keluarga/ahli waris dari yang bersangkutan untuk keperluan administrasi. Surat keterangan ini diberikan {purpose}, dengan ketentuan dapat dimintakan verifikasi lanjutan sesuai kebutuhan instansi tujuan.',
            'Surat Keterangan Janda/Duda' => 'Bahwa berdasarkan data administrasi kampung dan keterangan yang diketahui pemerintah kampung, yang bersangkutan berstatus janda/duda. Surat keterangan ini diberikan {purpose}.',
            'Surat Keterangan Beda Nama' => 'Bahwa terdapat perbedaan penulisan nama pada dokumen administrasi yang diajukan, namun berdasarkan keterangan pemohon dan data yang diketahui pemerintah kampung, identitas tersebut mengarah pada orang yang sama. Surat keterangan ini diberikan {purpose}.',
            'Surat Pengantar KTP' => 'Bahwa nama tersebut benar merupakan warga Kampung Palareng dan diberikan surat pengantar ini {purpose} sebagai kelengkapan administrasi pengurusan KTP.',
            'Surat Pengantar Kartu Keluarga' => 'Bahwa nama tersebut benar merupakan warga Kampung Palareng dan diberikan surat pengantar ini {purpose} sebagai kelengkapan administrasi pengurusan Kartu Keluarga.',
            'Surat Pengantar Nikah' => 'Bahwa nama tersebut benar merupakan warga Kampung Palareng dan diberikan surat pengantar ini {purpose} sebagai kelengkapan administrasi pengurusan pernikahan pada instansi yang berwenang.',
            'Surat Pengantar Pindah' => 'Bahwa nama tersebut benar merupakan warga Kampung Palareng dan diberikan surat pengantar ini {purpose} sebagai kelengkapan administrasi perpindahan penduduk.',
            'Surat Pengantar' => 'Bahwa nama tersebut benar merupakan warga Kampung Palareng dan diberikan surat pengantar ini {purpose}.',
        ];
    }

    private function defaultSuratNumbering(): array
    {
        return [
            'Surat Keterangan Domisili' => 'SKD',
            'Surat Keterangan Usaha' => 'SKU',
            'Surat Keterangan Tidak Mampu' => 'SKTM',
            'Surat Pengantar SKCK' => 'SPS',
            'Surat Keterangan Penghasilan Orang Tua' => 'SKPOT',
            'Surat Keterangan Belum Menikah' => 'SKBM',
            'Surat Keterangan Kelahiran' => 'SKL',
            'Surat Keterangan Kematian' => 'SKM',
            'Surat Keterangan Ahli Waris' => 'SKAW',
            'Surat Keterangan Janda/Duda' => 'SKJD',
            'Surat Keterangan Beda Nama' => 'SKBN',
            'Surat Pengantar KTP' => 'SPKTP',
            'Surat Pengantar Kartu Keluarga' => 'SPKK',
            'Surat Pengantar Nikah' => 'SPN',
            'Surat Pengantar Pindah' => 'SPP',
            'Surat Pengantar' => 'SP',
        ];
    }
}
