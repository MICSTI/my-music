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
							$body .= "<label for='artist-name' class='control-label col-xs-3'>Name</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<p class='form-control-static' id='artist-name'>" . $artist_details["ArtistName"] . "</p>";
							$body .= "</div>";
						$body .= "</div>";
						
						// countries
						$countries = $mc->getMDB()->getCountries();
						
						// main country
						$body .= "<div class='form-group'>";
							$body .= "<label for='record-type' class='control-label col-xs-3'>Origin</label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<select class='selectpicker form-control' id='artist-main-country' name='artist-main-country'>";
									// blank option
									$body .= "<option value='0'>None</option>";
									
									// display all country options
									foreach ($countries as $country) {
										$body .= "<option value='" . $country["CountryId"] . "' data-content=\"" . getCountryFlag($country, true) . "\" " . compareOption($country["CountryId"], $artist_details["ArtistMainCountryId"]) . ">" . $country["CountryName"] . "</option>";
									}
								$body .= "</select>";
							$body .= "</div>";
						$body .= "</div>";
						
						// secondary country
						$body .= "<div class='form-group'>";
							$body .= "<label for='record-type' class='control-label col-xs-3'> </label>";
							$body .= "<div class='col-xs-9'>";
								$body .= "<select class='selectpicker form-control' id='artist-secondary-country' name='artist-secondary-country'>";
									// blank option
									$body .= "<option value='0'>None</option>";
									
									// display all country options
									foreach ($countries as $country) {
										$body .= "<option value='" . $country["CountryId"] . "' data-content=\"" . getCountryFlag($country, true) . "\" " . compareOption($country["CountryId"], $artist_details["ArtistSecondaryCountryId"]) . ">" . $country["CountryName"] . "</option>";
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
				
			// MM link
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
									$body .= "We couldn't find any link suggestions for this song. You can add a song yourself below.";
								}
							$body .= "</div>";
						$body .= "</div>";
						
						// "add your own" panel
						$body .= "<div class='panel panel-default' id='mm-link-suggestions'>";
							$body .= "<div class='panel-heading bold'>Add song manually</div>";
							
							$body .= "<div class='panel-body'>";
								
							$body .= "</div>";
						$body .= "</div>";
						
					$data["body"] = $body;
					
					// footer
					$footer = $mc->getFrontend()->getModalButtons(array("cancel"));
					$data["footer"] = $footer;
					
					// save method id
					//$data["save"] = "CJqGfoAL";
				} else {
					$data["status"] = "error";
					$data["message"] = "No valid id was passed";
				}
				
			
				break;
				
			default:
				$data["status"] = "error";
				$data["message"] = "unknown action";
				
				break;
		}
	}
	
	echo json_encode($data);