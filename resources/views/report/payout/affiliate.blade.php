@extends('report.template')

@section('table-options')
    @include('report.options.dates')
@endsection

@section('report-title')
    Payout Report
@endsection
@section('table')
    <table class="table table-striped table-bordered  table_01">
        <thead>
        <tr>
            <th class="value_span9">Payout Type</th>
            <th class="value_span9">Notes</th>
            <th class="value_span9">Revenue</th>
            <th class="value_span9">Date Achieved</th>
        </tr>
        </thead>
        <tbody>
        @isset($report)
            @php
                $report->printReports();
            @endphp
        @endif
        </tbody>
    </table>
@endsection
@section('extra')
    <div id="apptwo">

        <div class="white_box manage_aff large_table value_span8">
            <table class="table table-striped table-bordered  table_01">
                <thead>
                <tr>
                    <th>Week range</th>
                    <th>Revenue</th>
                    <th>Deductions</th>
                    <th>Bonuses</th>
                    <th>Referrals</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($historyReport as $row)
                    @if(!empty($row))
                        <tr>
                            <td>{{$row["start_of_week"]}} - {{$row["end_of_week"]}}</td>
                            <td>{{$row['revenue']}}</td>
                            <td>{{$row['deductions']}}</td>
                            <td>{{$row['bonuses']}}</td>
                            <td>{{$row['referrals']}}</td>
                            <td>{{$row['TOTAL']}}</td>
                            <td>
                                <button v-if="this.activeIds.indexOf({{$row['id']}}) == -1"
                                        class="btn btn-default btn-sm"
                                        @click="fetchHistoryReport('{{$row['start_of_week']}}', '{{$row['end_of_week']}}', {{$row['id']}})">
                                    Expand
                                </button>

                                <button v-if="this.activeIds.indexOf({{$row['id']}}) > -1"
                                        class="btn btn-default btn-sm"
                                        @click="deActiveReport({{$row['id']}})">Minimize
                                </button>

                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary"
                                   :href="'/report/payout/pdf?d_from={{$row['start_of_week']}}&d_to={{$row['end_of_week']}}&adminLogin'">Download</a>
                            </td>
                        </tr>


                        <tr v-if="this.activeIds.indexOf({{$row['id']}}) > -1 " class="">
                            <td></td>
                            <td><b>Type</b></td>
                            <td><b>Notes</b></td>
                            <td><b>Revenue</b></td>
                            <td><b>Date Achieved</b></td>
                            <td></td>
                            <td></td>
                        </tr>


                        {{--Detailed Offer Report for Week Range--}}
                        <tr v-if="this.activeIds.indexOf({{$row['id']}}) > -1 " class="">
                            <td></td>
                            <td>Offer Breakdown</td>
                        </tr>

                        {{--Detailed Offer Report for Week Range--}}
                        <tr v-if="this.activeIds.indexOf({{$row['id']}}) > -1 " class="">
                            <td>
                            </td>
                            <td>
                                <table class="table table-sm table-striped table-bordered  table_01">
                                    <thead>
                                    <tr>
                                        <th class="value_span9">ID</th>
                                        <th class="value_span9">Name</th>
                                        <th class="value_span9">Raw</th>
                                        <th class="value_span9">Unique</th>
                                        <th class="value_span9">FreeSignUps</th>
                                        <th class="value_span9">Pending Conversions</th>
                                        <th class="value_span9">Conversions</th>
                                        <th class="value_span9">Revenue</th>
                                        <th class="value_span9">Deductions</th>
                                        <th class="value_span9">EPC</th>
                                        <th class="value_span9">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, key) in this.reportData[{{$row['id']}}].offerReport">
                                        <td v-text="row.idoffer"></td>
                                        <td v-text="row.offer_name"></td>
                                        <td v-text="row.Clicks"></td>
                                        <td v-text="row.UniqueClicks"></td>
                                        <td v-text="row.FreeSignUps"></td>
                                        <td v-text="row.PendingConversions"></td>
                                        <td v-text="row.Conversions"></td>
                                        <td v-text="row.Revenue"></td>
                                        <td v-text="row.Deductions"></td>
                                        <td v-text="row.EPC"></td>
                                        <td v-text="row.TOTAL"></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        {{--Total Offer Revenue--}}
                        <template v-if="this.activeIds.indexOf({{$row['id']}}) > -1 ">
                            <tr class="tr_row_space">
                                <td></td>
                                <td>Total Offer Revenue</td>
                                <td></td>
                                <td v-text="this.reportData[{{$row['id']}}].offer_revenue"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </template>


                        {{-- Salary --}}
                        <template v-if="this.activeIds.indexOf({{$row['id']}}) > -1 ">

                            <tr v-for="(salary, key) in this.reportData[{{$row['id']}}].salary"
                                :class="{ tr_row_space: key == 'total' }">
                                <td></td>
                                <template v-if="salary.reason != undefined">
                                    <td>Salary</td>
                                    <td>@{{salary.reason}}</td>
                                    <td>@{{salary.payout}}</td>
                                    <td>@{{salary.timestamp}}</td>
                                </template>

                                <template v-if="key == 'total'">
                                    <td>Total Salary</td>
                                    <td></td>
                                    <td>@{{salary}}</td>
                                    <td></td>
                                </template>
                                <td></td>
                                <td></td>
                            </tr>

                        </template>



                        {{-- Bonuses --}}
                        <template v-if="this.activeIds.indexOf({{$row['id']}}) > -1 ">
                            <tr v-for="(bonus, key) in this.reportData[{{$row['id']}}].bonuses"
                                :class="{ tr_row_space: key == 'total' }">
                                <td></td>
                                <template v-if="bonus.name != undefined">
                                    <td>Bonus</td>
                                    <td>@{{bonus.name}}</td>
                                    <td>@{{bonus.payout}}</td>
                                    <td>@{{bonus.timestamp}}</td>
                                </template>
                                <template v-if="key == 'total'">
                                    <td>Bonus Total</td>
                                    <td></td>
                                    <td>@{{bonus}}</td>
                                    <td></td>
                                </template>
                                <td></td>
                                <td></td>
                            </tr>
                        </template>



                        {{-- Referral Revenue --}}
                        <template v-if="this.activeIds.indexOf({{$row['id']}}) > -1 ">
                            <tr v-for="(ref, key) in this.reportData[{{$row['id']}}].referrals"
                                :class="{ tr_row_space: key == 'total' }">
                                <td></td>
                                <template v-if="ref.user_name != undefined">
                                    <td>Referral</td>
                                    <td>@{{ref.user_name}}</td>
                                    <td>@{{ref.Referral_Revenue}}</td>
                                    <td></td>
                                </template>
                                <template v-if="key == 'total'">
                                    <td>Total Referral Revenue</td>
                                    <td></td>
                                    <td>@{{ref}}</td>
                                    <td></td>
                                </template>
                                <td></td>
                                <td></td>
                            </tr>
                        </template>




                        {{-- Deductions --}}
                        <template v-if="this.activeIds.indexOf({{$row['id']}}) > -1 ">
                            <tr v-for="item in this.reportData[{{$row['id']}}].deductions">
                                <td></td>
                                <td>Deduction</td>
                                <td></td>
                                <td v-text="item"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </template>

                        {{-- Net --}}
                        <template v-if="this.activeIds.indexOf({{$row['id']}}) > -1 ">
                            <tr>
                                <td></td>
                                <td>Net</td>
                                <td></td>
                                <td v-text="this.reportData[{{$row['id']}}].net"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </template>

                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>



@endsection
@section('footer')
    <script type="text/javascript" defer>
        new Vue({
            'el': '#apptwo',
            data: {
                reportData: [],
                activeIds: []
            },
            methods: {
                fetchHistoryReport(startDate, endDate, rowId) {
                    let payout = axios.get('/report/payout?d_from=' + startDate + '&d_to=' + endDate + '&adminLogin');
                    let offer = axios.get('/report/offer?d_from=' + startDate + '&d_to=' + endDate + '&adminLogin');

                    axios.all([payout, offer])
                        .then(
                            axios.spread((payoutData, offerData) => {
                                this.reportData[rowId] = payoutData.data;
                                this.reportData[rowId].offerReport = offerData.data;
                                this.activeIds.push(rowId);
                            })
                        ).catch(err => console.log(err));

                },

                deActiveReport(id) {
                    this.activeIds.splice(this.activeIds.indexOf(id), 1);
                }


            }
        });
    </script>
@endsection
