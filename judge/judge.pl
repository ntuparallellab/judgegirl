#!/usr/bin/perl -wU

undef $/; # Read the whole input stream at once

# Judge system configuration
# 2010-9-8: now centralized into tools/JudgeBase.pm
use JudgeBase qw(connect_judgeDb htmlspecialchars psystem $SystemRoot);
use List::Util qw[min max];

{
    local ($s, $m, $h, $d, $M, $Y) = localtime(time);
    $Y += 1900;
    $M += 1;
    $log_target = sprintf "logs/log%d-%02d-%02d-%02d:%02d:%02d_%d", $Y, $M, $d, $h, $m, $s, $$;
    open STDERR, "| tee -ai $log_target";
}

$TmpDirPrefix = "/tmp";
$TmpDir       = "$TmpDirPrefix/Judge_$$"; # $$ means PID
$JudgePeriod  = 1; #integer, in second


# Default settings
# Customize these settings in $test_path/config.pl

%problemconf  = (
    CheckCmd    => '',
    TimeLimit   => '3',         # CPU limit, in seconds
    Timeout     => '10',        # Real time limit, in seconds
    SpaceLimit  => '16384',     # Virtual Memory limit, in kilo-bytes
    OutputLimit => '2048',      # File size limit, in 512-byte blocks
    PreTestCmd  => '',
    JudgeCmd    => '/usr/bin/diff -q -b -B $test_out $user_out',
    PostTestCmd => '',
    ScoreExpr   => '$point',
    TimeExpr    => '$time_avg',
    SpaceExpr   => '$space_max',
    ExecRecOut  => 'exec_record',
    SampleInput => '0',         # Whether error in the sample input gets card
    CardLimit   => '-1',         # Number of card received to get zero score
);

@sig_name = (
        '',
        'terminal line hangup',
        'interrupt program',
        'quit program',
        'illegal instruction',
        'trace trap',
        'abort program (formerly SIGIOT)',
        'emulate instruction executed',
        '發生除以 0 的運算', #floating-point exception
        '執行時間或記憶體用量超過限制', #kill program
        'bus error',
        '使用到不該用的記憶體', #segmentation violation
        'non-existent system call invoked',
        'write on a pipe with no reader',
        'real-time timer expired',
        'software termination signal',
        'urgent condition present on socket',
        'stop (cannot be caught or ignored)',
        'stop signal generated from keyboard',
        'continue after stop',
        'child status has changed',
        'background read attempted from control terminal',
        'background write attempted to control terminal',
        'I/O is possible on a descriptor (see fcntl(2))',
        'cpu time limit exceeded (see setrlimit(2))',
        '輸出訊息過量 (言多必失)',
        'virtual time alarm (see setitimer(2))',
        'profiling timer alarm (see setitimer(2))',
        'Window size change',
        'status request from keyboard',
        'User defined signal 1',
        'User defined signal 2',
        'thread interrupt',
);

