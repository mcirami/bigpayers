
class geoEdit {



    constructor(ruleID)
    {
        this.ruleID = ruleID;
    }


    loadGeoRule() {



        this.loadRuleCountries();

        this.getGeoRuleInfo();

        $("#geoRuleTitle").text("Edit Rule");

        $("#geoRuleID").val(this.ruleID);
        $("#geoCreateButton").hide();
        $("#geoUpdateButton").show();

        $("#geoUpdateButton").click(function () {
            var geo = new geoEdit(this.ruleID);
            geo.updateRule();

        });







    }

    updateRule()
    {
        var ruleData = {name: $("#geoRuleName").val(), ruleID: $("#geoRuleID").val(), redirectOffer: $("#geoRedirectOffer").val(), deny: document.getElementById("geoIsAllowed").checked, is_active: document.getElementById("geoIsActive").checked};
        console.log(ruleData);
        $.ajax({

                type: "POST",
                url: "/offer/rules/geo/" + ruleData["ruleID"],
                headers: {
                    "X-CSRF-TOKEN": window.csrfToken || $('meta[name="csrf-token"]').attr("content"),
                },
                data: {data: parseCountries("toAdd", true), ruleData: JSON.stringify(ruleData), ruleID: ruleData["ruleID"]},
                cache: false,
                traditional: true,
                success: function (result) {
                    console.log(result);
                    $('#geoModal').modal('hide');
                    location.reload();

                },
                error: function(result) {
                    alert(result);
                }


            }
        );


    }


     getGeoRuleInfo() {

        $.ajax({
            type: "GET",
            url: "/offer/rules/geo/" + this.ruleID,
            data: "&ruleInfo=1",
            cache: false,

            success: function (result) {
                console.log(result);
                var parsed = typeof result === "string" ? JSON.parse(result) : result;


                $("#geoRuleName").val(parsed["name"]);



                $('#geoRedirectOffer option[value="'+parsed["redirectOffer"]+'"]').prop('selected', true);
                console.log(parsed);

                if(parsed["deny"] === 1)
                    $("#geoIsAllowed").attr("checked", true);
                else
                    $("#geoIsAllowed").attr("checked", false);

                if(parsed["is_active"] === 1)
                    $("#geoIsActive").attr("checked", true);
                else
                    $("#geoIsActive").attr("checked", false);


            }
        });



    }


     loadRuleCountries() {

        $.ajax({

            type: "GET",
            url: "/offer/rules/geo/" + this.ruleID,
            data: "&getISOs=1",
            cache: false,

            success: function (result) {

                var parsed = typeof result === "string" ? JSON.parse(result) : result;

                for (var i = 0; i < parsed.length; i++)
                    addCountry(parsed[i]);


            }

        });

    }




}
