@extends('report.template')

@section('report-title')
    Black List Report
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Aff ID</th>
            <th class="value_span9">Affiliate</th>
            <th class="value_span9">Clicks</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($reps as $key => $rep) {
            if ($key !== count($reps) - 1) {
                echo "<tr>";
            } else {
                echo "<tr class='static'>";
            }
            echo "<td>{$rep["idrep"]}</td>";
            echo "<td>{$rep["user_name"]}</td>";
            echo "<td><a href=\"/user/{$rep["idrep"]}/clicks?d_from={$assign->d_from}&d_to={$assign->d_to}&dateSelect={$assign->dateSelect}&blacklist=1\" >{$rep["Clicks"]}</a></td>";
            echo "</tr>";
        }
        ?>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    widgets: ['staticRow']
                });
        });
    </script>
@endsection