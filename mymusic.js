function changeSettings(_action, _id) {
	var modal = $("#music-modal");
	
	$.ajax( {
		method: "GET",
		url: "ajax.settings.php",
		data: {
			action: _action,
			id: _id
		}
	}).done(function(data) {
		// set content of modal
		var content = JSON.parse(data);
		
		modal.find(".modal-title").text(content.title);
		modal.find(".modal-body").text(content.body);
		modal.find(".modal-footer").text(content.footer);
		
		modal.modal("show");
	}).fail(function(error) {
		// log error
		console.log("ajax.settings.php", error);
	});
}

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
		// list navigation (load content of clicked tab via AJAX)
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
					action: "tab",
					id: target
				}
			}).done(function(data) {
				// set content
				$("#settings-content").html(data);
			}).fail(function(error) {
				// log error
				console.log("ajax.settings.php", error);
			});
		});
		
		// modal
		/*$("#settings-modal").on("show.bs.modal", function(event) {
			// get button that triggered the modal
			var button = $(event.relatedTarget);
			
			// extract title
			var titleData = button.data("title");
			
			// set content of modal
			$(this).find(".modal-title").text(titleData);
		});*/
	}
	
	// Modal
	/*$("#music-modal").on("show.bs.modal", function(event) {
		// get the button that triggered the modal
		var button = $(event.relatedTarget);
		
		// extract id
		var id = button.data("id");
		
		// set content
		$(this).find(".modal-title").text(id);
	});*/
	
	var ModalRemote = function() {
		this.hi = function() {
			alert("HI");
		}
	}
	
	// init modal
	var modal = new ModalRemote();
	
	var removeSettingsActive = function() {
		$("#settings a").removeClass("active");
	}
} );