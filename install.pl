#!/usr/bin/perl -w 

######
#
# Import Environment Variables
#

$HOME = $ENV{'HOME'};

######
#
# Configurable Variables
# Edit These Variables before installing
#

$PREFIX=$HOME;
%conf = (
# Install Path
    __JHTML => "$PREFIX/public_html",
    __JROOT => "$PREFIX/judge", 
    __JSHRC => "$PREFIX/.bashrc_judge",
    __JPROB => "$PREFIX/problems",
# Course Information
   __COURSE => 'Course Name',
   __INSTRU => 'Instructor Name',
   __TMZONE => 'Asia/Taipei',
   __JSADDR => 'http://example.com/~user/',
   __CRADDR => 'http://example.com/course-address/',
# Host of MySQL, should be localhost if you installed locally
  __SQLHOST => 'localhost',
# Leave the following 3 lines unchanged if you want to setup DB later
  __SQLUSER => '__SQLUSER__',
  __SQLPASS => '__SQLPASS__',
  __SQLDBSE => '__SQLDBSE__'
);
%TAs = (
  'TA1 One' => 'ta1@mail.com',
  'TA2 Two' => 'ta2@mail.com' # More TAs are allowed
);

######
#
# Variables Based on Configured Settings
#

$conf{'__JCONF'} = $conf{'__JHTML'} . "/config.php";
$conf{'__JBASE'} = $conf{'__JROOT'} . "/JudgeBase.pm";
@TaName = ();
@TaMail = ();
while(my($k, $v) = each(%TAs)){
  push @TaName, $k;
  push @TaMail, $v;
}
$conf{'__TANAMES'} = join(', ', @TaName);
$conf{'__TAMAILS'} = join(', ', @TaMail);

######
#
# Copy files, set permissions
#

system('cp', '-av', 'judge', $conf{'__JROOT'});
system('cp', '-av', 'html', $conf{'__JHTML'});
system('cp', '-av', '_bashrc_judge', $conf{'__JSHRC'});
system('cp', '-av', 'src/_mysqlsetup.sh', 'mysqlsetup.sh');
system('cp', '-av', 'conf', $conf{'__JPROB'});
system('mkdir', $conf{'__JROOT'}.'/logs');
system('chmod', 'g+w', $conf{'__JROOT'}.'/logs');
system('chgrp -R judgegirl '. $conf{'__JPROB'});
system('chgrp -R judgegirl '. $conf{'__JROOT'}.'/{bin,logs,Judger,JudgeBase.pm,judge.pl,judgexec,safe_func_list}');

######
#
# Compile binaries
#

system("cd '$conf{'__JROOT'}/src'; make ; cd - ");

######
#
# Replace variables
#

@file_list = (
  'mysqlsetup.sh',
  $conf{'__JSHRC'},
  $conf{'__JBASE'},
  $conf{'__JCONF'}
);

undef $/;
for $f(@file_list){
  open FILE, "<$f";
  $_ = <FILE>;
  while(my($k, $v) = each(%conf)){
    s/\b${k}__\b/$v/g;
  }
  close FILE;
  open FILE, ">$f";
  print FILE;
  close FILE;
}

######
#
# Include Shell Extensions
#

open FILE, ">>", "$HOME/.bashrc";
print FILE "\n. '$conf{'__JSHRC'}'\n";
close FILE;

######
#
# Installation complete
#

print <<EOF
================================================================================
Installation Complete

You may want to check the configuration file to see if the variables are
set correctly:
    $conf{'__JCONF'}

If you haven't create MySQL database for judgegirl or want to test the database,
plese execute ./mysqlsetup.sh

You may be interested in manual installation and some manuals in the two
following files:
    $conf{'__JROOT'}/README
    $conf{'__JROOT'}/README.hostadmin
================================================================================
EOF
