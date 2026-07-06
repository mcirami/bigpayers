@extends('report.template')

@section('table-options')
    @include('report.options.dates')
@endsection

@section('report-title')
    Payout Report
@endsection
@section('table')
    <table>
        <thead>
        <tr>
            <th>Payout Type</th>
            <th>Notes</th>
            <th>Revenue</th>
            <th>Date Achieved</th>
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
    <div id="payout-history">
        <section class="bp-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Payout History</p>
                    <h3 class="bp-section-title">Weekly payout snapshots</h3>
                </div>
                <p class="bp-table-meta">Expand a week to inspect the offer, salary, bonus, referral, deduction, and net breakdown.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Week range</th>
                        <th>Revenue</th>
                        <th>Bonuses</th>
                        <th>Referrals</th>
                        <th>Actions</th>
                        <th>Download</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($historyReport as $row)
                        @if(!empty($row))
                            <tr>
                                <td>{{$row["start_of_week"]}} - {{$row["end_of_week"]}}</td>
                                <td>{{$row['revenue']}}</td>
                                <td>{{$row['bonuses']}}</td>
                                <td>{{$row['referrals']}}</td>
                                <td>
                                    <button type="button"
                                            class="bp-action-link"
                                            data-history-expand
                                            data-row-id="{{$row['id']}}"
                                            data-start-date="{{$row['start_of_week']}}"
                                            data-end-date="{{$row['end_of_week']}}">
                                        Expand
                                    </button>
                                    <button type="button"
                                            class="bp-action-link"
                                            data-history-collapse="{{$row['id']}}"
                                            hidden>
                                        Minimize
                                    </button>
                                </td>
                                <td>
                                    <a class="bp-action-link"
                                       href="/report/payout/pdf?d_from={{$row['start_of_week']}}&d_to={{$row['end_of_week']}}&adminLogin">Download</a>
                                </td>
                            </tr>
                            <tr data-history-details="{{$row['id']}}" hidden>
                                <td colspan="6">
                                    <div class="bp-table-meta">Loading payout details...</div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
