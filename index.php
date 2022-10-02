<?php
/*
index.php, controller file for bgs database
Nordgen 2014
Author: Jonas Nordling
*/
//Init, db
//error_reporting(E_ALL);
error_reporting(E_ERROR);
session_start();
require_once('vendor/autoload.php'); // Lazy load libraries through composer
require_once('system/config.php');
require_once('system/initdb.php');
require_once('system/common.php');
global $Zdb, $CONF, $supported_uploads, $maxuploadsize, $fs;
$fs = $CONF['filesep'];
$implode = 'implode';
//Do logincheck, and check role, permissions etc
$logedin = true;

$errors = [];

/* Show request vars for debug
foreach($_REQUEST as $key=>$val){
	echo $key."=".$val."<br >";
}
*/
//Redundant or meaningless columns, stored for reference to old excel files
//These columns are hidden even for logged in users, they are candidates for removal
$hide_columns = array(6);

//Control request input
$page = (isset($_REQUEST['pg']) && $_REQUEST['pg'] != "")
    ? $_REQUEST['pg'] . ".php"
    : "bgs_start.php"; //Choose default page

$ul = (isset($_REQUEST['ul']) && $_REQUEST['ul'] != "")
    ? $_REQUEST['ul']
    : ""; //default

$ulsel = (isset($_REQUEST['ulsel']) && $_REQUEST['ulsel'] != "")
    ? $_REQUEST['ulsel']
    : ""; //default


//******* Login **********************
if (isset($_REQUEST['do']) && $_REQUEST['do'] != "") {
    if ($_REQUEST['do'] == "auth_login") {
        require_once("./system/auth_login.php");
    } elseif ($_REQUEST['do'] == "logout") {
        unset($_SESSION["isloggedin"]);
    }
}

