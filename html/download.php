<?php
include("config.php");

session_start();

if(!isset($_SESSION['ID']))
    exit("Please <a href='index.htm'>login</a> first.");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$user = $_SESSION['ID'];

$volume = $_REQUEST['v'];
if(!preg_match('/^\w+$/', $volume))
    exit("Invalid volume.");

$number = $_REQUEST['n'];
if(!preg_match('/^\d+$/', $number))
    exit("Invalid number.");

$author = $_REQUEST['u'];
if(!preg_match('/^\w+$/', $author))
    exit("Invalid user name.");

$id = $_REQUEST['id'];
if(!preg_match('/^\d+$/', $id))
    exit("Invalid test id.");

$query = "SELECT COUNT(*) FROM volumes WHERE name = '$volume'";
if(!$_SESSION['SU'])
    $query .= " AND available = TRUE";
if(mysql_result(mysql_query($query), 0) == 0)
    exit("Volume not available.");

$query = "SELECT COUNT(*) FROM problems" .
    " WHERE volume = '$volume' AND number = '$number'" .
    " AND usertest IS NOT NULL";
if(!$_SESSION['SU'])
    $query .= " AND available = TRUE";
if(mysql_result(mysql_query($query), 0) == 0)
    exit("Problem not available.");


$query = "SELECT usertest FROM problems WHERE volume = '$volume' AND number = '$number'";
$prefix = mysql_result(mysql_query($query), 0);

if(!$prefix)
    exit("This problem is not available yet.");

$prefix .= "/$author-$id";

$zip = new ZipArchive();
$tmp = tempnam('/tmp', 'php-download');

if($zip->open($tmp, ZIPARCHIVE::CREATE) != true)
    exit("cannot open file");

$zip->addFile($prefix . ".in", basename($prefix) . ".in");
$zip->addFile($prefix . ".out", basename($prefix) . ".out");
$zip->close();

header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d, M Y H:i:s') . ' GMT');
header('Content-Type: text/plain');
header('Content-Length: ' . filesize($tmp));
header('Content-Disposition: attachment;' .
    'filename=' . $volume . '-' . $number . '-' . basename($prefix) . '.zip'
);

readfile($tmp);
unlink($tmp);

?>
