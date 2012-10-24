<?php
include("config.php");
$PageTitle = "$StrCourseName ".($count==0?"Vote Page":"Vote Result");

session_start();

if (!isset($_SESSION["ID"])) {
    Header("Location: index.htm");
    exit ("Please login first");
}

/*
if(!$_SESSION['SU']){
    exit ("under construction.");
}
*/

if ($_SESSION["guest"]){
    exit("Permission denied.");
}

if (($QuizEnv || $ContestEnv) && !$_SESSION["SU"]){
    if ($_SESSION["guest"])
        exit("Please submit later. There is a(n) exam/quiz now.");
    $conn_ip = getenv("REMOTE_ADDR");
    if ($conn_ip != $ContestIP)
        exit("Your IP address $conn_ip is not allowed to submit now.");
}

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if (!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$number = $_REQUEST["n"];
if(!preg_match('/^\d+$/', $number))
    exit("Invalid vote number.");

$query = "SELECT title, available, description, options, deadline FROM votes WHERE number = $number";
if(!$_SESSION['SU'])
    //$command .= " AND available = TRUE";
    $query .= " AND available = TRUE";
$result = mysql_query($query);
if(mysql_num_rows($result) == 0)
    exit("Vote not available.");

list($title, $available, $description, $options, $deadline) = mysql_fetch_row($result);
$optlist = split(":", $options);

$query = "SELECT COUNT(*) FROM voterec WHERE user = '${_SESSION['ID']}' AND number = $number";
$result = mysql_query($query);
$count = mysql_result($result, 0);

if ($_POST["SUBMIT"] == "Submit") {
    $userid = $_SESSION["ID"];

    /*
    $sep     = md5(implode("", $_POST));
    $program = "@" . $sep . "@";
    foreach(str_replace(".", "_", split(" ", $file_list)) as $fieldname){
	$program .=  $_POST[$fieldname] . "\n@" . $sep . "@";
    }
    */

    $time = date("Y-m-d H:i:s");
    //$ip = getenv("REMOTE_ADDR");
    //$valid = $time <= $deadline ? true : false;
    if($count > 0){
	exit("You've voted.");
    }
    if( $time > $deadline ){
	exit("Vote is over");
    }
    if( array_search($_POST['vote'], $optlist) === FALSE ){
	exit("Invalid vote option");
    }
    $query = "INSERT INTO voterec (user, number, selection, time) VALUES ('$userid', '$number', '${_POST['vote']}', '$time')";

    if (!mysql_query($query))
	die('Invalid query: ' . mysql_error());
    exit("The program has been submitted successfully. <br>Press <a href='?n=$number'>here</a> to return to the result page");
    /*
    $command = "INSERT INTO $volume (user, number, program, time, ip, valid) VALUES ('$userid', '$number', '$program', '$time', '$ip', '$valid')";
    if($_SESSION["guest"])
        $command = "INSERT INTO $volume (user, number, program, time, ip, valid, comment) VALUES ('$userid', '$number', '$program', '$time', '$ip', '$valid', 'guest')";

    if (!mysql_query($command))
	die('Invalid query: ' . mysql_error());
    exit("The program has been submitted successfully. <br>Press <a href='list.php?v=$volume&n=$number'>here</a> to return to the result page");
    */
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="zh-tw">
<title><?php echo $PageTitle;?></title>
</head>

<body background="images/back.gif">
<div id="content">
<h2><?php echo $PageTitle; ?></h2>
<?php include ("menu.php"); ?>
<?php include ("announce.php"); ?>
<hr>
<table width=100% >
<tr><td><h2><?php echo $title ?></h2>
<p><?php echo $description; ?></p>
<?php if($count <= 0 && (date("Y-m-d H:i:s") <= $deadline) && !$_SESSION['SU'] ){ ?>
<form name="submit_form" method="POST" action="?">
<input type="hidden" name="v" value="<?php echo $volume; ?>">
<input type="hidden" name="n" value="<?php echo $number; ?>">
<?php
    foreach($optlist as $opt){
	echo "<input type='radio' name='vote' id='v_$opt' value='$opt'/>
	    <label for='v_$opt'>$opt</label>
	    <br />";
    }
?>
<br /><input type="submit" name=SUBMIT value="Submit"> <input type="reset" value="Reset" onclick="document.load_form.reset()">
</form>
<?php
}else{
    echo "<h3>Result</h3>\n";
    $query = "SELECT selection, COUNT(*) as c FROM voterec
	WHERE number = $number
		AND user IN (SELECT user FROM users WHERE class='user')
	GROUP BY selection";
    $result = mysql_query($query);
    unset($vcount);
    foreach($optlist as $opt)
	$vcount[$opt] = 0;
    while($row = mysql_fetch_array($result))
	$vcount[$row['selection']] = $row['c'];
?>
<div id=BarGraphTable name=BarGraphTable ></div>
<script language=JavaScript src=BarGraphDraw.js></script> 
<script language=JavaScript>
         //  Element array constants used to identify positions in the array
         var idxBarGraphDisplayKey=0;
         var idxBarGraphDisplayName=1;
         var idxBarGraphDisplay=2;
         var idxBarGraphDisplayWeight=3;
         var idxBarGraphMathType=4
         var BarGraphArrays = new Array();
<?php
    $votesum = 0;
    $votemax = 0;
    foreach($optlist as $k => $v){
	echo "BarGraphArrays[$k] = new Array ('$k','$v',true,'".$vcount[$v]."',BarGraphMathTypeVote); \n";
	$votesum += $vcount[$v];
        if($votemax<$vcount[$v])
            $votemax = $vcount[$v];
    }
    echo "var BarGraphVoteMax=$votemax;";
?>
BarGraphDrawTable(BarGraphArrays);
</script>
<?php } ?>
</td>
<td align=right><img style="margin-left: 20px;" src=images/new_logo_mid.jpg></td>
</tr>
</table>
</div>
<hr>
<?php include("footnote.php"); ?>
</body>
</html>

