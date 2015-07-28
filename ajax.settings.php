<?php
	include('resources.php');
	
	if ($_GET) {
		$action = isset($_GET['action']) ? trim($_GET['action']) : "";
		$id = isset($_GET['id']) ? trim($_GET['id']) : "";
		
		switch ($action) {
			// tab selection
			case "tab":
				echo $mc->getFrontend()->getSettingsContent($mc->getMDB(), $id);
				break;
			
			// change icons
			case "changeIcon":
				$data = array();
				
				$data["title"] = "Edit icon";
				$data["body"] = "Here goes the form";
				$data["footer"] = "Maybe we should define a good solution for displaying the buttons and attach a function to handle saving";
				
				echo json_encode($data);
				break;
				
			default:
				echo "Unknown action";
				break;
		}
	}