<?php
include("config.php");

session_start();

if (!isset($_SESSION["ID"])) {
    Header("Location: index.htm");
    exit ("Please login first");
}
if ($_SESSION["guest"] && ($QuizEnv || $ContestEnv)){
    exit("Please submit later. There is a(n) exam/quiz now.");
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

$volume = $_REQUEST["v"];
if(!preg_match('/^\w+$/', $volume))
    exit("Invalid volume name.");
$number = $_REQUEST["n"];
if(!preg_match('/^\d+$/', $number))
    exit("Invalid problem number.");

$command = "SELECT * FROM volumes WHERE name = '$volume'";
if(!$_SESSION['SU'])
    $command .= " AND available = TRUE";
$result = mysql_query($command);
if(mysql_num_rows($result) == 0)
    exit("Volume not available.");
$command = "SELECT title, deadline, url, file FROM problems WHERE volume = '$volume' and number = '$number' and NOT testpath IS NULL";
if(!$_SESSION['SU'])
    $command .= " AND available = TRUE";
$result = mysql_query($command);
if(mysql_num_rows($result) == 0)
    exit("Problem not available.");

list($title, $deadline, $url, $file_list) = mysql_fetch_row($result);

if ($_POST["SUBMIT"] == "Submit") {
    $userid = $_SESSION["ID"];
    if(false && !$_SESSION['SU'] && ($ContestEnv || $QuizEnv) ){
        $query = "SELECT MAX(time) FROM $volume WHERE user = '$userid' AND number = $number";
        $result = mysql_query($query);
        if(mysql_num_rows($result) > 0){
            $last_time = mysql_result($result, 0, 0);
            if(time() - strtotime($last_time) < 60 )
                exit("You've just submitted for this problem lately, please come back later.");
        }
    }
    $wait_count = mysql_result(mysql_query("select count(*) from $volume where score is NULL and user = '$userid' and number = $number"), 0, 0);
    if($wait_count != 0)
        exit("You've just submitted for this problem lately, please come back after previous submission has been judged.");
    $sep     = md5(implode("", $_POST));
    $program = "@" . $sep . "@";
    foreach(str_replace(".", "_", explode(" ", $file_list)) as $fieldname){
        $program .=  $_POST[$fieldname] . "\n@" . $sep . "@";
    }
    $program = "0x".bin2hex($program);

    date_default_timezone_set($TimeZone);
    $time = date("Y-m-d H:i:s");
    $ip = getenv("REMOTE_ADDR");
    $valid = $time <= $deadline ? true : false;

    $command = "INSERT INTO $volume (user, number, program, time, ip, valid) VALUES ('$userid', '$number', $program, '$time', '$ip', '$valid')";
    if($_SESSION["guest"])
        $command = "INSERT INTO $volume (user, number, program, time, ip, valid, comment) VALUES ('$userid', '$number', $program, '$time', '$ip', '$valid', 'guest')";

    if (!mysql_query($command))
        die('Invalid query: ' . mysql_error());
    exit("The program has been submitted successfully. <br>Press <a href='list.php?v=$volume&n=$number'>here</a> to return to the result page");
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="zh-tw">
<title><?php echo $StrCourseName; ?> Program Submission Page</title>
<script type="text/javascript">
function getHttpRequest(){
    var http_request = null;
    try{
	http_request = new XMLHttpRequest();
    }catch(e){
	try{	 // for IE 6.0+
	    http_request = new ActiveXObject("Msxml2.XMLHTTP");
       	}catch(e){
	    try{ // for IE 5.5+
		http_request = new ActiveXObject("Microsoft.XMLHTTP");
	    }catch(e){
		alert("Your browser does not support AJAX!");
	    }
	}
    }
    return http_request;
}

function refreshField(program_name){
    if(http_request = getHttpRequest()){
	http_request.onreadystatechange = function(){
	    if(http_request.readyState == 4){
		if(http_request.status == 200)
		    document.getElementById(program_name).value = http_request.responseText;
		else
		    setTimeout("refreshField('" + program_name + "')", 250);
	    }
	}
	http_request.open("GET", "loadfile.php?ACTION=QUERY", true);
	http_request.send(null);
    }
}

function loadFile(program_name){
    document.getElementById("field_name").value = program_name;
    document.load_form.submit();
    setTimeout("refreshField('" + program_name + "')", 250);
}

function checkForm() {
    var form = document.forms["submit_form"];
    for(var i in form) {
        if(form[i].nodeName.toUpperCase() == "TEXTAREA") {
            if(/^\s*$/.test(form[i].value)) {
                alert("記得貼上程式在上傳喔！");
                return false;
            }
        }
    }
    return true;
}
</script>
</head>

<body background="images/back.gif">
<div id="content">
<h2><?php echo $StrCourseName; ?> Program Submission Page</h2>
<?php include ("menu.php"); ?>
<a class="ext menu" href="list.php?<?php echo "v=$volume&n=$number"; ?>">Judge status</a>
<?php include ("announce.php"); ?>
<hr>
<table>
  <tr>
    <td>
      <h2>程式提交 <?php echo $title ?></h2>
      <style type="text/css"> .block { display: block; margin: 1em 0; }</style>
      <span style="color:red;" class="block"><strong>***Warning***</strong> 上傳前<strong>請再三確認你的程式碼不會Compile Error</strong>。當你同一題得到兩次Compile Error之後，再次上傳該題目將會得到0分，且批改娘不會批改你的答案。</span>
      <span style="color:#333" class="block">
        <span>Compiler: GCC 4.4.4 (Red Hat 4.4.4-13)</span>
        <ul style="margin: 0;">
          <li>C: gcc -std=c99 -Wall -fno-builtin -O1</li>
        </ul>
      </span>
      <span class="block">上傳前<strong>記得將system()從程式碼中移除</strong>，否則你會得到Compile Error。</span>
      <span class="block"><a href="compile_info.php" target="_blank">Click Here for Other Information</a></span>
      <span style="color:red;" class="block">(It is not compatible with Safari on Mac. Please use other browsers, thanks.)</span>
      <?php
          if(date("Y-m-d H:i:s") > $deadline)
              echo "<font color='red'>The deadline ", $deadline, " has passed.</font><br>";
      ?>
      <form name="submit_form" method="POST" action="submission.php" onsubmit="javascript:return checkForm();">
        <input type="hidden" name="v" value="<?php echo $volume; ?>">
        <input type="hidden" name="n" value="<?php echo $number; ?>">
        <table>
        <?php
            foreach(explode(" ", $file_list) as $filename) {
            	$fieldname = strtr($filename, ".", "_");
        ?>
          <tr>
            <td colspan="2">
        <?php
            	echo "<fieldset><legend>$filename</legend><textarea id='$fieldname' name='$fieldname' rows='10' cols='60' style='border: 0; font-size: 16;'></textarea></fieldset>\n";
        ?>
            </td>
          </tr>
          <tr>
            <td id="td_load_<?php echo $fieldname; ?>" style="height: 2em; position: relative">
        <?php
            }
        ?>
            <td align="right"><input type="submit" name=SUBMIT value="Submit"> <input type="reset" value="Reset" onclick="document.load_form.reset()"></td>
          </tr>
        </table>
      </form>
      <form name="load_form" action="loadfile.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="ACTION" value="UPLOAD">
        <input type="hidden" name="FIELD" id="field_name">
        <input type="hidden" name="MAX_FILE_SIZE" value="65536">
        <?php
            foreach(explode(" ", $file_list) as $filename){
            	$fieldname = strtr($filename, ".", "_");
            	echo "$filename <input type='file' id='load_$fieldname' name='$fieldname' onchange='loadFile(\"$fieldname\")'><br>\n";
            }
        ?>
      </form>
    </td>
    <td>
      <img style="margin-top: 20px; margin-left: 20px;" src=images/new_logo_mid.jpg>
    </td>
  </tr>
  <tr>
    <td colspan=2>
    <?php
        include('simple_html_dom.php');
        if(preg_match('/groups.google.com/', $url))
            $problem_des = file_get_dom($url)->find('div#g_body') ;
        if(preg_match('/sites.google.com/', $url))
            $problem_des = file_get_dom($url)->find('div#sites-canvas-main') ;
        if( $problem_des ){
            echo "<h2>題目敘述</h2>";
            echo "<font style='sans-serif'>".iconv("UTF-8", "BIG5//TRANSLIT", $problem_des[0]).'</font>';
            echo "<hr>";
        }
    ?>
    </td>
  </tr>
</table>
<h2><strong>See the results</strong></h2>
<a href="list.php?<?php echo "v=$volume&n=$number"; ?>">Press here</a>
<hr>
<script type="text/javascript">
for(var i = 0; i < document.load_form.length; i++)
    if(document.load_form.elements[i].type == "file"){
	document.load_form.elements[i].style.left = document.getElementById("td_" + document.load_form.elements[i].id).offsetLeft + 3;
	document.load_form.elements[i].style.top  = document.getElementById("td_" + document.load_form.elements[i].id).offsetTop  + 3;
    }
</script>
<?php include("footnote.php"); ?>
</div>
<?php
$volume = $ProblemVolume;
include("balloon.php");
?>
</body>
</html>
