#!/usr/bin/perl -w

use JudgeBase qw(
        connect_judgeDb
        $StrCourse
        $StrCourseNameEng
        $TAemails
        $CourseAddr
        $JudgeAddr
    );

format MAILBODY=
Your account for @* has been created.
$StrCourseNameEng

  Username: @*
$user
  Password: @*
$passwd

You can login on @* and try it now.
$JudgeAddr

@* Course website: @*
$StrCourse, $CourseAddr
@* Online Judge System: @*
$StrCourse, $JudgeAddr
TAs' emails: @*
$TAemails
.


#####################################################
#
# Main Prog
#
#####################################################

use Digest::MD5 qw{md5_hex};
use List::Util qw{shuffle};
use feature qw{switch};

## Parse options
my $user_class = 'user';
if (defined $ARGV[0]){
    given($ARGV[0]){
        when('-g'){ $user_class = 'guest'; }
        when('-s'){ $user_class = 'superuser'; }
        when(m/-h|--help/){
            print "useradd <-g|-s> -- add account for users and write out message to info_<user_name>\n";
            print "  -g creates guest\n";
            print "  -s creates superuser\n";
            exit 0;
        }
        default{
            print "Warn: unknown option: $ARGV[0]\n";
        }
    }
}
print "User group assigned to '$user_class'.\n";

$/="\n";
$dbh = connect_judgeDb();
while(<STDIN>){
    chomp;
    ($user, $fullname) = split(/\s+/, $_, 2);
    next if $user eq '';

    @alphabet = ('2'..'9', 'A'..'H', 'J'..'N', 'P'..'Z', 'a'..'k', 'm'..'z');
    do{
	$passwd = join '',  map { $alphabet[rand($#alphabet + 1)] } (0..7);
    }until($passwd =~ /\d/ and $passwd =~ /[A-Z]/ and $passwd =~ /[a-z]/);
    $passwd_md5 = md5_hex $passwd;
    $dbh->do(qq{
        insert into users (user, passwd, class)
                   values ('$user', '$passwd_md5', '$user_class') 
        on duplicate key update 
                   passwd = '$passwd_md5', class='$user_class';
    });
    $dbh->do("update users set name = '$fullname' where user = '$user';") if defined $fullname;

    open MAILBODY, ">info_$user";
    write MAILBODY;
    close;
}

$dbh->disconnect();
