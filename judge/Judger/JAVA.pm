#!/usr/bin/perl -wU

package Judger::JAVA;

use JudgeBase qw(htmlspecialchars psystem $SystemRoot);
use Digest::MD5 qw{md5_hex};

$Judgexec     = "$SystemRoot/judgexec";
$JAVAC        = '/usr/bin/javac';  # ISO C99 standard
$JAVA         = '/usr/bin/java';

sub new {
    my($type) = $_[0];
    my($self) = {};
    bless($self, $type);
    return $self;
}

# Default settings
sub setproblemconf {
    my ($self, $conf_path, $conf_valr) = @_;
    while( my($key, $val) = each (%$conf_valr) ){
        eval "\$$key = '$val';";
    }
    $CompileCmd  = '$JAVAC $source';
    $ClassName   = 'Main';  # Nowar: Class name usually use big capital.

    $TestCmd     = '$ClassName < $test_in > $user_out';

    if(open CONFIG, "$conf_path"){
        eval <CONFIG>;
        close CONFIG;
    }
}

sub compile {
    my ($self, $files_ref, $err_msg_ref) = @_;
    my $objects  = "", $log = "";
    foreach my $source (@$files_ref){
        my $do_compile = 0;

        if( substr($source, -5) eq ".java" ){
            $objects .= substr($source, 0, -5).".class ";
            $do_compile = 1;
        }

# Compilation

        if($do_compile){
            my $msg = eval "psystem(qq{$CompileCmd 2>&1})";
            if( $msg ){
                $log .= sprintf $err_msg_ref->{WRN_COMPILE}, htmlspecialchars($msg);
            }
            if( $? ){
                $log .= sprintf $err_msg_ref->{ERR_COMPILE}, htmlspecialchars($msg);
                return ($log, $?, '');
            }
        }
    }

    my $exec_md5 = '';
    my $exec_ret = 0;

    if ( open EXEC, "$ClassName.class" ){
        $exec_md5 = md5_hex <EXEC>;
        close EXEC;
    }

    return ($log, $exec_ret, $exec_md5);
}

sub jexec {
    my ($self, $test_path, $test_num, $test_in, $user_out) = @_;
    my $cmd = qq[
        set -f; ulimit -t $TimeLimit;
        exec /usr/bin/time --format="%e\\\\n%M\\\\n" -o exec_record $JAVA -Xmx${SpaceLimit}k $TestCmd 2>exec_log
    ];
    eval "print STDERR qq{$cmd\n}";
    eval "exec qq{$cmd}";
}

sub exec_msg {
    my ($self, $ret_val, $ret_msg, $err_msg_ref, $sig_name_ref) = @_;
    my $crash = $ret_val, my $log = '';
    my ($exec_time, $exec_space) = (undef, undef);

    if ($crash) {
        my $exec_log = '';
#        open EXECLOG, "<exec_log";
#        my $exec_log = <EXECLOG>;
#        close EXECLOG;
        $log .= sprintf $err_msg_ref->{ERR_EXEC_PRE}, htmlspecialchars($exec_log."\n".$ret_msg);
    }else{
        $log .= '你的程式已執行完畢。<br>';
        open RECORD, " < exec_record";
        my @ary = split "\n", <RECORD>;
        ($exec_time, $exec_space) = @ary[-2..-1];
        close RECORD;
    }
    return ($crash, $log, $exec_time, $exec_space);
}

1;
