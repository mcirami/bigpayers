@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Offer Rules')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">Rules for {{ $offer->offer_name ?: 'Offer #' . $offer->idoffer }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Manage geo, device, and cap rules for this offer without dropping into the old page shell.
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
                    <button type="button" class="bp-button-secondary" data-toggle="modal" data-target="#geoModal">
                        Add geo rule
                    </button>
                    <button type="button" class="bp-button-secondary" data-toggle="modal" data-target="#deviceModal">
                        Add device rule
                    </button>
                    <a class="bp-button-secondary" href="/create_none_unique.php?id={{ $offer->idoffer }}">Add none-unique rule</a>
                </div>
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table id="rules" class="table table-bordered table_01 tablesorter bp-rules-table">
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

    <div class="modal fade" id="geoModal" tabindex="-1" role="dialog" aria-labelledby="geoModalLabel">
        <div class="modal-dialog modal-lg bp-rules-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="geoRuleTitle">New Geo Rule</h4>
                </div>
                <div class="modal-body">
                    <div class="bp-rules-modal-grid">
                        <div class="bp-rules-panel">
                            <div class="bp-rules-panel-head">
                                <label class="control-label">Country List</label>
                                <input type="text" id="searchCountryList" class="bp-form-input bp-rules-search" placeholder="Search countries...">
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="countryList" class="table table-bordered table-striped bp-rules-modal-table">
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
                                <label class="control-label">Items</label>
                                <p class="bp-rules-panel-note">Add selected countries to this rule and optionally apply caps.</p>
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="toAdd" class="table table-bordered table-striped bp-rules-modal-table">
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

                    <div class="bp-rules-settings-grid mt-4">
                        <label class="bp-choice-pill">
                            <input id="geoIsAllowed" type="checkbox">
                            <span>Items in this list will be denied</span>
                        </label>

                        <label class="bp-choice-pill">
                            <input checked id="geoIsActive" type="checkbox">
                            <span>Rule is active</span>
                        </label>

                        <input type="hidden" id="offerID" value="{{ $offer->idoffer }}">
                        <input type="hidden" id="geoRuleID" value="">

                        <label class="bp-form-field">
                            <span class="bp-form-label">Rule Name</span>
                            <input type="text" class="bp-form-input" id="geoRuleName">
                        </label>

                        <label class="bp-form-field">
                            <span class="bp-form-label">Redirect Offer</span>
                            {!! $geoRedirectOfferSelect !!}
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="geoCancelButton" type="button" class="bp-button-secondary" data-dismiss="modal">Cancel</button>
                    <button id="geoCreateButton" type="button" class="bp-button-primary">Create</button>
                    <button id="geoUpdateButton" type="button" class="bp-button-primary" style="display:none;">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deviceModal" tabindex="-1" role="dialog" aria-labelledby="deviceModalLabel">
        <div class="modal-dialog modal-lg bp-rules-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="deviceRuleTitle">New Device Rule</h4>
                </div>
                <div class="modal-body">
                    <div class="bp-rules-modal-grid">
                        <div class="bp-rules-panel">
                            <div class="bp-rules-panel-head">
                                <label class="control-label">Device List</label>
                                <p class="bp-rules-panel-note">Move devices into the rule to allow or deny them.</p>
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="deviceList" class="table table-bordered table-striped bp-rules-modal-table">
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
                                <label class="control-label">Items</label>
                                <p class="bp-rules-panel-note">Selected devices will be included in this rule configuration.</p>
                            </div>
                            <div class="bp-rules-table-scroll">
                                <table id="deviceToAdd" class="table table-bordered table-striped bp-rules-modal-table">
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

                    <div class="bp-rules-settings-grid mt-4">
                        <label class="bp-choice-pill">
                            <input id="deviceIsAllowed" type="checkbox">
                            <span>Items in this list will be denied</span>
                        </label>

                        <label class="bp-choice-pill">
                            <input checked id="deviceIsActive" type="checkbox">
                            <span>Rule is active</span>
                        </label>

                        <input type="hidden" id="deviceRuleID" value="">

                        <label class="bp-form-field">
                            <span class="bp-form-label">Rule Name</span>
                            <input type="text" class="bp-form-input" id="deviceRuleName">
                        </label>

                        <label class="bp-form-field">
                            <span class="bp-form-label">Redirect Offer</span>
                            {!! $deviceRedirectOfferSelect !!}
                        </label>

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
                </div>
                <div class="modal-footer">
                    <button id="deviceCancelButton" type="button" class="bp-button-secondary" data-dismiss="modal">Cancel</button>
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
        const redirectOfferMap = @json($redirectOfferMap ?? []);

        $(document).ready(function () {
            $("#rules").tablesorter({
                sortList: [[0, 0]]
            });
        });

        $("#searchCountryList").on('propertychange change keyup paste input', function () {
            searchCountryList($("#searchCountryList").val());
        });

        function searchCountryList(searchWords) {
            const filter = searchWords.toUpperCase();
            const table = document.getElementById("countryListBody");
            const rows = table.getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                const td = rows[i].getElementsByTagName("td")[0];
                if (td) {
                    rows[i].style.display = td.innerHTML.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }

        function editRule(ruleID, ruleType) {
            switch (ruleType) {
                case "geo":
                    resetGeoModal();
                    $("#geoRuleID").val(ruleID);
                    loadGeoRule(ruleID);
                    $('#geoModal').modal('show');
                    break;
                case "device":
                    resetDeviceModal();
                    $("#deviceRuleID").val(ruleID);
                    loadDeviceRule(ruleID);
                    $('#deviceModal').modal('show');
                    break;
            }
        }

        $("#geoCreateButton").click(function () {
            $.ajax({
                type: "POST",
                url: "/scripts/offer/rules/geo/addGeo.php",
                data: { data: parseCountries("toAdd") },
                cache: false,
                success: function () {
                    $("#geoModal").modal("hide");
                    location.reload();
                }
            });
        });

        $("#deviceCreateButton").click(function () {
            $.ajax({
                type: "POST",
                url: "/scripts/offer/rules/device/add.php",
                data: { data: parseDevices("deviceToAdd") },
                cache: false,
                success: function () {
                    $("#deviceModal").modal("hide");
                    location.reload();
                }
            });
        });

        function loadGeoRule(ruleID) {
            const rule = geoRules[String(ruleID)] || geoRules[ruleID];

            if (!rule) {
                return;
            }

            $("#geoRuleTitle").text("Edit Rule");
            $("#geoRuleID").val(ruleID);
            $("#geoRuleName").val(rule.name || "");
            syncSelectValue($("#geoRedirectOffer"), rule.redirectOffer, redirectOfferMap);
            $("#geoIsAllowed").prop("checked", Number(rule.deny) === 1);
            $("#geoIsActive").prop("checked", Number(rule.is_active) === 1);
            $("#geoCreateButton").hide();
            $("#geoUpdateButton").show();

            (rule.countries || []).forEach((country) => {
                addCountry(country.country_code, country.cap_status, country.cap, false);
            });

            sortTable($('#toAdd'), 'asc');
        }

        function loadDeviceRule(ruleID) {
            const rule = deviceRules[String(ruleID)] || deviceRules[ruleID];

            if (!rule) {
                return;
            }

            $("#deviceRuleTitle").text("Edit Rule");
            $("#deviceRuleID").val(ruleID);
            $("#deviceRuleName").val(rule.name || "");
            syncSelectValue($("#deviceRedirectOffer"), rule.redirectOffer, redirectOfferMap);
            $("#deviceIsAllowed").prop("checked", Number(rule.deny) === 1);
            $("#deviceIsActive").prop("checked", Number(rule.is_active) === 1);
            $("#capIsActive").prop("checked", Number(rule.capStatus) === 1);
            $("#deviceCap").val(rule.capAmount || 0);
            $("#deviceCreateButton").hide();
            $("#deviceUpdateButton").show();

            (rule.devices || []).forEach((deviceName) => {
                addDevice(deviceName);
            });
        }

        $("#geoUpdateButton").click(function () {
            const ruleData = {
                name: $("#geoRuleName").val(),
                ruleID: $("#geoRuleID").val(),
                redirectOffer: $("#geoRedirectOffer").val(),
                deny: document.getElementById("geoIsAllowed").checked,
                is_active: document.getElementById("geoIsActive").checked,
            };

            $.ajax({
                type: "POST",
                url: "/scripts/offer/rules/geo/editGeo.php",
                data: {
                    data: parseCountries("toAdd", true),
                    ruleData: JSON.stringify(ruleData),
                    ruleID: ruleData.ruleID,
                },
                cache: false,
                traditional: true,
                success: function () {
                    $("#geoModal").modal("hide");
                    location.reload();
                },
                error: function (result) {
                    alert(result.responseText || result);
                }
            });
        });

        $("#deviceUpdateButton").click(function () {
            const ruleData = {
                name: $("#deviceRuleName").val(),
                ruleID: $("#deviceRuleID").val(),
                redirectOffer: $("#deviceRedirectOffer").val(),
                deny: document.getElementById("deviceIsAllowed").checked,
                is_active: document.getElementById("deviceIsActive").checked,
                capAmount: document.getElementById("deviceCap").value,
                capStatus: document.getElementById("capIsActive").checked,
            };

            $.ajax({
                type: "POST",
                url: "/scripts/offer/rules/device/edit.php",
                data: {
                    data: parseDevices("deviceToAdd", true),
                    ruleData: JSON.stringify(ruleData),
                    ruleID: ruleData.ruleID,
                },
                cache: false,
                traditional: true,
                success: function () {
                    $("#deviceModal").modal("hide");
                    location.reload();
                },
                error: function (result) {
                    alert(result.responseText || result);
                }
            });
        });

        function resetDeviceModal() {
            const rows = $('#deviceToAdd > tbody > tr');

            $("#deviceRuleName").val("");
            $("#deviceRuleID").val("");
            $("#deviceRedirectOffer").val("");
            $("#deviceRuleTitle").text("New Device Rule");
            $("#deviceIsAllowed").prop("checked", false);
            $("#deviceIsActive").prop("checked", true);
            $("#deviceCreateButton").show();
            $("#deviceUpdateButton").hide();

            for (let i = 0; i < rows.length; i++) {
                $("#deviceListBody").append(rows[i]);
                $("#_" + rows[i].id).attr("onclick", "addDevice('" + rows[i].id + "')");
                setRuleActionState($("#_" + rows[i].id), "add");
            }
        }

        function resetGeoModal() {
            const rows = $('#toAdd > tbody > tr');

            $("#geoRuleName").val("");
            $("#geoRuleID").val("");
            $("#geoRedirectOffer").val("");
            $("#geoRuleTitle").text("New Geo Rule");
            $("#geoIsAllowed").prop("checked", false);
            $("#geoIsActive").prop("checked", true);
            $("#searchCountryList").val("");
            $("#geoCreateButton").show();
            $("#geoUpdateButton").hide();

            for (let i = 0; i < rows.length; i++) {
                rows[i].lastChild.remove();
                $("#countryListBody").append(rows[i]);
                $("#_" + rows[i].id).attr("onclick", "addCountry('" + rows[i].id + "')");
                setRuleActionState($("#_" + rows[i].id), "add");
            }

            sortCountries("a", "asc");
        }

        $("#geoCancelButton, #geoModal .close").click(function () {
            resetGeoModal();
        });

        $("#deviceCancelButton, #deviceModal .close").click(function () {
            resetDeviceModal();
        });

        function addDevice(deviceName) {
            const selectedDeviceTR = $("#" + deviceName);
            selectedDeviceTR.detach();
            $("#deviceToAdd tbody").append(selectedDeviceTR);
            $("#_" + deviceName).attr("onclick", "removeDevice('" + deviceName + "');");
            setRuleActionState($("#_" + deviceName), "remove");
        }

        function removeDevice(deviceName) {
            const selectedDevice = $("#" + deviceName);
            $(selectedDevice).remove();
            $("#deviceListBody").append('<tr id="' + deviceName + '">' + selectedDevice.html() + '</tr>');
            $("#_" + deviceName).attr("onclick", "addDevice('" + deviceName + "')");
            setRuleActionState($("#_" + deviceName), "add");
        }

        function parseDevices(tableName, onlyCountries = false) {
            const rows = $('#' + tableName + ' > tbody > tr');
            const offerID = $("#offerID").val();
            const redirectOffer = $("#deviceRedirectOffer").val();
            const ruleName = $("#deviceRuleName").val();
            const notAllowed = document.getElementById("deviceIsAllowed").checked;
            const capAmount = $("#deviceCap").val();
            const capStatus = $("#capIsActive").is(":checked");

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
            const rows = $('#' + tableName + ' > tbody > tr');
            const offerID = $("#offerID").val();
            const redirectOffer = $("#geoRedirectOffer").val();
            const geoRuleName = $("#geoRuleName").val();
            const countriesNotAllowed = document.getElementById("geoIsAllowed").checked;

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
            const tbody = table.find('tbody');

            tbody.find('tr').sort(function (a, b) {
                return asc
                    ? $('td:first', a).text().localeCompare($('td:first', b).text())
                    : $('td:first', b).text().localeCompare($('td:first', a).text());
            }).appendTo(tbody);
        }

        function sortCountries(table, order) {
            const asc = order === 'asc';
            const tbody = $("#countryListBody");

            tbody.find('tr').sort(function (a, b) {
                return asc
                    ? $('td:first', a).text().localeCompare($('td:first', b).text())
                    : $('td:first', b).text().localeCompare($('td:first', a).text());
            }).appendTo(tbody);
        }

        function normalizeCountryCode(countryCode) {
            return String(countryCode || '').trim().toUpperCase();
        }

        function buildCountryRow(countryCode) {
            const normalizedCode = normalizeCountryCode(countryCode);
            const countryLabel = countryMap[normalizedCode] || normalizedCode;

            return $(
                '<tr id="' + normalizedCode + '">' +
                    '<td>' + countryLabel + '</td>' +
                    '<td><button type="button" id="_' + normalizedCode + '" class="bp-rule-action-button" onclick="addCountry(\'' + normalizedCode + '\');"><span class="bp-rule-action-button-text">Add</span></button></td>' +
                '</tr>'
            );
        }

        function getCountryRow(countryCode) {
            const normalizedCode = normalizeCountryCode(countryCode);
            let row = $('#' + normalizedCode);

            if (!row.length) {
                row = $('#' + normalizedCode.toLowerCase());
            }

            if (!row.length) {
                row = buildCountryRow(normalizedCode);
            }

            return row;
        }

        function setRuleActionState(actionLink, mode) {
            const isRemove = mode === 'remove';
            actionLink.toggleClass('is-remove', isRemove);
            actionLink.find('.bp-rule-action-button-text').text(isRemove ? 'Remove' : 'Add');
        }

        function syncSelectValue(selectElement, rawValue, optionMap = {}) {
            const normalizedValue = String(rawValue || '').trim();

            if (!normalizedValue || normalizedValue === '0') {
                selectElement.val('');
                return;
            }

            let option = selectElement.find('option').filter(function () {
                return String($(this).val()).trim() === normalizedValue;
            }).first();

            if (!option.length && optionMap[normalizedValue]) {
                selectElement.append(
                    $('<option>', {
                        value: normalizedValue,
                        text: optionMap[normalizedValue]
                    })
                );

                option = selectElement.find('option').filter(function () {
                    return String($(this).val()).trim() === normalizedValue;
                }).first();
            }

            if (option.length) {
                selectElement.find('option').prop('selected', false);
                option.prop('selected', true);
                selectElement.val(normalizedValue).trigger('change');
            }
        }

        function addCountry(countryName, capStatus = 0, cap = 0, sortTableAfter = true) {
            const normalizedCode = normalizeCountryCode(countryName);
            const capIsChecked = Number(capStatus) ? ' checked' : '';
            const row = getCountryRow(normalizedCode);

            row.detach();

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
                row.append(html);
            }

            $("#toAdd tbody").append(row);
            $("#_" + normalizedCode).attr("onclick", "removeCountry('" + normalizedCode + "');");
            setRuleActionState($("#_" + normalizedCode), "remove");

            if (sortTableAfter) {
                sortTable($('#toAdd'), 'asc');
            }
        }

        function removeCountry(countryName, sortTableAfter = true) {
            const normalizedCode = normalizeCountryCode(countryName);
            const selectedCountry = $("#" + normalizedCode);
            $(selectedCountry).remove();
            selectedCountry[0].lastChild.remove();
            $("#countryListBody").append('<tr id="' + normalizedCode + '">' + selectedCountry.html() + '</tr>');
            $("#_" + normalizedCode).attr("onclick", "addCountry('" + normalizedCode + "');");
            setRuleActionState($("#_" + normalizedCode), "add");

            if (sortTableAfter) {
                sortCountries($('#countryList'), 'asc');
            }
        }

        $('.modal-dialog').draggable();

        $('#geoModal').on('show.bs.modal', function () {
            $(this).find('.modal-body').css({ 'max-height': '100%' });
        });
    </script>
@endsection
