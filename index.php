<?php
	set_time_limit(0);

	include('resources.php');
	
	// Init web page
	//$hallo = new frontend;
	//echo $hallo->getHTML();
	
	echo $mc->getIndexHTML();

	// Page header
	//echo $mc->getPageHTML($mc->getMMDB()->getXMLSongData()->saveXML());
	
	//$date = $mc->getMMDate(new DateTime('2012-09-20'));
	//echo $mc->getPageHTML($mc->getMMDB()->getPlayedForDate($date));
	
	/*$threshold_low = $mc->getMMDate(new DateTime('2012-07-01'));
	$threshold_high = $mc->getMMDate(new DateTime('2012-08-31'));
	
	echo $mc->getPageHTML($mc->getMMDB()->getTimeSpanStat($threshold_low, $threshold_high));*/
	
	/*$db = new PDO('mysql:host=localhost;dbname=d01b0305;charset=utf8', 'd01b0305', 'B2rHpqExD3JgnctC');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	
	//$sql = "INSERT INTO logs (action, status, description) VALUES (:action, :status, :description)";
	$sql = "SELECT * FROM artists";
	$query = $db->prepare($sql);
	//$success = $query->execute( array(':action' => $action, ':status' => $status, ':description' => $description) );
	$success = $query->execute( );
	
	echo var_dump($db->errorInfo()) . "<br/>";
	echo "Success: " . $success . "<br/>";
	echo "Last Insert: " . $db->lastInsertId() . "<br/>";
	echo "RowCount: " . $query->rowCount(). "<br/>";
	
	$fetched = $query->fetchAll();
	
	echo var_dump($fetched);*/
	
	/*$name = "Old 97's";
	$tags = explode(" ", $name);
	
	$sid = $mdb->pushArtist($name);
	
	$mdb->setSearchTags($sid, $tags);*/
	
	/*$db = new MMDB($mdb);

	$xml = $db->getXMLSongData();
	
	echo $xml->saveXML();*/
	
	//$mc->importSongs();
	
	//$mc->importPlayed();
	
	//$header = array(array('name' => 'SongName', 'display' => 'Title'), array('name' => 'ArtistName', 'display' => 'Artist'), array('name' => 'RecordName', 'display' => 'Record'), array('name' => 'PlayCount', 'display' => 'Played'));
	//echo $mc->getPageHTML($mc->getTableFromArray('hallo', $header, $mc->getMDB()->getPlayedStatistics('2012-01-01', '2012-12-31')));
	
	//$mc->initialImport();
	
	//$mc->updateDatabase();