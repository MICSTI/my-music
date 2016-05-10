<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Upcoming concerts</h3>";
	
	$html .= $mc->getFrontend()->notImplementedYet();
	
	echo $mc->getIndexHTML($html, "concerts");