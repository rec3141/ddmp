#!/usr/bin/perl
use warnings;
use strict;
# use diagnostics;
use Encode;
use open qw(:std :utf8);
use Data::Dumper;
# use Bio::DB::Taxonomy;

# my $idx_dir = '/work/bergeys/tables/idx/';
# my ( $nodefile, $namesfile ) = ( "nodes.dmp", "names.dmp" );
# my $db = new Bio::DB::Taxonomy( -source    => 'flatfile',
#                                 -nodesfile => $nodefile,
#                                 -namesfile => $namesfile,
#                                 -directory => $idx_dir
# );

my %tablehash;
my %traithash;
my %taxahash;
my %footnotes;
my %caphash;
my $new=0;
my $title;

#read tables into %tablehash
while (<>) {
  next if m/^\s*$/; #skip blank lines
  chomp; #remove newline
  s/\b\s+$//; #remove trailing spaces
  my $str = $_;
  if ($str =~ m/\#\#\#\#\#/) {
    $new=1;
  } elsif ($new==1) {
    (my @desc) = split(/\s/,$str);
    shift(@desc);
    $title = shift(@desc);
    my $caption = join(" ",@desc);
    $caphash{$title} = $caption;
    $new=2;
  } elsif ($new==2) {
    $str =~ s/^\s*\b//; #remove leading spaces
    my @taxa;
    if ($str =~ m/\t/) {
      @taxa = split(/\t/,$str);
    } else {
     @taxa = split(/ {2,}/,$str);
    }
    push(@{ $taxahash{$title} },@taxa);
    $new=0;
  } elsif ($str=~ m/footnote ([a-z][a-z]?): (.*)/) {
#       print "footnote $1: $2\n";
      $footnotes{$title}{$1} = $2;
  } else {
#     print nice_string($str),"\n";
    push(@{ $tablehash{$title} },$str);
  }
}


#find tables which match 'Utilization of:' etc.
#and parse data into new hash of traits

TABLE: foreach my $table (keys %tablehash) {
my $trait;
  LINE:  foreach my $line (@{ $tablehash{$table} }) {
#     print "$line\n";
    if ($line =~ m/^(.*?)\:\s*$/) {
      $trait = lc($1);
      $trait =~ s/\s/_/g;
#       print $trait,"\n";
    } elsif ($line =~ m/requirement/i) {
      push @{ $traithash{$table}{'requirement'} }, $line;
    } elsif ($line =~ m/growth/i) {
      push @{ $traithash{$table}{'growth'} }, $line;
    } elsif ($line =~ m/reduction/i) {
      push @{ $traithash{$table}{'reduction_of'} }, $line;
    } elsif (defined($trait)) {
      if ($line =~ m/^ /) {
	$line =~ s/^\s*\b//; #remove leading spaces
	push @{ $traithash{$table}{$trait} }, $line;
      } else {
      undef $trait;
      push @{ $traithash{$table}{'characteristic'} }, $line;
      }
    } else {
      push @{ $traithash{$table}{'characteristic'} }, $line;
    }
  } #end LINE
} #end TABLE

