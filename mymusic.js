function crudModal(_action, _id, _params) {
	var modal = $("#music-modal");
	
	if (_id === undefined)
		_id = 0;
	
	if (_params === undefined)
		_params = "";
	
	$.ajax( {
		method: "GET",
		url: "ajax.modal.php",
		data: {
			action: _action,
			id: _id,
			params: _params
		}
	}).done(function(data) {
		// parse JSON response
		var content = JSON.parse(data);
		
		if (content.status == "ok") {
			// set content of modal
			modal.find(".modal-title").html(content.title);
			modal.find(".modal-body").html(content.body);
			modal.find(".modal-footer").html(content.footer);
			
			// attach save button handler
			modal.find(".modal-action-save").on("click", function() {
				persistCrud(content.save, _id, $("#" + content.form_name).serialize());
			});
			
			// show modal
			modal.modal("show");
		} else {
			// an error occurred
			console.log("ajax.modal.php", content.message);
		}
	}).fail(function(error) {
		// log error
		console.log("crudModal", error);
	});
}

function persistCrud(_action, _id, _params) {
	var modal = $("#music-modal");
	
	$.ajax( {
		method: "GET",
		url: "ajax.modal.php",
		data: {
			action: _action,
			id: _id,
			params: _params
		}
	}).done(function(data) {
		// parse JSON response
		var content = JSON.parse(data);
		
		if (content.success) {
			// set success message
			
		} else {
			// set error message
			
		}
		
		modal.modal("hide");
	}).fail(function(error) {
		// log error
		console.log("persistCrud", error);
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
	}
	
	var removeSettingsActive = function() {
		$("#settings a").removeClass("active");
	}
} );