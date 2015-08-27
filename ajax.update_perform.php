<?php
	include('resources.php');
	
	$response = array();
	
	$message = "";

	if ($_POST) {
		$action = isset($_POST['action']) ? trim($_POST['action']) : "";
		
		switch ($action) {
			case "update_database":
				$status = $mc->updateDatabase();
				
				$response["suggestions"] = $status["suggestions"];
				$response["added"] = $status["added"];
				$response["updated"] = $status["updated"];
				
				$success = $status["success"];
				
				break;
				
			default:
				$success = false;
				$message = "Unknown action";
			
				break;
		}
	} else {
		$success = false;
		$message = "POST parameter not set";
	}
	
	$response["success"] = $success;
	$response["message"] = $message;
	
	echo json_encode($response);