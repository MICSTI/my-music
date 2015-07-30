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
				persistCrud(content.save, _id, $("#" + content.form_name).serialize(), content.tab_name);
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

function persistCrud(_action, _id, _params, _tab) {
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

		// wait until modal is completely hidden to update content and display success message
		modal.on("hidden.bs.modal", function() {
			if (content.success) {
				// show success message
				globalNotify("Changes successfully saved");
				
				// update content
				updateContent(_tab);
			} else {
				// show error message
				globalNotify("Changes could not be saved", "error");
			}
			
			// remove this handler to avoid performing more than once
			modal.off("hidden.bs.modal");
		});
		
		// actually hide the modal
		modal.modal("hide");
	}).fail(function(error) {
		// log error
		console.log("persistCrud", error);
	});
}

/**
	Update the content for the specified tab
*/
function updateContent(target) {
	$.ajax( {
		method: "GET",
		url: "ajax.settings.php",
		data: {
			action: "tab",
			id: target
		}
	}).done(function(data) {
		$("#settings-content").fadeOut(400, function() {
			// set content
			$(this).html(data).fadeIn(400);
		});
	}).fail(function(error) {
		// log error
		console.log("ajax.settings.php", error);
	});
}

function globalNotify(_text, _type, _position) {
	var text = _text || "Default";
	var type = _type || "success";
	var position = _position || "top center";
	
	// call notifiy
	$.notify(text, {
		className: type,
		globalPosition: position
	} );
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