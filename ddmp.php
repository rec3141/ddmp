<?php header('Content-Type: text/html; charset=utf-8' );?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
<link type="text/css" href="css/ui.multiselect.css" rel="stylesheet" /> 
<!-- <link rel="stylesheet" href="css/common.css" type="text/css" />  -->
<link type="text/css" rel="stylesheet" href="./css/smoothness/jquery-ui-1.8.16.custom.css" /> 

<script type="text/javascript" src="./js/jquery/jquery-1.4.2.min.js"></script> 
<!-- <script type="text/javascript" src="./js/jquery/jquery.js"></script> -->
<!-- <script type="text/javascript" src="js/jquery/jquery.tmpl.1.1.1.js"></script>  -->
<!-- <script type="text/javascript" src="js/jquery/jquery.blockUI.js"></script>  -->
<script type="text/javascript" src="./js/jquery-ui/jquery-ui.js"></script>
<script type="text/javascript" src="./js/jquery/jquery.form.js"></script>
<!-- <script type="text/javascript" src="./js/jquery-ui/ui.multiselect.js"></script> -->
<script type="text/javascript" src="./js/table2csv.js" > </script> 
<script type="text/javascript">
  // prepare the form when the DOM is ready 
  $(document).ready(function() {
    $('#DataForm').ajaxForm({
      target: '#TableDiv',
      success: function() { 
	$('#TableDiv').fadeIn('slow')
      }
    });
  });

</script>

<?php
error_reporting(E_ALL);

include("/home/reric/reric.org/loadddmp.php");
ConnectToDatabase();

$table_class = 'ddmp_class';
$table_prop = 'ddmp_prop';
$table_char = 'ddmp_char';
$table_taxa = 'ddmp_taxa';
$table_data = 'ddmp_data';

// $query_taxa = "SELECT DISTINCT $table_taxa.taxon,$table_taxa.id FROM $table_taxa LEFT JOIN $table_data ON $table_taxa.id=$table_data.taxa_id WHERE $table_data.data IS NOT NULL";
// SELECT DISTINCT $table_taxa.taxon, $table_taxa.id FROM ncbi_nodes LEFT JOIN $table_taxa ON $table_taxa.ncbi_id = ncbi_nodes.taxonid WHERE ncbi_nodes.rank='species'
$query_taxa = "SELECT $table_taxa.taxon, $table_taxa.id FROM ncbi_nodes RIGHT JOIN $table_taxa ON $table_taxa.ncbi_id = ncbi_nodes.taxonid WHERE (ncbi_nodes.rank='species' OR ncbi_nodes.rank='subspecies' OR ncbi_nodes.rank='no rank');";
$taxa = mysql_query($query_taxa) or die(mysql_error());
$taxa_hash = array();
while ($row = mysql_fetch_assoc($taxa)) {
    $taxa_hash[$row["id"]] = $row["taxon"];
}
asort($taxa_hash);

// $query_genera = "SELECT DISTINCT $table_taxa.taxon, $table_taxa.id FROM ncbi_nodes LEFT JOIN $table_taxa ON $table_taxa.ncbi_id = ncbi_nodes.taxonid WHERE ncbi_nodes.rank='genus'";
$query_genera = "SELECT $table_taxa.taxon, $table_taxa.id FROM ncbi_nodes RIGHT JOIN $table_taxa ON $table_taxa.ncbi_id = ncbi_nodes.taxonid WHERE (ncbi_nodes.rank='genus' OR ncbi_nodes.rank='family' OR ncbi_nodes.rank='order' OR ncbi_nodes.rank='phylum');";
$genera = mysql_query($query_genera) or die(mysql_error());
$genera_hash = array();
while ($row = mysql_fetch_assoc($genera)) {
    $genera_hash[$row["id"]] = $row["taxon"];
}
asort($genera_hash);

