<?php
include("config.php");
session_start();

if (!isset($_SESSION["ID"]))
    exit("Please <a href=\"index.php\">login</a> first.\n");
$PageName = 'Duplication Check';

if(!(mysql_connect($MYSQLhost, $MySQLuser, $MySQLpass))) {
    echo("connect failed<BR>");
    exit;
}

if (!(mysql_select_db($MySQLdatabase))) {
    echo("select failed<BR>");
    exit;
}

if (!$_SESSION['SU']){
    echo("Access denial.<BR>");
    exit;
}


$volume = $_REQUEST["v"];

if( $volume && $volume != ''){
    $queryv = "SELECT name, title from volumes where name = '$volume'";
    $result = mysql_query($queryv);
    if(mysql_num_rows($result) == 0)
	exit('Volume not available/exist');
    else
        list($name, $voltitle) = mysql_fetch_row($result);
    if ($_REQUEST['action'] != NULL){
        $userid = $_REQUEST["u"];
        if(!preg_match('/^\w+$/', $userid))
            exit("Invalid username.");
        $number = $_REQUEST["n"];
        if(!preg_match('/^\d+$/', $number))
            exit("Invalid problem number.");
        $trial = $_REQUEST["t"];
        if(!preg_match('/^\d+$/', $trial))
            exit("Invalid trial number.");
        $vv = -1;
        if($_REQUEST['action'] == 'dis'){
            $vv = 0;
        }else if($_REQUEST['action'] == 'ena'){
            $vv = 1;
        }
        if($vv != -1){
            $query = "UPDATE $volume set valid = $vv where user='$userid' and number = $number and trial = $trial";
            mysql_query($query);
        }
        header("Location: cheat_examine.php?v=$volume");
    }
}

$_SERVER['PHP_SELF'];

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo "$StrCourseName $PageName"; ?></title>
<style type='text/css'>
<!--
table > * > tr > td:nth-child(2) { display:none; }
tbody > tr > td:nth-child(3) > a { text-decoration:none; color:black ; }
tbody > tr > td:nth-child(5) > a { text-decoration:none; color:black; }
tbody > tr > td.inv:nth-child(5) > a { color:red; }
tr.alt_row { background-color:yellow; }
-->
</style>
</head>

<body background="images/back.gif"><!-- onload=LocateHistogram() onscroll=document.getElementById("histogram_board").style.visibility="hidden";HistogramBoardWait=5>-->

<h2><?php echo "$StrCourseName $PageName"; ?></h2>
<?php include ("menu.php"); include ("announce.php"); ?>
<hr>
<?php
if( $volume == NULL or $volume == ''){
    echo "<center>\n"; 
    $queryv = "SELECT name, title from volumes order by number";
    $result = mysql_query($queryv);
    for($i = 0; $i < mysql_num_rows($result); $i++){
        list($name, $title) = mysql_fetch_row($result);
        echo "<a href='?v=$name'>$title</a><br />";
    }
    echo "</center><hr>";
    include("footnote.php");
    exit;
}
?>
<center>
<h2><?php echo "$PageName for $voltitle"; ?> (<a href="?">back</a>)</h2>
</center>
    <table align="center" border="2px" cellspacing="0px" cellpadding="2px" width="600">
    <thead align="center">
	<tr> 
	    <td>Number</td>
	    <td>Exec_md5</td>
	    <td>User</td>
	    <td>Trial Number</td>
        <td>Time</td>
	    <td>Score</td>
        <td>Program Listing</td>
        </tr>
    </thead>
    <tbody align="center">
<?php

#$query = "SELECT user, trial, time, score, valid, comment FROM $volume WHERE number = $number ORDER BY time DESC";
$query = "select distinct temp2.exec_md5, $volume.number, $volume.user, $volume.trial, $volume.score, $volume.valid, $volume.time from (select exec_md5, count(*) as count  from  ( select distinct user, exec_md5 from $volume ) temp group by exec_md5 having count > 1 and exec_md5 != '' ) temp2, (select distinct user, number, exec_md5 from $volume ) temp3 , $volume where temp2.exec_md5 = temp3.exec_md5 and temp3.user = $volume.user and temp2.exec_md5 = $volume.exec_md5 order by number, exec_md5, user, trial ";
$result = mysql_query($query);
#$alt_str = "style='background-color:yellow'";
$alt_str = "class=alt_row";
$pre_md5 = ''; $alt_cnt = 1;

for($i = 0; $i < mysql_num_rows($result); $i++){
    list($exec_md5, $number, $user, $trial, $score, $valid, $time) = mysql_fetch_row($result);
	if( $pre_md5 != $exec_md5 ){
	    $alt_cnt = ($alt_cnt + 1)%2 ;
	    $pre_md5 = $exec_md5 ;
	}
	echo "<tr " .( $alt_cnt?$alt_str:"" ). ">";
	echo "<td>$number</td>";
	echo "<td>$exec_md5</td>";
	echo "<td><a href='list.php?v=$volume&n=$number&u=$user'>$user</a></td>";
	echo "<td>$trial</td>";
    echo "<td>$time</td>";
        echo "<td".($valid?"":" class=inv").">";
        echo "    <a href='?action=".($valid?"dis":"ena")."&v=$volume&u=$user&n=$number&t=$trial'>$score</a>";
        echo "</td>";
	echo "<td><a href='program.php?v=$volume&n=$number&u=$user&t=$trial'>program</a></td>";
	echo "</tr>\n";
}
?>
    </tbody>
    </table>  

<br>
<?php echo "Current time is " . date("Y-m-d H:i:s")  ?>
<hr>
<?php include("footnote.php"); ?>
</body></html>

