<?php
	if ($_POST) {
		$mobile_played_str = stripslashes(urldecode($_POST['mobile_played']));
		
		if ($mobile_played_str <> "") {
			file_put_contents( "mobile." . mktime() . ".xml", $mobile_played_str );
		}
	}