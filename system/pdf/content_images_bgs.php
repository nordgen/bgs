<?php
// Include the main TCPDF library (search for installation path).
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require_once("../../vendor/autoload.php"); // Lazy load libraries through composer
require_once("./../config.php");
require_once("./../initdb.php");
require_once("./../common.php");


global $Zdb, $doctitle;


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
    $rs = $Zdb->query($q,[$bgsnum])->getQueryResultSet();

    $did = $bgsrow['id'];
    $q = "select i.filename, i.caption from bgs_doc d join bgs_image_mapping im on (im.foreign_key_value=to_char(d.id,'FM999999999999')) join bgs_image i on (im.imageid=i.id) where im.foreign_table='bgs_doc' and im.foreign_key_name='id' and d.id=$1 order by im.ord";

    $rs2 = [];
    if (empty($did)) {
        throw new Exception("No document id is set.");
    }
    $rs2 = $Zdb->query($q,[$did])->getQueryResultSet();

} catch (exception $e) {
    echo "Error selecting bgs data, \$q: " . $q . " - " . $e->getMessage();
    exit();
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
            /*background-color: #FAFAFA;*/
            font: 10pt "Times";
        }

        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }

        @media screen {
            .pagecontent {
                /*width: 21cm;*/
                /*min-height: 29.7cm;*/
                padding-top: 5mm;
                padding-bottom: 5mm;
                /*margin: 1cm auto;*/
/*                border: 1px #D3D3D3 solid;
                border-radius: 5px;
                background: white;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
                page-break-after: always;*/
            }
        }

        /*.subpage {*/
        /*    padding: 1cm;*/
        /*    border: 5px red solid;*/
        /*    height: 256mm;*/
        /*    outline: 2cm #FFEAEA solid;*/
        /*}*/

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
                /*padding-top: 1mm;*/
                /*padding-bottom: 1mm;*/
                /*margin: 1cm auto*/
                /*border: initial;*/
                /*border-radius: initial;*/
                width: initial;
                min-height: initial;
                /*box-shadow: initial;*/
                /*background: white;*/
                page-break-after: always;
                page-break-inside: avoid;
            }
        }


        dl {
            /*border: 3px double #ccc;*/
            padding: 0.5em;
        }
        dt {
            float: left;
            clear: left;
            width: 16ch;
            text-align: left;
            font-weight: bold;
            /*color: green;*/
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

        h3 {
            font: 10pt "Times";
        }

        .left-caption td h3 {
            text-align: left;
        }

        figure {
            /*float: left;*/
            max-width: 100%;
            max-height: 100%;
            width: fit-content;
            height: fit-content;
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
            /*max-height: 100%;*/
            width: fit-content;
        }

        figcaption {
            padding-left: 0.2em;
        }


    </style>
</head>
<body>
<?php if(!empty($rs2) && $rs2->count()>0) { ?>
    <div class="pagecontent">
        <h2>Images:</h2>
<?php
    foreach ($rs2 as $row2) {
        ?>


        <figure>
            <img class=scaled src="/bgs/images/large/<?=$row2['filename']?>" alt="">
            <figcaption><h3><?=$row2["caption"]?></h3></figcaption>
        </figure>

        <?php
    }
}

?>
    </div>
</body>
</html>