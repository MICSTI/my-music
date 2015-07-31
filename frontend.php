<?php
	require_once('page.php');

	class frontend {
		private $page;
		
		private $PAGE_TITLE = "myMusic";
		
		private $STYLESHEETS = array(
								"http://fonts.googleapis.com/css?family=Oxygen",
								"external/bootstrap/css/bootstrap.min.css",
								"external/bootstrap-select/bootstrap-select.min.css",
								"auto_complete.css",
								"datepicker.css",
								"mymusic.css"
							);
		
		private $SCRIPTS = array(
								"external/jquery/jquery-2.1.4.min.js",
								"external/bootstrap/js/bootstrap.min.js",
								"external/bootstrap-select/bootstrap-select.min.js",
								"external/notifiy/notify-combined.min.js",
								"auto_complete.js",
								"bootstrap-datepicker.js",
								"util.js",
								"mymusic.js"
							);
							
		// Action ids
		private $SAVE_ICON = "TkTiW5a3";
		private $SAVE_ACTIVITY = "pXBciVn6";
		private $SAVE_DEVICE = "VnpguEAw";
		private $SAVE_DEVICE_TYPE = "21Uww2Uj";
		
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
						$menu .= "<li><a href='#'>Input</a></li>";
						$menu .= "<li><a href='#'>Concerts</a></li>";
						$menu .= "<li class='" . $this->getActiveText("settings", $selected) . "'><a href='settings.php'>Settings</a></li>";
					$menu .= "</ul>";
					
					// Search field
					$menu .= "<form id='form-search' role='search' class='navbar-form navbar-left'>";
						$menu .= "<div class='form-group'>";
							$menu .= "<input type='text' id='searchfield' class='form-control' placeholder='Search for songs or artists' size='34' autocomplete='off' />";
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
						$html .= "<div class='col-sm-3 bold'>Title:</div>";
						$html .= "<div class='col-sm-9'>" . $record_info["RecordName"] . "</div>";
						
						$html .= "<div class='col-sm-3 bold'>Artist:</div>";
						$html .= "<div class='col-sm-9'>" . getArtistLink($record_info["ArtistId"], $record_info["ArtistName"]) . "</div>";
					$html .= "</div>";
					
					$html .= "<div class='song-general-info col-sm-8'>";
						$html .= "<div class='col-sm-3 bold'>Duration:</div>";
						$html .= "<div class='col-sm-9'>" . millisecondsToMinutes($record_info["SongLengthCount"]) . " min</div>";
					
						$html .= "<div class='col-sm-3 bold'>Played song total:</div>";
						$html .= "<div class='col-sm-9'>" . $record_info["SongPlayedCount"] . "</div>";
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
										$html .= "<td class='hidden-xs'>" . $song["SongRating"] . "</td>";
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
					
				case "activities":
					$html .= $this->getActivitySettings($mdb);
					break;
					
				case "icons":
					$html .= $this->getIconSettings($mdb);
					break;
					
				case "devices":
					$html .= $this->getDeviceSettings($mdb);
					break;
					
				case "device-types":
					$html .= $this->getDeviceTypeSettings($mdb);
					break;
					
				case "record-types":
					$html .= $group;
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
			Returns the content of the icon settings tab
		*/
		private function getIconSettings($mdb) {
			$html = "";
			
			// get all icons from the database
			$icons = $mdb->getIcons();
			
			$html .= "<table class='table table-striped'>";
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
			
			return $html;
		}
		
		/**
			Returns the content of the device settings tab
		*/
		private function getDeviceSettings($mdb) {
			$html = "";
			
			// get all devices from the database
			$devices = $mdb->getDevices();
			
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
			
			// adds the tooltip initialization to the body
			$html .= getTooltipReadyFunction();
			
			return $html;
		}
		
		/**
			Returns the content of the device type settings tab
		*/
		private function getDeviceTypeSettings($mdb) {
			$html = "";
			
			// get all icons from the database
			$device_types = $mdb->getDeviceTypes();
			
			$html .= "<table class='table table-striped'>";
				$html .= "<thead>";
					$html .= "<tr>";
						$html .= "<th class='col-sm-1'> </th>";
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
				$html .= "<table class='table table-striped'>";
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
								$html .= "<td><span class='label label-big label-" . $activity["ActivityColor"] . "'>#" . $activity["ActivityName"] . "</span></td>";
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
	}