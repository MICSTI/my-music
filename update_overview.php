<?php
	include('resources.php');
	
	if ($_GET) {
		$data = isset($_GET["data"]) ? $_GET["data"] : "";
		
		$json_data = json_decode($data, true);
		
		$suggestions = $json_data["suggestions"];
		$new = $json_data["new"];
		$updated = $json_data["updated"];
		
		
	}
	
	echo $mc->getIndexHTML($html);