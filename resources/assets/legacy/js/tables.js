/**
 * Created by dean on 6/20/2017.
 */


function searchTable() {
	// Declare variables
	var input, filter, table, tr, td, i;
	input = document.getElementById("searchBox");
	filter = input.value.toUpperCase();
	table = document.getElementById("mainTable");
	tr = table.getElementsByTagName("tr");

	// Loop through all table rows, and hide those who don't match the search query
	for (i = 0; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td");
		
		
		for (k = 0; k < td.length; k++) {
			if (td[k].innerHTML.indexOf("TOTAL") > -1) {
				tr[i].style.display = "";
				return;
			}
			
			if (td[k].innerHTML.indexOf("</a>") === -1) {
				
				if (td[k].innerHTML.toUpperCase().indexOf(filter) > -1) {
					tr[i].style.display = "";
					break;
				} else {
					tr[i].style.display = "none";
				}
			}
			
		}
		
	}
}




// var inFormOrLink;
// $('a').on('click', function() { inFormOrLink = true; });
// $('form').bind('submit', function() { inFormOrLink = true; });
//
// $(window).bind('beforeunload', function(eventObject) {
//     var returnValue = undefined;
//     if (! inFormOrLink) {
//         returnValue = "Do you really want to close?";
//     }
//     eventObject.returnValue = returnValue;
//     return returnValue;
// });


$("#d_from").datepicker({dateFormat: 'yy-mm-dd'});
$("#d_to").datepicker({dateFormat: 'yy-mm-dd'});

//load datepickers..
$(function () {
    $("#d_from").datepicker({dateFormat: 'yy-mm-dd'});
    $("#d_to").datepicker({dateFormat: 'yy-mm-dd'});

});

String.prototype.replaceAll = function (str1, str2, ignore) {
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), (ignore ? "gi" : "g")), (typeof(str2) == "string") ? str2.replace(/\$/g, "$$$$") : str2);
};


function searchSelectBox(txtEle) {
    if (txtEle.id == "assigned")
        var selectBox = "replist";
    else
        var selectBox = "notAssigned";


    $('#' + selectBox + ' option').each(function (i, sel) {


        if (sel.text.toLowerCase().indexOf(txtEle.value.toLowerCase()) === -1) {
            //remove from list
            sel.style.display = "none";
            sel.disabled = true;
            // alert("cptn, we've reached maximmum dankness");

        }
        else {
            sel.disabled = false;
            sel.style.display = "block";
        }


    });


}


function areYouSure(dis) {


    if (!confirm('Are you sure you want to change this offer status?')) {
        $(dis).val($.data(dis, 'current')); // added parenthesis (edit)
        return false;

    }
    $.data(dis, 'current', $(dis).val());


}

function selectAll() {
    if (document.getElementById("assigned").value !== "") {
        if (!confirm("You currently have users filtered out from the assign to box, they will not be added unless you remove the search options. Continue?"))
            return false;
    }


    selectBox = document.getElementById("replist");

    for (var i = 0; i < selectBox.options.length; i++) {
        selectBox.options[i].selected = true;
    }

    selectBox = document.getElementById("notAssigned");

    for (var i = 0; i < selectBox.options.length; i++) {
        selectBox.options[i].selected = true;
    }


}

function selectAllMultiSelect(id) {
    selectBox = document.getElementById(id);

    for (var i = 0; i < selectBox.options.length; i++) {
        selectBox.options[i].selected = true;
    }
}

function selectAllBonuses() {

    var boxes = ['assignedAdmins', 'assignedManagers', 'assignedAffiliates', 'unAssignedAdmins', 'unAssignedManagers', 'unAssignedAffiliates'];
    for (var i = 0; i < boxes.length; i++) {
        selectBox = document.getElementById(boxes[i]);
        if (selectBox)
            for (var k = 0; k < selectBox.options.length; k++)
                selectBox.options[k].selected = true;
    }


}

function moveToSelect(ele, fromSelect, toSelect) {
    // var affName = $("#replist option[value=" + ele.value + "]").text();
    // var html = "<option  value=\"" + ele.value + "\" > "+ affName + " </option>";

    var html = "";

    $('#' + fromSelect + ' :selected').each(function (i, sel) {

        html += "<option value=\"" + sel.value + "\"> " + sel.text + "</option>";

        sel.remove();
    });
    $("#" + toSelect).append(html);

    // $("#replist option[value=" + ele.value + "]").remove();


}

function moveToUnAssign(ele) {
    // var affName = $("#replist option[value=" + ele.value + "]").text();
    // var html = "<option  value=\"" + ele.value + "\" > "+ affName + " </option>";

    var affName = "";
    var html = "";

    $('#replist :selected').each(function (i, sel) {

        html += "<option value=\"" + sel.value + "\"> " + sel.text + "</option>";

        sel.remove();
    });
    $("#notAssigned").append(html);

    // $("#replist option[value=" + ele.value + "]").remove();


}

function moveToAssign(ele) {
    var affName = "";
    var html = "";

    $('#notAssigned :selected').each(function (i, sel) {

        html += "<option value=\"" + sel.value + "\"> " + sel.text + "</option>";

        sel.remove();
    });
    $("#replist").append(html);
}


