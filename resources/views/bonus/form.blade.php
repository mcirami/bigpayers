@extends('layouts.dashboard-shell')

@section('page-title', $mode === 'edit' ? 'Edit Bonus' : 'Create Bonus')

@section('content')
    @php
        $isEdit = $mode === 'edit';
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Bonuses Workspace</p>
                    <h2 class="bp-section-title">{{ $isEdit ? 'Edit bonus' : 'Create bonus' }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Define the sales threshold, payout, status, and default user assignments.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/bonuses" class="bp-button-secondary">Back to bonuses</a>
                    @if($isEdit)
                        <a href="/bonuses/{{ $bonus->id }}/assign" class="bp-button-primary">Assign users</a>
                    @endif
                </div>
            </div>
        </section>

        <form method="post" action="{{ $action }}" class="space-y-6 lg:space-y-8">
            @csrf

            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Bonus Details</p>
                    <h3 class="bp-section-title">Definition</h3>
                </div>

                <div class="bp-form-grid mt-6 md:grid-cols-2">
                    <label class="bp-form-field">
                        <span class="bp-form-label">Name</span>
                        <input class="bp-form-input" type="text" name="name" value="{{ old('name', $bonus->name ?? '') }}" required>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Sales Required</span>
                        <input class="bp-form-input" type="number" min="0" name="sales_required" value="{{ old('sales_required', $bonus->sales_required ?? 0) }}" required>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Payout</span>
                        <input class="bp-form-input" type="number" min="0" step="0.01" name="payout" value="{{ old('payout', $bonus->payout ?? '0.00') }}" required>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Status</span>
                        <select class="bp-form-input" name="status">
                            <option value="1" @selected((int) old('status', $bonus->is_active ?? 1) === 1)>Active</option>
                            <option value="0" @selected((int) old('status', $bonus->is_active ?? 1) === 0)>In-Active</option>
                        </select>
                    </label>

                    <label class="bp-inline-toggle">
                        <span>Inheritable</span>
                        <input class="fixCheckBox" type="checkbox" name="inheritable" value="1" @checked((int) old('inheritable', $bonus->inheritable ?? 0) === 1)>
                    </label>
                </div>
            </section>

            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Default Assignments</p>
                    <h3 class="bp-section-title">Users</h3>
                </div>

                <div class="mt-6 grid gap-5 xl:grid-cols-3">
                    @forelse($userGroups as $group)
                        <section class="rounded-md border border-slate-200/70 bg-white/60 p-4">
                            <div>
                                <p class="bp-section-kicker">{{ $group['name'] }}</p>
                                <h4 class="bp-selection-title">Assigned {{ strtolower($group['name']) }}</h4>
                            </div>

                            <div class="bp-checklist mt-5">
                                @foreach($group['users'] as $user)
                                    <label class="bp-checklist-item">
                                        <input class="fixCheckBox" type="checkbox" name="user_ids[]" value="{{ $user->idrep }}" @checked($user->assigned)>
                                        <span>{{ $user->user_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <div class="bp-inline-note">
                            <strong>No assignable users</strong>
                            <span>Your current permissions do not expose any user groups for assignment.</span>
                        </div>
                    @endforelse
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="bp-button-primary">{{ $isEdit ? 'Save bonus' : 'Create bonus' }}</button>
            </div>
        </form>
    </div>
@endsection
