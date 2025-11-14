<?php
	$configFilePrefix = "../";
	include_once('../functions.php'); disableBuffering();	 // must be first line
	// Settings are now in $webSettings_array.

	$homePage = v("homePage", null, $webSettings_array);
	$includeGoogleAnalytics = v("includeGoogleAnalytics", false, $homePage);
	$thumbnailsortorder = v("thumbnailsortorder", "descending", $homePage);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" type="image/png" href="../allsky-favicon.png">
		<title>All Images</title>

<?php	if ($includeGoogleAnalytics && file_exists("../js/analyticsTracking.js")) {
			echo "<script src='../js/analyticsTracking.js'></script>";
		}
?>
		<link href="../font-awesome/css/all.min.css" rel="stylesheet">
		<link href="../css/allsky.css" rel="stylesheet">
	</head>
	<body>
<?php
	$back_button = "<button class='btn btn-primary' onclick='window.history.back();'>Back</button>";
	
	// Get all date directories
	$images_dir = "../images";
	$days = array();
	
	if (is_dir($images_dir) && $handle = opendir($images_dir)) {
		while (false !== ($entry = readdir($handle))) {
			// Check if it's a directory and matches YYYYMMDD format
			if (is_dir("$images_dir/$entry") && preg_match('/^\d{8}$/', $entry)) {
				$days[] = $entry;
			}
		}
		closedir($handle);
	}
	
	if (count($days) == 0) {
		echo "<p>$back_button</p>";
		echo "<div class='noImages'>No uploaded images found</div>";
	} else {
		// Sort days in descending order (newest first)
		if ($thumbnailsortorder === "descending") {
			arsort($days);
			$sortOrder = "Sorted newest to oldest (descending)";
		} else {
			asort($days);
			$sortOrder = "Sorted oldest to newest (ascending)";
		}
		
		echo "<table class='imagesHeader'>";
			echo "<tr>";
				echo "<td class='headerButton'>$back_button</td>";
				echo "<td class='headerTitle'>All Images</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td colspan='2'><div class='imagesSortOrder'>$sortOrder</div></td>";
			echo "</tr>";
		echo "</table>";
		echo "<div class='archived-files'>\n";
		
		foreach ($days as $day) {
			// Format: YYYYMMDD -> YYYY-MM-DD
			$year = substr($day, 0, 4);
			$month = substr($day, 4, 2);
			$day_num = substr($day, 6, 2);
			$formatted_date = "$year-$month-$day_num";
			
			// Count images in this day
			$count = 0;
			$first_image = null;
			$day_path = "$images_dir/$day";
			if ($handle = opendir($day_path)) {
				while (false !== ($file = readdir($handle))) {
					if (preg_match('/^\w+-.*\d{14}\.(jpe?g|png)$/i', $file)) {
						$count++;
						if ($first_image === null) {
							$first_image = $file;
						}
					}
				}
				closedir($handle);
			}
			
			// Use first image as thumbnail, or show no thumbnail
			$thumbnail_path = "../NoThumbnail.png";
			if ($first_image !== null) {
				$thumbnail_path = "$images_dir/$day/$first_image";
			}
			
			echo "<a href='day.php?day=$day'><div class='day-container'><div class='img-text'>";
				echo "<div class='image-container'>";
					echo "<img id='$day' src='$thumbnail_path' title='$formatted_date - $count images'/>";
				echo "</div>";
				echo "<div class='day-text'>$formatted_date ($count images)</div>";
			echo "</div></div></a>\n";
		}
		
		echo "</div>";	// archived-files
		echo "<div class='archived-files-end'></div>";	// clears "float" from archived-files
		echo "<div class='archived-files'><hr></div>";
	}
?>
	</body>
</html>
