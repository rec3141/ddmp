#!/usr/bin/perl
use warnings;
use strict;
use diagnostics;
use Encode;
use open qw(:std :utf8);
my $saved = '';
my %hash = ();
my $count=0;
while (<>) {
  chomp;
  my $str = $_;
  next if ($str =~ m/^\s*$/); #don't print blank lines
  if ($str =~ m/\#\#\#\#\#/){print "\n\n\n\n\n\#\#\#\#\#\n";next};
  if ($str =~ m/^TABLE/) {
    $saved = $str;
    print "$str\n";
    next;
  } elsif (length($saved)>0) {
      if (($str =~ m/^(\s{3,})/) & ( $str !~ m/\b\s{2,}\b/)){ #if spaces but not multiple words
	my $len = length($1);
	$str =~ s/^\s*//; #cut leading space
	$str =~ s/\s*$//; #cut trailing space
	if (defined($hash{$len})) {
	  $hash{$len} .= " $str";
	} else {$hash{$len} = $str}
	next;
      } else { #if not
# 	my @print = sort {$hash{$a}{'len'} <=> $hash{$b}{'len'} || $hash{$a}{'str'} cmp $hash{$b}{'str'}} keys %hash;
	my @print = sort {$a <=> $b} keys %hash;
	  if (scalar(@print)>0) {
	  print "   ";
	  foreach (@print) {print $hash{$_},"   "};
	  print "\n";
	}
	print "$str\n";
	$saved = '';
	%hash = ();
# 	$count=0;
      }
  } else {
      print "$str\n";
      $saved = '';
      %hash = ();
  }
}
