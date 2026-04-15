@extends('layouts.master')

@section('content')
    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer large_table ">
            <div class="heading_holder">
                <span class="lft value_span9">View User Accounts</span>

            </div>

            <div class='form-group '>
                @include('report.options.user-type')
                @include('report.options.active')
            </div>

            <div class="form-group searchDiv">
                <input id="searchBox"
                       class="form-control"
                       type="text"
                       placeholder="Search By Username, Email or ID" />
            </div>

            <div class="clear"></div>
            <div class="white_box_x_scroll white_box manage_aff large_table value_span8 ">
                <table class="table table-striped  table_01 manage_user_table " id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span8">User ID</th>
                        <th class="value_span8">Username</th>
                        <th class="value_span8">Actions</th>
                        <th class="value_span8">Manager</th>
                        <th class="value_span8">Timestamp</th>

                        @if (request('role',3) == 2)
                            <th></th>
                        @endif
                    </tr>
                    </thead>
                    <tbody id="users_container">
                    </tbody>
                </table>
            </div>
        </div>


    </div>
    <!--right_panel-->

@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
	        const EDIT_AFFILIATES = '<?php echo \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_AFFILIATES); ?>';
	        const CREATE_AFFILIATES = '<?php echo \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_AFFILIATES); ?>';
	        const CREATE_MANAGERS = '<?php echo \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_MANAGERS); ?>';
	        const role = '<?php echo request('role',3); ?>';

			let userCollection = '<?php echo $users; ?>';
            let users = JSON.parse(userCollection);

	        const itemsContainer = document.querySelector("#users_container");

	        document.getElementById('searchBox').addEventListener('input', (e) => {
		        const userInput = e.target.value.trim().toLowerCase();
		        let filteredUsers = users.filter((user) => {
			        return user.email.toLowerCase().includes(userInput) || user.user_name.toLowerCase().includes(userInput) || user.idrep.toString().includes(userInput);
		        })
		        showUsers(filteredUsers);
	        });

	        showUsers(users);
			function showUsers(users) {
				let html = "";

				users.forEach((user) => {
					html += "<tr> " +
						"<td>" + user['idrep'] + "</td>" +
						"<td>" + user['user_name'] + "</td>" +
                        "<td class='actions'>";
                            if (EDIT_AFFILIATES){
								html += "<a class='btn btn-default btn-sm value_span6-1 value_span4 ' data-toggle='tooltip' title='Edit User'" +
                                    " href='/aff_update.php?idrep=" + user['idrep'] + "'>Edit</a>";
                            }
							if(CREATE_AFFILIATES) {
								html += "<a class='btn btn-default btn-sm value_span5-1 ' data-toggle='tooltip' title='Login into this user'" +
									" href='#' onclick='adminLogin(" + user['idrep'] +")'>Login</a>";
                            }
							if(CREATE_MANAGERS && role == 2) {
								html += "<a class='btn btn-sm btn-default value_span5-1 ' data-toggle='tooltip' title='View Agents'" +
									" href='/user/" + user['idrep'] + "/affiliates'>View Agents</a>";
                            }
                    html +=    "</td>" +
                        "<td>" + user['referrer']['user_name'] +
						"<td>" + user['rep_timestamp'] + "</td>" +
                        "</tr>";
				})

                itemsContainer.innerHTML = html;
            }

	        $("#mainTable").tablesorter(
		        {
			        sortList: [[0, 0]],
			        widgets: ['staticRow']
		        });
        });
    </script>
@endsection

