<?php
# First Section of PHP Variables:
#   Only content of this section will be loaded by Perl Script
#
#   Inorder to cooperate with Perl, only '#' comment style could be used 
#   in this file, and make sure contents here is readable to Perl.

# Course Informations
$StrCourse = "__COURSE__";
$StrCourseName = $StrCourse." 線上批改系統";
$StrCourseNameEng = $StrCourse." JudgeGirl system";

# Site Informations
$TimeZone  = '__TMZONE__';
$JudgeAddr = '__JSADDR__';
$CourseAddr= '__CRADDR__';

# DB information, these tokens should not appear twice
# or Bash script would fail
$MySQLhost = '__SQLHOST__';
$MySQLuser = '__SQLUSER__';
$MySQLpass = '__SQLPASS__';
$MySQLdatabase = '__SQLDBSE__';

# Instructor Name
$InstName = '__INSTRU__';

# TA informations, should be separated by ', '
$TAnames   = '__TANAMES__';
$TAemails  = '__TAMAILS__';
?>

<?php
# PHP-only variables goes here
# User to be ignored
$BlackList = array();
$HomeworkVolumes = array('');

$QuizEnv         = 0;
$ContestEnv      = 0;
$ContestIP       = ''; # Contest Room IP
$ContestBeginTime= '';
$ProblemVolume   = "";
$ProblemMaxScore = 10;
$ProblemScore[0] = 10;
$ProblemScore[1] = 10;
$ProblemScore[2] = 10;
$ProblemScore[3] = 10;
$ProblemScore[4] = 10;
$ProblemScore[5] = 10;
$ProblemScore[6] = 10;
$ProblemScore[7] = 10;
$ProblemScore[8] = 10;
$ProblemScore[9] = 10;
$ProblemScore[10] = 10;
$ProblemScore[11] = 10;
$ProblemScore[12] = 10;
$ProblemScore[13] = 10;
$ProblemScore[14] = 10;
$ProblemScore[15] = 10;
$ProblemScoreSize = 16;
$ProblemColor[0] = "ddeeff";
$ProblemColor[1] = "ffeeee";
$ProblemColor[2] = "ffffee";
$ProblemColor[3] = "eeffdd";
$ProblemColor[4] = "eeffff";
$ProblemColor[5] = "eeeeff";
$ProblemColor[6] = "ddddff";
$ProblemColor[7] = "eedddd";
$ProblemColor[8] = "ddeedd";
$ProblemColor[9] = "dddddd";
$ProblemColor[10] = "ddeeff";
$ProblemColor[11] = "eeddcc";
$ProblemColor[12] = "cceedd";
$ProblemColor[13] = "ffddee";
$ProblemColor[14] = "ddeecc";
$ProblemColor[15] = "ffddee";
$ProblemColorSize = 16;
?>
