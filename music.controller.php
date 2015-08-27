<?php
	class MusicController {
		// PDO database connection
		private $dbc;
		
		// Main MySQL music database
		private $mdb;
		
		// MediaMonkey database connection
		private $mmdb;
		
		// Mobile database (less detailed than main MySQL database, but contains same basic data)
		private $mobile_db;
		
		// Web frontend
		private $frontend;
	
		// File name of songs file
		private static $songfile = "files/songs.xml";
		
		// File name of played file
		private static $playedfile = "files/played.xml";
		
		// Upload folder
		private static $upload_folder = "upload/";
		
		function __construct () {
			$this->dbc = new DatabaseConnection();
			
			$this->mobile_db = new MobileDatabaseConnection();
			
			$this->mdb = new MusicDB($this->dbc->getDBC(), $this->mobile_db->getMDBC());
			
			$this->mmdb = new MMDB($this->mdb);
			
			$this->frontend = new frontend();
		}
		
		/**
			Return MediaMonkey database connection
		*/
		public function getMMDB () {
			return $this->mmdb;
		}
		
		/**
			Returns main music database connection
		*/
		public function getMDB () {
			return $this->mdb;
		}
		
		/**
			Returns reference to frontend instance
		*/
		public function getFrontend() {
			return $this->frontend;
		}

		public function getIndexHTML ($main = "", $selected = "") {
			return $this->frontend->getIndex($main, $selected);
		}
		
		/**
			Creates an xml file for the specified type
		*/
		public function createXML ($type, $param) {
			$success = false;
		
			switch ($type) {
				case "songs":
					$success = $this->getMMDB()->getXMLSongData($param)->save($this->getSongFile());
					
					break;
					
				case "played":
					$success = $this->getMMDB()->getXMLPlayedData($param)->save($this->getPlayedFile());
					
					break;
					
				default:
					break;
			}
			
			return $success;
		}
		
		public function importDesktopFile($desktop_file) {
			return $this->getMMDB()->importDesktop($desktop_file);
		}
		
		public function importFromSongFile () {
			$this->getMMDB()->importSongs($this->getSongFile());
		}
		
		public function importFromPlayedFile () {
			$last_played_id = $this->getMMDB()->importPlayed($this->getPlayedFile());
			
			return $last_played_id;
		}
		
		public function importFromMobileFile ($mobile_file) {
			$success = $this->getMMDB()->importMobile($mobile_file);
			
			if ($success) {
				$this->getMDB()->addLog(__FUNCTION__, "success", "imported mobile file '" . $mobile_file . "'");
			} else {
				$this->getMDB()->addLog(__FUNCTION__, "error", "tried to import mobile file '" . $mobile_file . "'");
			}
			
			return $success;
		}
		
		public function importFromDesktopFile ($desktop_file) {
			$status = $this->getMMDB()->importDesktop($desktop_file);
			
			if ($status["success"]) {
				$this->getMDB()->addLog(__FUNCTION__, "success", "imported desktop file '" . $desktop_file . "'");
			} else {
				$this->getMDB()->addLog(__FUNCTION__, "error", "tried to import desktop file '" . $desktop_file . "'");
			}
			
			return $status;
		}
		
		/**
			Creates songs xml and imports it to the database
			Only tupel that are newer than the last modification time are imported
		*/
		private function importSongs () {
			$modified_timestamp = new UnixTimestamp($this->getMDB()->getConfig('mm_db_modification'));
		
			$this->createXML("songs", $modified_timestamp->convert2MMDate());
			
			$this->importFromSongFile();
			
			// Copy imported file to storage folder
			$song_file = $this->getSongFile();
			
			if (file_exists($song_file)) {
				copy($song_file, "storage/" . basename($song_file, ".xml") . "." . mktime() . "." . pathinfo($song_file, PATHINFO_EXTENSION));
			}
			
			// Save database file modification time
			$modification = filemtime($this->getMMDB()->getMMFile());
			
			$this->getMDB()->setConfig('mm_db_modification', $modification);
		}
		
		/**
			Creates played xml and imports it to the database
			Only tupel that are newer than the last imported played id are imported
		*/
		private function importPlayed () {
			$last_imported = $this->getMDB()->getConfig('last_imported_played_id');
			
			$this->createXML("played", $last_imported);
			
			$last_played_id = $this->importFromPlayedFile();
			
			// Copy imported file to storage folder
			$played_file = $this->getPlayedFile();
			
			if (file_exists($played_file)) {
				copy($played_file, "storage/" . basename($played_file, ".xml") . "." . mktime() . "." . pathinfo($played_file, PATHINFO_EXTENSION));
			}
			
			if ($last_played_id > 0) {
				$this->getMDB()->setConfig('last_imported_played_id', $last_played_id);
			}
		}
		
		/**
			Looks for mobile xml files and imports them to the database
		*/
		private function importMobile () {
			$upload_folder = $this->getUploadFolder();
			
			// Search for mobile xml files
			$mobile_files = $this->getMobileFiles();
			
			foreach ($mobile_files as $file) {
				$mobfile = $upload_folder . $file;
			
				// Import file to database
				$success = $this->importFromMobileFile($mobfile);
			
				// Move imported file to storage folder if import was successful
				if ($success) {
					rename($mobfile, "storage/" . $file);
				}
			}
		}
		
		/**
			Gets all update files (desktop and mobile) and returns them in one array, sorted by timestamp
		*/
		public function getUpdateFiles() {
			$upload_files = array();
			
			$desktop_files = $this->getDesktopFiles();
			$mobile_files = $this->getMobileFiles();
			
			$desktop_count = count($desktop_files);
			$mobile_count = count($mobile_files);
			
			$desktop_index = 0;
			$mobile_index = 0;
			
			while (($desktop_index + $mobile_index) < ($desktop_count + $mobile_count)) {
				if ($desktop_index == $desktop_count) {
					// take mobile file
					array_push($upload_files, $mobile_files[$mobile_index]);
					
					$mobile_index++;
				} else if ($mobile_index == $mobile_count) {
					// take desktop file
					array_push($upload_files, $desktop_files[$desktop_index]);
					
					$desktop_index++;
				} else {
					// get next desktop and mobile timestamp
					$next_desktop_timestamp = $this->getTimestampFromFilename($desktop_files[$desktop_index]);
					$next_mobile_timestamp = $this->getTimestampFromFilename($mobile_files[$mobile_index]);
					
					if ($next_desktop_timestamp < $next_mobile_timestamp) {
						// take desktop file
						array_push($upload_files, $desktop_files[$desktop_index]);
					
						$desktop_index++;
					} else {
						// take mobile file
						array_push($upload_files, $mobile_files[$mobile_index]);
					
						$mobile_index++;
					}
				}
			}
			
			return $upload_files;
		}
		
		/**
			Gets the timestamp portion from a filename ("desktop.12345678.xml" -> returns 12345678)
		*/
		public function getTimestampFromFilename($filename) {
			$needle = ".";
			
			$first_pos = strpos($filename, $needle);
			
			$cut_filename = substr($filename, $first_pos + 1);
			
			$second_pos = strpos($cut_filename, $needle);
			
			$timestamp = substr($cut_filename, 0, $second_pos);
			
			return $timestamp;
		}
		
		/**
			Gets the file type portion from a filename ("desktop.12345678.xml" -> returns "desktop")
		*/
		public function getTypeFromFilename($filename) {
			return substr($filename, 0, strpos($filename, "."));
		}
		
		/**
			Determines all mobile.{UNIX_TIMESTAMP}.xml files in the upload folder and returns them in an array.
			If no files are found, and empty array is returned.
		*/
		private function getMobileFiles () {
			$mobile_files = array();
		
			$upload_folder = $this->getUploadFolder();
			
			$file_name_start = "mobile.";
			$file_name_end = ".xml";
			
			$files = scandir($upload_folder);
			
			foreach ($files as $file) {
				if ($this->startsWith($file, $file_name_start) AND $this->endsWith($file, $file_name_end)) {
					array_push($mobile_files, $file);
				}
			}
			
			return $mobile_files;
		}
		
		/**
			Determines all desktop.{UNIX_TIMESTAMP}.xml files in the upload folder and returns them in an array.
			If no files are found, and empty array is returned.
		*/
		private function getDesktopFiles () {
			$mobile_files = array();
		
			$upload_folder = $this->getUploadFolder();
			
			$file_name_start = "desktop.";
			$file_name_end = ".xml";
			
			$files = scandir($upload_folder);
			
			foreach ($files as $file) {
				if ($this->startsWith($file, $file_name_start) AND $this->endsWith($file, $file_name_end)) {
					array_push($mobile_files, $file);
				}
			}
			
			return $mobile_files;
		}
		
		/**
			Performs an initial import of the whole MediaMonkey database file
		*/
		public function initialImport () {
			// Get start time
			$time_start = microtime(true);
		
			// Truncates all non-config tables for clean initial import
			//$this->getMDB()->truncateTables();
			
			// Resets config values to default
			//$this->getMDB()->setConfig('last_imported_played_id', 0);
			//$this->getMDB()->setConfig('mm_db_modification', '2000-01-01 12:00');
			//$this->getMDB()->setConfig('successful_update', 0);
			
			// Initialize mobile database
			//$this->getMDB()->createMobileDatabase();
			
			// Import songs
			//$this->importSongs();
			
			// Import played
			$this->importPlayed();
			
			// Log update info
			$db_modification = $this->getMDB()->getConfig('mm_db_modification');
			$last_imported = $this->getMDB()->getConfig('last_imported_played_id');
			
			$db_modification_unix = new UnixTimestamp($db_modification);
			$db_modification_mysql = $db_modification_unix->convert2MysqlDateTime();
			
			$this->getMDB()->setConfig('successful_update', mktime());
			
			// Get end time
			$time_end = microtime(true);
			
			$time = $time_end - $time_start;
			
			$this->getMDB()->addLog(__FUNCTION__, "success", "performed initial import in " . $time . " seconds, new database file modification time " . $db_modification_mysql . " (" . $db_modification . "), new current played id " . $last_imported);
		}
		
		/**
			Performs a full update of the database.
		*/
		public function updateDatabase() {
			$last_played_id = -1;
			$mm_db_modification = -1;
			
			// response array
			$response = array();
			
			// suggestions response element
			$suggestions = array();
			
			// added response element
			$added = array();
			
			// updated response element
			$updated = array();
			
			// upload folder
			$upload_folder = $this->getUploadFolder();
			
			// get start time
			$time_start = microtime(true);
			
			// get files
			$update_files = $this->getUpdateFiles();
			
			foreach ($update_files as $update_file) {
				// get file type
				$type = $this->getTypeFromFilename($update_file);
				
				// get file path
				$file_path = $upload_folder . $update_file;
				
				switch ($type) {
					case "desktop":
						// Import file to database
						$status = $this->importFromDesktopFile($file_path);
						 
						// push status data objects to the end of their arrays
						$suggestions = array_merge($suggestions, $status["suggestions"]);
						$added = array_merge($added, $status["added"]);
						$updated = array_merge($updated, $status["updated"]);
						
						if ($status["last_played_id"] > $last_played_id)
							$last_played_id = $status["last_played_id"];
						
						if ($status["mm_db_modification"] > $mm_db_modification)
							$mm_db_modification = $status["mm_db_modification"];
						
						// Move imported file to storage folder if import was successful
						if ($status["success"]) {
							rename($file_path, "storage/" . $update_file);
						}
					
						break;
						
					case "mobile":
						// Import file to database
						$success = $this->importFromMobileFile($file_path);
					
						// Move imported file to storage folder if import was successful
						if ($success) {
							rename($file_path, "storage/" . $update_file);
						}
						
						break;
						
					default:
						break;
				}
			}
			
			// get end time
			$time_end = microtime(true);
			
			// get execution time
			$time = $time_end - $time_start;
			
			// write config values to database
			if ($last_played_id > 0) {
				$this->getMDB()->setConfig('last_imported_played_id', $last_played_id);
			}
			
			if ($mm_db_modification > 0) {
				$this->getMDB()->setConfig('mm_db_modification', $mm_db_modification);
			}
			
			if ($last_played_id > 0 AND $mm_db_modification > 0) {
				$successful_update = mktime();
				$this->getMDB()->setConfig('successful_update', $successful_update);
				
				$unix_mm_db_modification = new UnixTimestamp($mm_db_modification);
				$austrian_mm_db_modification = $unix_mm_db_modification->convert2AustrianDateTime();
				
				$log_message = "performed update in " . $time . " seconds, new database file modification time " . $austrian_mm_db_modification . " (" . $mm_db_modification . "), new current played id " . $last_played_id;
			} else {
				// no files or only mobile files were imported
				$log_message = "performed update in " . $time . " seconds, no new database file modification time or new last played id.";
			}
			
			// add log entry
			$this->getMDB()->addLog(__FUNCTION__, "success", $log_message);
			
			// set response success
			$response["success"] = true;
			
			// add status data objects
			$response["suggestions"] = $suggestions;
			$response["added"] = $added;
			$response["updated"] = $updated;
			
			return $response;
		}

		/**
			Creates a html table in a string.
			Header information must be passed in an array (options: name [string], display [string], sortable [boolean], order [int], data-align [string], header-align [string] - defaults to center)
			Data information must be passed as a PDOStatement::fetchAll result
		*/
		public function getTableFromArray ($id, $header, $data) {
			$html = "";
			
			// Array with column names
			$column_names = array();
			
			// Array with display names
			$display_names = array();
			
			// parse header array
			foreach ($header as $head) {
				array_push($column_names, $head['name']);
				array_push($display_names, $head['display']);
			}
			
			$html .= "<table id='" . $id . "' class='display' cellspacing='0'>";
				// Header
				$html .= "<thead>";
					$html .= "<tr><th>" . implode("</th><th>", $display_names) . "</th></tr>";
				$html .= "</thead>";
				
				// Body
				$html .= "<tbody>";
					foreach ($data as $element) {
						$html .= "<tr>";
							foreach ($column_names as $column) {
								$html .= "<td>" . $element[$column] . "</td>";
							}
						$html .= "</tr>";
					}
				$html .= "</tbody>";
			$html .= "</table>";

			return $html;
		}
		
		/**
			Returns all column names from an array that are not numeric.
			Useful for getting the column names from a PDO::fetchAll statement
		*/
		private function getNonNumericColumnNames ($array) {
			$return = array();
		
			foreach ($array as $column) {
				if (!is_numeric($column)) {
					array_push($return, $column);
				}
			}
			
			return $return;
		}
		
		/**
			Checks if a newer version of the database is available
		*/
		public function checkForDatabaseUpdate () {
			$last_modification = $this->getMDB()->getConfig('mm_db_modification');
			
			$db_modification = filemtime($this->getMMDB()->getMMFile());
			
			if ($db_modification > $last_modification) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Correct the song added dates for either one specific song or all songs in the database.
			If the id is omitted or lower than zero, all songs are corrected.
		*/
		public function correctSongAddedDate($id = -1) {
			if ($id > 0) {
				// single mode
				
				// get matching mmid
				$mmid = $this->getMDB()->getMMIdfromSid($id);
				
				$songs = $this->getMMDB()->correctSongAddedDate($mmid);
			} else {
				// all mode
				
				$songs = $this->getMMDB()->correctSongAddedDate();
			}
			
			$this->getMDB()->correctSongAddedDate($songs);
		}
		
		public function startsWith ($haystack, $needle) {
			return $needle === "" || strpos($haystack, $needle) === 0;
		}
		
		public function endsWith ($haystack, $needle) {
			return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
		}

		
		public function getSongInfo ($sid) {
			$song_info = $this->getMDB()->getSong($sid);
			
			return $song_info;
		}
		
		public function getMMDate ($date) {
			$to_compare = new DateTime('1900-01-01');
			
			return $to_compare->diff($date, true)->format('%a') + 2;
		}
		
		private function getSongFile () {
			return MusicController::$songfile;
		}
		
		private function getPlayedFile () {
			return MusicController::$playedfile;
		}
		
		private function getUploadFolder () {
			return MusicController::$upload_folder;
		}
	}