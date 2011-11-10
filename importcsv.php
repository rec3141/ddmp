<?php header('Content-Type: text/html; charset=utf-8' );?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<body>
<?php 
//* get classes
//*   INSERT INTO ddmp_class if not present
//* get properties
//*   INSERT INTO ddmp_prop if not present
//*   link chemicals to chebi_compounds
// get characteristics
//   INSERT INTO ddmp_char if not present
// get table
//   INSERT INTO ddmp_src if not present
// get taxa names
//   INSERT INTO ddmp_taxa if not present
//   link taxa to ncbi_names
// get data
//   INSERT INTO ddmp_data if not present

error_reporting(E_ALL);

include("/home/reric/reric.org/loadddmp.php");
ConnectToDatabase();

$table_class = 'ddmp_class';
$table_prop = 'ddmp_prop';
$table_comp = 'ddmp_comp';
$table_char = 'ddmp_char';
$table_taxa = 'ddmp_taxa';
$table_src = 'ddmp_src';
$table_data = 'ddmp_data';
$table_fntext = 'ddmp_fntext';
$table_fnrel = 'ddmp_fnrel';
$table_ncbi = 'ncbi_names';
$table_chebi = 'chebi_compounds';


## CSV file to read in ##
foreach (glob("import.*.csv") as $csvfile) {
  echo "<h3>importing $csvfile</h3>\n";
  $classlist = array();
  $classlist_ids = array();
  $proplist = array();
  $proplist_ids = array();
  $charlist = array();
  $charlist_ids = array();
//   $charhash = array();
//   $taxalist = array();
  // data definition: [ncbi_taxid genus species ...]
  $handle = fopen($csvfile, "r") or die("couldn't open file $csvfile\n");
  while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
	$tbl = array_shift($data);
        $num = count($data);
	if ($tbl === $table_comp) {
	    $comp = mysql_real_escape_string(array_shift($data));
	    $chebi_id = mysql_real_escape_string(array_shift($data));
	    $check = mysql_real_escape_string(array_shift($data));
	    if (mysql_num_rows(mysql_query("SELECT `id` FROM `$table_prop` WHERE (`property`='$comp' );")) ) {
		if (mysql_num_rows(mysql_query("SELECT `id` FROM `chebi_compounds` WHERE `id`='$chebi_id';"))) {
		echo "<br>updating ChEBI compound:\tddmp_comp\t$comp\t$chebi_id\n";
		$update = "UPDATE `$table_prop` SET `chebi_id`='$chebi_id', `check`='$check' WHERE `property`='$comp';";
		mysql_query($update) or die(mysql_error() . " on $update\n<br>");
		} else {
		  echo "<br>incorrect ChEBI id:\tddmp_comp\t$comp\t$chebi_id\n";
		  $update = "UPDATE `$table_prop` SET `check`='N' WHERE `property`='$prop';";
		  mysql_query($update) or die(mysql_error() . " on $update\n<br>");
		}
	      } else {
		if (mysql_num_rows(mysql_query("SELECT `id` FROM `chebi_compounds` WHERE `id`='$chebi_id';"))) {
		  echo "<br>inserting ChEBI compound:\tddmp_comp\t$comp\t$chebi_id\n";
		  $insert = "INSERT INTO `$table_prop` (`property`,`chebi_id`,`check`) VALUES ( '$comp','$chebi_id','$check');";
		  mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
		} else {
		  echo "<br>incorrect ChEBI id:\tddmp_comp\t$comp\t$chebi_id\n";
		  $update = "UPDATE `$table_prop` SET `check`='N' WHERE `property`='$prop';";
		  mysql_query($update) or die(mysql_error() . " on $update\n<br>");
		}
	      }
	} else if ($tbl === $table_src) {
	      $source = mysql_real_escape_string($data[0]);
	      $desc = mysql_real_escape_string($data[1]);
	      if (! mysql_num_rows(mysql_query("SELECT `id` FROM `$tbl` WHERE (`source`='$source');")) ) {
		  echo "<br>inserting source $source\n";
		  $insert = "INSERT INTO `$tbl` (`source`, `desc`) VALUES ( '$source', '$desc');";
		  mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
	      }
	} else if ($tbl === $table_fntext) {
	      $source = mysql_real_escape_string($data[0]);
	      $fn = mysql_real_escape_string($data[1]);
	      $fntext = mysql_real_escape_string($data[2]);
	      $srcrows = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `$table_src` WHERE (`source`='$source');"));
	      $src_id = $srcrows{'id'};
	      if (! mysql_num_rows(mysql_query("SELECT `src_id` FROM `$tbl` WHERE (`src_id`='$src_id' AND `fn`='$fn');")) ) {
		  echo "<br>inserting footnote $source $fn\n";
		  $insert = "INSERT INTO `$tbl` (`src_id`,`fn`,`text`) VALUES ( '$src_id', '$fn', '$fntext' );";
		  mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
	      }
	} else if ($tbl == $table_class) { #parse classes names, add if not exist
	    while(preg_match('/^\s*$/',$data[0])) {array_shift($data);}
	    $classlist = $data;
	    $uniqclass = array_unique($classlist);
	    foreach ($uniqclass as $class) {
	      $class = mysql_real_escape_string($class);
	      if (! mysql_num_rows(mysql_query("SELECT `id` FROM `$tbl` WHERE (`class`='$class' );")) ) {
		  echo "<br>inserting class $class\n";
		  $insert = "INSERT INTO `$tbl` (`class`) VALUES ( '$class' );";
		  mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
		}
	      }
        } else if ($tbl === $table_prop) {
	    while(preg_match('/^\s*$/',$data[0])) {array_shift($data);}
	    $proplist = $data;
	    $numprops = count($proplist);
	    $uniqprop = array_unique($proplist);
	    foreach ($uniqprop as $prop) {
	      $prop = mysql_real_escape_string($prop);
	      if (! mysql_num_rows(mysql_query("SELECT `id` FROM `$tbl` WHERE (`property`='$prop' );")) ) {
// 		echo "<br>inserting property $prop\n";
		$insert = "INSERT INTO `$tbl` (`property`,`check`) VALUES ( '$prop','N');";
		mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
	      }
	    }

	    #check for ChEBI ids
	      $chebi_check = mysql_query("SELECT `property` FROM `$tbl` WHERE (`check`='N');");
	      for ($i=0;$i<mysql_num_rows($chebi_check);$i++) {
		$prop = mysql_real_escape_string(array_shift(mysql_fetch_row($chebi_check)));
		$chebi_query = mysql_query("SELECT DISTINCT `compound_id`, `parent_id`,`check` FROM ddmp_prop INNER JOIN chebi_names ON ddmp_prop.property = lower(chebi_names.name) INNER JOIN chebi_compounds ON chebi_names.compound_id = chebi_compounds.id WHERE ddmp_prop.property='$prop'");
		$compound_ids = array();
		for ($j=0;$j<mysql_num_rows($chebi_query);$j++) {
		  $chebi_row = mysql_fetch_assoc($chebi_query);
		  if ($chebi_row{'parent_id'}) {
		    array_push($compound_ids,$chebi_row{'parent_id'});
		  } else {
		    array_push($compound_ids,$chebi_row{'compound_id'});
		  }
		}
		if (count(array_unique($compound_ids))===1) {
		  if (mysql_num_rows(mysql_query("SELECT `id` FROM `chebi_compounds` WHERE `id`='$compound_ids[0]';"))) {
		    echo "<br>ChEBI match:\tddmp_comp\t$prop\t$compound_ids[0]\n";
		    $update = "UPDATE `$tbl` SET `chebi_id`='$compound_ids[0]', `check`='C' WHERE `property`='$prop';";
		    mysql_query($update) or die(mysql_error() . " on $update\n<br>");
		  } else {
		    echo "<br>ChEBI synonym (no match):\tddmp_comp\t$prop\t$compounds_id[0]\n";
		    $update = "UPDATE `$tbl` SET `check`='C' WHERE `property`='$prop';";
		    mysql_query($update) or die(mysql_error() . " on $update\n<br>");
		  }
		} else if (count(array_unique($compound_ids))>1){
		  $junk = array_values($compound_ids); sort($junk);
		  echo "<br>multiple ChEBI matches:\tddmp_comp\t$prop\t",join("\t",$junk),"\n";
		  $update = "UPDATE `$tbl` SET `check`='C' WHERE `property`='$prop';";
		  mysql_query($update) or die(mysql_error() . " on $update\n<br>");
		} else if (count(array_unique($compound_ids))===0){
		  echo "<br>no ChEBI matches:\tddmp_comp\t$prop\n";
		  $update = "UPDATE `$tbl` SET `check`='C' WHERE `property`='$prop';";
		  mysql_query($update) or die(mysql_error() . " on $update\n<br>");
		} else {}
	    }

// $values = array('firstname'=>'firstname_value','lastname'=>'lastname_value');
// sprintf('INSERT INTO %s (%s) VALUES ("%s")', 'table_name', implode(', ', array_map('mysql_escape_string', array_keys($values))), implode('", "',array_map('mysql_escape_string', $values)));
	    $c_line = sprintf("SELECT `class`,`id` FROM `ddmp_class` WHERE `class` IN ('%s');",implode("', '", array_map('mysql_escape_string', array_unique($classlist))));
	    $c_query = mysql_query($c_line);
// 	    echo $c_line,"\n";
	    while($c_list=mysql_fetch_assoc($c_query)) {
	      $classlist_ids{stripslashes($c_list{'class'})}=$c_list{'id'};
	    }

	    $p_line = sprintf("SELECT `property`,`id` FROM `ddmp_prop` WHERE `property` IN ('%s');",implode("', '", array_map('mysql_escape_string', array_unique($proplist))));
	    $p_query = mysql_query($p_line);
// echo $p_query,"\n";
	    while($p_list=mysql_fetch_assoc($p_query)) {
	      $proplist_ids{stripslashes($p_list{'property'})}=$p_list{'id'};
	    }

// print_r($classlist_ids);
// print_r($proplist_ids);

	    #populate characteristics
	    for ($i=0;$i<$numprops;$i++) {
	      $charprop = mysql_real_escape_string($proplist[$i]);
	      $charclass = mysql_real_escape_string($classlist[$i]);
// 	      $prop_ids = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `$table_prop` WHERE (`property`='$charprop' );"));
// 	      $prop_id = $prop_ids{'id'};
// 	      $class_ids = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `$table_class` WHERE (`class`='$charclass' );"));
// 	      $class_id = $class_ids{'id'};
	      $prop_id = $proplist_ids{$proplist[$i]};
	      $class_id = $classlist_ids{$classlist[$i]};
	
	      $char_query = mysql_query("SELECT `id` FROM `$table_char` WHERE (`prop_id`='$prop_id' AND `class_id`='$class_id');");
	      if (! mysql_num_rows($char_query) ) {
		  echo "<br>inserting characteristic '$charclass $charprop' \n";
		  $insert = "INSERT INTO `$table_char` (`prop_id`,`class_id`) VALUES ( '$prop_id','$class_id' );";
		  mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
		  $charlist_ids[] = mysql_insert_id();
	      } else {
		$charlist_ids[] = array_shift(mysql_fetch_row($char_query));
	      }
	    }
	    
// // 	    get charlist
// 	    $ch_line = sprintf("SELECT `char`,`id` FROM `ddmp_char` WHERE (`class`, IN ('%s');",implode("', '", array_map('mysql_escape_string', array_unique($classlist))));
// 	    $ch_query = mysql_query($ch_line);
// 	    while($ch_list=mysql_fetch_assoc($ch_query)) {
// 	      $charlist_ids{stripslashes($ch_list{'char'})}=$ch_list{'id'};
// 	    }


	} else if ($tbl === $table_data){
	    $source = mysql_real_escape_string(array_shift($data));
	    $taxon = mysql_real_escape_string(array_shift($data));

	    // check if taxon is in ddmp_taxa and get id
	    $taxa_query = "SELECT `id` FROM `$table_taxa` WHERE (`taxon`='$taxon');";
	    if (! mysql_num_rows(mysql_query($taxa_query))) {
	      // check if taxon is in ddmp_taxa
	      $ncbi_query = mysql_query("SELECT `taxonid` FROM ncbi_names WHERE `name`='$taxon'");
	      if (mysql_num_rows($ncbi_query)) {
		$taxonid = mysql_real_escape_string(array_shift(mysql_fetch_row($ncbi_query)));
		echo "<br>inserting taxon $taxon with NCBI taxonid $taxonid\n";
		$insert = "INSERT INTO `$table_taxa` (`taxon`,`ncbi_id`) VALUES ( '$taxon','$taxonid' );";
		mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");	      
	      } else {
// 		$ddmp_taxonid = substr(mysql_real_escape_string(hexdec(sha1($taxon))),0,11);
// 		echo "<br>inserting taxon $taxon with DDMP taxonid $ddmp_taxonid\n";
// 		$insert = "INSERT INTO `$tbl` (`taxon`,`ncbi_id`) VALUES ( '$taxon','$ddmp_taxonid' );";
		echo "<br>inserting taxon $taxon\n";
		$insert = "INSERT INTO `$table_taxa` (`taxon`) VALUES ( '$taxon');";
		mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");	      
	      }
	    }
	    // finally get taxa_id
	    $taxa_id = mysql_real_escape_string(array_shift(mysql_fetch_row(mysql_query($taxa_query)))); 
	    $src_query = "SELECT `id` FROM `$table_src` WHERE (`source`='$source');";
	    $src_id = mysql_real_escape_string(array_shift(mysql_fetch_row(mysql_query($src_query))));

	    for ($i=0;$i<count($charlist_ids);$i++) {
	      $char_id = $charlist_ids[$i];
	      $rawdata = array_shift($data);
	      if ($rawdata=='NULL') {continue;}
	      // footnote relationships
	      $foots = array();
	      if(preg_match_all('/\!([a-z][a-z]?)/', $rawdata, $foots)>0) {
		echo "<br>found footnote in $rawdata\n";
		$rawdata = preg_replace('/\!([a-z][a-z]?)/', '', $rawdata);
		foreach ($foots[1] as $fn) {
		  if (! mysql_num_rows(mysql_query("SELECT `src_id` FROM `$table_fnrel` WHERE `src_id`='$src_id' AND `taxa_id`='$taxa_id' AND `char_id`='$char_id' AND `fn`='$fn';"))) {
		    echo "<br>inserting footnote relation $fn, $src_id, $taxa_id, $char_id\n";
		    $insert = "INSERT INTO $table_fnrel (`src_id`,`taxa_id`,`char_id`,`fn`) VALUES ('$src_id','$taxa_id','$char_id','$fn');";
		    mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
		  }
		}
	      } else if ($rawdata == "'nd'"){
		  continue;
	      }

//		translation
//		NULL	not determined/no data
//		-1	no growth
//		0	no (most not)/minus
//		1	yes (most do)/plus
//		2	variable
// 	      import raw data
	      $rawhtml = trim(stripslashes(mysql_escape_string(charset_decode_utf_8($rawdata))),"'");
	      $conv_from = array('/^[dv]$/','/^ng$/','/^\+$/','/^\&\#8722;$/','/^\(\&\#8722\;\)$/','/^\(\+\)$/','/^ng$/','/^[vd]\&\#8722;$/','/^[vd]\+$/','/^\-/$','/^[vd]\-$/');
	      $conv_to   = array('2','-2','1','0','2','2','-2','2','2','0','2');
	      $trans = preg_replace($conv_from,$conv_to,$rawhtml);

	      if (! mysql_num_rows(mysql_query("SELECT `src_id` FROM `$tbl` WHERE `src_id`='$src_id' AND `taxa_id`='$taxa_id' AND `char_id`='$char_id' AND `raw`='$rawhtml';"))) {
		echo "<br>inserting data $rawhtml\t$trans\t$src_id\t$taxa_id\t$char_id\n";
		$insert = "INSERT INTO $tbl (`src_id`,`taxa_id`,`char_id`,`raw`,`data`) VALUES ('$src_id','$taxa_id','$char_id','$rawhtml','$trans');";
		mysql_query($insert) or die(mysql_error() . " on $insert\n<br>");
	      }
	    }
	}

}#end while
fclose($handle);
}#end foreach

## Close database connection when finished ## 
mysql_close($mycon);
echo "<br>done\n";


function charset_decode_utf_8 ($string) { 
      /* Only do the slow convert if there are 8-bit characters */ 
    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */ 
    if (! ereg("[\200-\237]", $string) and ! ereg("[\241-\377]", $string)) 
        return htmlentities($string);

    // decode three byte unicode characters 
    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",        
    "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'",    
    $string); 

#hex version
//     $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",        
//     "'\x{'.dechex((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).'}",    
//     $string);

    // decode two byte unicode characters 
    $string = preg_replace("/([\300-\337])([\200-\277])/e", 
    "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", 
    $string); 

#hex version
//     $string = preg_replace("/([\300-\337])([\200-\277])/e", 
//     "'\x{'.dechex((ord('\\1')-192)*64+(ord('\\2')-128)).'{'", 
//     $string);

    return $string; 
} 
?>
</body>
</html>