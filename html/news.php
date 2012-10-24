<?php
include("config.php");

session_start();

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$userid = $_SESSION["ID"];
if(!preg_match('/^\w*$/', $userid))
    exit("Invalid username.");

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title><?php echo $StrCourseName; ?> News</title>
<style type="text/css">
    div.wrapper { width: 60%; margin: 2em auto; text-align: left; }
</style>
</head>
<body background="images/back.gif">
<h2><?php echo $StrCourseName; ?> Recent News</h2>
<?php include("menu.php"); include ("announce.php"); ?>
<hr>
<center><h2>Recent News</h2></center>

<?php
/*------------------- admin start -------------------*/
if($_SESSION["SU"]) {
    $_title = $_POST["title"];
    $_content = preg_replace('/\n/m', '<br>', $_POST["content"]);
    if($_title && $_content) {
        mysql_query("INSERT INTO news (user, title, content) VALUES ('$userid', '$_title', '$_content');");
    }
    $_post_id = $_POST["post_id"];
    if($_post_id) {
        mysql_query("DELETE FROM news WHERE id = '$_post_id';");
    }
?>
<center>
<div class="wrapper">
    <h3>Create News</h3>
    <form method="POST" action="news.php">
        Title:<br>
        <input type="text" name="title"><br>
        Content:<br>
        <textarea name="content" rows="12" cols="80"></textarea><br>
        <input type="submit" value="Submit">
    </form>
</div>
</center>
<center>
<div class="wrapper">
    <h3>Delete News</h3>
    <form method="POST" action="news.php">
        ID: #<input type="text" name="post_id"><input type="submit" value="Delete">
    </form>
</div>
</center>
<?php
}
/*-------------------  admin end  -------------------*/
?>

<?php
$result = mysql_query("SELECT * FROM news ORDER BY timestamp DESC");
for($i=0; $i<mysql_num_rows($result); $i++) {
    list($post_id, $timestamp, $user, $title, $content) = mysql_fetch_row($result);
?>
    <center>
    <div class="wrapper">
        <h3>#<?php echo $post_id; ?>: <?php echo $title; ?></h3>
        <div><?php echo $content; ?></div>
        <div align="right">
            <span style="color:#666666;">Posted at <strong><?php echo $timestamp; ?></strong> By <strong><?php echo $user; ?></strong>.</span>
        </div>
    </div>
    </center>
<?php
}
?>

<?php
print "Current time is " . date("y-m-d H:i:s");  
?>
<hr>
<?php include("footnote.php"); ?>
</body>
</html>
<!-- Yi ;) -->