%err_msg = (
        ERR_CREATE   => '無法開啟程式檔。(系統錯誤，請戳助教)<br>',
        ERR_CHECK    => '你的程式碼不符合題目的要求喔！(也許用到了什麼不該用的東西？)<br>',
        WRN_COMPILE  => '以下是編譯器 (compiler) 對你的程式碼所發出的抱怨：<br>'.
                         '<pre style=background-color:#ccc;margin:10px;padding:15px>%s</pre>',
        ERR_COMPILE  => '編譯 (compile) 發生錯誤。你程式碼的語法有錯，或是用到還沒宣告的東西。<br>'.
                        '批改娘愛的叮嚀：如果你的程式碼編譯 (compile) 時發生錯誤，就沒辦法產生執行檔，是不能執行並測試的喔！啾咪 ^_<<br>',
        ERR_SAFEFUNC => '使用到不允許的函數或變數：<br>'.
                        '<pre style=background-color:#ccc;margin:10px;padding:15px>%s</pre>'.
                        '批改娘愛的叮嚀：你用到了沒有被允許使用的函數或變數 (或是根本不存在的函數或變數)，執行下去可能會讓批改娘受傷，所以先中止你的程式了。'.
                        '如果有什麼疑慮，請跟助教聯絡喔！<br>',
        ERR_LINK     => '連結 (link) 發生錯誤。可能你用到了沒有定義好的函數或變數。<br>'.
                        '以下是連結器 (linker) 所發出的抱怨：<br>'.
                        '<pre style=background-color:#ccc;margin:10px;padding:15px>%s</pre>'.
                        '批改娘愛的叮嚀：如果你的程式連結 (link) 時發生錯誤，就沒辦法產生執行檔，是不能執行並測試的喔！啾咪 ^_<<br>',
        ERR_TIMER    => '執行量測程式出錯：%s (請聯絡助教)<br>',
        ERR_NOEXEC   => '無法執行程式。原因：%s (系統錯誤，請戳助教)<br>',
        ERR_EXEC     => '你的程式當掉了！>"< 原因：%s<br>',
        ERR_EXEC_PRE => '你的程式當掉了！>"< 原因：<br>'.
                         '<pre style=background-color:#ccc;margin:10px;padding:15px>%s</pre>',
        ERR_EXIT     => '批改娘愛的叮嚀：你的結束碼不是 0，這樣就算答案對也不算對喔！<br>', # unused
        ERR_RED_CARD => '你已經拿到紅牌了喔，批改娘不想幫你改啦=3=<br>',
);

####
#
# Main Program
#
####

$dbh = connect_judgeDb();


print STDERR "judge begins...\n";

# After receive SIGTERM/SIGINT, the judge program will terminate itself when idling (without judging)
$SIG{INT} = $SIG{TERM} = sub {
    print STDERR "exiting..\n";
    $exit_flag = 1;
};

# Re-read volums list after signaled by SIGUSR1
$SIG{USR1} = sub {
    print STDERR "reloading volume list...\n";
    $reload = 1;
};

