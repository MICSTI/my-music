<?php
	class page {
		private $parts = array();
		
		// Master tag (contains html structure incl. header scripts and css)
		private $master;
		
		// Closing master tags
		private $close;
		
		public function getHTML () {
			$html = "";
			
			// Add master part
			$html .= $this->getMaster();
			
			// Add content parts
			foreach ($this->parts as $part) {
				$html .= $part["content"];
			}
			
			// Close master part
			$html .= $this->closeMaster();
			
			return $html;
		}
		
		// GETTER --- SETTER
		public function addPart ($id, $part) {
			array_push($this->parts, array("id" => $id, "content" => $part));
		}
		
		public function getParts () {
			return $this->parts;
		}
		
		public function setMaster ($master, $close) {
			$this->master = $master;
			$this->close = $close;
		}
		
		private function getMaster () {
			return $this->master;
		}
		
		private function closeMaster () {
			return $this->close;
		}
	}