<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Calendarial</h3>";
	
	$html .= $mc->getFrontend()->notImplementedYet();
	
	echo $mc->getIndexHTML($html, "charts");