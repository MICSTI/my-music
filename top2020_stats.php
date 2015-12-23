<?php
	include('resources.php');

	$html = "";
	
	// get available years from the database
	$years = $mc->getMDB()->getTop2020StatsYears();
	
	// flag for setting "in" class for first element
	$first = true;
	
	// Headline
	$html .= "<h3>Top 20/20 Stats</h3>";
	
	$html .= "<div id='top2020-stats-container'>";
		// nav left
		$html .= "<div class='col-sm-3'>";
			if (count($years) > 0) {
				// nav accordion
				$html .= "<div id='top2020-stats-accordion' class='panel-group' data-spy='affix' data-offset-top='230'>";
					foreach ($years as $year_elem) {
						if ($first) {
							$first = false;
							
							$collapse_in = " in";
							$aria_expanded = "true";
						} else {
							$collapse_in = "";
							$aria_expanded = "false";
						}
						
						$year = $year_elem["year"];
						
						$html .= "<div class='panel panel-default'>";
							$html .= "<div class='panel-heading'>";
								$html .= "<div class='panel-title'>";
									$html .= "<a class='top2020-stats-item' data-type='year' data-year='" . $year . "' data-toggle='collapse' data-parent='#top2020-stats-accordion' aria-expanded='" . $aria_expanded . "' href='#top2020-stats-" . $year . "'>" . $year . "</a>";
								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>";
						
						$html .= "<div id='top2020-stats-" . $year . "' class='panel-collapse collapse" . $collapse_in . "'>";
							$html .= "<div class='panel-body'>";
								// History
								$html .= "<div>";
									$html .= "<a href='#' class='top2020-stats-item' data-type='history' data-year='" . $year . "'>History</a>";
								$html .= "</div>";
								
								// Maximum amount
								$html .= "<div>";
									$html .= "<a href='#' class='top2020-stats-item' data-type='maximum' data-year='" . $year . "'>Maximum</a>";
								$html .= "</div>";	
								
								// No of #1 ranks
								$html .= "<div>";
									$html .= "<a href='#' class='top2020-stats-item' data-type='no1s' data-year='" . $year . "'># #1s</a>";
								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>";
					}
				$html .= "</div>";
			}
		$html .= "</div>";
		
		// content right
		$html .= "<div class='col-sm-9'>";
			$html .= "CONTENT";
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "statistics");