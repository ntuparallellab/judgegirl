<?php
include ("config.php");

session_start ();

?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Language" content="zh-tw">
    <title><?php echo $StrCourseName; ?></title>
    <style type="text/css">
        p.footnote {font-weight: bold; font-size: small}
    </style>
  </head>

  <body background="images/back.gif" onload='document.getElementsByName("Username")[0].focus()'>
    <h2><?php echo $StrCourseName; ?></h2>
    <?php
    if(isset($_SESSION["ID"])) {
        include("menu.php"); 
    }
    include ("announce.php");
    ?>
    <hr>
    <table width="100%">
      <tr valign=top>
        <td width="1%">
    <?php
    if($ContestEnv)
        echo "<img src='images/2012_Poster.jpg' alt=logo>";
    else
        echo "<img src='images/new_logo_mid.jpg' alt=logo>";
    ?>
        </td>
        <td>
    <?php
    if (!isset($_SESSION["ID"])) {
    ?>
          <form method="POST" action="login.php">
            <p>Your account information:</p>
            <blockquote>
            <table>
              <tr>
                <td align="right">
                  <em>Username</em>
                </td>
                <td>
                  <input type="text" name="Username" size="16" maxlength="16">
                </td>
              </tr>
              <tr>
                <td align="right">
                  <em>Password</em>
                </td>
                <td>
                  <input type="password" name="Password" size="16" maxlength="16">
                </td>
              </tr>
              <tr>
                <td align="right" colspan="2">
                  <input type="submit" name="SUBMIT" value="submit">
                </td>
              </tr>
            </table>
            </blockquote>
          </form>
    <?php
    }
    else {
        echo "<p>批改娘歡迎你的到來！" , $_SESSION['ID'] . "</p>";
        echo "<p>按<a href=\"problem.php\">這裡</a>進入題目列表</p>"
    ?>
    <?php
    }
    ?>
        </td>
      </tr>
    </table>
    <hr>
<?php include ('footnote.php'); ?>
  </body>
</html>
