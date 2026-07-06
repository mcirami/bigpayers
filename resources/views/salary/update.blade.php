@extends('layouts.dashboard-shell')

@section('page-title', 'Update Salary')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Payroll Workspace</p>
                    <h2 class="bp-section-title">Update salary for {{ $user->user_name }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Adjust the amount or active status for this affiliate salary.
                    </p>
                </div>

                <a href="/salaries/edit" class="bp-button-secondary">Back to salary setup</a>
            </div>
        </section>

        <section class="bp-card">
            <form action="/user/{{ $user->idrep }}/salary/update" method="post" class="space-y-6">
                @csrf

                <div class="bp-form-grid md:grid-cols-3">
                    <label class="bp-form-field">
                        <span class="bp-form-label">Salary</span>
                        <input class="bp-form-input" type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $salary->salary) }}" required>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Status</span>
                        <select class="bp-form-input" name="status">
                            <option value="1" @selected((int) old('status', $salary->status) === 1)>Active</option>
                            <option value="0" @selected((int) old('status', $salary->status) === 0)>In-Active</option>
                        </select>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Last Update</span>
                        <input class="bp-form-input" type="text" value="{{ \Carbon\Carbon::createFromTimestamp($salary->last_update)->diffForHumans() }}" readonly>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button class="bp-button-primary" type="submit">Save salary</button>
                </div>
            </form>
        </section>
    </div>
@endsection
