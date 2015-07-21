<?php
	include('resources.php');
	
	// GET keys
	$GET_SEARCH_TEXT = "search";
	
	// JSON keys
	$KEY_STATUS = "status";
	$KEY_DATA = "data";
	$KEY_DESCRIPTION = "description";
	$KEY_QUERY = "query";
	
	// JSON results
	$RESULT_OK = "ok";
	$RESULT_ERROR = "error";
	
	$ERROR_NO_GET = "No GET parameters passed";
	$ERROR_WRONG_GET = "Wrong GET parameters passed";
	
	// Response array
	$json = array();
	
	if ($_GET) {
		$query = isset($_GET[$GET_SEARCH_TEXT]) ? trim($_GET[$GET_SEARCH_TEXT]) : false;
		
		if ($query !== false) {
			// array containing the search results
			$data = array();
			
			// check if query is not empty
			if (!empty($query)) {
				// distinguish between search modes
				// 1. one-word search (looks for occurence in the three main categories song titles, artist names and record names)
				// 	 a. for up to three letters only the beginning of a term is matched
				//	 b. from four letters up occurences within words are matched too
				// 2. multi-word search (does a grouped-query so e.g. the search "Bowie Ashes" will return the song "Ashes To Ashes" by David Bowie)
				
				// split search query into separate words
				$split_query = explode($query, " ");
			}
			
			$json[$KEY_STATUS] = $RESULT_OK;
			$json[$KEY_QUERY] = $query;
			$json[$KEY_DATA] = $data;
		} else {
			// wrong get parameters error
			$json[$KEY_STATUS] = $RESULT_ERROR;
			$json[$KEY_DESCRIPTION] = $ERROR_WRONG_GET;
		}
	} else {
		// no get parameters error
		$json[$KEY_STATUS] = $RESULT_ERROR;
		$json[$KEY_DESCRIPTION] = $ERROR_NO_GET;
	}
	
	echo json_encode($json);