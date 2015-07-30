<?php
	include('resources.php');
	
	$data = array();
	
	if ($_GET) {
		$action = isset($_GET['action']) ? trim($_GET['action']) : "";
		$id = isset($_GET['id']) ? $_GET['id'] : 0;
		$params = isset($_GET['params']) ? urldecode($_GET['params']) : "";
		
		// set status ok - it will be set to error if an error occurs later on
		$data["status"] = "ok";
		
		switch ($action) {
			// add/edit icon
			case "TkTiW5a3":
				// get data if edit
				if ($id > 0) {
					$icon = $mc->getMDB()->getIcon($id);
				} else {
					$icon = array();
					$icon["IconName"] = "";
					$icon["IconType"] = "";
					$icon["IconPath"] = "";
				}
				
				// form name (for processing data in Javascript)
				$form_name = "icon-data";
				$data["form_name"] = $form_name;
				
				// tab name (for updating the content after saving)
				$tab_name = "icons";
				$data["tab_name"] = $tab_name;
			
				// title
				$title = $id <= 0 ? "Add new icon" : "Edit icon";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// name
					$body .= "<div class='form-group'>";
						$body .= "<label for='icon-name' class='control-label col-xs-2'>Name</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='icon-name' name='icon-name' placeholder='Name' value='" . $icon["IconName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// type
					$body .= "<div class='form-group'>";
						$body .= "<label for='icon-type' class='control-label col-xs-2'>Type</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<select class='form-control' id='icon-type' name='icon-type' placeholder='Select icon type'>";
								// icon types
								$body .= "<option value='glyphicon' " . compareOption("glyphicon", $icon["IconType"])  . ">Glyphicon</option>";
								$body .= "<option value='path' " . compareOption("path", $icon["IconType"])  . ">Path</option>";
							$body .= "</select>";
						$body .= "</div>";
					$body .= "</div>";
					
					// identifier or path
					$body .= "<div class='form-group'>";
						$body .= "<label for='icon-path' class='control-label col-xs-2'>Path</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='icon-path' name='icon-path' placeholder='Path' value='" . $icon["IconPath"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "UC6Bw9u5";
				
				break;
				
			// save icon
			case "UC6Bw9u5":
				parse_str($params, $get);
				
				// put a wrapper function that handles insert / update here
				$success = $mc->getMDB()->updateIcon($id, $get["icon-name"], $get["icon-type"], $get["icon-path"]);
			
				$data["success"] = $success;
			
				break;
				
			default:
				$data["status"] = "error";
				$data["message"] = "unknown action";
				
				break;
		}
	}
	
	echo json_encode($data);
	
	function compareOption($check, $actual) {
		return (($check == $actual) ? "selected" : "");
	}