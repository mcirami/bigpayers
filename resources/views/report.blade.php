@extends('layouts.master')
@section('content')
    <!--right_panel-->
    <div class = "right_panel">
        <div class = "white_box_outer large_table ">
            <div class = "heading_holder">
                <span class = "lft value_span9">{{$title}}</span>

            </div>


            <div class = "clear"></div>
            <div class = "white_box_x_scroll white_box manage_aff large_table value_span8  ">
                <table class = "table table-bordered table_01 tablesorter" id = "mainTable">
                    <thead>

                    <tr>
                        @foreach($tableHeaders as $header)
                            <th class = "value_span9">{{$header}}</th>
                        @endforeach


                    </tr>
                    </thead>
                    <tbody>
                    @foreach($report as $row)
                        <tr>
                            @foreach($row as $column)
                                <td>{{$column}}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script type = "text/javascript">


		$(document).ready(function () {
			$("#mainTable").tablesorter(
				{
					sortList: [[6, 1]],
					widgets: ['staticRow']
				});
		});
    </script>


@endsection

