<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  crm-now, www.crm-now.com
* Portions created by crm-now are Copyright (C)  crm-now c/o im-netz Neue Medien GmbH.
* All Rights Reserved.
 *
 ********************************************************************************/
require_once('modules/'.$_REQUEST[module].'/pdfcreator.php');

global $adb,$app_strings,$focus,$current_user;
createpdffile ($_REQUEST[record],'send');
echo "<script>window.history.back();</script>";
exit();
?>
