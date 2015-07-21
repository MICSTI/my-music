<?php
	class MusicDB {
		// Database connection
		private $db;
		
		// Mobile database connection
		private $mobile_db;
	
		// Should vital database transactions (insert, update, delete) be logged?
		private $logging = false;
		
		// Should every database transaction (even select) be logged?
		private $verbose = false;
		
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
			
				return $query->fetch();
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
					$mobile_query->execute( array(':id' => $inserted, ':name' => $name) );
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
			return $this->getSingleGeneric('artists', $id);
		}
		
		/**
			Returns a song with all meta data from the database
		*/
		public function getSong ($id) {
			return $this->getSingleGeneric('songs', $id);
		}
		
		/**
			Adds a new artist to the database.
			If insert was successful, the newly assigned id is returned, otherwise false.
		*/
		public function addArtist ($name) {
			return $this->addGenericByName('artists', $name);
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
			Adds a new song to the database.
			If insert was successful, the newly assigned id is returned, otherwise false.
		*/
		public function addSong ($name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating) {
			// strip input from code tags
			$name = strip_tags($name);
			$aid = strip_tags($aid);
			$rid = strip_tags($rid);
			$length = strip_tags($length);
			$bitrate = strip_tags($bitrate);
			$discno = strip_tags($discno);
			$trackno = strip_tags($trackno);
			$rating = strip_tags($rating);
			
			// Put song into database
			$sql = "INSERT INTO songs (name, aid, rid, length, bitrate, discno, trackno, rating) VALUES (:name, :aid, :rid, :length, :bitrate, :discno, :trackno, :rating)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':name' => $name, ':aid' => $aid, ':rid' => $rid, ':length' => $length, ':bitrate' => $bitrate, ':discno' => $discno, ':trackno' => $trackno, ':rating' => $rating) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
				
				// Mobile database entry
				$mobile_sql = "INSERT INTO songs (_id, name, aid, rid, rating, length, discno, trackno) VALUES (:id, :name, :aid, :rid, :rating, :length, :discno, :trackno)";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				$mobile_query->execute( array(':id' => $inserted, ':name' => $name, ':aid' => $aid, ':rid' => $rid, ':rating' => $rating, ':length' => $length, ':discno' => $discno, ':trackno' => $trackno) );
			
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
			If update was successful, true is returned, false otherwise
		*/
		public function updateSong ($id, $name, $aid, $rid, $length, $bitrate, $discno, $trackno, $rating) {
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
			
			$sql = "UPDATE songs SET name=:name, aid=:aid, rid=:rid, length=:length, bitrate=:bitrate, discno=:discno, trackno=:trackno, rating=:rating WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':name' => $name, ':aid' => $aid, ':rid' => $rid, ':length' => $length, ':bitrate' => $bitrate, ':discno' => $discno, ':trackno' => $trackno, ':rating' => $rating) );
			
			if ($query->rowCount() > 0) {
				// Mobile database entry
				$mobile_sql = "UPDATE songs SET name = :name, aid = :aid, rid = :rid, length = :length, discno = :discno, trackno = :trackno, rating = :rating WHERE _id = :id";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				$mobile_query->execute( array(':id' => $id, ':name' => $name, ':aid' => $aid, ':rid' => $rid, ':length' => $length, ':discno' => $discno, ':trackno' => $trackno, ':rating' => $rating) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in songs with id " . $id . " [" . implode(", ", func_get_args()) . "]");
				}
			
				return true;
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
				$mobile_query->execute( array(':id' => $inserted, ':name' => $name) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new tupel in records [ name: '" . $name . "', aid: '" . $aid . "', typeid: " . $typeid . ", release: '" . $release . "' ] with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new tupel in records [ name: '" . $name . "', aid: '" . $aid . "', typeid: " . $typeid . ", release: '" . $release . "' ] with \n" . implode(" / ", $query->errorInfo()));
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
		public function pushRecord ($name, $aid, $typeid = 0, $release = '0000-00-00') {
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
					$mobile_query->execute( array(':id' => $id, ':name' => $name) );
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
			Updates name of specified artist id.
			Returns true if update was successful, false otherwise.
		*/
		public function updateArtist ($id, $name) {
			return $this->updateGeneric('artists', $id, $name);
		}
		
		/**
			Updates name of specified record type id.
			Returns true if update was successful, false otherwise.
		*/
		public function updateRecordType ($id, $name, $level) {
			// strip input from code tags
			$id = strip_tags($id);
			$name = strip_tags($name);
			$level = strip_tags($level);
			
			$sql = "UPDATE record_type SET name = :name, level = :level WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':name' => $name, ':level' => $level) );
			
			if ($query->rowCount() > 0) {
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
			Updates name of specified record id.
			Returns true if update was successful, false otherwise.
		*/
		public function updateRecord ($id, $name, $aid, $typeid = 0, $release = '0000-00-00') {
			// strip input from code tags
			$name = strip_tags($name);
			$aid = strip_tags($aid);
			$typeid = strip_tags($typeid);
			$release = strip_tags($release);
			
			$sql = "UPDATE records SET name = :name, aid = :aid, typeid = :typeid, release = :release WHERE id = :id";
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
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in records [ name: '" . $name . "', aid: '" . $aid . "', typeid: " . $typeid . ", release: '" . $release . "' ] with id " . $id . "\n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Adds a new entry to the played table.
			Inserted id is returned if insert was successful, false if otherwise.
		*/
		public function addPlayed ($sid, $devid, $timestamp) {
			// strip input from code tags
			$sid = strip_tags($sid);
			$devid = strip_tags($devid);
			$timestamp = strip_tags($timestamp);
			
			$sql = "INSERT INTO played (sid, devid, timestamp) VALUES (:sid, :devid, :timestamp)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid, ':devid' => $devid, ':timestamp' => $timestamp) );
			
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
			Checks if a song with the specified mmid already exists in the database
			Returns true if the song exists, false if otherwise
		*/
		public function songExists ($mmid) {
			if ($this->getSongIdByValue('mmid', $mmid) !== false) {
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
		public function shortSingleSearch ($search, $limit = 10) {
			// escape string in case there's any apostrophes in it
			$search = mysql_real_escape_string($search);
			
			$sql = "SELECT SongId, SongName, ArtistName, RecordName FROM SongsView WHERE SongName LIKE '" . $search . "%' OR ArtistName LIKE '" . $search . "%' LIMIT :limit";
			
			try {
				$query = $this->db->prepare($sql);
				$query->execute( array(':limit' => $limit) );
				
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} catch (Exception $e) {
				return false;
			}
		}
		
		/**
			Performs a long single search on the database for a song, artist or record.
			Long single search means that there is only one word in the search query that consists of more than three characters.
			In this mode, entries that contain the term within a word are matched, too (i.e. LIKE '%xxxx%')
			Input sanitation is done before this step, so the input is guaranteed to be stripped off of any scripts tags.
			
			Returned is an array containing the results, or false if an error occurred during the SQL query.
		*/
		public function longSingleSearch ($search, $limit = 10) {
			// escape string in case there's any apostrophes in it
			$search = mysql_real_escape_string($search);
			
			$sql = "SELECT SongId, SongName, ArtistName, RecordName FROM SongsView WHERE SongName LIKE '%" . $search . "%' OR ArtistName LIKE '%" . $search . "%' LIMIT :limit";
			
			try {
				$query = $this->db->prepare($sql);
				$query->execute( array(':limit' => $limit) );
				
				return $query->fetchAll(PDO::FETCH_ASSOC);
			} catch (Exception $e) {
				return false;
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
		public function multiSearch ($search_array, $limit = 10) {
			$term_query = "";
			
			foreach ($search_array as $term) {
				$term = mysql_real_escape_string($term);
				
				if (strlen($term) <= 3) {
					// short word
					$tq = "( SELECT SongId FROM SongsView WHERE SongName LIKE '" . $term . "%' OR ArtistName LIKE '" . $term . "%' )";
				} else {
					// long word
					$tq = "( SELECT SongId FROM SongsView WHERE SongName LIKE '%" . $term . "%' OR ArtistName LIKE '%" . $term . "%' )";
				}
				
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
			Adds a new device to the database.
			A device is specified by name and device type id
			If adding was successful, the newly assigned device id is returned, false otherwise.
		*/
		public function addDevice ($name, $typeid) {
			// strip input from code tags
			$name = strip_tags($name);
			$typeid = strip_tags($typeid);
			
			$sql = "INSERT INTO devices (name, typeid) VALUES (:name, :typeid)";
			$query = $this->db->prepare($sql);
			$query->execute( array(':name' => $name, ':typeid' => $typeid) );
			
			if ($query->rowCount() > 0) {
				$inserted = $this->db->lastInsertId();
				
				// Mobile database entry
				$mobile_sql = "INSERT INTO devices (_id, name) VALUES (:id, :name)";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				$mobile_query->execute( array(':id' => $inserted, ':name' => $name) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "added new device '" . $name . "', type id " . $typeid . " with id " . $inserted);
				}
			
				return $inserted;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to add new device '" . $name . "', type id " . $typeid . " \n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
		}
		
		/**
			Updates an existing device.
			Returns true if update was successful, false if otherwise.
		*/
		public function updateDevice ($id, $name, $typeid) {
			// strip input from code tags
			$id = strip_tags($id);
			$name = strip_tags($name);
			$typeid = strip_tags($typeid);
			
			$sql = "UPDATE devices SET name = :name, typeid = :typeid WHERE id = :id";
			$query = $this->db->prepare($sql);
			$query->execute( array(':id' => $id, ':name' => $name, ':typeid' => $typeid) );
			
			if ($query->rowCount() > 0) {
				// Mobile database entry
				$mobile_sql = "UPDATE devices SET name = :name WHERE _id = :id";
				$mobile_query = $this->mobile_db->prepare($mobile_sql);
				$mobile_query->execute( array(':id' => $id, ':name' => $name) );
			
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "updated tupel in devices with id " . $id . " [ name: " . $name . ", typeid: " . $typeid . " ]");
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to update tupel in devices with id " . $id . " [ name: " . $name . ", typeid: " . $typeid . " ] \n" . implode(" / ", $query->errorInfo()));
				}
			
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
			
			$candidates = array();
			
			$sql = "SELECT id FROM songs WHERE (name, aid) = ( SELECT name, aid FROM songs WHERE id = :sid LIMIT 1 )";
			$query = $this->db->prepare($sql);
			$query->execute( array(':sid' => $sid) );
			
			if ($query->rowCount() > 0) {
				$fetched = $query->fetchAll();
				
				foreach ($fetched as $candidate) {
					$candidate_id = $candidate['id'];
					
					if ($candidate_id != $sid) {
						array_push($candidates, $candidate['id']);
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
			Confirms a MediaMonkey link candidate.
			As a result, the child song is removed from the database and the MM link is added to the database.
			With the second (boolean) parameter you can choose to keep the child song meta info and delete the parent song's info.
		*/
		public function confirmMMLinkCandidate ($mcid, $push_child_meta_info = false) {
			// strip input from code tags
			$mcid = strip_tags($mcid);
			
			// Determine which song's info should be stored
			$candidate = $this->getMMLinkCandidate($mcid);
			
			$sid = $candidate['sid'];
			$sid_candidate = $candidate['sid_candidate'];
			
			if ($push_child_meta_info) {
				$parent = $sid_candidate;
				$child = $sid;
			} else {
				$parent = $sid;
				$child = $sid_candidate;
			}
			
			// Correct MM link entry
			$sql = "UPDATE mmlink SET sid = :mlc_parent WHERE sid = :mlc_child";
			$query = $this->db->prepare($sql);
			$success_1 = $query->execute( array(':mlc_parent' => $parent, ':mlc_child' => $child ) );
			
			// Delete redundant song entry
			$sql_del = "DELETE FROM songs WHERE id = :id";
			$query_del = $this->db->prepare($sql_del);
			$success_2 = $query_del->execute( array(':id' => $child) );
			
			// Delete all MM link candidate entries for this song id
			$sql_del2 = "DELETE FROM mmlink_candidates WHERE sid_candidate = :sid_candidate";
			$query_del2 = $this->db->prepare($sql_del2);
			$success_3 = $query_del2->execute( array(':sid_candidate' => $sid_candidate) );
			
			if ($success_1 AND $success_2 AND $success_3) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "confirmed MM link candidate with id " . $mcid . ", new MM link between songs with id " . $parent . " (parent) and " . $child . " (child) established");
				}
			
				return true;
			} else {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "error", "tried to establish new MM link for candidate with id " . $mcid . " for songs with id " . $parent . " (parent) and " . $child . " (child).\n" . implode(" / ", $query->errorInfo()));
				}
			
				return false;
			}
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
			Adds a new device type to the database.
			A device type is specified by name only (laptop, mp3-device, etc.)
			Returns newly inserted id if successful, false if otherwise.
		*/
		public function addDeviceType ($name) {
			return $this->addGenericByName('device_type', $name);
		}
		
		/**
			Updates the device type with the specified id
			Returns true if update was successful, false if otherwise
		*/
		public function updateDeviceType ($id, $name) {
			return $this->updateGeneric('device_type', $id, $name);
		}
		
		/**
			Returns the play history for the specified song
			If no result is found, return will be null
		*/
		private function getPlayedSongHistory ($sid, $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$sid = strip_tags($sid);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			$sql = "SELECT
						devid,
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
			Returns the play history for the specfied date
			If no result is found, null is returned
		*/
		public function getPlayedHistoryForDate ($date, $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$date = strip_tags($date);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			$sql = "SELECT
						pl.timestamp AS 'Timestamp',
						so.name AS 'SongName',
						ar.name AS 'ArtistName',
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
				
				return $query->fetchAll();
			} else {
				return null;
			}
		}
		
		/**
			Returns all record releases by the specified artist, ordered by release date.
			Record type importance level is not considered.
		*/
		private function getArtistReleasesByDate ($aid, $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$aid = strip_tags($aid);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			$sql = "SELECT
						re.id,
						re.name,
						re.release,
						rt.name AS 'Record Type'
					FROM
						records re
						INNER JOIN record_type rt ON rt.id = re.typeid
					WHERE
						re.aid = :aid
					ORDER BY
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
		
		public function getPlayedStatistics ($date_from, $date_to, $limit_low = "", $limit_high = "") {
			// strip input from code tags
			$date_from = strip_tags($date_from);
			$date_to = strip_tags($date_to);
			$limit_low = strip_tags($limit_low);
			$limit_high = strip_tags($limit_high);
			
			$sql = "SELECT
						so.name AS 'SongName',
						ar.name AS 'ArtistName',
						re.name AS 'RecordName',
						COUNT(pl.sid) AS 'PlayCount'
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
						PlayCount DESC,
						ArtistName,
						SongName";

			$sql .= $this->getQueryLimit($limit_low, $limit_high);
			$query = $this->db->prepare($sql);
			$query->execute( array(':date_from' => $date_from, ':date_to' => $date_to) );
			
			if ($query->rowCount() > 0) {
				if ($this->logging) {
					$this->addLog(__FUNCTION__, "success", "fetched played statistics for range " . $date_from . " - " . $date_to);
				}
				
				return $query->fetchAll();
			} else {
				return null;
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
										name TEXT)");
										
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
				$dev_sql = "INSERT INTO devices (_id, name) VALUES (:id, :name)";
				$dev_query = $this->mobile_db->prepare($dev_sql);
				$dev_query->execute( array(':id' => $device['id'], ':name' => $device['name']) );
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
				$limit = "LIMIT " . $limit_low;
				
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