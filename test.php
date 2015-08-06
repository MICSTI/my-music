<?php
	include('resources.php');
	
	$html = "";
	
	$html .= "Test page";
	
	echo $mc->getIndexHTML($html);