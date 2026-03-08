<?php
//http://cps.local/AppsAPI/AppLogOut.php?UserID=68&authToken=Mwgq0LcFGSHvYdVzb1Ifq3L9lhWmi4IXBDWcQZR9hUt1q7UboELrUFVJZO244Ujo

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Config/config.php';
include '../Lib/lib.php';

$AuthToken = $_REQUEST['authToken'];
$UserID = $_REQUEST['UserID'];

$user = $app->getDBConnection()->fetch("SELECT id FROM userinfo WHERE id = ? AND IsActive = '1'", $UserID);

if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
} else {
    if (count($user) > 0) {
        $app->getDBConnection()->query("UPDATE userinfo SET IsOnline='0' Where id = ?", $UserID);
        Save('UserLogStatus', 'UserId, Status', "$UserID, 0");
        echo "Success!";
    } else {
        echo "Failed!";
    }
}
