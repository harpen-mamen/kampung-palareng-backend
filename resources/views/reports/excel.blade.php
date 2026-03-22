<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                @if($rows->isNotEmpty())
                    @foreach(array_keys($rows->first()->toArray()) as $key)
                        <th>{{ $key }}</th>
                    @endforeach
                @endif
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
</body>
</html>
