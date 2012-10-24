<?php
/*=======================================================================
// File: 				GENCOLORCHART.PHP
// Description: 		Automatically generates an indexpage of all named colors
// Created: 			2001-02-28
//	Last edit:			14/09/01 13:32
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					$Id: gencolorchart.php,v 1.4 2001/09/26 10:35:43 ljp Exp $ 
//
// License:				This code is released under GPL 2.0
//
// Note:					This is unsupported internal test-code.
//========================================================================
*/
include "jpgraph.php";
include "jpgraph_canvas.php";

// Height and width of each color sample
DEFINE("WIDTH",30);
DEFINE("HEIGHT",30);

function ColorBox($g,$x,$y,$nbr,&$colors) {
	$g->img->SetColor($colors[$nbr]);
	$g->img->FilledRectangle($x,$y,$x+WIDTH,$y+HEIGHT);
	$g->img->SetColor("black");
	$g->img->Rectangle($x,$y,$x+WIDTH,$y+HEIGHT);
	$g->img->SetTextAlign("center","top");
	$g->img->StrokeText($x+WIDTH/2,$y+HEIGHT+2,"$nbr:".$colors[$nbr]);
}

function GenColIndex() {
   $dummy=0;
   $rgb = new RGB($dummy);
   $colors = array_keys($rgb->rgb_table);
   sort($colors);

	$n=1;
	$c=0;
   echo "<ol>";
   while( $c < count($colors) ) {
   	$g = new CanvasGraph(700,1240);
   	$filename=CACHE_DIR."color_chart%02d.".$g->img->img_format;
   	$y=8;
   	$i=0;
   	while( $c<count($colors) && $i<112 ) {
   		$x=40; $j=0;
   		while( $c<count($colors) && $j<6 ) {
   			ColorBox($g,$x,$y,$c,$colors);
   			$x += 85+WIDTH;
   			++$i; ++$j; ++$c;
   		}	
   		$y += 35+HEIGHT;
   	}
   	$f = sprintf($filename,$n);
   	echo "<li>$f<br>\n";
   	flush();
   	$g->img->Stream($f);
   	$g->img->Destroy();
   	++$n;	
   	$allfiles[]=$f;
   }
   echo "</ol>";
   echo "<p>Generating color chart index page.";
   flush();
   
   $frm = "<img src=%s border=0>\n";
   $buf = "<h3>Color chart for JpGraph containing $c named colors</h3>\n";
   foreach( $allfiles as $f ) {
   	$buf .= sprintf($frm,basename($f));
   }
   $fp = fopen(CACHE_DIR."colorchart.html","w");
   if( !$fp ) 
   	die("Can't create index file.");
   fwrite($fp,$buf);
   fclose($fp);
   echo "<br>";
}

function GenThemes($themes) {
	
   $dummy=0;
   $rgb = new RGB($dummy);
   $colors = array_keys($rgb->rgb_table);
   sort($colors);

	$names = array_keys($themes);
	
	$n=1;
	echo "<ol>";
	foreach($themes as $ta) {
   	$g = new CanvasGraph(700,(HEIGHT+35)*ceil(count($ta)/5)+30);
   	$filename=CACHE_DIR."theme%02d.".$g->img->img_format;
   	$y=8;
   	$nbr=0;
   	while( $nbr < count($ta) ) {
   		$x=40; $j=0;
   		while( $nbr<count($ta) && $j<5 ) {
   			ColorBox($g,$x,$y,$ta[$nbr],$colors);
   			$x += 85+WIDTH;
   			++$j; ++$nbr;
   		}	
   		$y += 35+HEIGHT;
   	}
   	$f = sprintf($filename,$n);
   	$allfiles[]=$f;
   	echo "<li>$f [$nbr colors in theme '".$names[$n-1]."']\n";
   	flush();
   	$g->img->Stream($f);
   	$g->img->Destroy();
   	++$n;
	}
	echo "</ol>";
	
   echo "<p>Generating theme index page.";
   flush();
   
   $frm = "<img src=%s border=0>\n";
   --$n;
   $buf = "<h3>Theme chart for JpGraph containing $n themes</h3>\n";
   $i=1;
   foreach( $allfiles as $f ) {
   	$buf .= "<h4>Theme $i (<i>".$names[$i-1].")</i></h4>\n";
   	$buf .= sprintf($frm,basename($f));
   	++$i;
   }
   $fp = fopen(CACHE_DIR."themes.html","w");
   if( !$fp ) 
   	die("Can't create index file.");
   fwrite($fp,$buf);
   fclose($fp);
   echo "<br>";	
}

$th=array(
	"earth" 	=> array(22,424,10,34,40,45,49,62,63,74,77,119,120,134,136,141,168,180,209,218,346,395,89,430),
	"pastel" => array(22,424,27,38,42,58,66,79,105,110,128,147,152,230,240,331,337,405,415),
	"water"  => array(22,424,8,10,14,24,56,213,237,268,326,335,370,387,388),
	"sand"   => array(22,424,19,34,50,65,72,82,131,168,209)
);

echo "<h2>JpGraph color chart</h2>";
echo "Generating color chart images ...<br>\n";
flush();
$timer = new JpgTimer();
$timer->Push();
GenColIndex();

echo "<p>Generating themes...";
GenThemes($th);

$t=$timer->Pop()/1000;
$t=sprintf("<p>Work done in: %0.2f seconds.",round($t,2));
echo "$t<p>See <a href=\"".CACHE_DIR."colorchart.html\">Colorchart</a>\n";
echo "<br>See <a href=\"".CACHE_DIR."themes.html\">Index of themes</a>\n";
?>


