<table>
    <thead>
    <tr>
        <th>{{ $affiliateTypeLabel }} ID</th>
        <th>{{ $affiliateTypeLabel }} Username</th>
        <th>Raw</th>
        <th>Unique</th>
        <th>Conversion</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($affData as $data)
        <tr>
            <td>{{ $data['idrep'] }}</td>
            <td>{{ $data['user_name'] }}</td>
            <td>{{ $data['Clicks'] }}</td>
            <td>{{ $data['UniqueClicks'] }}</td>
            <td>{{ $data['Conversions'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
