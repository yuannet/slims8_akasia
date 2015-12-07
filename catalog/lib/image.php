<?php 

function image($size,$filename) {
// Content type
header('Content-Type: image/jpeg');

$path = "../../images/docs/{$filename}";
//$path = "../../images/docs/{webapp_arch.jpg}";
$filename  = $path;

// Get new dimensions
list($width_orig, $height_orig) = getimagesize($filename);
$width = $size;

if ($width != '300' ) {
    $height =250;
    $width = $width_orig*($size/$width_orig); 
} else {
    $width =$size;
    $height = $height_orig*($width/$width_orig);
}
//echo $height;
// Resample
$image_p = imagecreatetruecolor($width, $height);
$image = imagecreatefromjpeg($filename);
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

// Output
return imagejpeg($image_p, null, 100);
}

    isset($_GET['s']) ? $size = $_GET['s']:$size='120';
    isset($_GET['f']) ? $file = $_GET['f']:$file='0.jpg';   
    image($size,$file);
?>