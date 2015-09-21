<?php
	include('resources.php');
	
	$data = array();
	
	if ($_GET) {
		$action = isset($_GET['action']) ? trim($_GET['action']) : "";
		$id = isset($_GET['id']) ? $_GET['id'] : 0;
		$params = isset($_GET['params']) ? $_GET['params'] : "";
		
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
							$body .= "<input type='text' class='form-control autofocus' id='icon-name' name='icon-name' placeholder='Name' value='" . $icon["IconName"] . "' />";
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
				
				// on success action
				$data["onSuccess"] = "updateSettings";
			
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
							$body .= "<input type='text' class='form-control autofocus' id='device-name' name='device-name' placeholder='Name' value='" . $device["DeviceName"] . "' />";
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
				
				// on success action
				$data["onSuccess"] = "updateAdministration";
			
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
							$body .= "<input type='text' class='form-control autofocus' id='device-type-name' name='device-type-name' placeholder='Name' value='" . $device_type["DeviceTypeName"] . "' />";
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
				
				// on success action
				$data["onSuccess"] = "updateSettings";
			
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
							$body .= "<input type='text' class='form-control autofocus' id='activity-name' name='activity-name' placeholder='Name' value='" . $activity["ActivityName"] . "' />";
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
				
				// on success action
				$data["onSuccess"] = "updateAdministration";
			
				$data["success"] = $success;
				
				break;
				
			// add/edit country
			case "9wgH0bsX":
				// get data if edit
				if ($id > 0) {
					$country = $mc->getMDB()->getCountry($id);
				} else {
					$country = array();
					
					$country["CountryName"] = "";
					$country["CountryShort"] = "";
				}
				
				// form name (for processing data in Javascript)
				$form_name = "country-data";
				$data["form_name"] = $form_name;
				
				// tab name (for updating the content after saving)
				$tab_name = "countries";
				$data["tab_name"] = $tab_name;
			
				// title
				$title = $id <= 0 ? "Add new country" : "Edit country";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// name
					$body .= "<div class='form-group'>";
						$body .= "<label for='country-name' class='control-label col-xs-2'>Name</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control autofocus' id='country-name' name='country-name' placeholder='Name' value='" . $country["CountryName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// short (alpha2 code)
					$body .= "<div class='form-group'>";
						$body .= "<label for='country-short' class='control-label col-xs-2'>Short</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='country-short' name='country-short' placeholder='Short (Alpha2 code)' value='" . $country["CountryShort"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "ksBHxHq8";
				
				break;
				
			// save country
			case "ksBHxHq8":
				parse_str($params, $get);
				
				// save the country to the database
				$success = $mc->getMDB()->saveCountry($id, $get["country-name"], $get["country-short"]);
				
				// on success action
				$data["onSuccess"] = "updateSettings";
			
				$data["success"] = $success;
				
				break;
				
			// add/edit config property
			case "VACJ1wZn":
				// get data if edit
				if ($id > 0) {
					$config = $mc->getMDB()->getConfigProperty($id);
				} else {
					$config = array();
					
					$config["ConfigProperty"] = "";
					$config["ConfigValue"] = "";
				}
				
				// form name (for processing data in Javascript)
				$form_name = "config-data";
				$data["form_name"] = $form_name;
				
				// tab name (for updating the content after saving)
				$tab_name = "configuration";
				$data["tab_name"] = $tab_name;
			
				// title
				$title = $id <= 0 ? "Add new config property" : "Edit config property";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// property
					$body .= "<div class='form-group'>";
						$body .= "<label for='config-property' class='control-label col-xs-2'>Property</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control autofocus' id='config-property' name='config-property' placeholder='Property' value='" . $config["ConfigProperty"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// value
					$body .= "<div class='form-group'>";
						$body .= "<label for='config-value' class='control-label col-xs-2'>Value</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='config-value' name='config-value' placeholder='Value' value='" . $config["ConfigValue"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "JBOETIN7";
				
				break;
				
			// save config property
			case "JBOETIN7":
				parse_str($params, $get);
				
				// save the country to the database
				$success = $mc->getMDB()->saveConfigProperty($id, $get["config-property"], $get["config-value"]);
				
				// on success action
				$data["onSuccess"] = "updateSettings";
			
				$data["success"] = $success;
				
				break;
				
			// add/edit record type
			case "k2PZk2Zq":
				// get data if edit
				if ($id > 0) {
					$record_type = $mc->getMDB()->getRecordType($id);
				} else {
					$record_type = array();
					
					$record_type["RecordTypeName"] = "";
					$record_type["RecordTypeLevel"] = "";
				}
				
				// form name (for processing data in Javascript)
				$form_name = "record-type-data";
				$data["form_name"] = $form_name;
				
				// tab name (for updating the content after saving)
				$tab_name = "record-types";
				$data["tab_name"] = $tab_name;
			
				// title
				$title = $id <= 0 ? "Add new record type" : "Edit record type";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// name
					$body .= "<div class='form-group'>";
						$body .= "<label for='record-type-name' class='control-label col-xs-2'>Name</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control autofocus' id='record-type-name' name='record-type-name' placeholder='Name' value='" . $record_type["RecordTypeName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// level (hidden)
					$body .= "<input type='hidden' id='record-type-level' name='record-type-level' value='" . $record_type["RecordTypeLevel"] . "' />";
				$body .= "</form>";
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "uS9wWOLJ";
				
				break;
				
			// save record type
			case "uS9wWOLJ":
				parse_str($params, $get);
				
				// if it is a new record type, get the next record type level
				if ($id <= 0) {
					$level = $mc->getMDB()->getNextRecordTypeLevel();
				} else {
					$level = $get["record-type-level"];
				}
				
				// save the record type to the database
				$success = $mc->getMDB()->saveRecordType($id, $get["record-type-name"], $level);
				
				// on success action
				$data["onSuccess"] = "updateSettings";
			
				$data["success"] = $success;
				
				break;
				
			// save record type order
			case "U7GK66Ve":
				$order_array = explode(",", $params);
				
				$success = true;
				$order = 1;
				
				foreach($order_array as $rt) {
					if (!$mc->getMDB()->updateRecordTypeLevel($rt, $order)) {
						$success = false;
					}
					
					$order++;
				}
				
				// tab to updateRecordTypeLevel
				$data["tab"] = "record-types";
				
				$data["success"] = $success;
			
				break;
				
			// edit record details
			case "2HedLZAk":
				if ($id > 0) {
					// get data
					$record_details = $mc->getMDB()->getRecordDetails($id);
					
					// form name (for processing data in Javascript)
					$form_name = "record-detail-data";
					$data["form_name"] = $form_name;
				
					// title
					$title = "Edit record details";
					$data["title"] = $title;
					
					// body
					$body = "";
					
					$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
						// artist (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='artist-name' class='control-label col-xs-3'>Artist</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='artist-name'>" . $record_details["ArtistName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='record-name' class='control-label col-xs-3'>Title</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='record-name'>" . $record_details["RecordName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// type
						$body .= "<div class='form-group'>";
							$body .= "<label for='record-type' class='control-label col-xs-3'>Type</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<select class='selectpicker form-control' id='record-type' name='record-type'>";
									// display all options
									$record_types = $mc->getMDB()->getRecordTypes();
									
									foreach ($record_types as $record_type) {
										$body .= "<option value='" . $record_type["RecordTypeId"] . "' " . compareOption($record_type["RecordTypeId"], $record_details["RecordTypeId"]) . ">" . $record_type["RecordTypeName"] . "</option>";
									}
								$body .= "</select>";
							$body .= "</div>";
						$body .= "</div>";
						
						// publish date
						$publish_date = $record_details["RecordPublishDate"] != "0000-00-00" ? $record_details["RecordPublishDate"] : "";
						
						if ($record_details["RecordPublishDate"] != "0000-00-00") {
							$mysql_date = new MysqlDate($record_details["RecordPublishDate"]);
							$publish_date = $mysql_date->convert2AustrianDate();
						} else {
							$publish_date = "";
						}
						
						$body .= "<div class='form-group'>";
							$body .= "<label for='record-publish' class='control-label col-xs-3'>Publish date</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<input type='text' class='form-control date-picker' id='record-publish' name='record-publish' placeholder='Date' value='" . $publish_date . "' />";
							$body .= "</div>";
						$body .= "</div>";
						
					$body .= "</form>";
					
					$data["body"] = $body;
					
					// footer
					$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
					$data["footer"] = $footer;
					
					// save method id
					$data["save"] = "YocDZV0O";
				} else {
					$data["status"] = "error";
					$data["message"] = "No valid id was passed";
				}
				
				break;
				
			// save record details
			case "YocDZV0O":
				parse_str($params, $get);
				
				$typeid = $get["record-type"];
				$publish = getMysqlDate($get["record-publish"]);
				
				// save the record type to the database
				$success = $mc->getMDB()->updateRecordDetails($id, $typeid, $publish);
				
				// on success action
				$data["onSuccess"] = "updateRecordInformation";
			
				$data["success"] = $success;
				
				break;
				
			// edit artist details
			case "DVDT2mad":
				if ($id > 0) {
					// get data
					$artist_details = $mc->getMDB()->getArtist($id);
					
					// form name (for processing data in Javascript)
					$form_name = "artist-detail-data";
					$data["form_name"] = $form_name;
				
					// title
					$title = "Edit artist details";
					$data["title"] = $title;
					
					// body
					$body = "";
					
					$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
						// name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='artist-name' class='control-label col-xs-2'>Name</label>";
							$body .= "<div class='col-xs-10'>";
								$body .= "<p class='form-control-static' id='artist-name'>" . $artist_details["ArtistName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// main country
						$body .= "<div class='form-group'>";
							$body .= "<label for='record-type' class='control-label col-xs-2'>Origin</label>";
							$body .= "<div class='col-xs-10'>";
								$main_country_select_params = array("class" => "selectpicker form-control", "id" => "artist-main-country", "name" => "artist-main-country");
								
								$body .= getCountrySelectBox($mc->getMDB(), $main_country_select_params, $artist_details["ArtistMainCountryId"]);
							$body .= "</div>";
						$body .= "</div>";
						
						// secondary country
						$body .= "<div class='form-group'>";
							$body .= "<label for='record-type' class='control-label col-xs-2'> </label>";
							$body .= "<div class='col-xs-10'>";
								$secondary_country_select_params = array("class" => "selectpicker form-control", "id" => "artist-secondary-country", "name" => "artist-secondary-country");
								
								$body .= getCountrySelectBox($mc->getMDB(), $secondary_country_select_params, $artist_details["ArtistSecondaryCountryId"]);
							$body .= "</div>";
						$body .= "</div>";
						
					$body .= "</form>";
					
					$data["body"] = $body;
					
					// footer
					$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
					$data["footer"] = $footer;
					
					// save method id
					$data["save"] = "CJqGfoAL";
				} else {
					$data["status"] = "error";
					$data["message"] = "No valid id was passed";
				}
				
				break;
				
			// save artist details
			case "CJqGfoAL":
				parse_str($params, $get);
				
				$main_country_id = $get["artist-main-country"];
				$secondary_country_id = $get["artist-secondary-country"];
				
				// save the record type to the database
				$success = $mc->getMDB()->updateArtistDetails($id, $main_country_id, $secondary_country_id);
				
				// on success action
				$data["onSuccess"] = "updateArtistInformation";
				
				$data["success"] = $success;
				
				break;
				
			// get record details JSON
			case "JOqlKanU":
				// get data
				$record_details = $mc->getMDB()->getRecordDetails($id);
				
				$publish = new MysqlDate($record_details["RecordPublishDate"]);
				
				$data["record_type"] = $record_details["RecordTypeName"];
				$data["publish"] = $publish->convert2AustrianDate();
				$data["success"] = true;
			
				break;
				
			// get artist details JSON
			case "v8g8frcj":
				// get data
				$artist = $mc->getMDB()->getArtist($id);
				
				$data["main_country"] = getCountryFlag($mc->getMDB()->getCountry($artist["ArtistMainCountryId"]));
				$data["secondary_country"] = getCountryFlag($mc->getMDB()->getCountry($artist["ArtistSecondaryCountryId"]));
				$data["success"] = true;
			
				break;
				
			// MM link (normal)
			case "5r8G1TS4":
				if ($id > 0) {
					// get data
					$song_details = $mc->getMDB()->getSong($id);
					
					// form name (for processing data in Javascript)
					$form_name = "song-mm-link-data";
					$data["form_name"] = $form_name;
				
					// title
					$title = "Add MediaMonkey song link";
					$data["title"] = $title;
					
					// body
					$body = "";
					
					$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
						// song name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-song-name' class='control-label col-xs-3'>Song</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-song-name'>" . $song_details["SongName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// artist name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-artist-name' class='control-label col-xs-3'>Artist</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-artist-name'>" . $song_details["ArtistName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// record name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-record-name' class='control-label col-xs-3'>Record</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-record-name'>" . $song_details["RecordName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// song length
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-song-length' class='control-label col-xs-3'>Length</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-song-length'>" . millisecondsToMinutes($song_details["SongLength"]) . " min</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// date added (static)
						$date_added = new MysqlDate($mc->getMDB()->getSongAddedDate($song_details["SongId"]));
						
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-date-added' class='control-label col-xs-3'>Date added</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-date-added'>" . $date_added->convert2AustrianDate() . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// suggestion panel
						$candidates = $mc->getMDB()->getPossibleMMLinkCandidates($song_details["SongId"]);
						
						$body .= "<div class='panel panel-default' id='mm-link-suggestions'>";
							$body .= "<div class='panel-heading bold'>Link suggestions</div>";
							
							$body .= "<div class='panel-body'>";
									if (count($candidates) > 0) {
									foreach ($candidates as $candidate) {
										$candidate_song = $mc->getMDB()->getSong($candidate);
										
										$body .= "<div class='mm-link-suggestion'>";
											$body .= "<div class='col-xs-5'>";
												// song name
												$body .= "<div>";
													$body .= getSongLink($candidate_song["SongId"], $candidate_song["SongName"]);
												$body .= "</div>";
												
												// artist name
												$body .= "<div>";
													$body .= $candidate_song["ArtistName"];
												$body .= "</div>";
												
												// record name
												$body .= "<div>";
													$body .= $candidate_song["RecordName"];
												$body .= "</div>";
											$body .= "</div>";
											
											$body .= "<div class='col-xs-5'>";
												// Date added
												$candidate_added = new MysqlDate($mc->getMDB()->getSongAddedDate($candidate_song["SongId"]));
												
												$body .= "<div>";
													$body .= "Added on " . $candidate_added->convert2AustrianDate();
												$body .= "</div>";
											
												// Song length
												$body .= "<div>";
													$body .= millisecondsToMinutes($candidate_song["SongLength"]) . " min";
												$body .= "</div>";
											$body .= "</div>";
											
											$body .= "<div class='col-xs-2'>";
												// get right class for button
												$button_class = $mc->getFrontend()->getMMLinkConfirmButtonClass($date_added->convert2UnixTimestamp(), $candidate_added->convert2UnixTimestamp());
											
												$body .= "<button type='button' class='btn btn-" . $button_class . "' onclick=\"performMMLinkSafeCheck(this, '" . $song_details["SongId"] . "', '" . $candidate_song["SongId"] . "')\">Add link</button>";
											$body .= "</div>";
										$body .= "</div>";
									}
								} else {
									$body .= "We couldn't find any link suggestions for this song.";
								}
							$body .= "</div>";
						$body .= "</div>";
					$body .= "</form>";
						
					$data["body"] = $body;
					
					// footer
					$footer = $mc->getFrontend()->getModalButtons(array("cancel"));
					$data["footer"] = $footer;
				} else {
					$data["status"] = "error";
					$data["message"] = "No valid id was passed";
				}
				
				break;
				
			// MM link (from update report)
			case "372L6uL0":
				if ($id > 0) {
					// get data
					$song_details = $mc->getMDB()->getSong($id);
					
					// form name (for processing data in Javascript)
					$form_name = "song-mm-link-data";
					$data["form_name"] = $form_name;
				
					// title
					$title = "Add MediaMonkey song link";
					$data["title"] = $title;
					
					// body
					$body = "";
					
					$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
						// song name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-song-name' class='control-label col-xs-3'>Song</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-song-name'>" . $song_details["SongName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// artist name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-artist-name' class='control-label col-xs-3'>Artist</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-artist-name'>" . $song_details["ArtistName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// record name (static)
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-record-name' class='control-label col-xs-3'>Record</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-record-name'>" . $song_details["RecordName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// song length
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-song-length' class='control-label col-xs-3'>Length</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-song-length'>" . millisecondsToMinutes($song_details["SongLength"]) . " min</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// date added (static)
						$date_added = new MysqlDate($mc->getMDB()->getSongAddedDate($song_details["SongId"]));
						
						$body .= "<div class='form-group'>";
							$body .= "<label for='mm-link-date-added' class='control-label col-xs-3'>Date added</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='mm-link-date-added'>" . $date_added->convert2AustrianDate() . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// suggestion panel
						$candidates = $mc->getMDB()->getPossibleMMLinkCandidates($song_details["SongId"]);
						
						$body .= "<div class='panel panel-default' id='mm-link-suggestions'>";
							$body .= "<div class='panel-heading bold'>Link suggestions</div>";
							
							$body .= "<div class='panel-body'>";
									if (count($candidates) > 0) {
									foreach ($candidates as $candidate) {
										$candidate_song = $mc->getMDB()->getSong($candidate);
										
										$body .= "<div class='mm-link-suggestion'>";
											$body .= "<div class='col-xs-5'>";
												// song name
												$body .= "<div>";
													$body .= getSongLink($candidate_song["SongId"], $candidate_song["SongName"]);
												$body .= "</div>";
												
												// artist name
												$body .= "<div>";
													$body .= $candidate_song["ArtistName"];
												$body .= "</div>";
												
												// record name
												$body .= "<div>";
													$body .= $candidate_song["RecordName"];
												$body .= "</div>";
											$body .= "</div>";
											
											$body .= "<div class='col-xs-5'>";
												// Date added
												$candidate_added = new MysqlDate($mc->getMDB()->getSongAddedDate($candidate_song["SongId"]));
												
												$body .= "<div>";
													$body .= "Added on " . $candidate_added->convert2AustrianDate();
												$body .= "</div>";
											
												// Song length
												$body .= "<div>";
													$body .= millisecondsToMinutes($candidate_song["SongLength"]) . " min";
												$body .= "</div>";
											$body .= "</div>";
											
											$body .= "<div class='col-xs-2'>";
												// get right class for button
												$button_class = $mc->getFrontend()->getMMLinkConfirmButtonClass($date_added->convert2UnixTimestamp(), $candidate_added->convert2UnixTimestamp());
											
												$body .= "<button type='button' class='btn btn-" . $button_class . "' onclick=\"addMMLinkFromUpdateReport('" . $song_details["SongId"] . "', '" . $candidate_song["SongId"] . "')\">Add link</button>";
											$body .= "</div>";
										$body .= "</div>";
									}
								} else {
									$body .= "We couldn't find any link suggestions for this song.";
								}
							$body .= "</div>";
						$body .= "</div>";
					$body .= "</form>";
						
					$data["body"] = $body;
					
					// footer
					$footer = $mc->getFrontend()->getModalButtons(array("cancel"));
					$data["footer"] = $footer;
				} else {
					$data["status"] = "error";
					$data["message"] = "No valid id was passed";
				}
				
				break;
				
			// song administration
			case "57bB21kN":
				// get data if edit
				if ($id > 0) {
					$song = $mc->getMDB()->getSong($id);
					
					$added = new MysqlDate($mc->getMDB()->getSongAddedDate($id));
					$added_date = $added->convert2AustrianDate();
				} else {
					$song = array();
					
					$song["SongName"] = "";
					$song["ArtistName"] = "";
					$song["RecordName"] = "";
					$song["SongLength"] = "";
					$song["SongRating"] = "";
					$song["SongComment"] = "";
					$song["SongBitrate"] = "";
					$song["SongDiscNo"] = "";
					$song["SongTrackNo"] = "";
					
					$added_date = "";
				}	
					
				// form name (for processing data in Javascript)
				$form_name = "admin-song";
				$data["form_name"] = $form_name;
			
				// title
				$title = $id > 0 ? "Edit song" : "Add song";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// song title
					$body .= "<div class='form-group'>";
						$body .= "<label for='song-admin-song-title' class='control-label col-xs-2'>Title</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control autofocus' id='song-admin-song-title' name='song-admin-song-title' placeholder='Song title' value='" . $song["SongName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// artist name
					$body .= "<div class='form-group'>";
						$body .= "<label for='song-admin-artist-name' class='control-label col-xs-2'>Artist</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='song-admin-artist-name' name='song-admin-artist-name' placeholder='Artist name' value='" . $song["ArtistName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// record name
					$body .= "<div class='form-group'>";
						$body .= "<label for='song-admin-record-name' class='control-label col-xs-2'>Record</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control' id='song-admin-record-name' name='song-admin-record-name' placeholder='Record name' value='" . $song["RecordName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// don't show static content if it's a new song
					if ($id > 0) {
						$body .= "<div id='" . $form_name . "-static'>";
							// length (static)
							$body .= "<input type='hidden' id='song-admin-song-length' name='song-admin-song-length' value='" . $song["SongLength"] . "' />";
							
							$body .= "<div class='form-group'>";
								$body .= "<label for='song-admin-song-length' class='control-label col-xs-2'>Length</label>";
								$body .= "<div class='col-xs-10'>";
									$body .= "<p class='form-control-static'>" . millisecondsToMinutes($song["SongLength"]) . " min</p>";
								$body .= "</div>";
							$body .= "</div>";
							
							// bitrate (static)
							$body .= "<input type='hidden' id='song-admin-song-bitrate' name='song-admin-song-bitrate' value='" . $song["SongBitrate"] . "' />";
							
							$body .= "<div class='form-group'>";
								$body .= "<label for='song-admin-song-bitrate' class='control-label col-xs-2'>Bitrate</label>";
								$body .= "<div class='col-xs-10'>";
									$body .= "<p class='form-control-static'>" . formatBitrate($song["SongBitrate"]) . " kbps</p>";
								$body .= "</div>";
							$body .= "</div>";
							
							// discno (static)
							$body .= "<input type='hidden' id='song-admin-song-discno' name='song-admin-song-discno' value='" . $song["SongDiscNo"] . "' />";
							
							$body .= "<div class='form-group'>";
								$body .= "<label for='song-admin-song-discno' class='control-label col-xs-2'>Disc no</label>";
								$body .= "<div class='col-xs-10'>";
									$body .= "<p class='form-control-static'>" . $song["SongDiscNo"] . "</p>";
								$body .= "</div>";
							$body .= "</div>";
							
							// trackno (static)
							$body .= "<input type='hidden' id='song-admin-song-trackno' name='song-admin-song-trackno' value='" . $song["SongTrackNo"] . "' />";
							
							$body .= "<div class='form-group'>";
								$body .= "<label for='song-admin-song-trackno' class='control-label col-xs-2'>Track no</label>";
								$body .= "<div class='col-xs-10'>";
									$body .= "<p class='form-control-static'>" . $song["SongTrackNo"] . "</p>";
								$body .= "</div>";
							$body .= "</div>";
							
							// rating (static)
							$body .= "<input type='hidden' id='song-admin-song-rating' name='song-admin-song-rating' value='" . $song["SongRating"] . "' />";
							
							$body .= "<div class='form-group'>";
								$body .= "<label for='song-admin-song-rating' class='control-label col-xs-2'>Rating</label>";
								$body .= "<div class='col-xs-10'>";
									$body .= "<p class='form-control-static'>" . getStarsRating($song["SongRating"]) . "</p>";
								$body .= "</div>";
							$body .= "</div>";
							
							// added date (static)
							if ($added_date != "") {
								$body .= "<div class='form-group'>";
									$body .= "<label for='song-admin-song-added' class='control-label col-xs-2'>Added on</label>";
									$body .= "<div class='col-xs-10'>";
										$body .= "<p class='form-control-static' id='song-admin-song-added'>" . $added_date . "</p>";
									$body .= "</div>";
								$body .= "</div>";
							}
						$body .= "</div>";
					}
					
					// comment
					$body .= "<div class='form-group'>";
						$body .= "<label for='song-admin-record-comment' class='control-label col-xs-2'>Comment</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<textarea class='form-control' id='song-admin-comment' name='song-admin-comment' placeholder='Comment'>" . $song["SongComment"] . "</textarea>";
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
					
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "y2DqnxCB";
				
				break;
				
			// save song
			case "y2DqnxCB":
				parse_str($params, $get);
				
				// get artist id
				$aid = $mc->getMDB()->pushArtist(trim($get["song-admin-artist-name"]));
				
				// get record id
				$rid = $mc->getMDB()->pushRecord(trim($get["song-admin-record-name"]), $aid);
				
				// get other form values
				$name = $get["song-admin-song-title"];
				$comment  = $get["song-admin-comment"];
				
				if ($id > 0) {
					$length = $get["song-admin-song-length"];
					$bitrate = $get["song-admin-song-bitrate"];
					$discno  = $get["song-admin-song-discno"];
					$trackno  = $get["song-admin-song-trackno"];
					$rating  = $get["song-admin-song-rating"];
				} else {
					$length = 0;
					$bitrate = 0;
					$discno = 0;
					$trackno = 0;
					$rating = 0;
				}
				
				// save the song to the database
				$sid = $mc->getMDB()->saveSong($id, $name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating, $comment);
				
				// if a new song is added, add a mm link with id 0!
				if ($id <= 0) {
					$mmid = 0;
					
					$now = new UnixTimestamp(mktime());
					$added_mysql = $now->convert2MysqlDate();
					
					$mc->getMDB()->addMMLink($sid, $mmid, $added_mysql);
				}
				
				// set db modification timestamp (for mobile devices to know that the database has changed)
				$mc->getMDB()->setDbModificationTimestamp();
				
				if ($sid !== false) {
					$success = true;
					
					$data["SongId"] = $sid;
				} else {
					$success = false;
				}
				
				// on success action
				$data["onSuccess"] = "savedSong";
			
				$data["success"] = $success;
			
				break;
				
			// artist administration
			case "YTYrcS79":
				// get data if edit
				if ($id > 0) {
					$artist = $mc->getMDB()->getArtist($id);
					
				} else {
					$artist = array();
					
					$artist["ArtistName"] = "";
					$artist["ArtistMainCountryId"] = 0;
					$artist["ArtistSecondaryCountryId"] = 0;
				}	
					
				// form name (for processing data in Javascript)
				$form_name = "admin-artist";
				$data["form_name"] = $form_name;
			
				// title
				$title = $id > 0 ? "Edit artist" : "Add artist";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// artist name
					$body .= "<div class='form-group'>";
						$body .= "<label for='artist-admin-artist-name' class='control-label col-xs-2'>Name</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control autofocus' id='artist-admin-artist-name' name='artist-admin-artist-name' placeholder='Name' value='" . $artist["ArtistName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// main country
					$body .= "<div class='form-group'>";
						$body .= "<label for='artist-admin-main-country-id' class='control-label col-xs-2'>Origin</label>";
						$body .= "<div class='col-xs-10'>";
							$main_country_select_params = array("class" => "selectpicker form-control", "id" => "artist-admin-main-country-id", "name" => "artist-admin-main-country-id");
							
							$body .= getCountrySelectBox($mc->getMDB(), $main_country_select_params, $artist["ArtistMainCountryId"]);
						$body .= "</div>";
					$body .= "</div>";
					
					// secondary country
					$body .= "<div class='form-group'>";
						$body .= "<label for='artist-admin-secondary-country-id' class='control-label col-xs-2'> </label>";
						$body .= "<div class='col-xs-10'>";
							$secondary_country_select_params = array("class" => "selectpicker form-control", "id" => "artist-admin-secondary-country-id", "name" => "artist-admin-secondary-country-id");
							
							$body .= getCountrySelectBox($mc->getMDB(), $secondary_country_select_params, $artist["ArtistSecondaryCountryId"]);
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
					
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "QGdoP0vf";
				
				break;
				
			// save artist
			case "QGdoP0vf":
				parse_str($params, $get);
				
				// success flag
				$success = false;
				
				// get artist info form form
				$name = trim($get["artist-admin-artist-name"]);
				$main_country_id = $get["artist-admin-main-country-id"];
				$secondary_country_id = $get["artist-admin-secondary-country-id"];
				
				if ($id > 0) {
					$success = $mc->getMDB()->updateArtist($id, $name, $main_country_id, $secondary_country_id);
				} else {
					$id = $mc->getMDB()->addArtist($name, $main_country_id, $secondary_country_id);
					
					if ($id !== false) {
						$success = true;
					}
				}
				
				// set db modification timestamp (for mobile devices to know that the database has changed)
				$mc->getMDB()->setDbModificationTimestamp();
				
				// add artist id for success action (no need for error checking since it won't get executed if an error occurred)
				$data["ArtistId"] = $id;
				
				// on success action
				$data["onSuccess"] = "savedArtist";
			
				$data["success"] = $success;
			
				break;
				
			// record administration
			case "uXQMGi1b":
				// get data if edit
				if ($id > 0) {
					$record = $mc->getMDB()->getRecord($id);
					
				} else {
					$record = array();
					
					$record["RecordName"] = "";
					$record["ArtistId"] = -1;
					$record["ArtistName"] = "";
					$record["RecordTypeId"] = 0;
					$record["RecordPublishDate"] = "0000-00-00";
				}	
					
				// form name (for processing data in Javascript)
				$form_name = "admin-record";
				$data["form_name"] = $form_name;
			
				// title
				$title = $id > 0 ? "Edit record" : "Add record";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// record name
					$body .= "<div class='form-group'>";
						$body .= "<label for='record-admin-record-name' class='control-label col-xs-3'>Title</label>";
						$body .= "<div class='col-xs-9'>";
							$body .= "<input type='text' class='form-control autofocus' id='record-admin-record-name' name='record-admin-record-name' placeholder='Name' value='" . $record["RecordName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
				
					// artist name
					$body .= "<div class='form-group'>";
						$body .= "<label for='record-admin-artist-name' class='control-label col-xs-3'>Artist</label>";
						$body .= "<div class='col-xs-9'>";
							$body .= "<input type='text' class='form-control' id='record-admin-artist-name' name='record-admin-artist-name' placeholder='Artist name' value='" . $record["ArtistName"] . "' />";
						$body .= "</div>";
					$body .= "</div>";
					
					// record type
					$body .= "<div class='form-group'>";
						$body .= "<label for='record-admin-record-type' class='control-label col-xs-3'>Type</label>";
						$body .= "<div class='col-xs-9'>";
							$body .= "<select class='selectpicker form-control' id='record-admin-record-type' name='record-admin-record-type'>";
								// display all options
								$record_types = $mc->getMDB()->getRecordTypes();
								
								foreach ($record_types as $record_type) {
									$body .= "<option value='" . $record_type["RecordTypeId"] . "' " . compareOption($record_type["RecordTypeId"], $record["RecordTypeId"]) . ">" . $record_type["RecordTypeName"] . "</option>";
								}
							$body .= "</select>";
						$body .= "</div>";
					$body .= "</div>";
					
					// publish date
					$publish_date = $record["RecordPublishDate"] != "0000-00-00" ? $record["RecordPublishDate"] : "";
					
					if ($record["RecordPublishDate"] != "0000-00-00") {
						$mysql_date = new MysqlDate($record["RecordPublishDate"]);
						$publish_date = $mysql_date->convert2AustrianDate();
					} else {
						$publish_date = "";
					}
					
					$body .= "<div class='form-group'>";
						$body .= "<label for='record-admin-publish-date' class='control-label col-xs-3'>Publish date</label>";
						$body .= "<div class='col-xs-9'>";
							$body .= "<input type='text' class='form-control date-picker' id='record-admin-publish-date' name='record-admin-publish-date' placeholder='Date' value='" . $publish_date . "' />";
						$body .= "</div>";
					$body .= "</div>";
				$body .= "</form>";
					
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "JLLamRov";
				
				break;
				
			// save record
			case "JLLamRov":
				parse_str($params, $get);
				
				// get artist id
				$aid = $mc->getMDB()->pushArtist(trim($get["record-admin-artist-name"]));
				
				// get other form values
				$name = $get["record-admin-record-name"];
				$typeid = $get["record-admin-record-type"];
				
				$publish = trim($get["record-admin-publish-date"]);
				
				if (empty($publish)) {
					$publish_date = "0000-00-00";
				} else {
					$publish_date = getMysqlDate($publish);
				}
				
				// save the record to the database
				$rid = $mc->getMDB()->saveRecord($id, $name, $aid, $typeid, $publish_date);
				
				if ($rid !== false) {
					$success = true;
					
					// set db modification timestamp (for mobile devices to know that the database has changed)
					$mc->getMDB()->setDbModificationTimestamp();
					
					$data["RecordId"] = $rid;
				} else {
					$success = false;
				}
				
				// on success action
				$data["onSuccess"] = "savedRecord";
			
				$data["success"] = $success;
			
				break;
				
			// played administration
			case "6I6T4dfW":
				// get date info
				parse_str($params, $get);
				
				$date = $get["date"];
			
				// get data if edit
				$played = $mc->getMDB()->getPlayed($id);
				
				$song = $mc->getMDB()->getSong($played["SongId"]);
					
				// form name (for processing data in Javascript)
				$form_name = "admin-played";
				$data["form_name"] = $form_name;
			
				// title
				$title = "Edit played";
				$data["title"] = $title;
				
				// body
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
					// date (hidden)
					$body .= "<input type='hidden' id='played-admin-date' name='played-admin-date' value='" . $date . "' />";
				
					// time
					$mysql_datetime = new MysqlDateTime($played["PlayedTimestamp"]);
					$time = $mysql_datetime->convert2Time();
					
					$body .= "<div class='form-group'>";
						$body .= "<label for='played-admin-time' class='control-label col-xs-2'>Time</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= "<input type='text' class='form-control autofocus' id='played-admin-time' name='played-admin-time' placeholder='Time' value='" . $time . "' />";
						$body .= "</div>";
					$body .= "</div>";
				
					// song
					$body .= "<input type='hidden' id='song-id' name='song-id' value='" . $song["SongId"] . "' />";
					
					$body .= "<div class='form-group'>";
						$body .= "<label for='played-admin-song-id' class='control-label col-xs-2'>Song</label>";
						
						$body .= "<div id='played-admin-song-input' class='col-xs-10'>";
							$body .= "<input type='text' id='played-admin-song-id' class='form-control' placeholder='Choose song' value=\"" . $song["SongName"] . "\" />";
						$body .= "</div>";
						
						$body .= "<div id='played-admin-song-display' class='col-xs-10'>";
							// song info
							$body .= "<div class='search_artist_name'>" . $song["ArtistName"] . "</div>";
							$body .= "<div>" . $song["SongName"] . "</div>";
							$body.= "<div class='search_record_name'>" . $song["RecordName"] . "</div>";
						$body .= "</div>";
					$body .= "</div>";
					
					// device
					$device_params = array("class" => "selectpicker form-control", "id" => "played-admin-device-id", "name" => "played-admin-device-id");
					
					$body .= "<div class='form-group'>";
						$body .= "<label for='played-admin-device-id' class='control-label col-xs-2'>Device</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= getDeviceSelectBox($mc->getMDB(), $device_params, $played["DeviceId"]);
						$body .= "</div>";
					$body .= "</div>";
					
					// activity
					$activity_params = array("class" => "selectpicker form-control", "id" => "played-admin-activity-id", "name" => "played-admin-activity-id");
					
					$body .= "<div class='form-group'>";
						$body .= "<label for='played-admin-activity-id' class='control-label col-xs-2'>Activity</label>";
						$body .= "<div class='col-xs-10'>";
							$body .= getActivitySelectBox($mc->getMDB(), $activity_params, $played["ActivityId"]);
						$body .= "</div>";
					$body .= "</div>";
					
				$body .= "</form>";
					
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				// save method id
				$data["save"] = "Zqqmukyu";
				
				break;
				
			// save played
			case "Zqqmukyu":
				parse_str($params, $get);
				
				// get form data
				$song_id = $get["song-id"];
				$device_id = $get["played-admin-device-id"];
				$activity_id = $get["played-admin-activity-id"];
				
				$date = $get["played-admin-date"];
				$time = $get["played-admin-time"];
				
				$unix_timestamp = new UnixTimestamp(mktime(substr($time, 0, 2), substr($time, 3), 0, substr($date, 3, 2), substr($date, 0, 2), substr($date, 6)));
				$timestamp = $unix_timestamp->convert2MysqlDateTime();
				
				$success = $mc->getMDB()->updatePlayed($id, $song_id, $device_id, $activity_id, $timestamp);
				
				// on success action
				$data["onSuccess"] = "savedPlayed";
			
				$data["success"] = $success;
			
				break;
				
			// country overview
			case "olfOmquv":
				// data
				$country_id = $id;
				
				$country = $mc->getMDB()->getCountry($country_id);
				$country_flag = getCountryFlag($country);
				
				// title
				$title = $country_flag . " " . $country["CountryName"];
				$data["title"] = $title;
				
				$body = "";
				
				// Artists from this country
				$country_artists = $mc->getMDB()->getArtistsFromCountry($country_id);
				
				if (count($country_artists) > 0) {
					
					$body .= "<table class='table table-striped'>";
					
						$body .= "<thead>";
							$body .= "<tr>";
								$body .= "<th class='col-sm-6'>Artist</th>";
								$body .= "<th class='col-sm-6'># times played</th>";
							$body .= "</tr>";
						$body .= "</thead>";
						
						$body .= "<tbody>";
					
							foreach ($country_artists as $artist) {
								$body .= "<tr>";
									$body .= "<td>" . getArtistLink($artist["ArtistId"], $artist["ArtistName"]) . "</td>";
									$body .= "<td>" . $artist["PlayedCount"] . "</td>";
								$body .= "</tr>";
							}
						$body .= "</tbody>";
						
					$body .= "</table>";
				}
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("ok"));
				$data["footer"] = $footer;
				
				break;
				
			// select record
			case "4sncwonu":
				// title
				$title = "Select album";
				$data["title"] = $title;
				
				// form name (for processing data in Javascript)
				$form_name = "listen-whole-album";
				$data["form_name"] = $form_name;
				
				$body = "";
				
				$body .= "<form class='form-horizontal' id='" . $form_name . "'>";
				
					// record
					$body .= "<input type='hidden' id='record-id' name='record-id' value='0' />";
					
					$body .= "<div class='form-group'>";
						$body .= "<label for='whole-album-record-name' class='control-label col-xs-2'>Record</label>";
						
						$body .= "<div id='whole-album-record-input' class='col-xs-10'>";
							$body .= "<input type='text' id='whole-album-record-id' class='form-control autofocus' placeholder='Choose record' autocomplete='off' />";
						$body .= "</div>";
						
						$body .= "<div id='whole-album-record-display' class='col-xs-10'>";
						$body .= "</div>";
					$body .= "</div>";
				
				$body .= "</form>";
				
				$data["body"] = $body;
				
				// footer
				$footer = $mc->getFrontend()->getModalButtons(array("cancel", "save"));
				$data["footer"] = $footer;
				
				$data["save"] = "J55pluHk";
			
				break;
				
			// chosen record
			case "J55pluHk":
				parse_str($params, $get);
				
				// get form data
				$record_id = $get["record-id"];
				
				// get songs from this record
				$record_info = $mc->getMDB()->getRecord($record_id);
				
				if (count($record_info["SongList"]) > 0) {
					$data["songs"] = $record_info["SongList"];
					
					// add artist and record name
					$data["artist"] = $record_info["ArtistName"];
					$data["record"] = $record_info["RecordName"];
					
					// on success action
					$data["onSuccess"] = "chosenRecord";
				
					$success = true;
				} else {
					$success = false;
				}
				
				$data["success"] = $success;
			
				break;
				
			default:
				$data["status"] = "error";
				$data["message"] = "unknown action";
				
				break;
		}
	}
	
	echo json_encode($data);