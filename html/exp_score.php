<?php
//exit("Under Construction");
include("config.php");
session_start();
if (!isset($_SESSION["ID"]))
    exit("Please <a href=\"index.php\">login</a> first.\n");
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<!--meta http-equiv=refresh content=60-->
<title><?php echo $StrCourseName; ?> Expected Final Grade</title>
<style>
table > tbody > tr > td:nth-child(1) > a { text-decoration:none; color:black ; }
</style>
</head>

<body background="images/back.gif"><!-- onload=LocateHistogram() onscroll=document.getElementById("histogram_board").style.visibility="hidden";HistogramBoardWait=5>-->
<?php
if(!(mysql_connect($MYSQLhost, $MySQLuser, $MySQLpass))) {
    echo("connect failed<BR>");
    exit;
}

if (!(mysql_select_db($MySQLdatabase))) {
    echo("select failed<BR>");
    exit;
}

if($_SESSION['SU']){
    if($_REQUEST['u'] !== NULL)
	$display_user = $_REQUEST['u'];
    else
	$display_user = NULL;
}else
    $display_user = $_SESSION['ID'];

/*
if (!$_SESSION['SU']){
    echo("Access denial.<BR>");
    exit;
}
*/

$volcalw = array(
	'2011course,2011hw' => 0.4,
	'2011mid1'          => 0.2,
    '2011mid2'          => 0.2,
    '2011final'         => 0.2
    );
$mixhigh = array(
		//'mid2 5' => 'fixed 1',
	);

$keys_of_mixhigh = array_keys($mixhigh);
unset($Vols);
foreach($volcalw as $vs => $w)
    foreach((explode("|", $vs)) as $v)
    	foreach((explode(",", $v)) as $u)
	$Vols[] = $u;

unset($VolUserScore);
unset($VolNumMScore);
unset($VolDead);
foreach($Vols as $v){
    $VolDead[$v] = deadgrp($v);
    $VolNumMScore[$v] = num_maxs($v);
    $VolNumMScore['2011midhand'][1] = 30;
    $VolUserScore[$v] = user_score($v, $VolNumMScore[$v], $VolDead[$v]);
}

unset($Userlist);
$query = "SELECT DISTINCT user FROM users WHERE class = 'user' ORDER BY user";
$result = mysql_query($query);
while($row = mysql_fetch_array($result))
    $Userlist[] = $row["user"];

unset($VolTitle);
$query = "SELECT name, title from volumes";
$result = mysql_query($query);
while($row = mysql_fetch_array($result))
    $VolTitle[$row['name']] = $row['title'];

?>

<h2><?php echo $StrCourseName; ?> Expected Final Grade</h2>
<?php include ("menu.php"); include ("announce.php"); ?>
<hr>
<a href="exp_calc.html">分數計算方式說明 Scoring Details</a>
<center>
<h2>Expected Final Grade</h2>
</center>

<div align="center"><center>      
    <table style='text-align:center' border="2" cellspacing="0" cellpadding="2" width="700">
    <tbody>
    <tr align="center"> 
        <th width="15%">User</th>
        <!--th width="15%">Name</th-->
<?php
foreach($Vols as $v)
    echo "<th width=\"15%\">" . $VolTitle[$v] . "</th>\n";
echo "<th>Accum</th><th>Expected</th><th>Rank</th>\n";

/* 真強者名單 */
$specials = array(
);

?>
    </tr>
<?php

