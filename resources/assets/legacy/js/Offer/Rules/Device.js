class deviceEdit {
    constructor(ruleID) {
        this.ruleID = ruleID;
    }


    loadRule() {


        this.loadDevices();

        this.getDeviceRuleInfo();

        $("#deviceRuleTitle").text("Edit Rule");

        $("#deviceRuleID").val(this.ruleID);
        $("#deviceCreateButton").hide();
        $("#deviceUpdateButton").show();

        $("#deviceUpdateButton").click(function () {
            var geo = new deviceEdit(this.ruleID);
            geo.updateRule();

        });


    }


    updateRule() {

        var ruleData = {
            name: $("#deviceRuleName").val(),
            ruleID: $("#deviceRuleID").val(),
            redirectOffer: $("#deviceRedirectOffer").val(),
            deny: document.getElementById("deviceIsAllowed").checked,
            is_active: document.getElementById("deviceIsActive").checked
        };
        console.log(ruleData);
        $.ajax({

                type: "POST",
                url: "/scripts/offer/rules/device/edit.php",
                data: {data: parseDevices("deviceToAdd", true), ruleData: JSON.stringify(ruleData), ruleID: ruleData["ruleID"]},
                cache: false,
                traditional: true,
                success: function (result) {
                    console.log(result);
                    $('#deviceModal').modal('hide');
                    location.reload();

                },
                error: function (result) {
                    alert(result);
                }


            }
        );


    }


    loadDevices() {

        $.ajax({

            type: "GET",
            url: "/scripts/offer/rules/device/edit.php",
            data: "&ruleID=" + this.ruleID + "&getDevices=1",
            cache: false,

            success: function (result) {

                var parsed = JSON.parse(result);

                console.log("Devices:" + parsed);

                for (var i = 0; i < parsed.length; i++)
                    addDevice(parsed[i]);


            }

        });

    }

    getDeviceRuleInfo() {

        $.ajax({
            type: "GET",
            url: "/scripts/offer/rules/device/edit.php",
            data: "&ruleID=" + this.ruleID + "&ruleInfo=1",
            cache: false,

            success: function (result) {
                console.log(result);
                var parsed = JSON.parse(result);


                $("#deviceRuleName").val(parsed["name"]);


                $('#deviceRedirectOffer option[value="' + parsed["redirectOffer"] + '"]').prop('selected', true);
                console.log(parsed);

                if (parsed["deny"] === 1)
                    $("#deviceIsAllowed").attr("checked", true);
                else
                    $("#deviceIsAllowed").attr("checked", false);

                if (parsed["is_active"] === 1)
                    $("#deviceIsActive").attr("checked", true);
                else
                    $("#deviceIsActive").attr("checked", false);


            }
        });
    }
}