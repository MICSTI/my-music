function AdminSearch() {
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
	var resultId;
	
	// result div
	var result;
	
	// timer to keep track if user is still typing in the text input field to avoid unnecessary AJAX calls
	var typingTimer;
	
	// the time in milliseconds until the AJAX call is executed
	var doneTypingInterval = 400;
	
	// helper variable to keep track which choice is currently selected (for navigation with arrow keys)
	var choice = -1;
	var choicePointer;
	
	// class names for AJAX request result div
	var categoryClass;
	var choiceClass;
	var choiceSelectedClass;
	
	// array containing the accepted categories (* if all categories should be returned)
	var acceptedCategories;
	
	// function to display a category item
	var categoryItem;
	
	// function to handle the selection of an item
	var onItemSelected;
	
	// last searched string (to avoid searching for the same term again if element gets focus again)
	var lastSearchTerm = "";
	
	/**
		Performs the initialization of the important elements.
		Selectors for parent and result elements as well as key listeners.
	*/
	this.init = function(options) {
		// if no parent id or url was passed, abort iinit
		if (options.id === undefined || options.url === undefined)
			return;
		
		// parent element id
		id = options.id;
		
		// update url
		url = options.url;
		
		// parent reference
		parent = $("#" + id);
		
		// result div id
		resultId = id + "_result";
		
		// class name for normal and selected choice
		resultClass = options.classResult || "as_result";
		categoryClass = options.classCategory || "as_category";
		choiceClass = options.classChoice || "as_choice";
		choiceSelectedClass = options.classChoiceSelected || "as_choice_selected";
		
		// accepted categories
		acceptedCategories = options.categories || ["*"];
		
		// get reference for result div and hide it
		result = $("#" + resultId);
		
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
		
		// function to display items
		categoryItem = options.itemDisplay;
	}
	
	// perform keyhandler method to get updates and move through auto complete choices
	var doneTyping = function(event) {
		keyHandler(event);
	}
	
	/**
		Performs an AJAX call to retrieve the possible choices for the search term.
	*/
	var getUpdate = function() {
		var searchTerm = parent.val();
		
		if (searchTerm != lastSearchTerm) {
			searchedTerm = searchTerm;
			
			$.ajax({
				method: "GET",
				url: url,
				data: {
					search: searchedTerm,
					categories: acceptedCategories.join(",")
				}
			})
			 .done( function (msg) { 
				// parse json
				json = JSON.parse(msg);
				
				if (json.data) {								
					// set content of div
					result.html(getHtml());
				}
				
				lastSearchTerm = searchedTerm;
			 })
			 .fail( function (error) {
				 console.log("AJAX search error", error);
			 });
		}
	}
	
	/**
		Handles the key input from the user.
	*/
	var keyHandler = function(keyStroke) {
		getUpdate();
	}
	
	/**
		Returns the HTML for the result div.
	*/
	var getHtml = function() {
		var html = "";
		
		var count = 1;
		
		$.each(json.data, function (category, elements) {
			if (elements.length > 0) {
				$.each(elements, function (i, item) {
					html += categoryItem(category, item, choiceClass);
					count++;
				});
			}
		});
		
		return html;
	}
}