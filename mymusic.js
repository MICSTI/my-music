var DATEPICKER_INIT_OPTIONS = {
			format: "dd.mm.yyyy",
			weekStart: 1
		};

function crudModal(_action, _id, _params) {
	if (_id === undefined)
		_id = 0;
	
	if (_params === undefined)
		_params = "";
	
	var modal = $("#music-modal");
	
	// fires before modal is shown
	modal.on("show.bs.modal", function() {
		// add selectpicker
		$(".selectpicker").selectpicker( {} );
	});

	// fites when modal has completely finished loading
	modal.on("shown.bs.modal", function() {
		// assign focus to autofocus element and set cursor to the end of the input element
		$(".autofocus").first().focus().putCursorAtEnd();
		
		// init datepicker
		$(".date-picker")
			.datepicker(DATEPICKER_INIT_OPTIONS)
			.on("changeDate", function(e) {
				// changeDate fires when month or year selection of datepicker is clicked, so we have to check if the user actually selected a new date
				if (e.viewMode === "days") {
					// hide datepicker after date was changed
					$(this).datepicker("hide");
				}
			});
		
		// add tooltips
		addTooltips();
	});
	
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
				globalNotify("Changes saved successfully");
				
				// success action
				switch (content.onSuccess) {
					case "updateSettings":
						updateSettingsContent(_tab);
						break;
						
					case "updateRecordInformation":
						updateRecordInformation(_id);
						break;
						
					default:
						break;
				}
			} else {
				// show error message
				globalNotify("Changes could not be saved", "error");
			}
			
			// remove this handler to avoid performing it more than once
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
	Update the content for the specified tab.
*/
function updateSettingsContent(target) {
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

/**
	Updates the detail information for a record.
*/
function updateRecordInformation(_id) {
	$.ajax( {
		method: "GET",
		url: "ajax.modal.php",
		data: {
			action: "JOqlKanU",
			id: _id
		}
	}).done(function(data) {
		var record = JSON.parse(data);
		
		if (record.success) {
			$("#record-info-type").html(record.record_type);
			$("#record-info-publish").html(record.publish);
		}
	}).fail(function(error) {
		// log error
		console.log("ajax.settings.php", error);
	});
}

/**
	Shows a global notification (one that is not bound to any DOM element).
*/
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

/**
	Assigns the bootstrap tooltip to all tooltip'd elements
*/
function addTooltips() {
	$("[data-toggle='tooltip']").tooltip();
}

/**
	Handles reordering the record types by importance.
*/
function reorderRecordTypes() {
	// toggle reorder and control buttons
	$("#btn-record-type-reorder").hide();
	$("#btn-record-type-control").show();
	
	// hide add button
	$("#btn-record-type-add").hide();
	
	// hide edit buttons
	$(".record-type-edit").hide();
	
	// make record type divs sortable
	$("#record-type-order").sortable();
	
	// cancel button
	$("#btn-record-type-cancel").on("click", function() {
		// show add button
		$("#btn-record-type-add").show();
		
		// show edit buttons
		$(".record-type-edit").show();
		
		// make record type divs not-sortable
		$("#record-type-order").sortable("destroy");
		
		// toggle reorder and control buttons
		$("#btn-record-type-control").hide();
		$("#btn-record-type-reorder").show();
	});
	
	// save button
	$("#btn-record-type-save").on("click", function() {
		$(this).hide();
		$("#btn-record-type-cancel").hide();
		
		var new_order = [];
		
		$(".record-type-edit").each(function(i, item) {
			// id is like "record-type-id-x", so the actual id starts at index 15
			new_order.push(item.id.substr(15));
		});
		
		// save order
		$.ajax( {
			method: "GET",
			url: "ajax.modal.php",
			data: {
				action: "U7GK66Ve",
				params: new_order.join(",")
			}
		}).done(function(data) {
			// parse JSON response
			var content = JSON.parse(data);
			
			if (content.success) {
				// show success message
				globalNotify("Changes saved successfully");
			} else {
				// show error message
				globalNotify("Changes could not be saved", "error");
			}
						
			// update content
			updateSettingsContent(content.tab);
		}).fail(function(error) {
			// log error
			console.log("persistCrud", error);
		});
	});
}

$(document).ready( function () {
	// make sure you can't submit the search form (would interfere with enter listener of auto complete)
	$("#form-search").on("keypress", function(event) { return event.keyCode != 13; });
	
	// auto complete for search
	var searchAutoComplete = new AutoComplete();
	
	var searchACOptions = {
		id: "search-field",
		url: "search.php",
		categories: ["*"],
		itemDisplay: function(_category, _item, _choiceClass) {
			switch (_category) {
				case "songs":
					return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.SongId + "'>" +
								"<div class='search_artist_name'>" + _item.ArtistName + "</div>" +
								"<div>" + _item.SongName + "</div>" +
								"<div class='search_record_name'>" + _item.RecordName + "</div>" +
							"</div>";
					
					break;
					
				default:
					return "";
					break;
			}
		},
		itemSelection: function(elem) {
			switch (elem.dataset.category) {
				case "songs":
					window.location.href = "song.php?id=" + elem.dataset.id;
					break;
					
				default:
					break;
			}
		}
	};
	
	searchAutoComplete.init(searchACOptions);
	
	// init datepicker
	$("#pickdate")
		.datepicker(DATEPICKER_INIT_OPTIONS)
		.on("changeDate", function(e) {
			// changeDate fires when month or year selection of datepicker is clicked, so we have to check if the user actually selected a new date
			if (e.viewMode === "days") {
				// hide datepicker after date was changed
				$(this).datepicker("hide");
				
				// call history page for this day
				var formatted = $(this).val();
				window.location.href = "history.php?date=" + formatted.substring(6) + "-" + formatted.substring(3, 5) + "-" + formatted.substring(0, 2);
			}
		});

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
				
				// add tooltips
				addTooltips();
			}).fail(function(error) {
				// log error
				console.log("ajax.settings.php", error);
			});
		});
	}
	
	var removeSettingsActive = function() {
		$("#settings a").removeClass("active");
	}
	
	// add tooltips
	addTooltips();
} );