@section('footer')
    <script type="text/javascript" defer>
        (function () {
            const history = document.getElementById('payout-history');
            const reportCache = {};

            if (!history) {
                return;
            }

            function detailRow(rowId) {
                return history.querySelector('[data-history-details="' + rowId + '"]');
            }

            function collapseButton(rowId) {
                return history.querySelector('[data-history-collapse="' + rowId + '"]');
            }

            function showRow(rowId, show) {
                const row = detailRow(rowId);
                const collapse = collapseButton(rowId);
                const expand = history.querySelector('[data-history-expand][data-row-id="' + rowId + '"]');

                if (row) {
                    row.hidden = !show;
                }

                if (expand) {
                    expand.hidden = show;
                }

                if (collapse) {
                    collapse.hidden = !show;
                }
            }

            function appendCell(row, value) {
                const cell = document.createElement('td');
                cell.textContent = value == null ? '' : value;
                row.appendChild(cell);
            }

            function appendSummaryRow(body, label, value) {
                const row = document.createElement('tr');
                row.className = 'tr_row_space';
                appendCell(row, '');
                appendCell(row, label);
                appendCell(row, '');
                appendCell(row, value);
                appendCell(row, '');
                body.appendChild(row);
            }

            function appendDetailRow(body, type, notes, revenue, date) {
                const row = document.createElement('tr');
                appendCell(row, '');
                appendCell(row, type);
                appendCell(row, notes);
                appendCell(row, revenue);
                appendCell(row, date);
                body.appendChild(row);
            }

            function appendMappedRows(body, items, detailKey, labels) {
                Object.entries(items || {}).forEach(function ([key, item]) {
                    if (key === 'total') {
                        appendSummaryRow(body, labels.total, item);
                        return;
                    }

                    if (item && item[detailKey] !== undefined) {
                        appendDetailRow(body, labels.type, item[detailKey], item[labels.amountKey], item.timestamp || '');
                    }
                });
            }

            function renderOfferTable(offerReport) {
                const table = document.createElement('table');
                const head = document.createElement('thead');
                const body = document.createElement('tbody');
                const header = document.createElement('tr');

                ['ID', 'Name', 'Raw', 'Unique', 'FreeSignUps', 'Pending Conversions', 'Conversions', 'Revenue'].forEach(function (label) {
                    const cell = document.createElement('th');
                    cell.textContent = label;
                    header.appendChild(cell);
                });

                (offerReport || []).forEach(function (offer) {
                    const row = document.createElement('tr');
                    appendCell(row, offer.idoffer);
                    appendCell(row, offer.offer_name);
                    appendCell(row, offer.Clicks);
                    appendCell(row, offer.UniqueClicks);
                    appendCell(row, offer.FreeSignUps);
                    appendCell(row, offer.PendingConversions);
                    appendCell(row, offer.Conversions);
                    appendCell(row, offer.Revenue);
                    body.appendChild(row);
                });

                head.appendChild(header);
                table.appendChild(head);
                table.appendChild(body);

                return table;
            }

            function renderReport(data) {
                const wrapper = document.createElement('div');
                const table = document.createElement('table');
                const head = document.createElement('thead');
                const header = document.createElement('tr');
                const body = document.createElement('tbody');
                const offerRow = document.createElement('tr');
                const offerTableCell = document.createElement('td');

                ['Type', 'Notes', 'Revenue', 'Date Achieved', ''].forEach(function (label) {
                    const cell = document.createElement('th');
                    cell.textContent = label;
                    header.appendChild(cell);
                });

                appendCell(offerRow, 'Offer Breakdown');
                offerTableCell.colSpan = 4;
                offerTableCell.appendChild(renderOfferTable(data.offerReport));
                offerRow.appendChild(offerTableCell);
                body.appendChild(offerRow);

                appendSummaryRow(body, 'Total Offer Revenue', data.offer_revenue);
                appendMappedRows(body, data.salary, 'reason', {
                    type: 'Salary',
                    total: 'Total Salary',
                    amountKey: 'payout'
                });
                appendMappedRows(body, data.bonuses, 'name', {
                    type: 'Bonus',
                    total: 'Bonus Total',
                    amountKey: 'payout'
                });
                appendMappedRows(body, data.referrals, 'user_name', {
                    type: 'Referral',
                    total: 'Total Referral Revenue',
                    amountKey: 'Referral_Revenue'
                });

                Object.values(data.deductions || {}).forEach(function (item) {
                    appendDetailRow(body, 'Deduction', '', item, '');
                });

                appendSummaryRow(body, 'Net', data.net);

                head.appendChild(header);
                table.appendChild(head);
                table.appendChild(body);
                wrapper.appendChild(table);

                return wrapper;
            }

            function setRowContent(rowId, content) {
                const row = detailRow(rowId);

                if (!row) {
                    return;
                }

                row.cells[0].replaceChildren(content);
            }

            async function fetchHistoryReport(startDate, endDate, rowId) {
                if (reportCache[rowId]) {
                    setRowContent(rowId, renderReport(reportCache[rowId]));
                    showRow(rowId, true);
                    return;
                }

                showRow(rowId, true);

                const query = '?d_from=' + encodeURIComponent(startDate) + '&d_to=' + encodeURIComponent(endDate) + '&adminLogin';
                const [payoutResponse, offerResponse] = await Promise.all([
                    fetch('/report/payout' + query, {headers: {'X-Requested-With': 'XMLHttpRequest'}}),
                    fetch('/report/offer' + query, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                ]);

                if (!payoutResponse.ok || !offerResponse.ok) {
                    throw new Error('Unable to load payout history.');
                }

                const data = await payoutResponse.json();
                data.offerReport = await offerResponse.json();
                reportCache[rowId] = data;
                setRowContent(rowId, renderReport(data));
            }

            history.addEventListener('click', function (event) {
                const expand = event.target.closest('[data-history-expand]');
                const collapse = event.target.closest('[data-history-collapse]');

                if (expand) {
                    event.preventDefault();
                    fetchHistoryReport(expand.dataset.startDate, expand.dataset.endDate, expand.dataset.rowId)
                        .catch(function (error) {
                            const message = document.createElement('div');
                            message.className = 'bp-table-meta';
                            message.textContent = error.message;
                            setRowContent(expand.dataset.rowId, message);
                        });
                    return;
                }

                if (collapse) {
                    event.preventDefault();
                    showRow(collapse.dataset.historyCollapse, false);
                }
            });
        })();
    </script>
@endsection