# Main Loop
$reload = 1;
%judgers = ();
JUDGE: while(1){

    if($reload){
        ($sth, $volref) = prepare_volumes($dbh);
        load_judgers();
        $reload = 0;
    }

    $dbh->do(qq{LOCK TABLES }.join(", ", map "$_ WRITE", @{$volref}) );
    $sth->execute();
    $sth->bind_columns(\$user, \$volume, \$number, \$trial, \$program, \$time, \$comment);

    unless( $sth->fetch() ){
        $dbh->do(qq{UNLOCK TABLES});
        last if( $exit_flag );
        sleep($JudgePeriod);
        last if( $exit_flag );
        redo JUDGE;
    }
    $dbh->do(qq{UPDATE $volume SET comment = 'Judging'
                 WHERE user = '$user' and number = $number and trial = $trial});
    $dbh->do(qq{UNLOCK TABLES});

    ($count) = $dbh->selectrow_array(qq{select count(*) from $volume where user = '$user' and number = '$number' and result is not NULL});

    ($type) = $dbh->selectrow_array(qq{
        SELECT type from volumes WHERE name = '$volume'
    });

    ($title, $file_list, $test_path) = $dbh->selectrow_array(qq{
        SELECT title, file, testpath FROM problems WHERE volume = '$volume' AND number = $number
    });
    @files = split ' ', $file_list;

    my $judger = $judgers{$type};

# Configuration and Environment Setup

    $judger->setproblemconf("$test_path/config.pl", \%problemconf);
    foreach (keys %problemconf){
        eval "\$$_ = \$Judger::$type"."::$_;";
    }
    eval "\$Judger::$type"."::test_path = '$test_path';";

    $working_dir = "$TmpDir/$user-$volume-$number-$trial";
    psystem("/bin/mkdir -p $working_dir");
    chmod 0770, $working_dir;
    chdir $working_dir;

#     Card System

    $result = undef;
    if( $CardLimit >= 0 && $count >= $CardLimit )
    {
        $log = $err_msg{ERR_RED_CARD};
        $comment = "Red Card";
        $point = 0;
        next JUDGE;
    }

    $point = 0;
    $log   = "$user $title 第 $trial 次測試的結果：<br>";

# Fetch sources from database

    my $sep;
    $program =~ s/^@[0-9a-f]{32}@/$sep = $&, ""/e;
    for $source (@files){
        if( !open SOURCE, ">$source" ){
            $log .= $err_msg{ERR_CREATE};
            next JUDGE;
        }
        $program =~ s/(.*?)$sep/print(SOURCE $1), ""/se;
        close SOURCE;
    }

# Check additional requirements

    eval "psystem(qq{$CheckCmd > /dev/null})";
    if( $? ){
        $log .= $err_msg{ERR_CHECK};
        next JUDGE;
    }

# Compilation

    $judger->setproblemconf("$test_path/config.pl", \%problemconf);
    ($msg, $exec_ret, $exec_md5) = $judger->compile(\@files, \%err_msg);
    $log .= $msg;
    if($exec_ret)
    {
        $result = "CE";
        $comment = "Yellow Card" if ($CardLimit >= 0 && $count < $CardLimit-1);
        $comment = "Red Card" if ($CardLimit >= 0 && $count >= $CardLimit-1);
        next JUDGE;
    }

# Test

    # Input:    $test_path/*.in
    # Solution: $test_path/*.out
    # Output:   $working_dir/*.out
    
    my ($test_num, $normal_exit) = (0, 0);
    ( $time_min,  $time_avg,  $time_max) = (0.0, 0.0, 0.0);
    ($space_min, $space_avg, $space_max) = (  0,   0,   0);

    ++$test_num unless (-f "$test_path/".($test_num).".in");
    for(; -f "$test_path/".($test_num).".in"; $test_num++)
    {
        $test_in  = "$test_path/$test_num.in";
        $test_out = "$test_path/$test_num.out";
        $user_out = "$test_num.out";
        
        if ($test_num == 0) {
            $log .= '範例測資：';
        } else {
            $log .= '第 '. $test_num .' 次試驗：';
        }

        eval "psystem(qq{$PreTestCmd})";

#     Execution

        if(my $pid = fork){
            local $SIG{ALRM} = sub { kill 9, $pid };
            alarm $Timeout;
            wait;
            alarm 0;
        }else{
            $judger->jexec($test_path, $test_num, $test_in, $user_out);
        }

        my ($crash, $msg, $exec_time, $exec_space) = $judger->exec_msg($?, $!, \%err_msg, \@sig_name);
        $log .= $msg;
        if ($crash){
            $exec_time  = undef;
            $exec_space = undef;
        }else{
            $normal_exit++;
            if( defined($time_avg) and defined($exec_time) and 
                defined($space_avg) and defined($exec_space) ){
                if($test_num == 1){
                    $time_min = $time_max = $exec_time;
                    $space_min= $space_max= $exec_space;
                }else{
                    $time_min = min($time_min, $exec_time);
                    $time_max = max($time_max, $exec_time);
                    $space_min = min($space_min, $exec_space);
                    $space_max = max($space_max, $exec_space);
                }
                $time_avg+= $exec_time;
                $space_avg+= $exec_space;
            } else {
                ($time_min, $time_avg, $time_max) = (undef, undef, undef);
                ($space_min, $space_avg, $space_max) = (undef, undef, undef);
            }
        }

#     Verification

        my $judge_ret = 0;
        if( !$crash ){
            eval "psystem(qq{$JudgeCmd > /dev/null})";
            $judge_ret = $?;
        }
        if ($crash || $judge_ret) {
            $log .= '沒有通過試驗。:(<br>';
        } else {
            $log .= '恭喜你通過了試驗！:D<br>';
            $point++;
        }
#     Card System
     
        if ($test_num == 0 && $point == 0)
        {
            $log .= '批改娘愛的叮嚀：範例測資沒有過也會被我發一張牌唷，請記得在本機測過在上傳>.^<br>';
            $result = "SE";
            $comment = "Yellow Card" if ($CardLimit >= 0 && $count < $CardLimit-1);
            $comment = "Red Card" if ($CardLimit >= 0 && $count >= $CardLimit-1);
            next JUDGE;
        }

        $point = 0 if($test_num == 0);
            
#     Clean
        eval "psystem(qq{$PostTestCmd})";

    }

    #if($normal_exit == $test_num && $point == 0){
    #    print STDERR "test_num = $test_num, normal_exit = $normal_exit\n";
    #    $log .= '批改娘愛的叮嚀：如果你的程式的輸出格式沒有按照題目要求，批改娘有可能會看不懂喔！<br>';
    #}

    $test_num--;

    if($point == $test_num and defined($time_avg) and defined ($space_avg)){
        $time_avg /= $test_num;
        $space_avg /= $test_num;
        $log .= sprintf "Time   min %.0lf max %.0lf avg %.0lf ms <br>\n", $time_min * 1000, $time_max * 1000, $time_avg * 1000;
        $log .= sprintf "Memory min %.0lf max %.0lf avg %.0lf KB <br>\n", $space_min, $space_max, $space_avg;
    }

}continue{
    $log .= '你對'.$title.'上傳的第 '.$trial.' 次答案得到了 '.$point.' 分。<br>';
    print_log();

    $dbTime  = defined( $time_avg) ? eval( $TimeExpr) : 'NULL';
    $dbSpace = defined($space_avg) ? eval($SpaceExpr) : 'NULL';
    $dbCmt   = defined(  $comment) ? "'$comment'"     : 'NULL';
    $dbResult= defined(   $result) ? "'$result'"      : 'NULL';
    $dbh->do(qq{UPDATE $volume SET 
                    score = }.eval($ScoreExpr) .qq{, 
                      log = }.$dbh->quote($log).qq{, 
                 exec_md5 = }."'$exec_md5'"    .qq{,
                exec_time = }.$dbTime          .qq{,
               exec_space = }.$dbSpace         .qq{,
                  comment = }.$dbCmt           .qq{,
                   result = }.$dbResult        .qq{
              WHERE user='$user' and number=$number and trial=$trial});
    chdir;
    psystem(qq{/bin/sh -c "/usr/bin/find '$TmpDir' -mindepth 1 -user `/usr/bin/whoami` -delete 2> /dev/null"});
    psystem("/bin/rm -rf $working_dir");
    last if( $exit_flag );
}

