$(document).ready(function() {

    var timeInMinutes = 3.00;
    var currentTime = Date.parse(new Date());
    var deadline = new Date(currentTime + timeInMinutes*60*1000);

    initializeClock('countdown', deadline);

    getTimeRemaining(deadline).minutes;

    function getTimeRemaining(endtime) {
        var t = Date.parse(endtime) - Date.parse(new Date());
        var seconds = Math.floor( (t/1000) % 60);
        var minutes = Math.floor( (t/1000/60) % 60);

        if (minutes < 10 ) {
            minutes = "0" + minutes;
        }

        if (seconds < 10 ) {
            seconds = "0" + seconds;
        }

        return {
            'total' : t,
            'minutes' : minutes,
            'seconds' : seconds
        };
    }

    function initializeClock(id, endtime) {
        var clock = document.getElementById(id);
        var minutesSpan = clock.querySelector('.minutes');
        var secondsSpan = clock.querySelector('.seconds');


        function updateClock() {
            var t = getTimeRemaining(endtime);

            minutesSpan.innerHTML = t.minutes;
            secondsSpan.innerHTML = t.seconds;

            if (t.total <= 0) {
                clearInterval(timeinterval);
                clock.innerHTML = '<div class="row py-4">' +
                    '<div class="col-12 d-flex justify-content-center align-content-center">' +
                    '<img class="mr-2" src="images/stopwatch.jpg" alt="">' +
                    '<h3 class="minutes rounded-circle">00</h3>' +
                    '<span class="align-self-center px-2">:</span>' +
                    '<h3 class="seconds rounded-circle">00</h3>' +
                    '</div>' +
                    '</div>';
            }
        }

        updateClock();
        var timeinterval = setInterval(updateClock, 1000);
    }

    var splashpage_name 			= 'Jenny';
    var random_message_start 		= 3;
    var random_message_end 			= 6;
    var random_message_interval 	= (random_message_start + Math.floor(Math.random() * (random_message_end - random_message_start))) * 1000;
    var message_text_array = [
        "What's up babe? Accept my invite before it expires, I want to go live with you!" , "Hi babe. Accept my invite now so I can go live with you! Hurry before it expires!"
    ];
    var random_message_text 		= message_text_array[Math.floor(Math.random()*10+1)%2];

    $("#chat-send-form").submit(function (e) {
        e.preventDefault();

        var msg = $("input#message").val();
        appendChatMessage('<div class="chatOutgoingName" style="float: left; margin-right: 5px; color: #5cb85c;">Guest: </div>' + '<p style="color: #7c7c7c">' + msg + '</p>');
        sendAgeVerificationLink();
        return false;
    });
    sendInitialMessage();

    function findChat() {
        var chatFrame = document.getElementById("chat-frame");
        var chatWindow, chatDocument;
        if (window.frames && window.frames["chat-frame"]) chatWindow = window.frames["chat-frame"];
        else if (chatContents.contentWindow) chatWindow = chatFrame.contentWindow;
        else chatWindow = chatFrame;
        if (chatWindow.document) chatDocument = chatWindow.document;
        else chatDocument = chatWindow.contentDocument;
        return [chatWindow, chatDocument];
    }

    function appendChatMessage(message) {
        var chatContents = $("#chat-frame").contents().find('#contents');
        var chatBox = findChat();
        var outputMessage = $("<div>", chatBox[1]).html(message).appendTo(chatContents);
        $("#chat-frame").scrollTo(outputMessage, 500);
        $("#message").val("");
    }

    function sendInitialMessage() {
        setTimeout(function (i) {
            //$('#chat-typing-status').html('<p>Typing Status: Typing...</p>');
            setTimeout(function (i) {
                //$('#chat-typing-status').html('<p class="pull-left">Typing Status: Idle...</p>');
                appendChatMessage('<div class="chatIncomingName" style="float: left; margin-right: 5px; color: #2196f3;">' + splashpage_name + ': </div><span style="color: #7c7c7c">' + random_message_text + '</span>');
            }, random_message_interval);
        }, random_message_interval);
    }

    function sendAgeVerificationLink() {
        setTimeout(function (i) {
            appendChatMessage('<hr><span style="color: #f72d2b; text-transform: uppercase;">Error: </span>You must complete free signup process to communicate with this member.');
        }, 1000);
    }
});