<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $jenisSurat }}</title>
    <style>
        @page {
            margin: 28mm 24mm 28mm 24mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.65;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 24px;
        }

        .header h1,
        .header h2,
        .header p {
            margin: 0;
        }

        .header h1 {
            font-size: 18px;
            letter-spacing: 0.08em;
        }

        .header h2 {
            margin-top: 4px;
            font-size: 16px;
        }

        .header p {
            margin-top: 5px;
            font-size: 11px;
        }

        .title {
            text-align: center;
            margin-bottom: 18px;
        }

        .title h3 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .title p {
            margin: 4px 0 0;
            font-size: 12px;
        }

        .opening {
            margin-bottom: 12px;
        }

        .identity {
            width: 100%;
            margin-bottom: 16px;
        }

        .identity td {
            vertical-align: top;
            padding: 2px 0;
        }

        .identity .label {
            width: 130px;
        }

        .body-copy {
            text-align: justify;
        }

        .signature {
            margin-top: 32px;
            width: 100%;
        }

        .signature td {
            vertical-align: top;
        }

        .signature-box {
            width: 46%;
            margin-left: auto;
            text-align: center;
        }

        .name {
            margin-top: 64px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEMERINTAH KAMPUNG PALARENG</h1>
        <h2>KABUPATEN KEPULAUAN SANGIHE</h2>
        <p>Layanan surat administrasi masyarakat Kampung Palareng</p>
    </div>

    <div class="title">
        <h3>{{ $jenisSurat }}</h3>
        <p>Nomor: {{ $nomorSurat }}</p>
    </div>

    <p class="opening">Yang bertanda tangan di bawah ini menerangkan bahwa:</p>

    <table class="identity">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td>: {{ $namaPemohon }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td>: {{ $alamat }}</td>
        </tr>
        <tr>
            <td class="label">Lindongan</td>
            <td>: {{ $lindongan }}</td>
        </tr>
        <tr>
            <td class="label">Keperluan</td>
            <td>: {{ $keperluan ?: '-' }}</td>
        </tr>
    </table>

    <p class="body-copy">
        {{ $bodyParagraph }}
    </p>

    <p class="body-copy">
        Demikian surat ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagaimana mestinya.
    </p>

    <table class="signature">
        <tr>
            <td></td>
            <td class="signature-box">
                <p>Palareng, {{ $tanggalSurat }}</p>
                <p>{{ $jabatanPenandatangan }}</p>
                <p class="name">{{ $namaPenandatangan }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
