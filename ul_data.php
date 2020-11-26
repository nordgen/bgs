<?php
global $Zdb;

$ul = (isset($_REQUEST['ul']) && $_REQUEST['ul'] != "")
    ? $_REQUEST['ul']
    : "";


//ev. move database fetch to index.php, and loop in array here


?>
<!-- <link rel="stylesheet" type="text/css" href="style/main.css" > -->
<form name="ul_data_form" action="index.php">
    <input type="hidden" name="pg">
    <input type="hidden" name="act">
    <input type="hidden" name="ul" value="<?php echo $ul; ?>">
    <div id="sel_ul_div">
        <select name="ulsel" class="ul_sel" onchange="select_ul(this)">
            <option id="-" value="-">Choose UL file</option>
            <?php
            //Get all ul;

            $q = "select distinct u.value as ul, n.value as name from bgs_ul_data u, bgs_ul_data n where u.row_id=n.row_id and n.column_id=4 and u.column_id=3 order by ul";

            try {
                $rs = [];
                $rs = $Zdb->query($q)->getQueryResultSet();
                //echo "<br>".$q."<br>";
            } catch (exception $e) {
                echo "Error selecting ul: " . $e->getMessage();
                /*	Implement logging
                    logMess("E","error fetching tests, q:".$q);
                    if($debug){
                        logMess("D",$e->getMessage());
                        logMess("D",adodb_backtrace($e->gettrace()));
                    }
                */
            }
            foreach ($rs as $row) {
                if ($row['ul'] == $ul) {
                    $thisulname = $row['name'];
                    $selul = true;
                } else {
                    $selul = false;
                }
                ?>
                <option id="<?php echo $row['ul']; ?>"
                        value="<?php echo $row['ul']; ?>"<?php echo ($selul) ? " selected" : ""; ?>><?php echo $row['ul']; ?>
                    &nbsp;-&nbsp;<?php echo $row['name']; ?></option>
            <?php } ?>
    </select>
</div>

<?php
//if((!isset($ul_data) || count($ul_data)==0) && isset($ul_columns) && count($ul_columns) >0){
if (isset($ul_columns) && count($ul_columns) > 0) {
//Show columns
    ?>
    <div id="data_list_div">
        <?php
        //if(isset($ul_columns)){
        ?>
        <?php echo $ul . "&nbsp;-&nbsp;" . $thisulname; ?><br><span id="selColCbLab"><label for="selAllULColCB">Select all</label></span>&nbsp;&nbsp;&nbsp;<input
                id="selAllULColCB" type="checkbox" onclick="selAllULColumns()"><input type="button" value="Show"
                                                                                      onClick="showULData(this.form)"><input
                type="button" value="Export to Excel" onClick="exportULData(this.form)">
        <ul>
            <?php
            foreach ($ul_columns as $id => $name) {
                ?>
                <li id="ul_columns_li_<?php echo $id; ?>"
                    class="ul_columns_li ul_columns_li_unsel"><?php echo $name; ?></li>
            <?php } ?>
        </ul>
        <?php

        //Add new column
        if (hasAnyRole(array("ul_edit", "ul_admin")) && (isset($_REQUEST['ul']) && $_REQUEST['ul'] != "")) { ?>
            <div id="new_col_div"><select name="newulcol">
                    <option value="--">Add new column</option>
                    <?php
                    //add value for new column, get all not used columns
                    $q = "SELECT c.name AS colname,c.id AS colid FROM bgs_ul_data_column c ORDER BY c.ord";
                    try {
                        $rs = $Zdb->query($q)->getQueryResultSet();
                        //echo "<br>".$q."<br>";
                    } catch (exception $e) {
                        echo "Error selecting ul columnsfor not used list: " . $e->getMessage();
                    }
                    foreach ($rs as $row) {
                        if (in_array($row['colid'], array_keys($ul_columns))) {
                            continue;
                        } else { ?>
                            <option value="<?php echo $row['colid']; ?>"><?php echo $row['colname']; ?></option>
                            <?php
                        } //End in used list
                    }//End loop through all sections
                    ?>
                </select></div>
<?php
	} //End show add new column control
	
//} ?>
</div>
<?php } 
//elseif(isset($ul_data) && count($ul_data)>0){
?>
</form>
<div id="statusDiv"></div>
<div id="ul_data_div"></div>


