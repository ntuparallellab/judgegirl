<?php
session_start();

if (strcmp($_POST["SUBMIT"], "submit") == 0) {
    include("config.php");		
    if(!($link = mysql_connect($MySQLhost, $MySQLuser, $MySQLpass)))
    	exit("Connection to database server failed.");

    if (!(mysql_select_db($MySQLdatabase)))
	    exit("Connection to database failed.");

    $Username = $_REQUEST["Username"];
    $Password = $_REQUEST["Password"];
    if (preg_match('/^\w+$/', $Username)) {
        /** compare last login time and last news created time **/
        $query = "SELECT time FROM log WHERE user='$Username' ORDER BY time DESC LIMIT 0, 1";
        $last_login_time = mysql_result(mysql_query($query), 0);
        $query = "SELECT timestamp FROM news ORDER BY timestamp DESC LIMIT 0, 1";
        $lastest_news_time = mysql_result(mysql_query($query), 0);
        $has_news = ((!$last_login_time) || (!$lastest_news_time) || (strtotime($last_login_time) < strtotime($lastest_news_time)));

        $query = "select * from users where user='$Username' and class!='locked'";
        $result = mysql_query($query);
        if (mysql_num_rows($result) == 0){
            $query = "select * from users where user='$Username' and class='locked'";
            $result = mysql_query($query);
            if (mysql_num_rows($result) == 0)
                exit("Unknown user $Username.");
            else
                exit("Locked User $Username.");
        }
        $array = mysql_fetch_array($result, MYSQL_ASSOC);
        if ($array["passwd"] != md5($Password))
            exit("Incorrect password.");

        
        $_SESSION["ID"] = $Username;
        $_SESSION["SU"] = $array["class"] == "superuser" ? 1 : 0;
        $_SESSION["guest"] = $array["class"] == "guest" ? 1 : 0;
        $ip = $_SERVER['REMOTE_ADDR'];
        $login_time = date("Y-m-d H:i:s");
        $query = "INSERT INTO `log`(`user`, `time`, `ip`) VALUES ( '$Username',  '$login_time',  '$ip')";
        mysql_query($query);


        if($has_news)
            header("Location: news.php");
        else
            header("Location: problem.php");

    } else {
        exit("Invalid username.");
    }
}
?>
