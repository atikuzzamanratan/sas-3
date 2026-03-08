<?php header('Content-type: text/html; charset=UTF-8'); ?>

<link rel="stylesheet" href="../assets/css/styles.css?=121">
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600' rel='stylesheet' type='text/css'>
<link href='../assets/demo/variations/default.css' rel='stylesheet' type='text/css' media='all' id='styleswitcher'>
<link href='../assets/demo/variations/default.css' rel='stylesheet' type='text/css' media='all' id='headerswitcher'>
<link rel='stylesheet' type='text/css' href='../assets/plugins/form-daterangepicker/daterangepicker-bs3.css'/>
<link rel='stylesheet' type='text/css' href='../assets/plugins/fullcalendar/fullcalendar.css'/>
<link rel='stylesheet' type='text/css' href='../assets/plugins/form-markdown/css/bootstrap-markdown.min.css'/>
<link rel='stylesheet' type='text/css' href='../assets/plugins/codeprettifier/prettify.css'/>
<link rel='stylesheet' type='text/css' href='../assets/plugins/form-toggle/toggles.css'/>

<?PHP
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

$id = xss_clean($_REQUEST['id']);

require_once "../Config/config.php";
require_once "../Lib/lib.php";
$cn = ConnectDB();

$updateQueryString = "UPDATE Notification SET Status = ?, NotificationReadTime = getdate() where id = ?  AND NotificationReadTime IS NULL";
$app->getDBConnection()->query($updateQueryString, 1, $id);

$qry = "SELECT FromUserID, ToUserID, Notification, DataEntryDate FROM Notification WHERE id = ?";
$qryRes = $app->getDBConnection()->fetch($qry, $id);
$fromUserID = $qryRes->FromUserID;
$toUserID = $qryRes->ToUserID;
$notificationText = $qryRes->Notification;
$notificationEntryDate = $qryRes->DataEntryDate;

$fromUserName = getName($cn, getDBMain(), 'UserName', 'userinfo', $fromUserID);
$toUserName = getName($cn, getDBMain(), 'UserName', 'userinfo', $toUserID);
?>

<div class="container">
    <div class="panel panel-sky">
        <div class="panel-heading">
            <h4>Notification Detail</h4>
        </div>
        <div class="panel-body collapse in table-responsive">
            <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered datatables"
                   id="example">
                <thead>
                <tr role="row">
                    <th>Column Name</th>
                    <th>Column Value</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>From User</td>
                    <td><?php echo $fromUserName; ?></td>
                </tr>
                <tr>
                    <td>To User</td>
                    <td><?php echo $toUserName; ?></td>
                </tr>
                <tr>
                    <td>Notification</td>
                    <td><?php echo $notificationText; ?></td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td><?php echo $notificationEntryDate; ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
