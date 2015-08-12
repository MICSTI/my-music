<?php
	$response = array();
	
	$status = "";
	$message = "";

	if ($_POST) {
		$xml_data = stripslashes(urldecode($_POST['xmldata']));
		
		if ($xml_data <> "") {
			//file_put_contents( "mobile." . mktime() . ".xml", $mobile_played_str );
			
			$status = "success";
			$message = "Successful";
		}
	} else {
		$status = "error";
		$message = "No post data available";
	}
	
	$response["status"] = $status;
	$response["message"] = $message;
	
	echo json_encode($response);