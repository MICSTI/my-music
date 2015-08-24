<?php
	include('resources.php');
	
	$html = "";
	
	// Headline
	$html .= "<h3>Administration</h3>";
	
	// List selection on the left
	$html .= "<div class='col-sm-3'>";
		$html .= "<div id='administration' class='list-group' data-spy='affix' data-offset-top='230'>";
			// Add played song
			$html .= "<a href='#' id='administration-add-played' class='list-group-item active'>";
				$html .= "<span>Add played songs</span>";
			$html .= "</a>";
			
			// Charts
			$html .= "<a href='#' id='administration-charts' class='list-group-item'>";
				$html .= "<span>Charts</span>";
			$html .= "</a>";
			
			// Songs
			$html .= "<a href='#' id='administration-songs' class='list-group-item'>";
				$html .= "<span>Songs</span>";
			$html .= "</a>";
			
			// Artists
			$html .= "<a href='#' id='administration-artists' class='list-group-item'>";
				$html .= "<span>Artists</span>";
			$html .= "</a>";
			
			// Records
			$html .= "<a href='#' id='administration-records' class='list-group-item'>";
				$html .= "<span>Records</span>";
			$html .= "</a>";
			
			// Played
			$html .= "<a href='#' id='administration-played' class='list-group-item'>";
				$html .= "<span>Played</span>";
			$html .= "</a>";
			
			// Devices
			$html .= "<a href='#' id='administration-devices' class='list-group-item'>";
				$html .= "<span>Devices</span>";
			$html .= "</a>";
			
			// Activities
			$html .= "<a href='#' id='administration-activities' class='list-group-item'>";
				$html .= "<span>Activities</span>";
			$html .= "</a>";
		$html .= "</div>";
	$html .= "</div>";
	
	// Content on the right
	$html .= "<div id='administration-content' class='col-sm-9'>";
		$html .= $mc->getFrontend()->getAdministrationContent($mc->getMDB());
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "administration");