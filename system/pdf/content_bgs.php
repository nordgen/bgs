<?php
// Include the main TCPDF library (search for installation path).

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require_once("../../vendor/autoload.php"); // Lazy load libraries through composer
require_once("./../config.php");
require_once("./../initdb.php");
require_once("./../common.php");


global $Zdb, $doctitle, $CONF;


//****************** Create the html more or less like bgs_show

if (isset($_REQUEST['bgs']) && $_REQUEST['bgs'] != "") {
    $bgsnum = $_REQUEST['bgs'];
} else {
    echo "no bgsnum";
    exit();
}

$doctitle = "BGS" . $bgsnum;



try {
    //Get all data for this bgs
    $q = "select d.id, d.name as docname, d.stock_number_int, d.locus_name, d.locus_symbol from bgs_doc d join bgs_section_in_doc sid on (d.id=sid.docid) join bgs_docsection ds on (ds.id=sid.docsectionid) where d.stock_number_int=$1";

    $bgsrow = [];
    $bgsrow = $Zdb->queryOne($q,[$bgsnum]);

    //Get all data for this bgs
    $q = "select ds.name as section_title, sid.text, ds.id as section_id, sid.id as sidid from bgs_doc d join bgs_section_in_doc sid on (d.id=sid.docid) join bgs_docsection ds on (ds.id=sid.docsectionid) where d.stock_number_int=$1 order by sid.ord";
    $rs = [];
    $rs = $Zdb->query($q,[$bgsnum])->getQueryResult();

    $did = $bgsrow['id'];
    $q = "select i.filename, i.caption from bgs_doc d join bgs_image_mapping im on (im.foreign_key_value=to_char(d.id,'FM999999999999')) join bgs_image i on (im.imageid=i.id) where im.foreign_table='bgs_doc' and im.foreign_key_name='id' and d.id=$1 order by im.ord";

    $rs2 = [];
    if (empty($did)) {
        throw new Exception("No document id is set.");
    }
    $rs2 = $Zdb->query($q,[$did])->getQueryResult();

} catch (exception $e) {
    echo "Error selecting bgs data, \$q: " . $q . " - " . $e->getMessage();
    exit();
}


function createInformationSystemAccessionReferences(int $stock_number_int)
{
    global $Zdb;
    $q = <<<SQL
select uld_ngb.value as ngb_number /*, uld_bgs.value as bgs_number*/
from bgs_ul_data AS uld_bgs
left join bgs_ul_data AS uld_ngb on uld_ngb.row_id=uld_bgs.row_id and uld_ngb.column_id=5
where uld_bgs.column_id = 11
   and  substring(trim(replace(uld_bgs.value,'BGS','')) from '[^ ]+'::text) = $1
GROUP BY uld_ngb.value;
SQL;

    try {
        $rs2 = [];
        $rs2 = $Zdb->query($q,[$stock_number_int])->getQueryResult();
        //echo "<br>".$q."<br>";
    } catch (exception $e) {
        echo "Error selecting getting images, \$q: " . $q . " - " . $e->getMessage();
    }
    if (count($rs2)>0) {
        ?>
        <h2>NGB number url references to <?php echo $_SERVER['INFO_SYS_TITLE']; ?>:</h2>
        <div class="sectiondiv" >
            <ol>
        <?php
    }
    $serverPage = $_SERVER['INFO_SYS_URL'] . '/' . $_SERVER['INFO_SYS_ACCESSION_PAGE'];
    foreach ($rs2 as $row2) {
        $accessionNumber = 'NGB ' . $row2['ngb_number'];;
        $accessionUrl = $serverPage . $accessionNumber;
        ?>
                <li><a href="<?php echo $accessionUrl; ?>" target="_blank"><?php echo $accessionNumber; ?></a> (<?php echo $accessionUrl; ?>)</li>
    <?php } //end foreach
    if (count($rs2)>0) {
        ?>
            </ol>
        </div>
        <?php
    }
}


function renderSectionBody(string $text, ?int $sectionId): string
{
    if (isset($sectionId) && $sectionId == 7000) {
        $filteredReferences = array_map(
            fn($ref) => '<li>' . preg_replace('/^(\d+\.)/i','',$ref) . '</li>',
            explode("\n", htmlentities($text))
        );
        return '<div class="sectiondiv" ><ol>'.implode("\n", $filteredReferences).'</ol></div>';
    } else {
        $htmlentitiesText = htmlentities($text);
        return <<<HTML
        <div class="sectiondiv prew" >$htmlentitiesText</div>
HTML;

    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=$doctitle?></title>
    <style type="text/css">

        body {
            margin: 0;
            padding: 0;
            font: 10pt "Times";
        }

        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }

        @media screen {
            .pagecontent {
                padding-top: 5mm;
                padding-bottom: 5mm;
            }
        }

        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }
            /* ... the rest of the rules ... */

            .new-page {
                page-break-before: always;
                page-break-inside: auto;
            }

            .keep-together {
                page-break-inside: avoid;
            }

            .pagecontent {
                margin: 0;
                width: initial;
                min-height: initial;
                page-break-after: always;
                page-break-inside: avoid;
            }
        }


        dl {
            margin-top: 0.2em;
            padding: 0.1em;
        }
        dt {
            float: left;
            clear: left;
            width: 16ch;
            text-align: left;
            font-weight: bold;
        }
        dt::after {
            content: ":";
        }
        dd {
            margin: 0 0 0 120px;
            padding: 0 0 0.5em 0;
        }

        .prew {
            white-space: pre-line;
        }

        table.minimize {
            margin: 0;
            margin-top: 0.5em;
            border-width: 1px;
            border-collapse: collapse;
        }

        table.minimize td, table.minimize th
        {
            padding: 0; /* 'cellpadding' equivalent */

        }

        table.minimize img {
            vertical-align: top;
            text-align: left;
            object-fit: scale-down;
            max-width: 100%;
        }

        .full-width {
            /*width: 100%;*/
        }

        .center {
            text-align:center;
        }

        h1, h2 {
            margin-bottom: 0.33em;
        }

        h3 {
            font: 10pt "Times";
        }

        .left-caption td h3 {
            text-align: left;
        }

        figure {
            max-width: 100%;
            max-height: 100%;
            width: fit-content;
            text-align: center;
            font-style: italic;
            font-size: smaller;
            text-indent: 0;
            border: thin silver solid;
            margin: 0.5em;
            padding: 0.2em;
        }

        figure img.scaled {
            vertical-align: top;
            text-align: center;
            object-fit: scale-down;
            max-width: 100%;
            width: fit-content;
        }

        figcaption {
            padding-left: 0.2em;
        }
    </style>
</head>
<body>
<?php if(!empty($bgsrow)) { ?>
    <div class="pagecontent">
        <h1><?=$bgsrow['docname']?></h1>
        <dl>
            <dt>Stock&nbsp;number</dt><dd>BGS <?=$bgsrow['stock_number_int']?></dd>
            <dt>Locus&nbsp;name</dt><dd><?=$bgsrow['locus_name']?></dd>
            <dt>Locus&nbsp;symbol</dt><dd><?=$bgsrow['locus_symbol']?></dd>
        </dl>

<?php
    foreach ($rs as $row) {
?>
        <h2><?=$row["section_title"]?></h2>
        <?=renderSectionBody($row["text"],$row['section_id'])?>
<?php
        if ($CONF['reference_ngb_numbers'] && $row['section_id']==7) {
            createInformationSystemAccessionReferences($bgsrow['stock_number_int']);
        }
    } //End loop through all sections
?>
    </div>
        <?php
}
?>
</body>
</html>