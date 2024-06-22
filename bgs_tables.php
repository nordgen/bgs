<?php
global $Zdb;

if (isset($_REQUEST['m']) && $_REQUEST['m'] != "") {
    $mode = $_REQUEST['m'];
} else {
    exit();
}

try {
    if(!isset($Zdb) || !(is_a($Zdb,'\nordgen\DbBatch\DbBatchInterface'))) {
        throw new Exception("No DB connection!");
    }

    //First get number of rows - this is so we dont have to have $ADODB_COUNTRECS to true, for performance that affects UL data
/*    $nloci = $Zdb->queryScalar(<<<SQL
SELECT count(*) FROM bgs_doc
SQL
);*/

    $q = <<<SQL
SELECT d.id, d.stock_number_int AS bgsnum, d.locus_name AS locnam, d.locus_symbol AS locsym, d.chrom_location AS cromloc, 
       d.bgn_page, d.bgn_volume 
FROM bgs_doc d 
SQL;
    $title = '';
    if ($mode == "bgs") {
        $q .= "ORDER BY bgsnum ASC";
        $title = "Listing of Barley Genetic Stock (BGS) descriptions.";
    } elseif ($mode == "loc") {

        $q .= "ORDER BY symbol_ord ASC";
        $title = "Alphabetic listing of Barley Genetic Stock (BGS) descriptions for loci.";
    }

    $nloci = $Zdb->query($q)->getQueryRecordCount();

    $rs = [];
    $rs = $Zdb->getQueryResult();
    //$rs = $Zdb->query($q)->getQueryResult();
    //echo "<br>".$q."<br>";
} catch (exception $e) {
    echo (isset($q) ? ("Error selecting bgs data, \$q: " . $q . " - ") : "") . $e->getMessage();
}	

$firstrow=true;
$firstitem=true; //seperate flag for this, only applies to loggedin edit
$n=0;
foreach ($rs ?? [] as $row) {
	$n++;
	//First print header
	if ($firstrow) {
		$firstrow=false;
		
	if ($mode=="loc" && hasAnyRole(array("bgs_edit","bgs_admin"))) { ?>
        <form style="display:inline;" name="rowmoveform" method="post" action="index.php"><input type="hidden"
                                                                                                 name="act"><input
                type="hidden" name="docid"><input type="hidden" name="pg" value="bgs_tables"><input type="hidden"
                                                                                                    name="m"
                                                                                                    value="loc">
    <?php } //End add form tag ?>

<div id="tablestitle"><?php echo ($title ?? ''); ?></div>
        <table id="bgstab" class="minimize">
        <tr class="bgstabhead">
<?php if($mode=="bgs"){ ?>
<th>BGS no.</th><th>Locus symbol</th>
<?php }elseif($mode=="loc"){ ?>
<th>Locus symbol</th><th>BGS no.</th>
<?php } ?>
<th>Chr. loc.</th><th>Locus name or phenotype</th><th>BGN&nbsp;vol:page</th>
<?php if($mode=="loc" && hasAnyRole(array("bgs_edit","bgs_admin"))){ ?><th>&nbsp;</th><?php } ?>
</tr>
<?php } //end firstrow, add header ?>

<tr>
<?php if($mode=="bgs"){ ?>
<td><a class="button" href="index.php?pg=bgs_show&docid=<?php echo $row['id']; ?>" target="_self"><?php echo $row['bgsnum']; ?></a></td><td><?php echo $row['locsym']; ?></td>
<?php }elseif($mode=="loc"){ ?>
<td><?php echo $row['locsym']; ?></td><td><a class="button" href="index.php?pg=bgs_show&docid=<?php echo $row['id']; ?>" target="_self"><?php echo $row['bgsnum']; ?></a></td>
<?php } ?>
<td><?php echo $row['cromloc']; ?></td><td><?php echo $row['locnam']; ?></td><td><a class="button" href="https://wheat.pw.usda.gov/ggpages/bgn/<?php echo $row['bgn_volume']; ?>/index.html" target="_blank"><?php echo $row['bgn_volume']; ?>:<?php echo $row['bgn_page']; ?></a></td>

<?php if($mode=="loc" && hasAnyRole(array("bgs_edit","bgs_admin"))){ ?>
<td>
<?php if($firstitem){
	$firstitem=false;
}else{ ?>
<a class="button up" href='javascript:locusUp(document.rowmoveform,<?php echo $row['id']; ?>)'></a>&nbsp;&nbsp;
<?php } //End first locus or not
if($n < ($nloci ?? 0)){
?>
<a class="button down" href='javascript:locusDown(document.rowmoveform,<?php echo $row['id']; ?>)'></a>
<?php } //End last locus or not ?>
</td>
<?php } //End up & down buttons ?>

</tr>
<?php } //End loop through all rows `?>
</table>
<?php if($mode=="loc" && hasAnyRole(array("bgs_edit","bgs_admin"))){ ?></form><?php } ?>