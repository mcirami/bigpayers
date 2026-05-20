@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Offer Access')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">Affiliate access for {{ $offer->offer_name ?: 'Offer #' . $offer->idoffer }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Choose which owned affiliates can run this offer.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/offer/manage" class="bp-button-secondary">Back to offers</a>
                    <a href="/offer/view/{{ $offer->idoffer }}" class="bp-button-primary">View offer</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Offer ID</p>
                <p class="bp-stat-value">{{ $offer->idoffer }}</p>
                <p class="bp-stat-note">Access changes apply only to this offer.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Owned Affiliates</p>
                <p class="bp-stat-value">{{ $users->count() }}</p>
                <p class="bp-stat-note">Affiliates currently inside your user tree.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Assigned Access</p>
                <p class="bp-stat-value">{{ $assignedCount }}</p>
                <p class="bp-stat-note">Affiliates already enabled for this offer.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <form action="/offer/{{ $offer->idoffer }}/access" method="post" class="space-y-6">
                @csrf

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Access Matrix</p>
                        <h3 class="bp-section-title value_span9">Assigned affiliates</h3>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="bp-button-secondary" onclick="setOfferAccess(true)">Check all</button>
                        <button type="button" class="bp-button-secondary" onclick="setOfferAccess(false)">Uncheck</button>
                    </div>
                </div>

                @if($users->isNotEmpty())
                    <div class="bp-checklist mt-6">
                        @foreach($users as $user)
                            <label class="bp-checklist-item">
                                <input
                                    class="fixCheckBox"
                                    type="checkbox"
                                    name="userList[]"
                                    value="{{ $user->idrep }}"
                                    @checked($user->has_offer)
                                >
                                <span>{{ $user->user_name }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 bp-inline-note">
                        <strong>No affiliates available</strong>
                        <span>There are no owned affiliates available for this offer access list.</span>
                    </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">
                        Save access
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        function setOfferAccess(checked) {
            $("#users input[type='checkbox'], input[name='userList[]']").prop('checked', checked);
        }
    </script>
@endsection
