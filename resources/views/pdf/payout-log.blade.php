<!doctype HTML>
<html>
<head>
    <title>{{$title}}</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        tr {
            page-break-inside: avoid;
        }

        * {
            font-size: 12px;
        }


    </style>
</head>
<body>

<div class="container"
     style="background-color: #{{array_first(\App\Company::getInstance()->colors())}}; padding:10px; margin-bottom: 10px;">
    <img src="{{asset( \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() . '/logo.png')}}">
</div>

<div class="container">
    <div class="form-group">

        <label class="label label-default">Affiliate:</label>
        <span>{{\LeadMax\TrackYourStats\System\Session::user()->user_name}}</span>

    </div>
    <div class="form-group">
        <label class="label label-primary">Date Range:</label>
        <span>{{$dates['originalStart']}} - {{$dates['originalEnd']}}</span>
    </div>

    <table class="table table-sm table-bordered ">
        <thead>
        <tr>
            <th class="value_span9">Payout Type</th>
            <th class="value_span9">Notes</th>
            <th class="value_span9">Revenue</th>
            <th class="value_span9">Date Achieved</th>
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

    <table class="table  table-bordered table-sm ">
        <thead>
        <tr>
            <th>ID</th>
            <th class="value_span9">Offer Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">FreeSignUps</th>
            <th class="value_span9">Pending Conversions</th>
            <th class="value_span9">Conversions</th>
            <th class="value_span9">Revenue</th>
            <th class="value_span9">Deductions</th>
            <th class="value_span9">EPC</th>
            <th class="value_span9">Total</th>
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
            <th class="value_span9">Offer Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">FreeSignUps</th>
            <th class="value_span9">Pending Conversions</th>
            <th class="value_span9">Conversions</th>
            <th class="value_span9">Revenue</th>
            <th class="value_span9">Deductions</th>
            <th class="value_span9">EPC</th>
            <th class="value_span9">Total</th>
        </tr>
        </tfoot>
    </table>
</div>
This report was generated on: {{\Carbon\Carbon::now()->toFormattedDateString()}}
</body>
</html>
