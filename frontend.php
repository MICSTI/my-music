<?php
	require_once('page.php');

	class frontend {
		private $page;
		
		private $PAGE_TITLE = "myMusic - Everything you want to know about your music library";
		
		private $STYLESHEETS = array(
								"http://fonts.googleapis.com/css?family=Oxygen",
								"external/bootstrap/css/bootstrap.min.css",
								"mymusic.css"
							);
		
		private $SCRIPTS = array(
								"external/jquery/jquery-2.1.4.min.js",
								"external/bootstrap/js/bootstrap.min.js",
								"mymusic.js"
							);
		
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
			
			// add main div
			$this->page->addPart("main", $main);
			
			// add footer div
			$this->page->addPart("footer", $this->getFooter());
			
			return $this->getHTML();
		}
		
		// Content of title div
		private function getTitle () {
			$title = "";
			
			$title .= "<div class='page-header'>";
				$title .= "<h1>myMusic <small>Everything you want to know about your music library</small></h1>";
			$title .= "</div>";
			
			return $title;
		}
		
		// Content of menu div
		private function getMenu () {
			$menu = "";
			
			$menu .= "<nav role='navigation' class='navbar navbar-default'>";
				$menu .= "<div class='navbar-header'>";
					$menu .= "<button type='button' data-target='#navbarCollapse' data-toggle='collapse' class='navbar-toggle'>";
						$menu .= "<span class='sr-only'>Toggle navigation</span>";
						$menu .= "<span class='icon-bar'></span>";
						$menu .= "<span class='icon-bar'></span>";
						$menu .= "<span class='icon-bar'></span>";
					$menu .= "</button>";
					
					$menu .= "<a href='#' class='navbar-brand'>myMusic</a>";
				$menu .= "</div>";
				
				$menu .= "<div id='navbarCollapse' class='collapse navbar-collapse'>";
					$menu .= "<ul class='nav navbar-nav'>";
						$menu .= "<li class='active'><a href='#'>Home</a></li>";
						$menu .= "<li class='dropdown'>";
							$menu .= "<a href='#' data-toggle='dropdown' class='dropdown-toggle'>Charts <b class='caret'></b></a>";
							
							$menu .= "<ul class='dropdown-menu'>";
								$menu .= "<li><a href='#'>Top 20/20</a></li>";
								$menu .= "<li><a href='#'>Years</a></li>";
							$menu .= "</ul>";
						$menu .= "<li><a href='history.php?date=2012-01-30'>History</a></li>";
						$menu .= "<li><a href='#'>Input</a></li>";
						$menu .= "<li><a href='#'>Concerts</a></li>";
						$menu .= "<li><a href='#'>Settings</a></li>";
					$menu .= "</ul>";
					
					// Search field
					$menu .= "<form role='search' class='navbar-form navbar-left'>";
						$menu .= "<div class='form-group'>";
							$menu .= "<input type='text' id='searchfield' placeholder='Search for songs, artists or records' class='form-control' size='33' />";
						$menu .= "</div>";
					$menu .= "</form>";
				$menu .= "</div>";
			$menu .= "</nav>";
			
			return $menu;
		}
		
		// Content of footer div
		private function getFooter () {
			$footer = "";
			
			/*$footer .= "<div id='footer'>";
				$footer .= "&copy; Michael Stifter 2014";
			$footer .= "</div>";*/
			
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
					$master .= "<meta name='viewport' content='width=device-width, initial-scale=1' />";
				
					// Page title
					$master .= "<title>" . $this->PAGE_TITLE . "</title>";
					
					// CSS
					foreach ($this->STYLESHEETS as $css) {
						$master .= "<link rel='stylesheet' href='" . $css . "' type='text/css'>";
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
					
					// Javascript source files are put to bottom of body for improved page load time
					foreach ($this->SCRIPTS as $js) {
						$close .= "<script type='text/javascript' src='" . $js . "'></script>";
					}
				
				// close body tag
				$close .= "</body>";
			
			// close html tag
			$close .= "</html>";
			
			return $close;
		}
	}