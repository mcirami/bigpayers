@php
    $company = \App\Company::instance()->first();
    $companyName = $company ? ($company->getShortHand() ?: 'Chat Track Pro') : 'Chat Track Pro';
    $faviconPath = $company ? $company->getBrandAssetUrl('favicon.ico') : asset('favicon.ico');
    $dashboardShellCssPath = public_path('css/dashboard-shell.css');
    $dashboardShellCssUrl = asset('css/dashboard-shell.css') . (file_exists($dashboardShellCssPath) ? '?v=' . filemtime($dashboardShellCssPath) : '');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ $dashboardShellCssUrl }}" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" type="image/ico" href="{{ $faviconPath }}"/>
    <title>{{ $companyName }}</title>
</head>
<body class="bp-public-landing">
    <header class="bp-public-hero">
        <nav class="bp-public-nav" aria-label="Primary">
            <a class="bp-public-logo" href="/">
                <img src="{{ asset('images/logo.png') }}" alt="{{ $companyName }}">
            </a>

            <div class="bp-public-nav-links">
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="#contact">Contact</a>
                <a class="bp-public-button bp-public-button-quiet" href="/login">Login</a>
            </div>
        </nav>

        <div class="bp-public-hero-inner">
            <div class="bp-public-hero-copy">
                <p class="bp-public-kicker">Affiliate tracking workspace</p>
                <h1>{{ $companyName }}</h1>
                <p>
                    Custom tracking for serious marketers, with real-time reporting, tiered user management,
                    private domains, and offer controls in one focused platform.
                </p>
                <div class="bp-public-actions">
                    <a class="bp-public-button" href="skype:live:.cid.1a53fdcac7cdeced?chat">Contact Us</a>
                    <a class="bp-public-link" href="#features">Explore Features</a>
                </div>
            </div>

            <div class="bp-public-hero-media" aria-hidden="true">
                <img src="{{ asset('images/header_img.jpg') }}" alt="">
            </div>
        </div>
    </header>

    <main>
        <section class="bp-public-section" id="features">
            <div class="bp-public-section-heading">
                <p class="bp-public-kicker">Features</p>
                <h2>Built for operators who need the numbers now</h2>
                <p>
                    Track offers, users, clicks, postbacks, and sales across your network without splitting the workflow
                    across disconnected tools.
                </p>
            </div>

            <div class="bp-public-feature-grid">
                <article class="bp-public-feature">
                    <h3>Unlimited {{ $affiliateTypeLabelPlural }}</h3>
                    <p>Support every promoter in your network with live visibility into clicks, conversions, and payouts.</p>
                </article>
                <article class="bp-public-feature">
                    <h3>Unlimited {{ $accountTypeLabelPlural }}</h3>
                    <p>Give each {{ strtolower($accountTypeLabel) }} the structure they need while preserving admin-level oversight.</p>
                </article>
                <article class="bp-public-feature">
                    <h3>Public and Private Offers</h3>
                    <p>Run public, private, and requestable campaigns with routing and assignment controls.</p>
                </article>
                <article class="bp-public-feature">
                    <h3>Real-Time Stats</h3>
                    <p>Monitor performance as traffic moves through your offers, postbacks, and user tree.</p>
                </article>
                <article class="bp-public-feature">
                    <h3>Unique Domains</h3>
                    <p>Use custom network domains and branded paths that fit the way your operation presents itself.</p>
                </article>
                <article class="bp-public-feature">
                    <h3>Postback URLs</h3>
                    <p>Keep conversion tracking accurate with flexible postback URLs and offer-level controls.</p>
                </article>
            </div>
        </section>

        <section class="bp-public-split" id="about">
            <div class="bp-public-split-media">
                <img src="{{ asset('images/dark-section-image.png') }}" alt="">
            </div>
            <div class="bp-public-split-copy">
                <p class="bp-public-kicker">About</p>
                <h2>Straight from the pros</h2>
                <p>
                    Whether you are managing a small group or several offices in a large firm, {{ $companyName }} gives
                    you custom designs, offer controls, permissions, and real-time tracking in one place.
                </p>
            </div>
        </section>

        <section class="bp-public-section">
            <div class="bp-public-section-heading">
                <p class="bp-public-kicker">Structure</p>
                <h2>Tiered user setup</h2>
            </div>

            <div class="bp-public-role-grid">
                <article class="bp-public-role">
                    <img src="{{ asset('images/img_networkowner.png') }}" alt="">
                    <h3>Admins</h3>
                    <p>Admins can manage users, offers, traffic, and performance across the full network.</p>
                </article>
                <article class="bp-public-role">
                    <img src="{{ asset('images/img_merchant.png') }}" alt="">
                    <h3>{{ $accountTypeLabelPlural }}</h3>
                    <p>{{ $accountTypeLabelPlural }} can create and manage their own {{ strtolower($affiliateTypeLabel) }} accounts.</p>
                </article>
                <article class="bp-public-role">
                    <img src="{{ asset('images/img_affiliate.png') }}" alt="">
                    <h3>{{ $affiliateTypeLabelPlural }}</h3>
                    <p>{{ $affiliateTypeLabelPlural }} promote offers and track their clicks and sales in real time.</p>
                </article>
            </div>
        </section>

        <section class="bp-public-cta" id="contact">
            <p class="bp-public-kicker">Interested in your own network?</p>
            <h2>Get in touch</h2>
            <p>We will walk you through each stage so you have the tracking tools you need to be successful.</p>
            <a class="bp-public-button" href="skype:live:.cid.1a53fdcac7cdeced?chat">Contact Us</a>
        </section>
    </main>

    <footer class="bp-public-footer">
        <h2>{{ $companyName }}</h2>
        <nav aria-label="Footer">
            <a href="#about">About</a>
            <a href="#features">Features</a>
            <a href="#contact">Contact</a>
        </nav>
        <p>&copy; {{ $companyName }}</p>
    </footer>
</body>
</html>
