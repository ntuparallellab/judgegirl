<?php
include("config.php");

session_start();

if(!isset($_SESSION["ID"]))
    Header("Location: index.htm");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if(!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$user = $_SESSION["ID"];
$volume = $ContestEnv?$ProblemVolume:$_REQUEST["v"];
if( $volume && $volume != ''){
    $queryv = "SELECT name, title from volumes where available = 1 and name = '$volume'";
    $result = mysql_query($queryv);
    if(mysql_num_rows($result) == 0)
	exit('Volume not available/exist');
    else{
        $ProblemVolume = $volume;
        list($name, $voltitle) = mysql_fetch_row($result);
    }
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<!--<meta http-equiv=refresh content=60>-->
<title><?php echo $StrCourseName ?> Score Board</title>
<script src="diagram.js"></script>
</head>
<body background="images/back.gif">
<div id="content">
<h2><?php echo $StrCourseName ?> Score Board</h2>
<?php include ("menu.php") ; include ("announce.php"); ?>
<hr>
<center>
<?php
if( $volume == NULL or $volume == ''){
    $queryv = "SELECT name, title from volumes where available = 1 order by number";
    $result = mysql_query($queryv);
    for($i = 0; $i < mysql_num_rows($result); $i++){
        list($name, $title) = mysql_fetch_row($result);
        echo "<a href='?v=$name'>$title</a><br />";
    }
    echo "</center><hr>";
    include("footnote.php");
    exit;
}
echo "<h2>".$voltitle."</h2>\n";
?>
<h2>Problem Solving Status</h2>
<table border="2px" cellspacing="0px" cellpadding="5px">
<tr align="center">
    <td>Score</td>
<?php
$query = "SELECT number, title FROM problems WHERE volume = '$ProblemVolume' and available != 0 ORDER BY number";
$result = mysql_query($query);
$num_problems = mysql_num_rows($result);
for($number = 0; $number < $num_problems; $number++){
    $rec = mysql_fetch_assoc($result);
    $nums[] = $rec['number'];
    echo "<td style='background-color: ".$ProblemColor[$number%$ProblemColorSize]."'>";
#    echo "<a href='list.php?v=$ProblemVolume&n=".$nums[$number]."'>";
    echo $rec['title'];
#    echo "</a>";
    echo "</td>\n";
}
?>
</tr>
<?php
$query = "SELECT count(*) FROM users WHERE class = 'user'";
$result = mysql_query($query);
$num_users = mysql_result($result, 0) - count($BlackList);

$query = 
        "select maxscore as score, number, count(maxscore) as count from (
            select MAX(score) as maxscore, V.user, V.number
            from $ProblemVolume V, problems P
            where P.volume = '$ProblemVolume' and V.number = P.number 
                and V.valid = 1 and P.available = 1 
                and V.user in ( select user from users where class = 'user')
            group by V.user, V.number
         ) TMP group by maxscore, number";
/*
    "SELECT score, number, COUNT(score) AS count FROM (
	 SELECT V.number, MAX(V.score) AS score FROM $ProblemVolume V, users U
	 WHERE U.user = V.user AND U.class = 'user' AND V.valid = TRUE AND V.number > 0
	 GROUP BY V.user, V.number
     ) TMP GROUP BY score, number";
*/
$result = mysql_query($query);

foreach($nums as $number)
    $sleeping[$number] = $num_users;
$ProblemMaxScore_backup = $ProblemMaxScore;
$ProblemMaxScore = NULL;
while($rec = mysql_fetch_assoc($result)){
    $count[ceil($rec["score"])][$rec["number"]] += $rec["count"];
    if($ProblemMaxScore === NULL || $ProblemMaxScore < $rec['score'])
        $ProblemMaxScore = $rec['score'];
    $sleeping[$rec["number"]] -= $rec["count"];
}
if($ProblemMaxScore === NULL)
    $ProblemMaxScore = $ProblemMaxScore_backup;

for($score = $ProblemMaxScore; $score >= 0; --$score){
    $to_print = false;
    $print_str =  "<tr align='center'>";
    $print_str .= "<td>$score</td>";
    for($number = 0; $number < $num_problems; $number++){
        $ProblemNumber = $number + 1;
        $print_str .= sprintf("<td style='background-color: ".$ProblemColor[$number%$ProblemColorSize]."'><a href='scorelist.php?v=$ProblemVolume&n=$ProblemNumber&s=$score'>%d</a> <small>(%.2f%%)</small></td>",
                $count[$score][$nums[$number]], $count[$score][$nums[$number]]*100.0/$num_users);
        if($count[$score][$nums[$number]]>0)
            $to_print = true;
    }
    $print_str .= "</tr>\n";
    if($to_print || $ProblemMaxScore <= 20)
        echo $print_str;
}
?>
<tr align='center'>
<td>Sleeping</td>
<?php
for($number = 0; $number < $num_problems; $number++) {
    $ProblemNumber = $number + 1;
    printf("<td style='background-color: ".$ProblemColor[$number%$ProblemColorSize]."'><a href='sleepinglist.php?v=$ProblemVolume&n=$ProblemNumber'>%d</a> <small>(%.2f%%)</small></td>", $sleeping[$nums[$number]], $sleeping[$nums[$number]]*100.0/$num_users);
}
?>
</tr>
</table>
<br>
<hr>
<h2>Statistics Diagram</h2>
<?php
    $query =
        "select m as  score, count(user) as count from (
            select user, sum(maxscore) as m from (
                select MAX(score) as maxscore, V.user, V.number
                from $ProblemVolume V, problems P
                where P.volume = '$ProblemVolume' and V.number = P.number 
                    and V.valid = 1 and P.available = 1 
                    and V.user in ( select user from users where class = 'user')
                group by V.user, V.number
             ) as h group by user order by m desc, user
         ) as TMP group by m";
    /*    "SELECT score, COUNT(user) as count FROM (
    	 SELECT user, SUM(score) AS score FROM (
    	     SELECT V.user AS user, MAX(V.score) AS score FROM $ProblemVolume V, users U
    	     WHERE U.user = V.user AND U.class = 'user' AND V.valid = TRUE
    	     GROUP BY V.user, V.number
    	     UNION ALL SELECT user, 0 FROM users WHERE class = 'user'
    	 ) AS TMP1 GROUP BY user
         ) AS TMP2 GROUP BY score";
    */
    $result = mysql_query($query);
    unset($freq);
    while($rec = mysql_fetch_assoc($result))
        $freq[ceil($rec["score"])] += $rec["count"];
