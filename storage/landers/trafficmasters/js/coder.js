var itech = {
    init: function() {
        $("textarea[name='sql_content']").bind("focus", function() {
            if (this.value == "Please type you sql") { this.value = ""; }
        }).bind("blur", function() {
            if (this.value == "") { this.value = "Please type you sql"; }
        });
    },
    create: function() {
        itech.sql();
        itech.action("build");
    },
    view: function() {
        itech.sql();
        itech.action("view");
    },
    action: function(action) {
        var objForm = $("form[name='coder']");
        if ($("select[name='class_list']", objForm).val() == 0 && $("input[name='class_name']", objForm).val().replace(/\s/, "").length < 4) {
            $("input[name='class_name']", objForm).focus();
            alert("Please select a Class or type a Class name");
            return;
        }
        if ($("input[name='method_name']", objForm).val().replace(/\s/, "").length < 4) {
            $("input[name='method_name']", objForm).focus();
            alert("Please type a Method name");
            return;
        }
        var data_str = objForm.serialize();
        $.ajax({
            type : "post",
            url : objForm.attr("action") + action,
            data : data_str,
            success : function (e) {
                e = e.replace(/&lt;/ig, "<");
                e = e.replace(/&gt;/ig, ">");
                e = e.replace(/&quot;/ig, "\"");
                $("textarea[name='build_content']").val(e);
                if (action == "build") {
                    $( "#dialog-message" ).dialog({
                        modal: true,
                        buttons: { Ok: function() { $( this ).dialog( "close" ); } }
                    });
                }
            }
        });
    },
    valid: function() {
        var objForm = $("form[name='coder']");
        var valid = $("input[name='valid']", objForm);
        var param = $("input[name='param']", objForm);
        if (valid.attr("checked") && !param.attr("checked")) {
            param.attr("checked", true);
        }
    },
    sql: function() {
        var sqlContent = $("textarea[name='sql_content']");
        var typeList = $("select[name='type']");
        var sqlStr = sqlContent.val().toLowerCase();
        if (sqlStr.search(/insert\s/i) >= 0) {
            typeList.val(2);
        } else if (sqlStr.search(/update\s/i) >= 0) {
            typeList.val(3);
        } else if (sqlStr.search(/delete\s/i) >= 0) {
            typeList.val(4);
        } else {
            typeList.val((typeList.val() == 1) ? 1 : 0);
        }
    }
}

$(document).ready(function() {
    itech.init();
});