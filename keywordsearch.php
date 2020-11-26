<?php
error_reporting(E_ALL);
require_once("./vendor/autoload.php"); // Lazy load libraries through composer
require_once("./system/config.php");
require_once("./system/initdb.php");
global $Zdb;

if (!isset($_GET['term']) || $_GET['term'] == "") {
    echo "";
    exit();
}

$jsonout = "";

$userinp = strtolower($_GET['term']);

if(!is_numeric($userinp) && (strlen($userinp) < 4 || strtolower(substr($userinp,0,3))!= "bgs")) { //Not 'bgs..' or starting on a number


    $q = "SELECT keyword, category_id,bgsdoc_id FROM bgs_keyword_map WHERE lower(keyword) LIKE '" . $userinp . "%' ORDER BY keyword";

    try {
        $rs = [];
        $rs = $Zdb->query($q)->getQueryResultSet();
        //echo "<br>".$q."<br>";
    } catch (exception $e) {
        echo "";
        exit();
        //echo "Error selecting subgroups: ".var_dump($e);
        /*	Implement logging
            logMess("E","error fetching tests, q:".$q);
            if($debug){
                logMess("D",var_dump($e));
                logMess("D",adodb_backtrace($e->gettrace()));
            }
        */
	}
	//Merge rows with same keyword
	$oldkeyword = "";
	$keyC="";
	$keyD="";
	
	$firstobj = true;
	
	foreach($rs as $row){
		if($oldkeyword!=$row['keyword']){
			if($oldkeyword!=""){
				//Release a keyword
				if($firstobj){
					$jsonout .= "[";
					$firstobj = false;
				}else{
					$jsonout .= ",";	
				}
				//Debug value
				//$jsonout .= "{\"value\":\"C".$keyC."D".$keyD."\",\"label\":\"".$oldkeyword." - C".$keyC."D".$keyD."\"}";
				$jsonout .= "{\"value\":\"C".$keyC."D".$keyD."\",\"label\":\"".$oldkeyword."\"}";
				$keyC="";
				$keyD="";
			}
			$oldkeyword=$row['keyword'];
		}
		//Add category_id and doc_id as key
		if(isset($row['category_id']) && $row['category_id']!= null && $row['category_id'] != ""){
			if($keyC != ""){
				$keyC .= ",";
			}
			$keyC .= $row['category_id'];
		}
		if(isset($row['bgsdoc_id']) && $row['bgsdoc_id']!= null && $row['bgsdoc_id'] != ""){
			if($keyD != ""){
				$keyD .= ",";
			}
			$keyD .= $row['bgsdoc_id'];
		}
	}
	//Release last keyword
	if($keyC!="" || $keyD!=""){
		if($firstobj){
			$jsonout .= "[";
		}else{
			$jsonout .= ",";	
		}
		//Debug value
		//$jsonout .= "{\"value\":\"C".$keyC."D".$keyD."\",\"label\":\"".$oldkeyword." - C".$keyC."D".$keyD."\"}";
		$jsonout .= "{\"value\":\"C".$keyC."D".$keyD."\",\"label\":\"".$oldkeyword."\"}";
		
		$jsonout .= "]";
	}
} else { //At least 4 chars and starts with a number or bgs: search in bgs_doc, not in keywords

    if (is_numeric($userinp)) {
        $numpart = $userinp;
    } else {
        $numpart = trim(substr($userinp, 3));
    }

    $q = "SELECT stock_number_int,stock_number_char, id as docid FROM bgs_doc WHERE stock_number_char LIKE 'BGS " . $numpart . "%' ORDER BY stock_number_int";

    //echo "<br>".$q."<br>";

    try {
        $rs = $Zdb->query($q)->getQueryResultSet();
        //echo "<br>".$q."<br>";
    } catch (exception $e) {
        echo "";
        exit();
        //echo "Error selecting subgroups: ".var_dump($e);
        /*	Implement logging
            logMess("E","error fetching tests, q:".$q);
            if($debug){
                logMess("D",var_dump($e));
                logMess("D",adodb_backtrace($e->gettrace()));
            }
        */
    }
    $keyC = "";
    $keyD = "";
	
	$firstobj = true;
	
	foreach($rs as $row){
		$keyD="";
		if($firstobj){
			$jsonout .= "[";
			$firstobj = false;
		}else{
			$jsonout .= ",";	
		}
		
		if(isset($row['docid']) && $row['docid']!= null && $row['docid'] != ""){
			if($keyD != ""){
				$keyD .= ",";
			}
			$keyD .= $row['docid'];
			
			//Debug value
			//$jsonout .= "{\"value\":\"C".$keyC."D".$keyD."\",\"label\":\"".$oldkeyword." - C".$keyC."D".$keyD."\"}";
			$jsonout .= "{\"value\":\"C".$keyC."D".$keyD."\",\"label\":\"".$row['stock_number_char']."\"}";
		}
	}
	if($jsonout!=""){	
		
		$jsonout .= "]";
	}

}

echo $jsonout;


