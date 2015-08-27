<?php
	class MMDB extends SQLite3 {
		// File name of Media Monkey database
		private static $mmfile = 'data/MM.DB';
		
		// MusicDB connection
		private $music_db;
	
		/**
			A database connection must be passed to the model
			
			@param object	$db		A PDO database connection
		*/
		function __construct ($music_db) {
			try {
				$this->open($this->getMMFile());
				$this->setMusicDB($music_db);
			} catch (Exception $e) {
				exit("MediaMonkey database file could not be read.");
			}
		}
		
		/**
			Returns the xml structure for all songs in the database
		*/
		public function getXMLSongData ($modified) {
			// strip input from code tags
			$modified = strip_tags($modified);
			
			// calculate floor of modified
			$modified = floor($modified);
		
			// Create empty DOM
			$xml = $this->getEmptyDOM();
			
			// Create song parent element
			$xml_songs = $xml->createElement("songs");
		
			$result = $this->query("SELECT * FROM Songs WHERE FileModified >= " . $modified);
			
			while ($song = $result->fetchArray()) {
				$xml_song = $xml->createElement("song");
				
				$id = $xml->createElement('mmid', $song['ID']);
				$song_title = $xml->createElement('name', htmlspecialchars(trim($song['SongTitle'])));
				$record = $xml->createElement('record', htmlspecialchars(trim($song['Album'])));
				$disc_no = $xml->createElement('discno', $song['DiscNumber']);
				$track_no = $xml->createElement('trackno', $song['TrackNumber']);
				$rating = $xml->createElement('rating', $song['Rating']);
				$bitrate = $xml->createElement('bitrate', $song['Bitrate']);
				$date_added = $xml->createElement('added', $song['DateAdded']);
				$song_length = $xml->createElement('length', $song['SongLength']);
				
				// Make sure artist information is used (Artist has higher priority, but if left blank, AlbumArtist will be used instead)
				$cArtist = trim($song['Artist']);
				
				if (empty($cArtist)) {
					$album_artist = trim($song['AlbumArtist']);
					
					$cArtist = $album_artist;
				}
				
				$artist = $xml->createElement('artist', htmlspecialchars($cArtist));
				
				$xml_song->appendChild($id);
				$xml_song->appendChild($song_title);
				$xml_song->appendChild($artist);
				$xml_song->appendChild($record);
				$xml_song->appendChild($disc_no);
				$xml_song->appendChild($track_no);
				$xml_song->appendChild($rating);
				$xml_song->appendChild($bitrate);
				$xml_song->appendChild($date_added);
				$xml_song->appendChild($song_length);
				
				$xml_songs->appendChild($xml_song);
			}
			
			$xml->appendChild($xml_songs);
			
			return $xml;
		}
		
		/**
			Dummy function for temporarily getting the play history for a certain date
		*/
		public function getPlayedForDate ($date) {
			$return = "<table>";
		
			$result = $this->query("SELECT pl.PlayDate, pl.UTCOffset, so.SongTitle, so.Artist, so.Album FROM Played pl INNER JOIN Songs so ON so.ID = pl.IDSong WHERE pl.PlayDate LIKE '" . $date . "%' ORDER BY pl.PlayDate ASC");
			
			while ($play = $result->fetchArray()) {
				$playdate = $play['PlayDate'] + $play['UTCOffset'];
			
				$return .= "<tr>";
					$return .= "<td>" . $this->convertMMDateTime($playdate) . "</td>";
					$return .= "<td>" . $play['SongTitle'] . "</td>";
					$return .= "<td>" . $play['Artist'] . "</td>";
					$return .= "<td>" . $play['Album'] . "</td>";
				$return .= "</tr>";
			}
			
			$return .= "</table>";
			
			return $return;
		}
		
		public function getTimeSpanStat ($threshold_low, $threshold_high) {
			$return = "<table>";
		
			$result = $this->query("SELECT so.SongTitle, so.Artist, so.Album, COUNT(so.ID) AS 'Summe' FROM Played pl INNER JOIN Songs so ON so.ID = pl.IDSong WHERE (CAST(pl.PlayDate AS INT) + CAST(pl.UTCOffset AS INT)) >= " . $threshold_low . " AND (CAST(pl.PlayDate AS INT) + CAST(pl.UTCOffset AS INT)) <= " . $threshold_high . " GROUP BY so.ID ORDER BY Summe DESC");
			
			while ($play = $result->fetchArray()) {
				$return .= "<tr>";
					$return .= "<td>" . $play['SongTitle'] . "</td>";
					$return .= "<td>" . $play['Artist'] . "</td>";
					$return .= "<td>" . $play['Album'] . "</td>";
					$return .= "<td>" . $play['Summe'] . "</td>";
				$return .= "</tr>";
			}
			
			$return .= "</table>";
			
			return $return;
		}
		
		/**
			Returns the xml structure for all played statistics in the database
		*/
		public function getXMLPlayedData ($last_imported) {
			// strip input from code tags
			$last_imported = strip_tags($last_imported);
		
			// Create empty DOM
			$xml = $this->getEmptyDOM();
			
			// Create played parent element
			$xml_played = $xml->createElement("played");
			
			// Get current mm db devid id
			$mmdb_devid = $this->music_db->getConfig('mm_db_devid');
			
			$result = $this->query("SELECT * FROM Played WHERE IDPlayed > " . $last_imported);
			
			while ($play = $result->fetchArray()) {
				$xml_play = $xml->createElement("play");
				
				$play_date = $play['PlayDate'];
				$utc_offset = $play['UTCOffset'];
				
				$pldid = $xml->createElement('pldid', $play['IDPlayed']);
				$mmid = $xml->createElement('mmid', $play['IDSong']);
				$devid = $xml->createElement('devid', $mmdb_devid);
				$timestamp = $xml->createElement('timestamp', ($play_date + $utc_offset));
				
				$xml_play->appendChild($pldid);
				$xml_play->appendChild($mmid);
				$xml_play->appendChild($devid);
				$xml_play->appendChild($timestamp);
				
				$xml_played->appendChild($xml_play);
			}
			
			$xml->appendChild($xml_played);
			
			return $xml;
		}
		
		public function importDesktop($desktop_file) {
			// status return array
			$status = array();
			
			// get x path
			$xpath = $this->getXPath($desktop_file);
			
			// get songs
			$songs = $xpath->query("songs/song");
			
			// process songs
			$songs_status = $this->writeSongs($songs);
			
			// get playeds
			$playeds = $xpath->query("playeds/played");
			
			// process playeds
			$this->writePlayeds($playeds);
			
			// fill status array
			$configs = $xpath->query("config");
			
			foreach ($configs as $config) {
				// add db modification and last played id to status object
				$status["mm_db_modification"] = $config->getElementsByTagName('mm_db_modification')->item(0)->nodeValue;
				$status["last_played_id"] = $config->getElementsByTagName('last_imported_played_id')->item(0)->nodeValue;
			}

			$status["success"] = true;
			
			// append song status data objects
			$status["suggestions"] = $songs_status["suggestions"];
			$status["added"] = $songs_status["added"];
			$status["updated"] = $songs_status["updated"];
			
			return $status;
		}
		
		/**
			Imports all songs from the songs.xml file
			It is checked if a song with matching MM id already exists,
			if so the data for the song is overwritten,
			otherwise it is added to the database.
		*/
		public function importSongs ($file) {
			// parses song.xml file
			$songs = $this->parseXML($file, "song");
			
			// writes songs to database
			$this->writeSongs($songs);
		}
		
		/**
			Imports all playeds from the played.xml file
		*/
		public function importPlayed ($file) {
			// parses played.xml file
			$playeds = $this->parseXML($file, "play");
			
			// writes playeds to database
			return $this->writePlayeds($playeds);
		}
		
		/**
			Imports all playeds from the mobile.xml file
		*/
		public function importMobile ($file) {
			// parses mobile.xml file
			$mobile = $this->parseXML($file, "play");
			
			// writes mobile to database
			return $this->writeMobile($mobile);
		}
		
		private function writeSongs ($songs) {
			$status = array();
			
			$suggestions = array();
			$new = array();
			$updated = array();
			
			foreach ($songs as $song) {
				// Get element node values from song element
				$name = $song->getElementsByTagName('name')->item(0)->nodeValue;
				$artist = $song->getElementsByTagName('artist')->item(0)->nodeValue;
				$record = $song->getElementsByTagName('record')->item(0)->nodeValue;
				$length = $song->getElementsByTagName('length')->item(0)->nodeValue;
				$bitrate = $song->getElementsByTagName('bitrate')->item(0)->nodeValue;
				$mmid = $song->getElementsByTagName('mmid')->item(0)->nodeValue;
				$discno = $song->getElementsByTagName('discno')->item(0)->nodeValue;
				$trackno = $song->getElementsByTagName('trackno')->item(0)->nodeValue;
				$added = $song->getElementsByTagName('added')->item(0)->nodeValue;
				$rating = $song->getElementsByTagName('rating')->item(0)->nodeValue;
				
				// Remove code tags
				$name = htmlspecialchars_decode(strip_tags($name));
				$artist = htmlspecialchars_decode(strip_tags($artist));
				$record = htmlspecialchars_decode(strip_tags($record));
				$length = strip_tags($length);
				$bitrate = strip_tags($bitrate);
				$mmid = strip_tags($mmid);
				$discno = strip_tags($discno);
				$trackno = strip_tags($trackno);
				$added = strip_tags($added);
				$rating = strip_tags($rating);
				
				// Convert MM date
				$added_mm = new MMDate($added);
				$added_mysql = $added_mm->convert2MysqlDate();
				
				// Get artist and record ids (by push)
				$aid = $this->music_db->pushArtist($artist);
				$rid = $this->music_db->pushRecord($record, $aid);
				
				// Try to get existing song id
				$sid = $this->music_db->getSidFromMMId($mmid);
				
				if ($sid === false) {
					$sid = $this->music_db->addSong($name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating);
					
					// Add MM link
					$this->music_db->addMMLink($sid, $mmid, $added_mysql);
					
					// add to added data array
					$song = array("SongId" => $sid, "SongName" => $name, "ArtistName" => $artist, "RecordName" => $record, "SongLength" => $length, "SongRating" => $rating);
					array_push($new, $song);
				} else {
					$this->music_db->updateSong($sid, $name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating);
					
					// add to updated data array
					$song = array("SongId" => $sid, "SongName" => $name, "ArtistName" => $artist, "RecordName" => $record, "SongLength" => $length, "SongRating" => $rating);
					array_push($updated, $song);
				}
				
				// check MM link suggestions
				$candidates = $this->music_db->getPossibleMMLinkCandidates($sid);
				
				if (!empty($candidates)) {
					array_push($suggestions, $song);
				}
			}
			
			$status["suggestions"] = $suggestions;
			$status["added"] = $new;
			$status["updated"] = $updated;
			
			return $status;
		}
		
		private function writePlayeds ($playeds) {
			$last_played_id = -1;
		
			foreach ($playeds as $played) {
				// Get element node values from played element
				$pldid = $played->getElementsByTagName('pldid')->item(0)->nodeValue;
				$mmid = $played->getElementsByTagName('mmid')->item(0)->nodeValue;
				$timestamp = $played->getElementsByTagName('timestamp')->item(0)->nodeValue;
				
				// Remove code tags
				$pldid = strip_tags($pldid);
				$mmid = strip_tags($mmid);
				$timestamp = strip_tags($timestamp);
				
				// Get song id from MediaMonkey id
				$sid = $this->music_db->getSidFromMMId($mmid);
				
				// get default desktop device
				$devid = $this->music_db->getConfig("default_desktop_device");
				
				// get default desktop activity
				$actid = $this->music_db->getConfig("default_desktop_activity");
				
				// Convert MM datetime to mysql datetime
				$mm_date = new MMDate($timestamp);
				$timestamp = $mm_date->convert2MysqlDateTime();
				
				// Write tupel to database
				$this->music_db->addPlayed($sid, $devid, $actid, $timestamp);
				
				// Save played id (for returning last one)
				$last_played_id = $pldid;
			}
			
			return $last_played_id;
		}
		
		private function writeMobile ($mobile) {
			$success = true;
		
			foreach ($mobile as $played) {
				// Get element node values from played element
				$sid = $played->getElementsByTagName('sid')->item(0)->nodeValue;
				$devid = $played->getElementsByTagName('devid')->item(0)->nodeValue;
				$timestamp = $played->getElementsByTagName('timestamp')->item(0)->nodeValue;
				
				// Remove code tags
				$sid = strip_tags($sid);
				$devid = strip_tags($devid);
				$timestamp = strip_tags($timestamp);
				
				// get default mobile activity
				$actid = $this->music_db->getConfig("default_mobile_activity");
				
				// Convert Unix timestamp to mysql datetime
				$unix = new UnixTimestamp($timestamp);
				$timestamp = $unix->convert2MysqlDateTime();
				
				// Write tupel to database
				$insert_success = $this->music_db->addPlayed($sid, $devid, $actid, $timestamp);
				
				if ($insert_success === false) {
					$success = false;
				}
			}
			
			return $success;
		}
		
		/**
			Reads the DOM X path of the specified file and returns it.
		*/
		private function getXPath ($file) {
			// Create empty DOM
			$xml = $this->getEmptyDOM();
			
			$xml->load($file);
			
			return new DOMXPath($xml);
		}
		
		/**
			Parses the xml file for the specified x path query.
		*/
		private function parseXML ($file, $query) {
			$xpath = $this->getXPath($file);
			
			return $xpath->query($query);
		}
		
		/**
			Returns a song element in the specified XML structure for storing in songs.xml file
		*/
		public function getSongElement ($name, $artist, $record, $length, $bitrate, $mmid, $devid, $trackno, $discno, $comment) {
			$dom = $this->getEmptyDOM();
		
			// Create parent song element
			$song = $dom->createElement("song");
		
			// Create child song elements
			$name_elem = $dom->createElement("name", $name);
			$artist_elem = $dom->createElement("artist", $artist);
			$record_elem = $dom->createElement("record", $record);
			$length_elem = $dom->createElement("length", $length);
			$bitrate_elem = $dom->createElement("bitrate", $bitrate);
			$mmid_elem = $dom->createElement("mmid", $mmid);
			$devid_elem = $dom->createElement("devid", $devid);
			$trackno_elem = $dom->createElement("trackno", $trackno);
			$discno_elem = $dom->createElement("discno", $discno);
			
			// Add child song elements to parent element
			$song->appendChild($name_elem);
			$song->appendChild($artist_elem);
			$song->appendChild($record_elem);
			$song->appendChild($length_elem);
			$song->appendChild($bitrate_elem);
			$song->appendChild($mmid_elem);
			$song->appendChild($devid_elem);
			$song->appendChild($trackno_elem);
			$song->appendChild($discno_elem);
			
			return $song;
		}
		
		/**
			Returns a play element in the specified XML structure for storing in played.xml file
		*/
		public function getPlayElement ($mmid, $devid, $timestamp) {
			$dom = $this->getEmptyDOM();
			
			// Create parent play element
			$play = $dom->createElement("play");
			
			// Create child play elements
			$mmid_elem = $dom->createElement("mmid", $mmid);
			$devid_elem = $dom->createElement("devid", $devid);
			$timestamp_elem = $dom->createElement("timestamp", $timestamp);
			
			// Add child play elements to parent element
			$play->appendChild($mmid_elem);
			$play->appendChild($devid_elem);
			$play->appendChild($timestamp_elem);
			
			return $play;
		}
		
		public function correctSongAddedDate($id = -1) {
			$songs = array();
			
			if ($id > 0) {
				// single mode
				$result = $this->query("SELECT ID, DateAdded FROM songs WHERE ID = " . $id);
			} else {
				// all mode
				$result = $this->query("SELECT ID, DateAdded FROM songs");
			}
			
			while ($song_result = $result->fetchArray()) {
				$song = array();
				
				$song["mmid"] = $song_result["ID"];
				
				$date_added = new MMDate($song_result["DateAdded"]);
				$song["added"] = $date_added->convert2MysqlDate();
				
				array_push($songs, $song);
			}
			
			return $songs;
		}
		
		private function convertMMDateTime ($datetime) {
			$day = floor(abs($datetime));
		
			$time = $datetime - $day;
			
			$hour = floor($time * 24);
			$minute = floor((($time * 24) - $hour) * 60);
			//$sec = floor((((($time * 24) - $hour) * 60) - $minute) * 60);
			
			$day = $this->convertMMDate($day);
			$hour = $this->addLeadingZero($hour);
			$minute = $this->addLeadingZero($minute);
			//$sec = $this->addLeadingZero($sec);
			
			return $day . " " . $hour . ":" . $minute;
		}
		
		private function convertMMDate ($days) {
			$date = new DateTime('1900-01-01');
			$date->add(new DateInterval('P' . ($days - 2) . 'D'));
			return $date->format('Y-m-d');
		}
		
		private function addLeadingZero ($str, $target = 2) {
			if (strlen($str) == ($target - 1)) {
				return "0" . $str;
			}
			
			return $str;
		}
		
		public function getMMFile () {
			return MMDB::$mmfile;
		}
		
		private function setMusicDB ($music_db) {
			$this->music_db = $music_db;
		}
		
		public function getMusicDB () {
			return $this->music_db;
		}
		
		private function getEmptyDOM () {
			return new DOMDocument('1.0', 'utf-8');
		}
	}