<?php session_start (); ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
    <title>登出</title>
</head>
<body background="images/back.gif">
<div id="content">
<h2><?php echo $StrCourseName; ?> Log Out </h2>
<hr>

<?php
session_destroy ();
?>

您已登出批改娘:)<br>
請按<a href="./">這裡</a>回到首頁

</html>
