<?php
	include('resources.php');
	
	$html = "";
	
	// Headline
	$html .= "<h3>Settings</h3>";
	
	// List selection on the left
	$html .= "<div class='col-sm-3'>";
		$html .= "<div id='settings' class='list-group'>";
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
			
			// Devices
			$html .= "<a href='#' id='settings-devices' class='list-group-item'>";
				$html .= "<span>Devices</span>";
			$html .= "</a>";
			
			// Device types
			$html .= "<a href='#' id='settings-device-types' class='list-group-item'>";
				$html .= "<span>Device types</span>";
			$html .= "</a>";
			
			// Record types
			$html .= "<a href='#' id='settings-record-types' class='list-group-item'>";
				$html .= "<span>Record types</span>";
			$html .= "</a>";
		$html .= "</div>";
	$html .= "</div>";
	
	// Content on the right
	$html .= "<div id='settings-content' class='col-sm-9'>";
		$html .= $mc->getFrontend()->getSettingsContent($mc->getMDB());
	$html .= "</div>";
	
	// Modal
	$html .= "<div id='settings-modal' class='modal fade'>";
		$html .= "<div class='modal-dialog'>";
			$html .= "<div class='modal-content'>";
				$html .= "<div class='modal-header'>";
					$html .= "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>";
					$html .= "<h4 class='modal-title'>Confirmation</h4>";
				$html .= "</div>";
				
				$html .= "<div class='modal-body'>";
					$html .= "<p>Hello, I'm a modal!</p>";
				$html .= "</div>";
				
				$html .= "<div class='modal-footer'>";
					$html .= "<button tyoe='button' class='btn btn-default' data-dismiss='modal'>Close</button>";
					$html .= "<button tyoe='button' class='btn btn-primary'>Save</button>";
				$html .= "</div>";
			$html .= "</div>";
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "settings");