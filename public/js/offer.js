






function getPageSet() {
    var requestString = "";


    var opts = ["offer_name", "status", "description", "url", "payout"];

    for(var i = 0; i < opts.length; i++)
    {
        if(isSet(opts[i]))
        {

                requestString += "&" + opts[i] + "=" + isSet(opts[i]).replace("&", "%26");

        }

    }


    alert(requestString);








    return requestString;
}





function isSet(eleID) {
    return $("#" + eleID).val();
}