<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

$UserId = $_REQUEST["userid"];

$noticeCountRes = $app->getDBConnection()->fetchAll("SELECT count([id]) as 'NUM' FROM [Notification] WHERE ToUserID = ? and [Status]=0 ", $UserId);

foreach ($noticeCountRes as $row) {
    $TotalUnreadNotice = $row->NUM;
    echo '{"success": "' . $TotalUnreadNotice . '"}';
}