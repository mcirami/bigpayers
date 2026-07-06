<?php
$webroot = getWebRoot();
$company = \App\Company::instance()->first();
$companyName = $company ? ($company->getShortHand() ?: 'BigPayers') : 'BigPayers';
$logoPath = $company ? $company->getBrandAssetUrl('logo.png') : asset('images/logo.png');
$faviconPath = $company ? $company->getBrandAssetUrl('favicon.ico') : asset('favicon.ico');

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/ico"
          href="{{ $faviconPath }}"/>
    <link rel="stylesheet" media="screen" type="text/css"
          href="<?php echo $webroot; ?>css/company.css"/>

    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/font-awesome/css/all.css">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        .contact-page {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            color: #0f172a;
            font-family: Lato, "Helvetica Neue", Arial, sans-serif;
        }

        .contact-page img {
            max-width: 100%;
        }

        .contact-header {
            background: #fff;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 18px 40px -34px rgba(15, 23, 42, 0.55);
        }

        .contact-header__inner {
            display: flex;
            width: min(1180px, calc(100% - 32px));
            min-height: 82px;
            margin: 0 auto;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .contact-brand {
            display: inline-flex;
            align-items: center;
            flex: 0 0 auto;
        }

        .contact-brand img {
            display: block;
            max-width: 190px;
            max-height: 54px;
            object-fit: contain;
        }

        .contact-nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 24px;
        }

        .contact-nav__links {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .contact-nav__links a {
            color: #334155;
            font-weight: 800;
            text-decoration: none;
        }

        .contact-nav__links a:hover {
            color: #2563eb;
        }

        .contact-nav__actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-nav-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            color: #0f172a;
            font-weight: 900;
            text-decoration: none;
        }

        .contact-nav-button--primary {
            border-color: transparent;
            background: #2563eb;
            color: #fff;
        }

        .contact-shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 72px 0;
        }

        .contact-card {
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 28px 70px -46px rgba(15, 23, 42, 0.55);
        }

        .contact-card__inner {
            display: grid;
            grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
            gap: 0;
        }

        .contact-intro {
            display: flex;
            min-height: 100%;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
            padding: 44px;
            background: #0f172a;
            color: #fff;
        }

        .contact-eyebrow {
            margin: 0 0 14px;
            color: #93c5fd;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .contact-title {
            margin: 0;
            color: #fff;
            font-size: clamp(2rem, 5vw, 3.4rem);
            line-height: 1.02;
        }

        .contact-copy {
            margin: 18px 0 0;
            color: #cbd5e1;
            font-size: 1rem;
            line-height: 1.8;
        }

        .contact-facts {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .contact-facts li {
            display: flex;
            gap: 12px;
            align-items: center;
            color: #e2e8f0;
            font-weight: 700;
        }

        .contact-facts i {
            color: #f59e0b;
        }

        .contact-form-panel {
            padding: 44px;
        }

        .contact-form-heading {
            margin-bottom: 28px;
        }

        .contact-form-heading h3 {
            margin: 0;
            color: #0f172a;
            font-size: 1.7rem;
            line-height: 1.2;
        }

        .contact-form-heading p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 0.96rem;
            line-height: 1.7;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .contact-field {
            display: grid;
            gap: 8px;
            margin-bottom: 18px;
        }

        .contact-field--full {
            grid-column: 1 / -1;
        }

        .contact-input {
            width: 100%;
            min-height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #fff;
            color: #0f172a;
            font-size: 0.96rem;
            line-height: 1.5;
            padding: 12px 14px;
            transition: border-color 160ms ease, box-shadow 160ms ease;
        }

        .contact-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
            outline: none;
        }

        .contact-input::placeholder {
            color: #94a3b8;
        }

        .contact-select {
            appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, #64748b 50%),
                linear-gradient(135deg, #64748b 50%, transparent 50%);
            background-position:
                calc(100% - 18px) 21px,
                calc(100% - 13px) 21px;
            background-repeat: no-repeat;
            background-size: 5px 5px;
        }

        .contact-checks {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .contact-check {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
        }

        .contact-check input {
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
        }

        .contact-fieldset {
            margin: 0 0 18px;
            padding: 0;
            border: 0;
        }

        .contact-fieldset legend {
            color: #0f172a;
            font-weight: 900;
        }

        .contact-fieldset p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .contact-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 22px;
            border: 0;
            border-radius: 999px;
            background: #2563eb;
            color: #fff;
            font-weight: 900;
            box-shadow: 0 16px 30px -20px rgba(37, 99, 235, 0.8);
            cursor: pointer;
        }

        .contact-footer {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 0 0 32px;
            color: #64748b;
            font-size: 0.9rem;
            text-align: center;
        }

        .contact-success {
            padding: 44px;
        }

        .contact-success h3 {
            margin: 0;
            color: #0f172a;
            font-size: 1.7rem;
        }

        .contact-success p {
            margin: 12px 0 0;
            color: #64748b;
            line-height: 1.7;
        }

        @media (max-width: 880px) {
            .contact-header__inner,
            .contact-nav,
            .contact-nav__links,
            .contact-nav__actions {
                flex-wrap: wrap;
                justify-content: center;
            }

            .contact-header__inner {
                padding: 18px 0;
            }

            .contact-card__inner,
            .contact-grid,
            .contact-checks {
                grid-template-columns: 1fr;
            }

            .contact-intro,
            .contact-form-panel,
            .contact-success {
                padding: 28px;
            }
        }
    </style>
    @if(\App\Services\RuntimeEnvironment::runsProductionSnippets())
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-127417577-1"></script>
        <script>window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}

			gtag('js', new Date());
			gtag('config', 'UA-127417577-1');</script>
    @endif


    <title>{{ $companyName }}</title>
