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
<body class="login-page login-page-signup-success">
<div class="login-shell">
    <section class="login-shell__brand value_span1">
        <div class="login-shell__brand-inner">
            <a href="{{ $webroot }}" class="login-shell__logo-link" aria-label="{{ $company->getShortHand() }} home">
                <img src="{{ $logoPath }}" alt="{{ $company->getShortHand() }} logo" class="login-shell__logo">
            </a>
            <p class="login-shell__eyebrow">Account status</p>
            <h1 class="login-shell__title value_span2">
                @if($mid)
                    Welcome aboard
                @elseif($pending)
                    Pending approval
                @else
                    Thanks for signing up
                @endif
            </h1>
        </div>
    </section>

    <section class="login-shell__panel value_span8">
        <div class="login-card">
            @if($mid)
                <p class="login-card__kicker">Activated</p>
                <h2 class="login-card__title value_span9">Your account is live</h2>
                <p class="login-card__copy value_span10">
                    Your new account has been created and activated. Reach out to the manager who sent you your signup link if you need help getting started.
                </p>
            @elseif($pending)
                <p class="login-card__kicker">Still pending</p>
                <h2 class="login-card__title value_span9">Approval is still in progress</h2>
                <p class="login-card__copy value_span10">
                    Your account is still waiting for approval.
                    @if(!empty($messengerUsername))
                        Contact us for approval. Add username <span class="login-card__kicker">{{ $messengerUsername }}</span> to {{ $messengerType }} and send us a message.
                    @else
                        Please reach out to your contact if you need an update on timing.
                    @endif
                </p>
            @else
                <p class="login-card__kicker">Submitted</p>
                <h2 class="login-card__title value_span9">Thanks for registering</h2>
                <p class="login-card__copy value_span10">
                    Your signup has been received.
                    @if(!empty($messengerUsername))
                        Contact us for approval. Add username <span class="login-card__kicker">{{ $messengerUsername }}</span> to {{ $messengerType }} and send us a message.
                    @else
                        Once it is reviewed, your account will be approved and ready to use.
                    @endif
                </p>
            @endif

            <div class="login-form">
                <a href="/login" class="login-form__submit value_span11 value_span2 value_span4" style="text-decoration:none;">
                    Go to Login
                </a>
            </div>
        </div>
    </section>
</div>
</body>
</html>
