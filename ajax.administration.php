<?php
	include('resources.php');
	
	if ($_GET) {
		$action = isset($_GET['action']) ? trim($_GET['action']) : "";
		$id = isset($_GET['id']) ? trim($_GET['id']) : "";
		
		switch ($action) {
			// tab selection
			case "tab":
				echo $mc->getFrontend()->getAdministrationContent($mc->getMDB(), $id);
				break;
				
			default:
				echo "Unknown action";
				break;
		}
	}