<?php
include("config.php");
session_start();


if (strcmp($_POST["SUBMIT"], "submit") == 0) {

    if(!($link = mysql_connect($MySQLhost, $MySQLuser, $MySQLpass)))
	exit("Connection to database server failed.");

    if (!(mysql_select_db($MySQLdatabase)))
	exit("Connection to database failed.");

    $Username = $_REQUEST["Username"];
    $OldPassword = $_REQUEST["OldPassword"];
    $NewPassword1 = $_REQUEST["NewPassword1"];
    $NewPassword2 = $_REQUEST["NewPassword2"];

    if (preg_match('/^\w+$/', $Username)) {
	$table = "users";
	$query = "select passwd from $table where user = '$Username' and class!='locked'";
	$result = mysql_query($query);
	if (mysql_num_rows($result) == 0)
	    exit("Unknown user $Username.");
	$MD5Password = mysql_result($result, 0, 0);
	if ($MD5Password != md5($OldPassword))
	    exit("Incorrect password.");

	if ($NewPassword1 != $NewPassword2)
	    exit("The two new passwords are the not the same. Please reconfirm them.<br>Press <a href=\"passwd.php\">here</a> to change password.");

	$MD5NewPassword = md5($NewPassword1);
	$command = "update users set passwd = '$MD5NewPassword' where user = '$Username'";
	if (!mysql_query($command))
	    exit("Error occured while writing database. Password not updated.<br>Press <a href=\"index.htm\">here</a> to login.");

	exit("Password updated successfully.<br>Press <a href=\"index.htm\">here</a> to login.");
	
    } else {
	exit("Invalid username.");
    }
}
?>


<HTML>
<HEAD>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="zh-tw">
<title><?php echo $StrCourseName ?> Password Change Page</title>
</HEAD>

<BODY background="images/back.gif">
<H2><?php echo $StrCourseName ?> Password Change Page</H1>
<?php include("menu.php"); include ("announce.php"); ?>
<HR>
<P>Change Password Page</P>
<FORM METHOD="POST" ACTION="passwd.php">
<P>Your account information:</P>
<BLOCKQUOTE>
<TABLE>
<TR>
<TD ALIGN="right">
<EM>Username</EM></TD>
<TD>
<INPUT TYPE=TEXT NAME="Username" SIZE=16 MAXLENGTH=16>
</TD>
</TR>
<TR>
<TD ALIGN="right">
<EM>Old password</EM></TD>
<TD>
<INPUT TYPE=PASSWORD NAME="OldPassword" SIZE=16 MAXLENGTH=16>
</TD>
</TR>
<TR>
<TD ALIGN="right">
<EM>New password</EM></TD>
<TD>
<INPUT TYPE=PASSWORD NAME="NewPassword1" SIZE=16 MAXLENGTH=16>
</TD>
</TR>
<TR>
<TD ALIGN="right">
<EM>Confirm new password</EM></TD>
<TD>
<INPUT TYPE=PASSWORD NAME="NewPassword2" SIZE=16 MAXLENGTH=16>
</TD>
</TR>
<TR>
<TD ALIGN="right">
　</TD>
<TD>
　</TD>
</TR>
</TABLE>
</BLOCKQUOTE>
<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="submit">
</FORM>
<HR>
</BODY>

<?php include("footnote.php"); ?>
</HTML>
