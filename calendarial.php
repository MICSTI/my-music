<?php
	include('resources.php');

	$html = "";
	
	// Data
	$year_charts = $mc->getMDB()->getCalendarialYears();
	
	// flag for setting "in" class for first element
	$first = true;
	
	// Headline
	$html .= "<h3>Calendarial</h3>";
	
	$html .= "<div id='calendarial-container'>";
		// nav left
		$html .= "<div class='col-sm-3'>";
			
			if (count($year_charts) > 0) {
				// nav accordion
				$html .= "<div id='calendarial-accordion' class='panel-group'>";
					foreach ($year_charts as $year_chart) {
						$collapse_in = $first ? " in" : "";
						
						$year = $year_chart["ChartYear"];
						
						$html .= "<div class='panel panel-default'>";
							$html .= "<div class='panel-heading'>";
								$html .= "<div class='panel-title'>";
									$html .= "<a data-toggle='collapse' data-parent='#calendarial-accordion' href='#calendarial-" . $year . "'>" . $year . "</a>";
								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>";
						
						$html .= "<div id='calendarial-" . $year . "' class='panel-collapse collapse" . $collapse_in . "'>";
							$html .= "<div class='panel-body'>";
								$html .= "<p>" . $year . " Charts</p>";
							$html .= "</div>";
						$html .= "</div>";
						
						$first = false;
					}
				$html .= "</div>";
			}
			
		$html .= "</div>";
		
		// content right
		$html .= "<div class='col-sm-9' id='calendarial-content'>";
			$html .= "CONTENT";
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "charts");