// interval timer for updating the content of the update page
var updateContentTimer;

var DATEPICKER_INIT_OPTIONS = {
			format: "dd.mm.yyyy",
			weekStart: 1
		};
		
var ADD_SONG_AC_OPTIONS = {
	url: "search.php",
	categories: ["songs"],
	itemDisplay: function(_category, _item, _choiceClass) {
		switch (_category) {
			case "songs":
				return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.SongId + "' data-artist=\"" + _item.ArtistName + "\" data-song=\"" + _item.SongName + "\" data-record=\"" + _item.RecordName + "\">" +
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
		var parent_id = elem["parent-id"];
		
		switch (elem.dataset.category) {
			case "songs":
				$("#" + parent_id + "-result").hide();
				$("#" + parent_id + "-container .add-played-song-input").hide();
				$("#" + parent_id + "-container .add-played-song-display").html("<div class='pull-right'>" +
																					"<button type='button' class='btn btn-danger' onclick=\"$('#" + parent_id + "-container').remove()\">Remove</button>" +
																				"</div>" +
																				"<div>" +
																					"<input type='hidden' id='" + parent_id + "-song-id" + "' value='" + elem.dataset.id + "' />" + 
																					"<div>" + elem.dataset.song + "</div>" + 
																					"<div>" + elem.dataset.artist + "</div>" + 
																					"<div>" + elem.dataset.record + "</div>" +
																				"</div>"
																				)
																		  .show();
				break;
				
			default:
				break;
		}
		
		// hide result divs
		$(".ac_result").empty();
		
		// set focus to "Add song" button to easily add another song (or go to "Save" by hitting tab once)
		$("#add-played-song-add").focus();
	}
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
		initSelectpicker();
		
		// immediately remove handler to avoid attaching it over and over again
		modal.off("show.bs.modal");
	});

	// fites when modal has completely finished loading
	modal.on("shown.bs.modal", function() {
		// assign focus to autofocus element and set cursor to the end of the input element
		$(".modal-body .autofocus").first().focus().putCursorAtEnd();
		
		// init datepicker
		initDatepicker();
		
		// add tooltips
		addTooltips();
		
		// add control for played admin song input
		if ($("#played-admin-song-display").length > 0) {
			addPlayedAdminSongInputControl();
		}
		
		// immediately remove handler to avoid attaching it over and over again
		modal.off("shown.bs.modal");
	});
	
	// fires as the modal is being hidden
	modal.on("hidden.bs.modal", function() {
		// close all datepickers on this modal
		$(".modal-dialog .date-picker").datepicker("hide");
		
		// immediately remove handler to avoid attaching it over and over again
		modal.off("hidden.bs.modal");
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
			
			// attach ok button handler
			modal.find(".modal-action-ok").on("click", function() {
				modal.modal("hide");
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

function getStatic(_content, onSuccess) {
	$.ajax( {
		method: "GET",
		url: "ajax.static.php",
		data: {
			content: _content
		}
	}).done(function(response) {
		var data = JSON.parse(response);
		
		onSuccess(data);
	}).fail(function(error) {
		// log error
		console.log("ajax.static.php", error);
	});
}

function addSuccessMessage(text) {
	if (text === undefined) {
		text = "The changes have been saved successfully";
	}
	
	return "&messageType=success&messageText=" + encodeURIComponent(text);
}

/**
	basic initialization for a datepicker
*/
function initDatepicker() {
	$(".date-picker")
		.datepicker(DATEPICKER_INIT_OPTIONS)
		.on("changeDate", function(e) {
			// changeDate fires when month or year selection of datepicker is clicked, so we have to check if the user actually selected a new date
			if (e.viewMode === "days") {
				// hide datepicker after date was changed
				$(this).datepicker("hide");
			}
		});
}

/**
	basic initialization for select picker
*/
function initSelectpicker() {
	$(".selectpicker").selectpicker( {} );
}

/**
	control for played administration song input
*/
function addPlayedAdminSongInputControl() {
	var songDisplay = $("#played-admin-song-display");
	var songInput = $("#played-admin-song-id");
	var songInputContainer = $("#played-admin-song-input");
	
	// add auto complete
	var playedSongOptions = {
		url: "search.php",
		id: "played-admin-song-id",
		categories: ["songs"],
		itemDisplay: function(_category, _item, _choiceClass) {
			switch (_category) {
				case "songs":
					return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.SongId + "' data-artist=\"" + _item.ArtistName + "\" data-song=\"" + _item.SongName + "\" data-record=\"" + _item.RecordName + "\">" +
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
			// set song id in hidden input element
			$("#song-id").val(elem.dataset.id);
			
			switch (elem.dataset.category) {
				case "songs":
					$("#played-admin-song-ac-result").hide();
					songInputContainer.hide();
					songDisplay.html(	"<div>" +
											"<div>" + elem.dataset.song + "</div>" + 
											"<div>" + elem.dataset.artist + "</div>" + 
											"<div>" + elem.dataset.record + "</div>" +
										"</div>")
								 .show();
					break;
					
				default:
					break;
			}
			
			// hide result divs
			$(".ac_result").empty();
		}
	};
	
	// init autocomplete
	var playedAdminAC = new AutoComplete();
	playedAdminAC.init(playedSongOptions);
	
	songDisplay.on("click", function() {
		// toggle visibility
		songDisplay.toggle();
		songInputContainer.toggle();
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
				// success action
				switch (content.onSuccess) {
					case "updateSettings":
						updateSettingsContent(_tab);
						break;
						
					case "updateAdministration":
						updateAdministrationContent(_tab);
						break;
						
					case "updateRecordInformation":
						updateRecordInformation(_id);
						break;
						
					case "updateArtistInformation":
						updateArtistInformation(_id);
						break;
						
					case "savedSong":
						window.location.href = "song.php?id=" + content.SongId + addSuccessMessage();
						break;
						
					case "savedArtist":
						window.location.href = "artist.php?id=" + content.ArtistId + addSuccessMessage();
						break;
						
					case "savedRecord":
						window.location.href = "record.php?id=" + content.RecordId + addSuccessMessage();
						break;
						
					case "savedPlayed":
						updatePlayedOnDate();
						break;
						
					default:
						break;
				}
				
				// show success message
				globalNotify("Changes saved successfully");
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
	Update the content for the specified tab.
*/
function updateAdministrationContent(target) {
	$.ajax( {
		method: "GET",
		url: "ajax.administration.php",
		data: {
			action: "tab",
			id: target
		}
	}).done(function(data) {
		$("#administration-content").fadeOut(400, function() {
			// set content
			$(this).html(data).fadeIn(400);
		});
	}).fail(function(error) {
		// log error
		console.log("ajax.administration.php", error);
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
	Updates the detail information for a record.
*/
function updateArtistInformation(_id) {
	$.ajax( {
		method: "GET",
		url: "ajax.modal.php",
		data: {
			action: "v8g8frcj",
			id: _id
		}
	}).done(function(data) {
		var artist = JSON.parse(data);
		
		if (artist.success) {
			$("#artist-main-country-flag").html(artist.main_country);
			$("#artist-secondary-country-flag").html(artist.secondary_country);
			
			addTooltips();
		}
	}).fail(function(error) {
		// log error
		console.log("ajax.settings.php", error);
	});
}

/**
	Updates the played information for the currently selected date.
*/
function updatePlayedOnDate() {
	// clear result div
	$("#admin-search-played_result").empty();
	
	// get playeds for the currently selected date
	getPlayedForDateAjax($("#played-administration-date").val());
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
	
	// add country overview modal on click
	$(".flag-icon").off("click");
	$(".flag-icon").on("click", function() {
		crudModal("olfOmquv", this.dataset.id);
	});
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

function performMMLinkSafeCheck(elem, _parent_id, _child_id) {
	// check if button has danger class
	if ($(elem).hasClass("btn-danger")) {
		swal({
		  title: "MediaMonkey link connection",
		  text: "Are you sure that this link connection is correct?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Yes, link it!",
		  closeOnConfirm: false
		},
		function(){
		  addMMLinkConnection(_parent_id, _child_id);
		});
	} else {
		addMMLinkConnection(_parent_id, _child_id);
	}
}

function addMMLinkConnection(_parent_id, _child_id) {
	var modal = $("#music-modal");
	
	if (modal.length > 0) {
		// start process as soon as modal is hidden
		modal.on("hidden.bs.modal", function() {
			swal("MediaMonkey link connection", "Link connection is being set...", "success");
	
			var _data = {
				parent_id: _parent_id,
				child_id: _child_id
			};
			
			$.ajax( {
				method: "POST",
				url: "ajax.db.php",
				data: {
					action: "add_mm_link",
					data: JSON.stringify(_data)
				}
			}).done(function(resp) {
				var response = JSON.parse(resp);
				
				if (response.success) {
					// go to new song page
					window.location.href = "song.php?id=" + _parent_id + addSuccessMessage();
				} else {
					console.log("Error", response.message);
					globalNotify("MediaMonkey link connection could not be established", "error");
				}
			}).fail(function(error) {
				// log error
				console.log("ajax.db.php", error);
			});
			
			modal.off("hidden.bs.modal");
		});
		
		// hide modal
		modal.modal("hide");
	}
}

/**
	Adds input control to a played song div.
*/
function addPlayedSongInputControl(_id) {
	$("#" + _id + "-container .add-played-song-display").on("dblclick", function() {
		// hide display div and delete the saved values
		$(this).hide().empty();
		
		$("#" + _id + "-container .add-played-song-input").show();
	});
}

/**
	Adds a new add played div to the add played administration tab.
*/
function addNewAddPlayedDiv() {
	getStatic("add_played_song_add", function(data) {
		if ($(".add-played-song-div").length > 0) {
			// append the new div after the last div
			$(".add-played-song-div").last().after(data.content);
		} else {
			// if all divs have been deleted, append it to the form instead
			$("#add-played-song-form").append(data.content);
		}
		
		// auto complete for song adding
		var addSongAC = new AutoComplete();
		
		// add id to static options array
		ADD_SONG_AC_OPTIONS["id"] = data.id;
		
		addSongAC.init(ADD_SONG_AC_OPTIONS);
		
		// add onclick for editing the entered song
		addPlayedSongInputControl(data.id);
		
		// set focus to the last song text input field
		$(".add-played-song-time").last().focus();
	});
}

/**
	Updates the content of the update page.
*/
function updateUpdateContent() {
	getStatic("update", function(data) {
		$("#update-container").html(data.content);
		
		addPerformUpdateClickHandler();
	});
}

/**
	Applies the update files to the database.
*/
function performUpdate() {
	// hide button to avoid multiple executions
	$(this).remove();
	
	// stop refreshing update page
	window.clearInterval(updateContentTimer);
	
	// call update AJAX
	$.ajax( {
		method: "POST",
		url: "ajax.update_perform.php",
		data: {
			action: "update_database"
		}
	}).done(function(resp) {
		var response = JSON.parse(resp);
		
		if (response.success) {
			// overview data object
			var overview_data = {
				suggestions: response.suggestions,
				added: response.added,
				updated: response.updated
			};
			
			// redirect to update result overview page
			$.redirect("update_overview.php?" + addSuccessMessage("Database update was successful"), { data: JSON.stringify(overview_data) }, "POST");
		} else {
			globalNotify("Update not successful", "error");
			console.log("ajax.update_perform.php", response.message);
		}
	}).fail(function(error) {
		// log error
		console.log("ajax.update_perform.php", error);
	});
}

function addPerformUpdateClickHandler() {
	$("#perform-update").off("click");
	$("#perform-update").on("click", performUpdate);
}

function initAddPlayedSongAdministration() {
	if ($("#add-played-song-1").length > 0) {
		var addPlayedSongAdd = function() {
			$("#add-played-song-add").on("click", addNewAddPlayedDiv);
		}
		
		// save played song entries
		if ($("#add-played-song-save").length > 0) {
			$("#add-played-song-save").on("click", function() {
				// check if there are songs to save
				if ($(".add-played-song-div").length > 0) {
					var first_element = $(".add-played-song-time").first();
					
					// check if time has been entered in the first field - otherwise fill it with current time
					if (!validateTime(first_element.val())) {
						first_element.val(getTimeString());
					}
					
					// build JSON object
					var add_songs_data = {};
					
					// add date
					add_songs_data["date"] = $("#played-date").val();
					
					// add device
					add_songs_data["device-id"] = $("#administration-device").val();
					
					// add activity
					add_songs_data["activity-id"] = $("#administration-activity").val();
					
					// add songs form data
					var songs_data = [];
					
					var data_ok = true;
					
					$.each($(".add-played-song-div"), function(i, item) {
						var _id = item.id.substr(0, item.id.length - 10);
						
						// get time
						var song_time = $("#" + item.id + " .add-played-song-time").first().val();
						
						// if time is not valid, use empty string instead
						if (!validateTime(song_time)) {
							song_time = "";
						}
						
						// get song id
						var song_id = $("#" + _id + "-song-id").val();
						
						if (song_id === undefined) {
							// if a song has no valid id, it is simply ignored
							// alternatively, it would be possible to set data_ok to false here - but in this case
							// it is not possible to submit the form if the user accidentally added a new song line
						} else {						
							var song = { time: song_time, id: song_id };
							
							songs_data.push(song);
						}
					});
					
					add_songs_data["songs"] = songs_data;
					
					if (data_ok) {
						// send data via POST request
						$.ajax( {
							method: "POST",
							url: "ajax.db.php",
							data: {
								action: "add_songs_data",
								data: JSON.stringify(add_songs_data)
							}
						}).done(function(resp) {
							var response = JSON.parse(resp);
							
							if (response.success) {
								resetAddPlayed();
								
								globalNotify("Played songs were saved successfully");
							} else {
								console.log("Error", response.message);
								globalNotify("Played songs could not be saved", "error");
							}
						}).fail(function(error) {
							// log error
							console.log("ajax.db.php", error);
						});
					} else {
						globalNotify("There are errors in the played song form", "error");
					}
					
					
				} else {
					globalNotify("No songs to save", "info");
				}
			});
		}
		
		// add input control
		addPlayedSongInputControl();
		
		// immediately add the function to the already existing element
		addPlayedSongAdd();
		
		// add the container click function to the pre-existing add div
		addPlayedSongInputControl("add-played-song-1");
		
		// set the focus to this element if it's visible
		if ($("#add-played-song-1").length > 0) {
			$("#add-played-song-1").focus();
		}
		
		// ... and add the auto complete handler to it as well
		var addSongACFirst = new AutoComplete();
		
		ADD_SONG_AC_OPTIONS["id"] = "add-played-song-1";
		
		addSongACFirst.init(ADD_SONG_AC_OPTIONS);
	}
}

/**
	Resets the add played tab.
	The date, device and activity remain untouched.
*/
function resetAddPlayed() {
	// remove all add-played-song-divs
	$(".add-played-song-div").remove();
	
	// add a new one
	addNewAddPlayedDiv();
}

/**
	Gets the played data for the date.
*/
function getPlayedForDateAjax(date) {
	// result div
	var resultDiv = $("#admin-search-played_result");
	
	$.ajax( {
		method: "POST",
		url: "ajax.db.php",
		data: {
			action: "played_date",
			data: JSON.stringify({ date: date })
		}
	}).done(function(resp) {
		var response = JSON.parse(resp);
		
		if (response.success) {
			// empty result div
			resultDiv.empty();
			
			if (response.playeds) {
				// display played data
				$.each(response.playeds, function(i, item) {
					resultDiv.append(getPlayedAdministrationDiv(item));
				});
				
				// add tooltips
				addTooltips();
			} else {
				resultDiv.append("Unfortunately, there were no songs played on this date.");
			}
		} else {
			console.log("Error", response.message);
			globalNotify("Failed to fetch played data", "error");
		}
	}).fail(function(error) {
		// log error
		console.log("ajax.db.php", error);
	});
}

function getPlayedAdministrationDiv(played) {
	var html = "";
	
	html += "<div class='as_choice' data-id='" + played.PlayedId+ "'>" +
				"<div class='pull-right'><button type='button' class='btn btn-primary' onclick=\"crudModal('6I6T4dfW', '" + played.PlayedId + "', 'date=' + $('#played-administration-date').val())\"><span class='glyphicon glyphicon-pencil'></span></button></div>" +
				
				"<div class='played-admin-played-time'>" +
					"<span class='played-admin-time'>" + getFormattedTimestamp(played.UnixTimestamp) + "</span>" +
				"</div>" +
				
				"<div class='played-admin-song-info'>" + 
					"<div class='search_artist_name'>" + played.ArtistName + "</div>" +
					"<div>" + played.SongName + "</div>" +
					"<div class='search_record_name'>" + played.RecordName + "</div>" +
				"</div>" +
				
				"<div class='played-admin-played-details'>" +
					"<div class='played-admin-device'>" + played.Device + "</div>" + 
					"<div class='played-admin-activity'>" + played.Activity + "</div>" +
				"</div>" +
			"</div>";
	
	return html;
}

function initCharts() {
	// charts compilation buttons
	$(".btn-chart").on("click", function() {
		// hide button to avoid multiple execution
		$(this).hide();
		
		// show next paragraph with the info class (loading indicator)
		var loadingIndicator = $(this).next("p.loading-static");
		
		loadingIndicator.css("display", "inline-block");
		
		// get parameters
		var _params = this.id.split("-");
		
		var chart_type = _params[2];
		var chart_year = _params.length > 3 ? _params[3] : 0;
		
		// build data request object
		var _data = {
			chart_type: chart_type,
			year: chart_year
		};
		
		// AJAX request
		$.ajax( {
			method: "POST",
			url: "ajax.db.php",
			data: {
				action: "charts_compilation",
				data: JSON.stringify(_data)
			}
		}).done(function(resp) {
			var response = JSON.parse(resp);
			
			if (response.success) {
				// remove loading indicator
				loadingIndicator.remove();
				
				// update status message
				var status_id = "charts-compilation-status-" + chart_type;
				status_id += chart_year > 0 ? "-" + chart_year : "";
				
				$("#" + status_id).html(response.message);
				
				globalNotify("Charts compilation finished successfully");
			} else {
				console.log("Error", response.message);
				globalNotify("Charts compilation failed", "error");
			}
		}).fail(function(error) {
			// log error
			console.log("ajax.db.php", error);
		});
	});
}

/**
	Compiles the charts and updates the content of the chart administration div.
*/
function compileCharts() {
	
}

function switchToAddPlayedTab() {
	// set date correct for add played tab
	getNewSettingsTabContent("add-played", $("#played-administration-date").val());	
}

function getNewSettingsTabContent(target, params) {
	// get content
	$.ajax( {
		method: "GET",
		url: "ajax.administration.php",
		data: {
			action: "tab",
			id: target,
			params: params
		}
	}).done(function(data) {
		// special case: jump to add-played
		if (target == "add-played") {
			$("#administration a").removeClass("active");
			$("#administration-add-played").addClass("active");
		}
		
		// set content
		$("#administration-content").html(data);
		
		// add tooltips
		addTooltips();
		
		// init selects
		initSelectpicker();
		
		// init datepicker
		initDatepicker();
		
		// if tab is add played song, init add played song administration
		initAddPlayedSongAdministration();
		
		// played administration datepicker
		if ($("#played-administration-date").length > 0) {
			// switch to add played tab button
			$("#played-admin-add-played").on("click", function() {
				switchToAddPlayedTab();
			});
			
			// get data for default date
			getPlayedForDateAjax($("#played-administration-date").val());
			
			$("#played-administration-date").on("changeDate", function(e) {
				// changeDate fires when month or year selection of datepicker is clicked, so we have to check if the user actually selected a new date
				if (e.viewMode === "days") {
					// hide datepicker after date was changed
					$(this).datepicker("hide");
					getPlayedForDateAjax($(this).val());
				}
			});
		}
		
		// charts initialization
		if ($("#charts-container").length > 0) {
			initCharts();
		}
		
		// init admin search fields
		$(".admin-search").each(function(idx, item) {
			// category is at position 13
			var _cat = item.id.substring(13) + "s";
			
			var adminSearch = new AdminSearch();
			
			adminSearchOptions = {
				id: item.id,
				url: "search.php",
				categories: [_cat],
				itemDisplay: function(_category, _item, _choiceClass) {
					switch (_category) {
						case "songs":
							return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.SongId + "' data-artist='" + _item.ArtistName + "' data-song='" + _item.SongName + "' data-record='" + _item.RecordName + "'>" +
										"<div class='admin-search-edit pull-right'><button type='button' class='btn btn-primary' onclick=\"crudModal('57bB21kN', '" + _item.SongId + "')\"><span class='glyphicon glyphicon-pencil'></button></div>" + 
										"<div class='search_artist_name'>" + _item.ArtistName + "</div>" +
										"<div>" + _item.SongName + "</div>" +
										"<div class='search_record_name'>" + _item.RecordName + "</div>" +
									"</div>";
							
							break;
							
						case "artists":
							return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.ArtistId + "'>" +
										"<div class='admin-search-edit pull-right'><button type='button' class='btn btn-primary' onclick=\"crudModal('YTYrcS79', '" + _item.ArtistId + "')\"><span class='glyphicon glyphicon-pencil'></button></div>" + 
										"<div>" + _item.ArtistName + "</div>" +
									"</div>";
							
							break;
							
						case "records":
							return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.ArtistId + "'>" +
										"<div class='admin-search-edit pull-right'><button type='button' class='btn btn-primary' onclick=\"crudModal('uXQMGi1b', '" + _item.RecordId + "')\"><span class='glyphicon glyphicon-pencil'></button></div>" + 
										"<div class='search_artist_name'>" + _item.ArtistName + "</div>" +
										"<div>" + _item.RecordName + "</div>" +
									"</div>";
							
							break;
							
						default:
							return "";
							break;
					}
				}
			};
			
			adminSearch.init(adminSearchOptions);
		});
		
		// assign focus to autofocus element and set cursor to the end of the input element
		$("#administration-content .autofocus").first().focus().putCursorAtEnd();
	}).fail(function(error) {
		// log error
		console.log("ajax.administration.php", error);
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
					
				case "artists":
					return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.ArtistId + "'>" +
								"<div>" + _item.ArtistName + "</div>" +
							"</div>";
							
				case "records":
					return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.RecordId + "'>" +
								"<div class='search_artist_name'>" + _item.ArtistName + "</div>" +
								"<div>" + _item.RecordName + "</div>" +
							"</div>";
					
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
					
				case "artists":
					window.location.href = "artist.php?id=" + elem.dataset.id;
					break;
					
				case "records":
					window.location.href = "record.php?id=" + elem.dataset.id;
					break;
					
				default:
					break;
			}
		}
	};
	
	searchAutoComplete.init(searchACOptions);
	
	// check for incoming message
	var messageType = getUrlParameter("messageType") || "success";
	var messageText = getUrlParameter("messageText");	
	if (messageText !== undefined) {
		globalNotify(decodeURIComponent(messageText), messageType);
	}
	
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
		
	// init perform update button
	var performUpdateButton = $("#perform-update");
	if (performUpdateButton.length > 0)
		addPerformUpdateClickHandler();

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
		
		// affix for settings nav always to be visible
		settings.on("affix.bs.affix", function() {
			// a small hack to contain the width of the settings nav div
			var settingsWidth = settings.innerWidth();
			
			settings.on("affixed.bs.affix", function() {
				settings.css("width", settingsWidth + "px");
				
				// remove the listener immediately so we don't attach it over and over again if we scroll up and down
				settings.off("affixed.bs.affix");
			});
		});
	}
	
	// administration
	var administration = $("#administration");
	if (administration.length > 0) {
		// list navigation (load content of clicked tab via AJAX)
		$("#administration a").on("click", function(e) {
			// remove active class
			removeAdministrationActive();
			
			// get target
			var target = this.id.substring(+this.id.indexOf("-") + 1);
			
			// mark new active
			$(this).addClass("active");
			
			// get new tab content
			getNewSettingsTabContent(target);
		});
		
		// affix for nav always to be visible
		administration.on("affix.bs.affix", function() {
			// a small hack to contain the width of the settings nav div
			var administrationWidth = administration.innerWidth();
			
			administration.on("affixed.bs.affix", function() {
				administration.css("width", administrationWidth + "px");
				
				// remove the listener immediately so we don't attach it over and over again if we scroll up and down
				administration.off("affixed.bs.affix");
			});
		});
		
		// if tab is add played song, init add played song administration
		initAddPlayedSongAdministration();
	}
	
	var removeSettingsActive = function() {
		$("#settings a").removeClass("active");
	}
	
	// custom range statistics
	var custom_statistics = $("#custom-statistics-result");
	if (custom_statistics.length > 0) {
		// calculate button
		$("#custom-range-calculate").on("click", function() {
			_data = {
				from: $("#custom-statistics-start-date").val(),
				to: $("#custom-statistics-end-date").val()
			};
			
			$.ajax( {
				method: "POST",
				url: "ajax.db.php",
				data: {
					action: "custom_range_statistics",
					data: JSON.stringify(_data)
				}
			}).done(function(resp) {
				var response = JSON.parse(resp);
				
				if (response.success) {
					// set content
					custom_statistics.html(response.content);
					
					// add tooltips
					addTooltips();
				} else {
					console.log("Error", response.message);
					globalNotify("Error getting custom range statistics content", "error");
				}
			}).fail(function(error) {
				// log error
				console.log("ajax.db.php", error);
			});
		});
	}
	
	// calendarial charts
	var calendarial = $("#calendarial-accordion");
	if (calendarial.length > 0) {
		// accordion items
		$(".calendarial-item").on("click", function() {

			var _type = $(this).attr("data-type");
			var _year = $(this).attr("data-year");
			var _month = $(this).attr("data-month") ? $(this).attr("data-month") : 0;
			
			// data object
			var _data = {
				type: _type,
				year: _year,
				month: _month
			}
			
			$.ajax( {
				method: "POST",
				url: "ajax.db.php",
				data: {
					action: "calendarial_charts",
					data: JSON.stringify(_data)
				}
			}).done(function(resp) {
				var response = JSON.parse(resp);
				
				if (response.success) {
					// set content
					$("#calendarial-content").html(response.content);
					
					// add tooltips
					addTooltips();
				} else {
					console.log("Error", response.message);
					globalNotify("Error getting charts content", "error");
				}
			}).fail(function(error) {
				// log error
				console.log("ajax.db.php", error);
			});
			
			// hide open accordion elements
			$("a.calendarial-item[aria-expanded='true']").each(function(i, item) {
				if (item.dataset.year != _year)
					$("#calendarial-" + item.dataset.year).collapse("hide");
			});
		});
		
		// affix for accordion nav always to be visible
		calendarial.on("affix.bs.affix", function() {
			// a small hack to contain the width of the accordion nav div
			var calendarialWidth = calendarial.innerWidth();
			
			calendarial.on("affixed.bs.affix", function() {
				calendarial.css("width", calendarialWidth + "px");
				
				// remove the listener immediately so we don't attach it over and over again if we scroll up and down
				calendarial.off("affixed.bs.affix");
			});
		});
	}
	
	var removeAdministrationActive = function() {
		$("#administration a").removeClass("active");
	}
	
	// update page content update timer
	if ($("#update-container").length > 0) {
		updateContentTimer = window.setInterval(updateUpdateContent, 3000);
	}
	
	// add tooltips
	addTooltips();
	
	// init datepicker
	initDatepicker();
	
	// add hotkey listener
	$(document).bind("keydown", "alt+f", function() {
		// set focus to search field
		$("#search-field").focus();
		
		// catch event to prevent it from bubbling to the browser
		return false;
	});
} );