@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', $title)

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div>
                <p class="bp-section-kicker">Reporting Workspace</p>
                <h2 class="bp-section-title value_span9">{{ $title }}</h2>
            </div>
        </section>

        <section class="bp-card value_span8">
            <div class="bp-report-table-wrap">
                <table class="table table-bordered table_01 tablesorter" id="mainTable">
                    <thead>
                    <tr>
                        @foreach($tableHeaders as $header)
                            <th class="value_span9">{{ $header }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($report as $row)
                        <tr>
                            @foreach($row as $column)
                                <td>{{ $column }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {
			$("#mainTable").tablesorter(
				{
					sortList: [[6, 1]],
					widgets: ['staticRow']
				});
		});
    </script>
@endsection
