<?php
	class DatabaseConnection {
		private $dbc = null;
		
		public function __construct () {
			$host = getenv('DB_HOST') ?: 'localhost';
			$name = getenv('DB_NAME') ?: 'd01b0305';
			$user = getenv('DB_USER') ?: 'root';
			$pass = getenv('DB_PASSWORD') ?: '';

			$this->dbc = new PDO(
				"mysql:host=$host;dbname=$name;charset=utf8",
				$user,
				$pass
			);
			
			$this->dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbc->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}
		
		public function getDBC () {
			return $this->dbc;
		}
	}
	
	class MobileDatabaseConnection {
		private $mdbc = null;
		
		public function __construct () {
			$this->mdbc = new PDO('sqlite:files/myMobileMusic.DB');
			$this->mdbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->mdbc->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}
		
		public function getMDBC() {
			return $this->mdbc;
		}
	}