</head>

<body class="contact-page">

<header class="contact-header">
    <div class="contact-header__inner">
        <a class="contact-brand" href="{{$webroot}}">
            <img src="{{ $logoPath }}"
                 alt="{{ $companyName }}"
                 title="{{ $companyName }}"/>
        </a>

        <nav class="contact-nav" aria-label="Primary navigation">
            <ul class="contact-nav__links">
                <li><a aria-current="page" href="{{$webroot}}#home">Home</a></li>
                <li><a href="{{$webroot}}#our_benefits">Our Benefits</a></li>
                <li><a href="{{$webroot}}#faq">FAQ</a></li>
                <li><a href="{{$webroot}}#contact">Contact</a></li>
            </ul>

            <div class="contact-nav__actions">
                <a class="contact-nav-button contact-nav-button--primary" href="{{$webroot}}login">Sign In</a>
                <a class="contact-nav-button" href="{{$webroot}}contact-us">Contact us</a>
            </div>
        </nav>
    </div>
</header>

<main class="contact-shell">
    <section class="contact-card">
        <div class="contact-card__inner">
            <aside class="contact-intro">
                <div>
                    <p class="contact-eyebrow">Network Access</p>
                    <h1 class="contact-title">Tell us about your team.</h1>
                    <p class="contact-copy">
                        Share the details we need to understand your traffic, team size, offer interests, and weekly volume.
                    </p>
                </div>

                <ul class="contact-facts">
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Affiliate and network onboarding</span></li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Offer fit and traffic review</span></li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Follow-up from the account team</span></li>
                </ul>
            </aside>

            <div class="contact-form-panel">
                @if(\App\Support\RequestContext::hasSessionValue('success'))
                    <div class="contact-success success">
                        <h3>Thanks for contacting CPA Admin!</h3>
                        <p>We will review your application and contact you soon to get you set up.</p>
                    </div>
                @else
                    <form method="POST" action="{{route('contact.send')}}" id="contact_us_form">
                        <input type="hidden" name="_token" id="csrf-token" value="{{ csrf_token() }}" />
                        <div class="contact-form-heading">
                            <h3>Contact Us</h3>
                            <p>Submit the form below to let us know about your group, experience, type of traffic and current sales volume.</p>
                        </div>
                        <div class="contact-grid">
                                <div class="contact-field">
                                    <input id="first_name" class="contact-input" type="text" name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                                    @if ($errors->has('first_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('first_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="contact-field">
                                    <input class="contact-input" id="last_name" type="text" name ="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
                                    @if ($errors->has('last_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('last_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="contact-field">
                                    <input class="contact-input" id="office_name" type="text" name ="office_name" placeholder="Group/Office Name" value="{{ old('office_name') }}" required>
                                    @if ($errors->has('office_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('office_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="contact-field">
                                    <input class="contact-input" id="email" type="text" name="email" placeholder="E-mail" value="{{ old('email') }}" required>
                                    @if ($errors->has('email'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="contact-field">
                                    <select name="messenger" id="messenger" class="contact-input contact-select" required>
                                        <option value="">Select Instant Messenger</option>
                                        <option value="skype" @if(old('messenger') == 'skype') selected @endif>Skype</option>
                                        <option value="telegram" @if(old('messenger') == 'telegram') selected @endif>Telegram</option>
                                    </select>
                                    @if ($errors->has('messenger'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('messenger') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="contact-field">
                                    <input class="contact-input" id="messenger_name" type="text" name="messenger_name" placeholder="Messenger Name" value="{{ old('messenger_name') }}" required>
                                    @if ($errors->has('messenger_name'))
                                        <span class="errors">
                                            <strong>{{ $errors->first('messenger_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                        </div>
                        <div class="contact-field">
                            <input class="contact-input" id="location" type="text" name="location" placeholder="Location" value="{{ old('location') }}" required>
                            @if ($errors->has('location'))
                                <span class="errors">
                                    <strong>{{ $errors->first('location') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="contact-field">
                            <select class="contact-input contact-select" name="account_type" id="account_type" required>
                                <option value="">Which Best Describes you?</option>
                                <option value="Network Owner" @if(old('account_type') == 'Network Owner') selected @endif>Network Owner</option>
                                <option value="Office Owner" @if(old('account_type') == 'Office Owner') selected @endif>Office Owner</option>
                                <option value="Office Manager" @if(old('account_type') == 'Office Manager') selected @endif>Office Manager</option>
                                <option value="Office Admin" @if(old('account_type') == 'Office Admin') selected @endif>Office Admin</option>
                                <option value="Recruiter" @if(old('account_type') == 'Recruiter') selected @endif>Recruiter</option>
                            </select>
                            @if ($errors->has('account_type'))
                                <span class="errors">
                                    <strong>{{ $errors->first('account_type') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="contact-field">
                            <select class="contact-input contact-select" name="agents" id="agents" required>
                                <option value="">Number of {{ $affiliateTypeLabelPlural }}?</option>
                                <option value="1-5" @if(old('agents') == '1-5') selected @endif>1-5</option>
                                <option value="6-10" @if(old('agents') == '6-10') selected @endif>6-10</option>
                                <option value="11-20" @if(old('agents') == '11-20') selected @endif>11-20</option>
                                <option value="21-30" @if(old('agents') == '21-30') selected @endif>21-30</option>
                                <option value="31-50" @if(old('agents') == '31-50') selected @endif>31-50</option>
                                <option value="50-100" @if(old('agents') == '50-100') selected @endif>50-100</option>
                                <option value="101+" @if(old('agents') == '101+') selected @endif>101+</option>
                            </select>
                            @if ($errors->has('agents'))
                                <span class="errors">
                                    <strong>{{ $errors->first('agents') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="contact-field">
                            <fieldset class="contact-fieldset">
                                <legend>Seeking offer types</legend>
                                <p>(choose all that apply)</p>
                               <div class="contact-checks">
                                   <label class="contact-check" for="dating">
                                       <input name="offer_types[]" type="checkbox" value="Dating" id="dating"
                                              @if( is_array(old('offer_types')) && in_array("Dating", old('offer_types'))) checked @endif
                                       >
                                       <span>Dating</span>
                                   </label>
                                   <label class="contact-check" for="cams">
                                       <input name="offer_types[]" type="checkbox" value="Cams" id="cams"
                                              @if( is_array(old('offer_types')) && in_array("Cams", old('offer_types'))) checked @endif
                                       >
                                       <span>Cams</span>
                                   </label>
                                   <label class="contact-check" for="nutra">
                                       <input name="offer_types[]" type="checkbox" value="Nutra" id="nutra"
                                              @if( is_array(old('offer_types')) && in_array("Nutra", old('offer_types'))) checked @endif
                                       >
                                       <span>Nutra</span>
                                   </label>
                                   <label class="contact-check" for="mens_health">
                                       <input name="offer_types[]" type="checkbox" value="Mens Health" id="mens_health"
                                              @if( is_array(old('offer_types')) && in_array("Mens Health", old('offer_types'))) checked @endif
                                       >
                                       <span>Mens Health</span>
                                   </label>
                               </div>
                            </fieldset>
                            @if ($errors->has('offer_types'))
                                <span class="errors">
                                    <strong>{{ $errors->first('offer_types') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="contact-field">
                            <select class="contact-input contact-select" name="experience" id="experience" required>
                                <option value="">Years Experience</option>
                                <option value="0-1" @if(old('experience') == '0-1') selected @endif >0-1</option>
                                <option value="1-3" @if(old('experience') == '1-3') selected @endif >1-3</option>
                                <option value="3-5" @if(old('experience') == '3-5') selected @endif >3-5</option>
                                <option value="5+" @if(old('experience') == '5+') selected @endif >5+</option>
                            </select>
                            @if ($errors->has('experience'))
                                <span class="errors">
                                    <strong>{{ $errors->first('experience') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="contact-field">
                            <select class="contact-input contact-select" name="sales" id="sales" required>
                                <option value="">Current Sales Per Week</option>
                                <option value="0-9" @if(old('sales') == '0-9') selected @endif >0-9</option>
                                <option value="10-24" @if(old('sales') == '10-24') selected @endif >10-24</option>
                                <option value="25-49" @if(old('sales') == '25-49') selected @endif >25-49</option>
                                <option value="50-99" @if(old('sales') == '50-99') selected @endif >50-99</option>
                                <option value="100-149" @if(old('sales') == '100-149') selected @endif >100-149</option>
                                <option value="150-249" @if(old('sales') == '150-249') selected @endif >150-249</option>
                                <option value="250+" @if(old('sales') == '250+') selected @endif >250+</option>
                            </select>
                            @if ($errors->has('sales'))
                                <span class="errors">
                                    <strong>{{ $errors->first('sales') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="contact-field">
                            <textarea class="contact-input" name="additional_info" id="additional_info" rows="10" placeholder="Additional Info" required>{{ old('additional_info') }}</textarea>
                            @if ($errors->has('additional_info'))
                                <span class="errors">
                                    <strong>{{ $errors->first('additional_info') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="contact-field">
                            <input type="submit"
                                   name="button"
                                   class="contact-submit"
                                   value="Submit"
                            />
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
</main>

<footer class="contact-footer">
    &copy; {{ date('Y') }} {{ $companyName }}
</footer>

@yield('footer')

</body>
</html>
