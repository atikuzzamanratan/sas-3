<?php

//https://cpsbbsbd.com/AppsAPI/AppAuthCheckerForDataCollect.php?username=cd0095&password=cd0095&authToken=Mwgq0LcFGSHvYdVzb1Ifq3L9lhWmi4IXBDWcQZR9hUt1q7UboELrUFVJZO244Ujo

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Config/config.php';
include '../Lib/lib.php';

$AuthToken = $_REQUEST['authToken'];
$UserName = $_REQUEST['username'];
$Password = $_REQUEST['password'];

if ($AuthToken != $AuthTokenValue) {
    $response["success"] = 0;
    $response["fullname"] = "Null";
    $response["message"] = $unAuthorizedMsg;
} else {
    $user = $app->getDBConnection()->fetch('select id, UserName, enc_passw, FullName from userinfo where UserName = ? and IsActive = 1', $UserName);
    $uid = $user->id;
    if (count($user) > 0) {
        $isPasswordVerified = password_verify($Password, $user->enc_passw);
        if ($isPasswordVerified) {
            $app->getDBConnection()->query("UPDATE userinfo SET IsOnline='1' Where id = ?", $user->id);
            Save('UserLogStatus', 'UserId, Status', "$uid, 1");
            $response["success"] = $user->id;
            $response["fullname"] = $user->FullName;
            $response["message"] = "Hello " . $user->FullName . " (" . $user->UserName . "). Your Login is successful!";
        } else {
            $response["success"] = 0;
            $response["fullname"] = "Null";
            $response["message"] = "Login Failed! Invalid Password!";
        }
    } else {
        $response["success"] = 0;
        $response["fullname"] = "Null";
        $response["message"] = "Login Failed! Invalid User Name or User not active!";
    }
}
echo json_encode($response);
