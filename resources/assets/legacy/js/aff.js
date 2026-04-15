

function hideReferralForm()
{
    $("#referralP").hide();
    if($("#referralCheckBox").is(":checked"))
        $("#referralCheckBox").click();
}

function god()
{
    hideReferralForm();

    parseArrayToSelectBox(listGod);
}

function admin()
{
    hideReferralForm();


    parseArrayToSelectBox(listAdmin);
}

function manager()
{
    $("#referralP").show();


    parseArrayToSelectBox(listManager);
}

function appendToPermissions()
{
    $('<input/>').attr({type:'checkbox',name:'permissions[]', class:'fixCheckBox'}).appendTo('#permissionsP');
    $("#permissionsP").append("ASDF");
}
//
// function appendAdmin()
// {
//     $("#permissionsP").empty();
//
//     $('<input/>').attr({type:'checkbox',name:'permissions[]', class:'fixCheckBox', value:'create_admins'}).appendTo('#permissionsP');
//     $("#permissionsP").append("Can Create Admins");
//
// }
//
// function appendAffiliate()
// {
//     var p = $("#permissionsP");
//
//     p.empty();
// }
//
// function appendManager()
// {
//     var p = $("#permissionsP");
//
//     p.empty();
//
//     $('<input/>').attr({type:'checkbox',name:'permissions[]', class:'fixCheckBox', value:'create_managers'}).appendTo('#permissionsP');
//     p.append("Can Create Managers<br/>");
//
//     $('<input/>').attr({type:'checkbox',name:'permissions[]', class:'fixCheckBox', value:'create_offers'}).appendTo('#permissionsP');
//     p.append("Can Create Offers<br/>");
//
//     $('<input/>').attr({type:'checkbox',name:'permissions[]', class:'fixCheckBox', value:'create_affiliates'}).appendTo('#permissionsP');
//     p.append("Can Create Affiliates");
//
//
// }

function parseArrayToSelectBox(array)
{
    //find out select box
    box = $("#referrer_repid");
    //removes all options
    box
        .find('option')
        .remove();

    //loop through passed user array and append to select box
    for(var i = 0; i < array.length; i++)
    {
        var current = array[i];
        current = current.split(";");


        var o = new Option(current[1], current[0]);
/// jquerify the DOM object 'o' so we can use the html method
        $(o).html(current[1]);
        box.append(o);

    }

}