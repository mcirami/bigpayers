
@extends('layouts.master')
@section('content')

    <div class = "right_panel member_home">
        <div class = "white_box_outer">
            <div class = "heading_holder">
                <span class = "lft value_span9">Verification</span>
            </div>
            <div style="margin-bottom: 20px;" class = "white_box value_span8">
                <div style="margin-bottom: 12px;">
                    <label class="value_span9" for="country">Country: </label>
                    <select style="border-radius: 5px;" class="input-sm" id="country">
                        <option value="NL" selected>NL - Netherlands</option>
                        <option value="GB">GB - United Kingdom</option>
                        <option value="US_V">US_V - United States (Virtual)</option>
                        <option value="LV">LV - Latvia</option>
                        <option value="ID">ID - Indonesia</option>
                        <option value="PH">PH - Philippines</option>
                        <option value="IN">IN - India</option>
                        <option value="DK">DK - Denmark</option>
                        <option value="PL">PL - Poland</option>
                        <option value="LT">LT - Lithuania</option>
                        <option value="MX">MX - Mexico</option>
                        <option value="ES">ES - Spain</option>
                        <option value="BR">BR - Brazil</option>
                        <option value="HR">HR - Croatia</option>
                        <option value="HN">HN - Honduras</option>
                        <option value="VE">VE - Venezuela</option>
                        <option value="FI">FI - Finland</option>
                    </select>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <p class="value_span9" style="font-size: 16px;">Phone number:</p>
                    <span class="value_span9" id="phone-number">-</span>
                    <a class="value_span9" href="#" id="copy-phone-btn" style="display: none; font-size: 12px; text-decoration: underline;">Copy</a>
                </div>
                <p class="value_span9" style="margin: 10px 0; font-size: 16px;">Status:
                    <span style="font-weight: 800;" class="font-weight-bold" id="status">Idle</span>
                </p>
                <div style="display:flex; gap:8px; align-items:center; margin-top:4px;">
                    <p class="value_span9" style="font-size: 16px;">Code:</p>
                    <strong class="value_span9" id="code">-</strong>
                    <a class="value_span9" href="#" id="copy-code-btn" style="display: none; font-size: 16px; text-decoration: underline;">Copy</a>
                </div>

                <a id="get-number-btn" href="#" class="btn btn-sm value_span6-1 value_span2 value_span4">Request Verification Number</a>
            </div>
            <div style="display:inline-block;" id="instruction">
                <div id="error-box" style="display:none; margin-bottom: 16px; padding: 10px; border-radius: 6px; background:#ffe5e5; color:#900;">Enter this phone number into Instagram, then wait for the verification code to appear here</div>
                <p class="value_span9">
                    Choose a country and click the button above to request a phone number.
                </p>
                <p class="value_span9">Enter this phone number into Instagram, then wait for the verification code to appear here.</p>
            </div>
        </div>
    </div>
    <!--right_panel-->
    <script>
	    let currentPollInterval = null;
	    let currentOrderId = null;

	    const getNumberBtn = document.getElementById('get-number-btn');
	    const countrySelect = document.getElementById('country');
	    const phoneNumberEl = document.getElementById('phone-number');
	    const codeEl = document.getElementById('code');
	    const statusEl = document.getElementById('status');
	    const errorBoxEl = document.getElementById('error-box');
	    const copyPhoneBtn = document.getElementById('copy-phone-btn');
	    const copyCodeBtn = document.getElementById('copy-code-btn');

	    function showError(message) {
		    errorBoxEl.textContent = message || 'Something went wrong.';
		    errorBoxEl.style.display = 'block';
	    }

	    function hideError() {
		    errorBoxEl.textContent = '';
		    errorBoxEl.style.display = 'none';
	    }

	    function resetUiForNewRequest() {
		    hideError();
		    phoneNumberEl.textContent = '-';
		    codeEl.textContent = '-';
		    statusEl.textContent = 'Requesting verification number...';
		    copyPhoneBtn.style.display = 'none';
		    copyCodeBtn.style.display = 'none';
	    }

	    function stopPolling() {
		    if (currentPollInterval) {
			    clearInterval(currentPollInterval);
			    currentPollInterval = null;
		    }
	    }

	    async function copyText(text, button) {
		    try {
			    await navigator.clipboard.writeText(text);
			    const original = button.textContent;
			    button.textContent = 'Copied!';
			    setTimeout(() => {
				    button.textContent = original;
			    }, 1200);
		    } catch (error) {
			    showError('Unable to copy to clipboard.');
		    }
	    }

	    copyPhoneBtn.addEventListener('click', function () {
		    const phone = phoneNumberEl.textContent.trim();
		    if (phone && phone !== '-') {
			    copyText(phone, this);
		    }
	    });

	    copyCodeBtn.addEventListener('click', function () {
		    const code = codeEl.textContent.trim();
		    if (code && code !== '-') {
			    copyText(code, this);
		    }
	    });

	    getNumberBtn.addEventListener('click', async function () {
		    stopPolling();
		    currentOrderId = null;

		    const country = countrySelect.value;

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
					    country: country
				    })
			    });

			    const data = await response.json();

			    if (!response.ok) {
				    throw new Error(data.message || 'Unable to create SMS order.');
			    }

			    currentOrderId = data.id;

			    phoneNumberEl.textContent = data.phone_number || '-';
			    statusEl.textContent = 'Waiting for verification code...';

			    if (data.phone_number) {
				    copyPhoneBtn.style.display = 'inline-block';
			    }

			    startPolling(data.id);
		    } catch (error) {
			    statusEl.textContent = 'Error';
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
					    copyPhoneBtn.style.display = 'inline-block';
				    }

				    if (data.status === 'received' && data.code) {
					    statusEl.textContent = 'Code received';
					    codeEl.textContent = data.code;
					    copyCodeBtn.style.display = 'inline-block';
					    stopPolling();
					    return;
				    }

				    if (data.status === 'expired') {
					    statusEl.textContent = 'Expired';
					    showError(data.message || 'This verification number has expired. Please request a new one.');
					    stopPolling();
					    return;
				    }

				    if (data.status === 'pending') {
					    statusEl.textContent = 'Waiting for verification code...';
					    return;
				    }

				    statusEl.textContent = data.status || 'Unknown';
				    if (data.message) {
					    showError(data.message);
				    }
				    stopPolling();
			    } catch (error) {
				    statusEl.textContent = 'Error';
				    showError(error.message || 'Error checking order.');
				    stopPolling();
			    }
		    }, 4000);
	    }
    </script>
@endsection
