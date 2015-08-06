<?php
	include('resources.php');
	
	$html = "";
	
	$html .= "<div>";
		$html .= "<input type='text' id='searching' />";
	$html .= "</div>";
	
	$html .= "<div>";
		$html .= "<div><strong>Search reponse:</strong></div>";
		$html .= "<div id='search-response'></div>";
	$html .= "</div>";
	
	$html .= "<div>";
		$html .= "<div><strong>Search result:</strong></div>";
		$html .= "<div id='search-result'></div>";
	$html .= "</div>";
	
	
	echo $mc->getIndexHTML($html);