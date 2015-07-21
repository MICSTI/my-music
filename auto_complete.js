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
	
	var choice = -1;
	var choiceSelectId = "ac_r_";
	var choiceClass = "ac_r";
	
	this.setId = function(_id) {
		id = _id;
		
		init();
	}
	
	this.setUrl = function(_url) {
		url = _url;
	}
	
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
		
		// set keyup listener to get updates and move through auto complete choices (we use a closure so we can pass the event to the key handler)
		parent.on("keyup", function(event) { return keyHandler(event); } );
		
		// set focus listener to get results if parent gets focus
		parent.on("focus", getUpdate);
		
		// set blur listener to hide result div if parent has lost focus
		parent.on("blur", removeResult);
	}
	
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
	
	var getHtml = function() {
		var html = "";
		
		$.each(json.data, function (i, item) {
			html += "<div id='" + choiceSelectId + i + "' class='" + choiceClass + "'>" + item.SongName + "</div>";
		});
		
		return html;
	}
	
	var selectResult = function() {
		alert("You selected " + json.data[choice]["SongName"] + " by " + json.data[choice]["ArtistName"]);
	}
	
	var removeResult = function() {
		choice = -1;
		result.hide();
		result.html("");
	}
}