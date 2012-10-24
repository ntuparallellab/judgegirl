#!/usr/bin/perl -w

undef $/; # Read the whole input stream at once

open ALL, ">list.csv";
while(<[A-Z]*>){
    open FILE, $_;
    $input = <FILE>;
    close FILE;

    $input =~ /Username:\s*(\w+)\s*Password:\s*(\w+)/;
    print ALL "$1, $2\n";
}
close ALL;
