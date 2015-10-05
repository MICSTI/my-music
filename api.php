<?php
	include('resources.php');
	
	$KEY_CHART_TYPE = "chart_type";
	$KEY_TOP2020 = "top2020";
	$KEY_FAVOURITES = "favourites";
	$KEY_SONGS = "songs";
	$KEY_ARTISTS = "artists";
	
	$KEY_COMPILE_TIMESTAMP = "compile_timestamp";
	
	// return type (text / json)
	$type = "";
	
	// return string
	$return = "";
	
	
	if ($_GET) {
		$key = isset($_GET['key']) ? trim($_GET['key']) : "";
		
		switch ($key) {
			case "mobile_db_mod":
				$type = "text";
				$return = $mc->getMDB()->getConfig('mm_db_modification');
				
				break;
				
			case "top2020":
				$type = "json";
				
				// get chart info
				$chart_info = $mc->getMDB()->getChartInfo("top2020");
				$chart_id = $chart_info["ChartId"];
				
				$songs = $mc->getMDB()->getChartsContentSongs($chart_id);
				$artists = $mc->getMDB()->getChartsContentArtists($chart_id);
				
				// get ready-made objects for json
				$songs_object = $mc->getChartsObject("songs", $songs, true);
				$artists_object = $mc->getChartsObject("artists", $artists, true);
				
				$stats = array();
				
				// chart type
				$stats[$KEY_CHART_TYPE] = $KEY_TOP2020;
				
				// compile timestamp
				$stats[$KEY_COMPILE_TIMESTAMP] = $chart_info["ChartCompileTimestamp"];
				
				// songs
				$stats[$KEY_SONGS] = $songs_object;
				
				// artists
				$stats[$KEY_ARTISTS] = $artists_object;
				
				$return = $stats;
				
				break;
				
			case "favourites":
				$type = "json";
				
				// get chart info
				$chart_info = $mc->getMDB()->getChartInfo("favourites");
				$chart_id = $chart_info["ChartId"];
				
				$songs = $mc->getMDB()->getChartsContentSongs($chart_id);
				$artists = $mc->getMDB()->getChartsContentArtists($chart_id);
				
				// get ready-made objects for json
				$songs_object = $mc->getChartsObject("songs", $songs);
				$artists_object = $mc->getChartsObject("artists", $artists);
				
				$stats = array();
				
				// chart type
				$stats[$KEY_CHART_TYPE] = $KEY_FAVOURITES;
				
				// compile timestamp
				$stats[$KEY_COMPILE_TIMESTAMP] = $chart_info["ChartCompileTimestamp"];
				
				// songs
				$stats[$KEY_SONGS] = $songs_object;
				
				// artists
				$stats[$KEY_ARTISTS] = $artists_object;
				
				$return = $stats;
				
				break;
			
			default:
				break;
		}
	}
		
	switch ($type) {
		case "json":
			echo json_encode($return);
			break;
			
		default:
			echo $return;
			break;
	}