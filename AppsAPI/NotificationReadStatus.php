<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

$NotifyID = xss_clean($_REQUEST['NotifyID']);
$Status = xss_clean($_REQUEST['Status']);

$updateQueryString = "UPDATE [dbo].[Notification] SET [Status] = ?, NotificationReadTime = getdate() where id = ?  AND NotificationReadTime IS NULL";
$qryRes = $app->getDBConnection()->query($updateQueryString, $Status, $NotifyID);

if (true) {
    $response["success"] = 1;
} else {
    $response["success"] = 0;
}

echo json_encode($response);
