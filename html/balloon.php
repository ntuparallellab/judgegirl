<style type="text/css">
#balloon{
    position: fixed;
    top: 0px;
    right: 0px;
}
</style>
<!--[if IE]>
<style>
body{
    margin: auto;
    overflow-y: hidden;
}
#content{
    padding: 1em;
    height: 100%;
    overflow-y: auto;
}
#balloon{
    position: absolute;
    top: 0px;
    right: 1em;
}
</style>
<![endif]-->
<?php
if($ContestEnv && $volume == $ProblemVolume){
    echo "<div id='balloon'>";
    $query = "SELECT number, max(score) FROM $ProblemVolume WHERE user = '${_SESSION['ID']}' GROUP BY number";
    $result = mysql_query($query);
    for($i = 0; $i < mysql_num_rows($result); $i++){
	list($number, $score) = mysql_fetch_row($result);
	if($score == $ProblemScore[$number])
	    echo "<img src='images/balloon".$number.".gif'>";
    }
    echo "</div>\n";
}
?>
