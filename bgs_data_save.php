<?php
error_reporting(E_ALL);
require_once("./vendor/autoload.php"); // Lazy load libraries through composer
require_once("./system/config.php");
require_once("./system/initdb.php");
global $Zdb;

$error = "";

if (!isset($_POST['act']) || $_POST['act'] == "") {
    $error = "error: No action specified.";
    echo $error;
    exit();
}

//********** Save section ************

if($_POST['act'] =="saveSection"){

	if(isset($_POST['sectid']) && $_POST['sectid'] !=""){
		$sectid = $_POST['sectid'];
	}else{
		$error = "error: No section id.";
		echo $error;
		exit();
	}
	
	
	if(isset($_POST['secttext']) && $_POST['secttext'] !=""){
		$text = $_POST['secttext'];
	}else{
		$error = "error: No text in section.";
		echo $error;
		exit();
	}
	
	/*Debugging
	for($i=0;$i<strlen($text);$i++){
		echo ord(substr($text,$i)).",";
	}
	echo "\n\n";
	*/

    $nt = str_replace("'", "''", $text);

    $q = "UPDATE bgs_section_in_doc SET text='" . trim($nt) . "' WHERE id=" . $sectid;

    try {
        $rs = $Zdb->execute($q);
    } catch (exception $e) {
        $error = "Error saving section data";
        //$error .= "\n query:".$q;
        //$error .= "\n".var_dump($e);
    }

    if ($error != "") {
        echo $error;
    } else {
        echo "Section text was saved!";
		//echo "\n query:".$q."\n";
	}
}


//********** Save header field ************

elseif($_POST['act'] == "saveHead"){
	
	if(isset($_POST['headerfield']) && $_POST['headerfield'] !=""){
		$field = $_POST['headerfield'];
	}else{
		$error = "error: No header field specified.";
		echo $error;
		exit();
	}
	
	if(isset($_POST['headtext']) && $_POST['headtext'] !=""){
		$text = $_POST['headtext'];
	}else{
		$error = "error: No text in header field.";
		echo $error;
		exit();
	}
	if(isset($_POST['did']) && $_POST['did'] !=""){
		$docid = $_POST['did'];
    } else {
        $error = "error: No document id.";
        echo $error;
        exit();
    }

    $nt = str_replace("'", "''", $text);

    $q = "UPDATE bgs_doc SET " . $field . " = '" . trim($nt) . "' WHERE id=" . $docid;

    try {
        $rs = $Zdb->execute($q);
    } catch (exception $e) {
        $error = "Error saving header data, " . $field;
        //$error .= "\n query:".$q;
        //$error .= "\n".var_dump($e);
    }

    if ($error == "" && $field == "stock_number_int") {
        //Update stock_number_char
        $q = "UPDATE bgs_doc SET stock_number_char = 'BGS " . trim($nt) . "' WHERE id=" . $docid;

        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = "Error saving header data, stock_number_char";
            //$error .= "\n query:".$q;
            //$error .= "\n".var_dump($e);
        }
    }
	
	if($error == "") {
        //Update document name
        $q = "UPDATE bgs_doc SET name = stock_number_char||', '||locus_name||', '||locus_symbol WHERE id=" . $docid;

        try {
            $rs = $Zdb->execute($q);
        } catch (exception $e) {
            $error = " Error saving document name.";
            //$error .= "\n query:".$q;
            //$error .= "\n".var_dump($e);
        }

    }
	
	
	if($error != ""){
		echo $error;
	}
	else{
		echo "Header field was saved!";
	}
	
} //End save head
