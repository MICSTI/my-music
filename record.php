<?php
	include('resources.php');
	
	$html = "";
	
	if ($_GET) {
		$rid = isset($_GET['id']) ? $_GET['id'] : false;
		
		if ($rid !== false) {
			// data
			$record_info = $mc->getMDB()->getRecord($rid);
			
			// headline
			$html .= "<h3>Record details</h3>";
		
			$html .= $mc->getFrontend()->getRecordDetailsHtml($record_info);
		}
	}
	
	echo $mc->getIndexHTML($html);