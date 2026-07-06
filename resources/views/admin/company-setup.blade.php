@extends('layouts.dashboard-shell')

@section('page-title', $pageTitle)

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Provisioning Workspace</p>
                    <h2 class="bp-section-title">Company setup</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Create a company install, import the base schema, and set the bootstrap admin account.
                    </p>
                </div>

                <a href="/admin/database-updates" class="bp-button-secondary">Database updates</a>
            </div>
        </section>

        @if($result)
            <section class="bp-card border border-green-200">
                <p class="bp-section-kicker text-green-700">Setup Complete</p>
                <h3 class="bp-section-title">{{ $result['company']->shortHand }} is ready</h3>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <article class="bp-stat-card">
                        <p class="bp-stat-label">Install</p>
                        <p class="bp-stat-value">{{ $result['database'] }}</p>
                        <p class="bp-stat-note">Tenant database created.</p>
                    </article>
                    <article class="bp-stat-card">
                        <p class="bp-stat-label">Admin</p>
                        <p class="bp-stat-value">{{ $result['adminUserName'] }}</p>
                        <p class="bp-stat-note">{{ $result['adminEmail'] }}</p>
                    </article>
                    <article class="bp-stat-card">
                        <p class="bp-stat-label">Schema</p>
                        <p class="bp-stat-value">Imported</p>
                        <p class="bp-stat-note">{{ basename($result['schemaPath']) }}</p>
                    </article>
                </div>
            </section>
        @endif

        @if($errors->has('setup'))
            <section class="bp-card border border-red-200">
                <p class="bp-section-kicker text-red-700">Setup Error</p>
                <h3 class="bp-section-title">Unable to create install</h3>
                <p class="mt-3 text-sm leading-7 text-red-700">{{ $errors->first('setup') }}</p>
            </section>
        @endif

        <form method="post" action="/admin/setup" class="space-y-6 lg:space-y-8">
            @csrf

            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Company Information</p>
                    <h3 class="bp-section-title">Identity</h3>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="bp-form-field">
                        <label class="bp-form-label" for="shortHand">Company Short Hand</label>
                        <input id="shortHand" name="shortHand" class="bp-form-input" type="text" value="{{ old('shortHand') }}" required>
                        @error('shortHand') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="subDomain">Sub Domain</label>
                        <input id="subDomain" name="subDomain" class="bp-form-input" type="text" value="{{ old('subDomain') }}" required>
                        <p class="bp-form-note">Use letters, numbers, and underscores only.</p>
                        @error('subDomain') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="companyName">Full Company Name</label>
                        <input id="companyName" name="companyName" class="bp-form-input" type="text" value="{{ old('companyName') }}" required>
                        @error('companyName') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="email">Company Email</label>
                        <input id="email" name="email" class="bp-form-input" type="email" value="{{ old('email') }}" required>
                        @error('email') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="telephone">Telephone</label>
                        <input id="telephone" name="telephone" class="bp-form-input" type="text" value="{{ old('telephone') }}" required>
                        @error('telephone') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="skype">Messenger Username</label>
                        <input id="skype" name="skype" class="bp-form-input" type="text" value="{{ old('skype') }}">
                        @error('skype') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Company Contact</p>
                    <h3 class="bp-section-title">Address</h3>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="bp-form-field md:col-span-2">
                        <label class="bp-form-label" for="address">Address</label>
                        <input id="address" name="address" class="bp-form-input" type="text" value="{{ old('address') }}" required>
                        @error('address') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="city">City</label>
                        <input id="city" name="city" class="bp-form-input" type="text" value="{{ old('city') }}" required>
                        @error('city') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="state">State</label>
                        <input id="state" name="state" class="bp-form-input" type="text" value="{{ old('state') }}" required>
                        @error('state') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="zip">Zip Code</label>
                        <input id="zip" name="zip" class="bp-form-input" type="text" value="{{ old('zip') }}" required>
                        @error('zip') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Bootstrap Admin</p>
                    <h3 class="bp-section-title">Admin account</h3>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="bp-form-field">
                        <label class="bp-form-label" for="adminEmail">Email</label>
                        <input id="adminEmail" name="adminEmail" class="bp-form-input" type="email" value="{{ old('adminEmail') }}" required>
                        @error('adminEmail') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="userName">User Name</label>
                        <input id="userName" name="userName" class="bp-form-input" type="text" value="{{ old('userName') }}" required>
                        @error('userName') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="password">Password</label>
                        <input id="password" name="password" class="bp-form-input" type="password" required>
                        @error('password') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="confirmPassword">Confirm Password</label>
                        <input id="confirmPassword" name="confirmPassword" class="bp-form-input" type="password" required>
                        @error('confirmPassword') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Optional Routing</p>
                    <h3 class="bp-section-title">Login and registration</h3>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div class="bp-form-field">
                        <label class="bp-form-label" for="login_url">Login URL</label>
                        <input id="login_url" name="login_url" class="bp-form-input" type="text" value="{{ old('login_url') }}">
                        @error('login_url') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="landing_page">Landing Page</label>
                        <input id="landing_page" name="landing_page" class="bp-form-input" type="text" value="{{ old('landing_page') }}">
                        @error('landing_page') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="login_theme">Login Theme</label>
                        <input id="login_theme" name="login_theme" class="bp-form-input" type="text" value="{{ old('login_theme') }}">
                        @error('login_theme') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="allow_register">Allow Registration</label>
                        <select id="allow_register" name="allow_register" class="bp-form-input">
                            <option value="1" @selected(old('allow_register', '1') === '1')>Enabled</option>
                            <option value="0" @selected(old('allow_register', '1') === '0')>Disabled</option>
                        </select>
                        @error('allow_register') <p class="bp-form-note text-red-700">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="bp-button-primary">Create install</button>
            </div>
        </form>
    </div>
@endsection
