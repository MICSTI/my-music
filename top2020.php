<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Top 20/20</h3>";
	
	$html .= $mc->getFrontend()->notImplementedYet();
	
	echo $mc->getIndexHTML($html, "charts");