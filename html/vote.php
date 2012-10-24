<?php
include("config.php");

session_start();

if(!isset($_SESSION["ID"]))
    exit("Please <a href='index.htm'>login</a> first.\n");

/*
if(!$_SESSION['SU']){
    exit ("under construction.");
}
*/

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<title><?php echo $StrCourseName ?> Votes</title>
<style type="text/css">
tr.unava {
    color:gray;
}
</style>
</head>
<body background="images/back.gif">
<div id="content">
<h2><?php echo $StrCourseName; ?> Votes</h2>
<?php include("menu.php"); include ("announce.php"); ?>
<hr>

<center>
<!--h2>Submission Summary for user <?php echo $_SESSION['ID'] ?></h2-->

<table border="2px" cellspacing="0px" cellpadding="2px" width="700">
<thead align="center">
    <tr> 
    <td>Number</td>
    <td>Title</td>
    <td>Deadline</td>
    <td>Vote/Result</td>
    </tr>
</thead>
<tbody align="center">
<?php
$query = "SELECT number, title, available, options, deadline FROM votes";
if(!$_SESSION['SU'])
    $query .= " WHERE available = TRUE";
$query .= " ORDER by number";
$result = mysql_query($query);

$now_date = date("Y-m-d H:i:s");
for($j = 0; $j < mysql_num_rows($result); $j++){
    list($number, $title, $available, $options, $deadline) = mysql_fetch_row($result);

    if($available == 0)
	echo "<tr class=unava>";
    else
	echo "<tr>";
    echo "<td>$number";
    echo "</td>";
    echo "<td style='white-space: nowrap'>";
    echo     $title;
    echo "</td>";
    echo "<td".( $now_date > $deadline ? " style='color: red'" : "").">$deadline</td>";

    $query = "SELECT COUNT(*) FROM voterec WHERE user = '${_SESSION['ID']}' AND number = $number";
    $result = mysql_query($query);
    $count = mysql_result($result, 0);
    echo "<td><a href='voteact.php?n=$number'>";
    if($count == 0 && !$_SESSION['SU'])
	echo "Vote";
    else
	echo "Result";
    echo "</a></td>";
    echo "</tr>\n";
}
?>
</tbody>
</table>  
</center>
<br>
<?php echo "Current time is " . $now_date; ?>
<hr>
<?php include("footnote.php"); ?>
</div>
</body>
</html>
