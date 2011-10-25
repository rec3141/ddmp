#!/usr/bin/perl
use warnings;
use strict;
use diagnostics;
use Encode;
use open qw(:std :utf8);

my $saved = '';
while (<>) {
  chomp;
  my $str = $_;
  $str =~ s/^ //;
  next if ($str =~ m/^\s*$/); #don't print blank lines
  $str =~ s/\#\#\#\#\#/\n\n\n\n\n\#\#\#\#\#/;
  $str =~ s/ *$//; #remove trailing spaces
  if ($str =~ m/^\s*[a-z]{1,2}$/) {
    $str =~ s/^\s*//; #replace beginning whitespace
    $saved = "footnote $str:";
    next;
  } elsif (length($saved)>0) {
    $str =~ s/^\s*//; #replace beginning whitespace
    $str =~ s/\s{2,}/ /g; #replace extra whitespace
    unless ($str =~ m/.*\.$/) { #ends in a period
      $saved = "$saved $str";
      next;
      }
    print "$saved $str\n"; 
    $saved = '';
  } else {print "$str\n"};
}
