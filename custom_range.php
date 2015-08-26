<?php
	include('resources.php');
	
	$html = "";
	
	// headline
	$html .= "<h3>Custom range statistics</h3>";
	
	echo $mc->getIndexHTML($html, "statistics");