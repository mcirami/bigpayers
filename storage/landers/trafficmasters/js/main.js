$(document).ready(function() {
    $(".close-action").bind("click", function() {
        $(this).parent().parent().css("display", "none");
    });
	
	$('.scroll_arrow').click(function(e){
		e.preventDefault();
	});
});