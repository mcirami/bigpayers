@php
    $company = \LeadMax\TrackYourStats\System\Company::loadFromSession();
    $user = \LeadMax\TrackYourStats\System\Session::userData();
    $menuSections = isset($navBar) && method_exists($navBar, 'getVisibleMenu') ? $navBar->getVisibleMenu() : [];
    $logoPath = $webroot . $company->getImgDir() . '/logo.png';
    $faviconPath = $webroot . $company->getImgDir() . '/favicon.ico';
    $companyName = $company->getShortHand();
    $rawColors = $company->getColors();

    $hexColor = function ($value, $fallback) {
        $candidate = is_string($value) ? ltrim($value, '#') : '';
        $candidate = preg_replace('/[^a-fA-F0-9]/', '', $candidate);

        if (strlen($candidate) === 3) {
            $candidate = $candidate[0] . $candidate[0] . $candidate[1] . $candidate[1] . $candidate[2] . $candidate[2];
        }

        if (strlen($candidate) !== 6) {
            return $fallback;
        }

        return '#' . strtoupper($candidate);
    };

    $brandStrong = $hexColor($rawColors[3] ?? null, '#0F766E');
    $brandSoft = $hexColor($rawColors[4] ?? null, '#F59E0B');
    $brandDeep = $hexColor($rawColors[0] ?? null, '#0F172A');
    $pageBase = $hexColor($rawColors[7] ?? null, '#F8FAFC');
    $pageTint = $hexColor($rawColors[6] ?? null, '#E2E8F0');
    $textMuted = $hexColor($rawColors[8] ?? null, '#475569');
    $topbarDate = \Illuminate\Support\Carbon::now()->format('l, F j');
@endphp

<!DOCTYPE html>
<html
    lang="en"
    class="bp-shell-html"
    style="
        --brand-strong: {{ $brandStrong }};
        --brand-soft: {{ $brandSoft }};
        --brand-deep: {{ $brandDeep }};
        --app-base: {{ $pageBase }};
        --app-tint: {{ $pageTint }};
        --app-muted: {{ $textMuted }};
    "
>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" type="image/ico" href="{{ $faviconPath }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $webroot }}css/font-awesome/css/all.css">
    <link rel="stylesheet" type="text/css" href="{{ $webroot }}css/dashboard-shell.css?v=20260416e">
    <link rel="stylesheet" type="text/css" href="{{ $webroot }}css/company.css">
    @stack('head')
    <title>{{ $companyName }}</title>
</head>
<body class="bp-shell">
    <div class="bp-mobile-overlay" data-dashboard-overlay></div>

    <div class="bp-shell-layout">
        <aside class="bp-sidebar bp-sidebar-desktop">
            <div class="space-y-8">
                <a href="{{ $webroot }}" class="bp-brand">
                    <span class="bp-brand-mark">
                        <img src="{{ $logoPath }}" alt="{{ $companyName }} logo"/>
                    </span>
                    <span class="bp-brand-copy">
                        <span class="bp-brand-title">{{ $companyName }}</span>
                        <span class="bp-brand-subtitle">Affiliate command center</span>
                    </span>
                </a>

                @include('layouts.partials.dashboard-navigation', ['menuSections' => $menuSections, 'mobile' => false])
            </div>

            <div class="bp-profile-panel">
                <p class="bp-profile-kicker">Signed in</p>
                <p class="bp-profile-name">{{ trim($user->first_name . ' ' . $user->last_name) }}</p>
                <p class="bp-profile-email">{{ $user->email }}</p>
                <a href="/logout" class="bp-sidebar-logout">Logout</a>
            </div>
        </aside>

        <aside class="bp-mobile-drawer" data-dashboard-nav>
            <div class="space-y-8">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ $webroot }}" class="bp-brand">
                        <span class="bp-brand-mark">
                            <img src="{{ $logoPath }}" alt="{{ $companyName }} logo"/>
                        </span>
                        <span class="bp-brand-copy">
                            <span class="bp-brand-title">{{ $companyName }}</span>
                            <span class="bp-brand-subtitle">Affiliate command center</span>
                        </span>
                    </a>

                    <button type="button" class="bp-icon-button" data-dashboard-close aria-label="Close navigation">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                @include('layouts.partials.dashboard-navigation', ['menuSections' => $menuSections, 'mobile' => true])
            </div>
        </aside>

        <div class="bp-shell-main">
            <header class="bp-topbar">
                <div class="flex items-center gap-4">
                    <button type="button" class="bp-icon-button lg:hidden" data-dashboard-open aria-label="Open navigation">
                        <i class="fas fa-bars" aria-hidden="true"></i>
                    </button>

                    <div>
                        <p class="bp-topbar-kicker">{{ $topbarDate }}</p>
                        <h1 class="bp-topbar-title">@yield('page-title', 'Dashboard')</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ $webroot }}aff_update.php?idrep={{ $user->idrep }}" class="bp-button-secondary">Edit account</a>
                    <a href="/logout" class="bp-button-primary">Logout</a>
                </div>
            </header>

            <main class="bp-shell-content">
                @if ($errors->any() || isset($notify) || isset($message))
                    <div class="mb-6 space-y-3">
                        @if ($errors->any())
                            <div class="bp-toast bp-toast-danger">
                                <p class="bp-toast-title">Please review the highlighted items.</p>
                                <ul class="list-disc pl-5 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (isset($notify))
                            <div class="bp-toast bp-toast-info">
                                <p class="bp-toast-title">{{ $notify }}</p>
                            </div>
                        @endif

                        @if (isset($message))
                            <div class="bp-toast bp-toast-info">
                                <p class="bp-toast-title">{{ $message }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (() => {
            const nav = document.querySelector('[data-dashboard-nav]');
            const overlay = document.querySelector('[data-dashboard-overlay]');
            const openers = document.querySelectorAll('[data-dashboard-open]');
            const closers = document.querySelectorAll('[data-dashboard-close], [data-dashboard-nav-link]');

            if (nav && overlay) {
                const toggle = (open) => {
                    nav.classList.toggle('is-open', open);
                    overlay.classList.toggle('is-open', open);
                    document.body.classList.toggle('bp-lock-scroll', open);
                };

                openers.forEach((button) => button.addEventListener('click', () => toggle(true)));
                closers.forEach((button) => button.addEventListener('click', () => toggle(false)));
                overlay.addEventListener('click', () => toggle(false));
            }

            document.querySelectorAll('[data-copy-button]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const targetName = button.getAttribute('data-copy-button');
                    const source = document.querySelector(`[data-copy-source="${targetName}"]`);

                    if (!source) {
                        return;
                    }

                    const text = source.textContent.trim();
                    const original = button.textContent;

                    try {
                        if (navigator.clipboard && window.isSecureContext) {
                            await navigator.clipboard.writeText(text);
                        } else {
                            const input = document.createElement('textarea');
                            input.value = text;
                            document.body.appendChild(input);
                            input.select();
                            document.execCommand('copy');
                            document.body.removeChild(input);
                        }

                        button.textContent = 'Copied';
                        setTimeout(() => {
                            button.textContent = original;
                        }, 1500);
                    } catch (error) {
                        button.textContent = 'Copy failed';
                        setTimeout(() => {
                            button.textContent = original;
                        }, 1500);
                    }
                });
            });
        })();
    </script>

    @stack('scripts')
    @yield('footer')
</body>
</html>
