<?php
	// Unix timestamp
	class UnixTimestamp {
		private $unix;
		
		private $unixAsDateTime;
		private $mm_compare;
		
		public function __construct ($unix = 0) {
			$this->setUnix($unix);
			
			$this->unixAsDateTime = new DateTime(date('Y-m-d', $this->unix));
			$this->mm_compare = new DateTime('1900-01-01');
		}
		
		public function convert2MysqlDateTime () {
			return date('Y-m-d H:i:s', $this->unix);
		}
		
		public function convert2MMDate () {
			$full = $this->mm_compare->diff($this->unixAsDateTime)->format('%a');
			
			$temp = new MMDate($full);
			
			$seconds = $this->unix - $temp->convert2UnixTimestamp();
			
			return ($full + $this->getFractureFromSeconds($seconds));
		}
		
		public function convert2AustrianDate () {
			return date('d.m.Y', $this->unix);
		}
		
		public function convert2AustrianDateTime () {
			return date('d.m.Y H:i', $this->unix);
		}
		
		public function now () {
			return mktime();
		}
		
		private function getFractureFromSeconds ($seconds) {
			return $seconds / 86400;
		}
		
		public function setUnix ($unix) {
			$this->unix = $unix;
		}
		
		public function getUnix () {
			return $this->unix;
		}
	}

	// MySQL date format
	class MysqlDate {
		private $mysql;
		
		public function __construct ($mysql = "") {
			$this->setMysql($mysql);
		}
		
		public function convert2UnixTimestamp () {
			$y = $this->mysql->format('Y');
			$m = $this->mysql->format('m');
			$d = $this->mysql->format('d');
			$h = $this->mysql->format('H');
			$i = $this->mysql->format('i');
			
			return mktime($h, $i, 0, $m, $d, $y);
		}
		
		public function convert2AustrianDate () {
			return $this->mysql->format('d.m.Y');
		}
		
		public function convert2AustrianDatetime () {
			return $this->mysql->format('d.m.Y H:i');
		}
		
		public function convert2MMDate () {
			$unix = new UnixTimestamp( $this->convert2UnixTimestamp() );
			
			return $unix->convert2MMDate();
		}
		
		public function now () {
			return date('Y-m-d');
		}
		
		public function setMysql ($mysql) {
			$this->mysql = new DateTime($mysql);
		}
		
		public function getMysql () {
			return $this->mysql;
		}
	}
	
	// MySQL datetime format
	class MysqlDateTime {
		private $mysql_datetime;
		
		private $h, $i, $s, $d, $m, $y;
		private $unix;
		
		public function __construct ($mysql_datetime = "") {
			$this->setMysqlDateTime($mysql_datetime);
			
			$this->y = substr($mysql_datetime, 0, 4);
			$this->m = substr($mysql_datetime, 5, 2);
			$this->d = substr($mysql_datetime, 8, 2);
			$this->h = substr($mysql_datetime, 11, 2);
			$this->i = substr($mysql_datetime, 14, 2);
			$this->s = substr($mysql_datetime, 17);
			
			if ($this->s == "") {
				$this->s = 0;
			}
			
			$this->unix = mktime( $this->h, $this->i, $this->s, $this->m, $this->d, $this->y );
		}
		
		public function convert2UnixTimestamp () {
			return $this->unix;
		}
		
		public function convert2AustrianDate () {
			return date('d.m.Y', $this->unix);
		}
		
		public function convert2AustrianDateTime () {
			return date('d.m.Y H:i', $this->unix);
		}
		
		public function convert2Time() {
			return date('H:i', $this->unix);
		}
		
		public function convert2MMDate () {
			$unix = new UnixTimestamp($this->unix);
			
			return $unix->convert2MMDate();
		}
		
		public function setMysqlDateTime ($mysql_datetime) {
			$this->mysql_datetime = $mysql_datetime;
		}
		
		public function getMysqlDateTime () {
			return $this->mysql_datetime;
		}
	}

	// MediaMonkey date format
	class MMDate {
		private $mmdate;
		
		private $diff_date;
		
		public function __construct ($mmdate = "") {
			$this->setMMDate ($mmdate);
			
			$this->diff_date = new DateTime('1900-01-01');
		}
		
		public function convert2UnixTimestamp () {
			$interval = $this->calcInterval();
			
			$y = $interval->format('Y');
			$m = $interval->format('m');
			$d = $interval->format('d');
			$h = $interval->format('H');
			$i = $interval->format('i');
			$s = $interval->format('s');
			
			return mktime($h, $i, $s, $m, $d, $y);
		}
		
		public function convert2MysqlDate () {
			return $this->calcDate()->format('Y-m-d');
		}
		
		public function convert2MysqlDateTime () {
			return $this->calcInterval()->format('Y-m-d H:i:s');
		}
		
		public function convert2AustrianDate () {
			return $this->calcDate()->format('d.m.Y');
		}
		
		public function convert2AustrianDateTime () {
			return $this->calcInterval()->format('d.m.Y H:i');
		}
		
		private function calcInterval () {
			$day_fracture = $this->mmdate - floor($this->mmdate);

			$d = floor($this->mmdate) - 2;
			
			$hours = $day_fracture * 24;
			$h = floor($hours);
			
			$minutes = ($hours - $h) * 60;
			$m = floor($minutes);
			
			$seconds = ($minutes - $m) * 60;
			$s = floor($seconds);
			
			$temp = $this->diff_date;
		
			return $temp->add(new DateInterval('P' . $d . 'DT' . $h . 'H' . $m . 'M' . $s . 'S'));
		}
		
		private function calcDate () {
			$d = floor($this->mmdate) - 2;
			
			$temp = $this->diff_date;
		
			return $temp->add(new DateInterval('P' . $d . 'D'));
		}
		
		public function setMMDate ($mmdate) {
			$this->mmdate = $mmdate;
		}
		
		public function getMMDate () {
			return $this->mmdate;
		}
	}