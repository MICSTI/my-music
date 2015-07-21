<?php
	include('resources.php');
	
	// GET keys
	$GET_SEARCH_TEXT = "search";
	
	// JSON keys
	$KEY_STATUS = "status";
	$KEY_DATA = "data";
	$KEY_DESCRIPTION = "description";
	$KEY_QUERY = "query";
	
	$KEY_QUERY_TYPE = "type";
	$KEY_QUERY_TYPE_SINGLE = "single";
	$KEY_QUERY_TYPE_MULTI = "multi";
	
	// JSON results
	$RESULT_OK = "ok";
	$RESULT_ERROR = "error";
	
	$ERROR_NO_GET = "No GET parameters passed";
	$ERROR_WRONG_GET = "Wrong GET parameters passed";
	$ERROR_SQL_EXEC = "Error during SQL execution";
	
	// Response array
	$json = array();
	
	if ($_GET) {
		$query = isset($_GET[$GET_SEARCH_TEXT]) ? trim($_GET[$GET_SEARCH_TEXT]) : false;
		
		if ($query !== false) {			
			// check if query is not empty
			if (!empty($query)) {
				// distinguish between search modes
				// 1. single-word search (looks for occurence in the three main categories song titles, artist names and record names)
				// 	 a. for up to three letters only the beginning of a term is matched
				//	 b. from four letters up occurences within words are matched too
				// 2. multi-word search (does a grouped-query so e.g. the search "Bowie Ashes" will return the song "Ashes To Ashes" by David Bowie)
				
				// strip tags off query input
				$query = strip_tags($query);
				
				// split search query into separate words
				$split_query = explode(" ", $query);
				
				if (count($split_query) == 1) {
					// Case 1 (see above): single-word search
					$json[$KEY_QUERY_TYPE] = $KEY_QUERY_TYPE_SINGLE;
					
					$term = $split_query[0];
					
					if (strlen($term) <= 3) {
						// Case 1a (see above)
						$result = $mc->getMDB()->shortSingleSearch($term);
					} else {
						// Case 1b (see above)
						$result = $mc->getMDB()->longSingleSearch($term);
					}
				} else {
					// Case 2 (see above): multi-word search
					$json[$KEY_QUERY_TYPE] = $KEY_QUERY_TYPE_MULTI;
					
					$result = $mc->getMDB()->multiSearch($split_query);					
				}
				
				if ($result !== false) {
					// valid result
					$json[$KEY_STATUS] = $RESULT_OK;
					$json[$KEY_DATA] = $result;
				} else {
					// error during SQL execution
					$json[$KEY_STATUS] = $RESULT_ERROR;
					$json[$KEY_DESCRIPTION] = $ERROR_SQL_EXEC;
				}
			} else {
				// empty query, always returns ok with empty query
				$json[$KEY_STATUS] = $RESULT_OK;
				$json[$KEY_DATA] = array();
			}
			
			// add query to the JSON response for validation
			$json[$KEY_QUERY] = $query;
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