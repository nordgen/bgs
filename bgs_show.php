<?php
global $Zdb;
$did = '';
if (isset($_REQUEST['bgs']) && $_REQUEST['bgs'] != "") {
    $bgsnum = $_REQUEST['bgs'];
} elseif (isset($_REQUEST['locus']) && $_REQUEST['locus'] != "") {
    $loc = $_REQUEST['locus'];
} elseif (isset($_REQUEST['docid']) && $_REQUEST['docid'] != "") {
    $did = $_REQUEST['docid'];
} elseif (isset($docid) && $docid != "") {
    $did = $docid;
} else {
    exit();
}
//Get all data for this bgs
//First get number of rows - this is so we dont have to have $ADODB_COUNTRECS to true, for performance that affects UL data
$nsects = !empty($did) ? $Zdb->queryScalar("SELECT count(*) FROM bgs_section_in_doc WHERE docid=$1",[$did]) : null;


$q = <<<SQL
select d.id, d.name AS docname, d.stock_number_int, d.locus_name, d.locus_symbol, d.chrom_location, 
       ds.name AS section_title, sid.text, ds.id AS section_id, sid.id AS sidid, d.bgn_page, d.bgn_volume 
FROM bgs_doc d 
    JOIN bgs_section_in_doc sid ON (d.id=sid.docid) 
    JOIN bgs_docsection ds ON (ds.id=sid.docsectionid) 

SQL;

$params = [];
if(isset($did) && $did!=""){
	//We come in with docid
	$q .= <<<SQL
WHERE d.id=$1

SQL;

    $params[] = $did;
}
elseif(isset($bgsnum) && $bgsnum!=""){
	//We come in with bgsnum
    $q .= <<<SQL
WHERE d.stock_number_int=$1

SQL;

    $params[] = $bgsnum;
}

$q .= <<<SQL
ORDER BY sid.ord ASC
SQL;

