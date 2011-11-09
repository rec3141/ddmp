<?php header('Content-Type: text/html; charset=utf-8' );?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<body>
<?php 
error_reporting(E_ALL);

include("/home/reric/reric.org/loadddmp.php");
ConnectToDatabase();

$table_class = 'ddmp_class';
$table_prop = 'ddmp_prop';
$table_char = 'ddmp_char';
$table_taxa = 'ddmp_taxa';
$table_src = 'ddmp_src';
$table_data = 'ddmp_data';
$table_fn = 'ddmp_fn';
$table_fndata = 'ddmp_fndata';
$table_ncbi = 'ncbi_names';
$table_chebi = 'chebi_compounds';

// get classes
//   INSERT INTO ddmp_class if not present
// get properties
//   INSERT INTO ddmp_prop if not present
//   link chemicals to chebi_compounds
// get characteristics
//   INSERT INTO ddmp_char if not present
// get table
//   INSERT INTO ddmp_src if not present
// get taxa names
//   INSERT INTO ddmp_taxa if not present
//   link taxa to ncbi_names
// get data
//   INSERT INTO ddmp_data if not present



## CSV file to read in ##
foreach (glob("import.csv") as $csvfile) {
  echo "importing $csvfile\n<br>";
  $classlist = array();
  $proplist = array();
  $charlist = array();
  // data definition: [ncbi_taxid genus species ...]
  $handle = fopen($csvfile, "r") or die("couldn't open file $csvfile\n");
  while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
	$tbl = array_shift($data);
        $num = count($data);
	if ($tbl == $table_class) { #parse classes names, add if not exist
	    echo "importing classes\n<br>";
	    $classlist = $data;
	    array_shift($classlist);array_shift($classlist);
	    $uniqclass = array_unique($classlist);
	    foreach ($uniqclass as $class) {
	      $class = mysql_real_escape_string($class);
	      if (! mysql_num_rows(mysql_query("SELECT `id` FROM `$tbl` WHERE (`class`='$class' );")) ) {
		  $insert = "INSERT INTO `$tbl` (`class`) VALUES ( '$class' );";
		  mysql_query($insert) or die(mysql_error());
		}
	      }
        } else if ($tbl == $table_prop) {
	    echo "importing properties\n<br>";
	    $proplist = $data;
	    array_shift($proplist);array_shift($proplist);
	    $numprops = count($proplist);
	    $uniqprop = array_unique($proplist);
	    foreach ($uniqprop as $prop) {
	      $prop = mysql_real_escape_string($prop);
	      if (! mysql_num_rows(mysql_query("SELECT `id` FROM `$tbl` WHERE (`property`='$prop' );")) ) {
		  $insert = "INSERT INTO `$tbl` (`property`) VALUES ( '$prop' );";
		  mysql_query($insert) or die(mysql_error());
	      }
	    }

	    #populate characteristics
	    echo "populating characteristics\n<br>";
	    for ($i=0;$i<$numprops;$i++) {
	      $charprop = mysql_real_escape_string($proplist[$i]);
	      $charclass = mysql_real_escape_string($classlist[$i]);
	      $propid = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `$table_prop` WHERE (`property`='$charprop' );"));
	      $propid = $propid{'id'};
	      $classid = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `$table_class` WHERE (`class`='$charclass' );"));
	      $classid = $classid{'id'};
	      if (! mysql_num_rows(mysql_query("SELECT `id` FROM `$table_char` WHERE (`propid`='$propid' AND `classid`='$classid');")) ) {
		  $insert = "INSERT INTO `$table_char` (`propid`,`classid`) VALUES ( '$propid','$classid' );";
		  mysql_query($insert) or die(mysql_error());
	      }
	    }
	} else if ($tbl == $table_src) {
	    echo "importing sources\n<br>";
	      $source = mysql_real_escape_string($data[0]);
	      $desc = mysql_real_escape_string($data[1]);
	      if ($srcrows = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `$tbl` WHERE (`source`='$source');")) ) {
/*		$srcid = $srcrows{'id'};
		print_r($srcrows); echo "source1 id: $srcid, source: $source\n<br>";*/
	      } else {
		  $insert = "INSERT INTO `$tbl` (`source`, `desc`) VALUES ( '$source', '$desc');";
		  mysql_query($insert) or die(mysql_error());
	      }
	} else if ($tbl == $table_fn) {
	      echo "importing footnotes\n<br>";
	      $source = mysql_real_escape_string($data[0]);
	      $fn = mysql_real_escape_string($data[1]);
	      $fntext = mysql_real_escape_string($data[2]);
	      $srcrows = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `$table_src` WHERE (`source`='$source');"));
	      $srcid = $srcrows{'id'};
	      if (! mysql_num_rows(mysql_query("SELECT * FROM `$tbl` WHERE (`srcid`='$srcid' AND `fn`='$fn');")) ) {
		  $insert = "INSERT INTO `$tbl` (`srcid`,`fn`,`text`) VALUES ( '$srcid', '$fn', '$fntext' );";
		  mysql_query($insert) or die(mysql_error());
	      }
	} else if ($tbl == $table_data){
/*	    $table = mysql_real_escape_string($data[0]);
	    $taxon = mysql_real_escape_string($data[1]);
	    $id = mysql_real_escape_string($data[2]);
// 	    echo "ID $cleanid\n<br>";
	    if (strtolower(rtrim($cleanid)) == "NA") {
	      $cleanid = mysql_real_escape_string(md5("$my_taxon"));
	    }
	    for ($i=0; $i < $num; $i++) {
	      $cleancomponent = mysql_real_escape_string($propnames[$i]);
	      $cleantablename = mysql_real_escape_string($classnames[$i]);
// 	      echo "dirty $data[$i]\n<br>";
	      // Escape your data before using it with parseNull()
	      preg_match_all('/![a-z][a-z]?/', $data[$i], $fn);
	      $cleanfn = mysql_real_escape_string(implode(' ', $fn[0]));
	      preg_replace('/![a-z][a-z]?/', '', $data[$i]);
	      $cleandata = mysql_real_escape_string($data[$i]);
	      echo "clean $cleandata\n<br>";
	      if ($cleandata == 'NULL') {continue;}
	      $query = "SELECT * FROM `$cleantablename` WHERE (`ncbi_taxid`='$cleanid' AND `bergeys`='$cleantable' AND `component`='$cleancomponent');";
	      echo "$query\n<br>";
	      $result = mysql_query($query) or die(mysql_error());

	      if (mysql_num_rows($result) ) {
		$update = "UPDATE `$cleantablename` SET `data`='$cleandata' AND `footnotes`='$cleanfn' WHERE (`ncbi_taxid` ='$cleanid' AND `bergeys`='$cleantable' AND `component`='$cleancomponent');"; #$my_data
		echo "$update\n<br>";
		$upresult = mysql_query($update) or die(mysql_error());
	      } else {
// 		if ($i==0) {
		  $insert = "INSERT INTO `$cleantablename` (`bergeys`, `ncbi_taxid`, `component`, `data`, `footnotes`) VALUES ( '$cleantable', '$cleanid', '$cleancomponent', '$cleandata', '$cleanfn');";
// 		} 
else {
		  $insert = "INSERT INTO `$classnames[$i]` (`ncbi_taxid`, `$propnames[$i]`) VALUES ( " . parseNull($cleanid) . ", " . parseNull($my_data) . " );";
		}
		echo "$insert\n<br>";
		$inresult = mysql_query($insert) or die(mysql_error());
	      }*/
// 	print_r($data); echo "<br>\n";
  }
}#end while
fclose($handle);
}

## Close database connection when finished ## 
mysql_close($mycon);




#SUBROUTINES
// function parseNull($data) {
// // Be sure your data is escaped before you use this function
//  if (rtrim($data) != "") {
//   if (mb_strtolower(rtrim($data)) == "null") {
//     return "NULL";
//   } else {return "'" . $data . "'";}
//  } else {return "NULL";}
// }

/*function add_column_if_not_exist($table, $db, $column, $column_attr = "VARCHAR( 255 )" ){ # NULL
    $exists = false;
    $columns = mysql_query("show columns from $table in $db");
    while($c = mysql_fetch_assoc($columns)){
        if($c['Field'] == $column){
            $exists = true;
            break;
        }
    }
    if(!$exists){
// 	echo "adding $column to $table<br>\n";
        mysql_query("ALTER TABLE `$table` ADD `$column` $column_attr;") or die(mysql_error());
    }
}*/
?>
</body>
</html>