#!/usr/bin/perl -wU

package Judger::CPP;

# Judge system configuration
# 2010-9-8: now centralized into tools/JudgeBase.pm
use JudgeBase qw(htmlspecialchars psystem $SystemRoot);
use Digest::MD5 qw{md5_hex};

#$Judgexec     = "$SystemRoot/judgexec";
#$CC           = '/usr/bin/g++ -c -std=c++98 -Wall -fno-builtin -O1';  # ISO C99 standard
#$LD           = '/usr/bin/g++';

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
    $Judgexec     = "$SystemRoot/judgexec";
    $CC           = '/usr/bin/g++ -c -std=c++98 -Wall -fno-builtin -O1';  # ISO C99 standard
    $LD           = '/usr/bin/g++';
    $IncPaths    = '';
    $CompileCmd  = '$CC $IncPaths $source';
    $ObjectList  = '$objects';
    $LinkLibs    = '-lm';
    $LinkCmd     = '$LD $LinkLibs $objects';

    $TestCmd     = './a.out < $test_in > $user_out';

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

        if( substr($source, -2) eq ".c" ){
            $objects .= substr($source, 0, -2).".o ";
            $do_compile = 1;
        }
        if( substr($source, -4) eq ".cpp" ){
            $objects .= substr($source, 0, -4).".o ";
            $do_compile = 1;
        }
        if( substr($source, -3) eq ".cc" ){
            $objects .= substr($source, 0, -3).".o ";
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

# Linkage

    my $exec_md5 = '';
    my $msg = eval "psystem(qq{$LinkCmd 2>&1})";
    my $exec_ret = $?;
    if( $exec_ret ){
        $log .= sprintf $err_msg_ref->{ERR_LINK}, htmlspecialchars($msg);
    } else {
        open EXEC, "a.out";
        $exec_md5 = md5_hex <EXEC>;
        close EXEC;
    }

    return ($log, $exec_ret, $exec_md5);
}

sub jexec {
    my ($self, $test_path, $test_num, $test_in, $user_out) = @_;
    my $cmd = qq{
        set -f; ulimit -t $TimeLimit; ulimit -v $SpaceLimit;
        exec $SystemRoot/bin/time --format="%e\\\\n%M\\\\n%y\\\\n" -o exec_record $Judgexec $TestCmd
    };
    eval "print STDERR qq{$cmd\n}";
    eval "exec qq{$cmd}";
}

sub exec_msg {
    my ($self, $ret_val, $ret_msg, $err_msg_ref, $sig_name_ref) = @_;
    my $crash = 0, my $log = '';
    my ($exec_time, $exec_space) = (undef, undef);

    undef $@;
    eval {
# die strings should ends with "\n":
#   please refer to 2nd paragraph of http://perldoc.perl.org/functions/die.html

# Handling status when judge.pl waits for timing program
        die (sprintf $err_msg_ref->{ERR_EXEC}."\n",
                $sig_name_ref->[$ret_val & 127].($ret_val & 128?" (Core dumped)":""))
            if (($ret_val & 127) == 9); # This is killed by the judge.pl, so not a timer error.
        die (sprintf $err_msg_ref->{ERR_TIMER}."\n", "timer program not found")
            if ($ret_val == -1);
        die (sprintf $err_msg_ref->{ERR_TIMER}."\n", "timer program crashed: ".
                $sig_name_ref->[$ret_val & 127].($ret_val & 128?" (Core dumped)":""))
            if ($ret_val & 127);

# Handling timing program return status
        $ret_val = $ret_val >> 8;
        die (sprintf $err_msg_ref->{ERR_NOEXEC}."\n", "command not found")
            if ($ret_val == 127);
        die (sprintf $err_msg_ref->{ERR_NOEXEC}."\n", "command could not be executed")
            if ($ret_val == 126);
        die (sprintf $err_msg_ref->{ERR_TIMER}."\n", "timer program returns $ret_val")
            if ($ret_val > 0);

# Read timing program result
        open RECORD, " < exec_record" or
            die (sprintf $err_msg_ref->{ERR_TIMER}."\n", "cannot open timing result file: $!");
        ($exec_time, $exec_space, $waitstat) = split "\n", <RECORD>;
        print STDERR "time: $exec_time, memory: $exec_space, stat: $waitstat\n";
        close RECORD;

# Handling status timing program waits for the actual program
        die (sprintf $err_msg_ref->{ERR_NOEXEC}."\n", "command not found")
            if ($waitstat == -1);
        die (sprintf $err_msg_ref->{ERR_EXEC}."\n",
                $sig_name_ref->[$waitstat & 127].($waitstat & 128?" (Core dumped)":""))
            if ($waitstat & 127);

# Handling return status
        die $err_msg_ref->{ERR_EXIT}."\n" if ($waitstat >> 8);
    };
    return ($crash = 1, $log = $@, $exec_time, $exec_space) if $@;

    $log = '你的程式已執行完畢。<br>';
    return ($crash = 0, $log, $exec_time, $exec_space);
}

1;
