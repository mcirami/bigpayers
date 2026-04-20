@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', $pageTitle ?? 'Create Offer')

@section('content')
    @php
        $isEdit = ($mode ?? 'create') === 'edit';
        $offerTypeOptions = [
            \App\Offer::TYPE_CPA => 'CPA',
            \App\Offer::TYPE_CPC => 'CPC',
            \App\Offer::TYPE_PENDING_CONVERSION => 'Pending Conversion',
        ];

        $visibilityOptions = [
            \LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_PUBLIC => 'Public',
            \LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_PRIVATE => 'Private',
            \LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_REQUESTABLE => 'Requestable',
        ];

        $statusOptions = [
            1 => 'Active',
            0 => 'Disabled',
        ];
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $pageHeading ?? 'Create a new offer' }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        {{ $pageCopy ?? ('Launch a new offer, choose how it appears in the directory, and assign it to the right ' . strtolower($affiliateTypeLabelPlural) . ' from the same screen.') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/offer/manage" class="bp-button-secondary">Back to offers</a>
                    <span class="bp-status-pill bp-status-pill-active">{{ $isEdit ? 'Editing live offer' : 'New workflow' }}</span>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Visibility Modes</p>
                <p class="bp-stat-value">{{ count($visibilityOptions) }}</p>
                <p class="bp-stat-note">Public, private, and requestable offers all stay available in the new form.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Campaigns</p>
                <p class="bp-stat-value">{{ $campaigns->count() }}</p>
                <p class="bp-stat-note">Campaign assignment is available inline when advertisers already exist in the system.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Assignment Scope</p>
                <p class="bp-stat-value">{{ $isEdit ? 'Existing users' : $affiliateTypeLabelPlural }}</p>
                <p class="bp-stat-note">{{ $isEdit ? 'Use manage offers or mass-assign to change who currently has access to this offer.' : 'Offers are assigned directly to ' . strtolower($affiliateTypeLabelPlural) . ' from this workflow.' }}</p>
            </article>
        </section>

        <form action="{{ $formAction ?? '/offer/create' }}" method="post" class="space-y-6 lg:space-y-8" data-offer-create>
            {{ csrf_field() }}

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Offer Details</p>
                        <h3 class="bp-section-title value_span9">Core setup</h3>
                    </div>
                    <p class="bp-table-meta">These fields control how the offer appears in reporting, routing, and assignment tools.</p>
                </div>

                <div class="bp-form-grid mt-6">
                    <label class="bp-form-field">
                        <span class="bp-form-label">Name</span>
                        <input class="bp-form-input" id="offer_name" name="offer_name" type="text" value="{{ old('offer_name', $offer->offer_name) }}" required>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Visibility</span>
                        <select class="bp-form-input" name="is_public" id="is_public">
                            @foreach($visibilityOptions as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('is_public', $offer->is_public ?? \LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_PUBLIC) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    @if($campaigns->isNotEmpty())
                        <label class="bp-form-field">
                            <span class="bp-form-label">Advertiser</span>
                            <select class="bp-form-input" name="campaign_id" required>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}" @selected((int) old('campaign_id', $offer->campaign_id) === (int) $campaign->id)>{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif

                    <label class="bp-form-field">
                        <span class="bp-form-label">Type</span>
                        <select class="bp-form-input" id="offer_type" name="offer_type">
                            @foreach($offerTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('offer_type', $offer->offer_type ?? \App\Offer::TYPE_CPA) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Status</span>
                        <select class="bp-form-input" id="status" name="status">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('status', $offer->status ?? 1) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Payout</span>
                        <input class="bp-form-input" type="number" step="0.01" min="0" name="payout" maxlength="12" value="{{ old('payout', $offer->payout) }}" id="payout" required>
                        <span class="bp-form-note">The amount paid per conversion to the assigned {{ strtolower($affiliateTypeLabelPlural) }}.</span>
                    </label>

                    <label class="bp-form-field bp-form-field-full">
                        <span class="bp-form-label">Description</span>
                        <textarea class="bp-form-input bp-form-textarea" name="description" maxlength="555" id="description" rows="4">{{ old('description', $offer->description) }}</textarea>
                    </label>

                    <label class="bp-form-field bp-form-field-full">
                        <span class="bp-form-label">Destination URL</span>
                        <input class="bp-form-input" type="text" name="url" maxlength="555" id="url" value="{{ old('url', $offer->url) }}" required>
                        <span class="bp-form-note">Traffic will be directed to this URL. Tracking placeholders below can be inserted anywhere in the link.</span>
                    </label>
                </div>

                <div class="mt-6 grid gap-4 xl:grid-cols-2">
                    <div class="bp-inline-note">
                        <strong>Core variables</strong>
                        <span>#affid#</span>
                        <span>#user#</span>
                        <span>#clickid#</span>
                        <span>#offid#</span>
                    </div>
                    <div class="bp-inline-note">
                        <strong>Sub IDs</strong>
                        <span>#sub1#</span>
                        <span>#sub2#</span>
                        <span>#sub3#</span>
                        <span>#sub4#</span>
                        <span>#sub5#</span>
                    </div>
                </div>
            </section>

            @unless($isEdit)
                <section class="bp-card value_span8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Assignment</p>
                            <h3 class="bp-section-title value_span9">Choose {{ strtolower($affiliateTypeLabelPlural) }} for this offer</h3>
                        </div>
                        <p class="bp-table-meta">The selected list is what gets submitted with the offer when you create it.</p>
                    </div>

                    <div class="mt-6 bp-selection-grid bp-selection-grid-wide">
                        <article class="bp-selection-card">
                            <p class="bp-section-kicker">Available</p>
                            <h4 class="bp-selection-title" data-available-title>Unassigned {{ strtolower($affiliateTypeLabelPlural) }}</h4>
                            <div class="mt-4 bp-offer-search">
                                <label class="bp-detail-label" for="availableUsersSearch">Search available</label>
                                <input id="availableUsersSearch" class="bp-search-input" type="text" placeholder="Filter available records">
                            </div>
                            <select id="availableUsers" class="bp-dual-select mt-4" multiple size="14" aria-label="Available users"></select>
                        </article>

                        <div class="bp-transfer-controls" aria-hidden="true">
                            <button type="button" class="bp-button-secondary" data-transfer="add">Add selected</button>
                            <button type="button" class="bp-button-secondary" data-transfer="add-all">Add all</button>
                            <button type="button" class="bp-button-secondary" data-transfer="remove">Remove selected</button>
                            <button type="button" class="bp-button-secondary" data-transfer="remove-all">Remove all</button>
                        </div>

                        <article class="bp-selection-card">
                            <p class="bp-section-kicker">Assigned</p>
                            <h4 class="bp-selection-title" data-assigned-title>Assigned {{ strtolower($affiliateTypeLabelPlural) }}</h4>
                            <div class="mt-4 bp-offer-search">
                                <label class="bp-detail-label" for="assignedUsersSearch">Search assigned</label>
                                <input id="assignedUsersSearch" class="bp-search-input" type="text" placeholder="Filter assigned records">
                            </div>
                            <select id="assignedUsers" class="bp-dual-select mt-4" name="users[]" multiple size="14" aria-label="Assigned users"></select>
                            <p class="mt-4 bp-table-meta" data-assignment-count>0 selected</p>
                        </article>
                    </div>
                </section>
            @else
                <section class="bp-card value_span8">
                    <div class="bp-inline-note">
                        <strong>Assignment management</strong>
                        <span>Use Manage Offers or Multi-Assign Offers to change which {{ strtolower($affiliateTypeLabelPlural) }} currently have access to this offer.</span>
                    </div>
                </section>
            @endunless

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">{{ $submitLabel ?? 'Create offer' }}</button>
                <a href="/offer/manage" class="bp-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        (() => {
            const form = document.querySelector('[data-offer-create]');
            if (!form) {
                return;
            }

            const availableSelect = document.getElementById('availableUsers');
            const assignedSelect = document.getElementById('assignedUsers');
            const availableSearch = document.getElementById('availableUsersSearch');
            const assignedSearch = document.getElementById('assignedUsersSearch');
            const assignmentCount = document.querySelector('[data-assignment-count]');
            const availableTitle = document.querySelector('[data-available-title]');
            const assignedTitle = document.querySelector('[data-assigned-title]');
            const transferButtons = Array.from(form.querySelectorAll('[data-transfer]'));
            const initialAssignedIds = new Set(@json(array_map('intval', old('users', []))));

            if (!availableSelect || !assignedSelect || !availableSearch || !assignedSearch || !assignmentCount || !availableTitle || !assignedTitle) {
                return;
            }

            let availableUsers = [];
            let assignedUsers = [];

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const sortUsers = (users) => [...users].sort((left, right) => left.name.localeCompare(right.name));

            const updateTitles = () => {
                const label = @json(strtolower($affiliateTypeLabelPlural));
                availableTitle.textContent = `Unassigned ${label}`;
                assignedTitle.textContent = `Assigned ${label}`;
                assignmentCount.textContent = `${assignedUsers.length} selected`;
            };

            const renderSelect = (select, users, query) => {
                const normalizedQuery = query.trim().toLowerCase();
                const filteredUsers = normalizedQuery === ''
                    ? users
                    : users.filter((user) => user.name.toLowerCase().includes(normalizedQuery) || String(user.id).includes(normalizedQuery));

                select.innerHTML = filteredUsers.map((user) => `<option value="${user.id}">${escapeHtml(user.name)}</option>`).join('');
            };

            const render = () => {
                availableUsers = sortUsers(availableUsers);
                assignedUsers = sortUsers(assignedUsers);
                renderSelect(availableSelect, availableUsers, availableSearch.value);
                renderSelect(assignedSelect, assignedUsers, assignedSearch.value);
                updateTitles();
            };

            const fetchUsers = async () => {
                availableSelect.innerHTML = '<option>Loading...</option>';
                assignedSelect.innerHTML = '';
                assignedUsers = [];
                updateTitles();

                try {
                    const response = await fetch(`/offer/assignableUsers?user_type={{ \App\Privilege::ROLE_AFFILIATE }}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load assignable users');
                    }

                    availableUsers = await response.json();
                    if (initialAssignedIds.size) {
                        assignedUsers = availableUsers.filter((user) => initialAssignedIds.has(Number(user.id)));
                        availableUsers = availableUsers.filter((user) => !initialAssignedIds.has(Number(user.id)));
                    }
                    render();
                } catch (error) {
                    availableUsers = [];
                    availableSelect.innerHTML = '<option>Unable to load users</option>';
                    updateTitles();
                }
            };

            const moveUsers = (fromUsers, toUsers, selectedIds) => {
                const selectedSet = new Set(selectedIds.map(String));
                const moving = fromUsers.filter((user) => selectedSet.has(String(user.id)));
                const staying = fromUsers.filter((user) => !selectedSet.has(String(user.id)));

                moving.forEach((user) => {
                    if (!toUsers.some((existing) => String(existing.id) === String(user.id))) {
                        toUsers.push(user);
                    }
                });

                return staying;
            };

            transferButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const action = button.getAttribute('data-transfer');

                    if (action === 'add') {
                        availableUsers = moveUsers(availableUsers, assignedUsers, Array.from(availableSelect.selectedOptions).map((option) => option.value));
                    }

                    if (action === 'add-all') {
                        availableUsers = moveUsers(availableUsers, assignedUsers, availableUsers.map((user) => user.id));
                    }

                    if (action === 'remove') {
                        assignedUsers = moveUsers(assignedUsers, availableUsers, Array.from(assignedSelect.selectedOptions).map((option) => option.value));
                    }

                    if (action === 'remove-all') {
                        assignedUsers = moveUsers(assignedUsers, availableUsers, assignedUsers.map((user) => user.id));
                    }

                    render();
                });
            });

            availableSearch.addEventListener('input', render);
            assignedSearch.addEventListener('input', render);

            availableSelect.addEventListener('dblclick', () => {
                availableUsers = moveUsers(availableUsers, assignedUsers, Array.from(availableSelect.selectedOptions).map((option) => option.value));
                render();
            });

            assignedSelect.addEventListener('dblclick', () => {
                assignedUsers = moveUsers(assignedUsers, availableUsers, Array.from(assignedSelect.selectedOptions).map((option) => option.value));
                render();
            });

            form.addEventListener('submit', () => {
                Array.from(assignedSelect.options).forEach((option) => {
                    option.selected = true;
                });
            });

            fetchUsers();
        })();
    </script>
@endsection
