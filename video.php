<?php
require 'getid3-1.9.3/getid3/getid3.php';

$video = $_GET['video'];

$getID3 = new getID3;
$file = $getID3->analyze($video);

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>video.local Player</title>
	<link href="http://vjs.zencdn.net/c/video-js.css" rel="stylesheet">
</head>
<body>
	<video id="video_1" class="video-js vjs-default-skin" controls
  	  preload="auto" width="<?php echo $file['video']['resolution_x'] ?>" height="<?php echo $file['video']['resolution_y'] ?>" poster="my_video_poster.png"
  	  data-setup="{}">
  		<source src="<?php echo $video ?>" type='video/mp4'>
	</video>
	<br />
	<a href="index.php">back to search</a>
	<script src="http://vjs.zencdn.net/c/video.js"></script>
</body>
</html>