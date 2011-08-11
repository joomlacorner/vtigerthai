<?php
require_once('modules/'.$_REQUEST[module].'/pdfcreator.php');

global $adb,$app_strings,$focus,$current_user;
createpdffile ($_REQUEST[record],'send');
// Added to fix annoying bug that includes HTML in your PDF
//echo "<script>openPopUp('xComposeEmail',this,iurl,'createemailWin',830,662,opts);</script>";
echo "<script>window.history.back();</script>";
exit();
?>
