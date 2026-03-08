<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Config/config.php';
include '../Lib/lib.php';

$UserID = xss_clean($_REQUEST['UserID']);
$Latitude = xss_clean($_REQUEST['Lat']);
$Longitude = xss_clean($_REQUEST['Long']);
$LocationString = $Latitude . ',' . $Longitude;

$AuthToken = $_REQUEST['authToken'];

if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
} else {
    $user = $app->getDBConnection()->fetch("SELECT id FROM userinfo WHERE id = ? AND IsActive = '1'", $UserID);

    if (count($user) > 0) {
        $Field = "UserId, Location, DateTime";
        $Value = "'$UserID', N'$LocationString', GETDATE()";

        if (Save('UserLiveLocation', $Field, $Value)) {
            echo "Success! Location updated successfully.";
        } else {
            echo "Failed! Something went wrong.";
        }
    } else {
        echo "User not found!";
    }
}
