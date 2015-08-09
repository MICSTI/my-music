<?php
	include('resources.php');
	
	$html = "";
	
	// Headline
	$html .= "<h3>Settings</h3>";
	
	// List selection on the left
	$html .= "<div class='col-sm-3'>";
		$html .= "<div id='settings' class='list-group' data-spy='affix' data-offset-top='230'>";
			// General
			$html .= "<a href='#' id='settings-general' class='list-group-item active'>";
				$html .= "<span>General</span>";
			$html .= "</a>";
			
			// Update
			$html .= "<a href='#' id='settings-update' class='list-group-item'>";
				$html .= "<span>Update</span>";
			$html .= "</a>";
			
			// Icons
			$html .= "<a href='#' id='settings-icons' class='list-group-item'>";
				$html .= "<span>Icons</span>";
			$html .= "</a>";
			
			// Device types
			$html .= "<a href='#' id='settings-device-types' class='list-group-item'>";
				$html .= "<span>Device types</span>";
			$html .= "</a>";
			
			// Record types
			$html .= "<a href='#' id='settings-record-types' class='list-group-item'>";
				$html .= "<span>Record types</span>";
			$html .= "</a>";
			
			// Countries
			$html .= "<a href='#' id='settings-countries' class='list-group-item'>";
				$html .= "<span>Countries</span>";
			$html .= "</a>";
		$html .= "</div>";
	$html .= "</div>";
	
	// Content on the right
	$html .= "<div id='settings-content' class='col-sm-9'>";
		$html .= $mc->getFrontend()->getSettingsContent($mc->getMDB());
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "settings");