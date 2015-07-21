var search = function(elem) {
	var text = $("#" + elem.id).val();
	
	$.ajax({
		method: "GET",
		url: "search.php",
		data: {
			search: text
		}
	})
	 .done( function (msg) {
		alert(msg);
	 });
}

$(document).ready( function () {
	// search function (we use a closure so we can pass the text input reference as an argument)
	$("#searchfield").on("keyup", function() { return search(this); } );
} );