if(isset($_REQUEST['act']) && $_REQUEST['act'] !="") {

    //****** Get column data for ul ******
    if ($_REQUEST['act'] == "select_ul") {
        $ul_columns = [];

        //If not administrator or editor, filter hidden columns
        $do_hide = !hasAnyRole(["ul_admin", "ul_edit"]) ? "AND c.hidden=0" : '';
        //Get all dynamic columns for this ul
        $q = <<<SQL
SELECT distinct c.name AS colname ,c.id AS colid, c.ord 
FROM bgs_ul_data d, bgs_ul_data u, bgs_ul_data_column c 
WHERE d.column_id=c.id 
  AND d.row_id=u.row_id 
  AND u.column_id=3 
  AND u.value='$ul' 
  AND c.id NOT IN({$implode(',', $hide_columns)}) 
  $do_hide 
ORDER BY c.ord
SQL;

        try {
            $rs = [];
            $rs = $Zdb->query($q)->getQueryResult();
            //echo "<br>".$q."<br>";
        } catch (exception $e) {
            echo "Error selecting ul columns: " . $e->getMessage();
        }
        foreach ($rs as $row) {
            $ul_columns[$row['colid']] = $row['colname'];
        }
    } else {
        $ul_columns = null;
    }


    //***** BGS search logic

    if ($_REQUEST['act'] == "kws") {

        //keyword_key=C5D3
        $i0 = strpos($_REQUEST['kwk'], "D");

        $keyC = ($i0 > 1)
            ? explode(",", substr($_REQUEST['kwk'], 1, $i0 - 1))
            : [];

        $keyD = (strlen($_REQUEST['kwk']) > $i0 + 1)
            ? explode(",", substr($_REQUEST['kwk'], $i0 + 1))
            : [];

        $singledochit = 0; //=not tried yet or no category hit
        if (count($keyC) > 0) {

            //Om man vill ha med category name på keywords när vi inte har några doc i kategorin
            //kan man nog ändra till: category join general_category left join cbm left join doc left join image mapping
            //Doc har alltid category, category har alltid general category, men category har inte alltid doc, ex Size of kernel (27)
            //Men känns inte viktigt

            //Vi har category information, visa under rubriken general category & category


            $q = <<<SQL
SELECT d.id AS docid, stock_number_char, locus_name, locus_symbol, c.name AS cname, c.id AS catid, g.name AS gcname, g.chapter_number AS gchapt, im.imageid 
FROM bgs_doc d 
    JOIN bgs_category_bgsdocid_map cdm ON (d.id=cdm.bgsdocid) 
    JOIN bgs_category c ON (c.id=cdm.category_id) 
    JOIN bgs_general_category g ON (g.id=cdm.general_category_id) 
    LEFT JOIN bgs_image_mapping im ON (to_char(d.id,'FM999999999999')=im.foreign_key_value) 
WHERE ((im.foreign_table='bgs_doc' AND im.foreign_key_name='id') OR im.imageid IS NULL) 
  AND cdm.category_id IN ({$implode(',', $keyC)}) 
ORDER BY gchapt, catid, locus_name
SQL;


            //echo "<br>C-query: ".$q."<br>";

            try {
                $category_res = $Zdb->query($q)->getQueryResult();
                //echo "<br>".$q."<br>";
            } catch (exception $e) {
                echo "Error doing: " . $q . " - " . $e->getMessage();
            }

        }//End at least one category id

        $docid_res = [[]];
        if (count($keyD) > 0) {

            //Vi har direkt docid, visa under egen rubrik

            //We leave out category and general category here, because ther migth not be any

            $q = <<<SQL
SELECT d.id AS docid, d.stock_number_char, d.locus_name, d.locus_symbol,  im.imageid 
FROM bgs_doc d 
    LEFT JOIN bgs_image_mapping im ON (to_char(d.id,'FM999999999999')=im.foreign_key_value) 
WHERE ((im.foreign_table='bgs_doc' AND im.foreign_key_name='id') OR im.imageid IS NULL) 
  AND d.id IN ({$implode(',', $keyD)}) 
ORDER BY locus_name
SQL;


            //echo "<br>D-query: ".$q."<br>";

            try {
                $docid_res = $Zdb->query($q)->getQueryResult();
                //echo "<br>".$q."<br>";
            } catch (exception $e) {
                echo "Error doing: " . $q . " - " . $e->getMessage();
            }


        }//End at least one direct doc id

        if (count($keyD) == 1) { //Only one bgs, go directly to show and skip list
            $page = "bgs_show.php";
            $docid = $docid_res[0]['docid'];
        } else {
            $page = "bgs_list.php";
        }

    } //End act = "bgs_keywsearch"

    //Doc chrome location for tables

    elseif ($_REQUEST['act'] == "updateChromLoc") {

        $docid = $_POST['docid'];
        $chrom_location = $_POST['chrom_location'];

        $q = "UPDATE bgs_doc SET chrom_location='" . $chrom_location . "', updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $docid;
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error updating chrom location";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
    }
	
	//Doc bgn volume and page
	
	elseif($_REQUEST['act']=="updateBGN") {

        $docid = $_POST['docid'];
        $bgn_volume = trim($_POST['bgn_volume']);
        $bgn_page = trim($_POST['bgn_page']);

        $q = "UPDATE bgs_doc SET bgn_volume='" . $bgn_volume . "', bgn_page='" . $bgn_page . "', updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $docid;
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error updating bgn";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
    }
	
	
	
	//********* Section handling ***************
	
	//Remove section
	elseif($_REQUEST['act']=="removeSectionFromDoc") {

        $docid = $_POST['docid'];
        $sidid = $_POST['sid']; //This is bgs_section_in_doc.id

        $sql = "DELETE FROM bgs_section_in_doc WHERE id=" . $sidid;
        try {
            $Zdb->execute($sql);
            //echo $sql."<br >";
        } catch (exception $e) {
            echo "error deleting docsection from doc<br>";
            echo $e->getMessage();
            // adodb_backtrace($e->gettrace());
            exit();
        }
    } //Add section
    elseif ($_REQUEST['act'] == "addSectionToDoc") {
        $docid = $_POST['docid'];
        $sid = $_POST['sid']; //This is section bgs_docsection.id / bgs_section_in_doc.docsectionid

        //Get max section ord for this docid
        $maxord = $Zdb->queryScalar("SELECT MAX(ord) FROM bgs_section_in_doc WHERE docid=$1", [$docid]);
        $neword = $maxord + 1;


        //$sql="INSERT INTO bgs_section_in_doc (docid,docsectionid,ord,upddtm,updusr) VALUES (".$docid.",".$sid.",".$neword.",now(),'".$_SESSION['isloggedin']['username']."')";
        try {
            $record = ['docid' => $docid, 'docsectionid' => $sid, 'ord' => $neword, 'updusr' => $_SESSION['isloggedin']['username'],];
            $sql = $Zdb->createParameterizedInsertSqlString(
                'bgs_section_in_doc',
                $record
            );
            $Zdb->execute($sql, $record);
            //echo $sql."<br >";
        } catch (exception $e) {
            // Ignore this, the insert was already done.
//			echo "error adding section to doc<br>";
//            echo $e->getMessage();
//			// adodb_backtrace($e->gettrace());
//			exit();
        }
    } //Create new section and add to doc
    elseif ($_REQUEST['act'] == "addNewSectionToDoc") {
        $docid = $_POST['docid'];
        $newname = $_POST['newsection'];
        //Check duplicate section name
        $sectexists = $Zdb->queryScalar("SELECT count(*) FROM bgs_docsection WHERE name=$1", [$newname]);
        if ($sectexists > 0) {
            $errors[] = array("add_section_duplicate_name", "There is already one section with this name.");
        } else {
            //Add new section
            //$sql="INSERT INTO bgs_docsection (name,upddtm,updusr) VALUES ('".htmlspecialchars($newname, ENT_QUOTES)."',now(),'".$_SESSION['isloggedin']['username']."')";
            try {
                $record = ['name' => htmlspecialchars($newname, ENT_QUOTES), 'updusr' => $_SESSION['isloggedin']['username'],];
                $sql = $Zdb->createParameterizedInsertSqlString(
                    'bgs_docsection',
                    $record
                );
                $Zdb->execute($sql, $record);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "error adding new section to db<br>";
                echo $e->getMessage();
                // adodb_backtrace($e->gettrace());
                exit();
            }
            //Get new section id
            $nsid = $Zdb->queryScalar("SELECT MAX(id) FROM bgs_docsection");
            //Get max section ord for this docid
            $maxord = $Zdb->queryScalar("SELECT MAX(ord) FROM bgs_section_in_doc WHERE docid=$1", [$docid]);
            $neword = $maxord + 1;
//			$sql="INSERT INTO bgs_section_in_doc (docid,docsectionid,ord,upddtm,updusr) VALUES (".$docid.",".$nsid.",".$neword.",now(),'".$_SESSION['isloggedin']['username']."')";
            try {
                $record = ['docid' => $docid, 'docsectionid' => $nsid, 'ord' => $neword, 'updusr' => $_SESSION['isloggedin']['username'],];
                $sql = $Zdb->createParameterizedInsertSqlString(
                    'bgs_section_in_doc',
                    $record
                );
                $Zdb->execute($sql, $record);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "error adding new section to doc<br>";
                echo $e->getMessage();
                // adodb_backtrace($e->gettrace());
                exit();
            }
        } //End no duplicate section name
    } //Move section up
    elseif ($_REQUEST['act'] == "sectionUp") {
        $docid = $_POST['docid'];
        $sidid = $_POST['sidid']; //This is bgs_section_in_doc.id

        $thisord = $Zdb->queryScalar("SELECT ord FROM bgs_section_in_doc WHERE id=$1", [$sidid]);
        $neword = $thisord - 1;

        $q = "UPDATE bgs_section_in_doc SET ord=ord+1, updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE docid=" . $docid . " AND ord=" . $neword;
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error moving down section in sectionUp";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
        $q = "UPDATE bgs_section_in_doc SET ord=" . $neword . ", updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $sidid;
        try {
            $record = ['ord' => $neword, 'updusr' => $_SESSION['isloggedin']['username'],];
            $q = $Zdb->createParameterizedUpdateSqlString(
                'bgs_section_in_doc',
                $record,
                "id=$sidid"
            );
            $rs = $Zdb->execute($q, $record);
        } catch (exception $e) {
            $error = "Error moving up section in sectionUp";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
    } //Move section down
    elseif ($_REQUEST['act'] == "sectionDown") {
        $docid = $_POST['docid'];
        $sidid = $_POST['sidid']; //This is bgs_section_in_doc.id

        $thisord = $Zdb->queryScalar("SELECT ord FROM bgs_section_in_doc WHERE id=$1", [$sidid]);
        $neword = $thisord + 1;

        $q = "UPDATE bgs_section_in_doc SET ord=ord-1, updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE docid=" . $docid . " AND ord=" . $neword;
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error moving up section in sectionDown";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
        $q = "UPDATE bgs_section_in_doc SET ord=" . $neword . ", updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $sidid;
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error moving down section in sectionDown";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
    } //Move locus up in locus table
    elseif ($_REQUEST['act'] == "locusUp") {
        $docid = $_POST['docid'];

        $thisord = $Zdb->queryScalar("SELECT symbol_ord FROM bgs_doc WHERE id=$1", [$docid]);
        $neword = $thisord - 1;

        $q = "UPDATE bgs_doc SET symbol_ord=symbol_ord+1, updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE symbol_ord=" . $neword;
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error moving down locus in locusUp";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
        $q = "UPDATE bgs_doc SET symbol_ord=" . $neword . ", updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $docid;
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error moving up item in locusUp";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
    } //Move locus down in locus table
    elseif ($_REQUEST['act'] == "locusDown") {
        $docid = $_POST['docid'];

        $thisord = $Zdb->queryScalar("SELECT symbol_ord FROM bgs_doc WHERE id=$1", [$docid]);
        $neword = $thisord + 1;


        try {
            $Zdb->startTrans();
            $q = "UPDATE bgs_doc SET symbol_ord=symbol_ord-1, updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE symbol_ord=" . $neword;
            try {
                $rs = $Zdb->execute($q);
            } catch (exception $e) {
                $error = "Error moving up locus in locusDown";
                //$error .= "\n query:".$q;
                //$error .= "\n".$e->getMessage();
                throw new Exception($error);
//                exit();
            }
            $q = "UPDATE bgs_doc SET symbol_ord=" . $neword . ", updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $docid;
            try {
                $rs = $Zdb->execute($q);
            } catch (exception $e) {
                $error = "Error moving down item in locusDown";
                //$error .= "\n query:".$q;
                //$error .= "\n".$e->getMessage();
                throw new Exception($error);
//                exit();
            }
            $Zdb->completeTrans();
        } catch (Exception $e) {
            $Zdb->failTrans();
            echo $e->getMessage();
            exit();
        }

    } //******** Create new BGS document
    elseif ($_REQUEST['act'] == "createNewBGSDoc") {
        $newbgsnum = $_POST['newbgsnum'];
        $newlocusname = htmlspecialchars($_POST['newlocusname'], ENT_QUOTES);
        $newlocussymbol = htmlspecialchars($_POST['newlocussymbol'], ENT_QUOTES);

        //Check duplicate section name
        $bgsexists = $Zdb->queryScalar("SELECT count(*) FROM bgs_doc WHERE stock_number_int=" . $newbgsnum);
        if ($bgsexists > 0) {
            $errors[] = array("new_bgs_duplicate_number", "There is already one BGS document with this number.");
        } else {
            $numchar = "BGS " . $newbgsnum;
            $newname = $numchar . ", " . $newlocusname . ", " . $newlocussymbol;
            $sql = "INSERT INTO bgs_doc (stock_number_char,stock_number_int,locus_symbol,locus_name,name,upddtm,updusr) VALUES ('" . $numchar . "'," . $newbgsnum . ",'" . $newlocussymbol . "','" . $newlocusname . "','" . $newname . "',now(),'" . $_SESSION['isloggedin']['username'] . "')";
            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "error adding new BGS doc<br>";
                echo $e->getMessage();
                // adodb_backtrace($e->gettrace());
                exit();
            }
            //Get new bgs docid
            $docid = $Zdb->queryScalar("SELECT id FROM bgs_doc WHERE stock_number_int=" . $newbgsnum);
        } //End no duplicate bgs number
    } //******** Delete BGS document
    elseif ($_REQUEST['act'] == "delDoc") {
        $docid = $_POST['docid'];

        $sql = "DELETE FROM bgs_doc WHERE id=" . $docid;
        try {
            $Zdb->execute($sql);
            //echo $sql."<br >";
        } catch (exception $e) {
            echo "error deleting doc<br>";
            echo $e->getMessage();
            // adodb_backtrace($e->gettrace());
            exit();
        }
    }
	
	
	
	//******* Image admin ************
	
	//Update caption
	elseif($_REQUEST['act']=="upd_capt") {
        //Require imgid	as post, pg & docid as get
        if (!isset($_REQUEST['docid']) || !isset($_REQUEST['imgid'])) {
            echo "Updating caption, error: No document and/or image identification";
            exit();
        }
        $r = "caption_" . $_REQUEST['imgid'];
        $newcapt = $_POST[$r];
        $q = "UPDATE bgs_image SET caption='" . trim(str_replace("'", "\'", $newcapt)) . "', updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $_REQUEST['imgid'];

        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error saving new caption";
            //$error .= "\n query:".$q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }

    }//End act = "upd_capt"
	//Update order
	elseif($_REQUEST['act']=="upd_ord"){	
		//Require pg & docid as get
		if(!isset($_REQUEST['docid'])){
			echo "Updating caption, error: No document identification";
			exit();
		}
		$ords = "";
		foreach($_POST as $key=>$val){
			if(strlen($key) > 4 && substr($key,0,4)=="ord_"){
				if($ords!=""){
                    $ords .= ",";
                }
                $ii = substr($key, 4);
                $io = $val;
                if (!is_numeric($io)) {
                    $io = 0;
                }
                $ords .= "(" . $ii . "," . $io . ")";
            }
        }
        $q = "CREATE TEMPORARY TABLE temp_new_orders (imgid integer,ord integer)";
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error creating temp table for new orders";
            $error .= "\n query:" . $q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }
        $q = "INSERT INTO temp_new_orders (imgid,ord) VALUES $ords";
        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error populating temp table for new orders";
            $error .= "\n query:" . $q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }

        $q = <<<SQL
