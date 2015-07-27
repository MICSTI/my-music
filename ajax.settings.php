<?php
	include('resources.php');
	
	if ($_GET) {
		$tab = isset($_GET['tab']) ? trim($_GET['tab']) : "";
		
		echo $mc->getFrontend()->getSettingsContent($mc->getMDB(), $tab);
	}