@php
    /** @var \LeadMax\TrackYourStats\System\Company $company */
    $logoPath = $company->getBrandAssetUrl('logo.png');
    $faviconPath = $company->getBrandAssetUrl('favicon.ico');
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
            <p class="login-shell__eyebrow">Account recovery</p>
            <h1 class="login-shell__title value_span2">{{ env('FORGOT_PASS_PAGE_TEXT') }}</h1>
            <p class="login-shell__copy">
                Request a password reset link or choose a new password if you already have a valid reset token.
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
            <p class="login-card__kicker">{{ $token ? 'Reset password' : 'Forgot password' }}</p>
            <h2 class="login-card__title value_span9">
                {{ $token ? 'Create a new password' : 'We can help you back in' }}
            </h2>
            <p class="login-card__copy value_span10">
                @if($token && $tokenUserName)
                    Resetting password for {{ $tokenUserName }}.
                @elseif($token)
                    Enter a new password to finish resetting your account.
                @else
                    Enter the email address tied to your account and we’ll send reset instructions if it exists.
                @endif
            </p>

            @if($status)
                <div class="login-alert{{ $statusType === 'success' ? ' login-alert--success' : ($statusType === 'error' ? ' login-alert--error' : '') }}">
                    <span>{!! $status !!}</span>
                </div>
            @endif

            <form method="post" action="/forgot-password" class="login-form">
                {!! csrf_field() !!}

                @if($token)
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label class="login-field">
                        <span class="login-field__label">New password</span>
                        <input type="password" name="password" placeholder="Enter new password" required/>
                    </label>

                    <label class="login-field">
                        <span class="login-field__label">Confirm password</span>
                        <input type="password" name="confirmpassword" placeholder="Confirm new password" required/>
                    </label>
                @else
                    <label class="login-field">
                        <span class="login-field__label">Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required/>
                    </label>
                @endif

                <div class="login-form__row">
                    <a class="login-form__link value_span5" href="/login">{{ env('LOGIN_PAGE_BUTTON_TEXT') === 'Login Now' ? 'Back to login' : 'Return to login' }}</a>
                </div>

                <button type="submit" name="button" class="login-form__submit value_span11 value_span2 value_span4">
                    {{ env('FORGOT_PASS_PAGE_BUTTON_TEXT') }}
                </button>
            </form>
        </div>
    </section>
</div>
</body>
</html>
