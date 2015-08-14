<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h1>Home</h1>";
	
	$html .= $mc->getFrontend()->notImplementedYet();
	
	echo $mc->getIndexHTML($html, "home");