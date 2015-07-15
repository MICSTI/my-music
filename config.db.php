<?php
	class ConfigDB {
		public $db;
	
		/**
			A database connection must be passed to the model
			
			@param object	$db		A PDO database connection
		*/
		function __construct ($db) {
			try {
				$this->db = $db;
			} catch (PDOException $e) {
				exit("Database connection could not be established.");
			}
		}
		
		
	}