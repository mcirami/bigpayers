@extends('layouts.dashboard-shell')

@section('page-title', 'Mass Assign Offers')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title">Mass assign offers</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Select a user role, choose the users you want, then choose the offers to assign in one pass.
                    </p>
                </div>

                <div class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm">
                    {{ count($users) }} users • {{ count($offers) }} offers
                </div>
            </div>

        </section>

        <section class="bp-card">
            <form action="/offer/mass-assign?role={{ \App\Support\RequestContext::query('role', 3) }}" method="post" id="form" enctype="multipart/form-data" class="space-y-6">
                {{ csrf_field() }}

                <div class="bp-inline-toggle">
                    <label for="updatePayouts">Update offers payouts too</label>
                    <input class="fixCheckBox" type="checkbox" id="updatePayouts" name="updatePayouts" value="1">
                </div>

                <div class="bp-selection-grid">
                    <section class="bp-selection-card" id="users">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="bp-section-kicker">Users</p>
                                <h3 class="bp-selection-title">Choose recipients</h3>
                            </div>
                            <div class="flex gap-2">
                                <a class="bp-button-secondary" href="javascript:void(0);" onclick="checkBoxesInDiv('users')">Check all</a>
                                <a class="bp-button-secondary" href="javascript:void(0);" onclick="unCheckBoxesInDiv('users')">Uncheck</a>
                            </div>
                        </div>

                        <div class="bp-checklist mt-6">
                            @foreach($users as $user)
                                <label class="bp-checklist-item">
                                    <input class="fixCheckBox" type="checkbox" name="users[]" value="{{ $user->idrep }}">
                                    <span>{{ $user->user_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="bp-selection-card" id="offers">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="bp-section-kicker">Offers</p>
                                <h3 class="bp-selection-title">Choose inventory</h3>
                            </div>
                            <div class="flex gap-2">
                                <a class="bp-button-secondary" href="javascript:void(0);" onclick="checkBoxesInDiv('offers')">Check all</a>
                                <a class="bp-button-secondary" href="javascript:void(0);" onclick="unCheckBoxesInDiv('offers')">Uncheck</a>
                            </div>
                        </div>

                        <div class="bp-checklist mt-6">
                            @foreach($offers as $offer)
                                <label class="bp-checklist-item">
                                    <input class="fixCheckBox" type="checkbox" name="offers[]" value="{{ $offer->idoffer }}">
                                    <span>{{ $offer->offer_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="button" class="bp-button-primary">
                        Assign users
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
