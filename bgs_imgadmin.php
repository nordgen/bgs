<?php
if (!hasAnyRole(array("bgs_edit", "bgs_admin"))) {
    echo "Not authorized for page.";
    exit();
}
global $Zdb, $errortext, $errorinfo, $docname;

if (isset($uploaderror) && count($uploaderror) > 0) {
//Error reporting from file upload
    $noErrors = count($uploaderror);
    if ($noErrors > 0) { ?>
        <div class="uploaderrors">
            <h3>The following errors occured while uploading files:</h3>
            <ul>
                <?php for ($i = 0; $i < $noErrors; $i++) {
                    echo "<li>" . $errortext[$i] . "</li>";
                    echo "<li>" . $uploaderror[$i] . "</li>";
                    echo "<li>" . $errorinfo[$i] . "</li>";
                } ?>
            </ul>
        </div>
    <?php }
} //End isset uploaderror

if (isset($_REQUEST['docid']) && $_REQUEST['docid'] != "") {
    $did = $_REQUEST['docid'];
} else {
    echo "no docid!";
    exit();
}

//Get document name
$q = "select name as docname from bgs_doc where id=$1";
try {
    $docname = $Zdb->queryScalar($q,[$did]);
    //echo "<br>".$q."<br>";
} catch (exception $e) {
    echo "Error selecting name, \$q: " . $q . " - " . $e->getMessage();
}

//Get document and image data for this bgs
$q = <<<SQL
select d.stock_number_char, d.locus_name, d.locus_symbol, i.filename, i.caption, i.id as imgid, im.ord 
from bgs_doc d 
    join bgs_image_mapping im on (im.foreign_key_value=to_char(d.id,'FM999999999999')) 
    join bgs_image i on (im.imageid=i.id) 
where im.foreign_table='bgs_doc' 
  and im.foreign_key_name='id' 
  and d.id=$1 
order by im.ord
SQL;

try {
    $rs = [];
    $imgrows_html = '';
    $rs = $Zdb->query($q,[$did])->getQueryResult();
    //echo "<br>".$q."<br>";
} catch (exception $e) {
    echo "Error selecting images, \$q: " . $q . " - " . $e->getMessage();
}
$noimg = true;
$firstrow = true;

foreach ($rs as $row) {
    $noimg = false;
    $stocknumchar = $row['stock_number_char'];
    $locusname = $row['locus_name'];
    $locussymb = $row['locus_symbol'];
    $filename = $row['filename'];
    //$noextname = substr($filename,0,strlen($filename)-4);
    $imgid = $row['imgid'];
    $caption = $row['caption'];
    $ord = $row['ord'];


    if ($firstrow) {
        $firstrow = false;
    }
    // first row
    $imgrows_html .= <<<HTML
<tr>
<td>
<a href="javascript:showLargeImg('images/large/$filename','$caption')" id="a_$filename"><img alt="" src="images/small/$filename" style="border:solid #000000 2px;" id="img_$filename" ></a>
</td>
<td>
<label for="ord_$imgid">$filename
<br><br>
Order:</label>&nbsp;<input type="text" name="ord_$imgid" size="3" id="ord_$imgid" value="$ord" ><br><br>
<input type="button" value="Remove image" onclick="removeImage(this.form,$imgid,'$filename')">
</td>
<td>
<textarea cols="70" rows="2" id="caption_$imgid" name="caption_$imgid">$caption</textarea><br>
<input type="button" value="Update caption" onclick="updateImageCaption(this.form,$imgid)">
</td>
</tr>
HTML;

} //end foreach  ?>

<form name="editImagesForm" action="index.php?pg=bgs_imgadmin&docid=<?php echo $did; ?>" method="post"
      enctype="multipart/form-data">
    <input type="hidden" name="act">
    <input type="hidden" name="imgid">
    <div class="content-block">
        <span class="minimized-to-right"><a class="button" href="index.php?pg=bgs_show&docid=<?php echo $did; ?>">Return to main page</a></span>
        <h1 id="editimgh1">Edit images for <?php echo $docname; ?></h1>
    </div>
    <div class="content-block">
        <table class="edittab">
            <?= $imgrows_html ?>
            <tr>
                <td colspan="2">
                    <div class="head5"><label for="newimg">Upload new image</label></div>
                    <input id="newimg" name="newimg" type="file" size="25">
                    <br><br>
                    <label for="newimg_ord">Order:</label>&nbsp;<input type="text" name="newimg_ord" size="3"
                                                                       id="newimg_ord">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input
                            type="button" name="uplnewbut" value="Add new image" onClick="uploadNewImage(this.form)">
                </td>
                <td>
                    <textarea cols="70" rows="2" name="caption_new" id="caption_new"></textarea><br>
                    <label for="caption_new">Caption for new image</label>
                </td>
            </tr>
        </table>
        <input type="button" name="updord" value="Update image order" onclick="updateImageOrder(this.form)">
    </div>

</form>