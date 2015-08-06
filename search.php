<?php
	include('resources.php');
	
	// GET keys
	$GET_SEARCH_TEXT = "search";
	$GET_SEARCH_CATEGORIES = "categories";
	
	// JSON keys
	$KEY_STATUS = "status";
	$KEY_DATA = "data";
	$KEY_DATA_CATEGORIES = "categories";
	$KEY_DESCRIPTION = "description";
	$KEY_QUERY = "query";
	
	$KEY_CATEGORY_SONGS = "songs";
	$KEY_CATEGORY_ARTISTS = "artists";
	$KEY_CATEGORY_RECORDS = "records";
	
	$KEY_QUERY_TYPE = "type";
	$KEY_QUERY_TYPE_SINGLE = "single";
	$KEY_QUERY_TYPE_MULTI = "multi";
	
	// JSON results
	$RESULT_OK = "ok";
	$RESULT_ERROR = "error";
	
	$ERROR_NO_GET = "No GET parameters passed";
	$ERROR_WRONG_GET = "Wrong GET parameters passed";
	$ERROR_SQL_EXEC = "Error during SQL execution";
	
	// Category wildcard
	$CATEGORY_WILDCARD = "*";
	
	// Response array
	$json = array();
	
	if ($_GET) {
		$query = isset($_GET[$GET_SEARCH_TEXT]) ? trim($_GET[$GET_SEARCH_TEXT]) : false;
		$categories = isset($_GET[$GET_SEARCH_CATEGORIES]) ? trim($_GET[$GET_SEARCH_CATEGORIES]) : false;
		
		if ($query !== false) {			
			// check if query is not empty
			if (!empty($query)) {
				// distinguish between search modes
				// 1. single-word search (looks for occurence in the three main categories song titles, artist names and record names)
				// 	 a. for up to three letters only the beginning of a term is matched
				//	 b. from four letters up occurences within words are matched too
				// 2. multi-word search (does a grouped-query so e.g. the search "Bowie Ashes" will return the song "Ashes To Ashes" by David Bowie)
				
				// data array
				$data = array();
				
				// success flag
				$success = true;
				
				if ($categories === false) {
					// if no categories were passed, assume we want all categories
					$categories = $CATEGORY_WILDCARD;
				} else {
					// strip tags off categories input
					$categories = strip_tags($categories);
				}
				
				$category_array = explode(",", $categories);
				
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
						
						// songs category
						if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_SONGS, $category_array)) {
							$data[$KEY_CATEGORY_SONGS] = $mc->getMDB()->shortSingleSearch($term);
							
							if ($data[$KEY_CATEGORY_SONGS] === false) {
								$success = false;
							}
						}
						
						// artists category
						if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_ARTISTS, $category_array)) {
							// actual query!
							
							$data[$KEY_CATEGORY_ARTISTS] = array();
						}
						
						// records category
						if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_RECORDS, $category_array)) {
							// actual query!
							
							$data[$KEY_CATEGORY_RECORDS] = array();
						}
					} else {
						// Case 1b (see above)
						
						// songs category
						if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_SONGS, $category_array)) {
							$data[$KEY_CATEGORY_SONGS] = $mc->getMDB()->longSingleSearch($term);
							
							if ($data[$KEY_CATEGORY_SONGS] === false) {
								$success = false;
							}
						}
						
						// artists category
						if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_ARTISTS, $category_array)) {
							// actual query!
							
							$data[$KEY_CATEGORY_ARTISTS] = array();
						}
						
						// records category
						if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_RECORDS, $category_array)) {
							// actual query!
							
							$data[$KEY_CATEGORY_RECORDS] = array();
						}
					}
				} else {
					// Case 2 (see above): multi-word search
					$json[$KEY_QUERY_TYPE] = $KEY_QUERY_TYPE_MULTI;
					
					// songs category
					if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_SONGS, $category_array)) {
						$data[$KEY_CATEGORY_SONGS] = $mc->getMDB()->multiSearch($split_query);					
						
						if ($data[$KEY_CATEGORY_SONGS] === false) {
							$success = false;
						}
					}
					
					// artists category
					if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_ARTISTS, $category_array)) {
						// actual query!
						
						$data[$KEY_CATEGORY_ARTISTS] = array();
					}
					
					// records category
					if (in_array($CATEGORY_WILDCARD, $category_array) OR in_array($KEY_CATEGORY_RECORDS, $category_array)) {
						// actual query!
						
						$data[$KEY_CATEGORY_RECORDS] = array();
					}
				}
				
				// check if an error occurred
				if ($success) {
					// set valid result and data
					$json[$KEY_STATUS] = $RESULT_OK;
					$json[$KEY_DATA] = $data;
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