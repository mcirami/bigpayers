@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Referral Settings')

@section('content')
    @php
        $activeReferrals = $referrals->where('is_active', 1)->count();
        $percentageReferrals = $referrals->where('referral_type', 'percentage')->count();
        $defaultReferral = $referrals->first();
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $referrer->user_name }} referral workspace</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Manage which affiliates are referred by this user, update referral timing and payout settings, and remove structures without dropping back into the legacy editor.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/user/{{ $referrer->idrep }}/edit" class="bp-button-secondary">Back to user</a>
                    <a href="/user/{{ $referrer->idrep }}/referrals/create" class="bp-button-primary">Add referral</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Referral Rows</p>
                <p class="bp-stat-value">{{ $referrals->count() }}</p>
                <p class="bp-stat-note">Affiliates currently attached to this referral structure.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Active</p>
                <p class="bp-stat-value">{{ $activeReferrals }}</p>
                <p class="bp-stat-note">Referral rows that are currently marked active.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Percent Type</p>
                <p class="bp-stat-value">{{ $percentageReferrals }}</p>
                <p class="bp-stat-note">Rows using percentage-based commission instead of flat fee.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Editing</p>
                <p class="bp-stat-value">{{ $defaultReferral ? 'Ready' : 'Empty' }}</p>
                <p class="bp-stat-note">Select any row below to load it into the settings panel.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.85fr)]">
            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Referral Directory</p>
                        <h3 class="bp-section-title value_span9">Current referred affiliates</h3>
                    </div>
                    <p class="bp-table-meta">Choose a row to edit it or remove it directly from the actions column.</p>
                </div>

                <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                    <table class="table table-bordered table-striped table_01 tablesorter" id="referralTable">
                        <thead>
                        <tr>
                            <th class="value_span9">{{ $affiliateTypeLabel }}</th>
                            <th class="value_span9">Start</th>
                            <th class="value_span9">End</th>
                            <th class="value_span9">Type</th>
                            <th class="value_span9">Amount</th>
                            <th class="value_span9">Status</th>
                            <th class="value_span9">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($referrals as $row)
                            <tr data-affiliate="{{ $row->aff_id }}">
                                <td>{{ $row->user_name }}</td>
                                <td>{{ $row->start_date }}</td>
                                <td>{{ $row->end_date === '3000-01-01' || $row->end_date === '' ? 'Indefinite' : $row->end_date }}</td>
                                <td>{{ ucfirst($row->referral_type) }}</td>
                                <td>{{ $row->payout }}</td>
                                <td>{{ (int) $row->is_active === 1 ? 'Active' : 'Inactive' }}</td>
                                <td class="actions">
                                    <div class="bp-table-actions">
                                        <button type="button" class="btn btn-default btn-sm value_span6-1 value_span4 js-load-referral" data-affiliate="{{ $row->aff_id }}">Edit</button>
                                        <form action="/user/{{ $referrer->idrep }}/referrals/{{ $row->aff_id }}/delete" method="post" onsubmit="return confirm('Delete this referral structure?');">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-default btn-sm value_span5-1 value_span2 value_span4">Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Editor</p>
                        <h3 class="bp-section-title value_span9">Edit selected referral</h3>
                    </div>
                    <p class="bp-table-meta">The panel updates from the selected table row and saves back to the same dataset.</p>
                </div>

                @if($referrals->isNotEmpty())
                    <form action="/user/{{ $referrer->idrep }}/referrals" method="post" class="mt-6 space-y-5" id="referralEditForm">
                        {{ csrf_field() }}
                        <input type="hidden" id="affid" name="affid" value="{{ $defaultReferral->aff_id }}">

                        <div class="bp-form-grid">
                            <label class="bp-form-field">
                                <span class="bp-form-label">{{ $affiliateTypeLabel }}</span>
                                <input class="bp-form-input" type="text" id="selected_affiliate_name" value="{{ $defaultReferral->user_name }}" readonly>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Start Date</span>
                                <input class="bp-form-input" id="start_date" name="start_date" type="text" value="{{ $defaultReferral->start_date }}" required>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">End Date</span>
                                <input class="bp-form-input" id="end_date" name="end_date" type="text" value="{{ $defaultReferral->end_date === '3000-01-01' ? '' : $defaultReferral->end_date }}">
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Referral Type</span>
                                <select class="bp-form-input" id="referral_type" name="referral_type">
                                    <option value="flat" {{ $defaultReferral->referral_type === 'flat' ? 'selected' : '' }}>Flat Fee</option>
                                    <option value="percentage" {{ $defaultReferral->referral_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                </select>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Amount</span>
                                <input class="bp-form-input" id="amount" name="amount" type="number" min="0" step="0.01" value="{{ $defaultReferral->payout }}" required>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Status</span>
                                <select class="bp-form-input" id="is_active" name="is_active">
                                    <option value="active" {{ (int) $defaultReferral->is_active === 1 ? 'selected' : '' }}>Active</option>
                                    <option value="unactive" {{ (int) $defaultReferral->is_active !== 1 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </label>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">Save referral</button>
                            <a href="/user/{{ $referrer->idrep }}/referrals/create" class="bp-button-secondary">Create another</a>
                        </div>
                    </form>
                @else
                    <div class="mt-6 bp-inline-note">
                        <strong>No referral rows yet</strong>
                        <span>Create the first referral for this user to start populating the editor panel.</span>
                    </div>
                @endif
            </section>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        (() => {
            const referralMap = @json($referrals->keyBy('aff_id'));
            const rows = Array.from(document.querySelectorAll('.js-load-referral'));

            $("#start_date, #end_date").datepicker({dateFormat: 'yy-mm-dd'});
            $('#referralTable').tablesorter({
                sortList: [[0, 0]],
                widgets: ['staticRow']
            });

            const loadReferral = (affiliateId) => {
                const referral = referralMap[String(affiliateId)];
                if (!referral) {
                    return;
                }

                document.getElementById('affid').value = referral.aff_id;
                document.getElementById('selected_affiliate_name').value = referral.user_name;
                document.getElementById('start_date').value = referral.start_date || '';
                document.getElementById('end_date').value = referral.end_date === '3000-01-01' ? '' : (referral.end_date || '');
                document.getElementById('referral_type').value = referral.referral_type;
                document.getElementById('amount').value = referral.payout;
                document.getElementById('is_active').value = Number(referral.is_active) === 1 ? 'active' : 'unactive';
            };

            rows.forEach((button) => {
                button.addEventListener('click', () => loadReferral(button.dataset.affiliate));
            });
        })();
    </script>
@endsection
