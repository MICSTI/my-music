$(document).ready( function () {
	// make sure you can't submit the search form (would interfere with enter listener of auto complete)
	$("#form-search").on("keypress", function(event) { return event.keyCode != 13; });
	
	// auto complete
	var ac = new AutoComplete();
	ac.setId("searchfield");
	ac.setUrl("search.php");
	
	// datepicker
	var datepicker = $("#pickdate");
	if (datepicker.length > 0) {
		datepicker.datepicker( {
			format: "dd.mm.yyyy",
			weekStart: 1
		});
	}
	
	// settings
	var settings = $("#settings");
	if (settings.length > 0) {
		$("#settings a").on("click", function(e) {
			// remove active class
			removeSettingsActive();
			
			// get target
			var target = this.id.substring(+this.id.indexOf("-") + 1);
			
			// mark new active
			$(this).addClass("active");
			
			// get content
			$.ajax( {
				method: "GET",
				url: "ajax.settings.php",
				data: {
					tab: target
				}
			}).done(function(data) {
				// set content
				$("#settings-content").html(data);
			}).fail(function(error) {
				// log error
				console.log("ajax.settings.php", error);
			});
		});
	}
	
	var removeSettingsActive = function() {
		$("#settings a").removeClass("active");
	}
} );