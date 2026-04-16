@extends('layouts.dashboard-shell')

@section('page-title', 'Command Center')

@section('content')
    @php
        $user = \LeadMax\TrackYourStats\System\Session::userData();
        $roleLabels = [
            0 => 'God',
            1 => 'Admin',
            2 => 'Manager',
            3 => 'Affiliate',
        ];
        $roleLabel = $roleLabels[$userType] ?? 'Team Member';
        $menuSections = isset($navBar) && method_exists($navBar, 'getVisibleMenu') ? $navBar->getVisibleMenu() : [];
        $visibleLinkCount = collect($menuSections)->sum(function ($section) {
            return count($section['items']);
        });
        $signupLink = $userType == 2 ? $domain . $userId : null;
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-hero">
            <div class="relative z-10 flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <span class="bp-pill">
                        <i class="fas fa-compass" aria-hidden="true"></i>
                        Redesigned workspace
                    </span>
                    <p class="bp-hero-kicker mt-6">Main dashboard</p>
                    <h2 class="bp-hero-title">Welcome back, {{ $firstName }}.</h2>
                    <p class="bp-hero-copy">
                        This is the new dashboard foundation for the site: a cleaner shell, sharper hierarchy,
                        and a Tailwind-first layout that we can keep extending across reports, offers, and account tools.
                    </p>
                </div>

                <div class="relative z-10 grid gap-3 sm:grid-cols-2">
                    <a href="{{ $webroot }}aff_update.php?idrep={{ $userId }}" class="bp-button-secondary">Update profile</a>
                    <a href="/report/daily" class="bp-button-primary">Open daily report</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Role</p>
                <p class="bp-stat-value">{{ $roleLabel }}</p>
                <p class="bp-stat-note">Your dashboard now foregrounds access, shortcuts, and account context instead of burying everything in legacy panels.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Workspace Sections</p>
                <p class="bp-stat-value">{{ count($menuSections) }}</p>
                <p class="bp-stat-note">The left rail is generated from your real navigation permissions, so this shell is ready for the wider migration.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Quick Links</p>
                <p class="bp-stat-value">{{ $visibleLinkCount }}</p>
                <p class="bp-stat-note">Visible destinations are grouped for faster scanning on desktop and mobile instead of relying on nested dropdowns.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Tracking Access</p>
                <p class="bp-stat-value">{{ $canViewPostback ? 'Enabled' : 'Limited' }}</p>
                <p class="bp-stat-note">{{ $canViewPostback ? 'Your postback tools are active and ready to copy from this page.' : 'Postback tools are hidden for this account based on current permissions.' }}</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.95fr)]">
            <div class="space-y-6">
                <article class="bp-card">
                    <p class="bp-section-kicker">Account Snapshot</p>
                    <h3 class="bp-section-title">Profile details at a glance</h3>

                    <div class="mt-6 grid gap-x-8 md:grid-cols-2">
                        <div class="bp-detail-row">
                            <p class="bp-detail-label">Full Name</p>
                            <p class="bp-detail-value">{{ trim($user->first_name . ' ' . $user->last_name) }}</p>
                        </div>

                        <div class="bp-detail-row">
                            <p class="bp-detail-label">Username</p>
                            <p class="bp-detail-value">{{ $user->user_name }}</p>
                        </div>

                        <div class="bp-detail-row">
                            <p class="bp-detail-label">Email</p>
                            <p class="bp-detail-value"><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                        </div>

                        <div class="bp-detail-row">
                            <p class="bp-detail-label">Phone</p>
                            <p class="bp-detail-value">{{ $user->cell_phone ?: 'Not set' }}</p>
                        </div>

                        <div class="bp-detail-row">
                            <p class="bp-detail-label">Skype</p>
                            <p class="bp-detail-value">{{ $user->skype ?: 'Not set' }}</p>
                        </div>

                        <div class="bp-detail-row">
                            <p class="bp-detail-label">Security</p>
                            <p class="bp-detail-value">
                                <a href="{{ $webroot }}aff_update.php?idrep={{ $userId }}">Change password</a>
                            </p>
                        </div>
                    </div>
                </article>

                <article class="bp-card">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Navigation</p>
                            <h3 class="bp-section-title">Main work areas</h3>
                        </div>
                        <p class="text-sm text-slate-500">Built from your current permissions</p>
                    </div>

                    <div class="mt-6 bp-mini-list">
                        @foreach($menuSections as $section)
                            <div class="bp-mini-list-item">
                                <div>
                                    <p class="bp-mini-title">{{ $section['name'] }}</p>
                                    <p class="bp-mini-copy">
                                        {{ implode(' • ', collect($section['items'])->pluck('name')->take(3)->all()) }}
                                        @if(count($section['items']) > 3)
                                            • +{{ count($section['items']) - 3 }} more
                                        @endif
                                    </p>
                                </div>
                                <span class="bp-mini-badge">{{ count($section['items']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>

            <div class="space-y-6">
                <article class="bp-card">
                    <p class="bp-section-kicker">Quick Actions</p>
                    <h3 class="bp-section-title">Start from the highest-traffic tools</h3>

                    <div class="mt-6 grid gap-3">
                        <a href="/offer/manage" class="bp-button-secondary w-full justify-between">
                            <span>Manage offers</span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="/report/offer" class="bp-button-secondary w-full justify-between">
                            <span>Offer report</span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="/report/daily" class="bp-button-secondary w-full justify-between">
                            <span>Daily report</span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="{{ $webroot }}aff_update.php?idrep={{ $userId }}" class="bp-button-primary w-full justify-between">
                            <span>Edit my account</span>
                            <i class="fas fa-pen" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>

                <article class="bp-card">
                    <p class="bp-section-kicker">Tracking Links</p>
                    <h3 class="bp-section-title">Copy-ready endpoints</h3>

                    <div class="mt-6 space-y-4">
                        @if ($canViewPostback)
                            <div class="bp-link-card">
                                <div>
                                    <p class="bp-link-label">Postback URL</p>
                                    <p class="bp-link-value" data-copy-source="postback">{{ $postBackURL }}</p>
                                </div>
                                <button type="button" class="bp-copy-button" data-copy-button="postback">Copy link</button>
                            </div>
                        @endif

                        @if ($signupLink)
                            <div class="bp-link-card">
                                <div>
                                    <p class="bp-link-label">Manager Signup Link</p>
                                    <p class="bp-link-value" data-copy-source="signup">{{ $signupLink }}</p>
                                </div>
                                <button type="button" class="bp-copy-button" data-copy-button="signup">Copy link</button>
                            </div>
                        @endif

                        @unless ($canViewPostback || $signupLink)
                            <div class="bp-link-card">
                                <div>
                                    <p class="bp-link-label">Availability</p>
                                    <p class="bp-link-value">No copy-ready links are exposed for this account right now.</p>
                                </div>
                            </div>
                        @endunless
                    </div>
                </article>
            </div>
        </section>
    </div>
@endsection
