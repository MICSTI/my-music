<?php
	class MusicXML {
		// PDO database connection
		private $db;
		
		// Music DB connection
		private $dbc;
	
		/**
			A database connection must be passed to the model
			
			@param object	$db		A PDO database connection
		*/
		function __construct ($db) {
			try {
				$this->db = $db;
				
				// Database connection
				$this->dbc = new MusicDB($this->db);
			} catch (PDOException $e) {
				exit("Database connection could not be established.");
			}
		}
		
		
	}