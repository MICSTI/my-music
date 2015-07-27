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
		
		// Mobile folder
		private static $mobile_folder = "upload/";
		
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

		public function getIndexHTML ($main = "") {
			return $this->frontend->getIndex($main);
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
					
				default:
					break;
			}
			
			return $success;
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
			$mobile_folder = $this->getMobileFolder();
			
			// Search for mobile xml files
			$mobile_files = $this->getMobileFiles();
			
			foreach ($mobile_files as $file) {
				$mobfile = $mobile_folder . $file;
			
				// Import file to database
				$success = $this->importFromMobileFile($mobfile);
			
				// Move imported file to storage folder if import was successful
				if ($success) {
					rename($mobfile, "storage/" . $file);
				}
			}
		}
		
		/**
			Determines all mobile.{UNIX_TIMESTAMP}.xml files in the mobile folder and returns them in an array.
			If no files are found, and empty array is returned.
		*/
		private function getMobileFiles () {
			$mobile_files = array();
		
			$mobile_folder = $this->getMobileFolder();
			
			$file_name_start = "mobile.";
			$file_name_end = ".xml";
			
			$files = scandir($mobile_folder);
			
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
			For the very first update, use initialImport().
		*/
		public function updateDatabase () {		
			if ($this->checkForDatabaseUpdate()) {
				// Get start time
				$time_start = microtime(true);
			
				// Import songs
				$this->importSongs();
				
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
				
				$this->getMDB()->addLog(__FUNCTION__, "success", "performed update in " . $time . " seconds, new database file modification time " . $db_modification_mysql . " (" . $db_modification . "), new current played id " . $last_imported);
			} else {
				$this->getMDB()->addLog(__FUNCTION__, "info", "didn't perform database update because no newer file exists!");
			}
			
			// Import mobile files
			$this->importMobile();
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
		
		private function getMobileFolder () {
			return MusicController::$mobile_folder;
		}
	}