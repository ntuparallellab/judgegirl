#!/usr/bin/perl -w

use JudgeBase qw(connect_judgeDb);
use feature "switch";

$dbh = connect_judgeDb();

$no_test = (grep{$_ eq '--no-test'}@ARGV);
%prob = ();
READLINE: while(<STDIN>){
    chomp;
    s/^\s+//;
    ($_ eq '' || m/^#/ ) && next READLINE;
    given($_){
        when(/=/){
            local ($key, $value) = split(/\s*=\s*/, $_, 2);
            $prob{$key} = $value;
        }
        when(/^unset\s+/i){
            s/^unset\s+//i;
            delete $prob{$_};
        }
        when(/^insert$/i){
            local (@Keys, @Vals);
            while( ($key, $val) = each(%prob) ){
                push(@Keys, $key);
                push(@Vals, $val);
            }
            my $dbcmd = "insert into votes (".join(", ",@Keys).") values (".join(", ",@Vals).");";
            print $dbcmd, "\n";
            $dbh->do($dbcmd) if $no_test;
        }
        default{
            print STDERR "Error: not recognized format \n";
        }
    }
}
print "dry run done, please use option --no-test to execute if this is ok\n" if not $no_test ;
