<?php
	require_once('page.php');

	class frontend {
		private $page;
		
		private $PAGE_TITLE = "myMusic";
		
		private $STYLESHEETS = array(
								"http://fonts.googleapis.com/css?family=Oxygen",
								"external/bootstrap/css/bootstrap.min.css",
								"external/flags/css/flag-icon.min.css",
								"external/jquery/jquery-ui.min.css",
								"external/bootstrap-select/bootstrap-select.min.css",
								"auto_complete.css",
								"datepicker.css",
								"mymusic.css"
							);
		
		private $SCRIPTS = array(
								"external/jquery/jquery-2.1.4.min.js",
								"external/jquery/jquery-ui.min.js",
								"external/external.js",
								"external/bootstrap/js/bootstrap.min.js",
								"external/bootstrap-select/bootstrap-select.min.js",
								"external/notifiy/notify-combined.min.js",
								"external/jquery/jquery.hotkeys.js",
								"util.js",
								"auto_complete.js",
								"bootstrap-datepicker.js",
								"mymusic.js"
							);
							
		// Action ids
		private $SAVE_ICON = "TkTiW5a3";
		private $SAVE_ACTIVITY = "pXBciVn6";
		private $SAVE_CONFIG = "VACJ1wZn";
		private $SAVE_COUNTRY = "9wgH0bsX";
		private $SAVE_DEVICE = "VnpguEAw";
		private $SAVE_DEVICE_TYPE = "21Uww2Uj";
		private $SAVE_RECORD_TYPE = "k2PZk2Zq";
		private $SAVE_RECORD_DETAILS = "2HedLZAk";
		
		public function __construct() {
			$this->page = new page();
		
			// Set master container and its closing tags
			$this->page->setMaster($this->getMasterContainer(), $this->getMasterClose());
		}
		
		private function getHTML () {
			return $this->page->getHTML();
		}
		
		public function getIndex ($main = "", $menu_select = "") {
			// add title div
			$this->page->addPart("title", $this->getTitle());
			
			// add menu div
			$this->page->addPart("menu", $this->getMenu($menu_select));
			
			// add main div
			$this->page->addPart("main", $this->getMainWrapper($main));
			
			// add footer div
			$this->page->addPart("footer", $this->getFooter());
			
			return $this->getHTML();
		}
		
		// Content of title div
		private function getTitle () {
			$title = "";
			
			$title .= "<div class='page-header'>";
				$title .= "<h1>myMusic <small class='hidden-xs'>Everything you want to know about your music library</small></h1>";
			$title .= "</div>";
			
			return $title;
		}
		
		// Content of menu div
		private function getMenu ($selected) {
			$menu = "";
			
			$menu .= "<nav role='navigation' class='navbar navbar-default'>";
				$menu .= "<div class='navbar-header'>";
					$menu .= "<button type='button' data-target='#navbarCollapse' data-toggle='collapse' class='navbar-toggle'>";
						$menu .= "<span class='sr-only'>Toggle navigation</span>";
						$menu .= "<span class='icon-bar'></span>";
						$menu .= "<span class='icon-bar'></span>";
						$menu .= "<span class='icon-bar'></span>";
					$menu .= "</button>";
					
					$menu .= "<a href='#' id='icon-brand' class='navbar-brand glyphicon glyphicon-headphones'></a>";
				$menu .= "</div>";
				
				$menu .= "<div id='navbarCollapse' class='collapse navbar-collapse'>";
					$menu .= "<ul class='nav navbar-nav'>";
						$menu .= "<li class='" . $this->getActiveText("home", $selected) . "'><a href='#'>Home</a></li>";
						$menu .= "<li class='dropdown " . $this->getActiveText("charts", $selected) . "'>";
							$menu .= "<a href='#' data-toggle='dropdown' class='dropdown-toggle'>Charts <b class='caret'></b></a>";
							
							$menu .= "<ul class='dropdown-menu'>";
								$menu .= "<li><a href='#'>Top 20/20</a></li>";
								$menu .= "<li><a href='favourites.php'>Favourites</a></li>";
								$menu .= "<li><a href='#'>Years</a></li>";
							$menu .= "</ul>";
							
						$menu .= "<li class='" . $this->getActiveText("history", $selected) . "'><a href='history.php'>History</a></li>";
						$menu .= "<li><a href='#'>Concerts</a></li>";
						$menu .= "<li class='" . $this->getActiveText("administration", $selected) . "'><a href='administration.php'>Administration</a></li>";
						$menu .= "<li class='" . $this->getActiveText("settings", $selected) . "'><a href='settings.php'>Settings</a></li>";
					$menu .= "</ul>";
					
					// Search field
					$menu .= "<form id='form-search' role='search' class='navbar-form navbar-left'>";
						$menu .= "<div class='form-group'>";
							$menu .= "<input type='text' id='search-field' class='form-control' placeholder='Search for songs, artists or records' size='34' autocomplete='off' />";
						$menu .= "</div>";
					$menu .= "</form>";
				$menu .= "</div>";
			$menu .= "</nav>";
			
			return $menu;
		}
		
		/**
			Checks if the element class should be marked as "active".
		*/
		private function checkActive ($li, $actual) {
			return $li == $actual;
		}
		
		/**
			Performs a check if the menu element should be marked as active
		*/
		private function getActiveText ($li, $actual) {
			return $this->checkActive($li, $actual) ? "active" : "";
		}
		
		// main wrapper
		private function getMainWrapper ($main) {
			$wrapper = "<div class='container pull-left'>" . $main . "</div>";
			
			return $wrapper;
		}
		
		// Content of footer div
		private function getFooter () {
			$footer = "";
			
			/*$footer .= "<div id='footer'>";
				$footer .= "&copy; Michael Stifter 2014";
			$footer .= "</div>";*/
			
			return $footer;
		}
		
		private function getMasterContainer () {
			$master = "";
			
			// HTML structure
			$master .= "<!DOCTYPE HTML>";
			
			$master .= "<html>";
				
				// Head
				$master .= "<head>";
					// Meta information
					$master .= "<meta charset='utf-8' />";
					$master .= "<meta name='viewport' content='width=device-width, initial-scale=1' />";
					
					// Favicon
					$master .= "<link rel='icon' type='image/png' href='img/glyphicons-77-headphones.png' />";
				
					// Page title
					$master .= "<title>" . $this->PAGE_TITLE . "</title>";
					
					// CSS
					foreach ($this->STYLESHEETS as $css) {
						$master .= "<link rel='stylesheet' href='" . $css . "' type='text/css'>";
					}
				$master .= "</head>";
				
				// Body
				$master .= "<body>";
				
					// Master
					$master .= "<div id='master'>";
				
			return $master;
		}
		
		private function getMasterClose () {
			$close = "";
			
					// close master tag
					$close .= "</div>";
					
					// Modal
					$close .= "<div id='music-modal' class='modal fade'>";
						$close .= "<div class='modal-dialog'>";
							$close .= "<div class='modal-content'>";
								$close .= "<div class='modal-header'>";
									$close .= "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>";
									$close .= "<h4 class='modal-title'>Modal title</h4>";
								$close .= "</div>";
								
								$close .= "<div class='modal-body'>";
									$close .= "<p>Hello, I'm a modal!</p>";
								$close .= "</div>";
								
								$close .= "<div class='modal-footer'>";
								$close .= "</div>";
							$close .= "</div>";
						$close .= "</div>";
					$close .= "</div>";
					
					// Javascript source files are put to bottom of body for improved page load time
					foreach ($this->SCRIPTS as $js) {
						$close .= "<script type='text/javascript' src='" . $js . "'></script>";
					}
				
				// close body tag
				$close .= "</body>";
			
			// close html tag
			$close .= "</html>";
			
			return $close;
		}
		
		public function getModalButtons($button_array) {
			$html = "";
			
			// cancel button
			if (in_array("cancel", $button_array)) {
				$html .= "<button type='button' class='btn btn-default modal-action-cancel' data-dismiss='modal'>Cancel</button>";
			}
			
			// save button
			if (in_array("cancel", $button_array)) {
				$html .= "<button type='button' class='btn btn-primary modal-action-save'>Save</button>";
			}
			
			return $html;
		}
		
		public function getRecordDetailsHtml($record_info) {
			$html = "";
			
			// data
			$song_list = $record_info["SongList"];
			
			// general information
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>General information</div>";
				
				$html .= "<div class='panel-body'>";
					$html .= "<div class='song-general-info col-sm-4'>";
						// record name
						$html .= "<div class='col-sm-3 bold'>Title:</div>";
						$html .= "<div class='col-sm-9'>" . $record_info["RecordName"] . "</div>";
						
						// artist name
						$html .= "<div class='col-sm-3 bold'>Artist:</div>";
						$html .= "<div class='col-sm-9'>" . getArtistLink($record_info["ArtistId"], $record_info["ArtistName"]) . "</div>";
						
						// record type
						$html .= "<div class='col-sm-3 bold'>Type:</div>";
						$html .= "<div class='col-sm-9' id='record-info-type'>" . $record_info["RecordTypeName"] . "</div>";
					$html .= "</div>";
					
					$html .= "<div class='song-general-info col-sm-7'>";
						// record duration
						$html .= "<div class='col-sm-3 bold'>Duration:</div>";
						$html .= "<div class='col-sm-9'>" . millisecondsToMinutes($record_info["SongLengthCount"]) . " min</div>";
					
						// record play count
						$html .= "<div class='col-sm-3 bold'>Played song total:</div>";
						$html .= "<div class='col-sm-9'>" . $record_info["SongPlayedCount"] . "</div>";
						
						// publish date
						$publish_date = $record_info["RecordPublishDate"];
						
						if ($publish_date != "0000-00-00") {
							$publish = new MysqlDate($publish_date);
							
							$html .= "<div class='col-sm-3 bold'>Published:</div>";
							$html .= "<div class='col-sm-9' id='record-info-publish'>" . $publish->convert2AustrianDate() . "</div>";
						}
					$html .= "</div>";
					
					// record details edit button
					$html .= "<div class='song-general-info col-sm-1'>";
						$html .= "<button type='button' id='btn-record-details-edit' class='btn btn-default pull-right' onclick=\"crudModal('" . $this->SAVE_RECORD_DETAILS . "', '" . $record_info["RecordId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></button>";
					$html .= "</div>";	
				$html .= "</div>";
			$html .= "</div>";
			
			// song list
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>Songs</div>";
				
				$html .= "<div class='panel-body'>";
					if (!empty($song_list)) {
						$html .= "<table class='table table-striped'>";
							$html .= "<thead>";
								$html .= "<tr>";
									$html .= "<th class='col-sm-1'>Track no.</th>";
									$html .= "<th class='col-sm-4'>Title</th>";
									$html .= "<th class='col-sm-1 hidden-xs'>Duration</th>";
									$html .= "<th class='col-sm-2 hidden-xs'>Rating</th>";
									$html .= "<th class='col-sm-2'>Count</th>";
									$html .= "<th class='col-sm-2'>Last listened</th>";
								$html .= "</tr>";
							$html .= "</thead>";
							
							$html .= "<tbody>";
								foreach ($song_list as $song) {
									$sid = $song["SongId"];
									
									$track_no = $song["SongTrackNo"] > 0 ? $song["SongTrackNo"] : "";
									
									$html .= "<tr>";
										$html .= "<td class='rank'>" . $track_no . "</td>";
										$html .= "<td>" . getSongLink($song["SongId"], $song["SongName"]) . "</td>";
										$html .= "<td class='hidden-xs'>" . millisecondsToMinutes($song["SongLength"]) . "</td>";
										$html .= "<td class='hidden-xs'>" . getStarsRating($song["SongRating"]) . "</td>";
										$html .= "<td>" . $song["PlayedCount"] . "</td>";
										$html .= "<td>" . $song["MostRecentPlayed"] . "</td>";
									$html .= "</tr>";
								}
							$html .= "</tbody>";
						$html .= "</table>";
					} else {
						$html .= "Sadly, there are no songs associated with this record!";
					}
				$html .= "</div>";
			$html .= "</div>";
			
			return $html;
		}
		
		public function getSettingsContent($mdb, $group = "general") {
			$html = "";
			
			switch ($group) {
				case "general":
					$html .= $this->getGeneralSettings($mdb);
					break;
					
				case "update":
					$html .= $group;
					break;
					
				case "configuration":
					$html .= $this->getConfigurationSettings($mdb);
					break;
					
				case "countries":
					$html .= $this->getCountrySettings($mdb);
					break;
					
				case "icons":
					$html .= $this->getIconSettings($mdb);
					break;
					
				case "device-types":
					$html .= $this->getDeviceTypeSettings($mdb);
					break;
					
				case "record-types":
					$html .= $this->getRecordTypeSettings($mdb);
					break;
					
				default:
					$html .= "Unknown group";
					break;
			}
			
			return $html;
		}
		
		/**
			Returns the content of the general settings tab
		*/
		private function getGeneralSettings($mdb) {
			$html = "";
			
			// MM DB modification
			$mm_db_modification = new UnixTimestamp($mdb->getConfig("mm_db_modification"));
			
			$html .= "<p><div>";
				$html .= "<div><strong>MediaMonkey database modification</strong></div>";
				$html .= "<div>" . $mm_db_modification->convert2AustrianDateTime() . "</div>";
			$html .= "</div>";
			
			// Successful update
			$successful_update = new UnixTimestamp($mdb->getConfig("successful_update"));
			
			$html .= "<p><div>";
				$html .= "<div><strong>Last successful update</strong></div>";
				$html .= "<div>" . $successful_update->convert2AustrianDateTime() . "</div>";
			$html .= "</div>";
			
			// Version
			$version_number = $mdb->getConfig("version_number");
			$version_string = $mdb->getConfig("version_string");
			
			$html .= "<p><div>";
				$html .= "<div><strong>Version</strong></div>";
				$html .= "<div>" . $version_string . " (#" . $version_number . ")</div>";
			$html .= "</div>";
			
			return $html;
		}
		
		/**
			Returns the content of the configuration settings tab
		*/
		private function getConfigurationSettings($mdb) {
			$html = "";
			
			// get all configuration values from the database
			$configs = $mdb->getConfigProperties();
			
			if (!is_null($configs)) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-4'>Property</th>";
							$html .= "<th class='col-sm-6'>Value</th>";
							$html .= "<th class='col-sm-2'><button type='button' class='btn btn-primary' onclick=\"crudModal('" . $this->SAVE_CONFIG . "')\"><span class='glyphicon glyphicon-plus'></span></button></th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody>";
						foreach ($configs as $config) {
							$html .= "<tr>";
								$html .= "<td>" . $config["ConfigProperty"] . "</td>";
								$html .= "<td>" . $config["ConfigValue"] . "</td>";
								$html .= "<td><a href='#' role='button' class='btn btn-default' onclick=\"crudModal('" . $this->SAVE_CONFIG . "', '" . $config["ConfigId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				$html .= "<p>Currently, there are no config values saved.";
				$html .= "<p>If you want, you can <a href='#' onclick=\"crudModal('" . $this->SAVE_CONFIG . "')\">add</a> one.";
			}
			
			return $html;
		}
		
		/**
			Returns the content of the icon settings tab
		*/
		private function getIconSettings($mdb) {
			$html = "";
			
			// get all icons from the database
			$icons = $mdb->getIcons();
			
			if (!is_null($icons)) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-1'>Icon</th>";
							$html .= "<th class='col-sm-9'>Name</th>";
							$html .= "<th class='col-sm-2'><button type='button' class='btn btn-primary' onclick=\"crudModal('" . $this->SAVE_ICON . "')\"><span class='glyphicon glyphicon-plus'></span></button></th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody>";
						foreach ($icons as $icon) {
							$html .= "<tr>";
								$html .= "<td>" . getIconRef($icon, $mdb->getConfig("img_path")) . "</td>";
								$html .= "<td>" . $icon["IconName"] . "</td>";
								$html .= "<td><a href='#' role='button' class='btn btn-default' onclick=\"crudModal('" . $this->SAVE_ICON . "', '" . $icon["IconId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				$html .= "<p>Currently, there are no icons saved.";
				$html .= "<p>If you want, you can <a href='#' onclick=\"crudModal('" . $this->SAVE_ICON . "')\">add</a> one.";
			}
			
			return $html;
		}
		
		/**
			Returns the content of the device settings tab
		*/
		private function getDeviceSettings($mdb) {
			$html = "";
			
			// get all devices from the database
			$devices = $mdb->getDevices();
			
			if (!is_null($devices)) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-1'>Type</th>";
							$html .= "<th class='col-sm-9'>Name</th>";
							$html .= "<th class='col-sm-2'><button type='button' class='btn btn-primary' onclick=\"crudModal('" . $this->SAVE_DEVICE . "')\"><span class='glyphicon glyphicon-plus'></span></button></th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody>";
						foreach ($devices as $device) {
							// get icon
							$icon = $mdb->getIcon($device["DeviceDeviceTypeIconId"]);
							
							// row class (active devices are highlighted)
							$highlight = $device["DeviceActive"] == 1 ? "info" : "";
							
							$html .= "<tr class='" . $highlight . "'>";
								$html .= "<td>" . getIconRef($icon, $mdb->getConfig("img_path"), $device["DeviceDeviceTypeName"]) . "</td>";
								$html .= "<td>" . $device["DeviceName"] . "</td>";
								$html .= "<td><a href='#' role='button' class='btn btn-default' onclick=\"crudModal('" . $this->SAVE_DEVICE . "', '" . $device["DeviceId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				$html .= "<p>Currently, there are no devices saved.";
				$html .= "<p>If you want, you can <a href='#' onclick=\"crudModal('" . $this->SAVE_DEVICE . "')\">add</a> one.";
			}
			
			return $html;
		}
		
		/**
			Returns the content of the device type settings tab
		*/
		private function getDeviceTypeSettings($mdb) {
			$html = "";
			
			// get all icons from the database
			$device_types = $mdb->getDeviceTypes();
			
			if (!is_null($device_types)) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-1'>Icon</th>";
							$html .= "<th class='col-sm-9'>Device type</th>";
							$html .= "<th class='col-sm-2'><button type='button' class='btn btn-primary' onclick=\"crudModal('" . $this->SAVE_DEVICE_TYPE . "')\"><span class='glyphicon glyphicon-plus'></span></button></th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody>";
						foreach ($device_types as $device_type) {
							// get icon
							$icon = $mdb->getIcon($device_type["DeviceTypeIconId"]);
							
							$html .= "<tr>";
								$html .= "<td>" . getIconRef($icon, $mdb->getConfig("img_path")) . "</td>";
								$html .= "<td>" . $device_type["DeviceTypeName"] . "</td>";
								$html .= "<td><a href='#' role='button' class='btn btn-default' onclick=\"crudModal('" . $this->SAVE_DEVICE_TYPE . "', '" . $device_type["DeviceTypeId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				$html .= "<p>Currently, there are no device types saved.";
				$html .= "<p>If you want, you can <a href='#' onclick=\"crudModal('" . $this->SAVE_DEVICE_TYPE . "')\">add</a> one.";
			}
			
			return $html;
		}
		
		/**
			Returns the content of the activities settings tab
		*/
		private function getActivitySettings($mdb) {
			$html = "";
			
			// get all activities from the database
			$activities = $mdb->getActivities();
			
			if (!is_null($activities)) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-2'>Tag</th>";
							$html .= "<th class='col-sm-8'>Name</th>";
							$html .= "<th class='col-sm-2'><button type='button' class='btn btn-primary' onclick=\"crudModal('" . $this->SAVE_ACTIVITY . "')\"><span class='glyphicon glyphicon-plus'></span></button></th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody>";
						foreach ($activities as $activity) {							
							$html .= "<tr>";
								$html .= "<td>" . getActivitySpan($activity) . "</td>";
								$html .= "<td>" . $activity["ActivityName"] . "</td>";
								$html .= "<td><a href='#' role='button' class='btn btn-default' onclick=\"crudModal('" . $this->SAVE_ACTIVITY . "', '" . $activity["ActivityId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				$html .= "<p>Currently, there are no activities saved.";
				$html .= "<p>If you want, you can <a href='#' onclick=\"crudModal('" . $this->SAVE_ACTIVITY . "')\">add</a> one.";
			}
			
			return $html;
		}
		
		/**
			Returns the content of the country settings tab
		*/
		private function getCountrySettings($mdb) {
			$html = "";
			
			// get all countries from the database
			$countries = $mdb->getCountries();
			
			if (!is_null($countries)) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-2'>Flag</th>";
							$html .= "<th class='col-sm-4'>Name</th>";
							$html .= "<th class='col-sm-4 hidden-xs'>Short</th>";
							$html .= "<th class='col-sm-2'><button type='button' class='btn btn-primary' onclick=\"crudModal('" . $this->SAVE_COUNTRY . "')\"><span class='glyphicon glyphicon-plus'></span></button></th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody>";
						foreach ($countries as $country) {							
							$html .= "<tr>";
								$html .= "<td>" . getCountryFlag($country) . "</td>";
								$html .= "<td>" . $country["CountryName"] . "</td>";
								$html .= "<td class='hidden-xs'>" . strtoupper($country["CountryShort"]) . "</td>";
								$html .= "<td><a href='#' role='button' class='btn btn-default' onclick=\"crudModal('" . $this->SAVE_COUNTRY . "', '" . $country["CountryId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				$html .= "<p>Currently, there are no countries saved.";
				$html .= "<p>If you want, you can <a href='#' onclick=\"crudModal('" . $this->SAVE_COUNTRY . "')\">add</a> one.";
			}
			
			return $html;
		}
		
		/**
			Returns the content of the record type settings tab
		*/
		private function getRecordTypeSettings($mdb) {
			$html = "";
			
			// get all icons from the database
			$record_types = $mdb->getRecordTypes();
			
			if (!is_null($record_types)) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-7'>Record type</th>";
							
							$html .= "<th class='col-sm-3'>";
								// re-order record types
								$html .= "<button type='button' id='btn-record-type-reorder' class='btn btn-primary' onclick='reorderRecordTypes()'><span class='glyphicon glyphicon-move'></span> Reorder</button>";
								
								// save or dismiss record types order
								$html .= "<span id='btn-record-type-control'>";
									// cancel
									$html .= "<button type='button' id='btn-record-type-cancel' class='btn btn-default'><span class='glyphicon glyphicon-remove'></span> Cancel</button>";
								
									// save
									$html .= "<button type='button' id='btn-record-type-save' class='btn btn-success'><span class='glyphicon glyphicon-ok'></span> Save</button>";
								$html .= "</span>";
							$html .= "</th>";
							
							$html .= "<th class='col-sm-2'>";
								// add new record type
								$html .= "<button type='button' id='btn-record-type-add' class='btn btn-primary' onclick=\"crudModal('" . $this->SAVE_RECORD_TYPE . "')\"><span class='glyphicon glyphicon-plus'></span></button>";
							$html .= "</th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody id='record-type-order'>";
						foreach ($record_types as $record_type) {
							$html .= "<tr>";
								$html .= "<td>" . $record_type["RecordTypeName"] . "</td>";
								$html .= "<td> </td>";
								$html .= "<td><a href='#' role='button' id='record-type-id-" . $record_type["RecordTypeId"] . "' class='btn btn-default record-type-edit' onclick=\"crudModal('" . $this->SAVE_RECORD_TYPE . "', '" . $record_type["RecordTypeId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				$html .= "<p>Currently, there are no record types saved.";
				$html .= "<p>If you want, you can <a href='#' onclick=\"crudModal('" . $this->SAVE_RECORD_TYPE . "')\">add</a> one.";
			}
			
			return $html;
		}
		
		public function getAdministrationContent($mdb, $group = "add-played") {
			$html = "";
			
			switch ($group) {
				case "add-played":
					$html .= $this->getAddPlayedAdministration($mdb);
					break;
					
				case "songs":
					$html .= $this->getSongAdministration($mdb);
					break;
					
				case "artists":
					$html .= $group;
					break;
					
				case "records":
					$html .= $group;
					break;
					
				case "played":
					$html .= $group;
					break;
					
				case "devices":
					$html .= $this->getDeviceSettings($mdb);
					break;
					
				case "activities":
					$html .= $this->getActivitySettings($mdb);
					break;
					
				default:
					$html .= "Unknown group";
					break;
			}
			
			return $html;
		}
		
		/**
			Returns the content of the add played song administration tab
		*/
		private function getAddPlayedAdministration($mdb) {
			$html = "";
			
			$html .= "<div class='row'>";
				// Top bar
				$html .= "<div>";
					// datepicker
					$html .= "<div class='col-sm-2'>";
						$html .= "<div class='bold'>Date</div>";
						$html .= "<div><input type='text' id='played-date' class='form-control date-picker' placeholder='Date' value='" . date("d.m.Y") . "' /></div>";
					$html .= "</div>";
					
					// device
					$html .= "<div class='col-sm-3'>";
						$html .= "<div class='bold'>Device</div>";
						$html .= "<div><select class='selectpicker form-control' id='administration-device' name='administration-device'>";
							// display all options
							$devices = $mdb->getDevices();
							
							$default_device = $mdb->getConfig("default_device");
							
							// keep track of active state to add a divider between active and non-active devices
							$dev_active = true;
							
							foreach ($devices as $device) {
								$icon = $mdb->getIcon($device["DeviceDeviceTypeIconId"]);
								
								if ($device["DeviceActive"] != $dev_active) {
									$dev_active = $device["DeviceActive"];
									$html .= "<option data-divider='true'></option>";
								}
								
								$html .= "<option value='" . $device["DeviceId"] . "' data-icon='" . $icon["IconPath"] . "' " . compareOption($default_device, $device["DeviceId"]) . ">" . $device["DeviceName"] . "</option>";
							}
						$html .= "</select></div>";
					$html .= "</div>";
					
					// activity
					$html .= "<div class='col-sm-7'>";
						$html .= "<div class='bold'>Activity</div>";
						$html .= "<div><select class='selectpicker form-control' id='administration-activity' name='administration-activity'>";
							// display all options
							$activities = $mdb->getActivities();
							
							// get default web activity
							$default_activity = $mdb->getConfig("default_web_activity");
							
							foreach ($activities as $activity) {
								$html .= "<option value='" . $activity["ActivityId"] . "' data-content=\"<span class='label label-big label-" . $activity["ActivityColor"] . "'>#" . $activity["ActivityName"] . "</span> \" " . compareOption($default_activity, $activity["ActivityId"]) . ">"  . "</option>";
							}
						$html .= "</select></div>";
					$html .= "</div>";
				$html .= "</div>";
			$html .= "</div>";
				
			// input form
			$html .= "<div class='row'>";
				$html .= "<form id='add-played-song-form'>";
					$html .= $this->getAddPlayedSongLine("add-played-song-1", true);
				$html .= "</form>";
			$html .= "</div>";
			
			// add more songs and submit
			$html .= "<div class='row'>";
				$html .= "<div class='form-group'>";
					$html .= "<div id='add-played-song-submit' class='col-sm-12'>";
						$html .= "<div class='pull-right'>";
							$html .= "<span><button type='button' id='add-played-song-add' class='btn btn-info'>Add song</button></span> ";
							$html .= "<span><button type='button' id='add-played-song-save' class='btn btn-success'>Save</button></span>";
						$html .= "</div>";
					$html .= "</div>";
				$html .= "</div>";
			$html .= "</div>";
			
			return $html;
		}
		
		public function getAddPlayedSongLine($id, $fill_time = false) {
			$time = $fill_time ? date("H:i") : "";
			
			return "<div id='" . $id . "-container' class='form-group add-played-song-div'>
						<div class='col-sm-2'>
							<input type='text' class='form-control add-played-song-time' placeholder='Time' value='" . $time . "' />
						</div>
						
						<div class='add-played-song-input col-sm-10'>
							<input type='text' id='" . $id . "' class='form-control add-played-song' placeholder='Choose song' />
						</div>
						
						<div class='add-played-song-display col-sm-10'></div>
					</div>";
		}
		
		/**
			Returns the content of the song administration tab
		*/
		private function getSongAdministration($mdb) {
			$html = "";
			
			// Top bar with search field
			$html .= "<div class='row'>";
				$html .= "<input type='text' id='song-admin-search' class='autofocus' />";
			$html .= "</div>";
			
			// result div
			$html .= "<div class='row'>";
				
			$html .= "</div>";
			
			return $html;
		}
	}