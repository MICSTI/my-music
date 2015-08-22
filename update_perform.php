<?php
	include('resources.php');

	$success = $mc->updateDatabase();
	
	echo json_encode($success);