<?php
	class MusicDB {
		// Database connection
		private $db;
		
		// Mobile database connection
		private $mobile_db;
	
		// Should vital database transactions (insert, update, delete) be logged?
		private $logging = true;
		
		// Should every database transaction (even select) be logged?
		private $verbose = true;
		
		// Mobile database file
		private static $mobile_db_file = "files/myMobileMusic.DB";
	
		/**
			A database connection must be passed to the model
			
			@param object	$db		A PDO database connection
		*/
		function __construct ($db, $mobile_db) {
			try {
				$this->db = $db;
			} catch (PDOException $e) {
				exit("Database connection could not be established.");
			}
			
			try {
				$this->mobile_db = $mobile_db;
			} catch (PDOException $e) {
				exit("Mobile database connection could not be established.");
			}
		}
		
		public function getData ($name, $args) {
			switch ($name) {
				case "getPlayedSongHistory":
					return $this->getPlayedSongHistory($args['sid']);
				
				default:
					return null;
			}
		}
		
		/**
			Sets the config value for the specified property
			The method automatically detects if the property already exists in the table.
			If so, the value is updated, otherwise it is inserted.
			Returns true if operation was successful, false if otherwise.
		*/
		public function setConfig ($property, $value) {
			// strip input from code tags
			$property = strip_tags($property);
			$value = strip_tags($value);
			
			if ($this->propertyExists($property)) {
				return $this->updateConfig($property, $value);
			} else {
				return $this->insertConfig($property, $value);
			}
		}
		
		public function getConfig ($property) {
			// strip input from code tags
			$property = strip_tags($property);
			
			$sql = "SELECT value FROM config WHERE property LIKE :property";
			$query = $this->db->prepare($sql);
			$query->execute( array(':property' => $property) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				return $fetched['value'];
			} else {
				return false;
			}
		}
		
		/**
			Checks if a property exists in the config table
		*/
		private function propertyExists ($property) {
			$sql = "SELECT id FROM config WHERE property LIKE :property";
			$query = $this->db->prepare($sql);
			$query->execute( array(':property' => $property) );
			
			if ($query->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Updates the config value for the specified property.
			Returns true if operation was successful, false if otherwise.
		*/
		private function updateConfig ($property, $value) {
			$sql = "UPDATE config SET value=:value WHERE property LIKE :property";
			$query = $this->db->prepare($sql);
			$query->execute( array(':property' => $property, ':value' => $value) );
			
			if ($query->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Inserts a new config value for the specified property
			Returns true if operation was successful, false if otherwise.
		*/
		private function insertConfig ($property, $value) {
			$sql = "INSERT INTO config (property, value) VALUES (:property, :value)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':property' => $property, ':value' => $value) );
			if ($query->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
	
		/**
			Generates a sql query for the specified id.
			Returns a result array if found, false if otherwise
		*/
		private function getSingleGeneric ($type, $id) {
			// strip input from code tags
			$id = strip_tags($id);
		
			$sql = "SELECT * FROM " . $type . " WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched from " . $type . " with id " . $id);
				}
			
				return $query->fetch(PDO::FETCH_ASSOC);
			} else {
				return false;
			}
		}
		
		/**
			Adds a new row to the database, specified by name
			If insert was successful, the newly assigned id is returned, otherwise false.
		*/
		private function addGenericByName ($type, $name) {
			// strip input from code tags
			$name = strip_tags($name);
			
			$sql = "INSERT INTO " . $type . " (name) VALUES (:name)";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':name' => $name) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
				
				// Mobile database entry
				$mobile_tables = array('artists');
			
				if (in_array($type, $mobile_tables)) {
					$mobile_sql = "INSERT INTO " . $type . " (_id, name) VALUES (:id, :name)";
					$mobile_query = $this->mobile_db->prepare($mobile_sql);
					//$mobile_query->execute( array(':id' => $inserted, ':name' => $name) );
				}
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new tupel in " . $type . " '" . $name . "' with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new tupel in " . $type . " '" . $name . "' \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Returns an artist with all meta data from the database
		*/
		public function getArtist ($id) {
			$artist = array();
			
			// basic information
			$sql = "SELECT
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						ar.main_country_id AS 'ArtistMainCountryId',
						ar.sec_country_id AS 'ArtistSecondaryCountryId'
					FROM
						artists ar
					WHERE
						ar.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				$artist["ArtistId"] = $fetch["ArtistId"];
				$artist["ArtistName"] = $fetch["ArtistName"];
				$artist["ArtistMainCountryId"] = $fetch["ArtistMainCountryId"];
				$artist["ArtistSecondaryCountryId"] = $fetch["ArtistSecondaryCountryId"];
			}
			
			// artist play count
			$sql = "SELECT
						COUNT(*) AS 'ArtistPlayCount'
					FROM
						artists ar INNER JOIN
						songs so ON so.aid = ar.id INNER JOIN
						played pl ON pl.sid = so.id
					WHERE
						ar.id = :id";
							
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				$artist["ArtistPlayCount"] = $fetch["ArtistPlayCount"];
			}
			
			// get records in chronological descending order
			$releases = $this->getArtistReleases($id);
			
			$artist["Releases"] = $releases;
			
			return $artist;
		}
		
		/**
			Returns a song with all meta data from the database
		*/
		public function getSong ($id) {
			$sql = "SELECT
						so.id AS 'SongId',
						so.name AS 'SongName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						re.id AS 'RecordId',
						re.name AS 'RecordName',
						so.length AS 'SongLength',
						so.length AS 'SongBitrate',
						so.rating AS 'SongRating',
						so.discno AS 'SongDiscNo',
						so.trackno AS 'SongTrackNo',
						so.comment AS 'SongComment'
					FROM
						songs so INNER JOIN
						artists ar ON ar.id = so.aid INNER JOIN
						records re ON re.id = so.rid
					WHERE
						so.id = :id";

			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched song details for song id " . $id);
				}
				
				return $query->fetch(PDO::FETCH_ASSOC);
			} else {
				return null;
			}
		}
		
		/**
			Returns the number of times a song has been played.
		*/
		public function getSongPlayCount ($id) {
			$sql = "SELECT
						COUNT(*) AS 'PlayCount'
					FROM
						played
					WHERE
						sid = :id
					GROUP BY 
						sid";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched played count for song id " . $id);
				}
				
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
				
				return $fetch["PlayCount"];
			} else {
				return null;
			}
		}
		
		/**
			Returns the date when a song was added.
		*/
		public function getSongAddedDate ($id) {
			$sql = "SELECT
						added AS 'AddedDate'
					FROM
						mmlink
					WHERE
						sid = :id
					ORDER BY
						added ASC
					LIMIT 1";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched added date song id " . $id);
				}
				
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
				
				return $fetch["AddedDate"];
			} else {
				return null;
			}
		}
		
		/**
			Returns the most recent played entry for a song.
			If no played entry is found for this song, false is returned.
		*/
		public function getMostRecentPlayed($id) {
			$sql = "SELECT
						timestamp
					FROM
						played
					WHERE
						sid = :id
					ORDER BY
						timestamp DESC
					LIMIT 1";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched most recent played entry for song id " . $id);
				}
				
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
				
				return $fetch["timestamp"];
			} else {
				return false;
			}
		}
		
		/**
			Returns an array containing basic information about a record as well as the list of all songs in the record
		*/
		public function getRecord($id) {
			$record = array();
			
			// basic information
			$sql = "SELECT
						re.id AS 'RecordId',
						re.name AS 'RecordName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						re.publish AS 'RecordPublishDate',
						re.typeid AS 'RecordTypeId',
						rt.name AS 'RecordTypeName'
					FROM
						records re INNER JOIN
						artists ar ON ar.id = re.aid INNER JOIN
						record_type rt ON rt.id = re.typeid
					WHERE
						re.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
				
				$record["RecordId"] = $fetch["RecordId"];
				$record["RecordName"] = $fetch["RecordName"];
				$record["ArtistId"] = $fetch["ArtistId"];
				$record["ArtistName"] = $fetch["ArtistName"];
				$record["RecordPublishDate"] = $fetch["RecordPublishDate"];
				$record["RecordTypeId"] = $fetch["RecordTypeId"];
				$record["RecordTypeName"] = $fetch["RecordTypeName"];
			}
			
			// get additional record infos like the song list, total playing time and total play count
			$record_info = $this->getRecordSongList($id);
			
			// put items from 
			$record["SongList"] = $record_info["SongList"];
			$record["SongPlayedCount"] = $record_info["SongPlayedCount"];
			$record["SongLengthCount"] = $record_info["SongLengthCount"];
						
			return $record;
		}
		
		/**
			Returns an array containg the song list for a record.
			Additionally, there's a total play count of all the songs on the record
			and the total playing length of the record included.
		*/
		function getRecordSongList($id) {
			$record = array();
			
			// song list with played counts
			$sql = "SELECT
						sq.SongId,
						sq.SongName,
						sq.SongDiscNo,
						sq.SongTrackNo,
						sq.SongLength,
						sq.SongRating,
						SUM(sq.SongPlay) AS 'PlayedCount'
					FROM
						(SELECT
							so.id AS 'SongId',
							so.name AS 'SongName',
							so.discno AS 'SongDiscNo',
							so.trackno AS 'SongTrackNo',
							so.length AS 'SongLength',
							so.rating AS 'SongRating',
							IF(pl.sid, 1, 0) AS 'SongPlay'
						FROM
							songs so LEFT JOIN
							played pl ON pl.sid = so.id
						WHERE
							so.rid = :id) sq
					GROUP BY
						sq.SongId
					ORDER BY
						SongDiscNo, SongTrackNo";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			// song list
			$song_list = array();
			
			// song played ocunt
			$song_played_count = 0;
			
			// song duration count
			$song_length_count = 0;
			
			if ($query->rowCount() > 0) {
				$songs = $query->fetchAll(PDO::FETCH_ASSOC);
				
				foreach($songs as $song) {
					// add last played timestamp to song
					$most_recent = $this->getMostRecentPlayed($song["SongId"]);
				
					$song["MostRecentPlayed"] = getMostRecentPlayedText($most_recent);
					
					array_push($song_list, $song);
					$song_played_count += $song["PlayedCount"];
					$song_length_count += $song["SongLength"];
				}
			}
			
			$record["SongList"] = $song_list;
			$record["SongPlayedCount"] = $song_played_count;
			$record["SongLengthCount"] = $song_length_count;
			
			return $record;
		}
		
		/**
			Returns an array containing details about a record (artist name, record title, record type, publish date)
		*/
		public function getRecordDetails($id) {
			$record_details = array();
			
			// basic information
			$sql = "SELECT
						re.id AS 'RecordId',
						re.name AS 'RecordName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						re.publish AS 'RecordPublishDate',
						re.typeid AS 'RecordTypeId',
						rt.name AS 'RecordTypeName'
					FROM
						records re INNER JOIN
						artists ar ON ar.id = re.aid INNER JOIN
						record_type rt ON rt.id = re.typeid
					WHERE
						re.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
				
				$record_details["RecordId"] = $fetch["RecordId"];
				$record_details["RecordName"] = $fetch["RecordName"];
				$record_details["ArtistId"] = $fetch["ArtistId"];
				$record_details["ArtistName"] = $fetch["ArtistName"];
				$record_details["RecordPublishDate"] = $fetch["RecordPublishDate"];
				$record_details["RecordTypeId"] = $fetch["RecordTypeId"];
				$record_details["RecordTypeName"] = $fetch["RecordTypeName"];
			}
						
			return $record_details;
		}
		
		/**	
			Updates the details for a record.
			Returns true if operations was successful or nothing was changed, false if an error occurred.
		*/
		public function updateRecordDetails($id, $typeid, $publish) {
			$sql = "UPDATE records SET typeid = :typeid, publish = :publish WHERE id = :id";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':typeid' => $typeid, ':publish' => $publish) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Returns a played entry with all meta data from the database
		*/
		public function getPlayed ($id) {
			$sql = "SELECT
						pl.id AS 'PlayedId',
						pl.devid AS 'DeviceId',
						pl.actid AS 'ActivityId',
						pl.timestamp AS 'PlayedTimestamp',
						so.id AS 'SongId',
						so.name AS 'SongTitle',
						so.aid AS 'ArtistId',
						so.rid AS 'RecordId'
					FROM
						played pl INNER JOIN
						songs so ON so.id = pl.sid
					WHERE
						pl.id = :id";

			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				return $query->fetch(PDO::FETCH_ASSOC);
			} else {
				return null;
			}
		}
		
		/**
			Returns an array containing all 5 star songs by an artist ordered by play count.
		*/
		public function getPopularSongByArtist($id) {
			$songs = array();
			
			// song list with played counts
			$sql = "SELECT
						sq.SongId,
						sq.SongName,
						sq.SongLength,
						sq.RecordId,
						sq.RecordName,
						SUM(sq.SongPlay) AS 'PlayedCount'
					FROM
						(SELECT
							so.id AS 'SongId',
							so.name AS 'SongName',
							so.length AS 'SongLength',
							re.id AS 'RecordId',
							re.name AS 'RecordName',
							IF(pl.sid, 1, 0) AS 'SongPlay'
						FROM
							songs so INNER JOIN
							records re ON re.id = so.rid LEFT JOIN
							played pl ON pl.sid = so.id
						WHERE
							so.aid = :id AND
							so.rating = 100) sq
					GROUP BY
						sq.SongId
					ORDER BY
						PlayedCount DESC";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$popular = $query->fetchAll(PDO::FETCH_ASSOC);
				
				// add last played timestamp to each song
				foreach ($popular as $song) {
					$most_recent = $this->getMostRecentPlayed($song["SongId"]);
				
					$song["MostRecentPlayed"] = getMostRecentPlayedText($most_recent);
					
					array_push($songs, $song);
				}
			}
			
			return $songs;
		}
		
		/**
			Adds a new artist to the database.
			If insert was successful, the newly assigned id is returned, otherwise false.
		*/
		public function addArtist ($name, $main_country = 0, $secondary_country = 0) {
			// strip input from code tags
			$name = strip_tags($name);
			$main_country = strip_tags($main_country);
			$secondary_country = strip_tags($secondary_country);
			
			$sql = "INSERT INTO artists (name, main_country_id, sec_country_id) VALUES (:name, :main_country_id, :sec_country_id)";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':name' => $name, ':main_country_id' => $main_country, ':sec_country_id' => $secondary_country) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
				
				// Mobile database entry
				$mobile_sql = "INSERT INTO artists (_id, name) VALUES (:id, :name)";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				//$mobile_query->execute( array(':id' => $inserted, ':name' => $name) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new tupel in artists [ name: '" . $name . "', main_country_id: " . $main_country . ", sec_country_id: " . $secondary_country . " ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new tupel in artists [ name: '" . $name . "', main_country_id: " . $main_country . ", sec_country_id: " . $secondary_country . " ] with \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Returns an array containing all record types.
			If no record types exist, null is returned.
		*/
		public function getRecordTypes() {
			// get all record types
			$sql = "SELECT
						rt.id AS 'RecordTypeId',
						rt.name AS 'RecordTypeName',
						rt.level AS 'RecordTypeLevel'
					FROM
						record_type rt
					ORDER BY
						rt.level ASC";
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetchAll(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns the next level for a record type.
			If no record types exist, 1 will be returned.
		*/
		public function getNextRecordTypeLevel() {
			// get the next record type level
			$sql = "SELECT
						MAX(rt.level) + 1 AS 'NextRecordTypeLevel'
					FROM
						record_type rt";
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch["NextRecordTypeLevel"];
			} else {
				return 1;
			}
		}
		
		/**
			Returns the record type with the matching id from the database.
			If no record type is found with this id, null is returned.
		*/
		public function getRecordType($id) {
			// get record type
			$sql = "SELECT
						rt.id AS 'RecordTypeId',
						rt.name AS 'RecordTypeName',
						rt.level AS 'RecordTypeLevel'
					FROM
						record_type rt
					WHERE
						rt.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Persists a record type. The function updates the record type if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't.
		*/
		public function saveRecordType($id, $name, $level) {
			if ($id <= 0) {
				// add new record type
				$success = $this->addRecordType($name, $level);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing record type
				$success = $this->updateRecordType($id, $name, $level);
			}
			
			return $success;
		}
		
		/**
			Saves the order of a record type level.
			Useful for persisting the sortable grid of record types.
			Returns true if the operation was successful, false if it was not or an illegal id was passed.
		*/
		public function updateRecordTypeLevel($id, $level) {
			if ($id > 0) {
				$sql = "UPDATE record_type SET level = :level WHERE id = :id";
				$query = $this->db->prepare($sql);
				$success = $query->execute( array(':id' => $id, ':level' => $level) );
				
				if ($query->rowCount() > 0 OR $success !== false) {
					return true;
				} else {
					return false;
				}
			}
			
			return false;
		}
		
		/**
			Adds a new record type to the database.
			If insert was successful, the newly assigned id is returned, otherwise false.
		*/
		public function addRecordType ($name, $level) {
			// strip input from code tags
			$name = strip_tags($name);
			$level = strip_tags($level);
			
			$sql = "INSERT INTO record_type (name, level) VALUES (:name, :level)";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':name' => $name, ':level' => $level) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new tupel in " . $type . " '" . $name . "', importance level " . $level . " with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new tupel in " . $type . " '" . $name . "', importance level " . $level . " \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates name of specified record type id.
			Returns true if update was successful, false otherwise.
		*/
		public function updateRecordType ($id, $name, $level) {
			$sql = "UPDATE record_type SET name = :name, level = :level WHERE id = :id";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':name' => $name, ':level' => $level) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in record_type with id " . $id . " [ name: " . $name . ", importance level: " . $level . " ]");
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in record_type with id " . $id . " [ name: " . $name . ", importance level: " . $level . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Persists a song. The function updates the song if it already exists or adds it to the database if it doesn't exist.
			Returns the id of the song if the operation was successful, false if it wasn't.
		*/
		public function saveSong($id, $name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating, $comment) {
			if ($id <= 0) {
				// add new song
				$sid = $this->addSong($name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating, $comment);
			} else {
				// update existing song
				$sid = $this->updateSong($id, $name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating, $comment);
			}
			
			return $sid;
		}
		
		/**
			Adds a new song to the database.
			If insert was successful, the newly assigned id is returned, otherwise false.
		*/
		public function addSong ($name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating, $comment = "") {
			// strip input from code tags
			$name = strip_tags($name);
			$aid = strip_tags($aid);
			$rid = strip_tags($rid);
			$length = strip_tags($length);
			$bitrate = strip_tags($bitrate);
			$discno = strip_tags($discno);
			$trackno = strip_tags($trackno);
			$rating = strip_tags($rating);
			$comment = strip_tags($comment);
			
			// Put song into database
			$sql = "INSERT INTO songs (name, aid, rid, length, bitrate, discno, trackno, rating, comment) VALUES (:name, :aid, :rid, :length, :bitrate, :discno, :trackno, :rating, :comment)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':name' => $name, ':aid' => $aid, ':rid' => $rid, ':length' => $length, ':bitrate' => $bitrate, ':discno' => $discno, ':trackno' => $trackno, ':rating' => $rating, ':comment' => $comment) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
				
				// Mobile database entry
				$mobile_sql = "INSERT INTO songs (_id, name, aid, rid, rating, length, discno, trackno) VALUES (:id, :name, :aid, :rid, :rating, :length, :discno, :trackno)";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				//$mobile_query->execute( array(':id' => $inserted, ':name' => $name, ':aid' => $aid, ':rid' => $rid, ':rating' => $rating, ':length' => $length, ':discno' => $discno, ':trackno' => $trackno) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new tupel in songs with id " . $inserted . " [" . implode(", ", func_get_args()) . "]");
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new tupel in songs [" . implode(", ", func_get_args()) . "] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates a song in the database
			If update was successful, the id of the record is returned, false otherwise
		*/
		public function updateSong ($id, $name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating, $comment = "") {
			// strip input from code tags
			$id = strip_tags($id);
			$name = strip_tags($name);
			$aid = strip_tags($aid);
			$rid = strip_tags($rid);
			$length = strip_tags($length);
			$bitrate = strip_tags($bitrate);
			$discno = strip_tags($discno);
			$trackno = strip_tags($trackno);
			$rating = strip_tags($rating);
			$comment = strip_tags($comment);
			
			$sql = "UPDATE songs SET name=:name, aid=:aid, rid=:rid, length=:length, bitrate=:bitrate, discno=:discno, trackno=:trackno, rating=:rating, comment=:comment WHERE id = :id";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':name' => $name, ':aid' => $aid, ':rid' => $rid, ':length' => $length, ':bitrate' => $bitrate, ':discno' => $discno, ':trackno' => $trackno, ':rating' => $rating, ':comment' => $comment) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				// Mobile database entry
				$mobile_sql = "UPDATE songs SET name = :name, aid = :aid, rid = :rid, length = :length, discno = :discno, trackno = :trackno, rating = :rating WHERE _id = :id";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				//$mobile_query->execute( array(':id' => $id, ':name' => $name, ':aid' => $aid, ':rid' => $rid, ':length' => $length, ':discno' => $discno, ':trackno' => $trackno, ':rating' => $rating) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in songs with id " . $id . " [" . implode(", ", func_get_args()) . "]");
				}
			
				return $id;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in songs [" . implode(", ", func_get_args()) . "] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates a single property of a song.
			If update was successful, true is returned, false otherwise
		*/
		public function updateSongSingleProperty ($id, $property, $value) {
			// strip input from code tags
			$id = strip_tags($id);
			$property = strip_tags($property);
			$value = strip_tags($value);
			
			$sql = "UPDATE songs SET :property = :value WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':property' => $property, ':value' => $value) );
			
			if ($query->rowCount() > 0) {
				// Mobile database entry (only performed if property is relevant in mobile database)
				$mobile_properties = array('name', 'aid', 'rid', 'length', 'discno', 'trackno');
				
				if (in_array($property, $mobile_properties)) {
					$mobile_sql = "UPDATE songs SET :property = :value WHERE _id = :id";
					$mobile_query = $this->mobile_db->prepare($mobile_sql);
					$mobile_query->execute( array(':id' => $id, ':property' => $property, ':value' => $value) );
				}
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in songs with id " . $id . " [ property: " . $property . ", value: " + $value . " ]");
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in songs with id " . $id . " [ property: " . $property . ", value: " + $value . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Searches the database for the name, returns the corresponding id if found, false if not.
		*/
		private function getGenericIdByName ($type, $name) {
			// strip input from code tags
			$name = strip_tags($name);
		
			$sql = "SELECT id FROM " . $type . " WHERE name LIKE :name";
			$query = $this->db->prepare($sql);
			$query->execute( array(':name' => $name) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched from " . $type . " with name '" . $name . "'");
				}
				
				return $fetched['id'];
			} else {
				return false;
			}
		}
		
		/**
			Searches the artists for the name, returns the corresponding id if found, false if not.
		*/
		public function getArtistIdByName ($name) {
			return $this->getGenericIdByName('artists', $name);
		}
		
		/**
			Searches the records for the name, returns the corresponding id if found, false if not.
		*/
		public function getRecordId ($name, $aid) {
			// strip input from code tags
			$name = strip_tags($name);
			$aid = strip_tags($aid);
		
			$sql = "SELECT id FROM records WHERE name LIKE :name AND aid = :aid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':name' => $name, ':aid' => $aid) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched from records with name '" . $name . "' and aid " . $aid);
				}
				
				return $fetched['id'];
			} else {
				return false;
			}
		}
		
		/**
			Persists a record. The function updates the record if it already exists or adds it to the database if it doesn't exist.
			Returns the id of the record if the operation was successful, false if it wasn't.
		*/
		public function saveRecord($id, $name, $aid, $typeid, $publish) {
			if ($id <= 0) {
				// add new record
				$rid = $this->addRecord($name, $aid, $typeid, $publish);
			} else {
				// update existing record
				$rid = $this->updateRecord($id, $name, $aid, $typeid, $publish);
			}
			
			return $rid;
		}
		
		/**
			Adds a new record to the database.
			If insert was successful, the newly assigned id is returned, otherwise false.
		*/
		public function addRecord ($name, $aid, $typeid = 0, $publish = '0000-00-00') {
			// strip input from code tags
			$name = strip_tags($name);
			$aid = strip_tags($aid);
			$typeid = strip_tags($typeid);
			$publish = strip_tags($publish);
			
			$sql = "INSERT INTO records (name, aid, typeid, publish) VALUES (:name, :aid, :typeid, :publish)";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':name' => $name, ':aid' => $aid, ':typeid' => $typeid, ':publish' => $publish) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
				
				// Mobile database entry
				$mobile_sql = "INSERT INTO records (_id, name) VALUES (:id, :name)";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				//$mobile_query->execute( array(':id' => $inserted, ':name' => $name) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new tupel in records [ name: '" . $name . "', aid: '" . $aid . "', typeid: " . $typeid . ", publish: '" . $publish . "' ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new tupel in records [ name: '" . $name . "', aid: '" . $aid . "', typeid: " . $typeid . ", publish: '" . $publish . "' ] with \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/** 
			Returns the id for the artist. If artist has no id yet, a new one is assigned and returned.
			If artist could not be pushed, false is returned.
		*/
		public function pushArtist ($name) {
			// strip input from code tags
			$name = strip_tags($name);
		
			$aid = $this->getArtistIdByName($name);
			
			// If artist does not yet exist, add it
			if ($aid === false) {
				$aid = $this->addArtist($name);
			}
			
			return $aid;
		}
		
		/** 
			Returns the id for the record. If record has no id yet, a new one is assigned and returned.
			If artist could not be pushed, false is returned.
		*/
		public function pushRecord ($name, $aid, $typeid = 1, $release = '0000-00-00') {
			// strip input from code tags
			$name = strip_tags($name);
			$aid = strip_tags($aid);
			$typeid = strip_tags($typeid);
			$release = strip_tags($release);
		
			$rid = $this->getRecordId($name, $aid);
			
			// If record does not yet exist, add it
			if ($rid === false) {
				$rid = $this->addRecord($name, $aid, $typeid, $release);
			}
			
			return $rid;
		}
		
		/**
			Generic update method for changing the name property.
			Returns true if update was successful, false otherwise.
		*/
		private function updateGeneric ($type, $id, $name) {
			// strip input from code tags
			$type = strip_tags($type);
			$id = strip_tags($id);
			$name = strip_tags($name);
			
			$sql = "UPDATE " . $type . " SET name = :name WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':name' => $name) );
			
			if ($query->rowCount() > 0) {
				// Mobile database entry
				$mobile_tables = array('artists');
			
				if (in_array($type, $mobile_tables)) {
					$mobile_sql = "UPDATE " . $type . " SET name = :name WHERE _id = :id";
					$mobile_query = $this->mobile_db->prepare($mobile_sql);
					//$mobile_query->execute( array(':id' => $id, ':name' => $name) );
				}
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in " . $type . " with id " . $id . " [ name: " . $name . " ]");
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in " . $type . " with id " . $id . " [ name: " . $name . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}

		/**
			Updates the artist with the specified id.
			Returns true if update was successful, false otherwise.
		*/
		public function updateArtist ($id, $name) {
			return $this->updateGeneric("artists", $id, $name);
		}
		
		/**
			Updates the details of the artist with the specified id.
			Details entail main and secondary country.
		*/
		public function updateArtistDetails ($id, $main_country = 0, $secondary_country = 0) {
			$sql = "UPDATE artists SET main_country_id = :main_country_id, sec_country_id = :secondary_country_id WHERE id = :id";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':main_country_id' => $main_country, ':secondary_country_id' => $secondary_country) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in artists [ main_country_id: " . $main_country . ", sec_country_id: " . $secondary_country . " ] with id " . $id);
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in artists [ main_country_id: " . $main_country . ", sec_country_id: " . $secondary_country . " ] with id " . $id . "\n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates name of specified record id.
			Returns true if update was successful, false otherwise.
		*/
		public function updateRecord ($id, $name, $aid, $typeid = 0, $release = '0000-00-00') {
			// strip input from code tags
			$name = strip_tags($name);
			$aid = strip_tags($aid);
			$typeid = strip_tags($typeid);
			$release = strip_tags($release);
			
			$sql = "UPDATE records SET name = :name, aid = :aid, typeid = :typeid, publish = :release WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':name' => $name, ':aid' => $aid, ':typeid' => $typeid, ':release' => $release) );
			
			if ($query->rowCount() > 0) {
				// Mobile database entry
				$mobile_sql = "UPDATE records SET name = :name WHERE _id = :id";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				$mobile_query->execute( array(':id' => $id, ':name' => $name) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in records [ name: '" . $name . "', aid: '" . $aid . "', typeid: " . $typeid . ", release: '" . $release . "' ] with id " . $id);
				}
			
				return $id;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in records [ name: '" . $name . "', aid: '" . $aid . "', typeid: " . $typeid . ", release: '" . $release . "' ] with id " . $id . "\n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Persists a played entry. The function updates the played entry if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't.
		*/
		public function savePlayed($id, $sid, $devid, $actid, $timestamp) {
			if ($id <= 0) {
				// add new played entry
				$success = $this->addPlayed($sid, $devid, $actid, $timestamp);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing played entry
				$success = $this->updatePlayed($id, $sid, $devid, $actid, $timestamp);
			}
			
			return $success;
		}
		
		/**
			Adds a new entry to the played table.
			Inserted id is returned if insert was successful, false if otherwise.
		*/
		public function addPlayed ($sid, $devid, $actid, $timestamp) {
			// check if sid is valid
			$song_exists = $this->songExists($sid);
			
			if ($song_exists === false) {
				// check if song has a song connection (meaning that it was linked to another song)
				$connection_parent = $this->getConnectionParent($sid);
				
				if ($connection_parent !== false) {
					// if a connection parent song id was found, this is the correct one
					$sid = $connection_parent;
				} else {
					// played cannot be added, the song id is not found
					$this->addLog(__FUNCTION__, "error", "could not add played entry" . " [" . implode(", ", func_get_args()) . "]");
					
					return false;
				}
			}
			
			$sql = "INSERT INTO played (sid, devid, actid, timestamp) VALUES (:sid, :devid, :actid, :timestamp)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid, ':devid' => $devid, ':actid' => $actid, ':timestamp' => $timestamp) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new tupel in played with id " . $inserted . " [" . implode(", ", func_get_args()) . "]");
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new tupel in played [" . implode(", ", func_get_args()) . "] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates a played entry.
			If the operation was successful, true is returned, false otherwise.
		*/
		public function updatePlayed($id, $sid, $devid, $actid, $timestamp) {
			$sql = "UPDATE played SET sid = :sid, devid = :devid, actid = :actid, timestamp = :timestamp WHERE id = :id";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':sid' => $sid, ':devid' => $devid, ':actid' => $actid, ':timestamp' => $timestamp) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Adds an array of played songs to the database.
			Returns true if the operation was successful, false if it was not.
		*/
		public function addPlayedSongs($date, $device_id, $activity_id, $songs) {
			$success = true;
			
			$last_timestamp = 0;
			
			foreach ($songs as $song) {
				$time = $song["time"];
				$id = $song["id"];
				
				// check if a time was passed
				if ($time != "") {
					// build timestamp
					$day = substr($date, 0, 2);
					$month = substr($date, 3, 2);
					$year = substr($date, 6);
					
					$hour = substr($time, 0, 2);
					$minute = substr($time, 3, 2);
					
					// timestamp of start time
					$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
				} else {
					// no time was passed (it is ensured from the frontend that there will be a valid time for the first element)
					$timestamp = $last_timestamp;
				}
				
				// get song length (because the timestamp is the start time)
				$song_length = $this->getSongDetailById($id, "length");
				
				// add song length to get the end time
				$timestamp += floor($song_length / 1000);
				
				// save new last timestamp for the next element
				$last_timestamp = $timestamp;
				
				// make it a UnixTimestamp for easier conversion to MySQL date time format
				$unix_timestamp = new UnixTimestamp($timestamp);
				
				// check for error while executing
				if (!$this->addPlayed($id, $device_id, $activity_id, $unix_timestamp->convert2MysqlDateTime())) {
					$success = false;
				}
			}
			
			return $success;
		}
		
		/**
			Returns all entries for the played song, false if song id was not found.
		*/
		public function getPlayedBySongId ($sid) {
			// strip input from code tags
			$sid = strip_tags($sid);
		
			$sql = "SELECT * FROM played WHERE sid = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched from played with song id " . $sid);
				}
			
				return $query->fetchAll();
			} else {
				return false;
			}
		}
		
		/**
			Returns all entries from the played table for the specified date, false if no result was found
		*/
		public function getPlayedByDate ($date) {
			// strip input from code tags
			$date = strip_tags($date);
		
			$sql = "SELECT * FROM played WHERE DATE(timestamp) = :date";
			$query = $this->db->prepare($sql);
			$query->execute( array(':date' => $date) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched from played with date " . $date);
				}
			
				return $query->fetchAll();
			} else {
				return false;
			}
		}
		
		/**
			Returns the field value from the tupel with the specified id.
			False is returned if id or field is not found
		*/
		public function getSongDetailById ($id, $field) {
			// strip input from code tags
			$id = strip_tags($id);
			$field = strip_tags($field);
		
			$sql = "SELECT " . $field . " FROM songs WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched song detail " . $field . " for id " . $id);
				}
			
				return $fetched[$field];
			} else {
				return false;
			}
		}
		
		/**
			Returns the id from the tupel where the field has the specified value
			False is returned if id or field is not found
		*/
		public function getSongIdByValue ($field, $value) {
			// strip input from code tags
			$field = strip_tags($field);
			$value = strip_tags($value);
		
			$sql = "SELECT id FROM songs WHERE :field LIKE :value";
			$query = $this->db->prepare($sql);
			$query->execute( array(':field' => $field, ':value' => $value) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched song id for " . $field . " by value '" . $value . "'");
				}
			
				return $fetched['id'];
			} else {
				return false;
			}
		}
		
		/**
			Checks if a song with the specified id exists in the database.
			Returns true if it exists, false if it doesn't.
		*/
		public function songExists ($sid) {
			$sql = "SELECT id FROM songs WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $sid) );
			
			if ($query->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Adds a new log entry
			
			@param	$action			Performed action (e.g. function name)
			@param	$status			Success status (success, warning, error)
			@param	$description	Description of perfomed action (e.g. Inserted value "xy" with id #1)
			@param	$timestamp		Timestamp of permormed action
			@return	Id of newly inserted log entry, false if insert was not successful
		*/
		public function addLog ($action, $status, $description) {
			// strip input from code tags
			$action = strip_tags($action);
			$status = strip_tags($status);
			$description = strip_tags($description);
			
			$sql = "INSERT INTO logs (action, status, description) VALUES (:action, :status, :description)";
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':action' => $action, ':status' => $status, ':description' => $description) );
			
			if ($query->rowCount() > 0) {
				return $this->db->lastInsertId();
			} else {
				return false;
			}
		}
		
		/**
			Returns all log entries
		*/
		public function getLogs () {
			$sql = "SELECT * FROM logs";
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll();
			} else {
				return false;
			}
		}
		
		/**
			Returns a single log entry with the specified id.
			False is returned if id is not found
		*/
		public function getSingleLog ($id) {
			// strip input from code tags
			$id = strip_tags($id);
		
			$sql = "SELECT * FROM logs WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				return $query->fetch();
			} else {
				return false;
			}
		}
		
		/**
			Returns all log messages for the specified date (range)
			If second date is ommitted, only messages for the specified first date are displayed
			
			@param		$date_from		Begin of date range
			@param		$date_to		End of date range (optional, not needed if only log entries for one day should be displayed)
			@return		Array containing the query result, false if query was not successful or did not find log messages for the specified dates
		*/
		public function getLogsByDate ($date_from) {
			// strip input from code tags
			$date_from = strip_tags($date_from);
			$date_to = strip_tags($date_to);
		
			$sql = "SELECT * FROM logs WHERE timestamp LIKE :date_from%";
			$query = $this->db->prepare($sql);
			$query->execute( array(':date_from' => $date_from) );
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll();
			} else {
				return false;
			}
		}
		
		/**
			Searches the database for the search term.
			Returns the query object containing all matches.
		*/
		public function searchDatabase ($search) {
			$search = strip_tags($search);
			
			$terms = explode(" ", $search);
		
			// SQL query for determining all matched songs
				$term_query = "";
				
				foreach ($terms as $term) {
					$tq = "( SELECT SongId FROM SongsView WHERE SongName LIKE '%" . $term . "%' OR ArtistName LIKE '%" . $term . "%' OR RecordName LIKE '%" . $term . "%' )";
					
					if ($term_query != "") {
						$term_query .= " UNION ALL ";
					}
					
					$term_query .= $tq;
				}
				
				$count_query = " SELECT SongId, COUNT(SongId) AS 'SongCount' FROM ( " . $term_query . " ) count_query GROUP BY SongId ";
			
				$main_sql = "SELECT sv.SongId, sv.SongName, sv.ArtistName, sv.RecordName FROM ( " . $count_query . " ) sub_query INNER JOIN SongsView sv ON sv.SongId = sub_query.SongId WHERE SongCount >= :term_count";
		
			$query = $this->db->prepare($main_sql);
			$query->execute( array(':term_count' => count($terms)) );
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll();
			} else {
				return false;
			}
		}
		
		/**
			Performs a short single search on the database for a song, artist or record.
			Short single search means that there is only one word in the search query that consists of three or less characters.
			In this mode, only the beginning of entries in the database are matched (i.e. LIKE 'xx%')
			Input sanitation is done before this step, so the input is guaranteed to be stripped off of any scripts tags.
			
			Returned is an array containing the results, or false if an error occurred during the SQL query.
		*/
		public function shortSingleSearch ($mode, $search, $limit = 10) {
			// escape string in case there's any apostrophes in it
			$search = mysql_real_escape_string($search);
			
			switch ($mode) {
				case "songs":
					$sql = "SELECT SongId, SongName, ArtistName, RecordName FROM SongsView WHERE SongName LIKE '" . $search . "%' OR ArtistName LIKE '" . $search . "%' LIMIT :limit";
			
					try {
						$query = $this->db->prepare($sql);
						$query->execute( array(':limit' => $limit) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				case "artists":
					$sql = "SELECT id AS 'ArtistId', name AS 'ArtistName' FROM artists WHERE name LIKE '" . $search . "%' LIMIT :limit";
			
					try {
						$query = $this->db->prepare($sql);
						$query->execute( array(':limit' => $limit) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				case "records":
					$sql = "SELECT re.id AS 'RecordId', re.name AS 'RecordName', ar.name AS 'ArtistName' FROM records re INNER JOIN artists ar ON re.aid = ar.id WHERE re.name LIKE '" . $search . "%' LIMIT :limit";
			
					try {
						$query = $this->db->prepare($sql);
						$query->execute( array(':limit' => $limit) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
					break;
					
				default:
					return false;
					
					break;
			}
		}
		
		/**
			Performs a long single search on the database for a song, artist or record.
			Long single search means that there is only one word in the search query that consists of more than three characters.
			In this mode, entries that contain the term within a word are matched, too (i.e. LIKE '%xxxx%')
			Input sanitation is done before this step, so the input is guaranteed to be stripped off of any scripts tags.
			
			Returned is an array containing the results, or false if an error occurred during the SQL query.
		*/
		public function longSingleSearch ($mode, $search, $limit = 10) {
			// escape string in case there's any apostrophes in it
			$search = mysql_real_escape_string($search);
			
			switch ($mode) {
				case "songs":
					$sql = "SELECT SongId, SongName, ArtistName, RecordName FROM SongsView WHERE SongName LIKE '%" . $search . "%' OR ArtistName LIKE '%" . $search . "%' LIMIT :limit";
			
					try {
						$query = $this->db->prepare($sql);
						$query->execute( array(':limit' => $limit) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				case "artists":
					$sql = "SELECT id AS 'ArtistId', name AS 'ArtistName' FROM artists WHERE name LIKE '%" . $search . "%' LIMIT :limit";
			
					try {
						$query = $this->db->prepare($sql);
						$query->execute( array(':limit' => $limit) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				case "records":
					$sql = "SELECT re.id AS 'RecordId', re.name AS 'RecordName', ar.name AS 'ArtistName' FROM records re INNER JOIN artists ar ON re.aid = ar.id WHERE re.name LIKE '%" . $search . "%' LIMIT :limit";
			
					try {
						$query = $this->db->prepare($sql);
						$query->execute( array(':limit' => $limit) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				default:
					return false;
					
					break;
			}
		}
		
		/**
			Performs a multi search on the database for a song, artist or record.
			Multi search means that the query consists of at least two words.
			This mode performs a grouped-query, which means that searching for "Ashes Bowie" will return the song "Ashes To Ashes" by David Bowie".
			Like the single search, every word up to three characters is only matched from the beginning, every word with more than three letters is matched anywhere.
			Input sanitation is done before this step, so the input is guaranteed to be stripped off of any scripts tags.
			
			Returned is an array containing the results, or false if an error occurred during the SQL query.
		*/
		public function multiSearch ($mode, $search_array, $limit = 10) {
			$term_query = "";
			
			switch ($mode) {
				case "songs":
					foreach ($search_array as $term) {
						$term = mysql_real_escape_string($term);
						
						// for multi-search, we always match like "%XXX%"
						$tq = "( SELECT SongId FROM SongsView WHERE SongName LIKE '%" . $term . "%' OR ArtistName LIKE '%" . $term . "%' )";
						
						if ($term_query != "") {
							$term_query .= " UNION ALL ";
						}
						
						$term_query .= $tq;
					}
					
					$count_query = " SELECT SongId, COUNT(SongId) AS 'SongCount' FROM ( " . $term_query . " ) count_query GROUP BY SongId HAVING COUNT(SongId) >= :term_count ";
				
					$main_sql = "SELECT sv.SongId, sv.SongName, sv.ArtistName, sv.RecordName FROM ( " . $count_query . " ) sub_query INNER JOIN SongsView sv ON sv.SongId = sub_query.SongId LIMIT :limit";
					
					try {
						$query = $this->db->prepare($main_sql);
						$query->execute( array(':limit' => $limit, ':term_count' => count($search_array)) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				case "artists":
					foreach ($search_array as $term) {
						$term = mysql_real_escape_string($term);
						
						// for multi-search, we always match like "%XXX%"
						$tq = "( SELECT id FROM artists WHERE name LIKE '%" . $term . "%' )";
						
						if ($term_query != "") {
							$term_query .= " UNION ALL ";
						}
						
						$term_query .= $tq;
					}
					
					$count_query = " SELECT id, COUNT(id) AS 'ArtistCount' FROM ( " . $term_query . " ) count_query GROUP BY id HAVING COUNT(ArtistCount) >= :term_count ";
				
					$main_sql = "SELECT ar.id AS 'ArtistId', ar.name AS 'ArtistName' FROM ( " . $count_query . " ) sub_query INNER JOIN artists ar ON ar.id = sub_query.id LIMIT :limit";
					
					try {
						$query = $this->db->prepare($main_sql);
						$query->execute( array(':limit' => $limit, ':term_count' => count($search_array)) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				case "records":
					foreach ($search_array as $term) {
						$term = mysql_real_escape_string($term);
						
						// for multi-search, we always match like "%XXX%"
						$tq = "( SELECT id FROM records WHERE name LIKE '%" . $term . "%' )";
						
						if ($term_query != "") {
							$term_query .= " UNION ALL ";
						}
						
						$term_query .= $tq;
					}
					
					$count_query = " SELECT id, COUNT(id) AS 'RecordCount' FROM ( " . $term_query . " ) count_query GROUP BY id HAVING COUNT(RecordCount) >= :term_count ";
				
					$main_sql = "SELECT re.id AS 'RecordId', re.name AS 'RecordName', ar.id AS 'ArtistId', ar.name AS 'ArtistName' FROM ( " . $count_query . " ) sub_query INNER JOIN records re ON re.id = sub_query.id INNER JOIN artists ar ON re.aid = ar.id LIMIT :limit";
					
					try {
						$query = $this->db->prepare($main_sql);
						$query->execute( array(':limit' => $limit, ':term_count' => count($search_array)) );
						
						return $query->fetchAll(PDO::FETCH_ASSOC);
					} catch (Exception $e) {
						return false;
					}
					
					break;
					
				default:
					return false;
					
					break;
			}
		}
		
		/**
			Sets the search tags for a song.
			All tags are stored in lower case, for easier finding.
			You can specify tags not to be indexed for a song through the "search_tags_exclude" property in config table
		*/
		public function setSearchTags ($sid, $tags) {
			$success = true;
			
			// Load tags excluded from search from config table
			$exclude_setting = 'search_tags_exclude';
			
			$exclude_setting_exists = $this->propertyExists($exclude_setting);
			
			if ($exclude_setting_exists) {
				$excluded = explode(';', $this->getConfig($exclude_setting));
			}
		
			// strip sid from code tags
			$sid = strip_tags($sid);
		
			// If only a single tag has been passed, it is still put in an array for linear processing
			if (!is_array($tags)) {
				$tags = array($tags);
			}
			
			// tags are put to lowercase, including special characters
			$length = count($tags);
			
			for ($i = 0; $i < $length; $i++) {
				$tags[$i] = mb_strtolower($tags[$i], 'UTF-8');
			}
			
			// Remove the tags specified as excluded from indexing
			if ($exclude_setting_exists AND $excluded !== false) {
				$tags = array_diff($tags, $excluded);
			}
			
			// Check if search tags already exist for this song id, remove them if so
			if ($this->searchTagsExists($sid)) {
				$this->removeSearchTags($sid);
			}
			
			// Add search tags for this song id
			foreach ($tags as $tag) {
				// strip tag from code tags
				$tag = strip_tags($tag);
				
				// add search tag to table
				if (!$this->addSearchTag($sid, $tag)) {
					$success = false;
				}
			}
			
			return $success;
		}
		
		/**
			Returns the search tags for a song in an array.
			Array is empty if no tags are stored for the specified song id.
		*/
		public function getSearchTags ($sid) {
			// strip input from code tags
			$sid = strip_tags($sid);
			
			$return = array();
			
			$sql = "SELECT * FROM search WHERE sid = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				$result = $query->fetchAll();
				
				foreach ($result as $tag) {
					array_push($return, $tag['tag']);
				}
			}
			
			return $return;
		}
		
		private function addSearchTag ($sid, $tag) {
			$sql = "INSERT INTO search (sid, tag) VALUES (:sid, :tag)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid, ':tag' => $tag) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new search tag '" . $tag . "' for song id " . $sid . " with id " . $this->db->lastInsertId());
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new search tag '" . $tag . "' for song id " . $sid . " \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Looks if search tags exists for the specified song id
			Returns true if so, false if otherwise
		*/
		private function searchTagsExists ($sid) {
			// strip input from code tags
			$sid = strip_tags($sid);
			
			$sql = "SELECT id FROM search WHERE sid = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Removes search tags for the specified sid.
		*/
		private function removeSearchTags ($sid) {
			// strip input from code tags
			$sid = strip_tags($sid);
			
			$sql = "DELETE FROM search WHERE sid = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
		}
		
		/**
			Adds a new MMLink entry. This is necessary for keeping up a connection
			between deleted songs in MediaMonkey, like when an artist releases a single;
			this entry might be deleted in MediaMonkey when the same song is then featured
			on an album to avoid having the same song in the library twice.
			With the MMLink, the playing history for the song stays consistent.
		*/
		public function addMMLink ($sid, $mmid, $added) {
			// strip input from code tags
			$sid = strip_tags($sid);
			$mmid = strip_tags($mmid);
			$added = strip_tags($added);
			
			$sql = "INSERT INTO mmlink (sid, mmid, added) VALUES (:sid, :mmid, :added)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid, ':mmid' => $mmid, ':added' => $added) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new MM link between song id " . $sid . " and MM id " . $mmid . " (added on " . $added . ") with id " . $this->db->lastInsertId());
				}
			
				return $this->db->lastInsertId();
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new MM link between song id " . $sid . " and MM id " . $mmid . " (added on " . $added . ")\n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates an existing MM link.
		*/
		public function updateMMLink ($mmlid, $sid, $mmid) {
			// strip input from code tags
			$mmlid = strip_tags($mmlid);
			$sid = strip_tags($sid);
			$mmid = strip_tags($mmid);
			
			$sql = "UPDATE mmlink SET sid = :sid, mmid = :mmid WHERE id = :mmlid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid, ':mmid' => $mmid, ':mmlid' => $mmlid) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated MM link between song id " . $sid . " and MM id " . $mmid . " with MM link id " . $mmlid);
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update MM link between song id " . $sid . " and MM id " . $mmid . " with MM link id " . $mmlid . "\n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Returns the song id for the specified MediaMonkey id.
			If no song id is found, false is returned
		*/
		public function getSidFromMMId ($mmid) {
			// strip input from code tags
			$mmid = strip_tags($mmid);
			
			$sql = "SELECT sid FROM mmlink WHERE mmid = :mmid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':mmid' => $mmid) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched song id " . $fetched['sid'] . " for mmid " . $mmid);
				}
			
				return $fetched['sid'];
			} else {
				return false;
			}
		}
		
		/**
			******* ATTENTION: This method is not to be used as of version 1.0 ******
			* It only provides a hotfix method to correct a bug introduced in       *
			* version 0.1, where the song added dates may not have been imported    *
			* correctly															    *
			*************************************************************************
			
			Returns the MediaMonkey id for the specified song id.
			If no MM id is found, false is returned
		*/
		public function getMMIdfromSid ($sid) {
			$sql = "SELECT mmid FROM mmlink WHERE sid = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				return $fetched['mmid'];
			} else {
				return false;
			}
		}
		
		/**
			Returns an array containing all MM links for the specified song id.
			If no MM links exist for the song id, an empty array is returned.
		*/
		public function getMMLinks ($sid) {
			// strip input from code tags
			$sid = strip_tags($sid);
			
			$return = array();
			
			$sql = "SELECT id FROM mmlink WHERE sid = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				$result = $query->fetchAll();
				
				foreach ($result as $mmlink) {
					array_push($return, $mmlink['mmid']);
				}
			}
			
			return $return;
		}
		
		/**
			Checks if an MM link exists for the specified song id.
			Return true if one or more MM links exist, false otherwise.
		*/
		public function MMLinksExist ($sid) {
			// strip input from code tags
			$sid = strip_tags($sid);
			
			$sql = "SELECT id FROM mmlink WHERE sid = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Removes all existing MM links in the database for the specified song id.
			Returns true if whole operation was successful, false if otherwise.
		*/
		public function removeAllMMLinks ($sid) {
			$success = true;
			
			$mmlinks = $this->getMMLinks($sid);
			
			foreach ($mmlinks as $mmlink) {
				if (!$this->removeMMLink($sid, $mmlink)) {
					$success = false;
				}
			}
			
			return $success;
		}
		
		/**
			Removes an MM link from the database.
			Only a specific connection is removed (e.g. a sid / mmid pair)
			If you want to remove all MM links for a sid, use removeAllMMLinks.
		*/
		public function removeMMLink ($sid, $mmid) {
			// strip input from code tags
			$sid = strip_tags($sid);
			$mmid = strip_tags($mmid);
			
			$sql = "REMOVE FROM mmlink WHERE sid = :sid AND mmid = :mmid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid, ':mmid' => $mmid) );
			
			if ($query->rowCount() > 0) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
			Persists a device. The function updates the device if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't
		*/
		public function saveDevice($id, $name, $typeid, $active) {
			if ($id <= 0) {
				// add new device
				$success = $this->addDevice($name, $typeid, $active);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing device
				$success = $this->updateDevice($id, $name, $typeid, $active);
			}
			
			return $success;
		}
		
		/**
			Adds a new device to the database.
			If adding was successful, the newly assigned device id is returned, false otherwise.
		*/
		public function addDevice ($name, $typeid, $active) {
			// strip input from code tags
			$name = strip_tags($name);
			$typeid = strip_tags($typeid);
			
			$sql = "INSERT INTO devices (name, typeid, active) VALUES (:name, :typeid, :active)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':name' => $name, ':typeid' => $typeid, ':active' => $active) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
				
				// Mobile database entry
				$mobile_sql = "INSERT INTO devices (_id, name, active) VALUES (:id, :name, :active)";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				//$mobile_query->execute( array(':id' => $inserted, ':name' => $name, ':active' => $active) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new device '" . $name . "', type id " . $typeid . ", active " . $active . " with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new device '" . $name . "', type id " . $typeid . ", active " . $active . " \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates an existing device.
			Returns true if update was successful, false if otherwise.
		*/
		public function updateDevice ($id, $name, $typeid, $active) {
			// strip input from code tags
			$id = strip_tags($id);
			$name = strip_tags($name);
			$typeid = strip_tags($typeid);
			
			$sql = "UPDATE devices SET name = :name, typeid = :typeid, active = :active WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':name' => $name, ':typeid' => $typeid, ':active' => $active) );
			
			if ($query->rowCount() > 0) {
				// Mobile database entry
				$mobile_sql = "UPDATE devices SET name = :name, active = :active WHERE _id = :id";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				$mobile_query->execute( array(':id' => $id, ':name' => $name, ':active' => $active) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in devices with id " . $id . " [ name: " . $name . ", typeid: " . $typeid . ", active: " . $active . " ]");
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in devices with id " . $id . " [ name: " . $name . ", typeid: " . $typeid . ", active: " . $active . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Returns an array containing all devices.
			If no devices exist, null is returned.
			By passing true as the $active flag, active devices
			will be returned before the in-active ones.
		*/
		public function getDevices($active = true) {
			$order = $active ? "de.active DESC, de.name" : "de.name";
			
			// get all devices
			$sql = "SELECT
						de.id AS 'DeviceId',
						de.name AS 'DeviceName',
						de.typeid AS 'DeviceDeviceTypeId',
						dt.name AS 'DeviceDeviceTypeName',
						dt.iconid AS 'DeviceDeviceTypeIconId',
						de.active AS 'DeviceActive'
					FROM
						devices de INNER JOIN
						device_type dt ON dt.id = de.typeid
					ORDER BY
						" . $order;
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetchAll(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns the device with the matching id from the database.
			If no device is found with this id, null is returned.
		*/
		public function getDevice($id) {
			// get device
			$sql = "SELECT
						de.id AS 'DeviceId',
						de.name AS 'DeviceName',
						de.typeid AS 'DeviceDeviceTypeId',
						dt.name AS 'DeviceDeviceTypeName',
						dt.iconid AS 'DeviceDeviceTypeIconId',
						de.active AS 'DeviceActive'
					FROM
						devices de INNER JOIN
						device_type dt ON dt.id = de.typeid
					WHERE
						de.id = :id
					ORDER BY
						de.name";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns an array containing all icons.
			If no icons exist, null is returned.
		*/
		public function getIcons() {
			// get all icons
			$sql = "SELECT
						ic.id AS 'IconId',
						ic.name AS 'IconName',
						ic.type AS 'IconType',
						ic.path AS 'IconPath'
					FROM
						icons ic
					ORDER BY
						ic.name";
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetchAll(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns the icon with the matching id from the database.
			If no icon is found with this id, null is returned.
		*/
		public function getIcon($id) {
			// get icon
			$sql = "SELECT
						ic.id AS 'IconId',
						ic.name AS 'IconName',
						ic.type AS 'IconType',
						ic.path AS 'IconPath'
					FROM
						icons ic
					WHERE
						ic.id = :id
					ORDER BY
						ic.name";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Persists an icon. The function updates the icon if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't
		*/
		public function saveIcon($id, $name, $type, $path) {
			if ($id <= 0) {
				// add new icon
				$success = $this->addIcon($name, $type, $path);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing icon
				$success = $this->updateIcon($id, $name, $type, $path);
			}
			
			return $success;
		}
		
		/**
			Adds an icon to the database.
			Returns the id of the newly inserted icon, false if an error occurred.
		*/
		public function addIcon($name, $type, $path) {
			$sql = "INSERT INTO icons (name, type, path) VALUES (:name, :type, :path)";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':name' => $name, ':type' => $type, ':path' => $path) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new icon [ name: " . $name . ", type: " . $type . ", path: " . $path . " ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new icon [ name: " . $name . ", type: " . $type . ", path: " . $path . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates an icon in the database.
			Returns true if the update process was successful or nothing was changed, false if an error occurred.
		*/
		public function updateIcon($id, $name, $type, $path) {
			$sql = "UPDATE icons SET name = :name, type = :type, path = :path WHERE id = :id";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':name' => $name, ':type' => $type, ':path' => $path) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				// update successful or nothing was changed
				return true;
			} else {
				// update not successful
				$this->addLog(__FUNCTION__, "error", "tried to update icon with id " . $id . " and name " . $name . ", type " . $type . ", path " . $path . "\n" . implode(" / ", $query->errorInfo()));
				return false;
			}
		}
		
		/**
			Returns an array containing all config properties.
			If no properties exist, null is returned.
		*/
		public function getConfigProperties() {
			// get all config properties
			$sql = "SELECT
						co.id AS 'ConfigId',
						co.property AS 'ConfigProperty',
						co.value AS 'ConfigValue'
					FROM
						config co
					ORDER BY
						co.property";
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetchAll(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns the config property with the matching id from the database.
			If no property is found with this id, null is returned.
		*/
		public function getConfigProperty($id) {
			// get config property
			$sql = "SELECT
						co.id AS 'ConfigId',
						co.property AS 'ConfigProperty',
						co.value AS 'ConfigValue'
					FROM
						config co
					WHERE
						co.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Persists a config property. The function updates the property if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't
		*/
		public function saveConfigProperty($id, $property, $value) {
			if ($id <= 0) {
				// add new config property
				$success = $this->addConfigProperty($property, $value);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing config property
				$success = $this->updateConfigProperty($id, $property, $value);
			}
			
			return $success;
		}
		
		/**
			Adds a config property to the database.
			Returns the id of the newly inserted property, false if an error occurred.
		*/
		public function addConfigProperty($property, $value) {
			$sql = "INSERT INTO config (property, value) VALUES (:property, :value)";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':property' => $property, ':value' => $value) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new config property [ property: " . $property . ", value: " . $value . " ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new config property [ property: " . $property . ", value: " . $value . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates a config property in the database.
			Returns true if the update process was successful or nothing was changed, false if an error occurred.
		*/
		public function updateConfigProperty($id, $property, $value) {
			$sql = "UPDATE config SET property = :property, value = :value WHERE id = :id";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':property' => $property, ':value' => $value) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				// update successful or nothing was changed
				return true;
			} else {
				// update not successful
				$this->addLog(__FUNCTION__, "error", "tried to update config property with id " . $id . " and property " . $property . ", value " . $value . "\n" . implode(" / ", $query->errorInfo()));
				return false;
			}
		}
		
		/**
			Returns an array containing all activities.
			If no activities exist, null is returned.
		*/
		public function getActivities() {
			// get all activities
			$sql = "SELECT
						ac.id AS 'ActivityId',
						ac.name AS 'ActivityName',
						ac.color AS 'ActivityColor'
					FROM
						activities ac
					ORDER BY
						ac.name";
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetchAll(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns the activity with the matching id from the database.
			If no activity is found with this id, null is returned.
		*/
		public function getActivity($id) {
			// get activity
			$sql = "SELECT
						ac.id AS 'ActivityId',
						ac.name AS 'ActivityName',
						ac.color AS 'ActivityColor'
					FROM
						activities ac
					WHERE
						ac.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Persists an activity. The function updates the activity if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't
		*/
		public function saveActivity($id, $name, $color) {
			if ($id <= 0) {
				// add new activity
				$success = $this->addActivity($name, $color);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing activity
				$success = $this->updateActivity($id, $name, $color);
			}
			
			return $success;
		}
		
		/**
			Adds an activity to the database.
			Returns the id of the newly inserted activity, false if an error occurred.
		*/
		public function addActivity($name, $color) {
			$sql = "INSERT INTO activities (name, color) VALUES (:name, :color)";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':name' => $name, ':color' => $color) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new activity [ name: " . $name . ", color: " . $color . " ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new activity [ name: " . $name . ", color: " . $color . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates an activity in the database.
			Returns true if the update process was successful or nothing was changed, false if an error occurred.
		*/
		public function updateActivity($id, $name, $color) {
			$sql = "UPDATE activities SET name = :name, color = :color WHERE id = :id";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':name' => $name, ':color' => $color) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				// update successful or nothing was changed
				return true;
			} else {
				// update not successful
				$this->addLog(__FUNCTION__, "error", "tried to update activity with id " . $id . " and name " . $name . ", color " . $color . "\n" . implode(" / ", $query->errorInfo()));
				return false;
			}
		}
		
		/**
			Returns an array containing all countries.
			If no countries exist, null is returned.
		*/
		public function getCountries() {
			// get all countries
			$sql = "SELECT
						co.id AS 'CountryId',
						co.name AS 'CountryName',
						co.short AS 'CountryShort'
					FROM
						countries co
					ORDER BY
						co.name";
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetchAll(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns the country with the matching id from the database.
			If no country is found with this id, null is returned.
		*/
		public function getCountry($id) {
			// get country
			$sql = "SELECT
						co.id AS 'CountryId',
						co.name AS 'CountryName',
						co.short AS 'CountryShort'
					FROM
						countries co
					WHERE
						co.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Persists a country. The function updates the country if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't
		*/
		public function saveCountry($id, $name, $short) {
			if ($id <= 0) {
				// add new country
				$success = $this->addCountry($name, $short);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing country
				$success = $this->updateCountry($id, $name, $short);
			}
			
			return $success;
		}
		
		/**
			Adds a country to the database.
			Returns true if the country was inserted successfully, false if an error occurred.
		*/
		public function addCountry($name, $short) {
			$sql = "INSERT INTO countries (name, short) VALUES (:name, :short)";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':short' => $short, ':name' => $name) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new country [ name: " . $name . ", short: " . $short . " ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new country [ name: " . $name . ", short: " . $short . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates an country in the database.
			Returns true if the update process was successful or nothing was changed, false if an error occurred.
		*/
		public function updateCountry($id, $name, $short) {
			$sql = "UPDATE countries SET name = :name, short = :short WHERE id = :id";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':short' => $short, ':name' => $name) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				// update successful or nothing was changed
				return true;
			} else {
				// update not successful
				$this->addLog(__FUNCTION__, "error", "tried to update country with name " . $name . ", short " . $short . "\n" . implode(" / ", $query->errorInfo()));
				return false;
			}
		}
		
		/**
			Returns the name of the specified device id.
			False is returned if device does not exist.
		*/
		public function getDeviceName ($id) {
			// strip input from code tags
			$id = strip_tags($id);
			
			$sql = "SELECT name FROM devices WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':name' => $name) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetch();
			
				if ($this->logging AND $this->verbose) {
					$this->addLog(__FUNCTION__, "success", "fetched device with id " . $id);
				}
			
				return $fetched['name'];
			} else {
				return false;
			}
		}
		
		/**
			Returns an array containing all possible MM link candidates (specified by song ids)
			If no candidates were found, false is returned
		*/
		public function getPossibleMMLinkCandidates ($sid) {
			// strip input from code tags
			$sid = strip_tags($sid);
			
			// details of the song
			$song_details = $this->getSong($sid);
			
			// string occurence of the word "live", for safe-checking if a candidate is actually just a live version of the song
			$live_str = "live";
						
			// does "live" occur in the original song's record name?
			$song_live = strpos(strtolower($song_details["RecordName"]), $live_str);
			
			$candidates = array();
			
			$sql = "SELECT id FROM songs WHERE (name, aid) = ( SELECT name, aid FROM songs WHERE id = :sid LIMIT 1 )";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetchAll();
				
				foreach ($fetched as $candidate) {
					$candidate_id = $candidate['id'];
					
					if ($candidate_id != $sid) {
						// extra safe check: if only one song contains "live" in the record title, do not add it to the suggestions
						// if this is the case, it is very likely that it is just a live version of the song - it is not recommended to link the songs in this case.
						$candidate_details = $this->getSong($candidate_id);
						
						// does "live" occur in the candidate song's record name?
						$candidate_live = strpos(strtolower($candidate_details["RecordName"]), $live_str);
						
						// it's only a candidate if either live occurs in both song's and candidate's record names, or in none of them
						if (($song_live === false AND $candidate_live === false) OR ($song_live !== false AND $candidate_live !== false)) {
							array_push($candidates, $candidate['id']);
						}
					}
				}
				
				return $candidates;
			} else {
				return false;
			}
		}
		
		/**
			Adds a new MediaMonkey link candidate for a song id.
			If adding was successful, the newly assigned MM link candidate id is returned, false otherwise.
		*/
		public function addMMLinkCandidate ($sid, $sid_candidate) {
			// strip input from code tags
			$sid = strip_tags($sid);
			$sid_candidate = strip_tags($sid_candidate);
			
			$sql = "INSERT INTO mmlink_candidates (sid, sid_candidate) VALUES (:sid, :sid_candidate)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid, ':sid_candidate' => $sid_candidate) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new MM link candidate for song id " . $sid . " with song id " . $sid_candidate . " with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new MM link candidate for song id " . $sid . " with song id " . $sid_candidate . "\n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Adds a MediaMonkey link connection.
			The child will be removed from this database, the played history is added to the parent.
		*/
		public function addMMLinkConnection ($parent_id, $child_id) {
			// add connection (necessary if there's an upload from an old local database that has the old sid)
			$added = date("Y-m-d");
			
			$sql = "INSERT INTO song_connection (parent_id, child_id, added) VALUES (:parent_id, :child_id, :added)";
			$query = $this->db->prepare($sql);
			$exec_connection = $query->execute( array(':parent_id' => $parent_id, ':child_id' => $child_id, ':added' => $added) );
			
			if ($query->rowCount() > 0 OR $exec_connection !== false) {
				$success_connection = true;
			} else {
				$success_connection = false;
			}
			
			// correct mmlink
			$sql = "UPDATE mmlink SET sid = :parent_id WHERE sid = :child_id";
			$query = $this->db->prepare($sql);
			$exec_mmlink = $query->execute( array(':parent_id' => $parent_id, ':child_id' => $child_id) );
			
			if ($query->rowCount() > 0 OR $exec_mmlink !== false) {
				$success_mmlink = true;
			} else {
				$success_mmlink = false;
			}
			
			// correct played
			$sql = "UPDATE played SET sid = :parent_id WHERE sid = :child_id";
			$query = $this->db->prepare($sql);
			$exec_played = $query->execute( array(':parent_id' => $parent_id, ':child_id' => $child_id) );
			
			if ($query->rowCount() > 0 OR $exec_played !== false) {
				$success_played = true;
			} else {
				$success_played = false;
			}
		
			// add comment to parent
			$comment = "merged song with id " . $child_id;
			
			$sql = "UPDATE songs SET comment = :comment WHERE id = :parent_id";
			$query = $this->db->prepare($sql);
			$exec_comment = $query->execute( array(':comment' => $comment, ':parent_id' => $parent_id) );
			
			if ($query->rowCount() > 0 OR $exec_comment !== false) {
				$success_comment = true;
			} else {
				$success_comment = false;
			}
			
			// check if child's record is now empty - if so, delete it
			$sql = "SELECT
						re.id AS 'RecordId',
						COUNT(*) AS 'RecordSongCount'
					FROM
						records re INNER JOIN
						songs so ON re.id = so.rid
					WHERE
						re.id = (
							SELECT
								re.id AS 'RecordId'
							FROM
								songs so INNER JOIN
								records re ON re.id = so.rid
							WHERE
								so.id = :child_id
							)";
							
			$query = $this->db->prepare($sql);
			$exec_record_count = $query->execute( array(':child_id' => $child_id) );
			
			if ($query->rowCount() > 0 OR $exec_record_count !== false) {
				$result = $query->fetch(PDO::FETCH_ASSOC);

				// if it's only this song, delete the record
				if ($result["RecordSongCount"] <= 1) {
					$sql = "DELETE FROM records WHERE id = :record_id";
					
					$query_del_record = $this->db->prepare($sql);
					$exec_record_delete = $query_del_record->execute( array(':record_id' => $result["RecordId"]) );
					
					if ($query->rowCount() > 0 OR $exec_record_delete !== false) {
						$success_record_count = true;
						
						// delete record from mobile database too
						$mobile_sql = "DELETE FROM records WHERE _id = :record_id";
						$mobile_query = $this->mobile_db->prepare($mobile_sql);
						$mobile_query->execute( array(':record_id' => $result["RecordId"]) );
					} else {
						$success_record_count = false;
					}
				} else {
					// there's more than this song in the record, we don't have to delete it
					$success_record_count = true;
				}
			} else {
				$success_record_count = false;
			}
			
			// delete child
			$sql = "DELETE FROM songs WHERE id = :child_id";
			$query = $this->db->prepare($sql);
			$exec_delete = $query->execute( array(':child_id' => $child_id) );
			
			if ($query->rowCount() > 0 OR $exec_delete !== false) {
				$success_delete = true;
			} else {
				$success_delete = false;
			}
			
			// delete child from mobile database too
			$mobile_sql = "DELETE FROM songs WHERE _id = :child_id";
			$mobile_query = $this->mobile_db->prepare($mobile_sql);
			$mobile_query->execute( array(':child_id' => $child_id) );
			
			
			return $success_connection AND $success_mmlink AND $success_played AND $success_comment AND $success_record_count AND $success_delete;
		}
		
		/**
			If a song was linked to another, the original song id is no longer in the database.
			For this purpose, it is possible to get the correct song id for a song id that no longer exists in the database because an MM link was added.
		*/
		public function getConnectionParent($sid) {
			$sql = "SELECT parent_id AS 'ParentSongId' FROM song_connection WHERE child_id = :sid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				$song = $query->fetch(PDO::FETCH_ASSOC);
				
				return $song["ParentSongId"];
			}
			
			return false;
		}
		
		/**
			Returns the MM link candidate entry for the specified id.
			If id does not exist, false is returned.
		*/
		private function getMMLinkCandidate ($mcid) {
			// strip input from code tags
			$mcid = strip_tags($mcid);
			
			$sql = "SELECT sid, sid_candidate FROM mmlink_candidates WHERE mcid = :mcid";
			$query = $this->db->prepare($sql);
			$query->execute( array(':mcid' => $mcid) );
			
			if ($query->rowCount() > 0) {
				return $query->fetch();
			} else {
				return false;
			}
		}
		
		/**
			Returns an array containing all device types.
			If no device types exist, null is returned.
		*/
		public function getDeviceTypes() {
			// get all icons
			$sql = "SELECT
						dt.id AS 'DeviceTypeId',
						dt.name AS 'DeviceTypeName',
						dt.iconid AS 'DeviceTypeIconId'
					FROM
						device_type dt
					ORDER BY
						dt.name";
						
			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetchAll(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Returns the device type with the matching id from the database.
			If no device type is found with this id, null is returned.
		*/
		public function getDeviceType($id) {
			// get icon
			$sql = "SELECT
						dt.id AS 'DeviceTypeId',
						dt.name AS 'DeviceTypeName',
						dt.iconid AS 'DeviceTypeIconId'
					FROM
						device_type dt
					WHERE
						dt.id = :id";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id) );
			
			if ($query->rowCount() > 0) {
				$fetch = $query->fetch(PDO::FETCH_ASSOC);
	
				return $fetch;
			} else {
				return null;
			}
		}
		
		/**
			Persists a device type. The function updates the device type if it already exists or adds it to the database if it doesn't exist.
			Returns true if the process was successful, false if it wasn't.
		*/
		public function saveDeviceType($id, $name, $iconid) {
			if ($id <= 0) {
				// add new device type
				$success = $this->addDeviceType($name, $iconid);
				
				// set success to true if a correct id was returned
				if ($success !== false AND $success > 0) {
					$success = true;
				}
			} else {
				// update existing device type
				$success = $this->updateDeviceType($id, $name, $iconid);
			}
			
			return $success;
		}
		
		/**
			Adds a device type to the database.
			Returns the id of the newly inserted device type, false if an error occurred.
		*/
		public function addDeviceType($name, $iconid) {
			$sql = "INSERT INTO device_type (name, iconid) VALUES (:name, :iconid)";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':name' => $name, ':iconid' => $iconid) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new device type [ name: " . $name . ", iconid: " . $iconid . " ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new device type [ name: " . $name . ", iconid: " . $iconid . " ]  \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates an icon in the database.
			Returns true if the update process was successful or nothing was changed, false if an error occurred.
		*/
		public function updateDeviceType($id, $name, $iconid) {
			$sql = "UPDATE device_type SET name = :name, iconid = :iconid WHERE id = :id";
			
			$query = $this->db->prepare($sql);
			$success = $query->execute( array(':id' => $id, ':name' => $name, ':iconid' => $iconid) );
			
			if ($query->rowCount() > 0 OR $success !== false) {
				// update successful or nothing was changed
				return true;
			} else {
				// update not successful
				$this->addLog(__FUNCTION__, "error", "tried to update device type with id " . $id . " and name " . $name . ", iconid " . $iconid . "\n" . implode(" / ", $query->errorInfo()));
				return false;
			}
		}
		
		/**
			Returns the play history for the specified song
			If no result is found, return will be null
		*/
		public function getPlayedSongHistory ($sid, $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$sid = strip_tags($sid);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			$sql = "SELECT
						devid,
						actid,
						timestamp
					FROM
						played
					WHERE
						sid = :sid
					ORDER BY
						timestamp DESC";

			$sql .= $this->getQueryLimit($limit_low, $limit_high);
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched song history for song with id " . $sid);
				}
				
				return $query->fetchAll();
			} else {
				return null;
			}
		}
		
		/**
			Returns the last successful charts compile timestamp.
			Returns an array containing the info about the chart, false otherwise.
		*/
		public function getChartInfo($chart_type, $year = 0) {						
			if ($year > 0) {
				$year_where = " AND ch.year = :year";
				$exec_array = array(':chart_type' => $chart_type, ':year' => $year);
			} else {
				$year_where = "";
				$exec_array = array(':chart_type' => $chart_type);
			}
			
			$sql = "SELECT
						ch.id AS 'ChartId',
						ch.chart_type AS 'ChartType',
						ch.year AS 'ChartYear',
						ch.timestamp AS 'ChartCompileTimestamp'
					FROM
						charts ch
					WHERE
						ch.chart_type = :chart_type" . $year_where;
						
			$query = $this->db->prepare($sql);
			$query->execute( $exec_array );
			
			if ($query->rowCount() > 0) {
				return $query->fetch(PDO::FETCH_ASSOC);
			} else {
				return false;
			}
		}
		
		/**
			Compiles the charts for songs.
			It also handles the calculation for nationality and activity additional statistics.
			Returns the chart id of the chart container entry.
		*/
		public function compileCharts($type, $songs, $artists, $records, $year = 0) {
			// get old charts info
			$old_charts = $this->getChartInfo($type);
			
			// delete old charts
			if ($old_charts !== false) {
				$this->deleteCharts($old_charts["ChartId"]);
			}
			
			// create container entry
			$chart_id = $this->addChartContainerEntry($type, $year);
			
			// write songs
			if (count($songs) > 0)
				$this->writeChartsContent($chart_id, "songs", $songs);
			
			// write artists
			if (count($artists) > 0)
				$this->writeChartsContent($chart_id, "artists", $artists);
			
			// write records
			if (count($records) > 0)
				$this->writeChartsContent($chart_id, "records", $records);
			
			return $chart_id;
		}
		
		/**
			Creates a chart container entry and returns its id.
			If the creation was unsuccessful, false is returned.
		*/
		private function addChartContainerEntry($chart_type, $year = 0) {
			if ($year > 0) {
				$fields = "chart_type, year";
				$values = ":chart_type, :year";
				$exec_array = array(':chart_type' => $chart_type, ':year' => $year);
			} else {
				$fields = "chart_type";
				$values = ":chart_type";
				$exec_array = array(':chart_type' => $chart_type);
			}
			
			$sql = "INSERT INTO charts (" . $fields . ") VALUES (" . $values . ")";
			$query = $this->db->prepare($sql);
			$query->execute( $exec_array );
			
			if ($query->rowCount() > 0) {
				return $this->db->lastInsertId();
			}
			
			return false;
		}
		
		/**
			Utility function for updating the timestamp of a chart container entry.
			The actual timestamp updatting is done by MySQL.
		*/
		public function updateChartContainerTimestamp($chart_id) {
			$timestamp = new UnixTimestamp(mktime());
			
			$sql = "UPDATE charts SET timestamp = :timestamp WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $chart_id, ':timestamp' => $timestamp->convert2MysqlDateTime()) );
		}
		
		public function writeChartsContent($chart_id, $instance_type, $content) {
			// get instance name ("songs" -> "SongId", for getting right field from content array)
			$instance = substr($instance_type, 0, -1);
			$instance = strtoupper(substr($instance, 0, 1)) . substr($instance, 1) . "Id";
			
			$counter = 1;
			$rank = 1;
			$old_count = 0;
			
			foreach ($content as $entry) {
				$instance_id = $entry[$instance];
				$count = $entry["PlayedCount"];
				
				if ($count != $old_count) {
					// assign new rank if counts are different
					$rank = $counter;
				}
				
				$this->addChartsContentEntry($chart_id, $instance_type, $instance_id, $rank, $count);
				
				$old_count = $count;
				$counter++;
			}
		}
		
		private function addChartsContentEntry($chart_id, $instance_type, $instance_id, $rank, $count) {
			$sql = "INSERT INTO chart_content (chart_id, instance_type, instance_id, rank, cnt) VALUES (:chart_id, :instance_type, :instance_id, :rank, :cnt)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':chart_id' => $chart_id, ':instance_type' => $instance_type, ':instance_id' => $instance_id, ':rank' => $rank, ':cnt' => $count) );
		}
		
		/**
			Returns the content of the favourite song charts.
		*/
		public function getChartsContentFavouriteSongs($chart_id) {
			$sql = "SELECT
						so.id AS 'SongId',
						so.name AS 'SongName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						co.rank AS 'Rank',
						co.cnt AS 'PlayedCount'
					FROM
						chart_content co INNER JOIN
						songs so ON co.instance_id = so.id INNER JOIN
						artists ar ON so.aid = ar.id
					WHERE
						co.chart_id = :chart_id AND
						co.instance_type = 'songs'";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':chart_id' => $chart_id) );
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return array();
			}
		}
		
		/**
			Returns the content of the favourite artist charts.
		*/
		public function getChartsContentFavouriteArtists($chart_id) {
			$sql = "SELECT
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						co.rank AS 'Rank',
						co.cnt AS 'PlayedCount'
					FROM
						chart_content co INNER JOIN
						artists ar ON co.instance_id = ar.id
					WHERE
						co.chart_id = :chart_id AND
						co.instance_type = 'artists'";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':chart_id' => $chart_id) );
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return array();
			}
		}
		
		/**
			Returns the content of the favourite records charts.
		*/
		public function getChartsContentFavouriteRecords($chart_id) {
			$sql = "SELECT
						re.id AS 'RecordId',
						re.name AS 'RecordName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						co.rank AS 'Rank',
						co.cnt AS 'PlayedCount'
					FROM
						chart_content co INNER JOIN
						records re ON co.instance_id = re.id INNER JOIN
						artists ar ON re.aid = ar.id
					WHERE
						co.chart_id = :chart_id AND
						co.instance_type = 'records'";
						
			$query = $this->db->prepare($sql);
			$query->execute( array(':chart_id' => $chart_id) );
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return array();
			}
		}
		
		/**
			Deletes all info about a chart container entry in the database.
			All content and additional info are also deleted.
		*/
		private function deleteCharts($chart_id) {
			// delete additional info
			$sql = "DELETE FROM chart_additional WHERE chart_id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $chart_id) );
			
			// delete content
			$sql = "DELETE FROM chart_content WHERE chart_id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $chart_id) );
			
			 // delete chart container entry
			$sql = "DELETE FROM charts WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $chart_id) );
		}
		
		/**
			Returns the 250 most played songs in the database
		*/
		public function getMostPlayedSongs() {
			$sql = "SELECT
						so.id AS 'SongId',
						so.name AS 'SongName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						pc_q.PlayedCount AS 'PlayedCount'
					FROM
						(SELECT
							sid AS 'SongId',
							COUNT(sid) AS 'PlayedCount'
						FROM
							played
						GROUP BY
							sid
						ORDER BY
							PlayedCount DESC
						LIMIT 250) pc_q INNER JOIN
					songs so ON so.id = pc_q.SongId INNER JOIN
					artists ar ON ar.id = so.aid";

			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched most played songs");
				}
				
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return null;
			}
		}
		
		/**
			Returns the 250 most played artists in the database
		*/
		public function getMostPlayedArtists() {
			$sql = "SELECT
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						COUNT(pl.sid) AS 'PlayedCount'
					FROM
						artists ar INNER JOIN
						songs so ON so.aid = ar.id INNER JOIN
						played pl ON pl.sid = so.id
					GROUP BY
						ar.id
					ORDER BY
						PlayedCount DESC
					LIMIT 250";

			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched most played artists");
				}
				
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return null;
			}
		}
		
		/**
			Returns the 250 most played records in the database
		*/
		public function getMostPlayedRecords() {
			$sql = "SELECT
						pl_q.RecordId,
						pl_q.RecordName,
						pl_q.ArtistId,
						ar.name AS 'ArtistName',
						pl_q.PlayedCount
					FROM
						(SELECT
							re.id AS 'RecordId',
							re.name AS 'RecordName',
							re.aid AS 'ArtistId',
							COUNT(pl.sid) AS 'PlayedCount'
						FROM
							records re INNER JOIN
							songs so ON so.rid = re.id INNER JOIN
							played pl ON pl.sid = so.id
						GROUP BY
							re.id
						ORDER BY
							PlayedCount DESC
						LIMIT 250) pl_q INNER JOIN
						artists ar ON ar.id = pl_q.ArtistId";

			$query = $this->db->prepare($sql);
			$query->execute();
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched most played records");
				}
				
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return null;
			}
		}
		
		/**
			Returns the 20 most played songs over the last 20 days.
		*/
		public function getTop2020Songs() {
			$date_from = new UnixTimestamp(mktime(0, 0, 0, date("m"), date("d") - 21, date("Y")));
			$date_to = new UnixTimestamp(mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
			$limit_low = 0;
			$limit_high = 20;
			
			return $this->getPlayedSongStatistics($date_from->convert2MysqlDate(), $date_to->convert2MysqlDate(), $limit_low, $limit_high);
		}
		
		/**
			Returns the 20 most played artists over the last 20 days.
		*/
		public function getTop2020Artists() {
			$date_from = new UnixTimestamp(mktime(0, 0, 0, date("m"), date("d") - 21, date("Y")));
			$date_to = new UnixTimestamp(mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
			$limit_low = 0;
			$limit_high = 20;
			
			return $this->getPlayedArtistStatistics($date_from->convert2MysqlDate(), $date_to->convert2MysqlDate(), $limit_low, $limit_high);
		}
		
		/**
			Returns the play history for the specfied date
			If no result is found, null is returned
		*/
		public function getPlayedHistoryForDate ($date, $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$date = strip_tags($date);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			$sql = "SELECT
						pl.id AS 'PlayedId',
						pl.timestamp AS 'Timestamp',
						pl.actid AS 'ActivityId',
						pl.devid AS 'DeviceId',
						so.id AS 'SongId',
						so.name AS 'SongName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						re.id AS 'RecordId',
						re.name AS 'RecordName'
					FROM
						played pl 
						INNER JOIN songs so ON so.id = pl.sid
						INNER JOIN artists ar ON ar.id = so.aid
						INNER JOIN records re ON re.id = so.rid
					WHERE
						DATE(pl.timestamp) = :date
					ORDER BY
						pl.timestamp";

			$sql .= $this->getQueryLimit($limit_low, $limit_high);
			$query = $this->db->prepare($sql);
			$query->execute( array(':date' => $date) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched play history for date " . $date);
				}
				
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return null;
			}
		}
		
		/**
			Wrapper method for getting the releases of one artist
		*/
		private function getArtistReleases ($aid) {
			// get artist releases by date in descending chronological order
			$releases = $this->getArtistReleasesByDate($aid, 'DESC');
			
			return $releases;
		}
		
		/**
			Returns all record releases by the specified artist, ordered by release date.
			Record type importance level is not considered.
		*/
		private function getArtistReleasesByDate ($aid, $order = 'ASC', $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$aid = strip_tags($aid);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			$sql = "SELECT
						re.id AS 'RecordId',
						re.name AS 'RecordTitle',
						re.publish AS 'RecordPublishDate',
						rt.name AS 'RecordType'
					FROM
						records re INNER JOIN
						record_type rt ON rt.id = re.typeid
					WHERE
						re.aid = :aid
					ORDER BY
						re.publish " . $order;

			$sql .= $this->getQueryLimit($limit_low, $limit_high);
			$query = $this->db->prepare($sql);
			$query->execute( array(':aid' => $aid) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched artist releases for artist id " . $aid);
				}
				
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return null;
			}
		}
		
		/**
			Returns all record releases by the specified artist, ordered by importance level of release and release date.
			A level threshold can be set, if ommitted or set to zero, no filtering for importance level will be performed.
		*/
		private function getArtistReleasesByImportanceLevel ($aid, $level_from = 0, $level_to = 0, $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$aid = strip_tags($aid);
			$level_from = strip_tags($level_from);
			$level_to = strip_tags($level_to);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			if ($level_from > 0) {
				$additional_where = ", rt.level >= " . $level_from . " ";
				
				if ($level_to > 0) {
					$additional_where .= "AND rt.level <= " . $level_to . " ";
				}
			} else {
				$additional_where = "";
			}
			
			$sql = "SELECT
						re.id,
						re.name,
						re.release,
						rt.name AS 'Record Type'
					FROM
						records re
						INNER JOIN record_type rt ON rt.id = re.typeid
					WHERE
						re.aid = :aid";
			
			// Add additional where if filtering for importance level
			$sql .= $additional_where;
			
			$sql .= "ORDER BY
						rt.level ASC,
						re.release ASC";

			$sql .= $this->getQueryLimit($limit_low, $limit_high);
			$query = $this->db->prepare($sql);
			$query->execute( array(':aid' => $aid) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched artist releases for artist id " . $aid);
				}
				
				return $query->fetchAll();
			} else {
				return null;
			}
		}
		
		/**
			Method to correct a song added date.
			All songs in the array are corrected.
			In the array there has to be one key "mmid" (integer) and one key "added" (MySql date).
		*/
		public function correctSongAddedDate($songs) {
			if (count($songs) > 0) {
				foreach ($songs as $song) {
					// convert MM id to sid
					$sid = $this->getSidFromMMId($song["mmid"]);
					
					$sql = "UPDATE mmlink SET added = :added WHERE sid = :sid";
					$query = $this->db->prepare($sql);
					$query->execute( array(':added' => $song["added"], ':sid' => $sid) );
				}
			}
		}
		
		/**
			Returns an array containing the song statistics for the specified date range.
		*/
		public function getPlayedSongStatistics ($date_from, $date_to, $limit_low, $limit_high) {
			$sql = "SELECT
						so.id AS 'SongId',
						so.name AS 'SongName',
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						re.id AS 'RecordId',
						re.name AS 'RecordName',
						COUNT(pl.sid) AS 'PlayedCount'
					FROM
						played pl
						INNER JOIN songs so ON so.id = pl.sid
						INNER JOIN artists ar ON ar.id = so.aid
						INNER JOIN records re ON re.id = so.rid
					WHERE
						DATE(pl.timestamp) >= :date_from AND DATE(pl.timestamp) <= :date_to
					GROUP BY
						pl.sid
					ORDER BY
						PlayedCount DESC,
						ArtistName,
						SongName
					LIMIT
						:limit_low, :limit_high";

			$query = $this->db->prepare($sql);
			$query->execute( array(':date_from' => $date_from, ':date_to' => $date_to, ':limit_low' => $limit_low, ':limit_high' => $limit_high) );
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return array();
			}
		}
		
		/**
			Returns an array containing the song statistics for the specified date range.
		*/
		public function getPlayedArtistStatistics ($date_from, $date_to, $limit_low, $limit_high) {
			$sql = "SELECT
						ar.id AS 'ArtistId',
						ar.name AS 'ArtistName',
						COUNT(pl.sid) AS 'PlayedCount'
					FROM
						played pl
						INNER JOIN songs so ON so.id = pl.sid
						INNER JOIN artists ar ON ar.id = so.aid
					WHERE
						DATE(pl.timestamp) >= :date_from AND DATE(pl.timestamp) <= :date_to
					GROUP BY
						ar.id
					ORDER BY
						PlayedCount DESC,
						ArtistName
					LIMIT
						:limit_low, :limit_high";

			$query = $this->db->prepare($sql);
			$query->execute( array(':date_from' => $date_from, ':date_to' => $date_to, ':limit_low' => $limit_low, ':limit_high' => $limit_high) );
			
			if ($query->rowCount() > 0) {
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} else {
				return array();
			}
		}
		
		/**
			Truncates all tables that contain non-config data (i.e. data that comes from the MM.DB file)
		*/
		public function truncateTables () {
			$to_truncate = array('artists', 'logs', 'mmlink', 'mmlink_candidates', 'played', 'records', 'songs');
			
			foreach ($to_truncate as $table) {
				$sql = "TRUNCATE TABLE " . $table;
				
				$query = $this->db->prepare($sql);
				$query->execute();
			}
		}
		
		/**
			Basic setup for mobile database
		*/
		public function createMobileDatabase () {
			// Drop tables if they exist
			$this->mobile_db->exec("DROP TABLE IF EXISTS songs");
			$this->mobile_db->exec("DROP TABLE IF EXISTS artists");
			$this->mobile_db->exec("DROP TABLE IF EXISTS records");
			$this->mobile_db->exec("DROP TABLE IF EXISTS devices");
			$this->mobile_db->exec("DROP TABLE IF EXISTS played");
			$this->mobile_db->exec("DROP VIEW IF EXISTS SongsView");
			
			// Create tables
			$this->mobile_db->exec("CREATE TABLE songs (
										_id INTEGER PRIMARY KEY,
										name TEXT,
										aid INTEGER,
										rid INTEGER,
										rating INTEGER,
										length INTEGER,
										discno INTEGER,
										trackno INTEGER)");
										
			$this->mobile_db->exec("CREATE TABLE artists (
										_id INTEGER PRIMARY KEY,
										name TEXT)");
			
			$this->mobile_db->exec("CREATE TABLE records (
										_id INTEGER PRIMARY KEY,
										name TEXT)");
										
			$this->mobile_db->exec("CREATE TABLE devices (
										_id INTEGER PRIMARY KEY,
										name TEXT,
										active INTEGER)");
										
			$this->mobile_db->exec("CREATE TABLE played (
										_id INTEGER PRIMARY KEY AUTOINCREMENT,
										sid INTEGER,
										devid INTEGER,
										timestamp INTEGER)");
										
			$this->mobile_db->exec("CREATE VIEW
										SongsView
									AS
										SELECT
											so._id AS 'SongId',
											so.name AS 'SongName',
											ar.name AS 'ArtistName',
											re.name AS 'RecordName',
											so.rating AS 'SongRating',
											so.length AS 'SongLength',
											so.discno AS 'SongDiscNo',
											so.trackno AS 'SongTrackNo'
										FROM
											songs so
											INNER JOIN artists ar ON ar._id = so.aid
											INNER JOIN records re ON re._id = so.rid
										ORDER BY
											so.rating DESC,
											ar.name ASC,
											re.name ASC,
											so.discno ASC,
											so.trackno ASC");
										
			// Add existing devices
			$sql = "SELECT id, name FROM devices";
			$query = $this->db->prepare($sql);
			$query->execute();
			
			$devices = $query->fetchAll();
			
			foreach ($devices as $device) {
				$dev_sql = "INSERT INTO devices (_id, name, active) VALUES (:id, :name, :active)";
				$dev_query = $this->mobile_db->prepare($dev_sql);
				$dev_query->execute( array(':id' => $device['id'], ':name' => $device['name'], ':active' => $device['active']) );
			}
		}
		
		public function setLogging ($logging) {
			$this->logging = $logging;
		}
		
		public function getLogging () {
			return $this->logging;
		}
		
		public function setVerbose ($verbose) {
			$this->verbose = $verbose;
		}
		
		public function getVerbose () {
			return $this->verbose();
		}
		
		private function getQueryLimit ($limit_low, $limit_high) {
			if ($limit_low != "") {
				$limit = " LIMIT " . $limit_low;
				
				if ($limit_high != "") {
					$limit .= ", " . $limit_high;
				}
			} else {
				$limit = "";
			}
			
			return $limit;
		}
		
		private function getMobileDatabaseFilename () {
			return MusicDB::$mobile_db_file;
		}
	}