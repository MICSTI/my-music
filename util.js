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