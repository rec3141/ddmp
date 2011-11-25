#!/usr/bin/perl
#  -w
use warnings;
use strict;
use diagnostics;
# use Unicode::Normalize;
# use Unicode::String;
use Encode;
use open qw(:std :utf8);

while (<>) {
	  my $string =$_;
# 	  print $string;
# 	  print "2 ", nice_string($string),"\n";

	  while ($string =~ m/(\x{202A}(.*?)\x{202C})/) {
# 	      my $match = $1;
# 	      my $good = $2;
# 	    if ($match =~ /\x{202A}/) {
#     print "here " . nice_string($match) . "\n";
# 	     while ($match =~ m/(\x{202B}(.*?)\x{202A}(.*?)\x{202C}(.*?)\x{202C})/) {
# 	      my $revd = reverse($2 . reverse($3) . $4);
	      my $revd = reverse($2);
# 	      print "doublerevd ",$revd,"\n";
	      $string =~ s/\Q$1\E/$revd/;  
# 	      print "3 ",$string;
# 	      print "4 ",nice_string($string),"\n";
	     }
# 	    } else {
	  while ($string =~ m/(\x{202B}(.*?)\x{202C})/) {
	    my $revd = reverse($2);
# 	    print "revd ",$revd,"\n";
	    $string =~ s/\Q$1\E/$revd/;
# 	    print "5 ",$string;
# 	    print "6 ",nice_string($string),"\n";
	    }
$string =~ s/[\x{2018}\x{2019}]/\'/g; #single quote
$string =~ s/[\x{201C}\x{201D}]/\"/g; #double quote
$string =~ s/[\x{05DD}\x{0600}]/\+/g; #plus sign
$string =~ s/[\x{2212}\x{05DE}\x{0601}]/\x{2013}/g; #minus sign/en dash
$string =~ s/ ?\x{05DF}/\x{00D7}/g; #multiplication sign
$string =~ s/[\x{05E1}\x{0604}]/=/g; #equals sign
$string =~ s/[\x{05E2}\x{0613}]/\x{00B1}/g; #plus-or-minus sign
$string =~ s/\x{FB02}/fl/g; #fl ligature
$string =~ s/\x{03EF}/\x{2213}/g; #minus-or-plus sign
$string =~ s/[\x{03F3}\x{03F7}]/\x{223C}/g; #math tilde (approx equal)
$string =~ s/\x{03FE}/\x{003E}/g; #greater than
$string =~ s/\x{0408}/\x{2032}/g; #prime
$string =~ s/\x{0545}/\x{2264}/g; #less than or equal
$string =~ s/\x{0546}/\x{2265}/g; #greater than or equal
$string =~ s/\x{0131}/\x{00ED}/g; #í
$string =~ s/\x{02DC}/\x{00E3}/g; #ã
$string =~ s/\x{0BE2}//g; #strip (TM)
$string =~ s/\x{1B67}/\x{00A9}/g; #copyright (C)
$string =~ s/\x{2044}4/1\x{2044}4/g; #/4 to 1/4
$string =~ s/\x{2423}/\x{03B1}/g; #greek alpha
$string =~ s/\x{040A}/\x{00B0}/g; #degree sign
$string =~ s/\x{FB01}/fi/g; #fi ligature

$string =~ s/\#\#\#/\n\n\n\n\n\#\#\#\#\#/;
print $string unless ($string =~ m/^\s*$/); #don't print blank lines
# print $string if ($string =~ m/^\s*\d+$/);
# print nice_string($string),"\n";

#  unless ($string eq $_);
}



   sub nice_string {
       join("",
         map { $_ > 255 ?                  # if wide character...
               sprintf("\\x{%04X}", $_) :  # \x{...}
               chr($_) =~ /[[:cntrl:]]/ ?  # else if control character ...
               sprintf("\\x%02X", $_) :    # \x..
               chr($_)                     # else as themselves
         } unpack("U*", $_[0]));           # unpack Unicode characters
   }
# #                     # --------------------------------------------------------
# #                     # |Code  |Name                      |UTF-8 representation|
# #                     # |------|--------------------------|--------------------|
# #                     # |U+202a|Left-To-Right Embedding   |0xe2 0x80 0xaa      |
# #                     # |U+202b|Right-To-Left Embedding   |0xe2 0x80 0xab      |
# #                     # |U+202c|Pop Directional Formatting|0xe2 0x80 0xac      |
# #                     # |U+202d|Left-To-Right Override    |0xe2 0x80 0xad      |
# #                     # |U+202e|Right-To-Left Override    |0xe2 0x80 0xae      |
# #                     # --------------------------------------------------------
# #                     #
# #                     # The following are characters influencing BiDi, too, but
# #                     # they can be spared from filtering because they don't
# #                     # influence more than one character right or left:
# #                     # --------------------------------------------------------
# #                     # |Code  |Name                      |UTF-8 representation|
# #                     # |------|--------------------------|--------------------|
# #                     # |U+200e|Left-To-Right Mark        |0xe2 0x80 0x8e      |
# #                     # |U+200f|Right-To-Left Mark        |0xe2 0x80 0x8f      |
# #                     # --------------------------------------------------------
# #                     #
# #                     # Do the replacing in a loop so that we don't get tricked
# #                     # by stuff like 0xe2 0xe2 0x80 0xae 0x80 0xae.
# #                     while ($var =~ s/\xe2\x80(\xaa|\xab|\xac|\xad|\xae)//g) {
# #                     }
# #   
