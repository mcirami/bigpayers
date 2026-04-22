@php
    /** @var \LeadMax\TrackYourStats\System\Company $company */
    $logoPath = $company->getImgDir() . '/logo.png';
    $faviconPath = $company->getImgDir() . '/favicon.ico';
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
    $supportMessengerUsername = $company->getMessengerUsername();
    $supportMessengerType = $company->getMessengerType();
    $themeClass = $loginTheme ? 'login-theme-' . str_replace(['/', '\\', ' '], '-', $loginTheme) : 'login-theme-default';
@endphp
<!DOCTYPE html>
<html
    lang="en"
    style="
        --login-brand-strong: {{ $brandStrong }};
        --login-brand-soft: {{ $brandSoft }};
        --login-brand-deep: {{ $brandDeep }};
        --login-page-base: {{ $pageBase }};
        --login-page-tint: {{ $pageTint }};
        --login-text-muted: {{ $textMuted }};
    "
>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/ico" href="{{ $faviconPath }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $webroot }}css/company.css">
    @if($themeCssUrl)
        <link rel="stylesheet" type="text/css" href="{{ $themeCssUrl }}">
    @endif
    <title>{{ $company->getShortHand() }}</title>
</head>
<body class="login-page {{ $themeClass }}">
<div class="login-shell">
    <section class="login-shell__brand value_span1">
        <div class="login-shell__brand-inner">
            <a href="{{ $webroot }}" class="login-shell__logo-link" aria-label="{{ $company->getShortHand() }} home">
                <img src="{{ $logoPath }}" alt="{{ $company->getShortHand() }} logo" class="login-shell__logo">
            </a>
            <p class="login-shell__eyebrow">Affiliate login</p>
            <h1 class="login-shell__title value_span2">{{ env('LOGIN_PAGE_TEXT') }}</h1>
            <p class="login-shell__copy">
                Access your dashboard, reporting, offers, and account tools from a login experience that now follows your install’s live brand settings.
            </p>

            <div class="login-shell__support">
                @if($company->getEmail())
                    <div class="login-shell__support-item">
                        <span class="login-shell__support-label">Support Email</span>
                        <a href="mailto:{{ $company->getEmail() }}" class="login-shell__support-value">{{ $company->getEmail() }}</a>
                    </div>
                @endif

                @if($supportMessengerUsername)
                    <div class="login-shell__support-item">
                        <span class="login-shell__support-label">{{ $supportMessengerType }}</span>
                        <span class="login-shell__support-value">{{ $supportMessengerUsername }}</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="login-shell__panel value_span8">
        <div class="login-card">
            <p class="login-card__kicker">Sign in</p>
            <h2 class="login-card__title value_span9">Welcome back</h2>
            <p class="login-card__copy value_span10">Use your account credentials to continue into the affiliate workspace.</p>

            <form method="post" action="/login" class="login-form">
                {!! csrf_field() !!}
                @if(request()->has('redirectUri'))
                    <input type="hidden" name="redirectUri" value="{{ request('redirectUri') }}"/>
                @endif

                @if(isset($error))
                    <div class="login-alert">
                        <strong>Unable to sign in.</strong>
                        <span>{!! $error !!}</span>
                    </div>
                @endif

                <label class="login-field">
                    <span class="login-field__label">Username or email</span>
                    <input type="text" name="txt_uname_email" value="{{ $user->autoFillEmail }}" placeholder="Enter username or email" required/>
                </label>

                <label class="login-field">
                    <span class="login-field__label">Password</span>
                    <input type="password" name="txt_password" placeholder="Enter password" required/>
                </label>

                <div class="login-form__row">
                    <a class="login-form__link value_span5" href="aff_help.php">{{ env('FORGOT_PASS_LINK_TEXT') }}</a>
                </div>

                <button type="submit" name="button" class="login-form__submit value_span11 value_span2 value_span4">
                    {{ env('LOGIN_PAGE_BUTTON_TEXT') }}
                </button>
            </form>
        </div>
    </section>
</div>
</body>
</html>
