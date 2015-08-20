/**
	Returns the value for the specified url parameter
*/
function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return sParameterName[1];
        }
    }
}

/**
	Capitalizes the first letter in a string
*/
if (!String.prototype.capitalizeFirstLetter) {
	String.prototype.capitalizeFirstLetter = function() {
		return this.charAt(0).toUpperCase() + this.slice(1);
	};
}

/**
	Checks if a string is a valid time between 00:00 and 23:59
*/
function validateTime(time) {
	return /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(time);
}

/**
	Returns a string containing the current time.
*/
function getTimeString() {
	var _date = new Date();
	
	return ("0" + _date.getHours()).slice(-2) + ":" + ("0" + _date.getMinutes()).slice(-2);
}

/**
	Returns an Austrian datetime string of the passed UNIX timestamp
*/
function getFormattedTimestamp(unix_timestamp) {
	// create a new javascript Date object based on the timestamp
	// multiplied by 1000 so that the argument is in milliseconds, not seconds
	var date = new Date(unix_timestamp * 1000);
	
	// hours part from the timestamp
	var hours = date.getHours();
	
	// minutes part from the timestamp
	var minutes = "0" + date.getMinutes();

	// will display time in 10:30:23 format
	var formattedTime = hours + ':' + minutes.substr(-2);
	
	return formattedTime;
}