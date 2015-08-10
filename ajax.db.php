<?php
	include('resources.php');
	
	$response = array();
	
	if ($_POST) {
		$action = isset($_POST['action']) ? trim($_POST['action']) : "";
		$data = isset($_POST['data']) ? trim($_POST['data']) : "";
		
		switch ($action) {
			// add songs data
			case "add_songs_data":
				$json_data = json_decode($data, true);
				
				// get info from json data
				$date = $json_data["date"];
				$device_id = $json_data["device-id"];
				$activity_id = $json_data["activity-id"];
				$songs = $json_data["songs"];
				
				// put info into database
				$success = $mc->getMDB()->addPlayedSongs($date, $device_id, $activity_id, $songs);
				
				$response["success"] = true;
				
				break;
				
			default:
				$response["success"] = false;
				$response["message"] = "Unknown action";
				break;
		}
	}
	
	echo json_encode($response);