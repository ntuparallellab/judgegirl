<?php
include("config.php");

session_start();

if(!isset($_SESSION["ID"]))
    Header("Location: index.php");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$userid = $_SESSION["SU"] ? $_REQUEST["u"] : $_SESSION["ID"];
if(!preg_match('/^\w*$/', $userid))
    exit("Invalid username.");
$volume = $_REQUEST["v"];
if(!preg_match('/^\w+$/', $volume))
    exit("Invalid volume name.");
$number = $_REQUEST["n"];
if(!preg_match('/^\d+$/', $number)){
    if(!$_SESSION['SU'])
        exit("Invalid problem number.");
    else
        unset($number);
}
?>
<?php
$queryv = "SELECT * from volumes where name = '$volume'";
$queryp = "SELECT * from problems where volume = '$volume'";
if(isset($number))
    $queryp.= " and number = $number";
if(!$_SESSION['SU']){
    $queryv .= " and available = 1";
    $queryp .= " and available = 1";
}
$result = mysql_query($queryv);
if(mysql_num_rows($result) == 0)
    exit('Volume not available/exist');
$result = mysql_query($queryp);
if(mysql_num_rows($result) == 0)
    exit("Problem number $number not available/exist");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo $StrCourseName; ?> Judge Status</title>
<style type="text/css">
td{
    padding-left: 8px;
    padding-right: 8px;
}
table#main_tb > tbody > tr > td:nth-child(1) > a { text-decoration:none; color:black ; }
table#main_tb > tbody > tr > td:nth-child(5) { width: 20em ; }
table#main_tb > tbody > tr > td:nth-child(8) { width: 6em ; }
</style>
</head>
<body background="images/back.gif">
<div id="content">
<h2><?php echo $StrCourseName; ?> Judge Status</h2>
<?php
include ("menu.php");
if(isset($number))
    echo "<a class=\"ext menu\" href=\"submission.php?v=$volume&n=$number\">Submission page</a>";
include ("announce.php");
?>
<hr>
<center>
<table cellpadding="5px">
<tr align="center">
<td>
    <h2>Submission list for <?php echo $userid ? "user $userid" : "all users"; ?></h2>
<?php
if($_SESSION['SU']){
?>
<form method='GET' action='list.php'>
    <input type='hidden' name='v' value='<?php echo $volume; ?>'>
    <input type='hidden' name='n' value='<?php echo $number; ?>'>
    List for user: <input type='text' name='u' size='16' maxlength='16'>
    <input type='submit' value='Submit'>
</form>
<?php
}
?>
</td>
<td width="200px">
    <img src="images/new_logo_small.jpg">
</td>
</tr>
<tr align="center" valign="top">
<td>
    <table id=main_tb border="2px" cellspacing="0px" cellpadding="2px" width="600">
    <thead align="center">
	<tr> 
<?php
	    if( $_SESSION["SU"] ){
		echo "<td>User</td>\n";
	    }
?>
	    <td>Problem Number</td>
	    <td>Trial Number</td>
	    <td>Score</td>
	    <td>Received Time</td>
            <td>Log File</td> 
            <td>Program Listing</td>
<?php
	    //if( $_SESSION["SU"] ){
		echo "<td>Comment</td>\n";
	    //}
?>
        </tr>
    </thead>
    <tbody align="center">
<?php
#$query = "SELECT count(*) FROM $volume WHERE score IS NULL";
#$result = mysql_query($query);
#$wait_count = mysql_result($result, 0);

$query = "SELECT user, number, trial, time, score, valid, comment FROM $volume ".(isset($number)?"WHERE number = $number":"")." ORDER BY time DESC, user";
$result = mysql_query($query);

