#!/bin/bash
# 2010-9-8:
#   This programs's usage has been changed
#   just simply use this program with command ./run.sh and type password as it requires
# 2011-7-13:
#   Version update, no password execution is achieved by using sudoers configurations
#   Successfully avoid environment variable passing by executing in the same directory
sudo -u judgegirl ./judge.pl $(/usr/bin/whoami) &
#sudo -u judgegirl ./check.pl &
#sudo -u judgegirl ./check_judge.pl &
#sudo -u judgegirl ./check_insert.pl &
