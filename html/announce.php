<H2><a href="http://www.facebook.com/pages/pi-gai-niang-fen-si-tuan/257246016760">Judgegirl's fan page</a></H2>
<?php
date_default_timezone_set($TimeZone);
if($ContestEnv){
    /*
	echo "<H3><a href=\"../2011final/materials/\">Course Slides</a></H3>";
    echo "<b><span style=\"color:#ff6699;\">Editor Download: <a style=\"color:#ff9966;\" href=\"editor/vim73.zip\">Vim</a> <a style=\"color:#6699ff;\" href=\"editor/SciTE.zip\">SciTE</a></span></b><br>";
    */
}
if($_SESSION["SU"]){
    //echo "<H2><font color = red>This is announcement for superuser</font></H2>";
}else if($_SESSION["guest"]){
    //echo "<H2><font color = red>This is announcement for guest</font></H2>";
}else if($_SESSION["ID"]){
    //echo "<H2><font color = red>This is announcement for user</font></H2>";
}
?>
<br>請依本系統時間為 deadline 標準<br>
<?php echo "Current time is " . date("Y-m-d H:i:s");  ?>
<!-- ?php include ("xmas.php"); ? -->
