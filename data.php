<?php
global $Zdb;
if (isset($_REQUEST['ul']) && $_REQUEST['ul'] != "") {
    $ul = $_REQUEST['ul'];
} else {
    $ul = "";
}

//ev. move database fetch to index.php, and loop in array here

?>

<link rel="stylesheet" type="text/css" href="style/main.css">
<form name="bgs_data_form" action="index.php">
    <input type="hidden" name="pg">
    <input type="hidden" name="act">
    <input type="hidden" name="ul" value="<?php echo $ul; ?>">
    <p><br>
        <label for="ul">UL</label> &nbsp;
        <select id="ul" name="ul" class="ul_sel" onchange="select_ul(this)">
            <option id="-" value="-">Choose UL file</option>
            <?php
            //Get all ul    ;

            $q = "select distinct u.value as ul, n.value as name from bgs_data u, bgs_data n where u.row_id=n.row_id and n.column_id=4 and u.column_id=3 order by ul";

            try {
                $rs = $Zdb->query($q)->getQueryResult();
                //echo "<br>".$q."<br>";
            } catch (exception $e) {
                echo "Error selecting ul: " . $e->getMessage();
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
        &nbsp;&nbsp;&nbsp;
        <label for="mutant_subgroups">Column</label> &nbsp;
        <select id="mutant_subgroups" name="mutant_subgroups">
            <option id="all_subgroups">All subgroups</option>
            <?php
            //Get all subgroups
            $q = "SELECT * FROM bgs_subgroups ORDER BY number";

            try {
                $rs = $Zdb->query($q)->getQueryResult();
                //echo "<br>".$q."<br>";
            } catch (exception $e) {
                echo "Error selecting subgroups: " . $e->getMessage();
                /*	Implement logging
                    logMess("E","error fetching tests, q:".$q);
                    if($debug){
                        logMess("D",$e->getMessage());
                        logMess("D",adodb_backtrace($e->gettrace()));
                    }
                */
            }
foreach($rs as $row){ ?>
      <option id="<?php echo $row['id']; ?>"><?php echo $row['number']; ?>.&nbsp;<?php echo $row['name']; ?></option>	
 <?php } ?>
    </select>
  &nbsp;

<div id="data_list_div">  
  <?php
  	if(isset($ul_columns)){
  ?>
    <label for="select_all">Columns in <?php echo $ul . "&nbsp;-&nbsp;" . $thisulname; ?>&nbsp;&nbsp;Select
        all</label><input id="select_all" type="checkbox" onclick="selAllULColumns(this)">
    <ul>
        <?php
        foreach ($ul_columns as $id => $name) {
            ?>
            <li id="ul_columns_li_<?php echo $id; ?>"
                class="ul_columns_li ul_columns_li_unsel"><?php echo $name; ?></li>
        <?php }
        }
?>
</div>
</form>