UPDATE bgs_image_mapping im 
SET ord=tno.ord, updusr='{$_SESSION['isloggedin']['username']}', upddtm=now() 
FROM temp_new_orders tno, bgs_doc d 
WHERE im.imageid=tno.imgid 
  AND im.foreign_key_value='${_REQUEST['docid']}' 
  AND im.foreign_table='bgs_doc' 
  AND im.foreign_key_name='id'
SQL;


        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error updating bgs_image_mapping with image order from temporary table";
            $error .= "\n query:" . $q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }

        $q = "DROP TABLE temp_new_orders";

        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error dropping temporary table";
            $error .= "\n query:" . $q;
            //$error .= "\n".$e->getMessage();
            echo $error;
            exit();
        }

    }//End act = "upd_capt"
	//**************** upload new image ***********************
	elseif($_REQUEST['act']=="upl_new") {
        if (!isset($_REQUEST['docid'])) {
            echo "Updating caption, error: No document identification";
            exit();
        }
        $uploaderror = array();
        $errorinfo = array();
        $errortext = array();
        $errorix = 0;
        $imgpath = $fs . "images";
        $newfilename = '';

        if ($_FILES['newimg']['tmp_name'] == "none") {
            //Uppladdad fil finns inte
            $uploaderror[$errorix] = "nofile";
            $errortext[$errorix] = "No file was chosen for upload!";
            $errorinfo[$errorix] = "";
            $errorix++;
        } elseif (!is_uploaded_file($_FILES['newimg']['tmp_name'])) {
            //kolla att användaren inte skrivit in en sökväg i upload-fältet
			$uploaderror[$errorix]="noupload";
			$errortext[$errorix]="The filename was entered in the field instead of chosen. Please chose a file with the \"Browse\" button!";
			$errorinfo[$errorix]="";
			$errorix++;
		}	
		else{
			//kolla filtypen
			if(!in_array($_FILES['newimg']['type'],$supported_uploads)){
				$uploaderror[$errorix]="filetype";
				$errortext[$errorix]="The filetype of the chosen file is not supported!";
				$errorinfo[$errorix]=$_FILES['newimg']['type'];
				$errorix++;
			}
			//kolla storlek
			if($_FILES['newimg']['size'] > $maxuploadsize){
				$uploaderror[$errorix]="filesize";
				$maxfs=$maxuploadsize/1024;
				$errortext[$errorix]="The size of the chosen file exceeds the maximum allowed filesize which is ".$maxfs." kB!";				
				$errorinfo[$errorix]=$_FILES['newimg']['size'];				
				$errorix++;
			}
			//kolla filändelse
			if(!preg_match("/.[a-zA-Z0-9_]{3,4}$/",$_FILES['newimg']['name'])){
				$uploaderror[$errorix]="fileending";
				$errortext[$errorix]="The chosen file doesn't have a proper file-ending.";				
				$errorinfo[$errorix]=$_FILES['newimg']['name'];				
				$errorix++;
			}		
			//ev. döp om bilden
			//$filename=makeFileName($_FILES['newimg']['name']);
			$filename=strtolower(str_replace(" ","_",$_FILES['newimg']['name']));
			
			$ix=strrpos($filename,".");
			$name = substr($filename,0,$ix);
			$suf = substr($filename,$ix);	
			if($suf!=".png"){
				$newfilename=$name.".png";
			}else{
				$newfilename=$filename;
			}
			
			$filepath_t = $CONF['document_root'].$imgpath.$fs."temporary".$fs.$filename;
			$filepath_l = $CONF['document_root'].$imgpath.$fs."large".$fs.$newfilename;
            $filepath_s = $CONF['document_root'] . $imgpath . $fs . "small" . $fs . $newfilename;

            //Check if file exists
            if (file_exists($filepath_l)) {
                $uploaderror[$errorix] = "file_exists";
                $errortext[$errorix] = "The chosen file already exists.";
                $errorinfo[$errorix] = $filename;
                $errorix++;
            }
            //Go ahead and copy file to temp folder before conversion
            if ($errorix == 0) {

                //******** Error checking for move to Nordgen server *********

                error_reporting(E_ALL);

                echo "<br ><br >We expect temporary file at _FILES['newimg']['tmp_name']: " . $_FILES['newimg']['tmp_name'];
                echo "<br ><br >file_exists(_FILES['newimg']['tmp_name']): " . file_exists($_FILES['newimg']['tmp_name']);
                echo "<br ><br >We want to copy to directory: " . $CONF['document_root'] . $imgpath . $fs . "temporary" . $fs;
                echo "<br ><br >file_exists(" . $CONF['document_root'] . $imgpath . $fs . "temporary" . $fs . "): " . file_exists($CONF['document_root'] . $imgpath . $fs . "temporary" . $fs);
                echo "<br ><br >is_writable(" . $CONF['document_root'] . $imgpath . $fs . "temporary" . $fs . "): " . is_writable($CONF['document_root'] . $imgpath . $fs . "temporary" . $fs);

                //********** End special error checking **********

                if (!copy($_FILES['newimg']['tmp_name'], $filepath_t)) {
                    //if(false){
                    $uploaderror[$errorix] = "copyfile";
                    $errortext[$errorix] = "Failed to copy new file to server!";
                    $errorinfo[$errorix] = "From:" . $_FILES['newimg']['tmp_name'] . " To:" . $filepath_t;
                    $errorix++;
                } else {
                    //Convert and copy large file and thumbnail

                    $ret = '';
                    try {
                        //$cmd = "convert ".$filepath_t." -resize x100 ".$filepath_s; //Resize to height 100px
                        $cmd = "convert " . $filepath_t . " -resize x100 " . $filepath_s; //Resize to height 100px
                        $ret = system($cmd); //Resize to height 100px
                        //echo "<br >".$ret."<br >";
                        //echo "<br >".$cmd."<br >";
                    } catch (exception $e) {
                        $error = "Error converting file: " . $ret;
                        $error .= "<br ><br >" . $e->getMessage();
                        echo $error;
                        exit();
                    }


                    try {

                        $cmd = "convert " . $filepath_t . " -resize x900 " . $filepath_l; //Resize to height  900px
                        $ret = system($cmd);
                        //echo "<br >".$ret."<br >";
                        //echo "<br >".$cmd."<br >";
                    } catch (exception $e) {
                        $error = "Error converting file: " . $ret;
                        $error .= "<br ><br >" . $e->getMessage();
                        echo $error;
                        exit();
                    }
                    //Delete temporary file
                    unlink($filepath_t);
                }
			}
		}//End upload and convert
				
		if($errorix==0) {
            //Insert file, caption and mapping in db

            $sql = "INSERT INTO bgs_image (filename,caption,updusr,upddtm) VALUES ('" . $newfilename . "','" . str_replace("'", "\'", $_POST['caption_new']) . "','" . $_SESSION['isloggedin']['username'] . "',now())";
            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "error inserting into bgs_image<br>";
                echo $e->getMessage();
                // adodb_backtrace($e->gettrace());
                exit();
            }

            //Add mapping/s
            //Get image id
            $imgid = $Zdb->queryScalar("SELECT id FROM bgs_image WHERE filename='" . $newfilename . "'");

            $ord = trim($_REQUEST['newimg_ord']);
            if (!isset($ord) || !is_numeric($ord)) {
                $ord = "0";
            }
            $sql = "INSERT INTO bgs_image_mapping (imageid,foreign_table,foreign_key_name,foreign_key_value,ord,updusr,upddtm) VALUES (" . $imgid . ",'bgs_doc','id','" . $_REQUEST['docid'] . "'," . $ord . ",'" . $_SESSION['isloggedin']['username'] . "',now())";
            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "error inserting image mapping<br>";
                echo $e->getMessage();
                // adodb_backtrace($e->gettrace());
                exit();
            }


        }

    } //end act =upl_new, file upload
    //Delete image
    elseif ($_REQUEST['act'] == "del_img") {
        //Require imgid	as post, pg & docid as get
        if (!isset($_REQUEST['imgid'])) {
            echo "Deleting image, error: No image identification";
            exit();
        }
        $filename = $Zdb->queryScalar("SELECT filename FROM bgs_image WHERE id=" . $_REQUEST['imgid']);
        $imgpath = $fs . "images";
        $filepath_l = $CONF['document_root'] . $imgpath . $fs . "large" . $fs . $filename;
        $filepath_s = $CONF['document_root'] . $imgpath . $fs . "small" . $fs . $filename;

        unlink($filepath_l);
        unlink($filepath_s);

        try {
            $sql = "DELETE FROM bgs_image WHERE id=" . $_REQUEST['imgid'];
            $Zdb->execute($sql);
            //echo $sql."<br >";
        } catch (exception $e) {
            echo "error deleting bgs_image<br>";
            echo $e->getMessage();
            // adodb_backtrace($e->gettrace());
            exit();
        }

        try {
            $sql = "DELETE FROM bgs_image_mapping WHERE imageid=" . $_REQUEST['imgid'];
            $Zdb->execute($sql);
            //echo $sql."<br >";
        } catch (exception $e) {
            echo "error deleting bgs_image<br>";
            echo $e->getMessage();
            // adodb_backtrace($e->gettrace());
            exit();
        }

    }//end act =del_img
	
	//********** User administration **********
	//------ Add new user -------
	elseif($_POST['act']=="addUser"){
		
		if(isLoggedin() && hasAllRoles(array("user_admin"))) {

            $username = $_POST['username_new'];
            $password = $_POST['password_new'];
            $realname = $_POST['realname_new'];

            //Kolla att username är unikt
            $duplid = $Zdb->queryScalar("SELECT id FROM bgs_user WHERE username='" . $username . "'");

            if (isset($duplid)) {
                $errors[] = array("add_user", "Username already exist.");
            } else {

                $sql = "INSERT INTO bgs_user (username,password,real_name,upddtm,updusr) VALUES ('" . $username . "','" . md5($password) . "','" . $realname . "',now(),'" . $_SESSION['isloggedin']['username'] . "')";
                try {
                    $Zdb->execute($sql);
                    //echo $sql."<br >";
                } catch (exception $e) {
                    echo "error adding user<br>";
                    echo $e->getMessage();
                    // adodb_backtrace($e->gettrace());
                    exit();
                }

                //Get new user id
                $newuserid = $Zdb->queryScalar("SELECT MAX(id) FROM bgs_user");

                //Add role mappings
                $sql_values_arr = [];
                foreach ($_POST as $key => $val) {
                    if (strpos($key, "role_new_") === 0 && $val == "1") {
                        $sql_values_arr[] = "(" . $newuserid . "," . substr($key, strrpos($key, "_") + 1) . ",now(),'" . $_SESSION['isloggedin']['username'] . "')";
                    }
                }
                $sql = "INSERT INTO bgs_user_role (user_id,role_id,upddtm,updusr) VALUES {$implode(',',$sql_values_arr)}";
                if (count($sql_values_arr) > 0) {
                    try {
                        $Zdb->execute($sql);
                        //echo $sql."<br >";
                    } catch (exception $e) {
                        echo "error adding user roles<br>";
                        echo $e->getMessage();
                        // adodb_backtrace($e->gettrace());
                        exit();
                    }
                }//End has mappings
            }//End not duplicate username
        }//End user admin rights
	}//End act = addUser
	
	//------ Delete user -------	
	elseif($_POST['act']=="deleteUser"){
		if(isLoggedin() && hasAllRoles(array("user_admin"))) {
            $edituserid = $_POST['userid'];
            //Delete all user roles, leave user for reference of changes, but flag as deleted
            $sql = "DELETE FROM bgs_user_role WHERE user_id=" . $edituserid;
            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "error deleting bgs_user_roles<br>";
                echo $e->getMessage();
                // // adodb_backtrace($e->gettrace());
                exit();
            }

            $sql = "UPDATE bgs_user SET deleted=1, deleted_time=now(), updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $edituserid;

            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "Error flagging user as deleted";
                echo $e->getMessage();
                exit();
            }
        }
	}
	
	//------ Update user -------	
	elseif($_POST['act']=="updateUser"){
		if(isLoggedin() && hasAllRoles(array("user_admin"))) {
            $edituserid = $_POST['userid'];
            //$password = $_POST['password_'.$edituserid];
            $realname = $_POST['realname_' . $edituserid];

            //$sql = "UPDATE bgs_user SET username='".$username."', password='".md5($password)."', real_name='".$realname."', upddtm=now(), updusr='".$_SESSION['isloggedin']['username']."' WHERE id=".$edituserid;
            $sql = "UPDATE bgs_user SET real_name='" . $realname . "', upddtm=now(), updusr='" . $_SESSION['isloggedin']['username'] . "' WHERE id=" . $edituserid;

            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "Error updating user";
                echo $e->getMessage();
                exit();
            }

            //Delete all old role mappings
            $sql = "DELETE FROM bgs_user_role WHERE user_id=" . $edituserid;
            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "error deleting bgs_user_roles<br>";
                echo $e->getMessage();
                // // adodb_backtrace($e->gettrace());
                exit();
            }


            //Add new role mappings


            $sql_values_arr = [];
            foreach ($_POST as $key => $val) {
                if (strpos($key, "role_" . $edituserid . "_") === 0 && $val == "1") {
                    $sql_values_arr[] = "(" . $edituserid . "," . substr($key, strrpos($key, "_") + 1) . ",now(),'" . $_SESSION['isloggedin']['username'] . "')";
                }
            }
            $sql = "INSERT INTO bgs_user_role (user_id,role_id,upddtm,updusr) VALUES {$implode(',',$sql_values_arr)}";
            if (count($sql_values_arr) > 0) {
                try {
                    $Zdb->execute($sql);
                    //echo $sql."<br >";
                } catch (exception $e) {
                    echo "error adding user roles<br>";
                    echo $e->getMessage();
                    // // adodb_backtrace($e->gettrace());
                    exit();
                }
            }
        }
	}
	
	//------ Change password -------	
	elseif($_POST['act']=="changePassword"){
		if(isLoggedin() && hasAllRoles(array("user_admin"))) {

            $edituserid = $_POST['userid'];
            $password = $_POST['password_' . $edituserid];


            $sql = "UPDATE bgs_user SET password='" . md5($password) . "', updusr='" . $_SESSION['isloggedin']['username'] . "', upddtm=now() WHERE id=" . $edituserid;

            try {
                $Zdb->execute($sql);
                //echo $sql."<br >";
            } catch (exception $e) {
                echo "Error changeing password";
                echo $e->getMessage();
                exit();
            }
        }
	}
	
} //End isset act

//Hand over to view
require_once("./main.php");
