<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Update</h3>";
	
	$html .= $mc->getFrontend()->notImplementedYet();
	
	echo $mc->getIndexHTML($html, "update");