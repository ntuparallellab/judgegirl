<?php
/*=======================================================================
// File: 				JPGRAPH_PIE.PHP
// Description: 		Pie plot extension for JpGraph
// Created: 			2001-02-14
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					$Id: jpgraph_pie.php,v 1.8 2001/11/09 20:00:27 ljp Exp $
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS PiePlot
// Description: 
//===================================================
class PiePlot {
	var $posx=0.5,$posy=0.5;
	var $radius=0.3;
	var $explode_slice=-1;
	var $labels, $legends=null;
	var $csimtargets=null;  // Array of targets for CSIM
	var $csimareas='';		// Generated CSIM text	
	var $csimalts=null;		// ALT tags for corresponding target
	var $data=null;
	var $title;
	var $startangle=0;
	var $weight=1, $color="black";
	var $font_family=FF_FONT1,$font_style=FS_NORMAL,$font_size=12,$font_color="black";
	var $legend_margin=6,$show_labels=true;
	var $precision=1,$show_psign=true;
	var $themearr=array(
		"earth" 	=> array(10,34,40,45,46,62,63,134,74,77,120,136,141,168,180,209,218,346,395,89,430),
		"pastel" => array(27,38,42,58,66,79,105,110,128,147,152,230,236,240,331,337,405,415),
		"water"  => array(8,370,10,40,335,56,213,237,268,14,326,387,24,388),
		"sand"   => array(27,168,34,170,19,50,65,72,131,209,46,393));
	var $theme="earth";
	var $setslicecolors=array();
	var $labelformat="%01.0f"; // Default format for labels
	var $labeltype=0; // Default to percentage
	
//---------------
// CONSTRUCTOR
	function PiePlot(&$data) {
		$this->data = $data;
		$this->title = new Text("");
		$this->title->SetFont(FF_FONT1,FS_BOLD);
	}

//---------------
// PUBLIC METHODS	
	function SetCenter($x,$y=0.5) {
		$this->posx = $x;
		$this->posy = $y;
	}

	function SetCSIMTargets(&$targets,$alts=null) {
		$this->csimtargets=$targets;
		$this->csimalts=$alts;
	}
	
	function GetCSIMareas() {
		return $this->csimareas;
	}

	function AddSliceToCSIM($i,$xc,$yc,$radius,$sa,$ea) {  //Slice number, ellipse centre (x,y), height, width, start angle, end angle
		
		//add coordinates of the centre to the map
		$coords = "$xc, $yc";

		//add coordinates of the first point on the arc to the map
		$xp = floor(($radius*cos($sa))+$xc);
		$yp = floor($yc-$radius*sin($sa));
		$coords.= ", $xp, $yp";
		
		//add coordinates every 0.2 radians
		$a=$sa+0.2;
		while ($a<$ea) {
			$xp = floor($radius*cos($a)+$xc);
			$yp = floor($yc-$radius*sin($a));
			$coords.= ", $xp, $yp";
			$a += 0.2;
		}
		
		//Add the last point on the arc
		$xp = floor($radius*cos($ea)+$xc);
		$yp = floor($yc-$radius*sin($ea));
		$coords.= ", $xp, $yp";
		if( !empty($this->csimtargets[$i]) )
			$this->csimareas .= "<area shape=\"poly\" coords=\"$coords\" href=\"".$this->csimtargets[$i]."\"";
		if( !empty($this->csimalts[$i]) ) {										
			$tmp=sprintf($this->csimalts[$i],$this->data[$i]);
			$this->csimareas .= " alt=\"$tmp\"";
		}
		$this->csimareas .= ">\r\n";
	}

	
	function SetTheme($t) {
		if( in_array($t,array_keys($this->themearr)) )
			$this->theme = $t;
		else
			die("JpGraph Error: Unknown theme: $t");
	}
	
	function ExplodeSlice($e) {
		$this->explode_slice=$e;
	}
	
	function SetSliceColors($c) {
		$this->setslicecolors = $c;
	}
	
	function SetStartAngle($a) {
		assert($a>=0 && $a<2*M_PI);
		$this->startangle = $a;
	}
	
	function SetFont($family,$style=FS_NORMAL,$size=10) {
		$this->font_family=$family;
		$this->font_style=$style;
		$this->font_size=$size;
	}
	
	// Size in percentage
	function SetSize($size) {
		assert($size>0 && $size<=0.5);
		$this->radius = $size;
	}
	
	function SetFontColor($color) {
		$this->font_color = $color;
	}
	
	// Set label arrays
	function SetLegends($l) {
		$this->legends = $l;
	}
	
	// Should the values be displayed?
	function HideLabels($f=true) {
		$this->show_labels = !$f;
	}
	
	// Specify label format as a "C" printf string
	function SetLabelFormat($f) {
		$this->labelformat=$f;
		// If format is specified don't add any %-sign
		$this->show_psign=0;
	}
	
	// Should we display actual value or percentage?
	function SetLabelType($t) {
		if( $t<0 || $t>1 ) 
			die("JpGraph Error: Label type for pie plots must be 0 or 1 (not $t).");
		$this->labeltype=$t;
		// Don't show percentage value when displaying absolute values
		if( $t==1 )
			$this->show_psign=0;
	}
	
	// Setup the legends
	function Legend(&$graph) {
		$colors = array_keys($graph->img->rgb->rgb_table);
   	sort($colors);	
   	$ta=$this->themearr[$this->theme];	
   	
   	if( $this->setslicecolors==null ) 
   		$numcolors=count($ta);
   	else
   		$numcolors=count($this->setslicecolors);
		
		$sum=0;
		for($i=0; $i<count($this->data); ++$i)
			$sum += $this->data[$i];
				
		$i=0;
		if( count($this->legends)>0 ) {
			foreach( $this->legends as $l ) {
				
				// Replace possible format with actual values
				if( $this->labeltype==0 )
					$l = sprintf($l,100*$this->data[$i]/$sum);
				else
					$l = sprintf($l,$this->data[$i]);
				
				if( $this->setslicecolors==null ) 
					$graph->legend->Add($l,$colors[$ta[$i%$numcolors]]);
				else
					$graph->legend->Add($l,$this->setslicecolors[$i%$numcolors]);
				++$i;
				
				// Breakout if there are more legends then values
				if( $i==count($this->data) ) return;
			}
		}
	}
	
	// Specify precision for labels. This is almost a deprecated function
	// nowadays since the introduction of SetLabelFormat()
	function SetPrecision($p,$psign=true) {
		if( $p<0 || $p>8 ) 
			die("JpGraph Error: Pie label Precision must be between 0 and 8");
		$this->labelformat="%01.".$p."f";
		$this->show_psign=$psign;
	}
	
	function Stroke(&$img) {
		
		$colors = array_keys($img->rgb->rgb_table);
   	sort($colors);	
   	$ta=$this->themearr[$this->theme];	
   	
   	if( $this->setslicecolors==null ) 
   		$numcolors=count($ta);
   	else
   		$numcolors=count($this->setslicecolors);
   	
		// Draw the slices
		$sum=0;
		for($i=0; $i<count($this->data); ++$i)
			$sum += $this->data[$i];
		
		// Format the titles for each slice
		for( $i=0; $i<count($this->data); ++$i) {
			if( $this->labeltype==0 )
				if( $sum != 0 )
					$l = round(100*$this->data[$i]/$sum,$this->precision);
				else
					$l = 0;
			else
				$l = $this->data[$i];
			$l = sprintf($this->labelformat,$l);
			if( $this->show_psign ) $l .= "%";
			$this->labels[$i]=$l;
		}
		
		// Set up the pic-circle
		$radius = floor($this->radius*min($img->width,$img->height));
		$xc = $this->posx*$img->width;
		$yc = $this->posy*$img->height;

		// Draw the first slice first line
		$img->SetColor($this->color);			
		$img->SetLineWeight($this->weight);
		$a = $this->startangle;
		$x = round(cos($a)*$radius);
		$y = round(sin($a)*$radius);
		$img->Line($xc,$yc,$xc+$x,$yc-$y);		

		
		if( $this->explode_slice>=0 && $sum>0 ) {
			if( $this->explode_slice>0 )
				$p = $this->explode_slice-1;
			else 
				$p = count($this->data)-1;
				
			$acc=0;
			for($i=0; $i<$this->explode_slice; ++$i)
				$acc += $this->data[$i];
				
			$start = 360-($acc/$sum)*360;
			$end = 360-(($this->data[$this->explode_slice]/$sum)*360+($acc/$sum)*360);
			
			$img->Arc($xc,$yc,2*$radius,2*$radius,$start,$end);
		}
		else
			$img->Circle($xc,$yc,$radius);
		
		for($i=0; $sum>0 && $i<count($this->data); ++$i) {
			$img->SetColor($this->color);	
			$d = $this->data[$i];
			$la = $a + M_PI*$d/$sum;
			$old_a = $a;
			$a += 2*M_PI*$d/$sum;
			$x = round(cos($a)*$radius);
			$y = round(sin($a)*$radius);
			
			if ($this->csimtargets) {
				$this->AddSliceToCSIM($i,$xc,$yc,$radius,$old_a,$a);
			}			
			
			// Don't stroke last line since this is the same as the first
			// line drawn but due to rounding error it might be just a 
			// tad pixel off and may not look as good.
			if( $i<count($this->data)-1)
				$img->Line($xc,$yc,$xc+$x,$yc-$y);
			
			if( $this->setslicecolors==null )
				$slicecolor=$colors[$ta[$i%$numcolors]];
			else
				$slicecolor=$this->setslicecolors[$i%$numcolors];
				
			if( $i == $this->explode_slice ) {
				$this->explode_slice($img,$slicecolor,$this->labels[$i],$xc,$yc,$radius,$old_a,$a);
			}
			else {
				if( $this->show_labels ) 
					$this->StrokeLabels($this->labels[$i],$img,$xc,$yc,$la,$radius);			
				$img->SetColor($slicecolor);
				$xf = cos($la)*$radius/2;
				$yf = sin($la)*$radius/2;	
				$old_x = round(cos($a)*$radius/2);
				$old_y = round(sin($a)*$radius/2);
				$dist = sqrt(pow($old_x-$xf,2)+pow($old_y-$yf,2));
				if ($dist >=2)						
					$img->Fill($xf+$xc,$yc-$yf); 
			}
		}	
		
		// Adjust title position
		$this->title->Pos($xc,$yc-$img->GetFontHeight()-$radius,"center","bottom");
		$this->title->Stroke($img);
		
	}

//---------------
// PRIVATE METHODS	
	function explode_slice($img,$color,$label,$xc,$yc,$r,$old_a,$a) {
		$extract=0.3;
		$am = abs($a-$old_a)/2+$old_a;
		$xc = cos($am)*$r*$extract+$xc;
		$yc = $yc - sin($am)*$r*$extract;
		
		$x1 = cos($old_a)*$r + $xc;
		$x2 = cos($a)*$r + $xc;
		$y1 = $yc - sin($old_a)*$r;
		$y2 = $yc - sin($a)*$r;

		$xf=cos($am)*$r*0.5+$xc;
		$yf=$yc-sin($am)*$r*0.5;
						
		$old_a *= 360/(2*M_PI);
		$a *= 360/(2*M_PI);
		$start = 360-$a;
		$end = $start + abs($old_a-$a);
		
		$img->SetColor($this->color);
		$img->Arc($xc,$yc,$r*2,$r*2,$start,$end);
		$img->Line($xc,$yc,$x1,$y1);
		$img->Line($xc,$yc,$x2,$y2);
		
		$img->SetColor($color);
		$img->Fill($xf,$yf);
		
		$this->StrokeLabels($label,$img,$xc,$yc,$am,$r);					
	}
	
	// Position the labels of each slice
	function StrokeLabels($label,$img,$xc,$yc,$a,$r) {
		$img->SetFont($this->font_family,$this->font_style,$this->font_size);
		$img->SetColor($this->font_color);
		$img->SetTextAlign("left","top");
		$marg=6;
		$r += $img->GetFontHeight()/2;
		$xt=round($r*cos($a)+$xc);
		$yt=round($yc-$r*sin($a));

		// Position the axis title. 
		// dx, dy is the offset from the top left corner of the bounding box that sorrounds the text
		// that intersects with the extension of the corresponding axis. The code looks a little
		// bit messy but this is really the only way of having a reasonable position of the
		// axis titles.
		$h=$img->GetTextHeight($label);
		$w=$img->GetTextWidth($label);
		while( $a > 2*M_PI ) $a -= 2*M_PI;
		if( $a>=7*M_PI/4 || $a <= M_PI/4 ) $dx=0;
		if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dx=($a-M_PI/4)*2/M_PI; 
		if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dx=1;
		if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dx=(1-($a-M_PI*5/4)*2/M_PI);
		
		if( $a>=7*M_PI/4 ) $dy=(($a-M_PI)-3*M_PI/4)*2/M_PI;
		if( $a<=M_PI/4 ) $dy=(1-$a*2/M_PI);
		if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dy=1;
		if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dy=(1-($a-3*M_PI/4)*2/M_PI);
		if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dy=0;
		
		$img->StrokeText($xt-$dx*$w,$yt-$dy*$h,$label);		
	}	
} // Class



//===================================================
// CLASS PieGraph
// Description: 
//===================================================
class PieGraph extends Graph {
	var $posx, $posy, $radius;		
	var $legends=array();	
	var $plots=array();
//---------------
// CONSTRUCTOR
	function PieGraph($width=300,$height=200,$cachedName="",$timeout=0,$inline=1) {
		$this->Graph($width,$height,$cachedName,$timeout,$inline);
		$this->posx=$width/2;
		$this->posy=$height/2;
		$this->SetColor(array(255,255,255));		
	}

//---------------
// PUBLIC METHODS	
	function Add(&$pie) {
		$this->plots[] = $pie;
	}
	
	function SetColor($c) {
		$this->SetMarginColor($c);
	}

	// Method description
	function Stroke($aStrokeFileName="") {
		
		$this->StrokeFrame();		
		
		for($i=0; $i<count($this->plots); ++$i) 
			$this->plots[$i]->Stroke($this->img);
		
		foreach( $this->plots as $p)
			$p->Legend($this);	
		
		$this->legend->Stroke($this->img);
		$this->title->Center($this->img->left_margin,$this->img->width-$this->img->right_margin,5);
		$this->title->Stroke($this->img);		

		// Stroke texts
		if( $this->texts != null )
			foreach( $this->texts as $t) {
				$t->x *= $this->img->width;
				$t->y *= $this->img->height;
				$t->Stroke($this->img);
			}
		
		if ($this->showcsim) {
			foreach($this->plots as $p ) {
				$csim.= $p->GetCSIMareas();
			}
			//$csim.= $this->legend->GetCSIMareas();
			if (preg_match_all("/area shape=\"(\w+)\" coords=\"([0-9\, ]+)\"/", $csim, $coords)) {
				$this->img->SetColor($this->csimcolor);
				for ($i=0; $i<count($coords[0]); $i++) {
					if ($coords[1][$i]=="poly") {
						preg_match_all('/\s*([0-9]+)\s*,\s*([0-9]+)\s*,*/',$coords[2][$i],$pts);
						$this->img->SetStartPoint($pts[1][count($pts[0])-1],$pts[2][count($pts[0])-1]);
						for ($j=0; $j<count($pts[0]); $j++) {
							$this->img->LineTo($pts[1][$j],$pts[2][$j]);
						}
					} else if ($coords[1][$i]=="rect") {
						$pts = preg_split('/,/', $coords[2][$i]);
						$this->img->SetStartPoint($pts[0],$pts[1]);
						$this->img->LineTo($pts[2],$pts[1]);
						$this->img->LineTo($pts[2],$pts[3]);
						$this->img->LineTo($pts[0],$pts[3]);
						$this->img->LineTo($pts[0],$pts[1]);
						
					}
				}
			}
		}
		
		// Finally output the image
		$this->cache->PutAndStream($this->img,$this->cache_name,$this->inline,$aStrokeFileName);					
	}
} // Class

/* EOF */
?>
