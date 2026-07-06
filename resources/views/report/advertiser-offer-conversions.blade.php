@extends('report.template')

@section('report-title')
    Advertiser's Conversions By Offer
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection


@section('table')
    <table id="mainTable"  data-sortable-table data-sort-default="3:desc" >
        <thead>
        <tr>
            <th>Name</th>
            <th>Raw</th>
            <th>Unique</th>
            <th>Convs</th>
            <th>Revenue</th>
        </tr>
        </thead>
        <tbody>
        @foreach($affiliateReport as $row)
            <tr role="row">
                <td>{{$row->offer_name}}</td>
                <td>{{$row->total_clicks}}</td>
                <td>{{$row->unique_clicks}}</td>
                <td>{{$row->conversions}}</td>
                <td>${{$row->total}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4 bp-report-pagination">
        {{ $affiliateReport->links() }}
    </div>

@endsection
