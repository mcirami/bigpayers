@php
    $countries = [
        'NL' => 'Netherlands',
        'GB' => 'United Kingdom',
        'US_V' => 'United States (Virtual)',
        'LV' => 'Latvia',
        'ID' => 'Indonesia',
        'PH' => 'Philippines',
        'IN' => 'India',
        'DK' => 'Denmark',
        'PL' => 'Poland',
        'LT' => 'Lithuania',
        'MX' => 'Mexico',
        'ES' => 'Spain',
        'BR' => 'Brazil',
        'HR' => 'Croatia',
        'HN' => 'Honduras',
        'VE' => 'Venezuela',
        'FI' => 'Finland',
    ];
@endphp

@extends('layouts.dashboard-shell')

@section('page-title', 'Verification')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Verification Workspace</p>
                    <h2 class="bp-section-title value_span9">SMS verification</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Request a verification number, paste it into Instagram, and wait here for the code to arrive. This keeps the existing SMS ordering flow intact while moving the page into the redesigned shell.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <article class="bp-link-card">
                        <p class="bp-link-label">Provider</p>
                        <p class="bp-link-value">SMSPool</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Service</p>
                        <p class="bp-link-value">Instagram / Threads</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Polling</p>
                        <p class="bp-link-value">Every 4 seconds</p>
                    </article>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Request Number</p>
                    <h3 class="bp-section-title value_span9">Start a verification session</h3>
                </div>

                <div class="mt-6 space-y-6">
                    <div class="bp-form-field">
                        <label class="bp-form-label" for="country">Country</label>
                        <select id="country" class="bp-form-input">
                            @foreach ($countries as $code => $label)
                                <option value="{{ $code }}" {{ $code === 'NL' ? 'selected' : '' }}>{{ $code }} - {{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="bp-form-note">Choose the country pool to request a fresh verification number from.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button id="get-number-btn" type="button" class="bp-button-primary">Request verification number</button>
                    </div>

                    <div id="error-box" class="bp-toast bp-toast-danger" style="display:none;">
                        <p class="bp-toast-title" id="error-text">Something went wrong.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <article class="bp-link-card">
                            <p class="bp-link-label">Phone number</p>
                            <p class="bp-link-value" id="phone-number">-</p>
                            <button type="button" id="copy-phone-btn" class="bp-action-link" style="display:none;">Copy number</button>
                        </article>

                        <article class="bp-link-card">
                            <p class="bp-link-label">Status</p>
                            <span class="bp-status-pill" id="status-pill">Idle</span>
                            <p class="bp-form-note" id="status-copy">Request a number to begin polling for the verification code.</p>
                        </article>

                        <article class="bp-link-card">
                            <p class="bp-link-label">Code</p>
                            <p class="bp-link-value" id="code">-</p>
                            <button type="button" id="copy-code-btn" class="bp-action-link" style="display:none;">Copy code</button>
                        </article>
                    </div>
                </div>
            </section>

            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Instructions</p>
                    <h3 class="bp-section-title value_span9">Recommended flow</h3>
                </div>

                <div class="mt-6 bp-mini-list">
                    <div class="bp-link-card">
                        <p class="bp-link-label">1. Request</p>
                        <p class="bp-link-value">Choose a country and request a number from the pool.</p>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">2. Enter</p>
                        <p class="bp-link-value">Paste the provided phone number into Instagram or Threads as the verification number.</p>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">3. Wait</p>
                        <p class="bp-link-value">Leave this page open while the code is polled in the background and surfaced here automatically.</p>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">4. Retry</p>
                        <p class="bp-link-value">If the number expires, request a new one and repeat the process.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        let currentPollInterval = null;
        let currentOrderId = null;

        const getNumberBtn = document.getElementById('get-number-btn');
        const countrySelect = document.getElementById('country');
        const phoneNumberEl = document.getElementById('phone-number');
        const codeEl = document.getElementById('code');
        const statusPillEl = document.getElementById('status-pill');
        const statusCopyEl = document.getElementById('status-copy');
        const errorBoxEl = document.getElementById('error-box');
        const errorTextEl = document.getElementById('error-text');
        const copyPhoneBtn = document.getElementById('copy-phone-btn');
        const copyCodeBtn = document.getElementById('copy-code-btn');

        function setStatus(label, note, isActive = false) {
            statusPillEl.textContent = label;
            statusPillEl.classList.toggle('bp-status-pill-active', isActive);
            statusCopyEl.textContent = note;
        }

        function showError(message) {
            errorTextEl.textContent = message || 'Something went wrong.';
            errorBoxEl.style.display = 'block';
        }

        function hideError() {
            errorTextEl.textContent = '';
            errorBoxEl.style.display = 'none';
        }

        function resetUiForNewRequest() {
            hideError();
            phoneNumberEl.textContent = '-';
            codeEl.textContent = '-';
            copyPhoneBtn.style.display = 'none';
            copyCodeBtn.style.display = 'none';
            setStatus('Requesting', 'Requesting a fresh verification number from the selected country pool.', false);
        }

        function stopPolling() {
            if (currentPollInterval) {
                clearInterval(currentPollInterval);
                currentPollInterval = null;
            }
        }

        async function copyText(text, button) {
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
                }, 1200);
            } catch (error) {
                showError('Unable to copy to clipboard.');
            }
        }

        copyPhoneBtn.addEventListener('click', () => {
            const phone = phoneNumberEl.textContent.trim();

            if (phone && phone !== '-') {
                copyText(phone, copyPhoneBtn);
            }
        });

        copyCodeBtn.addEventListener('click', () => {
            const code = codeEl.textContent.trim();

            if (code && code !== '-') {
                copyText(code, copyCodeBtn);
            }
        });

        getNumberBtn.addEventListener('click', async () => {
            stopPolling();
            currentOrderId = null;

            getNumberBtn.disabled = true;
            resetUiForNewRequest();

            try {
                const response = await fetch('/api/sms-orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        service: 'Instagram / Threads',
                        country: countrySelect.value,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to create SMS order.');
                }

                currentOrderId = data.id;
                phoneNumberEl.textContent = data.phone_number || '-';

                if (data.phone_number) {
                    copyPhoneBtn.style.display = 'inline-flex';
                }

                setStatus('Pending', 'Number ready. Enter it into Instagram and wait here for the code.', true);
                startPolling(data.id);
            } catch (error) {
                setStatus('Error', 'The verification request did not complete successfully.', false);
                showError(error.message || 'Unable to create SMS order.');
            } finally {
                getNumberBtn.disabled = false;
            }
        });

        function startPolling(orderId) {
            currentPollInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/api/sms-orders/${orderId}`, {
                        headers: {
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Error checking order.');
                    }

                    if (data.phone_number) {
                        phoneNumberEl.textContent = data.phone_number;
                        copyPhoneBtn.style.display = 'inline-flex';
                    }

                    if (data.status === 'received' && data.code) {
                        codeEl.textContent = data.code;
                        copyCodeBtn.style.display = 'inline-flex';
                        setStatus('Received', 'Verification code received and ready to copy.', true);
                        stopPolling();
                        return;
                    }

                    if (data.status === 'expired') {
                        setStatus('Expired', 'This number expired before a code arrived. Request a new one to continue.', false);
                        showError(data.message || 'This verification number has expired. Please request a new one.');
                        stopPolling();
                        return;
                    }

                    if (data.status === 'pending') {
                        setStatus('Pending', 'Still waiting for the verification code to arrive.', true);
                        return;
                    }

                    setStatus(data.status || 'Unknown', 'The verification session returned an unexpected state.', false);
                    if (data.message) {
                        showError(data.message);
                    }
                    stopPolling();
                } catch (error) {
                    setStatus('Error', 'The polling request failed before a code was returned.', false);
                    showError(error.message || 'Error checking order.');
                    stopPolling();
                }
            }, 4000);
        }
    </script>
@endsection
