<?php
session_start ();
header ("Content-type: image/png");

$width = 150;
$height = 40;
$im = ImageCreate ($width, $height);


$background_color = ImageColorAllocate ($im, 230, 230, 230);
$black = ImageColorAllocate ($im, 0, 0, 0);
$grey = ImageColorAllocate ($im, 170, 170, 170);

mt_srand((double)microtime()*1000000);

for ($i=5 ;$i<$width ;$i+=5)
{
	ImageLine ($im, $i, 0, $i, $height, $grey);
}

for ($i=5 ;$i<$height ;$i+=5)
{
	ImageLine ($im, 0, $i, $width, $i, $grey);
}

$ans = "";

$str = "0123456789";

$ans = array (rand()%10, rand()%10, rand()%10, rand()%10, rand()%10, rand()%10);
$tmp = "";
for ($i=0 ;$i<6 ;$i++)
{
	$tmp .= $ans[$i];
}

$tmp = join ($ans);

$_SESSION["checkvalue"] = $tmp;


for ($i=0 ;$i<6 ;$i++)
{
	ImageTTFText ($im, mt_rand(16, 20), mt_rand(-30, 30), 20+$i*20, 30, $black, "ARIAL.TTF", $ans[$i]);
}

for ($i=0 ;$i<300 ;$i++)
{
	ImageSetPixel ($im, mt_rand(0, $width), mt_rand(0, $height), $black);
}

ImagePng ($im);
?>

