#!/usr/bin/perl -w

use JudgeBase qw(connect_judgeDb);
use feature "switch";

$dbh = connect_judgeDb();

$no_test = (grep{$_ eq '--no-test'}@ARGV);
$qnum = (grep( /^-n(\d+)$/, @ARGV ))[0];
$target_volume = (grep /^-v/, @ARGV)[0];
unless ( defined $qnum ){
    print "ERROR: no problem number\n";
    exit 1;
}
unless ( defined $target_volume ){
    print "ERROR: no volume name\n";
    exit 1;
}
$qnum =~ s/-n//;
$target_volume =~ s/-v//;
print "volume = $target_volume\n";
print "probno = $qnum\n";

my $chksth = $dbh->prepare_cached('select user from users where user = ?');
my $inssth = $dbh->prepare_cached("insert into $target_volume (user, number, score, trial, valid ) values (?, ?, ?, 1, 1 ) on duplicate key update score = ?");

%uscore = ();
$allok = 1;
while(<STDIN>){
    chomp;s/^\s+//;s/\r+//;
    next if $_ eq '' or m/^#/ ;

    local ($user, $score) = split(',', $_, 2);
    if( not defined $uscore{$user} ){ 
        $uscore{$user} = $score ; 
    } else {
        print "ERROR: duplicate user on '$user'" ;
        $allok = 0;
    }
}
for $user ( keys %uscore ){
    $chksth->execute($user);
    if ( $chksth->rows == 0 ){
        print "ERROR: user '$user' does not exist.\n";
        $allok = 0;
    }
}
exit 1 unless $allok ;

if(not $no_test){
    print "seems ok, please use option --no-test to execute if this is ok\n";
}else{
    $inssth->execute($user, $qnum, $score, $score) while ( ($user, $score) = each(%uscore) ) ;
    $dbh->do("UPDATE $target_volume, users SET comment = 'guest' WHERE $target_volume.user = users.user AND users.class = 'guest'"); 
}
