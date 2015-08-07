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
		resultClass = options.classResult || "ac_result";
		categoryClass = options.classCategory || "ac_category";
		choiceClass = options.classChoice || "ac_choice";
		choiceSelectedClass = options.classChoiceSelected || "ac_choice_selected";
		
		// accepted categories
		acceptedCategories = options.categories || ["*"];
		
		// add hidden result div
		parent.after("<div class='" + resultClass + "' id='" + resultId + "'></div>");
		
		// get reference for result div and hide it
		result = $("#" + resultId);
		result.hide();
		
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
		
		// on selected handler
		onItemSelected = options.itemSelection;
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
				search: parent.val(),
				categories: acceptedCategories.join(",")
			}
		})
		 .done( function (msg) { 
			// parse json
			json = JSON.parse(msg);
			
			if (json.data) {								
				// clear choice pointer
				choicePointer = undefined;
			
				// set content of div
				result.html(getHtml());
				
				// attach on click events for choices
				$("." + choiceClass).on("click", function() {
					selectChoice(this);
				});
				
				// make choice divs as wide as parent for good looks
				result.css("width", parent.innerWidth() + "px");
				
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
					
					// is a choice selected?
					if (choicePointer !== undefined) {
						// yes, so select it
						selectChoice(choicePointer[0]);
					} else {
						// nope, so we check how many choices there are currently
						var currentChoices = $("." + choiceClass);
						
						if (currentChoices.length == 1) {
							// if there's only one, go select that one
							selectChoice(currentChoices.get(0));
						}
					}
	
					break;
					
				// Escape
				case 27:
					removeResult();
					break;
					
				// Arrow up
				case 38:
					if (choicePointer === undefined) {
						// select last choice
						choicePointer = $("." + choiceClass).last();
						choicePointer.toggleClass(choiceSelectedClass);
					} else {
						// remove active from previous choice
						choicePointer.toggleClass(choiceSelectedClass);
						
						if (choicePointer.prev().length > 0) {
							// what class has the previous element?
							if (choicePointer.prev().hasClass(choiceClass)) {
								choicePointer = choicePointer.prev();
							} else {
								// is it a category?
								if (choicePointer.prev().hasClass(categoryClass)) {
									// is the previous previous element a choice?
									if (choicePointer.prev().prev().hasClass(choiceClass)) {
										choicePointer = choicePointer.prev().prev();
									} else {
										// otherwise jump back to the last choice
										choicePointer = $("." + choiceClass).last();
									}
								} else {
									// nope, so we're at the very top and go to the bottom
									choicePointer = $("." + choiceClass).last();
								}
							}
						} else {
							// otherwise jump back to the last choice
							choicePointer = $("." + choiceClass).last();
						}
						
						// add active to new choice
						choicePointer.toggleClass(choiceSelectedClass);
					}
				
					break;
					
				// Arrow down
				case 40:
					if (choicePointer === undefined) {
						// select first choice
						choicePointer = $("." + choiceClass).first();
						choicePointer.toggleClass(choiceSelectedClass);
					} else {
						// remove active from previous choice
						choicePointer.toggleClass(choiceSelectedClass);
						
						if (choicePointer.next().length > 0) {
							// what class has the next element?
							if (choicePointer.next().hasClass(choiceClass)) {
								choicePointer = choicePointer.next();
							} else {
								// is it a category?
								if (choicePointer.next().hasClass(categoryClass)) {
									// is the next next element a choice?
									if (choicePointer.next().next().hasClass(choiceClass)) {
										choicePointer = choicePointer.next().next();
									} else {
										// otherwise jump back to the first choice
										choicePointer = $("." + choiceClass).first();
									}
								} else {
									// nope, so we're at the very bottom and go to the top
									choicePointer = $("." + choiceClass).first();
								}
							}
						} else {
							// otherwise jump back to the first choice
							choicePointer = $("." + choiceClass).first();
						}
						
						// add active to new choice
						choicePointer.toggleClass(choiceSelectedClass);
					}
					
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
		
		var count = 1;
		
		$.each(json.data, function (category, elements) {
			// category (displayed only if there are elements in it)
			if (elements.length > 0) {
				html += "<div class='" + categoryClass + "'>" + category.capitalizeFirstLetter() + "</div>";
				
				// category elements
				$.each(elements, function (i, item) {
					html += categoryItem(category, item, choiceClass);
					count++;
				});
			}
		});
		
		return html;
	}
	
	/**
		Handles the selection of a choice.
	*/
	var selectChoice = function(elem) {
		// call on item selected handler
		onItemSelected(elem);
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