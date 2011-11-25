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

$query_taxa = "SELECT DISTINCT $table_taxa.taxon,$table_taxa.id FROM $table_taxa LEFT JOIN $table_data ON $table_taxa.id=$table_data.taxa_id WHERE $table_data.data IS NOT NULL";
$taxa = mysql_query($query_taxa) or die(mysql_error());
$taxa_hash = array();
while ($row = mysql_fetch_assoc($taxa)) {
    $taxa_hash[$row["id"]] = $row["taxon"];
}
asort($taxa_hash);

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

<div id="DataFormDiv">
<form id="DataForm" action="_dataform.php" method="post">
<div style="width:400px;float:left;">
<br>Find taxa:
<br>
<select id="taxa" name="taxa[]" multiple="multiple" class="multiselect">
<?php
foreach ($taxa_hash as $taxon_id => $taxon) {
    printf("<option value=\"%s\">%s</option>\n", $taxon_id, $taxon);
}
?>
</select>
</div>

<div style="width:400px;float:left;">
<br>Having phenotypes:
<br>
<select id="chars" name="chars[]" multiple="multiple" class="multiselect">
<?php
foreach ($char_hash as $char_id => $char) {
    printf("<option value=\"%s\">%s</option>\n", $char_id, $char);
}
?>
</select>
</div>

<div style="clear:both;">
<input type="radio" name="datatype" value="raw" checked>Raw Data<br>
<input type="radio" name="datatype" value="data">Nominal Data<br>
<input type="submit" value="Get Table" /> 
</div>

</form>
</div>

<br>
<div id="TableDiv"></div>
</body>