<?php
include("config.php");

session_start();

if(!$_SESSION["SU"])
    exit("Permission denied.");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if (!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$volume = $_REQUEST["v"];
if(!preg_match('/^\w+$/', $volume))
    exit("Invalid volume name.");
$number = $_REQUEST["n"];
if(!preg_match('/^\d+$/', $number))
    exit("Invalid problem number.");
$score = $_REQUEST["s"];
if(!preg_match('/^\d+$/', $score))
    exit("Invalid score.");
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo $StrCourseName; ?> Submission Status</title>
<style type="text/css">
table > tbody > tr > td:nth-child(1) > a { text-decoration:none; color:black ; }
</style>
</head>

<body background="images/back.gif">
<?php
  $user = $_SESSION["ID"];
  $query = "
  select number, time, trial, score, user, comment
  from $volume
  where number=$number 
  and score=$score
  order by user, trial DESC";
  
  $query2 = "select user from $volume
    where number=$number and valid = 1
	group by user having max(score)=$score
	";
 
  
  $result = mysql_query($query);
  $result2 = mysql_query($query2);
  
?>

<h2><?php echo $StrCourseName; ?> Submission Status</h2>
<?php include ("menu.php"); ?>
<br />
<a href="submission.php?<?php echo "v=$volume&n=$number"; ?>">Submission page</a>
<?php include ("announce.php"); ?>
<hr>
<?php

		if (mysql_num_rows($result2)>0){
			for($j=0;$j<mysql_num_rows($result2); $j++){
//				echo mysql_result($result2, $j, "user");
//				echo "<br>";
			}
		}
print "<center><h2>Program listing for Homework ".$number." Grade:".$score."<img style=vertical-align:middle src=images/judgegirl_logo_100x100.gif></h2></center>";  
?>
<div align="center"><center>      
	<table><tr valign=top><td>
    <table border="2" cellspacing="0" cellpadding="2" width="600">
    <tbody>
	<tr align="center"> 
		<?php
			if( $_SESSION['SU'] ){
				echo '<td>User</td>';
			}
		?>
	    <td width="15%">Homework number </td>
	    <td width="15%">Trial number</td>
	    <td width="15%">Grade</td>
	    <td width="20%">Time received</td>
            <td width="15%">Log file</td> 
            <td width="15%">Program Listing</td>
            <td width="15%">Comment</td>
        </tr>
	<?php
	    for($i=1; $i<=mysql_num_rows($result); $i++){
		$array = mysql_fetch_array($result, MYSQL_ASSOC);
		$number = $array["number"];
		$trial = $array["trial"];
		$score = $array["score"];
		$time = $array["time"];
		$p_user = $array["user"];
        $comment = $array["comment"];
		#$bad = $array["bad"];
		
		$flag = 0;
		if (mysql_num_rows($result2)>0){
			for($j=0;$j<mysql_num_rows($result2); $j++){
				if ($p_user == mysql_result($result2, $j, "user")){
					$flag = 1;
					break;
				}
			}
		}
		if ($flag ==0 ){
			continue;
		}



		echo "<tr align=\"center\"";
		#if( $bad==1 ){
		#	echo " style=background-color:#999";
		#}
		#else if( $bad==2 ){
		#	echo " style=background-color:#c36";
		#}
		echo ">";		
		if( $_SESSION['SU'] ){
            $query = "select name from users where user = '$p_user'";
            $chinese_name = mysql_query($query);
            if(mysql_num_rows($chinese_name) > 0)
                $chinese_name = mysql_result($chinese_name, 0, 0);
            else
                $chinese_name = '';
            echo "<td><a href='list.php?v=$volume&n=$num&u=$p_user'>$p_user<br>$chinese_name</a></td>";
		}
		echo "<td>$number</td>";
		echo "<td><a name=$i>$trial</a></td>";

                if (is_null($score)) { 
                  echo "<td>N/A</td>";
                } else {
                  echo "<td>$score</td>";
                } 

                echo "<td>$time</td>";

		if (is_null($score)) {
		  echo "<td>N/A</td>";
		} else {
		  $href = "href=\"log.php?v=$volume&n=$number&u=$p_user&t=$trial\"";
		  echo "<td><a " . $href . ">detail</a></td>"; 
		}

        $href = "href=\"program.php?v=$volume&n=$number&u=$p_user&t=$trial\"";
        echo "<td><a " . $href . ">program</a></td>"; 
        if(strcmp($comment, "Red Card") == 0)
            echo "<td><img src=\"images/red_card.png\"></td>";
        else if(strcmp($comment, "Yellow Card") == 0)
            echo "<td><img src=\"images/yellow_card.png\"></td>";
        else
        	echo "<td>" . ($comment ? $comment : "&nbsp") . "</td>";
		echo "</tr>";
	    }
	    $_SESSION["LOGARRAY"] = $logarray;
	    $_SESSION["PROGRAMARRAY"] = $programarray;
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
<h2><strong>Submit Program</h2>
<a href="submission.php?<?php echo "v=$volume&n=$number"; ?>">Press here</a>
<hr>
<?php include("footnote.php"); ?>
</body></html>
