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

    $errorMessages = [
        'USERNAME_OR_EMAIL_EXISTS' => 'The username or email you entered already exists in the system.',
        'INVALID_EMAIL' => 'The email you entered is invalid.',
        'INVALID_USERNAME' => 'The username must be at least 4 characters long and contain no special characters.',
        'PASSWORD_MISMATCH' => 'Passwords do not match, or they are too short.',
        'MISSING_OR_INVALID_FIELDS' => 'Some required fields are missing or invalid. Please double-check the form.',
        'DATABASE_ERROR' => 'Something went wrong while creating the account. Please try again.',
    ];
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
<body class="login-page login-page-signup">
<div class="login-shell">
    <section class="login-shell__brand value_span1">
        <div class="login-shell__brand-inner">
            <a href="{{ $webroot }}" class="login-shell__logo-link" aria-label="{{ $company->getShortHand() }} home">
                <img src="{{ $logoPath }}" alt="{{ $company->getShortHand() }} logo" class="login-shell__logo">
            </a>
            <p class="login-shell__eyebrow">Affiliate signup</p>
            <h1 class="login-shell__title value_span2">Create your account</h1>
            <p class="login-shell__copy">
                Register for access to the affiliate workspace, reporting tools, and brand-specific tracking environment.
            </p>
        </div>
    </section>

    <section class="login-shell__panel value_span8">
        <div class="login-card login-card-signup">
            <p class="login-card__kicker">Sign up</p>
            <h2 class="login-card__title value_span9">Join {{ $company->getShortHand() }}</h2>
            <p class="login-card__copy value_span10">Fill out the details below and we’ll route your account through the correct approval flow.</p>

            <form method="post" action="{{ request()->path() === 'signup.php' ? '/signup.php' : '/signup' }}" class="login-form">
                {!! csrf_field() !!}
                @if($mid !== '')
                    <input type="hidden" name="mid" value="{{ $mid }}">
                @endif

                @if($errorCode && isset($errorMessages[$errorCode]))
                    <div class="login-alert">
                        <strong>Unable to complete signup.</strong>
                        <span>{{ $errorMessages[$errorCode] }}</span>
                    </div>
                @endif

                <div class="signup-grid">
                    <label class="login-field">
                        <span class="login-field__label">First Name</span>
                        <input type="text" name="tys_first_name" value="{{ $formValues['tys_first_name'] ?? '' }}" required>
                    </label>

                    <label class="login-field">
                        <span class="login-field__label">Last Name</span>
                        <input type="text" name="tys_last_name" value="{{ $formValues['tys_last_name'] ?? '' }}" required>
                    </label>

                    <label class="login-field login-field--full">
                        <span class="login-field__label">Email</span>
                        <input type="email" name="tys_email" value="{{ $formValues['tys_email'] ?? '' }}" required>
                    </label>

                    <label class="login-field">
                        <span class="login-field__label">Username</span>
                        <input type="text" name="tys_username" value="{{ $formValues['tys_username'] ?? '' }}" required>
                    </label>

                    <label class="login-field">
                        <span class="login-field__label">Company</span>
                        <input type="text" name="tys_company_name" value="{{ $formValues['tys_company_name'] ?? '' }}">
                    </label>

                    <label class="login-field">
                        <span class="login-field__label">Password</span>
                        <input type="password" name="tys_password" required>
                    </label>

                    <label class="login-field">
                        <span class="login-field__label">Confirm Password</span>
                        <input type="password" name="tys_confirm_password" required>
                    </label>

                    <label class="login-field login-field--full">
                        <span class="login-field__label">Telegram</span>
                        <input type="text" name="tys_telegram" value="{{ $formValues['tys_telegram'] ?? '' }}">
                    </label>
                </div>

                <button type="submit" name="button" class="login-form__submit value_span5-1 value_span2 value_span4">
                    Sign Up
                </button>
            </form>
        </div>
    </section>
</div>
</body>
</html>
