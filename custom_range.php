<?php
	include('resources.php');
	
	$html = "";
	
	// headline
	$html .= "<h3>Custom range statistics</h3>";
	
	// top bar with two datepickers and submit button
	$html .= "<div class='custom-statistics-control'>";
	$html .= "</div>";
	
	// result content div
	$html .= "<div id='custom-statistics-result'>";
		
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "statistics");