<?php
include("config.php");
session_start();
if (!isset($_SESSION["ID"]))
    exit("Please <a href=\"index.php\">login</a> first.\n");
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo $StrCourseName; ?> Guest Submission</title>
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

$volume = "homework";
$N_Problem = mysql_result(mysql_query("select count(*) from problems where number >= 1 and volume = '$volume' and available = 1"), 0);
if (!$_SESSION['SU']){
    echo("Access denial.<BR>");
    exit;
}

$query = "select MAX(score) as maxscore, user, number from $volume where valid = 1 and number >= 1 and user in (select user from users where class = 'guest') group by user, number";
$result = mysql_query($query);

$query = "select user from users where class = 'guest' order by user";
$resultUser = mysql_query($query);


for($i=1; $i<=mysql_num_rows($resultUser); $i++){
    $array = mysql_fetch_array($resultUser, MYSQL_ASSOC);
    $Userlist[$array["user"]] = 0;
}

foreach($Userlist as $u => $g){
    for($i=1;$i<=$N_Problem;$i++){
        $UserGrade[$u][$i] = -1;
        $UserTrial[$u][$i] = -1;
    }
}


for($i=1; $i<=mysql_num_rows($result); $i++){
    $array = mysql_fetch_array($result);
    $UserGrade[$array["user"]][$array["number"]] = $array["maxscore"];
}
?>

<h2><?php echo $StrCourseName; ?> Guest Submission</h2>
<?php include ("menu.php"); include ("announce.php"); ?>
<hr>
<center>
<?php
$query = "SELECT title FROM volumes WHERE name = '$volume'";
$result = mysql_query($query);
echo "<h2>".mysql_result($result, 0)."</h2>\n";
?>

<h2>Guest Submission List</h2>
</center>

<div align="center"><center>      
    <table><tr valign=top><td>
    <table border="2" cellspacing="0" cellpadding="2">
    <tbody>
    <tr align="center"> 
        <td width="15%">User</td>
        <td width="50">Name</td>
<?php
for($i=1; $i<=$N_Problem; $i++){
    echo "<td>$i</td>";
}
?>
    </tr>
<?php
for($i=1;$i<=$N_Problem;$i++){
    $score[$i] = 0;
}
$n_user = 0;
foreach($Userlist as $u => $g){
    if ($g==0){
        $n_user++;
        echo "<tr>\n";
        echo "<td bgcolor=FFFFAA>$u</td>\n";
        $query = "select name from users where user = '$u'";
        $chinese_name = mysql_query($query);
        $chinese_name = mysql_result($chinese_name, 0, 0);
        echo "<td bgcolor=88FF88>$chinese_name</td>\n";
        for($i=1;$i<=$N_Problem;$i++){
            if($UserGrade[$u][$i] < 0)
                echo "<td bgcolor=FF88FF>" . "N/A" . "</td>\n";
            else{
                echo "<td bgcolor=$ProblemColor[$i]>" . $UserGrade[$u][$i] . "</td>\n";
                $score[$i] += $UserGrade[$u][$i];
            }

        }
        echo "</tr>\n";
    }
}
?>
        </tbody>
    </table>  
</table>
</center></div>  
<br>
<?php echo "Current time is " . date("Y-m-d H:i:s")  ?>
<hr>
<?php include("footnote.php"); ?>
</body></html>