//if($num_problems > 20){
//    echo "Sorry, this volume contains too many problems therefore is incapable of showing statics diagram<br/><br />";
//}else
//{
    ?>
    <div id=diagram_no1 style="width:750px; height:300px; background-image: url('images/judgegirl_logo_bg.gif'); background-position: center center; background-repeat: no-repeat;"></div>
    <script type="text/javascript">
    <?php
    $Max = 0;
    foreach($freq as $s => $c)
	if($Max < $s)
	    $Max = $s;
    echo "var freq = new Array(";
    for($i = 0; $i < $Max; $i++)
	if($freq[$i] === NULL)
	    echo "0, ";
        else
	    echo "$freq[$i], ";
    echo $freq[$Max].");\n";
    echo "draw_diagram(freq, 'diagram_no1');";
    echo "</script>";
    if($Max > 300){
	echo "<table border=2px cellspacing=0px cellpadding=5px>";
	echo "<tr><td>Count</td>";
	foreach($freq as $k => $v)
	    echo "<td>$v</td>";
	echo "</tr><tr><td>Score</td>";
	foreach($freq as $k => $v)
	    echo "<td>$k</td>";
	echo "</tr></table>";
    }
//}
?>
</center>
<br>
<?php echo "Current time is " . date("Y-m-d H:i:s") ?>
<hr>
<?php include("footnote.php"); ?>
</div>
<?php
$volume = $ProblemVolume;
include("balloon.php");
?>
</body>
</html>
