<?php
include("config.php");

session_start();

if(!$_SESSION["SU"])
    exit("Permission denied.");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$volume = $_REQUEST["v"];
if(!preg_match('/^\w+$/', $volume))
    exit("Invalid volume name.");
$number = $_REQUEST["n"];
if(!preg_match('/^\d+$/', $number))
    exit("Invalid problem number.");
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo $StrCourseName; ?> Submission Status</title>
</head>

<body background="images/back.gif">
<?php
  if(!(mysql_connect($MYSQLhost, $MySQLuser, $MySQLpass))) {
    echo("connect failed<BR>");
    exit;
  }

  if (!(mysql_select_db($MySQLdatabase))) {
    echo("select failed<BR>");
    exit;
  }

  $user = $_SESSION["ID"];
  $query = "
  select distinct user 
  from $volume
  where number=$number and valid = 1";
  
  $result = mysql_query($query);
  
  $query = "
  SELECT user 
  FROM users 
  WHERE class='user' ORDER BY user";
  
  $result2 = mysql_query($query);

  $notshit = array();
  $all = array();
  $shit = array(); 
  for($i=1; $i<=mysql_num_rows($result); ++$i){
	$array = mysql_fetch_array($result, MYSQL_ASSOC);
  	$notshit[$i-1] = $array["user"];
  }
  for($i=1; $i<=mysql_num_rows($result2); ++$i){
	$array = mysql_fetch_array($result2, MYSQL_ASSOC);
  	$all[$i-1] = $array["user"];
  }
  $k = 0;
  for($i=0; $i<count($all); ++$i){
  	$truly_shit = true;
	for($j=0; $j<count($notshit); ++$j){
	  if( $all[$i]==$notshit[$j] ){
	    $truly_shit = false;
	  }
	}
	if( $truly_shit )
	  $shit[$k++] = $all[$i];
  }
?>

<h2><?php echo $StrCourseName; ?> Submission Status</h2>
<?php include ("menu.php"); ?>
<br />
<a href="submission.php?<?php echo "v=$volume&n=$number"; ?>">Submission page</a>
<?php include ("announce.php"); ?>
<hr>
<?php print "<center><img style=vertical-align:middle src=images/judgegirl_logo_100x100.gif></h2></center>";  
?>
<div align="center"><center>      
    <table border="2">
    <tbody>
        <?php
            echo '<tr align = "center"><td>User';
            for($i=0; $i<count($shit); $i++){
                if(in_array($shit[$i], $BlackList))
                    continue;
                $query = "select name from users where user = '$shit[$i]'";
                $chinese_name = mysql_query($query);
                $chinese_name = mysql_result($chinese_name, 0, 0);
                echo "<tr align = \"center\"><td>$shit[$i]<br>$chinese_name</td></tr>";
                #echo "<tr><td>$shit[$i]";
            }
        ?>
        </tbody>
    </table>  
</center></div>  
<br>

<?php
print "Current time is " . date("y-m-d H:i:s");  
?>
<hr>
<h2><strong>Submit Program</h2>
<a href="submission.php?<?php echo "v=$volume&n=$number"; ?>">Press here</a>
<hr>
<?php include("footnote.php"); ?>
</body></html>
