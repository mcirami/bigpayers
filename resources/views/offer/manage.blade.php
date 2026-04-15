@php use LeadMax\TrackYourStats\System\Session; @endphp
@extends('layouts.master')

@section('content')

	<!--right_panel-->
	<div class="right_panel">
		<div class="white_box_outer large_table">
			<div class="heading_holder">
				<span class="lft value_span9">Offers</span>
				@if ( Session::permissions()->can("create_offers"))
					<a style='margin-left: 1%; margin-top:.3%;' href="/offer_add.php"
					   class='btn btn-default btn-sm value_span5-1 value_span6-5 value_span2'>Create New Offer</a>
				@endif
			</div>

			@if( Session::userType() !== \App\Privilege::ROLE_AFFILIATE)
				@include('report.options.active')
			@endif

			@if( Session::userType() == \App\Privilege::ROLE_AFFILIATE)
				<div class='form-group'>
					<p class='form-control'>
						Add up to 5 Sub variables as follows: http://domain.com/?repid=1&offerid=1&sub1=XXX&sub2=YYY&sub3=ZZZ&sub4=AAA&sub5=BBB
					</p>

				</div>
			@endif


			<script type="text/javascript">
				function handleSelect(elm) {
					window.location = '/{{request()->path()}}?url=' + elm.value <?= request('adminLogin',
							null) ? " + '&adminLogin'" : ""?>;
				}
			</script>


			<div style="margin:0 0 1px 0; padding:5px; width:250px;">

				@if( Session::userType() == \App\Privilege::ROLE_AFFILIATE)

					<label class="value_span9">Offer URLS: </label>
					<select onchange='handleSelect(this);' class="form-control input-sm " id="offer_url"
							name="offer_url">


						@for ($i = 0; $i < count($urls); $i++)
							@if (request('url',0) == $i) {
							<option selected value='{{$i}}'> {{$urls[$i]}}</option>
							@else
								<option value='{{$i}}'> {{$urls[$i]}}</option>
							@endif
						@endfor

					</select>

				@endif
			</div>


			<div class="form-group searchDiv">
				<input id="searchBox"
					   class="form-control"
					   type="text"
					   placeholder="Search offers...">
			</div>

			<div class="clear"></div>
			<div class="white_box manage_aff white_box_x_scroll large_table value_span8">


				<table class="table table-condensed table-bordered table_01" id="mainTable">
					<thead>

					<tr>
						<th class="value_span9">Offer ID</th>
						<th class="value_span9">Offer Name</th>
						<th class="value_span9">Offer Type</th>

						@if ( Session::userType() == \App\Privilege::ROLE_AFFILIATE)
							<th class="value_span9">Offer Link</th>
                        @elseif( Session::permissions()->can("edit_affiliates") &&
        Session::userType() != \App\Privilege::ROLE_AFFILIATE &&
        Session::userType() != \App\Privilege::ROLE_MANAGER)
                            <th class="value_span9">Affiliate Access</th>
                        @endif


						@if ( Session::userType() !== \App\Privilege::ROLE_MANAGER)
							<th class="value_span8">Payout</th>
						@endif

						<th class="value_span9">Adv</th>
						@if ( Session::userType() == \App\Privilege::ROLE_AFFILIATE)
							<th class="value_span9">Postback Options</th>
						@endif

						@if ( Session::userType() != \App\Privilege::ROLE_AFFILIATE)
							<th class="value_span9">Offer Timestamp</th>
						@endif

						@if ( Session::userType() != \App\Privilege::ROLE_AFFILIATE)
							<th class="value_span9">Actions</th>
						@endif
					</tr>
					</thead>
					<tbody id="offers_container">


					@if(isset($requestableOffers))
						@foreach ($requestableOffers as $offer)
							<tr>
								<td>{{$offer->idoffer}}</td>
								<td>{{$offer->offer_name}}</td>
								<td>Requires Offer</td>
								<td>${{$offer->payout}}</td>
								<td>{{$offer->campaign_name}}</td>
								<td>Requires Offer</td>
								<td>
									<button id='btn_{{$offer->idoffer}}' class='btn btn-sm btn-default'
											onclick='requestOffer({{$offer->idoffer}})'>Request Offer
									</button>
								</td>
							</tr>
						@endforeach
					@endif


					</tbody>
				</table>

				<div id="pagination" class="pagination-container"></div>

			</div>
		</div>
	</div>
	<!--right_panel-->

@endsection

@section('footer')
	<script type="text/javascript">
		function requestOffer(id) {


			$("#btn_" + id).attr('disabled', true);

			$.ajax({
				url: "/offer/" + id + '/request?' <?= (isset($_GET["adminLogin"])) ? " + '&adminLogin'" : ""?>,
				success: function (result) {

					$.notify({

								title: 'Successfully',
								message: ' requested offer!'

							}, {
								placement: {
									from: 'top',
									align: 'center'
								},
								type: 'info',
								animate: {
									enter: 'animated fadeInDown',
									exit: 'animated fadeOutUp'
								},
							}
					);
				},

				error: function (result) {
					$("#btn_" + id).attr('disabled', false);

					$.notify({

								title: 'Failed to request offer!',
								message: ' Please try again later or contact an admin.'

							}, {
								placement: {
									from: 'top',
									align: 'center'
								},
								type: 'danger',
								animate: {
									enter: 'animated fadeInDown',
									exit: 'animated fadeOutUp'
								},
							}
					);
				}
			});

		}
	</script>

	<script type="text/javascript">

		$(document).ready(function () {
			document.querySelectorAll('.delete_offer').forEach((offer) => {
				offer.addEventListener('click', (e) =>{
					e.preventDefault();
					const offerID = e.target.dataset.offer;
					confirmSendTo('Are you sure you want to delete this offer?', "/offer/" + offerID + "/delete");
				})
			});

			let itemsPerPage = 20;
			let offersCollection = '<?php echo $offers; ?>';
			let offers = JSON.parse(offersCollection);

			const paginationContainer = "#pagination";

			document.getElementById('searchBox').addEventListener('input', (e) => {
				const userInput = e.target.value.trim().toLowerCase();
				let filteredOffers = offers.filter((offer) => {
					return offer.offer_name.toLowerCase().includes(userInput) || offer.idoffer.toString().includes(userInput);
				})
				paginate(filteredOffers, itemsPerPage, paginationContainer);
			});

			paginate(offers, itemsPerPage, paginationContainer);

			function paginate(items, itemsPerPage, paginationContainer) {
				let currentPage = 1;
				const totalPages = Math.ceil(items.length / itemsPerPage);

				function showItems(page) {
					const startIndex = (page - 1) * itemsPerPage;
					const endIndex = startIndex + itemsPerPage;
					const pageItems = items.slice(startIndex, endIndex);

					const itemsContainer = document.querySelector("#offers_container");
					itemsContainer.innerHTML = "";

					let html = "";
					let userType = '<?php echo Session::userType(); ?>';
					let url = '<?php echo $urls[request('url',0)]; ?>';
					let permissions = '<?php echo json_encode( Session::permissions()); ?>'
					permissions = JSON.parse(permissions);
					const sessionUser = '<?php echo Session::userID(); ?>';
					pageItems.forEach((offer) => {
						html += `<tr id='offer_row'>` +
								`<td>` + offer['idoffer'] + `</td>` +
								`<td>` + offer['offer_name'];

						if (userType == 3) {
							html += `<br/><span class='link_label'>Offer Link:</span><br /> ` +
								`<span class='offer_link'>https://` + url +
								`/?repid=` + sessionUser +
								`&offerid=` + offer['idoffer'] + `&sub1=</span>`;
						}

						html += `</td>` +
								`<td>CPA</td>`;

						if (parseInt(userType) === 3) {
							html +=
									`<td class='value_span10'>` +
									`<p  style='display:none;' id='url_` + offer['idoffer'] + `'>http://` + url +
									`/?repid=` + sessionUser +
											`&offerid=` + offer['idoffer'] + `&sub1=</p>` +
									`<button data-url='https://` + url +
									`/?repid=` + sessionUser +
											`&offerid=` + offer['idoffer'] + `&sub1=' data-toggle='tooltip' title='Copy My Link' ` +
									`class='copy_button btn btn-default'>Copy My Link` +
									`</button></td>`;
						}

						if (permissions.permissions.edit_affiliates &&
							(parseInt(userType) === 0 || parseInt(userType) === 1)) {
							html += `<td class='value_span10'>` +
								`<a target='_blank' class='btn btn-sm btn-default value_span5-1' href='/offer_access.php?id=` +
								offer['idoffer'] + `'>Affiliate Access</a>` +
								`</td>`;
						}

						if (parseInt(userType) !== 2) {
							if (parseInt(userType) === 3) {
								html += `<td class='value_span10'>$` + offer['pivot']['payout'] + `</td>`;
							} else {
								html += `<td class='value_span10'>$` + offer['payout'] + `</td>`;
							}
						}

						html += `<td class='value_span10'>` + offer['campaign_name'] + `</td>`;
						/*if (offer['status'] === 1) {
							html += `Active`;
						} else {
							html += `Inactive`;
						}*/


						if (parseInt(userType) === 3) {
							html += `<td class='value_span10'>` +
									`<a class='btn btn-default value_span6-1 value_span4' data-toggle='tooltip' title='Offer PostBack Options' ` +
									`href='/offer_edit_pb.php?offid='` + offer['idoffer'] + `'>` +
									`Edit Post Back</a>` +
									`</td>`;
						}

						if (parseInt(userType) !== 3) {
							html += `<td class='value_span10'>` + offer['offer_timestamp'] + `</td>`;
						}

						if (parseInt(userType) !== 3) {
							html += `<td class='value_span10 action_column'>`;
						}
						if (parseInt(userType) !== 3) {
							if (permissions.permissions.create_offers) {
								html += `<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Edit Offer' ` +
										`href='/offer_update.php?idoffer=` + offer['idoffer'] + `'>Edit</a>`;
							}
						}

						if(permissions.permissions.edit_offer_rules && parseInt(userType) !== 3) {
							html += `<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Edit Offer Rules' ` +
									`href='/offer_edit_rules.php?offid=` + offer[`idoffer`] + `'> Rules</a>`;
						}

						if(parseInt(userType) !== 3) {
							html += `<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='View Offer' ` +
									`href='/offer_details.php?idoffer=` + offer['idoffer'] + `'> View</a>`;
						}

						if (parseInt(userType) === 0) {
							html +=
									`<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Duplicate Offer' ` +
									`href='/offer/` + offer['idoffer'] + `/dupe'> Duplicate </a>` +
									`<a class='delete_offer btn btn-default btn-sm value_span11 value_span4' data-toggle='tooltip' data-offer='` + offer['idoffer'] +`' title='Delete Offer' ` +
									`href='#'>Delete</a>`;
						}

						if(parseInt(userType) !== 3) {
							html += `</td>`;
						}
						html += `</tr>`;
						itemsContainer.innerHTML = html;
					});
					copyLink()
				}

				function setupPagination() {
					const pagination = document.querySelector(paginationContainer);
					pagination.innerHTML = "";

					for (let i = 1; i <= totalPages; i++) {
						const link = document.createElement("a");
						link.href = "#";
						link.innerText = i;
						link.classList.add("value_span2-2", "value_span3-2", "value_span6-1", "value_span2", "value_span6");

						if (i === currentPage) {
							link.classList.add("value_span4", "active");
						}

						link.addEventListener("click", (event) => {
							event.preventDefault();
							currentPage = i;
							showItems(currentPage);

							const currentActive = pagination.querySelector(".active");
							currentActive.classList.remove("active", "value_span4");
							link.classList.add("active", "value_span4");
						});

						pagination.appendChild(link);
					}
				}
				copyLink();

				showItems(currentPage);
				setupPagination();
			}

			function copyLink() {
				document.querySelectorAll('.copy_button').forEach((button) => {
					button.addEventListener("click", (e) => {
						e.preventDefault();
						const url = e.target.dataset.url;
						const unsecuredCopyToClipboard = (text) => { const textArea = document.createElement("textarea"); textArea.value=text; document.body.appendChild(textArea); textArea.focus();textArea.select(); try{document.execCommand('copy')}catch(err){console.error('Unable to copy to clipboard',err)}document.body.removeChild(textArea)};
						if (window.isSecureContext && navigator.clipboard) {
							navigator.clipboard.writeText(url);
						} else {
							unsecuredCopyToClipboard(url);
						}
					})
				})
			}

			$("#mainTable").tablesorter(
					{
						sortList: [[1, 0]],
						widgets: ['staticRow']
					});
		});
	</script>
@endsection

