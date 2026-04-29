@php
    $company = \LeadMax\TrackYourStats\System\Company::loadFromSession();
    $userId = \LeadMax\TrackYourStats\System\Session::userID();
    $logoPath = $company->getBrandAssetUrl('logo.png');
    $faviconPath = $company->getBrandAssetUrl('favicon.ico');
    $companyName = $company->getShortHand() ?: 'BigPayers';
    $rawColors = $company->getColors();
    $dashboardShellCssPath = public_path('css/dashboard-shell.css');
    $companyCssPath = public_path('css/company.css');
    $dashboardShellCssUrl = asset('css/dashboard-shell.css') . (file_exists($dashboardShellCssPath) ? '?v=' . filemtime($dashboardShellCssPath) : '');
    $companyCssUrl = asset('css/company.css') . (file_exists($companyCssPath) ? '?v=' . filemtime($companyCssPath) : '');

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
    $primaryHref = $userId ? '/' : '/login';
    $primaryLabel = $userId ? 'Back to dashboard' : 'Go to login';
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
    <link rel="stylesheet" type="text/css" href="{{ $dashboardShellCssUrl }}">
    <link rel="stylesheet" type="text/css" href="{{ $companyCssUrl }}">
    <title>404 | {{ $companyName }}</title>
    <style>
        .bp-error-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem;
            background:
                radial-gradient(circle at top left, color-mix(in srgb, var(--brand-soft) 18%, transparent), transparent 32%),
                radial-gradient(circle at bottom right, color-mix(in srgb, var(--brand-strong) 16%, transparent), transparent 28%),
                linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.98));
        }

        .bp-error-card {
            width: min(100%, 58rem);
            display: grid;
            gap: 2rem;
            padding: 2.4rem;
            border-radius: 2rem;
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 32px 80px -48px rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(16px);
        }

        .bp-error-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .bp-error-brand img {
            width: 4.5rem;
            height: 4.5rem;
            object-fit: contain;
            border-radius: 1.25rem;
            background: rgba(15, 23, 42, 0.05);
            padding: 0.55rem;
        }

        .bp-error-grid {
            display: grid;
            gap: 1.5rem;
        }

        .bp-error-code {
            font-size: clamp(4rem, 16vw, 7rem);
            line-height: 0.92;
            font-weight: 800;
            letter-spacing: -0.06em;
            color: var(--brand-deep);
        }

        .bp-error-copy {
            display: grid;
            gap: 0.85rem;
            max-width: 42rem;
        }

        .bp-error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
            align-items: center;
        }

        .bp-error-note {
            color: #64748b;
            font-size: 0.92rem;
            line-height: 1.65;
        }

        @media (min-width: 900px) {
            .bp-error-card {
                grid-template-columns: 1.1fr 0.9fr;
                align-items: end;
            }

            .bp-error-grid {
                grid-column: 1 / 2;
            }
        }
    </style>
</head>
<body class="bp-shell">
    <main class="bp-error-page">
        <section class="bp-error-card value_span8">
            <div class="bp-error-grid">
                <a href="/" class="bp-error-brand">
                    <img src="{{ $logoPath }}" alt="{{ $companyName }} logo">
                    <div>
                        <p class="bp-section-kicker">Page not found</p>
                        <p class="bp-brand-title">{{ $companyName }}</p>
                    </div>
                </a>

                <div class="bp-error-code">404</div>

                <div class="bp-error-copy">
                    <h1 class="bp-section-title value_span9">That page isn’t available.</h1>
                    <p class="text-base leading-7 text-slate-600">
                        The link may be outdated, the page may have moved, or the address may have been typed incorrectly.
                    </p>
                    <p class="bp-error-note">
                        Try heading back to a working area of the site and starting again from there.
                    </p>
                </div>

                <div class="bp-error-actions">
                    <a href="{{ $primaryHref }}" class="bp-button-primary">{{ $primaryLabel }}</a>
                    <a href="/" class="bp-button-secondary">Home</a>
                </div>
            </div>

            <div class="bp-stat-card">
                <p class="bp-stat-label">Helpful next step</p>
                <p class="bp-stat-value">{{ $userId ? 'Resume your workflow' : 'Sign back in' }}</p>
                <p class="bp-stat-note">
                    {{ $userId
                        ? 'Use the dashboard to jump back into reports, offers, or account tools.'
                        : 'Open the login screen and continue into the branded workspace from there.' }}
                </p>
            </div>
        </section>
    </main>
</body>
</html>