unset($VolSum);
unset($scoreFreq);
//unset($Alertlist);
unset($exp_list);
unset($pro_list);
$exp_sum = 0; $accu_sum=0; $gpa_sum=0;
$n_user = 0;
foreach($Userlist as $u){
    $str = "<tr>\n";
    $str.= "<td bgcolor=FFFFAA>";
	if($_SESSION['SU'] && $display_user === NULL )
		$str.= "<a href='?u=$u'>".strtoupper($u)."</a>";
	else
		$str.= $u;
    $str.= "</td>\n";
    /*
    $query = "select name from users where user = '$u'";
    $chinese_name = mysql_query($query);
    $chinese_name = mysql_result($chinese_name, 0, 0);
    $str.= "<td bgcolor=88FF88>$chinese_name</td>\n";
    */
    $exp=0; $exp_w = 0;
	$accu=0;
	foreach($volcalw as $vs => $w){
		$segavg = 0;
		$segw = 0;
		if(strstr($vs, "|") !== FALSE){
		    $dead_count = 0;
		    foreach((explode("|", $vs)) as $v){
			$str.= "<td>".round($VolUserScore[$v][$u], 2)."</td>\n";
			$segavg += ($VolUserScore[$v][$u]) * count($VolDead[$v]);
			$dead_count += count($VolDead[$v]);
			if(!in_array($u, $BlackList))
			    $VolSum[$v] += $VolUserScore[$v][$u];
		    }
		    $segavg /= $dead_count;
		    $exp += $segavg * $w;
			/*
			if($vs == '2011mid' || $vs == '2011midhand')
				$accu += $segavg * $w / 2;
			else
			*/
				$accu += $segavg * $w ;
		} else {
		    foreach((explode(",", $vs)) as $v){
			$str.= "<td>".round($VolUserScore[$v][$u], 2)."</td>\n";
			$segavg += $VolUserScore[$v][$u];
			if(!in_array($u, $BlackList))
			    $VolSum[$v] += $VolUserScore[$v][$u];
		    }
		    $segavg /= count(explode(",", $vs));
		    if($vs == 'mid1,mid2,final' &&
			    round(
				($VolUserScore['mid1'][$u]+$VolUserScore['mid2'][$u])/2,
				2
				) == 100 && !in_array($u, $specials))
			$pro_list[] = $u;
		    $exp += $segavg * $w;
# fix $accu			
#			if($vs == '2011mid1' || $vs == '2011mid2')
#				$accu += $segavg * $w * 2 / 3;
#			else
			
				$accu += $segavg * $w ;
		}
		$exp_w += $w;
	}
	$str.= "<td>".round($accu,2)."</td>";
    $exp /= $exp_w ;

	if(in_array($u, $specials))
		$exp = 100;

	$is_under = $u[0] == 'b';
	$is_pass  =  $is_under?$exp > 59:$exp > 69;

	$td_style = "<td bgcolor=";
    if($is_pass){
		if(in_array($u,$specials))
			$td_style.= "FFFFFF>";
		else
			$td_style.= "CCAAAA>";
	}
    else{
		$td_style.= "446666>";
		//$Alertlist[] = "$u, ". round($exp,0);
	}
	$str.=$td_style;
    
    //$exp_int = round($exp, 0);
    $exp_int = ceil($exp);
	$exp_list[] = $exp_int;
    $str.= $exp_int."</td>";

	$str.= $td_style;
	if(!$is_under && !$is_pass)
		$str.= "C</td>";
	else
		$str.= rank($exp_int)."</td>";

	if(!in_array($u, $BlackList)){
		$accu_sum += $accu;
		$exp_sum += $exp_int;
		$gpa_sum += gpa($exp_int);
		$n_user ++;
		if($scoreFreq[$exp_int] === NULL)
			$scoreFreq[$exp_int] = 1;
		else
			$scoreFreq[$exp_int]++;
	}
    $str.= "</tr>\n";
    if($display_user === NULL || $display_user == $u)
		if(!in_array($u, $BlackList))
			echo $str;
}
echo "<tr>\n";
echo "<td bgcolor=FFFFAA>Average</td>\n";
foreach($Vols as $v)
    echo "<td bgcolor=AABBAA>" . round($VolSum[$v] / $n_user, 2) . "</td>\n";
echo "<td>" . round( $accu_sum / $n_user, 2) . "</td>\n";
echo "<td>" . round( $exp_sum / $n_user, 2) . "</td>\n";
echo "<td>" . round( $gpa_sum / $n_user, 2) . "</td>\n";
echo "</tr>\n";
?>
        </tbody>
    </table>  
