<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Config/config.php';
include '../Lib/lib.php';

$UserID = xss_clean($_REQUEST['UserID']);
$OldPass = xss_clean($_REQUEST['OldPass']);
$NewPass = xss_clean($_REQUEST['NewPass']);

$AuthToken = $_REQUEST['authToken'];

if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
} else {
    $user = $app->getDBConnection()->fetch("SELECT id FROM userinfo WHERE id = ? AND Password = ? AND IsActive = '1'", $UserID, $OldPass);

    if (count($user) > 0) {
        $encPassword = password_hash($NewPass, PASSWORD_DEFAULT);

        $param = "Password='$NewPass', enc_passw='$encPassword'";
        $cond = "id='$UserID' and Password='$OldPass'";

        if (Edit('userinfo', $param, $cond)) {
            echo "Success! Password updated successfully.";
        } else {
            echo "Failed! Something went wrong.";
        }
    } else {
        echo "User not found!";
    }
}
