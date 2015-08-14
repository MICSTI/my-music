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
				return "<div class='" + _choiceClass + "' data-category='" + _category + "' data-id='" + _item.SongId + "' data-artist='" + _item.ArtistName + "' data-song='" + _item.SongName + "' data-record='" + _item.RecordName + "'>" +
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
	});

	// fites when modal has completely finished loading
	modal.on("shown.bs.modal", function() {
		// assign focus to autofocus element and set cursor to the end of the input element
		$(".autofocus").first().focus().putCursorAtEnd();
		
		// init datepicker
		initDatepicker();
		
		// add tooltips
		addTooltips();
	});
	
	// fires as the modal is being hidden
	modal.on("hidden.bs.modal", function() {
		// close all datepickers on this modal
		$(".modal-dialog .date-picker").datepicker("hide");
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

function addSuccessMessage() {
	return "&messageType=success&messageText=" + encodeURIComponent("The changes have been saved successfully");
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
						window.location.href="song.php?id=" + content.SongId + addSuccessMessage();
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
		});
		
		// hide modal
		modal.modal("hide");
	}
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
			
			// get content
			$.ajax( {
				method: "GET",
				url: "ajax.administration.php",
				data: {
					action: "tab",
					id: target
				}
			}).done(function(data) {
				// set content
				$("#administration-content").html(data);
				
				// add tooltips
				addTooltips();
				
				// init selects
				initSelectpicker();
				
				// init datepicker
				initDatepicker();
				
				// init admin search fields
				$(".admin-search").each(function(idx, item) {
					var adminSearch = new AdminSearch();
					
					adminSearchOptions = {
						id: item.id,
						url: "search.php",
						categories: ["songs"],
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
		});
		
		// affix for nav always to be visible
		administration.on("affix.bs.affix", function() {
			// a small hack to contain the width of the settings nav div
			var administrationWidth = administration.innerWidth();
			
			administration.on("affixed.bs.affix", function() {
				administration.css("width", settingsWidth + "px");
				
				// remove the listener immediately so we don't attach it over and over again if we scroll up and down
				administration.off("affixed.bs.affix");
			});
		});
		
		var addPlayedSongAdd = function() {
			$("#add-played-song-add").on("click", function() {
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
			});
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
							data_ok = false;
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
		
		var addPlayedSongInputControl = function(_id) {
			$("#" + _id + "-container .add-played-song-display").on("dblclick", function() {
				// hide display div and delete the saved values
				$(this).hide().empty();
				
				$("#" + _id + "-container .add-played-song-input").show();
			});
		}
		
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
	
	var removeSettingsActive = function() {
		$("#settings a").removeClass("active");
	}
	
	var removeAdministrationActive = function() {
		$("#administration a").removeClass("active");
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