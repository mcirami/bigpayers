@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Offers')

@section('content')
    @php
        $sessionUserType = \LeadMax\TrackYourStats\System\Session::userType();
        $permissions = \LeadMax\TrackYourStats\System\Session::permissions();
        $canCreateOffers = $permissions->can('create_offers');
        $canEditAffiliates = $permissions->can('edit_affiliates');
        $canEditOfferRules = $permissions->can('edit_offer_rules');
        $isAffiliate = $sessionUserType == \App\Privilege::ROLE_AFFILIATE;
        $isManager = $sessionUserType == \App\Privilege::ROLE_MANAGER;
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">Offer management</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Browse active offers, request access, copy tracking URLs, and move into the deeper legacy offer tools when needed.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($canCreateOffers)
                        <a href="/offer/create" class="bp-button-primary">Create new offer</a>
                        <a href="/offer/mass-assign" class="bp-button-secondary">Mass assign offers</a>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                {{--<div class="bp-report-toolbar">
                    @if (!$isAffiliate)
                        @include('report.options.active')
                    @endif

                    @if ($isAffiliate)
                        <div class="bp-inline-note">
                            Add up to 5 Sub variables:
                            <span>http://domain.com/?repid=1&offerid=1&sub1=XXX&sub2=YYY&sub3=ZZZ&sub4=AAA&sub5=BBB</span>
                        </div>
                    @endif

                    @if ($isAffiliate)
                        <div class="bp-select-group">
                            <label class="value_span9" for="offer_url">Offer URLs</label>
                            <select onchange="handleSelect(this);" class="selectBox" id="offer_url" name="offer_url">
                                @for ($i = 0; $i < count($urls); $i++)
                                    <option value="{{ $i }}" {{ request('url', 0) == $i ? 'selected' : '' }}>{{ $urls[$i] }}</option>
                                @endfor
                            </select>
                        </div>
                    @endif
                </div>--}}

                <div class="bp-offer-search">
                    <label class="bp-detail-label" for="searchBox">Search offers</label>
                    <input id="searchBox" class="bp-search-input" type="text" placeholder="Search by offer name or ID">
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-2">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Visible Offers</p>
                <p class="bp-stat-value">{{ count($offers) }}</p>
                <p class="bp-stat-note">Current results loaded into the interactive offer table.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Offer URLs</p>
                <p class="bp-stat-value">{{ count($urls) }}</p>
                <p class="bp-stat-note">Available branded URL domains for outbound tracking links.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Offer Directory</p>
                    <h3 class="bp-section-title value_span9">Searchable offer table</h3>
                </div>
                <p class="text-sm text-slate-500">Table sorting and pagination are still powered by the legacy scripts underneath.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-condensed table-bordered table_01 bp-offer-manage-table" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">Offer</th>
                        @if ($isAffiliate)
                            <th class="value_span9">Link</th>
                        @elseif($canEditAffiliates && !$isAffiliate && !$isManager)
                            <th class="value_span9">Access</th>
                        @endif

                        @if (!$isManager)
                            <th class="value_span9">Payout</th>
                        @endif

                        @if ($isAffiliate)
                            <th class="value_span9">Postback</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody id="offers_container">
                    @if(isset($requestableOffers))
                        @foreach ($requestableOffers as $offer)
                            <tr>
                                <td>{{ $offer->idoffer }}</td>
                                <td>{{ $offer->offer_name }}</td>
                                <td>${{ $offer->payout }}</td>
                                <td>{{ $offer->campaign_name }}</td>
                                <td>Requires Offer</td>
                                <td>
                                    <button id="btn_{{ $offer->idoffer }}" class="btn btn-sm btn-default" onclick="requestOffer({{ $offer->idoffer }})">
                                        Request Offer
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>

                <div id="pagination" class="bp-pagination"></div>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        const adminLoginSuffix = @json(request()->has('adminLogin') ? '&adminLogin' : '');

        function handleSelect(elm) {
            window.location = "/{{ request()->path() }}?url=" + elm.value + adminLoginSuffix;
        }

        function requestOffer(id) {
            $("#btn_" + id).attr('disabled', true);

            $.ajax({
                url: "/offer/" + id + "/request?" + adminLoginSuffix.replace(/^&/, ""),
                success: function () {
                    $.notify(
                        {
                            title: "Successfully",
                            message: " requested offer!"
                        },
                        {
                            placement: { from: "top", align: "center" },
                            type: "info",
                            animate: { enter: "animated fadeInDown", exit: "animated fadeOutUp" }
                        }
                    );
                },
                error: function () {
                    $("#btn_" + id).attr('disabled', false);

                    $.notify(
                        {
                            title: "Failed to request offer!",
                            message: " Please try again later or contact an admin."
                        },
                        {
                            placement: { from: "top", align: "center" },
                            type: "danger",
                            animate: { enter: "animated fadeInDown", exit: "animated fadeOutUp" }
                        }
                    );
                }
            });
        }

        $(document).ready(function () {
            const userType = {{ (int) $sessionUserType }};
            const canCreateOffers = @json($canCreateOffers);
            const canEditAffiliates = @json($canEditAffiliates);
            const canEditOfferRules = @json($canEditOfferRules);
            const sessionUser = {{ (int) \LeadMax\TrackYourStats\System\Session::userID() }};
            const selectedUrl = @json($urls[request('url', 0)] ?? $urls[0] ?? request()->getHttpHost());
            const offers = @json($offers);
            const paginationContainer = "#pagination";
            const itemsContainer = document.querySelector("#offers_container");
            const searchBox = document.getElementById("searchBox");

            document.querySelectorAll(".delete_offer").forEach((offer) => {
                offer.addEventListener("click", (e) => {
                    e.preventDefault();
                    const offerID = e.target.dataset.offer;
                    confirmSendTo("Are you sure you want to delete this offer?", "/offer/" + offerID + "/delete");
                });
            });

            searchBox.addEventListener("input", (e) => {
                const userInput = e.target.value.trim().toLowerCase();
                const filteredOffers = offers.filter((offer) => {
                    return offer.offer_name.toLowerCase().includes(userInput) || offer.idoffer.toString().includes(userInput);
                });

                paginate(filteredOffers, 20, paginationContainer);
            });

            paginate(offers, 20, paginationContainer);

            function paginate(items, itemsPerPage, paginationContainer) {
                let currentPage = 1;
                const totalPages = Math.ceil(items.length / itemsPerPage);

                function showItems(page) {
                    const startIndex = (page - 1) * itemsPerPage;
                    const endIndex = startIndex + itemsPerPage;
                    const pageItems = items.slice(startIndex, endIndex);

                    itemsContainer.innerHTML = "";

                    let html = "";

                    pageItems.forEach((offer) => {
                        html += "<tr>" +
                            "<td>" + offer.idoffer + "</td>" +
                            "<td>" + offer.offer_name;

                        if (userType === 3) {
                            html += "<br><span class='link_label'>Offer Link:</span><br>" +
                                "<span class='offer_link'>https://" + selectedUrl +
                                "/?repid=" + sessionUser +
                                "&offerid=" + offer.idoffer + "&sub1=</span>";
                        }

                        html += "</td>";

                        if (userType === 3) {
                            html += "<td class='value_span10'>" +
                                "<button data-url='https://" + selectedUrl +
                                "/?repid=" + sessionUser +
                                "&offerid=" + offer.idoffer + "&sub1=' data-toggle='tooltip' title='Copy My Link' class='copy_button btn btn-default'>Copy My Link</button></td>";
                        }

                        if (canEditAffiliates && (userType === 0 || userType === 1)) {
                            html += "<td class='value_span10'>" +
                                "<a target='_blank' class='btn btn-sm btn-default value_span5-1' href='/offer_access.php?id=" + offer.idoffer + "'>Affiliate Access</a>" +
                                "</td>";
                        }

                        if (userType !== 2) {
                            if (userType === 3) {
                                html += "<td class='value_span10'>$" + offer.pivot.payout + "</td>";
                            } else {
                                html += "<td class='value_span10'>$" + offer.payout + "</td>";
                            }
                        }

                        /*html += "<td class='value_span10'>" + offer.campaign_name + "</td>";*/

                        if (userType === 3) {
                            html += "<td class='value_span10'>" +
                                "<a class='btn btn-default value_span6-1 value_span4' data-toggle='tooltip' title='Offer PostBack Options' href='/offer_edit_pb.php?offid=" + offer.idoffer + "'>Edit Post Back</a>" +
                                "</td>";
                        }

                       /* if (userType !== 3) {
                            html += "<td class='value_span10'>" + offer.offer_timestamp + "</td>";
                            html += "<td class='value_span10 action_column'><div class='bp-table-actions'>";
                        }*/

                        if (userType !== 3 && canCreateOffers) {
                            html += "<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Edit Offer' href='/offer/edit/" + offer.idoffer + "'>Edit</a>";
                        }

                        if (canEditOfferRules && userType !== 3) {
                            html += "<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Edit Offer Rules' href='/offer/rules/" + offer.idoffer + "'>Rules</a>";
                        }

                        /*if (userType !== 3) {
                            html += "<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='View Offer' href='/offer/view/" + offer.idoffer + "'>View</a>";
                        }*/

                        if (userType === 0) {
                            html += "<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Duplicate Offer' href='/offer/" + offer.idoffer + "/dupe'>Duplicate</a>" +
                                "<a class='delete_offer btn btn-default btn-sm value_span11 value_span4' data-toggle='tooltip' data-offer='" + offer.idoffer + "' title='Delete Offer' href='#'>Delete</a>";
                        }

                       /* if (userType !== 3) {
                            html += "</div></td>";
                        }*/

                        html += "</tr>";
                    });

                    itemsContainer.innerHTML = html;
                    copyLink();
                    bindDeleteButtons();
                }

                function setupPagination() {
                    const pagination = document.querySelector(paginationContainer);
                    pagination.innerHTML = "";

                    if (totalPages <= 1) {
                        return;
                    }

                    for (let i = 1; i <= totalPages; i++) {
                        const link = document.createElement("a");
                        link.href = "#";
                        link.innerText = i;
                        link.classList.add("value_span2-2", "value_span3-2", "value_span6-1", "value_span2", "value_span6", "bp-pagination-link");

                        if (i === currentPage) {
                            link.classList.add("value_span4", "active");
                        }

                        link.addEventListener("click", (event) => {
                            event.preventDefault();
                            currentPage = i;
                            showItems(currentPage);

                            const currentActive = pagination.querySelector(".active");
                            if (currentActive) {
                                currentActive.classList.remove("active", "value_span4");
                            }

                            link.classList.add("active", "value_span4");
                        });

                        pagination.appendChild(link);
                    }
                }

                showItems(currentPage);
                setupPagination();
            }

            function bindDeleteButtons() {
                document.querySelectorAll(".delete_offer").forEach((offer) => {
                    offer.addEventListener("click", (e) => {
                        e.preventDefault();
                        const offerID = e.target.dataset.offer;
                        confirmSendTo("Are you sure you want to delete this offer?", "/offer/" + offerID + "/delete");
                    });
                });
            }

            function copyLink() {
                document.querySelectorAll(".copy_button").forEach((button) => {
                    button.addEventListener("click", (e) => {
                        e.preventDefault();
                        const url = e.target.dataset.url;

                        const unsecuredCopyToClipboard = (text) => {
                            const textArea = document.createElement("textarea");
                            textArea.value = text;
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();

                            try {
                                document.execCommand("copy");
                            } catch (err) {
                                console.error("Unable to copy to clipboard", err);
                            }

                            document.body.removeChild(textArea);
                        };

                        if (window.isSecureContext && navigator.clipboard) {
                            navigator.clipboard.writeText(url);
                        } else {
                            unsecuredCopyToClipboard(url);
                        }
                    });
                });
            }

            $("#mainTable").tablesorter({
                sortList: [[1, 0]],
                widgets: ["staticRow"]
            });
        });
    </script>
@endsection
