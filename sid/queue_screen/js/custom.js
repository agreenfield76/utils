$(document).ready(function() {
	
	$("body").css("display", "none");

    $("body").fadeIn(500);
	
	setTimeout(function(){
		document.getElementById("testform").submit();
	}, 6000);
	
});