// $query_others = "SELECT DISTINCT $table_taxa.taxon, $table_taxa.id FROM ncbi_nodes LEFT JOIN $table_taxa ON $table_taxa.ncbi_id = ncbi_nodes.taxonid WHERE (ncbi_nodes.rank!='genus' AND ncbi_nodes.rank!='species')";
$query_others = "SELECT $table_taxa.taxon, $table_taxa.id FROM ncbi_nodes RIGHT JOIN $table_taxa ON $table_taxa.ncbi_id = ncbi_nodes.taxonid WHERE (ncbi_nodes.rank IS NULL);";
$others = mysql_query($query_others) or die(mysql_error());
$others_hash = array();
while ($row = mysql_fetch_assoc($others)) {
    $others_hash[$row["id"]] = $row["taxon"];
}
asort($others_hash);

// GET PROPERTY LIST
$query_char = "SELECT $table_prop.property, $table_class.class, $table_char.id FROM 
$table_char LEFT JOIN $table_class ON $table_class.id=$table_char.class_id
LEFT JOIN $table_prop ON $table_prop.id=$table_char.prop_id;";

$chars = mysql_query($query_char) or die(mysql_error());
$char_hash = array();
while ($row = mysql_fetch_assoc($chars)) {
  $char_hash{$row["id"]} = ($row["class"] . ": " . $row["property"]);
}
asort($char_hash);


## Close database connection when finished ## 
mysql_close($mycon);


?>

</head>
<body>
<h1>'ddmp' is a work in progress.</h1>
<h2>you can download the current database on github: <a 
href="https://github.com/rec3141/ddmp">Digital Database of Microbial 
Phenotypes</a></h2>

<div id="DataFormDiv" style="width:100%;float:left;margin:50px 50px 50px 50px;">
<form id="DataForm" action="_dataform.php" method="post">
<div style="width:30%;float:left;">
<br>Find data for genera or higher ranks:
<br>
<select id="taxa" name="taxa[]" multiple="multiple" class="multiselect" size="10" style="width:90%">
<?php
foreach ($genera_hash as $taxon_id => $taxon) {
    if ($taxon === NULL) {continue;};
    printf("<option value=\"%s\">%s</option>\n", $taxon_id, $taxon);
}
?>
</select>
</div>

<div style="width:30%;float:left;">
<br>Find data for species or lower ranks:
<br>
<select id="taxa" name="taxa[]" multiple="multiple" class="multiselect" size="10" style="width:90%">
<?php
foreach ($taxa_hash as $taxon_id => $taxon) {
    if ($taxon === NULL) {continue;};
    printf("<option value=\"%s\">%s</option>\n", $taxon_id, $taxon);
}
?>
</select>
</div>

<div style="width:30%;float:left;">
<br>Find data for unknown ranks:
<br>
<select id="taxa" name="taxa[]" multiple="multiple" class="multiselect" size="10" style="width:90%">
<?php
foreach ($others_hash as $taxon_id => $taxon) {
    if ($taxon === NULL) {continue;};
    printf("<option value=\"%s\">%s</option>\n", $taxon_id, $taxon);
}
?>
</select>
</div>

<br>

<div style="width:100%;float:left;">
<div style="width:60%;floatLleft;">
<br>Having phenotypes:
<br>
<select id="chars" name="chars[]" multiple="multiple" class="multiselect" size="10" style="width:60%">
<?php
foreach ($char_hash as $char_id => $char) {
    printf("<option value=\"%s\">%s</option>\n", $char_id, $char);
}
?>
</select>
</div>
<div style="float:left;margin:20px 20px 20px 20px;">
<input type="radio" name="datatype" value="raw" checked>Raw Data<br>
<input type="radio" name="datatype" value="data">Nominal Data<br>
</div>
<div style="float:left;margin:20px 20px 20px 20px;">
<input type="radio" name="format" value="html" checked>HTML<br>
<input type="radio" name="format" value="csv">CSV<br>
</div>
<div style="float:left;margin:20px 20px 20px 20px;">
<input type="submit" value="Get Table" />
</div>

</div>


<br>
<div id="TableDiv" style="clear:both;float:left;width:100%;"></div>

</body>