<!doctype HTML>
<html>
<head>
    <title>{{$title}}</title>
    <style>
        * {
            box-sizing: border-box;
            font-size: 12px;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        .container {
            width: 100%;
            margin: 0 auto 10px;
        }

        .form-group {
            margin-bottom: 8px;
        }

        label {
            display: inline-block;
            margin-bottom: 4px;
            font-weight: 700;
        }

        table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            padding: 4px 6px;
            border: 1px solid #d1d5db;
            line-height: 1.35;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: 700;
        }
    </style>
</head>
<body>

@php
    /** @var \App\Company $company */
    $company = \App\Company::getInstance();
@endphp

<div class="container"
     style="background-color: #{{array_first($company->colors())}}; padding:10px; margin-bottom: 10px;">
    <img src="{{asset($company->getImgDir() . '/logo.png')}}">
</div>

<div class="container">
    <div class="form-group">

        <label class="label label-default">Affiliate:</label>
        <span>{{ $affiliateUserName }}</span>

    </div>
    <div class="form-group">
        <label class="label label-primary">Date Range:</label>
        <span>{{$dates['originalStart']}} - {{$dates['originalEnd']}}</span>
    </div>

    <table class="table table-sm table-bordered">
        <thead>
        <tr>
            <th>Payout Type</th>
            <th>Notes</th>
            <th>Revenue</th>
            <th>Date Achieved</th>
        </tr>
        </thead>
        <tbody>
        @isset($payoutReport)
            @php
                $payoutReport->printReports();
            @endphp
        @endif
        </tbody>
    </table>

    <table class="table  table-bordered table-sm">
        <thead>
        <tr>
            <th>ID</th>
            <th>Offer Name</th>
            <th>Raw</th>
            <th>Unique</th>
            <th>FreeSignUps</th>
            <th>Pending Conversions</th>
            <th>Conversions</th>
            <th>Revenue</th>
            <th>Deductions</th>
            <th>EPC</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($offerReport as $row)
            <tr>
                <td>{{$row['idoffer']}}</td>
                <td>{{$row['offer_name']}}</td>
                <td>{{$row['Clicks']}}</td>
                <td>{{$row['UniqueClicks']}}</td>
                <td>{{$row['FreeSignUps']}}</td>
                <td>{{$row['PendingConversions']}}</td>
                <td>{{$row['Conversions']}}</td>
                <td>{{$row['Revenue']}}</td>
                <td>{{$row['Deductions']}}</td>
                <td>{{$row['EPC']}}</td>
                <td>{{$row['TOTAL']}}</td>
            </tr>
        @endforeach

        </tbody>
        <tfoot>
        <tr>
            <th>ID</th>
            <th>Offer Name</th>
            <th>Raw</th>
            <th>Unique</th>
            <th>FreeSignUps</th>
            <th>Pending Conversions</th>
            <th>Conversions</th>
            <th>Revenue</th>
            <th>Deductions</th>
            <th>EPC</th>
            <th>Total</th>
        </tr>
        </tfoot>
    </table>
</div>
This report was generated on: {{\Carbon\Carbon::now()->toFormattedDateString()}}
</body>
</html>
