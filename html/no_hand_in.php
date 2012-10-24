<?php
include("config.php");
session_start();

if(!isset($_SESSION["ID"]))
    Header("Location: index.php");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Language" content="zh-tw">
    <title><?php echo $StrCourseName; ?></title>
  </head>
  <body background="images/back.gif">
    <h2><?php echo $StrCourseName; ?></h2>
    <?php
        if(isset($_SESSION["ID"]))
            include("menu.php");
        include("announce.php");
    ?>
    <hr>

    <div>
    <?php
        for($i=0; $i<count($HomeworkVolumes); $i++) {
    ?>
      <table border="2px" cellpadding="2px" cellspacing="0" align="center"><tbody>
        <tr>
          <td>User</td>
    <?php
            $query = "SELECT number, title FROM problems WHERE volume='$HomeworkVolumes[$i]'";
            $result = mysql_query($query);
            $problems = array();
            for($j=0; $j<mysql_num_rows($result); $j++) {
                list($number, $title) = mysql_fetch_row($result);
                $problems[$j] = $number;
                echo "<td>$title</td>";
            }
    ?>
        </tr>
    <?php
            $query = "SELECT user FROM users WHERE class='guest'";
            $AuditList = mysql_query($query);
            for($j=0; $j<mysql_num_rows($AuditList); $j++) {
                list($user) = mysql_fetch_row($AuditList);
                echo "<tr>";
                echo "<td><a href=\"recent_submission.php?u=$user\" style=\"color:black;text-decoration:none;\">$user</a></td>";
                for($no=0; $no<count($problems); $no++) {
                    $query = "SELECT COUNT(*) FROM $HomeworkVolumes[$i] " .
                        "WHERE user='$user' AND number='$problems[$no]'";
                    $result = mysql_result(mysql_query($query), 0);
                    if($result > 0)
                        echo "<td>&nbsp;</td>";
                    else
                        echo "<td style=\"color:red;\">N/A</td>";
                }
                echo "</tr>";
            }
    ?>
      </tbody></table>
    <?php
        }
    ?>
    </div>

    <hr>
    <?php
        include("footnote.php");
    ?>
  </body>
</html>

