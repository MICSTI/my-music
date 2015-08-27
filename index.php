<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Home</h3>";
	
	// Welcome
	$html .= "<div class='col-xs-12'>";
		$html .= "<div class='panel panel-default'>";
			$html .= "<div class='panel-heading'><h4>Welcome to myMusic</h4></div>";
			
			$html .= "<div class='home-welcome panel-body'>";
				$html .= "<p>Here you can learn everything you want about your music library.</p>
						  <p>Which song you listened to most last year in April, who your favourite artists are or where they are from.</p>
						  <p>Whatever you are interested in, myMusic will happily tell you.</p>";
			$html .= "</div>";
		$html .= "</div>";
	$html .= "</div>";
	
	// Total statistics
	$html .= "<div class='col-xs-12'>";
		$html .= "<div class='panel panel-default'>";
			$html .= "<div class='panel-heading'><h4>A few interesting facts...</h4></div>";
			
			$html .= "<div class='panel-body'>";
				$html .= "";
			$html .= "</div>";
		$html .= "</div>";
	$html .= "</div>";
	
	// Top 5 Songs
	$songs = $mc->getMDB()->getTopXSongs(5);
	
	$html .= "<div class='col-xs-6'>";
		$html .= "<div class='panel panel-default'>";
			$html .= "<div class='panel-heading'><h4>Your favourite songs</h4></div>";
			
			$html .= "<div class='panel-body'>";
				if (!empty($songs)) {
					$html .= "<table class='table table-striped'>";
						$html .= "<thead>";
							$html .= "<tr>";
								$html .= "<th>Song</th>";
								$html .= "<th>Artist</th>";
							$html .= "</tr>";
						$html .= "</thead>";
					
						$html .= "<tbody>";
							foreach ($songs as $song) {
								$html .= "<tr>";
									$html .= "<td class='col-xs-6'>" . getSongLink($song["SongId"], $song["SongName"]) . "</td>";
									$html .= "<td class='col-xs-6'>" . getArtistLink($song["ArtistId"], $song["ArtistName"]) . "</td>";
								$html .= "</tr>";
							}
						$html .= "</body>";
					$html .= "</table>";
				} else {
					$html .= "Unfortuntately we were not able to get your 5 favourite songs.";
				}
			$html .= "</div>";
		$html .= "</div>";
	$html .= "</div>";
	
	// Top 5 Artists
	$artists = $mc->getMDB()->getTopXArtists(5);
	
	$html .= "<div class='col-xs-6'>";
		$html .= "<div class='panel panel-default'>";
			$html .= "<div class='panel-heading'><h4>Your favourite artists</h4></div>";
			
			$html .= "<div class='panel-body'>";
				if (!empty($artists)) {
					$html .= "<table class='table table-striped'>";
						$html .= "<thead>";
							$html .= "<tr>";
								$html .= "<th>Artist</th>";
							$html .= "</tr>";
						$html .= "</thead>";
					
						$html .= "<tbody>";
							foreach ($artists as $artist) {
								$html .= "<tr>";
									$html .= "<td class='col-xs-12'>" . getArtistLink($artist["ArtistId"], $artist["ArtistName"]) . "</td>";
								$html .= "</tr>";
							}
						$html .= "</body>";
					$html .= "</table>";
				} else {
					$html .= "Unfortuntately we were not able to get your 5 favourite artists.";
				}
			$html .= "</div>";
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "home");