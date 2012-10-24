<?php
include("config.php");

session_start();

if(!isset($_SESSION["ID"]))
    exit("Please <a href='index.htm'>login</a> first.\n");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");
if($_SESSION['SU'] && $_REQUEST['action'] != NULL){
    $volume = $_REQUEST["v"];
    if(!preg_match('/^\w+$/', $volume))
        exit("Invalid volume name.");
    $number = $_REQUEST["n"];
    if(!preg_match('/^\d+$/', $number))
        exit("Invalid problem number.");
    $avai = -1 ;
    if($_REQUEST['action'] == 'dis')
        $avai = 0;
    else if($_REQUEST['action'] == 'ena')
        $avai = 1;
    if($avai != -1){
        mysql_query("UPDATE problems SET available = $avai WHERE volume = '$volume' and number = $number");
    }
    if($_REQUEST['action'] == 'rejudge' ){
        mysql_query("UPDATE $volume SET score = null, result = null, comment = null WHERE number = $number");
    }
	header("Location: problem.php");
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<script type="text/javascript" src="js/floating-1.6.js"></script>
<title><?php echo $StrCourseName; ?> Problem List</title>
<style type="text/css">
td{
    padding-left: 8px;
    padding-right: 8px;
}
</style>
</head>
<body background="images/back.gif">
<div id="content">
<h2><?php echo $StrCourseName; ?> Problem List</h2>
<?php include("menu.php"); include ("announce.php"); ?>
<hr>
<div id="volume_list"><!-- style="padding-right:160px"-->
<center>
<h2>Submission Summary for user <?php echo $_SESSION['ID']; ?></h2>
<?php
$query_vol = "SELECT name, title, available, number FROM volumes";
if(!$_SESSION['SU'])
    $query_vol .= " WHERE available = TRUE";
$query_vol .= " ORDER by number";
$result_vol = mysql_query($query_vol);

$vol_menu = "";

for($j = 0; $j < mysql_num_rows($result_vol); $j++){
    list($volume, $vtitle, $vavailable) = mysql_fetch_row($result_vol);
?>
<h3><?php echo "<a name='$volume'>$vtitle</a>"; $vol_menu .= "<a href='#$volume'>$vtitle</a><br/>"; ?></h3>
<table border="2px" cellspacing="0px" cellpadding="2px" width="700"
<?php if($vavailable == 0){
    echo "bordercolor=\"#D0D0D0\"";
}?>
>
<thead align="center">
    <tr> 
    <td>Problem Number</td>
    <td>Title</td>
    <td>Deadline</td>
    <td>Trials</td>
    <td>Hi-score</td>
    <td>Submit Program</td>
    </tr>
</thead>
<tbody align="center">
<?php
$now_date = date("Y-m-d H:i:s");
for($k = 0; $k < 2; $k ++){
    $query = "SELECT number, title, available, deadline, url, testpath FROM problems WHERE volume = '$volume' ORDER BY number";
    $result = mysql_query($query);

    for($i = 0; $i < mysql_num_rows($result); $i++){
        list($number, $title, $available, $deadline, $url, $testpath) = mysql_fetch_row($result);
        if($k ^ ($now_date > $deadline) )
            continue;
        echo "<tr>";
        echo "<td>$number";
        if($_SESSION['SU'] && $testpath){
            echo " <a href=?v=$volume&n=$number&action=rejudge>rejudge</a>";
        }
        echo "</td>";
        echo "<td style='white-space: nowrap'>".($url?"<a href='$url'>$title</a>":$title)."</td>";
        echo "<td".(date("Y-m-d H:i:s") > $deadline ? " style='color: red'" : "").">$deadline</td>";

        $query_trial = "SELECT COUNT(*) FROM $volume WHERE user = '${_SESSION['ID']}' AND number = $number";
        $result_trial = mysql_query($query_trial);
        $count = mysql_result($result_trial, 0);
        echo "<td><a href='list.php?v=$volume&n=$number'>$count</a></td>";

        $query_score = "SELECT MAX(score) FROM $volume WHERE user = '${_SESSION['ID']}' AND number = $number AND valid = TRUE";
        $result_score = mysql_query($query_score);
        echo "<td>".($count ? floatval(mysql_result($result_score, 0)): "N/A")."</td>";

        echo "<td>";
        if($testpath){
            if($_SESSION['SU']){
                echo "<a href='submission.php?v=$volume&n=$number'>".($available?"submit":"N/A")."</a>";
                echo " <a href=?v=$volume&n=$number&action=".($available?"dis >off":"ena >on")."</a>";
            }else{
                if($available == 1){
                    echo "<a href='submission.php?v=$volume&n=$number'>submit</a>";
                }else{
                    echo "N/A";
                }
            }
        }else{
            echo "N/A";
        }
        echo "</td>";
        //echo "<td>".(($testpath && ($_SESSION['SU'] || $available == 1)) ? "<a href='submission.php?v=$volume&n=$number'>submit</a>" : "N/A")."</td>";
        echo "</tr>\n";
    }
}
?>
</tbody>
</table>  
<?php
}
?>
</center>
</div>
<br>
<?php echo "Current time is " . date("Y-m-d H:i:s")  ?>
<hr>
<?php include("footnote.php"); ?>
</div>
<!--div id="problem_menu" style="
	position:absolute;
	width:160px; padding:2px; border:1px solid #000000;
	padding:2px;background:#FFFFFF;
	z-index:100;
	font-size:small;
	white-space: nowrap;
	overflow: hidden;"><?php echo $vol_menu ?></div>
<script type="text/javascript">
	floatingMenu.add('problem_menu',{
		targetRight:5,
		centerY:true,
		snap: true});
</script-->
<?php
$volume = $ProblemVolume;
include("balloon.php");
?>
</body>
</html>