<?php
if($display_user !== NULL){
    echo "<h3>Detailed score for $display_user</h3>\n";
    unset($UVolNumScore);
    foreach($Vols as $v)
	$UVolNumScore[$v] = num_score($display_user, $v);
    $max_n = 0;
    foreach($Vols as $v)
	foreach($VolNumMScore[$v] as $n => $s)
	if($max_n < $n)
	    $max_n = $n;

    echo "<table style='text-align:center;' border=2 cellspacing=0 cellpadding=2 width=700>";
    echo "<tr><th rowspan=2 width=5%>Number</th>";
    foreach($Vols as $v)
	echo "<th colspan=2 width=15%>" . $VolTitle[$v] . "</th>";
    echo "</tr>\n";
    echo "<tr>";
    foreach($Vols as $v)
	echo "<td>raw</td><td>score</td>";
    echo "</tr>\n";

    unset($UVolAvg);
    unset($VolSeg_iidx);
    foreach($Vols as $v) //      i idx
	$VolSeg_iidx[$v] = array(1, 0);
    for($i=1; $i<=$max_n; $i++){
	echo "<tr><td>".$i."</td>";
	foreach($Vols as $v){
	    if($UVolNumScore[$v][$i] !== NULL)
		echo "<td>".$UVolNumScore[$v][$i]."/".$VolNumMScore[$v][$i]."</td>";
	    else if($VolNumMScore[$v][$i] !== NULL)
		echo "<td>--/".$VolNumMScore[$v][$i]."</td>";
	    else{
		echo "<td></td><td></td>";
		$VolSeg_iidx[$v][0]++;
	    }
	    //echo "<td>&nbsp;</td>";
	    if($VolSeg_iidx[$v][0] == $i){
		$rspn = 0;
		$segsum = 0; 
		foreach($VolDead[$v][$VolSeg_iidx[$v][1]] as $pnum){
		    $rspn ++;
		    $segsum += $UVolNumScore[$v][$pnum] * 100 / $VolNumMScore[$v][$pnum];
		}
		$UVolAvg[$v] += $segsum / $rspn ;
		echo "<td rowspan=$rspn>". round($segsum / $rspn, 2) ."</td>";
		$VolSeg_iidx[$v][0] += $rspn;
		$VolSeg_iidx[$v][1] ++;
	    }
	}
	echo "</tr>";
    }
    echo "<tr><td>Vol Avg.</td>";
    unset($VsAvg);
    unset($VsWeight);
    foreach($volcalw as $vs => $w){
	$VsAvg[$vs] = 0;
	if(strstr($vs, "|")){
	    $dead_count = 0;
	    foreach(explode("|", $vs) as $v){
		$UVolAvg[$v] /= count($VolDead[$v]);
		echo "<td colspan=2>" . round( $UVolAvg[$v], 2 ) . "</td>";
		$VsAvg[$vs] += $UVolAvg[$v] * count($VolDead[$v]);
		$dead_count += count($VolDead[$v]);
	    }
	    $VsAvg[$vs] /= $dead_count;
	}else{
	    foreach(explode(",", $vs) as $v){
		$UVolAvg[$v] /= count($VolDead[$v]);
		echo "<td colspan=2>" . round( $UVolAvg[$v], 2 ) . "</td>";
		$VsAvg[$vs] += $UVolAvg[$v];
	    }
	}
    }
    echo "</tr>\n";
    echo "<tr><td>Type Avg.</td>";
    $exp = 0; $accu = 0;
    $sum_w = 0;
    foreach($volcalw as $vs => $w){
	if(strstr($vs, "|")){
	    $cspn = count(explode("|", $vs));
	    $cspn *= 2;
	    echo "<td colspan=$cspn>" . round( $VsAvg[$vs], 2 ) . "</td>";
	}else{
	    $cspn = count(explode(",", $vs));
	    $VsAvg[$vs] /= $cspn;
	    $cspn *= 2;
	    echo "<td colspan=$cspn>" . round( $VsAvg[$vs], 2 ) . "</td>";
	}
	$exp += $w * $VsAvg[$vs];
	
#	if($vs == '2011mid1' || $vs == '2011mid2')
#	    $accu += $w * $VsAvg[$vs] * 2 / 3;
#	else
	
	    $accu += $w * $VsAvg[$vs];
	$sum_w += $w;
    }
    echo "</tr>\n";
    $cspn = 2 * count($Vols);
    $exp /= $sum_w ;
    echo "<tr><td>Accum</td><td colspan=$cspn>". round($accu,2). "</td></tr>\n";
    echo "<tr><td>Expec</td><td colspan=$cspn>". round($exp,2). "</td></tr>\n";
    echo "</table>\n";
}
?>
<hr>
<h2>Statistics Diagram</h2>
<script src="diagram.js"></script>
<table>
<tr>
<td><div id=diagram_no1 style="width:750px; height:300px; background-image: url('images/judgegirl_logo_bg.gif'); background-position: center center; background-repeat: no-repeat;"></div></td>
<td>
<table style='text-align:center' border="2" cellspacing="0" cellpadding="2">
<tr><th>Rank</th><th>Count</th></tr>
<?php
	$Ranklist = array ("A+", "A", "A-", "B+", "B", "B-", "C+", "C", "C-", "F"); 
	unset($RankCnt);
	foreach($scoreFreq as $s => $c){
		$r = rank($s);
		if($RankCnt[$r] == NULL)
			$RankCnt[rank($s)] = $c;
		else
			$RankCnt[rank($s)] += $c;
	}
	foreach($Ranklist as $r){
		echo "<tr><td>$r</td><td>";
		if($RankCnt[$r] == NULL)
			echo 0;
		else
			echo $RankCnt[$r];
		echo "</td></tr>\n";
	}
