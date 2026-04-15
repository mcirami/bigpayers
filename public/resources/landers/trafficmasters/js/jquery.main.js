$(function(){
    //THIS IS THE JS USED ON ALL OF THE PAGES OF THE AFFILIATE AREA

    //giv our admin links at the bottom a slide up action
    $('#footer .head, #footer .block').hover(function(){
        // 6274 - added support for variable number of rows in the admin menu
        var admin_menu_row_count = $("#admin_menu_row_count").attr('value');
        var numberRows = parseInt(admin_menu_row_count);
        if (numberRows && numberRows >= 0 ) {
            var myHeight = (30 + (25 * numberRows)) * -1;
            $("#footer .slide-block").animate({top: myHeight+'px'},{duration:'fast', queue: false});
        }
        else {
            $("#footer .slide-block").animate({top: '-130px'},{duration:'fast', queue: false});
        }
    }, function(){
        $("#footer .slide-block").animate({top: '0px'},{duration:'fast', queue: false});
    });

    //give our navigation menu a nice drop down
    $("#nav li").hover(function(){
        $(this).children(".sub-nav").each(function(){
            $(this).show();
        });
        $(this).children(".sub-nav-child").each(function(){
            var parwid = $(this).parent().width();
            $(this).css({ "left": parwid+"px" });
            $(this).show();
        });
    }, function(){
        $(this).children("ul").hide();
    });

    //let any element be hidden
    $(".heading .open-close").click(function(){
        var curId = $(this).attr('id');
        var idParts = curId.split('-');
        if(idParts[0] == 'tog'){
            $(".disp-"+idParts[1]).slideToggle(50);
        }
        var curbtn = $(">span", this);
        $(this).parent().parent().siblings(".content").slideToggle(50, function(){
            var curdisp = $(this).css('display');
            if(curdisp == 'none'){
                curbtn.addClass("open-plus");
                curbtn.html("+");
                var setVal = "1";
            }
            else{
                curbtn.removeClass("open-plus");
                curbtn.html("-");
                var setVal = "0";
            }
            if(curId){
                if(idParts[1]) curId = idParts[1];
                //ajax update the setting
                $.post('ajax_data.php', { 'function': 'ajax_update_affiliate_setting', 'setting': curId, 'value': setVal });
            }
        });
        return false;
    });

    $(".tools .button").hover(function(){
        $(this).children("ul").show();
    }, function(){
        $(this).children("ul").hide();
    });

    //allow the closing of an action message
    $(".close-action").click(function(){
        $(this).parent().parent().remove();
        return false;
    });

    //allow the minizing of a page description
    $(".min-page-desc").click(function(){
        var curId = $(this).attr('id');
        var curbtn = $(this);
        $(this).parent().siblings('p').slideToggle(50, function(){
            var curdisp = $(this).css('display');
            if(curdisp == 'none'){
                curbtn.addClass("min-page-desc-plus");
                curbtn.html("+");
                var setVal = "1";
            }
            else{
                curbtn.removeClass("min-page-desc-plus");
                curbtn.html("-");
                var setVal = "0";
            }
            if(curId){
                //ajax update the setting
                $.post('ajax_data.php', { 'function': 'ajax_update_affiliate_setting', 'setting': curId, 'value': setVal });
            }
        });
        return false;
    });

    //setup the hover affect for the table rows
    $('.hover-row, .last-row').hover(function(){
        var prevRow = $(this).prev();
        var nextRow = $(this).next();
        var prntRowHead = $(this).parent().prev('thead');
        $('.hover-row, .last-row').removeClass('hover-next-row hover-matched-highlight-row hover-matched-row');
        if(prevRow.hasClass('hover-row') || prevRow.hasClass('last-row')){
            prevRow.addClass('hover-next-row');
            if($(this).hasClass('two-layer-top')){
                if($(this).hasClass('light-highlight-row')) nextRow.addClass('hover-matched-highlight-row');
                else nextRow.addClass('hover-matched-row');
                prevRow.prev().addClass('hover-next-row');
            }
            else if($(this).hasClass('two-layer-bottom')){
                if($(this).hasClass('light-highlight-row')) prevRow.addClass('hover-matched-highlight-row');
                else prevRow.addClass('hover-matched-row');
                if(prevRow.prev().hasClass('hover-row')){
                    prevRow.prev().addClass('hover-next-row');
                    prevRow.prev().prev().addClass('hover-next-row');
                }
                else if(prntRowHead.children('.hover-row') || prntRowHead.children('.last-row')) {
                    prntRowHead.children('.hover-row').addClass('hover-next-row');
                }
            }
        }
        else if(prntRowHead.children('.hover-row')) {
            prntRowHead.children('.hover-row').addClass('hover-next-row');
            if($(this).hasClass('two-layer-top')){
                if($(this).hasClass('light-highlight-row')) nextRow.addClass('hover-matched-highlight-row');
                else nextRow.addClass('hover-matched-row');
            }
            else if($(this).hasClass('two-layer-bottom')){
                if($(this).hasClass('light-highlight-row')) prevRow.addClass('hover-matched-highlight-row');
                else prevRow.addClass('hover-matched-row');
            }
        }
    }, function(){
        var prevRow = $(this).prev();
        var nextRow = $(this).next();
        var prntRowHead = $(this).parent().prev('thead');
        if(prevRow.hasClass('hover-row') || prevRow.hasClass('last-row')){
            prevRow.removeClass('hover-next-row');
            if($(this).hasClass('two-layer-top')){
                nextRow.removeClass('hover-matched-row hover-matched-highlight-row');
                prevRow.prev().removeClass('hover-next-row');
            }
            else if($(this).hasClass('two-layer-bottom')){
                prevRow.removeClass('hover-matched-row hover-matched-highlight-row');
                if(prevRow.prev().hasClass('hover-row')){
                    prevRow.prev().removeClass('hover-next-row');
                    prevRow.prev().prev().removeClass('hover-next-row');
                }
                else if(prntRowHead.children('.hover-row') || prntRowHead.children('.last-row')) {
                    prntRowHead.children('.hover-row').removeClass('hover-next-row');
                }
            }
        }
        else if(prntRowHead.children('.hover-row')) {
            prntRowHead.children('.hover-row').removeClass('hover-next-row');
            if($(this).hasClass('two-layer-top')){
                nextRow.removeClass('hover-matched-row hover-matched-highlight-row');
            }
            else if($(this).hasClass('two-layer-bottom')){
                prevRow.removeClass('hover-matched-row hover-matched-highlight-row');
            }
        }
    });

    $(".hover-row input, .last-row input").hover(function(){
        var curRow = $(this).parents('.hover-row, .last-row');
        var prevRow = curRow.prev();
        var prntRowHead = curRow.parent().prev('thead');

        if(prevRow.hasClass('hover-row')){
            prevRow.addClass('hover-next-row');
            if(curRow.hasClass('two-layer-top')){
                if(curRow.hasClass('light-highlight-row')) curRow.next().addClass('hover-matched-highlight-row');
                else curRow.next().addClass('hover-matched-row');
                prevRow.prev().addClass('hover-next-row');
            }
            else if(curRow.hasClass('two-layer-bottom')){
                if(curRow.hasClass('light-highlight-row')) prevRow.addClass('hover-matched-highlight-row');
                else prevRow.addClass('hover-matched-row');
                if(prevRow.prev().hasClass('hover-row')){
                    prevRow.prev().addClass('hover-next-row');
                    prevRow.prev().prev().addClass('hover-next-row');
                }
                else if(prntRowHead.children('.hover-row') || prntRowHead.children('.last-row')) {
                    prntRowHead.children('.hover-row').addClass('hover-next-row');
                }
            }
        }
        else if(prntRowHead.children('.hover-row')) {
            prntRowHead.children('.hover-row').addClass('hover-next-row');
            if(curRow.hasClass('two-layer-top')){
                if(curRow.hasClass('light-highlight-row')) curRow.next().addClass('hover-matched-highlight-row');
                else curRow.next().addClass('hover-matched-row');
            }
            else if(curRow.hasClass('two-layer-bottom')){
                if(curRow.hasClass('light-highlight-row')) prevRow.addClass('hover-matched-highlight-row');
                else prevRow.addClass('hover-matched-row');
            }
        }
    });

    var myTimer = {};

    //display mouseover images
    $(".mouseover_display_image").hover(function(){
        var myimagebox = $(this);
        // Set the timer for 2 seconds
        myTimer = $.timer(100,function(){
            myimagebox.children('.mouseover_image').children('img').show('fast');
        });

        if($(this).parents('.hover-row, .last-row')){

            var curRow = $(this).parents('.hover-row, .last-row');
            var prevRow = curRow.prev();
            var prntRowHead = curRow.parent().prev('thead');

            if(prevRow.hasClass('hover-row') || prevRow.hasClass('last-row')){
                prevRow.addClass('hover-next-row');
                if(curRow.hasClass('two-layer-top')){
                    if(curRow.hasClass('light-highlight-row')) curRow.next().addClass('hover-matched-highlight-row');
                    else curRow.next().addClass('hover-matched-row');
                    prevRow.prev().addClass('hover-next-row');
                }
                else if(curRow.hasClass('two-layer-bottom')){
                    if(curRow.hasClass('light-highlight-row')) prevRow.addClass('hover-matched-highlight-row');
                    else prevRow.addClass('hover-matched-row');
                    if(prevRow.prev().hasClass('hover-row')){
                        prevRow.prev().addClass('hover-next-row');
                        prevRow.prev().prev().addClass('hover-next-row');
                    }
                    else if(prntRowHead.children('.hover-row') || prntRowHead.children('.last-row')) {
                        prntRowHead.children('.hover-row').addClass('hover-next-row');
                    }
                }
            }
            else if(prntRowHead.children('.hover-row')) {
                prntRowHead.children('.hover-row').addClass('hover-next-row');
                if(curRow.hasClass('two-layer-top')){
                    if(curRow.hasClass('light-highlight-row')) curRow.next().addClass('hover-matched-highlight-row');
                    else curRow.next().addClass('hover-matched-row');
                }
                else if(curRow.hasClass('two-layer-bottom')){
                    if(curRow.hasClass('light-highlight-row')) prevRow.addClass('hover-matched-highlight-row');
                    else prevRow.addClass('hover-matched-row');
                }
            }
        }
        return false;
    }, function(){
        $(this).children('.mouseover_image').children('img').hide('fast');
        $.clearTimer(myTimer);
        return false;
    });

    //allow help box tooltips
    $(".helpbtn").tooltip({
        offset: [-20, 97],
        delay: 100,
        layout: '<div><div class="tooltip-arrow-border"></div><div class="tooltip-arrow"></div><div class="tooltip-hover-helper"></div></div>'
    }).dynamic({left: {offset: [-20,67]}});


});
