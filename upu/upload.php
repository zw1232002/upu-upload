<?php
/**
 * $Id: upload.php,v 1.2 2005/09/22 02:17:10 legend9 Exp $
 */

set_time_limit(0);
ignore_user_abort();

header('Content-Type: text/html; charset=utf-8');
include("upu.class.php");

$processID = $_GET['processID'];

$myupload = new UPU();
$myupload->processID = $processID;

echo $myupload->processRequest();
echo "over";
?>