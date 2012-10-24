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
/*
$volume = $_REQUEST["v"];
if(!preg_match('/^\w+$/', $volume))
    exit("Invalid volume name.");
$number = $_REQUEST["n"];
if(!preg_match('/^\d+$/', $number))
    exit("Invalid problem number.");
$score = $_REQUEST["s"];
if(!preg_match('/^\d+$/', $score))
    exit("Invalid score.");*/
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo $StrCourseName; ?> Login log</title>
</head>

<body background="images/back.gif">

<h2><?php echo $StrCourseName; ?> Login log</h2>
<?php include ("menu.php") ; include ("announce.php"); ?>
<hr>
<?php 
$query = "select user, time, ip from log ";
if($userid) $query .= "where user = '$userid' ";
$query .= "order by time DESC limit 0, 30";
$result = mysql_query($query);
?>
<center><h2>Login log<img style=vertical-align:middle src=images/new_logo_small.jpg></h2></center>  
<center>
<form method='GET' action='login_log.php'>
    List for user: <input type='text' name='u' size='16' maxlength='16'>
    <input type='submit' value='Submit'>
</form>
</center>
<div align="center"><center>      
	<table><tr valign=top><td>
    <table border="2" cellspacing="0" cellpadding="2">
    <tbody>
	<tr align="center"> 
	    <td>User</td>
	    <td>Time</td>
	    <td>ip</td>
        </tr>
	<?php
        for($i=0; $i<mysql_num_rows($result); $i++){
            $array = mysql_fetch_array($result, MYSQL_ASSOC);
            $p_user = $array['user'];
            $time = $array['time'];
            $ip = $array['ip'];


            echo "<tr align=\"center\">";
            if( $_SESSION['SU'] ){
                $query = "select name from users where user = '$p_user'";
                $chinese_name = mysql_query($query);
                $chinese_name = mysql_result($chinese_name, 0, 0);
                echo "<td>$p_user<br>$chinese_name</td>";
            }
            echo "<td>$time</td>";
            echo "<td>$ip</td>";


            echo "</tr>";
        }
	?>
        </tbody>
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
