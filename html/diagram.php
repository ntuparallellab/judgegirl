<?php
include("config.php");
$fp = fopen('diagram.dat', 'r');
$tmp = fscanf($fp, "%d");
$lasttime = $tmp[0];
$tmp = fscanf($fp, "%d");
$num = $tmp[0];
for($i=0;$i<$num;$i++){
	$tmp = fscanf($fp, "%d");
	$data[$i] = $tmp[0];
}
fclose($fp);

$curtime = time();	
if (($lasttime-$curtime)>10 || $lasttime == -1){
	//		$fp = fopen('diagram.dat', 'w');
	//		fprintf($fp, "%d\n",$curtime);
	//		fprintf($fp, "$d\n",$lasti);

	if(!(mysql_connect($MYSQLhost, $MySQLuser, $MySQLpass))) {
		echo("connect failed<BR>");
		exit;
	}

	if (!(mysql_select_db($MySQLdatabase))) {
		echo("select failed<BR>");
		exit;
	}

	$query = "select time from $ProblemVolume where number = $ProblemNumber order by time ";
	$result = mysql_query($query);
	$oridata[0] = strtotime(mysql_result($result,0));
	$min = $oridata[0];
	$cur = $min+60*60;
	$curidx = 0;
	$data[0] = 1;
	for($i=1;$i<mysql_num_rows($result);$i++){
		$oridata[$i] = strtotime(mysql_result($result,$i));
		while( $oridata[$i] > $cur){
			$cur+=60*60;
			$curidx+=1;
			$data[$curidx]=0;
		}
		$data[$curidx]+=1;		
	}
}
draw_chart("Submittions", $data, 7 ,1);

function draw_chart($title,$data, $colspan, $rowspan){
	$max=$data[0];
	for($i=0;$i<count($data);$i++){
		if($max < $data[$i]) 
			$max = $data[$i];
	}
	$max+=1;
	print ("<table border=0 bgcolor=#8888cc><tir><td><b><font size=2>$title</font><tr><td>");
	print ("<table border=0 cellSpacing=0 cellPadding=0>");


	if ($max%$rowspan==0){
		print ('<tr><td rowspan="'.$rowspan.'"><font size=2>');
		print ($max);
		print ('</font></td>');		
	}else{
		print ('<tr><td rowspan="'.$max%$rowspan.'"><font size=2>');
		print ($max);
		print ('</font></td>');		
	}
	for($x=0;$x<count($data);$x++){
		print ("<td rowspan=\"");
		print  $max-$data[$x];
		print "\" BGCOLOR=\"#8888cc\"></td>";
	}
	print ('</tr>');

	for($y=$max-1;$y>0;$y--){

		print('<tr>');
		if ($y%$rowspan==0){
			print ('<td rowspan ="' .$rowspan .'"><font size=2>');
			print $y;
			print ('</font></td>');
		}

		for($x=0;$x<count($data);$x++){
			if ($data[$x]==$y){
				print "<td rowspan=\"";
				print $y;
				print "\"BGCOLOR=\"#FF0000\"></td>";		
			}
		}
		print ('</tr>');
	}
	print ('<tr><td></td>');


	for($x=0;$x<count($data)-$colspan;$x+=$colspan){
		print ('<td COLSPAN="'. $colspan .'"><pre><font size=2>');
		printf ( "% -6d", $x);
		print ('</font></pre></td>');
	}
	print ('<td COLSPAN="'. (count($data) - $x) .'"><pre><font size=1>');
	printf ( "% -d", $x);
	print ('</font></pre></td>');

	print ('</tr>');

	print ('</table></table>');
}

