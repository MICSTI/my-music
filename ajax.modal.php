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
							$body .= "<select class='selectpicker form-control' id='icon-type' name='icon-type' placeholder='Select icon type'>";
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
				
				// adds the selectpicker initialization to the body
				$body .= getSelectpickerReadyFunction();
				
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
				
				// save the icon to the database
				$success = $mc->getMDB()->saveIcon($id, $get["icon-name"], $get["icon-type"], $get["icon-path"]);
			
				$data["success"] = $success;
			
				break;
				
			// add/edit device
			case "VnpguEAw":
				// get data if edit
				if ($id > 0) {
					$device = $mc->getMDB()->getDevice($id);
				} else {
					$device = array();
					
					$device["DeviceName"] = "";
					$device["DeviceDeviceTypeId"] = "";
					
					// for new devices, active is checked per default
					$device["DeviceActive"] = 1;
				}
				
				// form name (for processing data in Javascript)
				$form_name = "device-data";
				$data["form_name"] = $form_name;
				
				// tab name (for updating the content after saving)
				$tab_name = "devices";
				$data["tab_name"] = $tab_name;
			
				// title
				$title = $id <= 0 ? "Add new device" : "Edit device";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// name
					$body .= "<div class='form-group'>";
						$body .= "<label for='device-name' class='control-label col-xs-2'>Name</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='device-name' name='device-name' placeholder='Name' value='" . $device["DeviceName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// device type
					$body .= "<div class='form-group'>";
						$body .= "<label for='device-type' class='control-label col-xs-2'>Type</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<select class='selectpicker form-control' id='device-type' name='device-type'>";
								// display all options
								$device_types = $mc->getMDB()->getDeviceTypes();
								
								foreach ($device_types as $device_type) {
									$icon = $mc->getMDB()->getIcon($device_type["DeviceTypeIconId"]);
									
									$body .= "<option value='" . $device_type["DeviceTypeId"] . "' data-icon='" . $icon["IconPath"] . "' " . compareOption($device_type["DeviceTypeId"], $device["DeviceDeviceTypeId"]) . ">" . $device_type["DeviceTypeName"] . "</option>";
								}
							$body .= "</select>";
						$body .= "</div>";
					$body .= "</div>";
					
					// active
					$body .= "<div class='form-group'>";
						$body .= "<div class='col-xs-offset-2 col-xs-10'>";
							$body .= "<div class='checkbox'>";
								$body .= "<label><input type='checkbox' id='device-active' name='device-active' " . compareCheck($device["DeviceActive"], 1) . " /> Active</label>";
							$body .= "</div>";
						$body .= "</div>";
					$body .= "</div>";
					
				$body .= "</form>";
				
				// adds the selectpicker initialization to the body
				$body .= getSelectpickerReadyFunction();
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "Cg6PwT3H";
				
				break;
				
			// save device
			case "Cg6PwT3H":
				parse_str($params, $get);
				
				$active = isset($get["device-active"]) ? 1 : 0;
				
				// save the device type to the database
				$success = $mc->getMDB()->saveDevice($id, $get["device-name"], $get["device-type"], $active);
			
				$data["success"] = $success;
				
				break;
				
			// add/edit device type
			case "21Uww2Uj":
				// get data if edit
				if ($id > 0) {
					$device_type = $mc->getMDB()->getDeviceType($id);
				} else {
					$device_type = array();
					
					$device_type["DeviceTypeName"] = "";
					$device_type["DeviceTypeIconId"] = "";
				}
				
				// form name (for processing data in Javascript)
				$form_name = "device-type-data";
				$data["form_name"] = $form_name;
				
				// tab name (for updating the content after saving)
				$tab_name = "device-types";
				$data["tab_name"] = $tab_name;
			
				// title
				$title = $id <= 0 ? "Add new device type" : "Edit device type";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// name
					$body .= "<div class='form-group'>";
						$body .= "<label for='device-type-name' class='control-label col-xs-2'>Name</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='device-type-name' name='device-type-name' placeholder='Name' value='" . $device_type["DeviceTypeName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// icon
					$body .= "<div class='form-group'>";
						$body .= "<label for='device-type-icon' class='control-label col-xs-2'>Icon</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<select class='selectpicker form-control' id='device-type-icon' name='device-type-icon'>";
								// display all options
								$icons = $mc->getMDB()->getIcons();
								
								foreach ($icons as $icon) {
									$body .= "<option value='" . $icon["IconId"] . "' data-icon='" . $icon["IconPath"] . "' " . compareOption($icon["IconId"], $device_type["DeviceTypeIconId"]) . ">" . $icon["IconName"] . "</option>";
								}
							$body .= "</select>";
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "RWxHGHMK";
				
				break;
				
			// save device type
			case "RWxHGHMK":
				parse_str($params, $get);
				
				// save the device type to the database
				$success = $mc->getMDB()->saveDeviceType($id, $get["device-type-name"], $get["device-type-icon"]);
			
				$data["success"] = $success;
				
				break;
				
			// add/edit activity
			case "pXBciVn6":
				// get data if edit
				if ($id > 0) {
					$activity = $mc->getMDB()->getActivity($id);
				} else {
					$activity = array();
					
					$activity["ActivityName"] = "";
					$activity["ActivityColor"] = "";
				}
				
				// form name (for processing data in Javascript)
				$form_name = "activity-data";
				$data["form_name"] = $form_name;
				
				// tab name (for updating the content after saving)
				$tab_name = "activities";
				$data["tab_name"] = $tab_name;
			
				// title
				$title = $id <= 0 ? "Add new activity" : "Edit activity";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// name
					$body .= "<div class='form-group'>";
						$body .= "<label for='activity-name' class='control-label col-xs-2'>Name</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='activity-name' name='activity-name' placeholder='Name' value='" . $activity["ActivityName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// color
					$body .= "<div class='form-group'>";
						$body .= "<label for='activity-color' class='control-label col-xs-2'>Color</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<select class='selectpicker form-control' id='activity-color' name='activity-color'>";
								// display all options
								$colors = getColors();
								
								foreach ($colors as $color) {
									$body .= "<option value='" . $color . "' data-content=\"<span class='color-label label-" . $color . "'></span> " . capitalizeFirstLetter($color) . "\" " . compareOption($activity["ActivityColor"], $color) . "></option>";
								}
							$body .= "</select>";
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
				
				// adds the selectpicker initialization to the body
				$body .= getSelectpickerReadyFunction();
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "EH5gIhz4";
				
				break;
				
			// save activity
			case "EH5gIhz4":
				parse_str($params, $get);
				
				// save the activity to the database
				$success = $mc->getMDB()->saveActivity($id, $get["activity-name"], $get["activity-color"]);
			
				$data["success"] = $success;
				
				break;
				
			default:
				$data["status"] = "error";
				$data["message"] = "unknown action";
				
				break;
		}
	}
	
	echo json_encode($data);