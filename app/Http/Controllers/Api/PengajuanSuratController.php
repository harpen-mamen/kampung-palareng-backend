<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSurat;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PengajuanSuratController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanSurat::query()
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');

                $builder->where(function ($inner) use ($search) {
                    $inner
                        ->where('nama_pemohon', 'like', '%' . $search . '%')
                        ->orWhere('jenis_surat', 'like', '%' . $search . '%')
                        ->orWhere('nomor_surat', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->when($request->filled('lindongan'), fn ($builder) => $builder->where('lindongan', $request->string('lindongan')))
            ->when($request->boolean('archived_only'), fn ($builder) => $builder->whereNotNull('arsip_surat_at'))
            ->latest();

        return response()->json($query->paginate($request->integer('per_page', 10)));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $payload = $this->validatePayload($request, false);
        $payload['nama_pemohon'] = $user->name;
        $payload['alamat'] = $user->alamat ?? $payload['alamat'];
        $payload['lindongan'] = $user->lindongan ?? $payload['lindongan'];
        $payload['whatsapp_pemohon'] = $user->whatsapp ?? $user->phone;
        $payload['lampiran'] = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('pengajuan-surat', 'public')
            : null;

        $pengajuan = PengajuanSurat::create($payload);

        return response()->json($pengajuan, 201);
    }

    public function storeManual(Request $request)
    {
        $payload = $request->validate([
            'nama_pemohon' => ['required', 'string', 'max:255'],
            'jenis_surat' => ['required', 'string', 'max:255', 'in:' . implode(',', $this->allowedJenisSurat())],
            'keperluan' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'lindongan' => ['required', 'in:Lindongan 1,Lindongan 2,Lindongan 3,Lindongan 4'],
            'whatsapp_pemohon' => ['required', 'string', 'max:50'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        $payload['status'] = 'diperiksa';

        $pengajuan = PengajuanSurat::create($payload);

        return response()->json($pengajuan, 201);
    }

    public function show(PengajuanSurat $pengajuanSurat)
    {
        return response()->json($pengajuanSurat);
    }

    public function downloadDocument(PengajuanSurat $pengajuanSurat)
    {
        if (! $pengajuanSurat->file_surat || ! Storage::disk('public')->exists($pengajuanSurat->file_surat)) {
            return response()->json([
                'message' => 'Dokumen surat belum tersedia. Setujui surat terlebih dahulu agar arsip otomatis dibuat.',
            ], 404);
        }

        $filename = ($pengajuanSurat->nomor_surat ?: 'surat-kampung') . '.pdf';

        return Response::download(
            Storage::disk('public')->path($pengajuanSurat->file_surat),
            str_replace('/', '-', $filename),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function update(Request $request, PengajuanSurat $pengajuanSurat)
    {
        $payload = $this->validatePayload($request, true);

        if ($request->hasFile('lampiran')) {
            if ($pengajuanSurat->lampiran) {
                Storage::disk('public')->delete($pengajuanSurat->lampiran);
            }

            $payload['lampiran'] = $request->file('lampiran')->store('pengajuan-surat', 'public');
        }

        $pengajuanSurat->update($payload);

        return response()->json($pengajuanSurat);
    }

    public function destroy(PengajuanSurat $pengajuanSurat)
    {
        if ($pengajuanSurat->lampiran) {
            Storage::disk('public')->delete($pengajuanSurat->lampiran);
        }

        $pengajuanSurat->delete();

        return response()->json(['message' => 'Pengajuan surat berhasil dihapus.']);
    }

    public function updateStatus(Request $request, PengajuanSurat $pengajuanSurat)
    {
        $payload = $request->validate([
            'status' => ['required', 'in:diajukan,diperiksa,perlu_perbaikan,disetujui,selesai'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        if ($payload['status'] === 'disetujui') {
            $payload = array_merge(
                $payload,
                $this->buildApprovedDocumentPayload($request, $pengajuanSurat)
            );
        }

        $pengajuanSurat->update($payload);

        return response()->json($pengajuanSurat->fresh('approver'));
    }

    private function validatePayload(Request $request, bool $includeStatus): array
    {
        $jenisSuratRules = ['required', 'string', 'max:255', 'in:' . implode(',', $this->allowedJenisSurat())];

        $rules = [
            'jenis_surat' => $jenisSuratRules,
            'keperluan' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'lindongan' => ['nullable', 'in:Lindongan 1,Lindongan 2,Lindongan 3,Lindongan 4'],
            'lampiran' => ['nullable', 'file', 'max:2048'],
            'catatan_admin' => ['nullable', 'string'],
        ];

        if ($includeStatus) {
            $rules['status'] = ['required', 'in:diajukan,diperiksa,perlu_perbaikan,disetujui,selesai'];
        }

        return $request->validate($rules);
    }

    private function allowedJenisSurat(): array
    {
        return [
            'Surat Keterangan Domisili',
            'Surat Keterangan Usaha',
            'Surat Keterangan Tidak Mampu',
            'Surat Pengantar SKCK',
            'Surat Keterangan Penghasilan Orang Tua',
            'Surat Keterangan Belum Menikah',
            'Surat Keterangan Kelahiran',
            'Surat Keterangan Kematian',
            'Surat Keterangan Ahli Waris',
            'Surat Keterangan Janda/Duda',
            'Surat Keterangan Beda Nama',
            'Surat Pengantar KTP',
            'Surat Pengantar Kartu Keluarga',
            'Surat Pengantar Nikah',
            'Surat Pengantar Pindah',
            'Surat Pengantar',
        ];
    }

    private function buildApprovedDocumentPayload(Request $request, PengajuanSurat $pengajuanSurat): array
    {
        $today = now();
        $setting = SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'hero_badge' => 'Kabupaten Kepulauan Sangihe',
                'hero_title' => 'Website Resmi Kampung Palareng',
                'hero_primary_label' => 'Ajukan Surat',
                'hero_primary_url' => '/surat',
                'hero_secondary_label' => 'Buka Peta Digital',
                'hero_secondary_url' => '/peta',
                'hero_panel_title' => 'Selayang Pandang',
                'official_name' => 'Kapitalaung Kampung Palareng',
                'official_position' => 'Pemerintah Kampung Palareng',
                'profile_title' => 'Profil Kampung Palareng',
                'surat_templates' => $this->defaultSuratTemplates(),
                'surat_numbering' => $this->defaultSuratNumbering(),
            ]
        );

        $nomorUrut = $pengajuanSurat->nomor_urut_surat
            ?? (PengajuanSurat::query()
                ->where('jenis_surat', $pengajuanSurat->jenis_surat)
                ->whereYear('tanggal_surat', $today->year)
                ->max('nomor_urut_surat') ?? 0) + 1;

        $tanggalSurat = $pengajuanSurat->tanggal_surat instanceof Carbon
            ? $pengajuanSurat->tanggal_surat
            : $today->copy();

        $namaPenandatangan = $setting->official_name ?: 'Kapitalaung Kampung Palareng';
        $jabatanPenandatangan = $setting->official_position ?: 'Pemerintah Kampung Palareng';

        $nomorSurat = $pengajuanSurat->nomor_surat ?: $this->formatNomorSurat($pengajuanSurat, $nomorUrut, $today, $setting);
        $namaPenandatangan = $pengajuanSurat->nama_penandatangan ?: $namaPenandatangan;
        $jabatanPenandatangan = $pengajuanSurat->jabatan_penandatangan ?: $jabatanPenandatangan;
        $isiSurat = $this->generateSuratContent(
            pengajuanSurat: $pengajuanSurat,
            nomorSurat: $nomorSurat,
            tanggalSurat: $tanggalSurat,
            namaPenandatangan: $namaPenandatangan,
            jabatanPenandatangan: $jabatanPenandatangan,
        );
        $fileSurat = $this->generateAndStorePdf(
            pengajuanSurat: $pengajuanSurat,
            nomorSurat: $nomorSurat,
            tanggalSurat: $tanggalSurat,
            namaPenandatangan: $namaPenandatangan,
            jabatanPenandatangan: $jabatanPenandatangan,
        );

        return [
            'nomor_urut_surat' => $nomorUrut,
            'nomor_surat' => $nomorSurat,
            'tanggal_surat' => $tanggalSurat->toDateString(),
            'disetujui_at' => $pengajuanSurat->disetujui_at ?? $today,
            'approved_by' => $request->user()?->id,
            'nama_penandatangan' => $namaPenandatangan,
            'jabatan_penandatangan' => $jabatanPenandatangan,
            'isi_surat' => $isiSurat,
            'file_surat' => $fileSurat,
            'arsip_surat_at' => now(),
        ];
    }

    private function formatNomorSurat(
        PengajuanSurat $pengajuanSurat,
        int $nomorUrut,
        Carbon $tanggal,
        SiteSetting $setting
    ): string
    {
        $numbering = $setting->surat_numbering ?: $this->defaultSuratNumbering();
        $code = $numbering[$pengajuanSurat->jenis_surat] ?? 'SURAT';

        return sprintf(
            '%03d/%s/KP-PAL/%s/%s',
            $nomorUrut,
            $code,
            $this->toRomanMonth((int) $tanggal->format('m')),
            $tanggal->format('Y')
        );
    }

    private function toRomanMonth(int $month): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$month] ?? 'I';
    }

    private function generateSuratContent(
        PengajuanSurat $pengajuanSurat,
        string $nomorSurat,
        Carbon $tanggalSurat,
        string $namaPenandatangan,
        string $jabatanPenandatangan
    ): string {
        $title = strtoupper($pengajuanSurat->jenis_surat);
        $body = $this->resolveBodyByJenisSurat($pengajuanSurat);
        $tanggalIndonesia = $tanggalSurat->locale('id')->translatedFormat('d F Y');

        return implode("\n", [
            'PEMERINTAH KAMPUNG PALARENG',
            'KABUPATEN KEPULAUAN SANGIHE',
            '',
            $title,
            'Nomor: ' . $nomorSurat,
            '',
            'Yang bertanda tangan di bawah ini menerangkan bahwa:',
            '',
            'Nama Lengkap : ' . $pengajuanSurat->nama_pemohon,
            'Alamat       : ' . $pengajuanSurat->alamat,
            'Lindongan    : ' . $pengajuanSurat->lindongan,
            'Keperluan    : ' . ($pengajuanSurat->keperluan ?: '-'),
            '',
            $body,
            '',
            'Demikian surat keterangan ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagaimana mestinya.',
            '',
            'Palareng, ' . $tanggalIndonesia,
            $jabatanPenandatangan,
            '',
            '',
            '',
            $namaPenandatangan,
        ]);
    }

    private function resolveBodyByJenisSurat(PengajuanSurat $pengajuanSurat): string
    {
        $setting = SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'surat_templates' => $this->defaultSuratTemplates(),
                'surat_numbering' => $this->defaultSuratNumbering(),
            ]
        );
        $templates = $setting->surat_templates ?: $this->defaultSuratTemplates();
        $template = $templates[$pengajuanSurat->jenis_surat]
            ?? 'Bahwa nama tersebut benar merupakan warga Kampung Palareng dan surat ini diterbitkan {purpose}.';

        return str_replace(
            ['{purpose}', '{keperluan}', '{nama}', '{alamat}', '{lindongan}'],
            [
                $this->purposeText($pengajuanSurat->keperluan),
                $pengajuanSurat->keperluan ?: 'administrasi',
                $pengajuanSurat->nama_pemohon,
                $pengajuanSurat->alamat,
                $pengajuanSurat->lindongan,
            ],
            $template
        );
    }

    private function purposeText(?string $keperluan): string
    {
        $cleaned = trim((string) $keperluan);

        if ($cleaned === '') {
            return 'untuk keperluan administrasi yang sah';
        }

        return 'untuk keperluan ' . $cleaned;
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

    private function generateAndStorePdf(
        PengajuanSurat $pengajuanSurat,
        string $nomorSurat,
        Carbon $tanggalSurat,
        string $namaPenandatangan,
        string $jabatanPenandatangan
    ): string {
        $bodyParagraph = $this->resolveBodyByJenisSurat($pengajuanSurat);
        $tanggalIndonesia = $tanggalSurat->copy()->locale('id')->translatedFormat('d F Y');
        $directory = 'surat-arsip/' . $tanggalSurat->format('Y');
        $safeNomorSurat = str_replace(['/', '\\', ' '], '-', $nomorSurat);
        $path = $directory . '/' . $safeNomorSurat . '.pdf';

        if ($pengajuanSurat->file_surat && Storage::disk('public')->exists($pengajuanSurat->file_surat)) {
            Storage::disk('public')->delete($pengajuanSurat->file_surat);
        }

        $pdf = Pdf::loadView('documents.surat', [
            'jenisSurat' => $pengajuanSurat->jenis_surat,
            'nomorSurat' => $nomorSurat,
            'namaPemohon' => $pengajuanSurat->nama_pemohon,
            'keperluan' => $pengajuanSurat->keperluan,
            'alamat' => $pengajuanSurat->alamat,
            'lindongan' => $pengajuanSurat->lindongan,
            'bodyParagraph' => $bodyParagraph,
            'tanggalSurat' => $tanggalIndonesia,
            'namaPenandatangan' => $namaPenandatangan,
            'jabatanPenandatangan' => $jabatanPenandatangan,
        ])->setPaper('a4', 'portrait');

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
