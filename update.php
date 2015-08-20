<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Update</h3>";
	
	// File list
	$update_files = $mc->getUpdateFiles();
	
	// Files panel
	$html .= "<div class='panel panel-default'>";
		$html .= "<div class='panel-heading bold'>Available files</div>";
		
		$html .= "<div class='panel-body'>";
			if (count($update_files) > 0) {
				$html .= "<table class='table'>";
					$html .= "<thead>";
						$html .= "<tr>";
							$html .= "<th class='col-sm-2'>Date</th>";
							$html .= "<th class='col-sm-10'>Filename</th>";
						$html .= "</tr>";
					$html .= "</thead>";
					
					$html .= "<tbody>";
						foreach ($update_files as $update_file) {
							$type = substr($update_file, 0, strpos($update_file, "."));
							
							$timestamp = new UnixTimestamp($mc->getTimestampFromFilename($update_file));
							
							$row_class = $type == "desktop" ? "success" : "info";
							
							$html .= "<tr class='" . $row_class . "'>";
								$html .= "<td>" . $timestamp->convert2AustrianDatetime() . "</td>";
								$html .= "<td>" . $update_file . "</td>";
							$html .= "</tr>";
						}
					$html .= "</tbody>";
				$html .= "</table>";
			} else {
				// no files are available
				$html .= "There are no files for upload available.";
			}
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "update");