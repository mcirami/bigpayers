<table>
    <thead>
    <tr>
        <th>Offer ID</th>
        <th>Offer Name</th>
        <th>Raw</th>
        <th>Unique</th>
        <th>Conversion</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($offerData as $data)
        <tr>
            <td>{{ $data['idoffer'] }}</td>
            <td>{{ $data['offer_name'] }}</td>
            <td>{{ $data['Clicks'] }}</td>
            <td>{{ $data['UniqueClicks'] }}</td>
            <td>{{ $data['Conversions'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>