$params = count($params)>0 ? $params : null;
try {
    $rs = [];
    $rs = $Zdb->query($q,$params)->getQueryResult();
    //echo "<br>".$q."<br>";
} catch (exception $e) {
    echo "Error selecting bgs data, \$q: " . $q . " - " . $e->getMessage();
}	
$firstrow=true;
$firstsect=true;
$oursectids = array(); //Used to filter out sections we don't have and might want to add at the bottom
//$nsects = $rs->RecordCount();
$n=0;
foreach($rs as $row){ 
	$n++;
	//First print header
	if($firstrow) {
        $firstrow = false;
        //$docname=$row['docname'];
        $stocknumint = $row['stock_number_int'];
        $locusname = $row['locus_name'];
        $locussymb = $row['locus_symbol'];
        $did = $row['id'];
        $docname = "BGS " . $stocknumint . ", " . $locusname . ", " . $locussymb;
        $chromloc = $row['chrom_location'];
        $bgn_volume = $row['bgn_volume'];
        $bgn_page = $row['bgn_page'];
        $bgn = $bgn_volume . ":" . $bgn_page;
        ?>
        <h1 style="display:inline;"><?php echo $docname; ?></h1>
        <form style="float:right;" action="#" name="exportpdf"><a
                    href="http://wheat.pw.usda.gov/ggpages/bgn/<?php echo $bgn_volume; ?>/index.html" class="bgnlink"
                    target="_blank">BGN&nbsp;&nbsp;<?php echo $bgn; ?></a><input type="button" value="Export to PDF"
                                                                                 onClick="exportPdf(<?php echo $stocknumint; ?>)">
        </form>
        <div>Stock number:&nbsp;BGS&nbsp;<span class="headspan"
                                               id="head_stock_number_int"><?php echo $stocknumint; ?></span>
            <?php if (hasAnyRole(array("bgs_edit", "bgs_admin"))) { ?>
                <span class="edit_head" id="edit_stock_number_int">[<a
                            href='javascript:editHead("stock_number_int",<?php echo $did; ?>)'>Edit</a>]</span>
            <?php }  //End has editrights
            ?></div>
        <div>Locus name:&nbsp;<span class="headspan" id="head_locus_name"><?php echo $locusname; ?></span>
            <?php if (hasAnyRole(array("bgs_edit", "bgs_admin"))) { ?>
                <span class="edit_head" id="edit_locus_name">[<a
                            href='javascript:editHead("locus_name",<?php echo $did; ?>)'>Edit</a>]</span>
            <?php }  //End has editrights
            ?></div>
        <div>Locus symbol:&nbsp;<span class="headspan" id="head_locus_symbol"><?php echo $locussymb; ?></span>
            <?php if (hasAnyRole(array("bgs_edit", "bgs_admin"))) { ?>
                <span class="edit_head" id="edit_locus_symbol">[<a
                            href='javascript:editHead("locus_symbol",<?php echo $did; ?>)'>Edit</a>]</span>
            <?php }  //End has editrights
            ?></div>
    <?php } //end special for first row
    $oursectids[] = $row['section_id'];
    ?>

    <h2><?php echo $row['section_title']; ?></h2>
    <?php if (hasAnyRole(array("bgs_edit", "bgs_admin"))) { ?>
        <span class="section_button" id="sectbut_<?php echo $row['sidid']; ?>">[<a
                    href='javascript:editSection(<?php echo $row['sidid']; ?>)'>Edit</a>]</span>
        <form style="display:inline;" name="sectmoveform_<?php echo $row['sidid']; ?>" method="post" action="index.php">
            <input type="hidden" name="act"><input type="hidden" name="sidid"
                                                   value="<?php echo $row['sidid']; ?>"><input type="hidden"
                                                                                               name="docid"
                                                                                               value="<?php echo $did; ?>"><input
                    type="hidden" name="pg" value="bgs_show">
            <span class="section_move">
<?php if ($firstsect) {
    $firstsect = false;
} else { ?>
    [<a href='javascript:sectionUp(document.sectmoveform_<?php echo $row['sidid']; ?>)'>Up</a>]&nbsp;&nbsp;
<?php } //End first section or not
if ($n < $nsects) {
    ?>
    [<a href='javascript:sectionDown(document.sectmoveform_<?php echo $row['sidid']; ?>)'>Down</a>]
<?php } //End last section or not ?>
</span></form>
    <?php } //End has editrights
    ?>

    <div class="sectiondiv" id="section_<?php echo $row['sidid']; ?>">
        <?php
        echo str_replace("\n", "<br>", htmlentities($row['text']));
?></div>
<?php if($row['section_id']==3) { //Description, add images here

        //Get all image data for this bgs
        $q = <<<SQL
select i.filename, i.caption 
from bgs_doc d 
    join bgs_image_mapping im on (im.foreign_key_value=to_char(d.id,'FM999999999999')) 
    join bgs_image i on (im.imageid=i.id) 
where im.foreign_table='bgs_doc' 
  and im.foreign_key_name='id' 
  and d.id=$1 
order by im.ord asc
SQL;

        try {
            $rs2 = [];
            $rs2 = $Zdb->query($q,[$did])->getQueryResult();
            //echo "<br>".$q."<br>";
        } catch (exception $e) {
            echo "Error selecting getting images, \$q: " . $q . " - " . $e->getMessage();
        }
        $noimg = true;
        foreach ($rs2 as $row2) {
            if ($noimg == true) { //First image, add edit link
                $noimg = false;
                if (hasAnyRole(array("bgs_edit", "bgs_admin"))) { ?>
                    <div class="edtimgdiv">[<a href='index.php?pg=bgs_imgadmin&docid=<?php echo $did; ?>'>Edit
                            images</a>]
                    </div>
                <?php } //End has editrights
            }
            ?>
            <a href="javascript:showLargeImg('images/large/<?php echo $row2['filename']; ?>','<?php echo $row2['caption']; ?>')"
               id="a_<?php echo $row2['filename']; ?>" title="<?php echo $row2['caption']; ?>"><img alt=""
                                                                                                    src="images/small/<?php echo $row2['filename']; ?>"
                                                                                                    style="border:solid #000000 2px;"
                                                                                                    id="img_<?php echo $row2['filename']; ?>"></a>

        <?php } //end foreach
        if ($noimg) {
            if (hasAnyRole(array("bgs_edit", "bgs_admin"))) { ?>
                <div class="edtimgdiv">[<a href='index.php?pg=bgs_imgadmin&docid=<?php echo $did; ?>'>Add images</a>]
                </div>
            <?php } //End has editrights
            echo "No images"; ?>
        <?php } //end no img
    } //End section==3
} //End loop through all sections ?>

