<?php
error_reporting(E_ALL);
require_once("./vendor/autoload.php"); // Lazy load libraries through composer
require_once("./system/config.php");
require_once("./system/initdb.php");
global $Zdb;

if (isset($_REQUEST['r']) && $_REQUEST['r'] != "") {
    $rowid = $_REQUEST['r'];
} else {
    echo "error:no row id";
    exit();
}

if (isset($_REQUEST['c']) && $_REQUEST['c'] != "") {
    $colid = $_REQUEST['c'];
} else {
    echo "error:no column id";
    exit();
}

if(isset($_REQUEST['v'])){
	$value = $_REQUEST['v'];
}else{
	echo "error:no value";
	exit();
}

$stat = "";

//Value == "" -> delete record
if($value == "") {
    $q = "DELETE FROM bgs_ul_data WHERE row_id=" . $rowid . " AND column_id=" . $colid;
    $stat .= $q;
    try {
        $rs = $Zdb->execute($q);
        //echo "<br>".$q."<br>";
    } catch (exception $e) {
        echo "Error deleting record: " . $e->getMessage();
        exit();
    }
}
else { //Value not empty, update or insert


    //Check if value exists->update, else insert
    $q = "SELECT id FROM bgs_ul_data WHERE row_id=" . $rowid . " AND column_id=" . $colid;
    try {
        $rs = $Zdb->query($q)->getQueryResultSet();
        //echo "<br>".$q."<br>";
    } catch (exception $e) {
        echo "Error checking record: " . $e->getMessage();
        exit();
    }
    //Idiotic check if we have a value because adodb/postgres doesn't support num_rows
    $n = 0;
    foreach ($rs as $row) {
        $n++;
    }
    $stat .= " n=" . $n . ", ";
    if ($n > 0) {  //We have data: Update
        $q = "UPDATE bgs_ul_data SET value='" . $value . "' WHERE row_id=" . $rowid . " AND column_id=" . $colid;
        $stat .= $q;
        try {
            $rs2 = $Zdb->execute($q);
            //echo "<br>".$q."<br>";
        } catch (exception $e) {
            echo "Error updating data: " . $e->getMessage();
            exit();
        }
    }else { //Insert
        $q = "INSERT INTO bgs_ul_data (value,row_id,column_id) VALUES('" . $value . "'," . $rowid . "," . $colid . ")";
        $stat .= $q;
        try {
            $rs2 = $Zdb->execute($q);
            //echo "<br>".$q."<br>";
        } catch (exception $e) {
            echo "Error inserting data: " . $e->getMessage();
            exit();
        }
    }
} //End value !=""
echo "Value was saved!";
//echo "ul_data_save, stat=".$stat;
