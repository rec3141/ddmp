#!/usr/bin/perl
use warnings;
use strict;
use diagnostics;
use Encode;
use open qw(:std :utf8);
# my $charspace =0;
while (<>) {
  my $str = $_;
#   if ($str =~ m/^([ ]+)Characteristic/) {$charspace = $1}
#   $str =~ s/^$charspace//;
#   $str =~ s/\b[ ]+$//; #remove trailing spaces
#   $str =~ s/^[ ]+\b//; #remove beginning spaces
  while ($str =~ s/(\S*)[ ]{3,}(\S)/$1\t$2/g) {; #make tabs
  $str =~ s/ \t/\t/g; #despace tabs
  }
print $str;# unless ($str eq $_);
}


#in kate: \n([a-z]{2,}.*)\s*
# s/TABLE.*\n([a-z]{2,}.*)\s*/$1 $2/ 