?>
</table>
</td>
</tr>
</table>
<script type="text/javascript">
<?php
    echo "var freq = new Array(";
    for($i = 0; $i < 100; $i++)
	if($scoreFreq[$i] === NULL)
	    echo "0, ";
        else
	    echo "$scoreFreq[$i], ";
    if($scoreFreq[100] === NULL)
        echo 0;
    else
        echo $scoreFreq[100];
    echo ");\n";
    echo "draw_diagram(freq, 'diagram_no1');";
?>
</script>
<?php
if($_SESSION['SU']){
	//echo "<h3>Alert list</h3>\n";
	//echo implode("<br \>\n", $Alertlist);
	/*
	echo "<h3>Exp list</h3>\n";
	echo implode(" ", $exp_list);
	echo "<h3>職業組名單</h3>\n";
	if(defined($pro_list)){
		echo count($pro_list)."<br />\n";
		echo implode("<br />\n", $pro_list);
		if(in_array($display_user, $pro_list))
			echo "<br />\n$display_user is in Prolist<br />";
	}
	echo "<br />users = $n_user<br />";
	echo "<h3>真強者名單</h3>\n";
	if(count($specials) > 0 )
		echo implode("<br />\n", $specials);
	*/
}
?>
</center></div>  
<br>
<?php echo "Current time is " . date("Y-m-d H:i:s")  ?>
<hr>
<?php include("footnote.php"); ?>
</body></html>

