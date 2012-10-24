#!/usr/bin/perl -w

package JudgeBase;
use Exporter 'import';
@ISA = qw(Exporter);
@EXPORT_OK = qw(
        htmlspecialchars
        psystem
        connect_judgeDb
        $SystemRoot
        $StrCourse
        $StrCourseName
        $StrCourseNameEng
        $TAemails
        $CourseAddr
        $JudgeAddr
    );

use DBI;
$SystemRoot = "__JROOT__";
$ConfigPath = "__JCONF__";

$prev_break = $/;
undef $/;
open CONFIG, "$ConfigPath";
$_ = <CONFIG>;
s/<\?php\s*((.*\s*)*?)\?>/eval $1/e;
close CONFIG;
$/ = $prev_break;

sub connect_judgeDb{
    return DBI->connect("DBI:mysql:$MySQLdatabase:$MySQLhost", $MySQLuser, $MySQLpass, {PrintError=>1, RaiseError=>0});
}

sub htmlspecialchars {
    $_ = shift;
    s/&/&amp;/g;
    s/"/&quot;/g;
    s/</&lt;/g;
    s/>/&gt;/g;
    $_;
}

sub psystem {
    $_ = shift() . "\n";
    print STDERR;
    qx{$_};
}

1;
