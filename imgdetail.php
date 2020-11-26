<?php
$imgname = '';
if (isset($_REQUEST['i']) && $_REQUEST['i'] != "") {
    $imgname = $_REQUEST['i'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $imgname; ?></title>
</head>

<body>
<a href="javascript:window.close()">Close</a><br>
<img alt="" src="images/large/<?php echo $imgname; ?>">
</body>
</html>