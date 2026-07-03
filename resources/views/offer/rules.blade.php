@extends('layouts.dashboard-shell')

@section('page-title', 'Offer Rules')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">Rules for {{ $offer->offer_name ?: 'Offer #' . $offer->idoffer }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Manage geo, device, and cap rules for this offer from one focused workspace.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/offer/view/{{ $offer->idoffer }}" class="bp-button-secondary">View offer</a>
                    <a href="/offer/edit/{{ $offer->idoffer }}" class="bp-button-primary">Edit offer</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Offer ID</p>
                <p class="bp-stat-value">{{ $offer->idoffer }}</p>
                <p class="bp-stat-note">Primary offer identifier for these rule definitions.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Status</p>
                <p class="bp-stat-value">{{ (int) $offer->status === 1 ? 'Active' : 'Disabled' }}</p>
                <p class="bp-stat-note">Offer availability still applies before any rule redirect logic runs.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Rule Types</p>
                <p class="bp-stat-value">Geo + Device</p>
                <p class="bp-stat-note">Use these rules to allow, deny, redirect, or cap traffic segments.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Cap Status</p>
                <p class="bp-stat-value">{{ $activeCap ? 'Enabled' : 'Optional' }}</p>
                <p class="bp-stat-note">{{ $activeCap ? 'Device caps are currently active on at least one rule.' : 'Caps can be enabled from the device rule editor.' }}</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Rule Editor</p>
                    <h3 class="bp-section-title value_span9">Current rules</h3>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" class="bp-button-secondary" data-open-modal="geoModal">
                        Add geo rule
                    </button>
                    <button type="button" class="bp-button-secondary" data-open-modal="deviceModal">
                        Add device rule
                    </button>
                    <a class="bp-button-secondary" href="/offer/rules/{{ $offer->idoffer }}/none-unique/create">Add none-unique rule</a>
                </div>
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table id="rules" class="bp-rules-table" data-sortable-table data-sort-default="0:asc">
                    <thead>
                    <tr>
                        <th class="value_span9">Rule</th>
                        <th class="value_span9">Type</th>
                        <th class="value_span9">Mode</th>
                        <th class="value_span9">Redirect</th>
                        <th class="value_span9">Status</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {!! $rulesTableHtml !!}
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="bp-rules-modal-shell" id="geoModal" tabindex="-1" role="dialog" aria-labelledby="geoModalLabel">
        <div class="bp-rules-modal" role="document">
            <div class="bp-rules-modal-content">
                <div class="bp-rules-modal-header">
                    <button type="button" class="bp-rules-modal-close" data-close-modal="geoModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="bp-rules-modal-title" id="geoRuleTitle">New Geo Rule</h4>
                </div>
                <div class="bp-rules-modal-body">
                    <div class="bp-rules-modal-grid">
                        <div class="bp-rules-panel">
                            <div class="bp-rules-panel-head">
                                <label class="bp-rules-panel-label">Country List</label>
                                <input type="text" id="searchCountryList" class="bp-form-input bp-rules-search" placeholder="Search countries...">
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="countryList" class="bp-rules-modal-table">
                                    <colgroup>
                                        <col>
                                        <col class="bp-rules-col-action">
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th>Country</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody id="countryListBody">
                                    {!! $countryRowsHtml !!}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bp-rules-panel">
                            <div class="bp-rules-panel-head">
                                <label class="bp-rules-panel-label">Items</label>
                                <p class="bp-rules-panel-note">Add selected countries to this rule and optionally apply caps.</p>
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="toAdd" class="bp-rules-modal-table">
                                    <colgroup>
                                        <col>
                                        <col class="bp-rules-col-action">
                                        <col class="bp-rules-col-caps">
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th>Country</th>
                                        <th>Action</th>
                                        <th>Caps</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bp-rules-settings-stack mt-4">
                        <div class="bp-rules-settings-row bp-rules-settings-row--two">
                            <label class="bp-form-field">
                                <span class="bp-form-label">Load Predefined Rule</span>
                                <select id="geoPredefinedRuleSelect" class="bp-form-input">
                                    <option value="">Select a saved rule...</option>
                                    @foreach ($predefinedGeoRules as $predefinedRule)
                                        <option value="{{ $predefinedRule['id'] }}">{{ $predefinedRule['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Rule Name</span>
                                <input type="text" class="bp-form-input" id="geoRuleName">
                            </label>
                        </div>

                        <div class="bp-rules-settings-row bp-rules-settings-row--two">
                            <label class="bp-choice-pill">
                                <input checked id="geoIsActive" type="checkbox">
                                <span>Rule is active</span>
                            </label>

                            <label class="bp-choice-pill">
                                <input id="geoIsAllowed" type="checkbox">
                                <span>Items in this list will be denied</span>
                            </label>
                        </div>

                        <div class="bp-rules-settings-row">
                            <label class="bp-form-field">
                                <span class="bp-form-label">Redirect Offer</span>
                                {!! $geoRedirectOfferSelect !!}
                            </label>
                        </div>

                        <div class="bp-rules-settings-row bp-rules-settings-row--two">
                            <label class="bp-choice-pill">
                                <input id="geoShouldSavePredefined" type="checkbox">
                                <span>Create predefined rule</span>
                            </label>

                            <label class="bp-form-field bp-hidden" id="geoPredefinedRuleNameField">
                                <span class="bp-form-label">Predefined Rule Name</span>
                                <input type="text" class="bp-form-input" id="geoPredefinedRuleName" placeholder="Save this rule for reuse..." disabled>
                            </label>
                        </div>

                        <input type="hidden" id="offerID" value="{{ $offer->idoffer }}">
                        <input type="hidden" id="geoRuleID" value="">
                    </div>
                </div>
                <div class="bp-rules-modal-footer">
                    <button id="geoCancelButton" type="button" class="bp-button-secondary" data-close-modal="geoModal">Cancel</button>
                    <button id="geoCreateButton" type="button" class="bp-button-primary">Create</button>
                    <button id="geoUpdateButton" type="button" class="bp-button-primary" style="display:none;">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="bp-rules-modal-shell" id="deviceModal" tabindex="-1" role="dialog" aria-labelledby="deviceModalLabel">
        <div class="bp-rules-modal" role="document">
            <div class="bp-rules-modal-content">
                <div class="bp-rules-modal-header">
                    <button type="button" class="bp-rules-modal-close" data-close-modal="deviceModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="bp-rules-modal-title" id="deviceRuleTitle">New Device Rule</h4>
                </div>
                <div class="bp-rules-modal-body">
                    <div class="bp-rules-modal-grid">
                        <div class="bp-rules-panel">
                            <div class="bp-rules-panel-head">
                                <label class="bp-rules-panel-label">Device List</label>
                                <p class="bp-rules-panel-note">Move devices into the rule to allow or deny them.</p>
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="deviceList" class="bp-rules-modal-table">
                                    <colgroup>
                                        <col>
                                        <col class="bp-rules-col-action">
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody id="deviceListBody">
                                    <tr id="desktop">
                                        <td>Desktop</td>
                                        <td><button type="button" id="_desktop" class="bp-rule-action-button" onclick="addDevice('desktop');"><span class="bp-rule-action-button-text">Add</span></button></td>
                                    </tr>
                                    <tr id="mobile">
                                        <td>Mobile</td>
                                        <td><button type="button" id="_mobile" class="bp-rule-action-button" onclick="addDevice('mobile');"><span class="bp-rule-action-button-text">Add</span></button></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bp-rules-panel">
                            <div class="bp-rules-panel-head">
                                <label class="bp-rules-panel-label">Items</label>
                                <p class="bp-rules-panel-note">Selected devices will be included in this rule configuration.</p>
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="deviceToAdd" class="bp-rules-modal-table">
                                    <colgroup>
                                        <col>
                                        <col class="bp-rules-col-action">
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bp-rules-settings-stack mt-4">
                        <div class="bp-rules-settings-row bp-rules-settings-row--two">
                            <label class="bp-form-field">
                                <span class="bp-form-label">Load Predefined Rule</span>
                                <select id="devicePredefinedRuleSelect" class="bp-form-input">
                                    <option value="">Select a saved rule...</option>
                                    @foreach ($predefinedDeviceRules as $predefinedRule)
                                        <option value="{{ $predefinedRule['id'] }}">{{ $predefinedRule['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Rule Name</span>
                                <input type="text" class="bp-form-input" id="deviceRuleName">
                            </label>
                        </div>

                        <div class="bp-rules-settings-row bp-rules-settings-row--two">
                            <label class="bp-choice-pill">
                                <input checked id="deviceIsActive" type="checkbox">
                                <span>Rule is active</span>
                            </label>

                            <label class="bp-choice-pill">
                                <input id="deviceIsAllowed" type="checkbox">
                                <span>Items in this list will be denied</span>
                            </label>
                        </div>

                        <div class="bp-rules-settings-row">
                            <label class="bp-form-field">
                                <span class="bp-form-label">Redirect Offer</span>
                                {!! $deviceRedirectOfferSelect !!}
                            </label>
                        </div>

                        <div class="bp-rules-settings-row bp-rules-settings-row--two">
                            <label class="bp-form-field">
                                <span class="bp-form-label">Cap</span>
                                <label class="bp-choice-pill">
                                    <input {{ $activeCap ? 'checked' : '' }} id="capIsActive" type="checkbox">
                                    <span>Enable</span>
                                </label>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Max Conv</span>
                                <input type="number" class="bp-form-input" id="deviceCap" value="{{ $capAmount }}">
                            </label>
                        </div>

                        <div class="bp-rules-settings-row bp-rules-settings-row--two">
                            <label class="bp-choice-pill">
                                <input id="deviceShouldSavePredefined" type="checkbox">
                                <span>Create predefined rule</span>
                            </label>

                            <label class="bp-form-field bp-hidden" id="devicePredefinedRuleNameField">
                                <span class="bp-form-label">Predefined Rule Name</span>
                                <input type="text" class="bp-form-input" id="devicePredefinedRuleName" placeholder="Save this rule for reuse..." disabled>
                            </label>
                        </div>

                        <input type="hidden" id="deviceRuleID" value="">
                    </div>
                </div>
                <div class="bp-rules-modal-footer">
                    <button id="deviceCancelButton" type="button" class="bp-button-secondary" data-close-modal="deviceModal">Cancel</button>
                    <button id="deviceCreateButton" type="button" class="bp-button-primary">Create</button>
                    <button id="deviceUpdateButton" type="button" class="bp-button-primary" style="display:none;">Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        const countryMap = @json($countryMap);
        const geoRules = @json($geoRules);
        const deviceRules = @json($deviceRules);
        const predefinedGeoRules = @json($predefinedGeoRules);
        const predefinedDeviceRules = @json($predefinedDeviceRules);
        const redirectOfferMap = @json($redirectOfferMap ?? []);
        const csrfToken = @json(csrf_token());
        window.csrfToken = csrfToken;
        let geoSubmitting = false;
        let deviceSubmitting = false;

        const get = (id) => document.getElementById(id);
        const valueOf = (id) => get(id)?.value || '';
        const setValue = (id, value) => {
            const element = get(id);
            if (element) {
                element.value = value ?? '';
            }
        };
        const isChecked = (id) => Boolean(get(id)?.checked);
        const setChecked = (id, value) => {
            const element = get(id);
            if (element) {
                element.checked = Boolean(value);
            }
        };
        const setDisabled = (id, value) => {
            const element = get(id);
            if (element) {
                element.disabled = Boolean(value);
            }
        };
        const setText = (id, value) => {
            const element = get(id);
            if (element) {
                element.textContent = value;
            }
        };
        const showElement = (id, shouldShow) => {
            const element = get(id);
            if (element) {
                element.style.display = shouldShow ? '' : 'none';
            }
        };

        document.querySelectorAll('[data-open-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                showRulesModal(button.dataset.openModal);
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                hideRulesModal(button.dataset.closeModal);
            });
        });

        document.querySelectorAll('.bp-rules-modal-shell').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    hideRulesModal(modal.id);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('.bp-rules-modal-shell.is-open').forEach((modal) => {
                hideRulesModal(modal.id);
            });
        });

        function showRulesModal(modalID) {
            const modal = document.getElementById(modalID);

            if (!modal) {
                return;
            }

            modal.classList.add('is-open', 'show');
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            document.body.classList.add('bp-modal-open');

            const modalBody = modal.querySelector('.bp-rules-modal-body');
            if (modalBody) {
                modalBody.style.maxHeight = '100%';
            }
        }

        function hideRulesModal(modalID) {
            const modal = document.getElementById(modalID);

            if (!modal) {
                return;
            }

            modal.classList.remove('is-open', 'show');
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');

            if (!document.querySelector('.bp-rules-modal-shell.is-open')) {
                document.body.classList.remove('bp-modal-open');
            }
        }

        get('searchCountryList')?.addEventListener('input', (event) => {
            searchCountryList(event.target.value);
        });

        function searchCountryList(searchWords) {
            const filter = searchWords.toUpperCase();
            const rows = get("countryListBody").getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                const td = rows[i].getElementsByTagName("td")[0];
                if (td) {
                    rows[i].style.display = td.innerHTML.toUpperCase().indexOf(filter) > -1 ?"" :"none";
                }
            }
        }

        function editRule(ruleID, ruleType) {
            switch (ruleType) {
                case"geo":
                    resetGeoModal();
                    setValue("geoRuleID", ruleID);
                    loadGeoRule(ruleID);
                    showRulesModal('geoModal');
                    break;
                case"device":
                    resetDeviceModal();
                    setValue("deviceRuleID", ruleID);
                    loadDeviceRule(ruleID);
                    showRulesModal('deviceModal');
                    break;
            }
        }

        function togglePredefinedNameField(toggleID, inputID, fieldID) {
            const shouldSave = isChecked(toggleID);
            setDisabled(inputID, !shouldSave);
            get(fieldID)?.classList.toggle("bp-hidden", !shouldSave);

            if (!shouldSave) {
                setValue(inputID,"");
            }
        }

        function clearGeoSelections() {
            const rows = Array.from(document.querySelectorAll('#toAdd > tbody > tr'));
            const countryListBody = get("countryListBody");

            rows.forEach((row) => {
                row.querySelector('td.caps')?.remove();
                countryListBody.append(row);
                const button = get("_" + row.id);
                button?.setAttribute("onclick","addCountry('" + row.id +"');");
                setRuleActionState(button,"add");
            });

            sortCountries();
        }

        function clearDeviceSelections() {
            const rows = Array.from(document.querySelectorAll('#deviceToAdd > tbody > tr'));
            const deviceListBody = get("deviceListBody");

            rows.forEach((row) => {
                deviceListBody.append(row);
                const button = get("_" + row.id);
                button?.setAttribute("onclick","addDevice('" + row.id +"')");
                setRuleActionState(button,"add");
            });
        }

        function fillGeoRuleForm(rule) {
            setValue("geoRuleName", rule.name || rule.rule_name ||"");
            syncSelectValue(get("geoRedirectOffer"), rule.redirectOffer || rule.redirect_offer, redirectOfferMap);
            setChecked("geoIsAllowed", Number(rule.deny) === 1 || rule.deny === true);
            setChecked("geoIsActive", Number(rule.is_active) === 1 || rule.is_active === true);
            clearGeoSelections();

            (rule.countries || rule.items || []).forEach((country) => {
                addCountry(
                    country.country_code || country.code || country.countryCode,
                    country.cap_status ?? country.capStatus ?? 0,
                    country.cap ?? 0,
                    false
                );
            });

            sortTable(get('toAdd'), 'asc');
        }

        function fillDeviceRuleForm(rule) {
            setValue("deviceRuleName", rule.name || rule.rule_name ||"");
            syncSelectValue(get("deviceRedirectOffer"), rule.redirectOffer || rule.redirect_offer, redirectOfferMap);
            setChecked("deviceIsAllowed", Number(rule.deny) === 1 || rule.deny === true);
            setChecked("deviceIsActive", Number(rule.is_active) === 1 || rule.is_active === true);
            setChecked("capIsActive", Number(rule.capStatus ?? rule.cap_status) === 1 || rule.capStatus === true || rule.cap_status === true);
            setValue("deviceCap", rule.capAmount ?? rule.cap_amount ?? 0);
            clearDeviceSelections();

            (rule.devices || rule.items || []).forEach((deviceName) => {
                addDevice(String(deviceName).toLowerCase());
            });
        }

        function getGeoItems() {
            return Array.from(document.querySelectorAll('#toAdd > tbody > tr')).map((row) => {
                return {
                    country_code: normalizeCountryCode(row.id),
                    country_name: row.children[0].innerText,
                    cap_status: row.querySelector('.cap_active')?.checked ? 1 : 0,
                    cap: row.querySelector('.cap_amount')?.value || 0
                };
            });
        }

        function getDeviceItems() {
            return Array.from(document.querySelectorAll('#deviceToAdd > tbody > tr')).map((row) => {
                return row.id;
            });
        }

        function buildGeoPredefinedRulePayload() {
            return {
                _token: csrfToken,
                type: 'geo',
                name: valueOf("geoPredefinedRuleName").trim(),
                rule_name: valueOf("geoRuleName").trim(),
                redirect_offer: valueOf("geoRedirectOffer"),
                deny: isChecked("geoIsAllowed") ? 1 : 0,
                is_active: isChecked("geoIsActive") ? 1 : 0,
                cap_amount: 0,
                cap_status: 0,
                items: getGeoItems()
            };
        }

        function buildDevicePredefinedRulePayload() {
            return {
                _token: csrfToken,
                type: 'device',
                name: valueOf("devicePredefinedRuleName").trim(),
                rule_name: valueOf("deviceRuleName").trim(),
                redirect_offer: valueOf("deviceRedirectOffer"),
                deny: isChecked("deviceIsAllowed") ? 1 : 0,
                is_active: isChecked("deviceIsActive") ? 1 : 0,
                cap_amount: valueOf("deviceCap") || 0,
                cap_status: isChecked("capIsActive") ? 1 : 0,
                items: getDeviceItems()
            };
        }

        async function httpRequest(url, options) {
            const response = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    ...(options.headers || {})
                },
                ...options
            });

            const text = await response.text();
            let body = null;

            if (text) {
                try {
                    body = JSON.parse(text);
                } catch (error) {
                    body = { message: text };
                }
            }

            if (!response.ok) {
                throw new Error(body?.message || text || 'Request failed.');
            }

            return body;
        }

        function postForm(url, data) {
            const formData = new URLSearchParams();

            Object.entries(data).forEach(([key, value]) => {
                formData.append(key, value);
            });

            return httpRequest(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: formData.toString()
            });
        }

        function persistPredefinedRule(payload) {
            return httpRequest("/offer/rules/predefined", {
                method:"POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
        }

        function validatePredefinedRuleRequest(toggleID, inputID) {
            if (!isChecked(toggleID)) {
                return true;
            }

            if (valueOf(inputID).trim() !=="") {
                return true;
            }

            alert("Please enter a predefined rule name.");
            get(inputID)?.focus();
            return false;
        }

        function setGeoSubmitting(isSubmitting) {
            geoSubmitting = isSubmitting;
            setDisabled("geoCreateButton", isSubmitting);
            setDisabled("geoUpdateButton", isSubmitting);
        }

        function setDeviceSubmitting(isSubmitting) {
            deviceSubmitting = isSubmitting;
            setDisabled("deviceCreateButton", isSubmitting);
            setDisabled("deviceUpdateButton", isSubmitting);
        }

        get("geoCreateButton")?.addEventListener('click', async () => {
            if (geoSubmitting) {
                return;
            }

            if (!validatePredefinedRuleRequest("geoShouldSavePredefined","geoPredefinedRuleName")) {
                return;
            }

            setGeoSubmitting(true);

            try {
                await postForm("/offer/rules/geo", { data: parseCountries("toAdd") });

                if (isChecked("geoShouldSavePredefined")) {
                    try {
                        await persistPredefinedRule(buildGeoPredefinedRulePayload());
                    } catch (error) {
                        alert(error.message ||"Rule saved, but predefined rule could not be created.");
                    }
                }

                hideRulesModal("geoModal");
                location.reload();
            } catch (error) {
                alert(error.message ||"Request failed.");
            } finally {
                setGeoSubmitting(false);
            }
        });

        get("deviceCreateButton")?.addEventListener('click', async () => {
            if (deviceSubmitting) {
                return;
            }

            if (!validatePredefinedRuleRequest("deviceShouldSavePredefined","devicePredefinedRuleName")) {
                return;
            }

            setDeviceSubmitting(true);

            try {
                await postForm("/offer/rules/device", { data: parseDevices("deviceToAdd") });

                if (isChecked("deviceShouldSavePredefined")) {
                    try {
                        await persistPredefinedRule(buildDevicePredefinedRulePayload());
                    } catch (error) {
                        alert(error.message ||"Rule saved, but predefined rule could not be created.");
                    }
                }

                hideRulesModal("deviceModal");
                location.reload();
            } catch (error) {
                alert(error.message ||"Request failed.");
            } finally {
                setDeviceSubmitting(false);
            }
        });

        function loadGeoRule(ruleID) {
            const rule = geoRules[String(ruleID)] || geoRules[ruleID];

            if (!rule) {
                return;
            }

            setText("geoRuleTitle","Edit Rule");
            setValue("geoRuleID", ruleID);
            setValue("geoRuleName", rule.name ||"");
            fillGeoRuleForm(rule);
            showElement("geoCreateButton", false);
            showElement("geoUpdateButton", true);
        }

        function loadDeviceRule(ruleID) {
            const rule = deviceRules[String(ruleID)] || deviceRules[ruleID];

            if (!rule) {
                return;
            }

            setText("deviceRuleTitle","Edit Rule");
            setValue("deviceRuleID", ruleID);
            fillDeviceRuleForm(rule);
            showElement("deviceCreateButton", false);
            showElement("deviceUpdateButton", true);
        }

        get("geoUpdateButton")?.addEventListener('click', async () => {
            if (geoSubmitting) {
                return;
            }

            if (!validatePredefinedRuleRequest("geoShouldSavePredefined","geoPredefinedRuleName")) {
                return;
            }

            setGeoSubmitting(true);

            const ruleData = {
                name: valueOf("geoRuleName"),
                ruleID: valueOf("geoRuleID"),
                redirectOffer: valueOf("geoRedirectOffer"),
                deny: isChecked("geoIsAllowed"),
                is_active: isChecked("geoIsActive"),
            };

            try {
                await postForm("/offer/rules/geo/" + ruleData.ruleID, {
                    data: parseCountries("toAdd", true),
                    ruleData: JSON.stringify(ruleData),
                    ruleID: ruleData.ruleID,
                });

                if (isChecked("geoShouldSavePredefined")) {
                    try {
                        await persistPredefinedRule(buildGeoPredefinedRulePayload());
                    } catch (error) {
                        alert(error.message ||"Rule updated, but predefined rule could not be created.");
                    }
                }

                hideRulesModal("geoModal");
                location.reload();
            } catch (error) {
                alert(error.message ||"Request failed.");
            } finally {
                setGeoSubmitting(false);
            }
        });

        get("deviceUpdateButton")?.addEventListener('click', async () => {
            if (deviceSubmitting) {
                return;
            }

            if (!validatePredefinedRuleRequest("deviceShouldSavePredefined","devicePredefinedRuleName")) {
                return;
            }

            setDeviceSubmitting(true);

            const ruleData = {
                name: valueOf("deviceRuleName"),
                ruleID: valueOf("deviceRuleID"),
                redirectOffer: valueOf("deviceRedirectOffer"),
                deny: isChecked("deviceIsAllowed"),
                is_active: isChecked("deviceIsActive"),
                capAmount: valueOf("deviceCap"),
                capStatus: isChecked("capIsActive"),
            };

            try {
                await postForm("/offer/rules/device/" + ruleData.ruleID, {
                    data: parseDevices("deviceToAdd", true),
                    ruleData: JSON.stringify(ruleData),
                    ruleID: ruleData.ruleID,
                });

                if (isChecked("deviceShouldSavePredefined")) {
                    try {
                        await persistPredefinedRule(buildDevicePredefinedRulePayload());
                    } catch (error) {
                        alert(error.message ||"Rule updated, but predefined rule could not be created.");
                    }
                }

                hideRulesModal("deviceModal");
                location.reload();
            } catch (error) {
                alert(error.message ||"Request failed.");
            } finally {
                setDeviceSubmitting(false);
            }
        });

        function resetDeviceModal() {
            setValue("deviceRuleName","");
            setValue("deviceRuleID","");
            setValue("deviceRedirectOffer","");
            setValue("devicePredefinedRuleSelect","");
            setChecked("deviceShouldSavePredefined", false);
            setValue("devicePredefinedRuleName","");
            setDisabled("devicePredefinedRuleName", true);
            get("devicePredefinedRuleNameField")?.classList.add("bp-hidden");
            setText("deviceRuleTitle","New Device Rule");
            setChecked("deviceIsAllowed", false);
            setChecked("deviceIsActive", true);
            setChecked("capIsActive", {{ $activeCap ? 'true' : 'false' }});
            setValue("deviceCap", {{ $capAmount ?: 0 }});
            showElement("deviceCreateButton", true);
            showElement("deviceUpdateButton", false);
            setDeviceSubmitting(false);
            clearDeviceSelections();
        }

        function resetGeoModal() {
            setValue("geoRuleName","");
            setValue("geoRuleID","");
            setValue("geoRedirectOffer","");
            setValue("geoPredefinedRuleSelect","");
            setChecked("geoShouldSavePredefined", false);
            setValue("geoPredefinedRuleName","");
            setDisabled("geoPredefinedRuleName", true);
            get("geoPredefinedRuleNameField")?.classList.add("bp-hidden");
            setText("geoRuleTitle","New Geo Rule");
            setChecked("geoIsAllowed", false);
            setChecked("geoIsActive", true);
            setValue("searchCountryList","");
            showElement("geoCreateButton", true);
            showElement("geoUpdateButton", false);
            setGeoSubmitting(false);
            clearGeoSelections();
        }

        get("geoCancelButton")?.addEventListener('click', resetGeoModal);
        document.querySelector("#geoModal .bp-rules-modal-close")?.addEventListener('click', resetGeoModal);

        get("deviceCancelButton")?.addEventListener('click', resetDeviceModal);
        document.querySelector("#deviceModal .bp-rules-modal-close")?.addEventListener('click', resetDeviceModal);

        get("geoShouldSavePredefined")?.addEventListener('change', () => {
            togglePredefinedNameField("geoShouldSavePredefined","geoPredefinedRuleName","geoPredefinedRuleNameField");
        });

        get("deviceShouldSavePredefined")?.addEventListener('change', () => {
            togglePredefinedNameField("deviceShouldSavePredefined","devicePredefinedRuleName","devicePredefinedRuleNameField");
        });

        get("geoPredefinedRuleSelect")?.addEventListener('change', (event) => {
            const ruleID = event.target.value;
            const rule = predefinedGeoRules.find((item) => String(item.id) === String(ruleID));

            if (!rule) {
                return;
            }

            fillGeoRuleForm(rule);
        });

        get("devicePredefinedRuleSelect")?.addEventListener('change', (event) => {
            const ruleID = event.target.value;
            const rule = predefinedDeviceRules.find((item) => String(item.id) === String(ruleID));

            if (!rule) {
                return;
            }

            fillDeviceRuleForm(rule);
        });

        function addDevice(deviceName) {
            const selectedDeviceTR = get(deviceName);

            if (!selectedDeviceTR) {
                return;
            }

            selectedDeviceTR.remove();
            document.querySelector("#deviceToAdd tbody")?.append(selectedDeviceTR);
            const button = get("_" + deviceName);
            button?.setAttribute("onclick","removeDevice('" + deviceName +"');");
            setRuleActionState(button,"remove");
        }

        function removeDevice(deviceName) {
            const selectedDevice = get(deviceName);

            if (!selectedDevice) {
                return;
            }

            selectedDevice.remove();
            get("deviceListBody")?.append(selectedDevice);
            const button = get("_" + deviceName);
            button?.setAttribute("onclick","addDevice('" + deviceName +"')");
            setRuleActionState(button,"add");
        }

        function parseDevices(tableName, onlyCountries = false) {
            const rows = document.querySelectorAll('#' + tableName + ' > tbody > tr');
            const offerID = valueOf("offerID");
            const redirectOffer = valueOf("deviceRedirectOffer");
            const ruleName = valueOf("deviceRuleName");
            const notAllowed = isChecked("deviceIsAllowed");
            const capAmount = valueOf("deviceCap");
            const capStatus = isChecked("capIsActive");

            let parsed = [];
            if (!onlyCountries) {
                parsed = [offerID, ruleName, redirectOffer, notAllowed, capAmount, capStatus];
            }

            for (let i = 0; i < rows.length; i++) {
                parsed.push(rows[i].id);
            }

            return JSON.stringify(parsed);
        }

        function parseCountries(tableName, onlyCountries = false) {
            const rows = document.querySelectorAll('#' + tableName + ' > tbody > tr');
            const offerID = valueOf("offerID");
            const redirectOffer = valueOf("geoRedirectOffer");
            const geoRuleName = valueOf("geoRuleName");
            const countriesNotAllowed = isChecked("geoIsAllowed");

            let parsed = [];
            if (!onlyCountries) {
                parsed = [offerID, geoRuleName, redirectOffer, countriesNotAllowed];
            }

            for (let i = 0; i < rows.length; i++) {
                const capToggle = rows[i].querySelector('.cap_active');
                const capAmount = rows[i].querySelector('.cap_amount');

                parsed.push([
                    rows[i].id,
                    rows[i].children[0].innerText,
                    capToggle ? (capToggle.checked ? 1 : 0) : 0,
                    capAmount ? capAmount.value : 0
                ]);
            }

            return JSON.stringify(parsed);
        }

        function sortTable(table, order) {
            const asc = order === 'asc';
            const tbody = table?.querySelector('tbody');

            Array.from(tbody?.querySelectorAll('tr') || []).sort((a, b) => {
                const aText = a.querySelector('td:first-child')?.textContent || '';
                const bText = b.querySelector('td:first-child')?.textContent || '';
                return asc
                    ? aText.localeCompare(bText)
                    : bText.localeCompare(aText);
            }).forEach((row) => tbody.append(row));
        }

        function sortCountries(order = 'asc') {
            const asc = order === 'asc';
            const tbody = get("countryListBody");

            Array.from(tbody?.querySelectorAll('tr') || []).sort((a, b) => {
                const aText = a.querySelector('td:first-child')?.textContent || '';
                const bText = b.querySelector('td:first-child')?.textContent || '';
                return asc
                    ? aText.localeCompare(bText)
                    : bText.localeCompare(aText);
            }).forEach((row) => tbody.append(row));
        }

        function normalizeCountryCode(countryCode) {
            return String(countryCode || '').trim().toUpperCase();
        }

        function buildCountryRow(countryCode) {
            const normalizedCode = normalizeCountryCode(countryCode);
            const countryLabel = countryMap[normalizedCode] || normalizedCode;

            const row = document.createElement('tr');
            row.id = normalizedCode;
            row.innerHTML =
                '<td>' + countryLabel + '</td>' +
                '<td><button type="button" id="_' + normalizedCode + '" class="bp-rule-action-button" onclick="addCountry(\'' + normalizedCode + '\');"><span class="bp-rule-action-button-text">Add</span></button></td>';

            return row;
        }

        function getCountryRow(countryCode) {
            const normalizedCode = normalizeCountryCode(countryCode);
            let row = get(normalizedCode);

            if (!row) {
                row = get(normalizedCode.toLowerCase());
            }

            if (!row) {
                row = buildCountryRow(normalizedCode);
            }

            return row;
        }

        function setRuleActionState(actionButton, mode) {
            if (!actionButton) {
                return;
            }

            const isRemove = mode === 'remove';
            actionButton.classList.toggle('is-remove', isRemove);
            const label = actionButton.querySelector('.bp-rule-action-button-text');
            if (label) {
                label.textContent = isRemove ? 'Remove' : 'Add';
            }
        }

        function syncSelectValue(selectElement, rawValue, optionMap = {}) {
            if (!selectElement) {
                return;
            }

            const normalizedValue = String(rawValue || '').trim();

            if (!normalizedValue || normalizedValue === '0') {
                selectElement.value = '';
                return;
            }

            let option = Array.from(selectElement.options).find((item) => {
                return String(item.value).trim() === normalizedValue;
            });

            if (!option && optionMap[normalizedValue]) {
                option = new Option(optionMap[normalizedValue], normalizedValue);
                selectElement.append(option);
            }

            if (option) {
                Array.from(selectElement.options).forEach((item) => {
                    item.selected = false;
                });
                option.selected = true;
                selectElement.value = normalizedValue;
                selectElement.dispatchEvent(new Event('change'));
            }
        }

        function addCountry(countryName, capStatus = 0, cap = 0, sortTableAfter = true) {
            const normalizedCode = normalizeCountryCode(countryName);
            const capIsChecked = Number(capStatus) ? ' checked' : '';
            const row = getCountryRow(normalizedCode);

            row.remove();

            if (!document.getElementById(normalizedCode + '_capIsActive')) {
                const html =
                    '<td class="caps">' +
                        '<label class="bp-cap-toggle">' +
                            '<input class="cap_active" id="' + normalizedCode + '_capIsActive"' + capIsChecked + ' type="checkbox">' +
                            '<span>Enable</span>' +
                        '</label>' +
                        '<label class="bp-cap-input" for="' + normalizedCode + '_geoCap">' +
                            '<span>Cap</span>' +
                            '<input class="cap_amount" type="number" id="' + normalizedCode + '_geoCap" value="' + cap + '">' +
                        '</label>' +
                    '</td>';
                row.insertAdjacentHTML('beforeend', html);
            }

            document.querySelector("#toAdd tbody")?.append(row);
            const button = get("_" + normalizedCode);
            button?.setAttribute("onclick","removeCountry('" + normalizedCode +"');");
            setRuleActionState(button,"remove");

            if (sortTableAfter) {
                sortTable(get('toAdd'), 'asc');
            }
        }

        function removeCountry(countryName, sortTableAfter = true) {
            const normalizedCode = normalizeCountryCode(countryName);
            const selectedCountry = get(normalizedCode);

            if (!selectedCountry) {
                return;
            }

            selectedCountry.remove();
            selectedCountry.querySelector('td.caps')?.remove();
            get("countryListBody")?.append(selectedCountry);
            const button = get("_" + normalizedCode);
            button?.setAttribute("onclick","addCountry('" + normalizedCode +"');");
            setRuleActionState(button,"add");

            if (sortTableAfter) {
                sortCountries('asc');
            }
        }

    </script>
    @include('layouts.partials.sortable-table-script')
@endsection
