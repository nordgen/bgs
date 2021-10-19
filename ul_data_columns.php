<?php
error_reporting(E_ALL);
session_start();
require_once("./vendor/autoload.php"); // Lazy load libraries through composer
require_once("./system/config.php");
require_once("./system/initdb.php");
require_once("./system/common.php");
global $Zdb;

$ul = '';
if (isset($_REQUEST['ul']) && $_REQUEST['ul'] != "") {
    $ul = $_REQUEST['ul'];
} else {
    echo "error:no_ul";
}
$colids = '';
if (isset($_REQUEST['colids']) && $_REQUEST['colids'] != "") {
    $colids = $_REQUEST['colids'];
} else {
    echo "error:no_colids";
}

if (isset($_REQUEST['newcolid']) && $_REQUEST['newcolid'] != "") {
    $newcolid = $_REQUEST['newcolid'];
} else {
    $newcolid = -1;
}

$colidarr = explode(";",$colids);

//Get all data cells for ul / selected columns
$q = "select distinct c.name as colname, c.id as colid, c.ord, d.value, d.row_id FROM bgs_ul_data d, bgs_ul_data u, bgs_ul_data_column c WHERE d.row_id=u.row_id AND u.column_id=3 AND u.value='".$ul."' AND c.id=d.column_id AND d.column_id IN(";
$first=true;

foreach($colidarr as $cid){
	if($first){
		$first=false;
	}else{
		$q .= ",";
	}
	$q .= $cid;
}
 
$q .= ") order by d.row_id, c.ord";
try {
    $rs = [];
    if (empty($ul)) {
        throw new Exception("no_ul");
    }
    $rs = $Zdb->query($q)->getQueryResultSet();
} catch (exception $e) {
    echo "Error selecting ul: " . $e->getMessage();
}

//Now put it in a structure that help us draw the table without requiring values for all row-column nodes
$alldata = array();
$cols_order = array();	
	foreach($rs as $row){		
		if(!isset($alldata[$row['row_id']])){
			$alldata[$row['row_id']] = array();
		}
		$alldata[$row['row_id']][$row['colid']]	= $row['value'];
		
		//We also need all coluimn names and colids in rigth order from the resultset. Colids list in request can have any order
		//If there are no data we will not get column names and won't print any column row. This is OK here because we sill not have any empty UL sheets. If we should want column row also for empty sheets we need to make a special query for column names
		if(!in_array($row['colid'],array_keys($cols_order))){
			$cols_order[$row['colid']] = $row['colname'];
			
			//***** Save coded value names here also, for columns of data_type=1

		}
	} //Loop through results
$newcolname = '';
if($newcolid > 0) { //We also have a new column, get its name
    $q = "select distinct c.name as colname FROM bgs_ul_data_column c WHERE c.id=" . $newcolid;
    try {
        $newcolname = $Zdb->queryScalar($q);
    } catch (exception $e) {
        echo "Error selecting name, $q: " . $q . " - " . $e->getMessage();
    }
}

?>

    <form name="ul_data_column_form" action="index.php">
        <input type="hidden" name="pg">
        <input type="hidden" name="act">
        <input type="hidden" name="ul" value="<?php echo $ul; ?>">
        <input type="hidden" name="colids" value="<?php echo $colids; ?>">

        <?php
        $nrow = 0;
        if (count($alldata) > 0) { //We have data
            ?>
            <table id="ul_data_table" class="minimize">
                <tr class="ul_data_header">

                    <?php //First print header row
                    foreach ($cols_order as $colid => $colname) {
                        echo "<td>" . $colname . "</td>";
                    }

                    //We have a new column
                    if ($newcolid > 0) {
                        echo "<td>" . $newcolname . "</td>";
                    }
                    ?>
                </tr>
<?php
	foreach($alldata as $rowid => $row){
		$nrow++;
		echo "<tr>";
		
		foreach($cols_order as $colid=> $colname){
			if(isset($alldata[$rowid][$colid])){ 
            	$value = $alldata[$rowid][$colid];	
			}
			else{ //No data for this row & column
				$value = "&nbsp;";		
			}

			echo "<td id='".$rowid."_".$colid."' class='ul_data_cell'>".$value."</td>";    
		} //End loop through all columns in right order
		
		//We have a new column
		if($newcolid > 0){
			echo "<td id='".$rowid."_".$newcolid."' class='ul_data_cell'>&nbsp;</td>";
		}

		echo "</tr>";
	}//End loop through all rows
?>
</table>
<?php
} //End we have data ?>
</form>
<?php
//Set editable if user is authorized
if(hasAnyRole(array("ul_admin"))){  ?>
<script type="text/javascript">
openULDataForEdit();
</script>
<?php } ?>