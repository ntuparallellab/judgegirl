<?php
include("config.php");

session_start();

if(!isset($_SESSION["ID"]))
    exit("Please <a href='index.htm'>login</a> first.<br>");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

if( !$_SESSION["SU"] && $_REQUEST["u"] && $_REQUEST["u"] != $_SESSION["ID" ] )
    exit("Access denial.");
$userid = $_SESSION["SU"] ? $_REQUEST["u"] : $_SESSION["ID"];
if(!preg_match('/^\w+$/', $userid))
    exit("Invalid username.");
$volume = $_REQUEST["v"];
if(!preg_match('/^\w+$/', $volume))
    exit("Invalid volume name.");
$number = $_REQUEST["n"];
if(!preg_match('/^\d+$/', $number))
    exit("Invalid problem number.");
$trial = $_REQUEST["t"];
if(!preg_match('/^\d+$/', $trial))
    exit("Invalid trial number.");

$queryv = "SELECT * from volumes where name = '$volume'";
$queryp = "SELECT * from problems where volume = '$volume' and number = $number";
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

$query = "SELECT log FROM $volume WHERE number = $number AND trial = $trial AND user = '$userid'";
$result = mysql_query($query);
if(mysql_num_rows($result) == 0)
    exit("No such submission.");
?>
<html>
<head><title>Log Listing</title>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
</head>
<body style="font-family: monospace;" onload=document.getElementById('back').focus()>
<?php
echo mysql_result($result, 0, 0);
?>
<a href="list.php?<?php echo "v=$volume&n=$number"; ?>">return to the list</a>
</body>
</html>
