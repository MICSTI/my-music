function AutoComplete() {
	// reference to this for access in nested callbacks
	var self = this;
	
	// element id
	var id;
	
	// parent text field
	var parent;
	
	// update url
	var url;
	
	// JSON response
	var json;
	
	// result div id
	var result_id;
	
	// result div
	var result;
	
	// timer to keep track if user is still typing in the text input field to avoid unnecessary AJAX calls
	var typingTimer;
	
	// the time in milliseconds until the AJAX call is executed
	var doneTypingInterval = 200;
	
	// helper variable to keep track which choice is currently selected (for navigation with arrow keys)
	var choice = -1;
	
	// class names for AJAX request result div
	var choiceSelectId = "ac_r_";
	var choiceClass = "ac_r";
	
	this.setId = function(_id) {
		id = _id;
		
		// init jQuery selectors for parent and result div and bind key listeners
		init();
	}
	
	this.setUrl = function(_url) {
		url = _url;
	}
	
	/**
		Performs the initialization of the important elements.
		Selectors for parent and result elements as well as key listeners.
	*/
	var init = function() {
		result_id = id + "_result";
		parent = $("#" + id);
		
		// add hidden result div
		parent.after("<div id='" + result_id + "'></div>");
		
		// get reference for result div
		result = $("#" + result_id);
		result.hide();
		
		// position result div correctly
		result.css("position", "absolute")
			  .css("background-color", "teal");
			  
		$("." + choiceClass).css("width", parent.outerWidth + "px")
		
		// set keyup listener 
		parent.on("keyup", function(event) {
			// if input is enter or escape, up or down arrow, put it through immediately, otherwise wait some time until user has stopped typing
			if (event.which == 13 || event.which == 27 || event.which == 38 || event.which == 40) {
				doneTyping(event);
			} else {
				// stop the timeout
				clearTimeout(typingTimer);
				
				// start the timeout to see if user presses another key (we use a closure here so we can pass the event along to the doneTyping method)
				typingTimer = setTimeout( function() { return doneTyping(event); }, doneTypingInterval);
			}
		});
		
		// clear the timeout if the user presses a key down
		parent.on("keydown", function() {
			clearTimeout(typingTimer);
		});
		
		// set focus listener to get results if parent gets focus
		parent.on("focus", getUpdate);
	}
	
	// perform keyhandler method to get updates and move through auto complete choices
	var doneTyping = function(event) {
		keyHandler(event);
	}
	
	/**
		Performs an AJAX call to retrieve the possible choices for the search term.
	*/
	var getUpdate = function() {		
		$.ajax({
			method: "GET",
			url: url,
			data: {
				search: parent.val()
			}
		})
		 .done( function (msg) { 
			// parse json
			json = JSON.parse(msg);
			
			// result choices
			choice = -1;
			
			if (json.data && json.data.length > 0) {
				// set content of div
				result.html(getHtml());
				
				// add onclick listener for choices
				$("." + choiceClass).each(function (i, item) {
					$(this).on("click", function() { 
						choice = i;
						selectResult();
					});
				});
				
				// show result div
				result.show();
			} else {
				// hide and empty result div
				removeResult();
			}
			
		 })
		 .fail( function (error) {
			 console.log("AJAX search error", error);
		 });
	}
	
	/**
		Handles the key input from the user.
		Enter, escape, and arrow up and down are caught, for every other key the update function will be called.
	*/
	var keyHandler = function(keyStroke) {
		var text = parent.val();
		
		if (text != "") {
			switch (keyStroke.which) {
				// Enter
				case 13:
					keyStroke.preventDefault();
					selectResult();
					break;
					
				// Escape
				case 27:
					removeResult();
					break;
					
				// Arrow up
				case 38:
					// remove active class from old choice
					$("#" + choiceSelectId + choice).toggleClass("ac_active");
				
					choice--;
					
					if (choice < 0) {
						choice = json.data.length - 1;
					}
					
					// add active class to new choice
					$("#" + choiceSelectId + choice).toggleClass("ac_active");
				
					break;
					
				// Arrow down
				case 40:
					// remove active class from old choice
					$("#" + choiceSelectId + choice).toggleClass("ac_active");
				
					choice++;
					
					if (choice >= json.data.length) {
						choice = 0;
					}
					
					// add active class to new choice
					$("#" + choiceSelectId + choice).toggleClass("ac_active");
					
					break;
					
				default:
					getUpdate();
					break;
			}
		} else {
			// hide and empty result div
			removeResult();
		}
	}
	
	/**
		Returns the HTML for the result div.
	*/
	var getHtml = function() {
		var html = "";
		
		$.each(json.data, function (i, item) {
			html += "<div id='" + choiceSelectId + i + "' class='" + choiceClass + "'>" + item.SongName + "</div>";
		});
		
		return html;
	}
	
	/**
		Handles the selection of a choice.
	*/
	var selectResult = function() {
		alert("You selected " + json.data[choice]["SongName"] + " by " + json.data[choice]["ArtistName"]);
	}
	
	/**
		Hides the result div and resets the choice variable.
	*/
	var removeResult = function() {
		choice = -1;
		result.hide();
		result.html("");
	}
	
	this.selectChoice = function (i) {
		selectResult(i);
	}
}