<?php if(hasAllRoles(array("user_admin"))) {
	
	if($n==0) { //We have added a new BGS document or removed all sections display the header that is missing because there were no sections

        $q = "select d.name AS docname, d.stock_number_int, d.locus_name, d.locus_symbol FROM bgs_doc d WHERE d.id=$1";
        try {
            $rs = [];
            $rs = $Zdb->query($q,[$did])->getQueryResult();
            //echo "<br>".$q."<br>";
        } catch (exception $e) {
            echo "Error selecting bgs header data, \$q: " . $q . " - " . $e->getMessage();
        }
        foreach ($rs as $row) {
            $stocknumint = $row['stock_number_int'];
            $locusname = $row['locus_name'];
            $locussymb = $row['locus_symbol'];
            $docname = $row['docname'];
        }
        ?>
        <h1 style="display:inline;"><?php echo $docname; ?></h1>
        <div>Stock number:&nbsp;BGS&nbsp;<span class="headspan"
                                               id="head_stock_number_int"><?php echo $stocknumint; ?></span>
            <span class="edit_head" id="edit_stock_number_int">[<a
                        href='javascript:editHead("stock_number_int",<?php echo $did; ?>)'>Edit</a>]</span>
        </div>
        <div>Locus name:&nbsp;<span class="headspan" id="head_locus_name"><?php echo $locusname; ?></span>
            <span class="edit_head" id="edit_locus_name">[<a
                        href='javascript:editHead("locus_name",<?php echo $did; ?>)'>Edit</a>]</span>
        </div>
        <div>Locus symbol:&nbsp;<span class="headspan" id="head_locus_symbol"><?php echo $locussymb; ?></span>
            <span class="edit_head" id="edit_locus_symbol">[<a
                        href='javascript:editHead("locus_symbol",<?php echo $did; ?>)'>Edit</a>]</span>
        </div>
    <?php } //End display header, no sections

//Edit chromosome location field for tables

    //Add and remove sections
    //Get all sections that we don't have
    $q = "SELECT ds.name AS section_title, ds.id AS section_id FROM bgs_docsection ds ORDER BY ds.id";
    try {
        $rs2 = [];
        $rs2 = $Zdb->query($q)->getQueryResult();
        //echo "<br>".$q."<br>";
    } catch (exception $e) {
        echo "Error selecting sections to add, \$q: " . $q . " - " . $e->getMessage();
    }
    ?>
    <form name="sectioneditform" method="post" action="index.php">
        <input type="hidden" name="act">
        <input type="hidden" name="sid">
        <input type="hidden" name="docid" value="<?php echo $did; ?>">
        <input type="hidden" name="pg" value="bgs_show">
        <div class="editblock">
            <div class="edit_section"><label for="chrom_location">Edit chromosome location for tables</label></div>
            <input id="chrom_location" type="text" size="8" name="chrom_location" value="<?php echo $chromloc; ?>">
            <input type="button" value="Update chromosome location" onclick="updateChromLoc(this.form)">
        </div>
        <div class="editblock">
            <div class="edit_section"><label for="bgn_volume">Edit BGN page and volume</label></div>
            <input id="bgn_volume" type="text" size="8" name="bgn_volume" value="<?php echo $bgn_volume; ?>">:<input
                    type="text" size="8" name="bgn_page" value="<?php echo $bgn_page; ?>">
            <input type="button" value="Update BGN page and volume" onclick="updateBGN(this.form)">
        </div>
        <div class="editblock">
            <div class="edit_section"><label for="addSectSel">Add section to document</label></div>
            <select id="addSectSel" name="addSectSel">
                <?php
                foreach ($rs2 as $row) { //Resultset with all sections, filter out the ones we have
                    if (in_array($row['section_id'], $oursectids)) {
                        continue;
                    }
                    echo "<option value='" . $row['section_id'] . "'>" . $row['section_title'] . "</option>\n";
                } ?>
            </select>
            <input type="button" value="Add section" onclick="addSectionToDoc(this.form)">
        </div>
        <div class="editblock">
            <div class="edit_section">Create new section</div>
            <input type="text" size="60" name="newsection" placeholder="Write name of new section">
            <input type="button" value="Add new section" onclick="addNewSectionToDoc(this.form)">
        </div>
        <div class="editblock">
            <div class="edit_section"><label for="remSectSel">Remove section from document</label></div>
            <select id="remSectSel" name="remSectSel">
                <?php
                foreach ($rs as $row) { //Resultset with all sections we have from above
                    echo "<option value='" . $row['sidid'] . "'>" . $row['section_title'] . "</option>\n";
                } ?>
            </select>
            <input type="button" value="Remove section" onclick="removeSectionFromDoc(this.form)">
        </div>
        <div class="editblock">
            <div class="edit_section2">Create new BGS document</div>
            <table id="addnewbgstab" style="border:0">
                <tr>
                    <td><label for="newbgsnum">New BGS number:</label></td>
                    <td><input id="newbgsnum" type="text" size="4" name="newbgsnum"><span class="inputhelptxt">Only number without "BGS" prefix.</span>
                    </td>
                </tr>
                <tr>
                    <td><label for="newlocusname">Locus name:</label></td>
                    <td><input id="newlocusname" type="text" size="12" name="newlocusname"></td>
                </tr>
                <tr>
                    <td><label for="newlocussymbol">Locus symbol:</label></td>
                    <td><input id="newlocussymbol" type="text" size="12" name="newlocussymbol"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="button" value="Create new BGS document"
                                           onclick="createNewBGSDoc(this.form)"></td>
                </tr>
            </table>
        </div>
        <div id="deldocdiv">[<a
                    href='javascript:delDoc(document.sectioneditform,"<?php echo $docname; ?>")'>Delete <?php echo $docname; ?></a>]
        </div>
    </form>
<?php } //End has editrights  ?>
