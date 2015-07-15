<?php
	function getPageHTML ($content) {
		$html = "";
		
		// Set doctype
		$html .= "<!DOCTYPE html>";
		
		// Start with html tag
		$html .= "<html>";
			// Head
			$html .= "<head>";
				// Meta information
				$html .= "<meta charset='utf-8' />";
				
				// Page title
				$title = "myMusic - Everything you want to know about your music library";
				
				$html .= "<title>" . $title . "</title>";
				
				// CSS
				$stylesheets = array("http://fonts.googleapis.com/css?family=Oxygen", "mymusic.css");
				
				foreach ($stylesheets as $css) {
					$html .= "<link rel='stylesheet' href='" . $css . "' type='text/css'>";
				}
				
				// JS
				$scripts = array();
				
				foreach ($scripts as $js) {
					$html .= "<script type='text/javascript src='" . $js . "'></script>";
				}
			$html .= "</head>";
			
			// Body
			$html .= "<body>";
				$html .= $content;
			$html .= "</body>";
		
		$html .= "</html>";
		
		return $html;
	}

	/**
		Creates a html table in a string.
		Header information must be passed in an array (options: name [string], display [string], sortable [boolean], order [int], data-align [string], header-align [string] - defaults to center)
		Data information must be passed as a PDOStatement::fetchAll result
	*/
	function getTableFromArray ($id, $header, $data) {
		$html = "";
		
		// Array with column names
		$column_names = array();
		
		// parse header array
		foreach ($header as $head) {
			array_push($column_names, $head['display']);
		}
		
		$html .= "<table id='" . $id . "' class='display' cellspacing='0' width='100%'>";
			// Header
			$html .= "<thead>";
				$html .= "<tr><th>" . implode("</th><th>", $column_names) . "</th></tr>";
			$html .= "</thead>";
			
			// Body
			$html .= "<tbody>";
				foreach ($data as $element) {
					$html .= "<tr>";
						foreach ($column_names as $column) {
							$html .= "<td>" . $element[$column] . "</td>";
						}
					$html .= "</tr>";
				}
			$html .= "</tbody>";
		$html .= "</table>";
		
		return $html;
	}
	
	/**
		Returns all column names from an array that are not numeric.
		Useful for getting the column names from a PDO::fetchAll statement
	*/
	function getNonNumericColumnNames ($array) {
		$return = array();
	
		foreach ($array as $column) {
			if (!is_numeric($column)) {
				array_push($return, $column);
			}
		}
		
		return $return;
	}