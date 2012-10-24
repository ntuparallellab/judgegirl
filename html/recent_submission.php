<?php
include("config.php");

session_start();

if(!$_SESSION["SU"])
    exit("Permission denied.");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if (!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$userid = $_REQUEST["u"];
if(!preg_match('/^\w*$/', $userid))
    exit("Invalid username.");
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo $StrCourseName; ?> Submission Status</title>
</head>

<body background="images/back.gif">

<h2><?php echo $StrCourseName; ?> Submission Status</h2>
<?php include("menu.php"); include ("announce.php"); ?>
<hr>
<?php 
$query2 = "select name from volumes";
$result2 = mysql_query($query2);
$query = "select user, score, valid, volume, number, trial, time, comment from (";
$count = 200;
for($i = 0; $i < mysql_num_rows($result2); $i++){
    $table = mysql_fetch_row($result2);
    if($i > 0) $query .= " union ";
    $query .= "(select user, score, valid, \"".$table[0]."\" as volume, number, trial, time, comment from $table[0] ";
    if($userid) $query .= "where user = '$userid'";
    $query .= "order by time desc limit $count)";
}
$query .= ") as full order by time desc limit $count";
#$query .= ") as full order by time desc";

?>
<center><h2>Recent Submissions<img style=vertical-align:middle src=images/new_logo_small.jpg></h2>
<form method='GET' action='recent_submission.php'>
    List for user: <input type='text' name='u' size='16' maxlength='16'>
    <input type='submit' value='Submit'>
</form>
</center>  

<div align="center"><center>      
    <table><tr valign=top><td>
    <table border="2px" cellspacing="0px" cellpadding="2px" width="800">
    <thead align="center">
    <tr> 
        <td>Volume</td>
        <td>Problem Number</td>
        <td>Title</td>
        <td>User</td>
        <td>Trial Number</td>
        <td>Score</td>
        <td>Received Time</td>
            <td>Log File</td> 
            <td>Program Listing</td>
            <td>Comment</td>
        </tr>
    </thead>
    <tbody align="center">
<?php
$result = mysql_query($query);

for($i = 0; $i < mysql_num_rows($result); $i++){
    list($user, $score, $valid, $volume, $number, $trial, $time, $comment) = mysql_fetch_row($result);
    $title = mysql_result(mysql_query("select title from problems where volume = '$volume' and number = '$number'"), 0);

    echo "<tr>";
    echo "<td><a style=\"text-decoration:none;color:black;\" href='scoreboard.php?v=$volume'>$volume</a></td>";
    echo "<td><a href='list.php?v=$volume&n=$number'>$number</a></td>";
    echo "<td>$title</td>";
    $query = "select name from users where user = '$user'";
    $chinese_name = mysql_query($query);
    if(mysql_num_rows($chinese_name) > 0)
        $chinese_name = mysql_result($chinese_name, 0, 0);
    else
        $chinese_name = '';
    echo "<td>";
    if($userid == "")
        echo "<a style=\"text-decoration:none;color:black;\" href='list.php?v=$volume&n=$number&u=$user'>";
    echo "$user<br>$chinese_name";
    if($userid == "")
        echo "</a>";
    echo "</td>";
    echo "<td>$trial</td>";
    if( is_null($score) ){
        $wait_count = mysql_result(mysql_query("select count(*) from $volume where score is NULL and time < '$time'"), 0, 0);
        if( $wait_count == 0 )
            echo "<td>Judging...</td>";
        else
            echo "<td>Waiting for $wait_count ".($wait_count > 1 ? "submissions":"submission")."...</td>";
    }else
        echo "<td>".($valid ? $score : "<font color='red'>$score (INVALID)</font>")."</td>";
    echo "<td><p style='width: 8em'>$time</p></td>";
    if ( is_null($score) )
        echo "<td>N/A</td>";
    else
        echo "<td><a href='log.php?v=$volume&n=$number&u=$user&t=$trial'>detail</a></td>"; 
    echo "<td><a href='program.php?v=$volume&n=$number&u=$user&t=$trial'>program</a></td>";
    if(strcmp($comment, "Red Card") == 0)
        echo "<td><img src=\"images/red_card.png\"></td>";
    else if(strcmp($comment, "Yellow Card") == 0)
        echo "<td><img src=\"images/yellow_card.png\"></td>";
    else
    	echo "<td>" . ($comment ? $comment : "&nbsp") . "</td>";
    echo "</tr>\n";
}
?>
</table>
</table>
</center></div>  
<br>

<?php
print "Current time is " . date("y-m-d H:i:s");  
?>
<hr>
<?php include("footnote.php"); ?>
</body></html>
