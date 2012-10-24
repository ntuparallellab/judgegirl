<?php $newtag='<img src="images/new.gif"/>'; ?>
<style type="text/css">
    a.menu { color: blue; padding: 0 .2em; }
    a.menu.ext { color: gray; }
    a.menu.admin { color: #6666ff; }
</style>

<a class="menu" href="index.php">Home Page</a>
<a class="menu" href="news.php" style="color:red;">News</a>
<a class="menu" href="<?echo $CourseAddr?>">Course</a>
<a class="menu" href="problem.php">Problem List</a>
<a class="menu" href="scoreboard.php">Score board</a>
<!--a class="menu" href="vote.php">Vote</a--> <!--?php echo $newtag; ?-->
<!--a class="menu" href="faq.php">FAQ</a--> <!--?php echo $newtag; ?-->
<?php
if($_SESSION["SU"]) {
    echo "<a class=\"menu admin\" href=\"login_log.php\">Login Log</a>\n";
    echo "<a class=\"menu admin\" href=\"recent_submission.php\">Recent Submissions</a>\n";
/**    echo "<a class=\"menu admin\" href=\"rank.php\">Rank</a>\n"; **/
/** echo "<a class=\"menu admin\" href=\"cheat_exaime.php\">Duplication Check</a> $newtag\n"; **/
}
?>
<a class="menu" href="logout.php">Log Out</a>
<a class="menu" href="passwd.php" style="color:gray;">Change Password</a>
