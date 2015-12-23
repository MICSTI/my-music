<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Top 20/20 Stats</h3>";
	
	$html .= $mc->getFrontend()->notImplementedYet();
	
	echo $mc->getIndexHTML($html, "statistics");