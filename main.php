<?php
global $errors, $page;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <meta name="keywords"
          content="barley genetic stocks, barley genetics, barley genes, barley alleles, barley chromosomes">
    <meta name="description" content="International database for barley genes and barley genetic stocks.">

    <title>Barley Genetic Stocks Databse</title>
    <link rel="stylesheet" type="text/css" href="style/main.css">

    <link href="jquery-ui-1.11.4.custom/jquery-ui.css" rel="stylesheet">
    <style type="text/css">
        img {
        }

        img.half {
            transform-origin: top left;
            -webkit-transform: scale(0.5); /* Saf3.1+, Chrome */
            -moz-transform: scale(0.5); /* FF3.5+ */
            -ms-transform: scale(0.5); /* IE9 */
            -o-transform: scale(0.5); /* Opera 10.5+ */
            transform: scale(0.5); /* IE6–IE9 */
            filter: progid:DXImageTransform.Microsoft.Matrix(M11=0.9999619230641713, M12=-0.008726535498373935, M21=0.008726535498373935, M22=0.9999619230641713, SizingMethod='auto expand');
        }
    </style>
    <script type="text/javascript" src="jquery-ui-1.11.4.custom/external/jquery/jquery.js"></script>
    <script type="text/javascript" src="jquery-ui-1.11.4.custom/jquery-ui.js"></script>

    <script type="text/javascript" src="script/bgs.js"></script>
    <script type="text/javascript">
        $(function () {

<?php if(isset($ul_columns) && count($ul_columns) > 0){ ?>
	//We have ul data columns list
	$(".ul_columns_li").bind("click", function(){
		if($(this).hasClass("ul_columns_li_sel")){
			$(this).removeClass("ul_columns_li_sel");
			$(this).addClass("ul_columns_li_unsel");
		}else{
			$(this).removeClass("ul_columns_li_unsel");
			$(this).addClass("ul_columns_li_sel");
		}
	});
	$("#selAllULColCB").attr("checked", false);
<?php } ?>

});
</script>
</head>

<body>
<?php
require_once("top.php");
?>
<div id="centerdiv">
    <!-- <hr id="topdivider" > -->
    <p id="frptitle">International Database for Barley Genes and Barley Genetic Stocks</p>
<?php
//Print any errors from the controller (index.php)
foreach($errors as $err){ ?>
<div class="errormess"><?php echo $err[1]; ?></div>
<?php } 
//require_once($centerpage);
require_once($page);
?>
</div>
<div id="footer" style="clear:both">
    <a href="https://www.nordgen.org"><img class="half" alt="NordGen logo" src="images/layout/Nordgen_logo.png"></a>
</div>
<!-- Large image -->
<div id="fullsize">
    <table class="minimize pad8" style="width:100%">
        <tr>
            <td id="fullsizeimg">&nbsp;</td>
            <td style="text-align: right; vertical-align: top"><p><br>
                    <a href="javascript:closeFullsize()" style="color:#000000;">CLOSE</a>&nbsp;&nbsp;
                    <br>
                    &nbsp;
            </td>
        </tr>
        <tr>
            <td colspan="2" class="largeimg_caption" id="largeimg_caption">&nbsp;</td>
        </tr>
    </table>
</div>

<!-- End large image -->
<!-- Webpage by Jonas Nordling, Komut Konsult -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-35686319-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());



    gtag('config', 'UA-35686319-1');
</script>
</body>
</html>