function copyToClipboard(elem) {
    // create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";
    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
    var origSelectionStart, origSelectionEnd;
    if (isInput) {
        // can just use the original source element for the selection and copy
        target = elem;
        origSelectionStart = elem.selectionStart;
        origSelectionEnd = elem.selectionEnd;
    } else {
        // must use a temporary form element for the selection and copy
        target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        var str = elem.textContent;
        str = str.replace(/\s/g, '');
        target.textContent = str;

    }
    // select the content
    var currentFocus = document.activeElement;
    target.focus();

    target.setSelectionRange(0, target.value.length);

    // copy the selection
    var succeed;
    try {
        succeed = document.execCommand("copy");
    } catch (e) {
        succeed = false;
    }
    // restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }

    if (isInput) {
        // restore prior selection
        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {
        // clear temporary content
        target.textContent = "";
    }
    succeed = succeed.replace(/\s/g, '');
    return succeed;
}


function confirmSendTo(message, sendTo) {
    if (confirm(message))
        window.location = sendTo;
}


function setCustom() {
    var sel = document.getElementById('preDefined');
    sel.value = 7;
    dateSelect = 7;

}

function refreshDates()
{
   handleDateSelect($("#d_from"));
   handleDateSelect($("#d_to"));
}

function handleDateSelect(elm) {
    var value1 = elm.value;

    var timezone = $("#timezone").val();

    var d = moment().tz(timezone);

    switch (value1) {
        //Today
        case "0":
            document.getElementById('d_from').value = d.format('YYYY-MM-DD');
            document.getElementById('d_to').value = d.format('YYYY-MM-DD');
            dateSelect = value1;
            break;

        //Yesterday
        case "1":
            document.getElementById('d_from').value = d.subtract(1, 'days').format("YYYY-MM-DD");
            document.getElementById('d_to').value = d.format("YYYY-MM-DD");
            dateSelect = value1;
            break;

        //Week to Date
        case "2":
            document.getElementById("d_from").value = moment().tz(timezone).startOf('isoWeek').format('YYYY-MM-DD');
            document.getElementById("d_to").value = moment().tz(timezone).format("YYYY-MM-DD");

            dateSelect = value1;
            break;

        //Month to Date
        case "3":

            document.getElementById("d_from").value = moment().tz(timezone).startOf('month').format('YYYY-MM-DD');
            document.getElementById("d_to").value = moment().tz(timezone).format("YYYY-MM-DD");
            dateSelect = value1;
            break;

        //Year to Date
        case "4":

            document.getElementById("d_from").value = moment().tz(timezone).startOf('year').format('YYYY-MM-DD');
            document.getElementById("d_to").value = moment().tz(timezone).format("YYYY-MM-DD");
            dateSelect = value1;
            break;

        //Last Week
        case "5":
            document.getElementById("d_from").value = moment().tz(timezone).subtract(1, 'weeks').startOf('isoWeek').format('YYYY-MM-DD');
            document.getElementById("d_to").value = moment().tz(timezone).subtract(1, 'weeks').endOf('isoWeek').format('YYYY-MM-DD');

            dateSelect = value1;
            break;

        //Last Month
        case "6":
            document.getElementById("d_from").value = moment().tz(timezone).subtract(1, 'months').startOf('month').format('YYYY-MM-DD');
            document.getElementById("d_to").value = moment().tz(timezone).subtract(1, 'months').endOf('month').format('YYYY-MM-DD');

            dateSelect = value1;
            break;

        //Custom
        case "7":
            document.getElementById('d_from').focus();
            dateSelect = value1;
            break;

        default:
            break;
    }

//                         window.location = '{$_SERVER['PHP_SELF']}?page=1&query={$query_box}&column={$column}&order={$last_order}&active={$active}&idoffer={$idoffer}&repID={$repID}&d_from={$d_from}&d_to={$d_to}&dateSelect='+ elm.value;
}

function processDates() {
    var d_from = document.getElementById("d_from").value;
    var d_to = document.getElementById("d_to").value;
    var strang = "&d_from=" + d_from + "&d_to=" + d_to + "&dateSelect=" + dateSelect;

    return strang;
}


function getSubVal()
{
    return $("#sub").val();
}



function processTimezone() {
    var timezone = document.getElementById("timezone").value;
    document.cookie = "timezone=" + timezone + "; expires=Thu, 18 Dec 2020 12:00:00 UTC";

    return "&timezone=" + timezone;
}

function submitRequest(PHP, TABLE_NAME) {

    var d_from = document.getElementById("d_from").value;
    var d_to = document.getElementById("d_to").value;

    var postString = "idoffer=" + idoffer + "&d_from=" + d_from + "&d_to=" + d_to;

    alert(postString);

    // var boxes = document.getElementsByTagName("input");
    //
    // for (var i = 0; i < boxes.length; i++) {
    //     if (i == 0) {
    //         postString += boxes[i].id + "=" + boxes[i].value;
    //     }
    //     else {
    //         postString += "&" + boxes[i].id + "=" + boxes[i].value;
    //     }
    // }


    // alert(postString);

    $.ajax({
        type: "POST",
        url: PHP,
        data: postString,
        success: function (x) {
            alert(x);
            updateTable(x, TABLE_NAME);
        }
    });


}


function updateTable(arr, TABLE_NAME) {
    $("#" + TABLE_NAME + " > tbody:last").children().remove();

    // parsed = JSON.parse(arr);
    // alert(parsed.length);
    // var htmlString = "";
    //
    //
    // for (var i = 0; i < parsed; i++) {
    //     htmlString += "<tr>";
    //
    //     var cur = parsed[i];
    //     for (var a = 0; a < cur; a++)
    //     {
    //         htmlString += "<td>" + cur[a] + "</td>";
    //
    //
    //     }
    //
    //     htmlString += "</tr>";
    // }

    $("#" + TABLE_NAME + " > tbody:last-child").append(arr);

}
