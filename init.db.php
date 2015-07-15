<?php
	class DatabaseConnection {
		private $dbc = null;
		
		public function __construct () {
			$this->dbc = new PDO('mysql:host=localhost;dbname=d01b0305;charset=utf8', 'root', '');
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