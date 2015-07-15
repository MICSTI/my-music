<?php
	require_once('page.php');

	class frontend {
		private $page;
		
		private $PAGE_TITLE = "myMusic - Everything you want to know about your music library";
		
		private $STYLESHEETS = array("http://fonts.googleapis.com/css?family=Oxygen", "mymusic.css");
		
		private $SCRIPTS = array("mymusic.js");
		
		public function __construct() {
			$this->page = new page();
		
			// Set master container and its closing tags
			$this->page->setMaster($this->getMasterContainer(), $this->getMasterClose());
		}
		
		private function getHTML () {
			return $this->page->getHTML();
		}
		
		public function getIndex ($main = "") {
			// add title div
			$this->page->addPart("title", $this->getTitle());
			
			// add menu div
			$this->page->addPart("menu", $this->getMenu());
			
			// add search div
			$this->page->addPart("search", $this->getSearch());
			
			// add main div
			$this->page->addPart("main", $main);
			
			// add footer div
			$this->page->addPart("footer", $this->getFooter());
			
			return $this->getHTML();
		}
		
		// Content of title div
		private function getTitle () {
			$title = "";
			
			$title .= "<div id='bar_title'>";
				$title .= "<div>myMusic</div>";
				$title .= "<div>Everything you want to know about your music library.</div>";
			$title .= "</div>";
			
			return $title;
		}
		
		// Content of menu div
		private function getMenu () {
			$menu = "";
			
			$menu .= "<div id='bar_menu'>";
				$menu .= "MENU";
			$menu .= "</div>";
			
			return $menu;
		}
		
		// Content of search div
		private function getSearch () {
			$search = "";
			
			$search .= "<div id='bar_search'>";
				$search .= "<div><input type='text' id='search' size='40' value='Search for songs, artists, records - whatever you want to know' /></div>";
			$search .= "</div>";
			
			return $search;
		}
		
		// Content of footer div
		private function getFooter () {
			$footer = "";
			
			$footer .= "<div id='footer'>";
				$footer .= "&copy; Michael Stifter 2014";
			$footer .= "</div>";
			
			return $footer;
		}
		
		private function getMasterContainer () {
			$master = "";
			
			// HTML structure
			$master .= "<!DOCTYPE HTML>";
			
			$master .= "<html>";
				
				// Head
				$master .= "<head>";
					// Meta information
					$master .= "<meta charset='utf-8' />";
				
					// Page title
					$master .= "<title>" . $this->PAGE_TITLE . "</title>";
					
					// CSS
					foreach ($this->STYLESHEETS as $css) {
						$master .= "<link rel='stylesheet' href='" . $css . "' type='text/css'>";
					}
					
					// JS
					foreach ($this->SCRIPTS as $js) {
						$master .= "<script type='text/javascript src='" . $js . "'></script>";
					}
				$master .= "</head>";
				
				// Body
				$master .= "<body>";
				
					// Master
					$master .= "<div id='master'>";
				
			return $master;
		}
		
		private function getMasterClose () {
			$close = "";
			
					// close master tag
					$close .= "</div>";
				
				// close body tag
				$close .= "</body>";
			
			// close html tag
			$close .= "</html>";
			
			return $close;
		}
	}