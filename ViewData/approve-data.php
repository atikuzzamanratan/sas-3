<?php
session_start();

error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$id = xss_clean($_REQUEST['id']);
$tbl_name = xss_clean($_REQUEST['tbl']);

$loggedUserName = $app->cleanInput($_SESSION['User']);
$loggedUserID = $app->cleanInput($_SESSION['UserID']);

if (strpos($loggedUserName, 'val') === false) {
	$param = "IsApproved = 1, IsChecked = 0";
} else {
	$param = "IsApproved = 1, IsChecked = 0, ValidationDate=CURRENT_TIMESTAMP, ValidatorID=$loggedUserID";
}

$cond = "id = '$id'";

if (Edit($tbl_name, $param, $cond)) {
    $msg = 'Successfully updated.';


    // ✅ Post-trigger fix: Restore IsCorrected=1 for edited fields after approval
    // (because trigger resets IsCorrected to 0)
    try {
        $xFormRecordId = (int)$id;
        $fixSql = "UPDATE masterdatarecord_Approved
                   SET IsCorrected = 1
                   WHERE XFormRecordId = $xFormRecordId
                     AND IsEdited = 1";
        $app->getDBConnection()->query($fixSql);
    } catch (Exception $e) {
        file_put_contents(__DIR__.'/../debug.log',
            '['.date('c')."] approve-data.php | Post-trigger IsCorrected fix failed: ".$e->getMessage()."\n",
            FILE_APPEND
        );
    }

} else {
    $msg = 'Failed to update data!';
}
echo $msg;