for($i = 0; $i < mysql_num_rows($result); $i++){
    list($user, $num, $trial, $time, $score, $valid, $comment) = mysql_fetch_row($result);
    #if( is_null($score) )
	#--$wait_count;

    if( ($_SESSION['SU'] && $userid == "") || $userid == $user ){
	echo "<tr>";
	if( $_SESSION['SU'] ){
        $query = "select name from users where user = '$user'";
        $chinese_name = mysql_query($query);
        if(mysql_num_rows($chinese_name) > 0)
            $chinese_name = mysql_result($chinese_name, 0, 0);
        else
            $chinese_name = '';
        echo "<td>";
        if($userid == "")
            echo "<a href='list.php?v=$volume&n=$num&u=$user'>";
        echo "$user<br>$chinese_name";
        if($userid == "")
            echo "</a>";
        echo "</td>";
    }
	echo "<td>$num</td>";
	echo "<td>$trial</td>";
	if( is_null($score) ){
        $wait_count = mysql_result(mysql_query("select count(*) from $volume where score is NULL and time < '$time'"), 0, 0);
	    if( $wait_count == 0 )
		echo "<td>Judging...</td>";
	    else
		echo "<td>Waiting for $wait_count ".($wait_count > 1 ? "submissions":"submission")."...</td>";
	}else
	    echo "<td>".($valid ? $score : "<font color='red'>$score (INVALID)</font>")."</td>";
	echo "<td>$time</td>";
	if ( is_null($score) )
	    echo "<td>N/A</td>";
	else
	    echo "<td><a href='log.php?v=$volume&n=$num&u=$user&t=$trial'>detail</a></td>"; 
	echo "<td><a href='program.php?v=$volume&n=$num&u=$user&t=$trial'>program</a></td>";
    if(strcmp($comment, "Red Card") == 0)
        echo "<td><img src=\"images/red_card.png\"></td>";
    else if(strcmp($comment, "Yellow Card") == 0)
        echo "<td><img src=\"images/yellow_card.png\"></td>";
    else
    	echo "<td>" . ($comment ? $comment : "&nbsp") . "</td>";
	echo "</tr>\n";
    }
}
?>
    </tbody>
    </table>  
</td>
<td>
<?php
if(isset($number)){
echo"    <h3>Histogram</h3>
    <table cellspacing=0 cellpadding=5 border=1>
	<tr align=center>
	    <th>Score</th>
	    <th>Number<br>Of<br>Students</th>
	</tr>";
    $query = "SELECT COUNT(*) FROM users WHERE class = 'user'";
    $result = mysql_query($query);
    $total = mysql_result($result, 0, 0) - count($BlackList);
    $sleeping = $total;
    
    $query = "SELECT score, COUNT(score) FROM
    	    (SELECT MAX(V.score) AS score FROM $volume V, users U
    		WHERE V.user = U.user AND U.class = 'user' AND V.number = $number AND V.valid = TRUE
    		GROUP BY V.user
    	    ) as HS GROUP BY score DESC";
    $result = mysql_query($query);
    
    for($i = 0; $i < mysql_num_rows($result); $i++){
        list($score, $count) = mysql_fetch_row($result);
        $sleeping -= $count;
    
        echo "<tr align='right'>";
        echo "<td>$score</td>";
        if( $_SESSION['SU'] )
    	echo "<td><a href='scorelist.php?v=$volume&n=$number&s=$score'>$count</a></td>";
        else
    	echo "<td>$count</td>";
        echo "</tr>\n";
    }
    
    echo "<tr>";
    echo "<td align='center'>Sleeping</td>";
    if( $_SESSION['SU'] )
        echo "<td align='right'><a href='sleepinglist.php?v=$volume&n=$number'>$sleeping</a></td>";
    else
        echo "<td align='right'>$sleeping</td>";
    echo "</tr>\n";
    echo "<tr>";
    echo "<td align='center'>Total</td>";
    echo "<td align='right'>$total</td>";
    echo "</tr>\n";
echo"    </table>";
}
?>
</td>
</table>
</center>
<br>
<?php
print "Current time is " . date("Y-m-d H:i:s");  
?>
<hr>
<h2><strong>Submit Program</h2>
<a href="submission.php?<?php echo "v=$volume&n=$number"; ?>">Press here</a>
<hr>
<?php include("footnote.php"); ?>
</div>
<?php
$volume = $ProblemVolume;
include("balloon.php");
?>
</body>
</html>
