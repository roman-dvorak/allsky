<?php
// Image archive browser - displays hierarchical images/YYYY/MMDD/ structure
$configFilePrefix = "../";
include_once('../functions.php');
disableBuffering();

$homePage = v("homePage", null, $webSettings_array);
$includeGoogleAnalytics = v("includeGoogleAnalytics", false, $homePage);
$thumbnailsortorder = v("thumbnailsortorder", "ascending", $homePage);
$thumbnailSizeX = v("thumbnailsizex", 100, $homePage);

// Get year and date from URL parameters
$year = isset($_GET['year']) ? $_GET['year'] : '';
$mmdd = isset($_GET['mmdd']) ? $_GET['mmdd'] : '';
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
			if ($year) $toggle_url .= "?year=$year";
			if ($mmdd) $toggle_url .= ($year ? "&" : "?") . "mmdd=$mmdd";
			if (!$view_raw) $toggle_url .= (($year || $mmdd) ? "&" : "?") . "raw=1";
			
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

// If both year and mmdd are specified, show images
if ($year && $mmdd) {
	$image_dir = "../$base_dir/$year/$mmdd";
	
	if (!is_dir($image_dir)) {
		echo "<p style='text-align:center; color:red;'>No images found for $year-$mmdd</p>";
	} else {
		$images = glob($image_dir . "/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
		
		if (!$images || count($images) == 0) {
			echo "<p style='text-align:center;'>No images found for $year-$mmdd</p>";
		} else {
			// Sort images
			if ($thumbnailsortorder === "descending") {
				rsort($images);
			} else {
				sort($images);
			}
			
			echo "<h2 style='text-align:center;'>Images for $year-" . substr($mmdd, 0, 2) . "-" . substr($mmdd, 2, 2) . "</h2>";
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
	echo "<a href='index.php" . ($view_raw ? "?raw=1" : "") . "&year=$year' class='back-link'><i class='fa fa-arrow-left'></i> Back to $year</a>";
	echo "</div>";

// If only year is specified, show months (MMDD)
} elseif ($year) {
	$year_dir = "../$base_dir/$year";
	
	if (!is_dir($year_dir)) {
		echo "<p style='text-align:center; color:red;'>No images found for year $year</p>";
	} else {
		$month_dirs = glob($year_dir . "/*", GLOB_ONLYDIR);
		
		if (!$month_dirs || count($month_dirs) == 0) {
			echo "<p style='text-align:center;'>No images found for year $year</p>";
		} else {
			rsort($month_dirs); // Newest first
			
			echo "<h2 style='text-align:center;'>Days in $year</h2>";
			echo '<div class="calendar-grid">';
			
			foreach ($month_dirs as $month_dir) {
				$mmdd_val = basename($month_dir);
				$count = get_image_count($month_dir);
				
				if ($count > 0) {
					$month = substr($mmdd_val, 0, 2);
					$day = substr($mmdd_val, 2, 2);
					
					echo "<a href='index.php?year=$year&mmdd=$mmdd_val" . ($view_raw ? "&raw=1" : "") . "' class='calendar-item'>";
					echo "<div class='calendar-date'>$month-$day</div>";
					echo "<div class='calendar-count'>$count images</div>";
					echo "</a>";
				}
			}
			
			echo '</div>';
		}
	}
	
	echo "<div style='text-align:center; margin-top:30px;'>";
	echo "<a href='index.php" . ($view_raw ? "?raw=1" : "") . "' class='back-link'><i class='fa fa-arrow-left'></i> Back to Years</a>";
	echo "</div>";

// Show available years
} else {
	if (!is_dir("../$base_dir")) {
		echo "<p style='text-align:center; color:red;'>No $base_dir directory found. Enable 'Upload With Original Name' in settings.</p>";
	} else {
		$year_dirs = glob("../$base_dir/*", GLOB_ONLYDIR);
		
		if (!$year_dirs || count($year_dirs) == 0) {
			echo "<p style='text-align:center;'>No archived images found yet.</p>";
		} else {
			rsort($year_dirs); // Newest first
			
			echo "<h2 style='text-align:center;'>Available Years</h2>";
			echo '<div class="calendar-grid">';
			
			foreach ($year_dirs as $year_dir) {
				$year_val = basename($year_dir);
				
				// Count total images in this year
				$total = 0;
				$month_dirs = glob($year_dir . "/*", GLOB_ONLYDIR);
				if ($month_dirs) {
					foreach ($month_dirs as $month_dir) {
						$total += get_image_count($month_dir);
					}
				}
				
				if ($total > 0) {
					echo "<a href='index.php?year=$year_val" . ($view_raw ? "&raw=1" : "") . "' class='calendar-item'>";
					echo "<div class='calendar-year'>$year_val</div>";
					echo "<div class='calendar-count'>$total images</div>";
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
