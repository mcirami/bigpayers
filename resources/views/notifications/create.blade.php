@extends('layouts.dashboard-shell')

@section('page-title', 'Create Notification')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Inbox Workspace</p>
                    <h2 class="bp-section-title value_span9">Send a notification</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Compose an internal notification, target the right recipients, and optionally mirror the message by email without going through the old dual-table composer.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <article class="bp-link-card">
                        <p class="bp-link-label">Recipients</p>
                        <p class="bp-link-value">{{ $recipientCount }} selectable users</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Delivery</p>
                        <p class="bp-link-value">Inbox by default, email optional</p>
                    </article>
                </div>
            </div>
        </section>

        <form method="post" action="/notifications/create" class="space-y-6 lg:space-y-8">
            @csrf

            <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                <section class="bp-card value_span8">
                    <div>
                        <p class="bp-section-kicker">Message Setup</p>
                        <h3 class="bp-section-title value_span9">Title and body</h3>
                    </div>

                    <div class="mt-6 bp-form-grid">
                        <div class="bp-form-field bp-form-field-full">
                            <label class="bp-form-label" for="title">Title</label>
                            <input id="title" name="title" class="bp-form-input" type="text" value="{{ old('title') }}" required>
                        </div>

                        <div class="bp-form-field bp-form-field-full">
                            <label class="bp-form-label" for="body">Body</label>
                            <textarea id="body" name="body" class="bp-form-input bp-form-textarea" required>{{ old('body') }}</textarea>
                        </div>

                        <label class="bp-choice-pill bp-form-field-full">
                            <input type="checkbox" name="sendEmails" value="1" {{ old('sendEmails') ? 'checked' : '' }}>
                            <span>Send matching email notifications</span>
                        </label>
                    </div>
                </section>

                <section class="bp-card value_span8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Recipients</p>
                            <h3 class="bp-section-title value_span9">Choose who receives it</h3>
                        </div>

                        <div class="bp-toolbar-cluster">
                            <div class="bp-form-field">
                                <label class="bp-form-label" for="recipientSearch">Search users</label>
                                <input id="recipientSearch" type="text" class="bp-form-input" placeholder="Search by username">
                            </div>

                            <div class="bp-form-field">
                                <label class="bp-form-label" for="recipientType">Type</label>
                                <select id="recipientType" class="bp-form-input">
                                    <option value="all">All</option>
                                    @foreach ($recipientGroups as $group)
                                        <option value="{{ strtolower($group['type']) }}">{{ $group['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <button type="button" class="bp-button-secondary" id="selectVisibleRecipients">Select visible</button>
                        <button type="button" class="bp-button-secondary" id="clearVisibleRecipients">Clear visible</button>
                    </div>

                    <div class="mt-6 bp-checklist" id="recipientChecklist">
                        @foreach ($recipientGroups as $group)
                            @foreach ($group['users'] as $user)
                                <label
                                    class="bp-checklist-item bp-notification-recipient"
                                    data-type="{{ strtolower($group['type']) }}"
                                    data-search="{{ strtolower($user->name . ' ' . $group['type']) }}"
                                >
                                    <input type="checkbox" name="userList[]" value="{{ $user->id }}" {{ in_array((string) $user->id, old('userList', []), true) ? 'checked' : '' }}>
                                    <span class="flex-1">
                                        <strong>{{ $user->name }}</strong>
                                        <span class="bp-form-note">{{ $group['type'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bp-button-primary">Send notification</button>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <script>
        (() => {
            const searchInput = document.getElementById('recipientSearch');
            const typeSelect = document.getElementById('recipientType');
            const recipientRows = Array.from(document.querySelectorAll('.bp-notification-recipient'));
            const selectVisible = document.getElementById('selectVisibleRecipients');
            const clearVisible = document.getElementById('clearVisibleRecipients');

            const applyFilters = () => {
                const search = (searchInput?.value || '').trim().toLowerCase();
                const type = typeSelect?.value || 'all';

                recipientRows.forEach((row) => {
                    const matchesSearch = (row.dataset.search || '').includes(search);
                    const matchesType = type === 'all' || row.dataset.type === type;
                    row.style.display = matchesSearch && matchesType ? '' : 'none';
                });
            };

            searchInput?.addEventListener('input', applyFilters);
            typeSelect?.addEventListener('change', applyFilters);

            selectVisible?.addEventListener('click', () => {
                recipientRows.forEach((row) => {
                    if (row.style.display !== 'none') {
                        const checkbox = row.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    }
                });
            });

            clearVisible?.addEventListener('click', () => {
                recipientRows.forEach((row) => {
                    if (row.style.display !== 'none') {
                        const checkbox = row.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            checkbox.checked = false;
                        }
                    }
                });
            });
        })();
    </script>
@endsection