# parse hash of traits into exportable data
my %bytrait; # $bytrait{'fermentation_of'}{'glucose'}{'Table 1'}[0] = '+';
my %byorg; # $byorg{'Table 1'}{'organism'}{'fermentation_of'}{'glucose'} = '+';
foreach my $table (keys %traithash) {
  foreach my $trait (keys %{$traithash{$table}}) {
  foreach my $line (@{ $traithash{$table}{$trait} }) {
    my @traitvals;
    if ($line =~ m/\t/) {
      @traitvals = split(/\t/,lc($line));
    } else {
      @traitvals = split(/ {2,}/,lc($line));
    }
#     print(join("~",@traitvals),"\n");
    my $chem = shift(@traitvals);

    #deal with commas
    my @simtrait = ();
    if ($chem =~ m/, /) {
      @simtrait = split(/, /,$chem);
      foreach my $subchem (@simtrait) {
# 	$subchem =~ s/ //g;
	$subchem =~ s/^\s*\b//; #remove leading spaces
	$subchem =~ s/\b\s*$//; #remove trailing spaces
	push @{ $bytrait{$trait}{$subchem}{$table} }, @traitvals;
# 	print "$trait\t$chem\t",join("\t",@traitvals),"\n";
	if (scalar(@{ $bytrait{$trait}{$subchem}{$table} }) == scalar(@{$taxahash{$table}})) {
	  foreach my $taxon (@{$taxahash{$table}}) {
	  my $val = shift(@{ $bytrait{$trait}{$subchem}{$table} });
	  if ($val =~ m/([+\x{2013}\x{2212}])([a-z][a-z]?)/) {
	    $val =~ s/([+\x{2013}\x{2212}])([a-z][a-z]?)/$1!$2/ if defined($footnotes{$table}{$2});
	  }
# 	  $val =~ s/^[\x{2013}\x{2212}]([ ]*)/0$1/; #replace leading -
# 	  $val =~ s/([ ]*)[\x{2013}\x2212]$/0$1/; #replace trailing -
# 	  $val =~ s/^\+([ ]*)/1$1/; #replace leading +
# 	  $val =~ s/([ ]*)\+$/0$1/; #replace trailing +
	  $byorg{$table}{$taxon}{$trait}{$subchem} = "'$val'";
	  }
	} else {die("lengths don't match in $trait \'$subchem\' ",scalar(@traitvals)," vs ",scalar(@{$taxahash{$table}}),"\n")}
      }
    } else {
#       $chem =~ s/ //g;
	push(@{ $bytrait{$trait}{$chem}{$table} }, @traitvals);
#       print "$trait\t$chem\t",join("\t",@traitvals),"\n";
	if (scalar(@{ $bytrait{$trait}{$chem}{$table} }) == scalar(@{$taxahash{$table}})) {
	  foreach my $taxon (@{$taxahash{$table}}) {
	  my $val = shift(@{ $bytrait{$trait}{$chem}{$table} });
	  if ($val =~ m/([+\x{2013}\x{2212}])([a-z][a-z]?)/) {
	    $val =~ s/([+\x{2013}\x{2212}])([a-z][a-z]?)/$1!$2/ if defined($footnotes{$table}{$2});
	  }
# 	  $val =~ s/^[\x{2013}\x{2212}](\s*)/neg$1/; #replace leading -
# 	  $val =~ s/(\s*)[\x{2013}\x2212]$/neg$1/; #replace trailing -
# 	  $val =~ s/^\+(\s*)/pos$1/; #replace leading +
# 	  $val =~ s/(\s*)\+$/pos$1/; #replace trailing +
	  $byorg{$table}{$taxon}{$trait}{$chem} = "'$val'";
	  }
	} else {die("lengths don't match in $trait \'$chem\' ",scalar(@traitvals)," vs ",scalar(@{$taxahash{$table}}),"\n")}
    }
#deal with multi-lines
  } #end foreach my $line
  } #end foreach my $trait
} #end foreach my $table


# no need to do it so it matches import.csv
# print out clean table
# foreach my $trait (keys %bytrait) {
#   foreach my $chem (keys %{ $bytrait{$trait} }) {
#     foreach my $table (keys %{ $bytrait{$trait}{$chem} }) {
# 	print "taxonomy\t\t",join("\t",@{ $taxahash{$table} }),"\n";
# 	print "$trait\t",join("\t",@{ $bytrait{$trait}{$chem}{$table} }),"\n";
#     }
#   }
# }

# print out clean table
# "taxonomy","taxonomy","taxonomy","bergeys","growth","growth","growth","growth","growth","characteristic","reduction_of","characteristic","characteristic","characteristic","hydrolysis_of","hydrolysis_of","utilization_of","utilization_of","utilization_of","utilization_of","utilization_of","fatty_acid","characteristic"
# "ncbi_taxid","genus","species","BXCII.a.92.","0%_nacl","10%_nacl","4°c","25°c","35°c","barophilic","nitrate","denitrification","fermentation","anaerobic_respiration","gelatin","starch","d-glucose","maltose","salicin","galacturonate","dl-glycerate","polyunsaturated_fatty_acid","g+c_content"

foreach my $table (keys %caphash) {
    print "ddmp_src\t$table\t$caphash{$table}\n";
} print "\n";

foreach my $table (keys %footnotes) {
  foreach my $fn (sort keys %{$footnotes{$table}}) {
    print "ddmp_fntext\t$table\t$fn\t$footnotes{$table}{$fn}\n";
  }
} print "\n";

print "ddmp_class\t\t";
foreach my $trait (sort keys %bytrait) {
  foreach my $chem (sort keys %{$bytrait{$trait}}) {
    print "\t",$trait;
  }
} print "\n";

print "ddmp_prop\t\t";
foreach my $trait (sort keys %bytrait) {
  foreach my $chem (sort keys %{$bytrait{$trait}}) {
    print "\t",$chem;
  }
} print "\n";

foreach my $table (sort keys %byorg) {
  foreach my $org (sort keys %{ $byorg{$table} }) {
      print "ddmp_data\t$table\t$org";
    foreach my $trait (sort keys %bytrait) {
      foreach my $chem (sort keys %{$bytrait{$trait}}) {
	if (defined($byorg{$table}{$org}{$trait}{$chem})) {
	  if ($byorg{$table}{$org}{$trait}{$chem} eq 'nd') {
	    print "\tNULL";
	  } else {
	    print "\t",$byorg{$table}{$org}{$trait}{$chem};
	  }
	} else {
	  print "\tNULL";
	}
    }
  }
  print "\n";
  }
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

# print Dumper(%bytrait);
# print Dumper(%taxahash);


# -2	NO growth
# -1	not determined/no data
# 0	no (most not)/minus
# 1	yes (most do)/plus
# 2	variable
# 3	
# 4	
# 5	
# !	footnote
