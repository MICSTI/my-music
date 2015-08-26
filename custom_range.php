<?php
	include('resources.php');
	
	$html = "";
	
	// headline
	$html .= "<h3>Custom range statistics</h3>";
	
	// top bar with two datepickers and submit button
	$html .= "<div class='custom-statistics-control'>";
		$html .= "<form class='form-inline'>";
		
			$html .= "<div class='form-group'>";
				$html .= "<input type='text' id='custom-statistics-start-date' class='form-control date-picker' placeholder='Start date' />";
			$html .= "</div>";
			
			$html .= "<div class='form-group custom-statistics-between'>";
				$html .= "to";
			$html .= "</div>";
			
			$html .= "<div class='form-group'>";
				$html .= "<input type='text' id='custom-statistics-end-date' class='form-control date-picker' placeholder='End date' />";
			$html .= "</div>";
			
			$html .= "<div class='form-group custom-statistics-between'>";
				$html .= " ";
			$html .= "</div>";
			
			$html .= "<div class='form-group'>";
				$html .= "<button type='button' id='custom-range-calculate' class='btn btn-primary'>Calculate</button>";
			$html .= "</div>";
		
		$html .= "</form>";
	$html .= "</div>";
	
	// result content div
	$html .= "<div id='custom-statistics-result'></div>";
	
	echo $mc->getIndexHTML($html, "statistics");