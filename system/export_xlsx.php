<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require_once("../vendor/autoload.php"); // Lazy load libraries through composer
require_once("./config.php");
require_once("./initdb.php");
require_once("./common.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;

global $Zdb;

$coldes = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ", "BA", "BB", "BC", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BK", "BL", "BM", "BN", "BO", "BP", "BQ", "BR", "BS", "BT", "BU", "BV", "BW", "BX", "BY", "BZ", "CA", "CB", "CC", "CD", "CE", "CF", "CG", "CH", "CI", "CJ", "CK", "CL", "CM", "CN", "CO", "CP", "CQ", "CR", "CS", "CT", "CU", "CV", "CW", "CX", "CY", "CZ");

if (isset($_REQUEST['t']) && $_REQUEST['t'] != "") {
    $export_type = $_REQUEST['t'];
} else {
    echo "error:no_export_type";
    exit();
}

// Create new PhpSpreadsheet object
$spreadsheet = new Spreadsheet();

//********************* Export UL data *****************************
if ($export_type == "ul") {
    if (isset($_REQUEST['ul']) && $_REQUEST['ul'] != "") {
        $ul = $_REQUEST['ul'];
    } else {
        echo "error:no_ul";
        exit();
    }

    if (isset($_REQUEST['colids']) && $_REQUEST['colids'] != "") {
        $colids = $_REQUEST['colids'];
    } else {
        echo "error:no_colids";
        exit();
    }

    $doctitle = $ul;
    $filename = $ul . "_data_" . date("Ymd");

    $colidarr = explode(";", $colids);
    $implode = 'implode';

    //Get all data cells for ul / selected columns
    //$q = "select distinct c.name as colname, c.id as colid, c.ord, c.data_type, d.value, d.row_id from bgs_ul_data d, bgs_ul_data u, bgs_ul_data_column c WHERE d.row_id=u.row_id AND u.column_id=3 AND u.value='".$ul."' AND c.id=d.column_id AND d.column_id IN(";
    $q = <<<SQL
select distinct c.name as colname, c.id as colid, c.ord, d.value, d.row_id 
from bgs_ul_data d, bgs_ul_data u, bgs_ul_data_column c 
WHERE d.row_id=u.row_id 
  AND u.column_id=3 
  AND u.value='$ul' 
  AND c.id=d.column_id 
  AND d.column_id IN({$implode(',', $colidarr)}) 
order by d.row_id, c.ord
SQL;

    //echo "<br>".$q."<br>";
    try {
        $rs = [];
        $rs = $Zdb->query($q)->getQueryResultSet();
        //echo "<br>".$q."<br>";
    } catch (exception $e) {
        echo "Error selecting ul: " . $e->getMessage();
    }


    // Set document properties
    $spreadsheet->getProperties()->setCreator("BGS database")
        ->setLastModifiedBy("BGS database")
        ->setTitle($doctitle)
        ->setSubject($doctitle)
        ->setDescription("Data export from " . $doctitle)
        ->setKeywords("BGSp " . $doctitle)
        ->setCategory("BGS export");
    // Add some data
    $sheet = $spreadsheet->setActiveSheetIndex(0);
    $sheet->setTitle($doctitle);


    //Now put it in a structure that help us draw the table without requiring values for all row-column nodes
    $alldata = array();
    $cols_order = array();
    if (isset($rs) && $rs != NULL && count($rs) > 0) {  //We have data

        foreach ($rs as $row) {
            if (!isset($alldata[$row['row_id']])) {
                $alldata[$row['row_id']] = array();
            }
            $alldata[$row['row_id']][$row['colid']] = $row['value'];

            //We also need all coluimn names and colids in rigth order from the resultset. Colids list in request can have any order
            //If there are no data we will not get column names and won't print any column row. This is OK here because we will not have any empty UL sheets. If we should want column row also for empty sheets we need to make a special query for column names
            if (!in_array($row['colid'], array_keys($cols_order))) {
                $cols_order[$row['colid']] = $row['colname'];
				
				//***** Save coded value names here also, for columns of data_type=1
					
			}
		} //Loop through results
		
//¤¤¤¤ Write to excel ¤¤¤¤¤¤
		
		//Write header row
		$c = 0;
		foreach($cols_order as $colid => $colname){
			$sheet->setCellValue($coldes[$c]."1", $colname);
			$c++;
		}
		
		
		//Write data
		if(count($alldata) > 0){ //We have data
		
			$nrow = 0;
			foreach($alldata as $rowid => $row){
				$c = 0; //column index
				$nrow++;
				$xlrow = $nrow+1;
				//Write row number

				//Write rest of row
				foreach($cols_order as $colid=> $colname){
					
					if(isset($alldata[$rowid][$colid])){ 
						$value = $alldata[$rowid][$colid];
						$sheet->setCellValue($coldes[$c].$xlrow, $value);	
					}
					$c ++;
	/*				
					else{ //No data for this row & column
						$value = "&nbsp;";		
					}
*/		
					   
				} //End loop through all columns in right order 
				
			}//End loop through all rows
		
		} //End alldata >0 
	
	
	} //End we have data

} //End export_type == "ul"




// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');

exit;
