#!/usr/bin/perl -w

use JudgeBase qw(connect_judgeDb);

$dbh = connect_judgeDb();

#
# Modify this part for changing default tables of option -u -p -l -v
#

@user_table = ("users");
@problems_table = ("problems");
@volume_table = ("volumes");
@logs_table = ("log");
@votes_table = ("votes");
@voterec_table = ("voterec");

#
#
# Main Program, edit with care
#
#
use feature qw(switch);

( show_help() && exit 0 ) if @ARGV == 0;
$force = (grep {$_ eq '-f'} @ARGV);
$title = '';
for(@ARGV){
    given($_){
        when('-u'){ create_users(@user_table)        if($force or confirm('users',@user_table)) ; }
        when('-p'){ create_problems(@problems_table) if($force or confirm('problems',@problems_table)) ; }
        when('-l'){ create_logs(@logs_table)         if($force or confirm('logs',@logs_table)); }
        when('-v'){ create_volumes(@volume_table)    if($force or confirm('volumes',@volume_table)); }
        when(/^-t/){ $title=$_ ; $title =~ s/-t// ; }
        when('-f'){ ; }
        when('-vote'){
		if($force or confirm('vote',(@votes_table, @voterec_table))){
			create_votes(@votes_table);
			create_voterec(@voterec_table);
		}
	}
        default{
	    my ($type, $name) = split(":", $_);
            if ($force or confirm("problemset".(($title ne '') && ", titled '$title', of type '$type'" || ""), ($name) )){
                create_problemset(($name));
                $dbh->do("insert into volumes ( name, type ) values ( '$name', '$type' );");
                if($title ne ''){
                    $dbh->do("update volumes set title='$title' where name='$name';");
                    $title = '';
                }
            }
        }
    }
}

$dbh->disconnect();

#
#
# Subroutines
#
#

sub confirm {
    local $name = shift;
    do{
        print "Create table of $name: ".join(", ", @_).", continue? [Y/n] ";
        $_ = <STDIN>;
        chomp;
    }until(m/^[YyNn]$/ or $_ eq '');
    return (m/^[Yy]$/ or $_ eq '');
}

sub show_help {
    print "create_table.pl [OPTIONS] [-tTitle] Judge_type:problemset_name ...
    -t<Title> set the incoming problem set titled as <Title>.
    -u          create users tables:            ".join(", ",@user_table).".
    -p          create problems tables:         ".join(", ",@problems_table).".
    -l          create log tables:              ".join(", ",@logs_table).".
    -v          create volume tables:           ".join(", ",@volume_table).".
    -vote       create vote table(desc and rec) ".join(", ",(@votes_table, @voterec_table))."
    -f          create without confirm.
        To change the tables created by -u -p -l -v, please refer the source code.

Example
  for new setup
    ./create_table.pl -u -p -l -v '-tTEST ProblemSet Title' jtype1:test jtype2:untitled_probset

  for new problem sets
    ./create_table.pl '-tProbSet Title1' Judge_type:set1 '-tProbSet Title2' Judge_type:set2 

  NOTE: Judge_type should be one of the following:
    C
    CPP
    JAVA
    PYTHON
"
}

sub create_users {
    foreach (@_){
	$dbh->do(qq{
        CREATE TABLE  $_ (
         user CHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
          passwd CHAR( 32 ) NULL DEFAULT NULL ,
           class CHAR( 16 ) NULL DEFAULT  'user',
            name BLOB NOT NULL,
              PRIMARY KEY(user)
            )
		});
    }
}

sub create_problems {
    foreach (@_){
	$dbh->do(qq{
		CREATE TABLE $_(
		    volume char(255),
		    number tinyint(3) unsigned AUTO_INCREMENT,
		    title varchar(255),
		    available tinyint(1) DEFAULT 0,
		    deadline datetime,
		    file varchar(255) DEFAULT \"source.c\",
		    url varchar(255),
		    testpath varchar(255),
		    usertest varchar(255),
		    PRIMARY KEY (volume, number)
		    )
		});
    }
}

sub create_problemset {
    foreach (@_){
	$dbh->do(qq{
		CREATE TABLE $_(
		    user char(16),
		    program blob,
		    number tinyint unsigned DEFAULT 0,
		    time datetime,
		    trial tinyint unsigned AUTO_INCREMENT,
		    score double,
            exec_time double,
            exec_space double,
		    log text,
		    exec_md5 char(32),
		    ip char(39),
		    valid tinyint(1),
		    comment text,
            result varchar(255),
		    PRIMARY KEY (user, number, trial)
		    )
		});
    }
}

sub create_volumes {
    foreach (@_){
	$dbh->do(qq{
        CREATE TABLE  $_ (
         number TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
          type CHAR( 255 ) NOT NULL ,
          name CHAR( 255 ) NOT NULL ,
           title VARCHAR( 255 ) NULL DEFAULT NULL ,
            available TINYINT( 1 ) NULL DEFAULT  0
            )
        });
    }
}

sub create_logs {
    foreach (@_){
        $dbh->do(qq{
            CREATE TABLE  $_ (
            user CHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            time DATETIME NULL DEFAULT NULL ,
            ip CHAR( 20 ) NULL DEFAULT NULL
            )
        });
    }
}

sub create_votes {
    foreach (@_){
	$dbh->do(qq{
		CREATE TABLE $_(
		    number tinyint(3) unsigned AUTO_INCREMENT,
		    title varchar(255),
		    description text,
		    options text,
		    available tinyint(1) DEFAULT 0,
		    deadline datetime,
		    PRIMARY KEY (number)
		    )
		});
    }
}

sub create_voterec {
    foreach (@_){
	$dbh->do(qq{
		CREATE TABLE $_(
		    user char(16),
		    number tinyint unsigned DEFAULT 0,
		    time datetime,
		    selection text,
		    comment text,
		    PRIMARY KEY (user, number)
		    )
		});
    }
}

