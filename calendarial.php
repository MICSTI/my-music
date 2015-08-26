<?php
	include('resources.php');

	$html = "";
	
	// Data
	$year_charts = $mc->getMDB()->getCalendarialYears();
	
	// flag for setting "in" class for first element
	$first = true;
	
	// first year (for getting the content automatically for this year)
	$first_year;
	
	// Headline
	$html .= "<h3>Calendarial</h3>";
	
	$html .= "<div id='calendarial-container'>";
		// nav left
		$html .= "<div class='col-sm-3'>";
			
			if (count($year_charts) > 0) {
				// nav accordion
				$html .= "<div id='calendarial-accordion' class='panel-group' data-spy='affix' data-offset-top='230'>";
					foreach ($year_charts as $year_chart) {
						if ($first) {
							$first = false;
							
							$first_year = $year_chart["ChartYear"];
							$collapse_in = " in";
							$aria_expanded = "true";
						} else {
							$collapse_in = "";
							$aria_expanded = "false";
						}
						
						$year = $year_chart["ChartYear"];
						
						$html .= "<div class='panel panel-default'>";
							$html .= "<div class='panel-heading'>";
								$html .= "<div class='panel-title'>";
									$html .= "<a class='calendarial-item' id='#calendarial-year-" . $year . "' data-toggle='collapse' data-parent='#calendarial-accordion' aria-expanded='" . $aria_expanded . "' href='#calendarial-" . $year . "'>" . $year . "</a>";
								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>";
						
						$html .= "<div id='calendarial-" . $year . "' class='panel-collapse collapse" . $collapse_in . "'>";
							$html .= "<div class='panel-body'>";
								
							$html .= "</div>";
						$html .= "</div>";
						
						$first = false;
					}
				$html .= "</div>";
			}
			
		$html .= "</div>";
		
		// content right
		$html .= "<div class='col-sm-9' id='calendarial-content'>";
			// automatically get content for the first year in the accordion
			$html .= $mc->getFrontend()->getCalendarialChartsContent($mc->getMDB(), "year", $first_year);
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "statistics");