<table>
    <thead>
        <tr>
            <th>Click ID</th>
            <th>Timestamp</th>
            <th>Offer Name</th>
            <th>Conversion Timestamp</th>
            <th>Paid</th>
            <th>Sub1</th>
            <th>Sub2</th>
            <th>Sub3</th>
            <th>Referer URL</th>
            <th>IP Address</th>
            <th>Iso Code</th>
            <th>Sub Division</th>
            <th>City</th>
            <th>Postal</th>
            <th>Longitude</th>
            <th>Latitude</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($clicks as $click)
        <tr>
            <td>{{ $click->idclicks }}</td>
            <td>{{ $click->timestamp }}</td>
            <td>{{ $click->offer_name }}</td>
            <td>{{ $click->conversion_timestamp }}</td>
            <td>{{ $click->paid }}</td>
            <td>{{ $click->sub1 }}</td>
            <td>{{ $click->sub2 }}</td>
            <td>{{ $click->sub3 }}</td>
            <td>{{ $click->referer }}</td>
            <td>{{ $click->ip_address }}</td>
            <td>{{ $click->iso_code }}</td>
            <td>{{ $click->subDivision}}</td>
            <td>{{ $click->city }}</td>
            <td>{{ $click->postal }}</td>
            <td>{{ $click->latitude}}</td>
            <td>{{ $click->longitude}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
