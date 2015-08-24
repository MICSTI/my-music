<?php
	include('resources.php');
	
	$response = array();

	if ($_POST) {
		$action = isset($_POST['action']) ? trim($_POST['action']) : "";
		
		switch ($action) {
			case "update_database":
				$success = $mc->updateDatabase();
				
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