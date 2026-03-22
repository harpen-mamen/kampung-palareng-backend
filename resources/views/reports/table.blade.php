<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 landscape; margin: 18px 16px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        h1 { margin: 0 0 6px; font-size: 16px; }
        p.meta { margin: 0 0 12px; font-size: 10px; color: #475569; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        th {
            background: #e2e8f0;
            font-size: 9px;
        }
        td {
            font-size: 8.5px;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">Dokumen ini dibuat otomatis dari sistem administrasi Kampung Palareng.</p>
    @if($rows->isEmpty())
        <p>Tidak ada data.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($rows->first()->toArray()) as $key)
                        <th>{{ $key }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row->toArray() as $value)
                            <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
