<?php
	include('resources.php');
	
	// compile top 20/20
	$mc->getMDB()->compileTop2020();
	
	// compile favourites
	$mc->getMDB()->compileFavourites();