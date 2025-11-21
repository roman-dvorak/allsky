<?php
// Image archive browser - displays hierarchical images/YYYY/MMDD/ structure
$configFilePrefix = "../";
include_once('../functions.php');
disableBuffering();

$homePage = v("homePage", null, $webSettings_array);
$includeGoogleAnalytics = v("includeGoogleAnalytics", false, $homePage);
$thumbnailsortorder = v("thumbnailsortorder", "ascending", $homePage);
$thumbnailSizeX = v("thumbnailsizex", 100, $homePage);

// Get date from URL parameter (YYYYMMDD format)
$date = isset($_GET['date']) ? $_GET['date'] : '';
$view_raw = isset($_GET['raw']) && $_GET['raw'] == '1';

$base_dir = $view_raw ? 'raw_images' : 'images';
$title = $view_raw ? "Raw Images Archive" : "Images Archive";

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/png" href="../allsky-favicon.png">
	<title><?php echo $title; ?></title>
<?php
	if ($includeGoogleAnalytics && file_exists("../myFiles/analyticsTracking.js")) {
		echo "<script src='../myFiles/analyticsTracking.js'></script>";
	}
?>
	<link href="../font-awesome/css/all.min.css" rel="stylesheet">
	<link href="../css/allsky.css" rel="stylesheet">
	<style>
		.calendar-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
			gap: 15px;
			margin: 20px auto;
			max-width: 1200px;
		}
		.calendar-item {
			text-align: center;
			padding: 15px;
			background: #f5f5f5;
			border-radius: 5px;
			text-decoration: none;
			color: #333;
			transition: background 0.2s;
		}
		.calendar-item:hover {
			background: #e0e0e0;
		}
		.calendar-year {
			font-size: 24px;
			font-weight: bold;
		}
		.calendar-date {
			font-size: 18px;
			margin-top: 5px;
		}
		.calendar-count {
			font-size: 14px;
			color: #666;
			margin-top: 5px;
		}
		.back-link {
			display: inline-block;
			margin: 20px;
			padding: 10px 20px;
			background: #007bff;
			color: white;
			text-decoration: none;
			border-radius: 5px;
		}
		.back-link:hover {
			background: #0056b3;
			color: white;
		}
		.toggle-raw {
			margin: 20px;
			padding: 10px 20px;
			background: #28a745;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			display: inline-block;
		}
		.toggle-raw:hover {
			background: #218838;
			color: white;
		}
	</style>
</head>
<body>
	<h1 style="text-align: center;"><?php echo $title; ?></h1>
	
	<div style="text-align: center;">
		<a href="../index.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to Live View</a>
		<?php
		// Toggle between images and raw_images
		if (is_dir("../raw_images")) {
			$toggle_url = "index.php";
			if ($date) $toggle_url .= "?date=$date";
			if (!$view_raw) $toggle_url .= ($date ? "&" : "?") . "raw=1";
			
			$toggle_text = $view_raw ? "View Processed Images" : "View Raw Images";
			echo "<a href='$toggle_url' class='toggle-raw'><i class='fa fa-sync'></i> $toggle_text</a>";
		}
		?>
	</div>

<?php

// Function to get image count in a directory
function get_image_count($dir) {
	if (!is_dir($dir)) return 0;
	$files = glob($dir . "/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
	return $files ? count($files) : 0;
}

// If date is specified (YYYYMMDD format), show images for that date
if ($date) {
	$image_dir = "../$base_dir/$date";
	
	if (!is_dir($image_dir)) {
		echo "<p style='text-align:center; color:red;'>No images found for $date</p>";
	} else {
		$images = glob($image_dir . "/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
		
		if (!$images || count($images) == 0) {
			echo "<p style='text-align:center;'>No images found for $date</p>";
		} else {
			// Sort images
			if ($thumbnailsortorder === "descending") {
				rsort($images);
			} else {
				sort($images);
			}
			
			// Format date nicely: YYYYMMDD -> YYYY-MM-DD
			$formatted_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
			echo "<h2 style='text-align:center;'>Images for $formatted_date</h2>";
			echo "<p style='text-align:center;'>Total: " . count($images) . " images</p>";
			
			// Display images with lightgallery
			echo '<div id="lightgallery" style="text-align: center;">';
			foreach ($images as $image) {
				$rel_path = str_replace("../", "", $image);
				$basename = basename($image);
				echo "<a href='$rel_path' data-lg-size='1600-2400'>";
				echo "<img alt='$basename' width='$thumbnailSizeX' height='$thumbnailSizeX' src='$rel_path' loading='lazy' style='margin: 5px;' />";
				echo '</a>';
			}
			echo '</div>';
			
			// Add lightgallery JS
			echo '<link type="text/css" rel="stylesheet" href="../js/lightgallery/css/lightgallery-bundle.min.css" />';
			echo '<script src="../js/lightgallery/lightgallery.min.js"></script>';
			echo '<script src="../js/lightgallery/plugins/zoom/lg-zoom.min.js"></script>';
			echo '<script src="../js/lightgallery/plugins/thumbnail/lg-thumbnail.min.js"></script>';
			echo '<script>';
			echo 'const galleryElement = document.getElementById("lightgallery");';
			echo 'lightGallery(galleryElement, {';
			echo '  selector: "a",';
			echo '  plugins: [lgZoom, lgThumbnail],';
			echo '  mode: "lg-slide-circular",';
			echo '  speed: 400,';
			echo '  download: false,';
			echo '  thumbnail: true';
			echo '});';
			echo '</script>';
		}
	}
	
	echo "<div style='text-align:center; margin-top:30px;'>";
	echo "<a href='index.php" . ($view_raw ? "?raw=1" : "") . "' class='back-link'><i class='fa fa-arrow-left'></i> Back to All Dates</a>";
	echo "</div>";


// Show available dates (YYYYMMDD format)
} else {
	if (!is_dir("../$base_dir")) {
		echo "<p style='text-align:center; color:red;'>No $base_dir directory found. Enable 'Upload With Original Name' in settings.</p>";
	} else {
		$date_dirs = glob("../$base_dir/*", GLOB_ONLYDIR);
		
		if (!$date_dirs || count($date_dirs) == 0) {
			echo "<p style='text-align:center;'>No archived images found yet.</p>";
		} else {
			rsort($date_dirs); // Newest first
			
			echo "<h2 style='text-align:center;'>Available Dates</h2>";
			echo '<div class="calendar-grid">';
			
			foreach ($date_dirs as $date_dir) {
				$date_val = basename($date_dir);
				$count = get_image_count($date_dir);
				
				if ($count > 0) {
					// Format date nicely: YYYYMMDD -> YYYY-MM-DD
					$formatted_date = substr($date_val, 0, 4) . "-" . substr($date_val, 4, 2) . "-" . substr($date_val, 6, 2);
					
					echo "<a href='index.php?date=$date_val" . ($view_raw ? "&raw=1" : "") . "' class='calendar-item'>";
					echo "<div class='calendar-date'>$formatted_date</div>";
					echo "<div class='calendar-count'>$count images</div>";
					echo "</a>";
				}
			}
			
			echo '</div>';
		}
	}
}

?>
</body>
</html>