<?php
function num_maxs($volume){
    unset($maxscore);
    $query = "select max(score) as score, number from $volume where number in (
	    SELECT number FROM problems WHERE available = 1 AND volume = '$volume'
	) group by number";
    $result = mysql_query($query);
    while($row = mysql_fetch_array($result))
        $maxscore[$row["number"]] = $row["score"];
    return $maxscore;
}
function deadgrp($volume){
    unset($deadlinegroup);
    unset($retgroup);
    $query = "select number, deadline from problems where available = 1 and volume = '$volume' order by deadline";
    $result = mysql_query($query);
    while($row = mysql_fetch_array($result))
        $deadlinegroup[$row['deadline']][] = $row['number'];
    foreach($deadlinegroup as $ary)
	$retgroup[] = $ary;
    return $retgroup;
}
function user_score($volume, $maxscore, $deadlinegroup) {
    // return volume scores evenly weighted by deadline group from each user
    // score from 0 to 100
	global $mixhigh, $keys_of_mixhigh;

    $query = "select max(score) as score, number, user from $volume
	where valid = 1 and number in (
	    select number from problems where available = 1 and volume = '$volume'
	) group by number, user";
    $result = mysql_query($query);
    while($row = mysql_fetch_array($result)){
		$fixedscore = NULL;
		if( in_array($volume." ".$row['number'], $keys_of_mixhigh) ){
			list($fixvol, $fixnum) = explode(' ', $mixhigh[$volume." ".$row['number']], 2);
			$query = "select max(score) as score from $fixvol
						where user = '".$row['user']."' and valid = 1 and number = $fixnum and number in (
							select number from problems where available = 1 and volume = '$fixvol'
						) ";
			$fixedscore = mysql_query($query);
			$fixedscore = mysql_result($fixedscore, 0, 0);
		}
		if($fixedscore !== NULL && $fixedscore > $row['score'])
			$row['score'] = $fixedscore;
        $userscore[$row["user"]][$row["number"]] = $row["score"] * 100 / $maxscore[$row["number"]];
	}

    unset($retscore);
    foreach($userscore as $user => $num_sc_ary){
	$retscore[$user] = 0;
	foreach($deadlinegroup as $prob_ary){
	    $avg = 0;
	    foreach($prob_ary as $i)
		$avg += $num_sc_ary[$i];
	    $avg /= count($prob_ary);
	    $retscore[$user] += $avg;
	}
	$retscore[$user] /= count($deadlinegroup);
    }

    return $retscore;
}
function num_score($user, $volume){
	global $mixhigh, $keys_of_mixhigh;
	unset($retary);

	$query = "SELECT MAX(score) AS score, number FROM $volume
		WHERE valid = 1 AND user = '$user' AND number IN (
				SELECT number FROM problems WHERE available = 1 AND volume = '$volume'
				) GROUP BY number ORDER BY number";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$fixedscore = NULL;
		if( in_array($volume." ".$row['number'], $keys_of_mixhigh) ){
			list($fixvol, $fixnum) = explode(' ', $mixhigh[$volume." ".$row['number']], 2);
			$query = "select max(score) as score from $fixvol
						where user = '$user' and valid = 1 and number = $fixnum and number in (
							select number from problems where available = 1 and volume = '$fixvol'
						) ";
			$fixedscore = mysql_query($query);
			$fixedscore = mysql_result($fixedscore, 0, 0);
		}
		if($fixedscore !== NULL && $fixedscore > $row['score'])
			$row['score'] = $fixedscore;
		$retary[$row['number']] = $row['score'];
	}

	return $retary;
}
function rank($exp_int){
	if($exp_int >= 90) return "A+";
	if($exp_int >= 85) return "A";
	if($exp_int >= 80) return "A-";
	if($exp_int >= 77) return "B+";
	if($exp_int >= 73) return "B";
	if($exp_int >= 70) return "B-";
	if($exp_int >= 67) return "C+";
	if($exp_int >= 63) return "C";
	if($exp_int >= 60) return "C-";
	return "F";
}
function gpa($exp_int){
	if($exp_int >= 90) return 4.3;
	if($exp_int >= 85) return 4.0;
	if($exp_int >= 80) return 3.7;
	if($exp_int >= 77) return 3.3;
	if($exp_int >= 73) return 3.0;
	if($exp_int >= 70) return 2.7;
	if($exp_int >= 67) return 2.3;
	if($exp_int >= 63) return 2.0;
	if($exp_int >= 60) return 1.7;
	return 0;
}

?>
