<?php
	$configFilePrefix = "../";
	include_once('../functions.php'); disableBuffering();	 // must be first line
	// Settings are now in $webSettings_array.

	$homePage = v("homePage", null, $webSettings_array);
	$includeGoogleAnalytics = v("includeGoogleAnalytics", false, $homePage);
	$thumbnailsortorder = v("thumbnailsortorder", "descending", $homePage);
	$thumbnailSizeX = v("thumbnailsizex", 100, $webSettings_array['homePage']);
	
	// Get the day from query parameter
	$day = isset($_GET['day']) ? $_GET['day'] : null;
	
	// Validate day format (YYYYMMDD)
	if ($day === null || !preg_match('/^\d{8}$/', $day)) {
		header("Location: index.php");
		exit;
	}
	
	// Format: YYYYMMDD -> YYYY-MM-DD
	$year = substr($day, 0, 4);
	$month = substr($day, 4, 2);
	$day_num = substr($day, 6, 2);
	$formatted_date = "$year-$month-$day_num";
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" type="image/png" href="../allsky-favicon.png">
		<title>Images - <?php echo $formatted_date; ?></title>

<?php	if ($includeGoogleAnalytics && file_exists("../js/analyticsTracking.js")) {
			echo "<script src='../js/analyticsTracking.js'></script>";
		}
?>
		<link href="../font-awesome/css/all.min.css" rel="stylesheet">
		<link href="../css/allsky.css" rel="stylesheet">
		<link href="../documentation/css/viewer.min.css" rel="stylesheet">
		<script src="../js/jquery.min.js"></script>
		<script src="../js/viewer.min.js"></script>
		<script src="../js/jquery-viewer.min.js"></script>
	</head>
	<body>
<?php
	$back_button = "<button class='btn btn-primary' onclick='window.location.href=\"index.php\";'>Back</button>";
	
	$images_dir = "../images/$day";
	$images = array();
	
	if (is_dir($images_dir) && $handle = opendir($images_dir)) {
		while (false !== ($file = readdir($handle))) {
			// Name format: "image-YYYYMMDDHHMMSS.jpg" or .jpe or .png
			if (preg_match('/^\w+-.*\d{14}\.(jpe?g|png)$/i', $file)) {
				$images[] = $file;
			}
		}
		closedir($handle);
	}
	
	if (count($images) == 0) {
		echo "<p>$back_button</p>";
		echo "<div class='noImages'>No images for $formatted_date</div>";
	} else {
		// Sort images
		if ($thumbnailsortorder === "descending") {
			arsort($images);
			$sortOrder = "Sorted newest to oldest (descending)";
		} else {
			asort($images);
			$sortOrder = "Sorted oldest to newest (ascending)";
		}
?>

<script>
$( document ).ready(function() {
	$('#images').viewer({
		url(image) {
			return image.src;
		},
		transition: false
	});
	$('.thumb').each(function(){		
		this.title += "\n" + getTimeStamp(this.src) + "\n" + "Click for full resolution image.";
	});
});

function getTimeStamp(url)
{
	var filename = url.substring(url.lastIndexOf('/')+1);			// everything after the last "/"
	var timeStamp = filename.substr(filename.lastIndexOf('-')+1);	// everything after the last "-"
	// YYYY MM DD HH MM SS
	// 0123 45 67 89 01 23
	var year = timeStamp.substr(0, 4);
	var month = timeStamp.substr(4, 2);
	var day = timeStamp.substr(6, 2);
	var hour = timeStamp.substr(8, 2);
	var minute = timeStamp.substr(10, 2);
	var second = timeStamp.substr(12, 2);
	var date = new Date(year, month-1, day, hour, minute, second, 0);
	return date.toDateString() + " @ " + hour + ":" + minute + ":" + second;
}
</script>

<?php
		echo "<table class='imagesHeader'>";
			echo "<tr>";
				echo "<td class='headerButton'>$back_button</td>";
				echo "<td class='headerTitle'>Images - $formatted_date &nbsp; &nbsp; <span class='imagesSortOrder'>$sortOrder</span></td>";
			echo "</tr>";
		echo "</table>";
		echo "<div class='row'>";
			echo "<div id='images'>";
		
		foreach ($images as $image) {
			$image_path = "$images_dir/$image";
			echo "<div class='left'>";
			echo "<img src='$image_path' title='$image' class='thumb thumbBorder' style='max-width: {$thumbnailSizeX}px;'/>";
			echo "</div>\n";
		}
		
		echo "</div>";	// images
		echo "</div>";	// row
	}
?>
	</body>
</html>
