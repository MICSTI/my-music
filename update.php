<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Update</h3>";
	
	$html .= "<div id='update-container'>";
		$html .= $mc->getFrontend()->getUpdateContent($mc);
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "update");