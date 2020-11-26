<?php //Jag borde ta tillfället i akt och bli mer objektorienterad i php! Jag kan göra ett sökresultat-objekt ?>

<h3 style="font-family:Arial, Helvetica, sans-serif;">Search result for "<?php echo $_REQUEST['kwl']; ?>"</h3>
<?php
//Debug output
/*
foreach($category_res as $row){ 
echo $row['gcname'].", ".$row['cname'].", ".$row['docid'].", ".$row['locus_name'].", ".$row['locus_symbol'].", ".$row['stock_number_char'].", ".$row['imageid']."<br>";
}
*/
$nResult = 0;
if(isset($category_res) && $category_res != null){
	$old_gcat="";
	$old_cat="";
	$old_doc=array('docid'=>"",'locus_name'=>"",'locus_symbol'=>"",'stock_number_char'=>"",'imgids'=>array());
	$firstrow=true;
	foreach($category_res as $row){ 
		$nResult++;
		if($firstrow){
			$firstrow=false;
?>
<table>
<?php 	
		} // End first row 
		
		if($old_doc['docid']!=$row['docid']){ //New doc, docs first so I can release any doc before new category
			if($old_doc['docid']!=""){ //Release old doc row
		?>
<tr>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_name']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_symbol']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['stock_number_char']; ?></a></td>
<td><?php if(count($old_doc['imgids']) > 0){ //Make some icon or something to put mouse over and see image thumbnails
		 if(count($old_doc['imgids']) == 1){
			echo "(".count($old_doc['imgids'])." image)";
		 }else{
			 echo "(".count($old_doc['imgids'])." images)";
		 }
}else{
	echo "No images";
} ?></td>
</tr>
<?php  		} //End release old doc row
            $old_doc['docid']=$row['docid'];
            $old_doc['locus_name']=$row['locus_name'];
            $old_doc['locus_symbol']=$row['locus_symbol'];
            $old_doc['stock_number_char']=$row['stock_number_char'];
            $old_doc['imgids'] = array();
		}//End new doc
		
		
		if($old_gcat != $row['gcname']){
			//New general category ?>
<tr><td colspan="4" style="padding-top:18px; font-size:18px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; text-align:center;"><?php echo $row['gcname']; ?></td></tr>
<?php	
			$old_gcat = $row['gcname'];
		} //End new general category	
		
		if($old_cat != $row['cname']){
			//New category ?>
<tr><td colspan="4" style="padding-top:18px; font-size:16px; font-family:Arial, Helvetica, sans-serif; font-weight:normal; text-align:center;"><?php echo $row['cname']; ?></td></tr>
<?php	
			$old_cat = $row['cname'];
		} //End new category 
		
		if(isset($row['imageid']) && $row['imageid']!=""){
			$old_doc['imgids'][] = $row['imageid'];
		}
	} //End foreach row
	//Release last row ?>
<tr>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_name']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_symbol']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['stock_number_char']; ?></a></td>
<td><?php if(count($old_doc['imgids']) > 0){ //Make some icon or something to put mouse over and see image thumbnails
		 if(count($old_doc['imgids']) == 1){
			echo "(".count($old_doc['imgids'])." image)";
		 }else{
			 echo "(".count($old_doc['imgids'])." images)";
		 }
}else{
	if(!$firstrow){
		echo "No images";
	}
} ?></td>
</tr>

</table>

<?php }// End category res 

//*#*#*#*#*#* Direct bgs search

if(isset($docid_res) && $docid_res != null){
	
	$old_doc=array('docid'=>"",'locus_name'=>"",'locus_symbol'=>"",'stock_number_char'=>"",'imgids'=>array());
	$firstrow=true;
	foreach($docid_res as $row){
		$nResult++; 
		if($firstrow){
			$firstrow=false;
?>
<table>
<?php 	
		} // End first row 
		
		if($old_doc['docid']!=$row['docid']){ //New doc, docs first so I can release any doc
			if($old_doc['docid']!=""){ //Release old doc row
		?>
<tr>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_name']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_symbol']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['stock_number_char']; ?></a></td>
<td><?php if(count($old_doc['imgids']) > 0){ //Make some icon or something to put mouse over and see image thumbnails
		 if(count($old_doc['imgids']) == 1){
			echo "(".count($old_doc['imgids'])." image)";
		 }else{
			 echo "(".count($old_doc['imgids'])." images)";
		 }
}else{
	echo "No images";
} ?></td>
</tr>
<?php  		} //End release old doc row
            $old_doc['docid']=$row['docid'];
            $old_doc['locus_name']=$row['locus_name'];
            $old_doc['locus_symbol']=$row['locus_symbol'];
            $old_doc['stock_number_char']=$row['stock_number_char'];
            $old_doc['imgids'] = array();
		}//End new doc
		
		if(isset($row['imageid']) && $row['imageid']!=""){
			$old_doc['imgids'][] = $row['imageid'];
		}
	} //End foreach row
	//Release last row ?>
<tr>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_name']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['locus_symbol']; ?></a></td>
<td><a href="index.php?pg=bgs_show&docid=<?php echo $old_doc['docid']; ?>" target="_self"><?php echo $old_doc['stock_number_char']; ?></a></td>
<td><?php if(count($old_doc['imgids']) > 0){ //Make some icon or something to put mouse over and see image thumbnails
		 if(count($old_doc['imgids']) == 1){
			echo "(".count($old_doc['imgids'])." image)";
		 }else{
			 echo "(".count($old_doc['imgids'])." images)";
		 }
}else{
	if(!$firstrow){
		echo "No images";
	}
} ?></td>
</tr>

</table>

<?php }// End doc res 

if($nResult == 0){ ?>

No search results.
<?php } ?>
