<?php
//=========================================================================
// Name:			adjimg.php
// Written by: Johan Persson (johanp@aditus.nu)
// Last edit:	14/09/01 13:32	
// Ver: 			$Id: adjimg.php,v 1.4 2001/09/27 22:07:12 ljp Exp $
//
// Description:
// (Unsupported) Utility to take an image and adjust it's brightness, 
// contrast and saturation. The modified image is the displayed. 
// The original file is untouched.
//
// Usage: adjimg.php?file=name&b=value&c=value&s=scale&sat=saturation
//=========================================================================

function LoadImage($filename,$format="png") {
	$f = "imagecreatefrom".$format;
	$img = @$f($filename);
	if( !$img ) {
		die("Error: Can't read image file: $filename");   
	}
	return $img;
}

function AdjSat($img,$sat) {
	$nbr = imagecolorstotal ($img);
	for( $i=0; $i<$nbr; ++$i ) {
		$colarr = imagecolorsforindex ($img,$i);
		$rgb[0]=$colarr["red"];
		$rgb[1]=$colarr["green"];
		$rgb[2]=$colarr["blue"];
		$rgb = AdjRGBSat($rgb,$sat);
		imagecolorset ($img, $i, $rgb[0], $rgb[1], $rgb[2]);
	}
}

function AdjBrightContrast($img,$bright,$contr) {
	$nbr = imagecolorstotal ($img);
	for( $i=0; $i<$nbr; ++$i ) {
		$colarr = imagecolorsforindex ($img,$i);
		$r = AdjRGBBrightContrast($colarr["red"],$bright,$contr);
		$g = AdjRGBBrightContrast($colarr["green"],$bright,$contr);
		$b = AdjRGBBrightContrast($colarr["blue"],$bright,$contr);		
		imagecolorset ($img, $i, $r, $g, $b);
	}
}

function AdjRGBBrightContrast($rgb,$bright,$contr) {
	// First handle contrast, i.e change the dynamic range around grey
	if( $contr <= 0 ) {
		// Decrease contrast
		$adj = abs($rgb-128) * (-$contr);
		if( $rgb < 128 ) 
			$rgb += $adj;
		else 
			$rgb -= $adj;
	}
	else { // $contr > 0
		// Increase contrast
		if( $rgb < 128 )
			$rgb = $rgb - ($rgb * $contr);
		else
			$rgb = $rgb + ((255-$rgb) * $contr);
	}
	
	// Add (or remove) various amount of white
	$rgb += $bright*255;	
	$rgb=min($rgb,255);
	$rgb=max($rgb,0);
	return $rgb;	
}
	


// Adjust saturation for RGB array $u. $sat is a value between -1 and 1
// Note: Due to GD inability to handle true color the RGB values are only between
// 8 bit. This makes saturation quite sensitive for small increases in parameter sat.
// 
// Tip: To get a grayscale picture set sat=-100, values <-100 changes the colors
// to the complementary colors.
// 
// Implementation note: The saturation is implemented directly in the RGB space
// by adjusting the perpendicular distance between the RGB point and the "grey"
// line (1,1,1). Setting $sat>0 moves the point away from the line along the perp.
// distance and a negative value moves the point closer to the line.
// The values are truncated when the color point hits the bounding box along the
// RGB axis.
// DISCLAIMER: I'm not 100% sure this is he correct way to imeplemen a saturation 
// function in RGB space. 
function sign($a) {if( $a>=0) return 1; else return -1;}
function AdjRGBSat($rgb,$sat) {
	// Gray vector
	$v=array(1,1,1);

	// Dot product
	$dot = $rgb[0]*$v[0]+$rgb[1]*$v[1]+$rgb[2]*$v[2];

	// Normalize dot product
	$normdot = $dot/3;

	// Direction vector between $u and its projection onto $v
	for($i=0; $i<3; ++$i)
		$r[$i] = $rgb[$i] - $normdot*$v[$i];

	// Adjustment factor so that sat==1 sets the highest RGB value to 255
	if( $sat > 0 ) {
		$m=0;
		for( $i=0; $i<3; ++$i) {
			if( sign($r[$i]) == 1 && $r[$i]>0)
				$m=max($m,(255-$rgb[$i])/$r[$i]);
		}
		$tadj=$m;
	}
	else
		$tadj=1;
		
	$tadj = $tadj*$sat;	
	for($i=0; $i<3; ++$i) {
		$un[$i] = round($rgb[$i] + $tadj*$r[$i]);		
		
		// Truncate color when they reach 0
		if( $un[$i]<0 ) $un[$i]=0;
		
		// Avoid potential rounding error
		if( $un[$i]>255 ) $un[$i]=255;
	}		
	return $un;	
}
	

	
if( empty($file) )
	die("<b>Usage:</b><br>r.php?file=name&[b=value][&c=value][&s=scale][&sat=saturation]<p>
	file= Filename, must end with the image format, i.e. .png, .jpg, .gif<br>
	b	= Brightness value [-1, 1]<br>
	c	= Contrast value [-1, 1]<br>
	s	= Scale<br>
	sat= Color saturation value.<br>");

if(strstr($file,"png")) 
	$bkg = LoadImage($file);
elseif( strstr($file,"jpg")) 
	$bkg = LoadImage($file,"jpeg");
elseif( strstr($file,"gif")) 
	$bkg = LoadImage($file,"gif");		
	
if( empty($b) ) $b=0;
if( empty($c) ) $c=0;
if( empty($s) ) $s=1;
if( empty($sat) ) $sat=0;

// Adjust contrast and brightness of background color
if( $b || $c )
	AdjBrightContrast($bkg,$b,$c);

// Adjust color saturation
if( $sat )
	AdjSat($bkg,$sat);

// Get width & height
$bw = ImageSX($bkg);
$bh = ImageSY($bkg);

// Scale image
$w=$bw*$s;
$h=$bh*$s;

$img = imagecreate($w,$h);
imagecopyresized($img,$bkg,0,0,0,0,$w,$h,$bw,$bh);
$background_color = ImageColorAllocate ($img, 255, 255, 255);

if(strstr($file,"png")) {
	header ("Content-type: image/png");
	imagepng($img);
}
elseif( strstr($file,"jpg")) {
	header ("Content-type: image/jpeg");
	imagejpeg($img);	
}
elseif( strstr($file,"gif")) {
	header ("Content-type: image/gif");
	imagegif($img);	
}
else
	die("Unknown graphic format in file $file");
?>
