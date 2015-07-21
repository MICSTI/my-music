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
		console.log(msg);
	 })
	 .fail( function (error) {
		 console.log("AJAX search error", error);
	 });
}

$(document).ready( function () {
	// search function (we use a closure so we can pass the text input reference as an argument)
	$("#searchfield").on("keyup", function() { return search(this); } );
} );