$dbh->disconnect();

chdir;
psystem("/bin/rm -rf $TmpDir");

####
#
# End of Main Program
#
####

sub prepare_volumes {
    local $dbh = shift ;
    local $volref = $dbh->selectcol_arrayref(qq{SELECT name FROM volumes});
    print STDERR "volumes ", (join ", ", @{$volref}), " loaded\n";
    return ($dbh->prepare((join " UNION ", map qq{
                (SELECT user, '$_', number, trial, program, time, comment FROM $_
                 WHERE score IS NULL and (comment != 'Judging' or comment is NULL))
            }, @{$volref})." ORDER BY time"),
            $volref );
}

sub print_log {
    $_ = $log;
    s/'/'"'"'/g;
    open SCREEN, "|/usr/bin/html2text 1>&2";
    print SCREEN $_;
    close SCREEN;
}

sub load_judgers {
    %judgers = ();
    open LIST, "/bin/ls $SystemRoot/Judger -1 |" or die "Cannot open pipe /bin/ls";
    @jlist = split "\n", <LIST>;
    close LIST;
    foreach (@jlist){
        next unless m/\.pm$/i;
        s|(.*)\.pm$|$1|i;
        eval "use Judger::$_;";
        eval "\$judgers{\$_} = Judger::$_"."->new();";
    }
}
