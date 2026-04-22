@extends('layouts.dashboard-shell')

@section('page-title', $pageTitle)

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Brand Workspace</p>
                    <h2 class="bp-section-title value_span9">Company settings</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Update the company identity, theme colors, and brand assets that power the existing `value_span*` styling contract across this install.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <article class="bp-link-card">
                        <p class="bp-link-label">Theme scope</p>
                        <p class="bp-link-value">11 shared brand tokens</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Install</p>
                        <p class="bp-link-value">{{ strtoupper($subDomain) }}</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Assets</p>
                        <p class="bp-link-value">{{ $logoUrl ? 'Custom logo ready' : 'Using default logo path' }}</p>
                    </article>
                </div>
            </div>
        </section>

        <form method="post" action="/settings" enctype="multipart/form-data" class="space-y-6 lg:space-y-8">
            @csrf

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Theme Tokens</p>
                        <h3 class="bp-section-title value_span9">Color system</h3>
                    </div>
                    <p class="bp-table-meta">These values are saved back into the existing company color string, so the legacy class hooks continue to work untouched.</p>
                </div>

                <div class="mt-6 bp-settings-color-grid">
                    @foreach ($colorFields as $field)
                        <div class="bp-settings-color-card">
                            <div class="bp-settings-color-head">
                                <span class="bp-settings-color-swatch" style="background: {{ $field['pickerValue'] }}"></span>
                                <div>
                                    <p class="bp-detail-label">{{ $field['label'] }}</p>
                                    <p class="bp-form-note">{{ $field['note'] }}</p>
                                </div>
                            </div>

                            <div class="bp-settings-color-controls">
                                <input
                                    type="color"
                                    class="bp-settings-color-picker"
                                    value="{{ old($field['key'] . '_picker', $field['pickerValue']) }}"
                                    data-color-picker
                                    data-target="{{ $field['key'] }}"
                                    aria-label="{{ $field['label'] }} color picker"
                                >
                                <input
                                    type="text"
                                    id="{{ $field['key'] }}"
                                    name="{{ $field['key'] }}"
                                    class="bp-form-input bp-settings-color-input"
                                    value="{{ old($field['key'], $field['value']) }}"
                                    maxlength="6"
                                    autocomplete="off"
                                    data-color-text
                                >
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="bp-settings-main-grid">
                <section class="bp-card value_span8">
                    <div>
                        <p class="bp-section-kicker">Company Details</p>
                        <h3 class="bp-section-title value_span9">Identity and routing</h3>
                    </div>

                    <div class="mt-6 bp-settings-details-grid">
                        <div class="bp-form-field">
                            <label class="bp-form-label" for="shortHand">Name</label>
                            <input id="shortHand" name="shortHand" class="bp-form-input" type="text" value="{{ $settingsValues['shortHand'] }}" required>
                            <p class="bp-form-note">Displayed in the header, emails, notifications, and branded pages.</p>
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="email">Email</label>
                            <input id="email" name="email" class="bp-form-input" type="email" value="{{ $settingsValues['email'] }}">
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="telegram">Telegram</label>
                            <input id="telegram" name="telegram" class="bp-form-input" type="text" value="{{ $settingsValues['telegram'] }}">
                            <p class="bp-form-note">Legacy compatibility field. New signup approval messaging uses the messenger settings below.</p>
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="messenger_type">Messenger Type</label>
                            <input id="messenger_type" name="messenger_type" class="bp-form-input" type="text" value="{{ $settingsValues['messenger_type'] }}">
                            <p class="bp-form-note">Examples: Telegram, Skype, Discord, Slack.</p>
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="messenger_username">Messenger Username</label>
                            <input id="messenger_username" name="messenger_username" class="bp-form-input" type="text" value="{{ $settingsValues['messenger_username'] }}">
                            <p class="bp-form-note">Shown on signup success so new users know who to contact for approval.</p>
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="loginURL">Login URL</label>
                            <input id="loginURL" name="loginURL" class="bp-form-input" type="text" value="{{ $settingsValues['loginURL'] }}">
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="login_theme">Login Theme</label>
                            <select id="login_theme" name="login_theme" class="bp-form-input">
                                @foreach ($availableLoginThemes as $themeValue => $themeLabel)
                                    <option value="{{ $themeValue }}" {{ $settingsValues['login_theme'] === $themeValue ? 'selected' : '' }}>{{ $themeLabel }}</option>
                                @endforeach
                            </select>
                            <p class="bp-form-note">Loads the login layout from `/public/login_themes/&lt;theme&gt;/theme.css` while still using the shared brand colors and company logo.</p>
                        </div>

                        <div class="bp-form-field bp-form-field-full">
                            <label class="bp-form-label" for="landingPage">Landing Page</label>
                            <input id="landingPage" name="landingPage" class="bp-form-input" type="text" value="{{ $settingsValues['landingPage'] }}">
                            <p class="bp-form-note">Used by install-specific flows that reference the company landing destination.</p>
                        </div>

                        <div class="bp-form-field bp-form-field-full">
                            <label class="bp-form-label" for="allow_register">Allow Registration</label>
                            <select id="allow_register" name="allow_register" class="bp-form-input">
                                <option value="1" {{ $settingsValues['allow_register'] ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !$settingsValues['allow_register'] ? 'selected' : '' }}>Disabled</option>
                            </select>
                            <p class="bp-form-note">If disabled, `/signup` will redirect to `/login` for this install.</p>
                        </div>
                    </div>
                </section>

                <section class="bp-card value_span8">
                    <div>
                        <p class="bp-section-kicker">Brand Assets</p>
                        <h3 class="bp-section-title value_span9">Logo and favicon</h3>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div class="bp-settings-asset-card">
                            <p class="bp-detail-label">Current logo</p>
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Current company logo" class="bp-settings-logo-preview">
                            @else
                                <p class="bp-form-note">No custom logo uploaded yet.</p>
                            @endif
                            <input name="logo" class="bp-form-input" type="file" accept=".png,image/png">
                            <p class="bp-form-note">Upload a `.png` logo. Saving the form will replace `logo.png` for this install.</p>
                        </div>

                        <div class="bp-settings-asset-card">
                            <p class="bp-detail-label">Current favicon</p>
                            <div class="bp-settings-favicon-row">
                                @if ($faviconUrl)
                                    <img src="{{ $faviconUrl }}" alt="Current favicon" class="bp-settings-favicon-preview">
                                @else
                                    <span class="bp-settings-favicon-placeholder">ICO</span>
                                @endif
                                <p class="bp-form-note">Use a true `.ico` file to keep browser support consistent.</p>
                            </div>
                            <input name="favicon" class="bp-form-input" type="file" accept=".ico,image/x-icon,image/vnd.microsoft.icon">
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bp-button-primary value_span11 value_span2 value_span4">Save settings</button>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <script>
        (() => {
            const textInputs = Array.from(document.querySelectorAll('[data-color-text]'));
            const pickerInputs = Array.from(document.querySelectorAll('[data-color-picker]'));

            const normalize = (value) => value.replace(/[^a-fA-F0-9]/g, '').slice(0, 6).toUpperCase();
            const toPickerValue = (value) => `#${normalize(value).padEnd(6, '0')}`;

            pickerInputs.forEach((picker) => {
                picker.addEventListener('input', (event) => {
                    const target = document.getElementById(event.target.dataset.target);
                    if (!target) {
                        return;
                    }

                    target.value = event.target.value.replace('#', '').toUpperCase();
                });
            });

            textInputs.forEach((input) => {
                input.addEventListener('input', (event) => {
                    event.target.value = normalize(event.target.value);
                    const picker = document.querySelector(`[data-target="${event.target.id}"]`);
                    const swatch = event.target.closest('.bp-settings-color-card')?.querySelector('.bp-settings-color-swatch');

                    if (picker) {
                        const pickerValue = toPickerValue(event.target.value);
                        picker.value = pickerValue;
                        if (swatch) {
                            swatch.style.background = pickerValue;
                        }
                    }
                });

                const initialPicker = document.querySelector(`[data-target="${input.id}"]`);
                const initialSwatch = input.closest('.bp-settings-color-card')?.querySelector('.bp-settings-color-swatch');
                const initialValue = toPickerValue(input.value);

                if (initialPicker) {
                    initialPicker.value = initialValue;
                }

                if (initialSwatch) {
                    initialSwatch.style.background = initialValue;
                }
            });
        })();
    </script>
@endsection
