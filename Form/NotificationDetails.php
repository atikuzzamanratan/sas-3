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
require_once "../Config/config.php";
require_once "../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$id = xss_clean($_REQUEST["id"]);
$UserName = $_SESSION['User'];

$FormName = getName($cn, getDBMain(), 'FormName', 'datacollectionform', $FormID);

$qry1 = $app->getDBConnection()->query("Update [Notification] SET Status = 1, NotificationReadTime = getdate()  WHERE id = ? AND NotificationReadTime IS NULL ", $id);
$qry = $app->getDBConnection()->query("SELECT [FromUserID],[ToUserID],[Notification],[DataEntryDate] FROM [Notification] WHERE id = ?", $id);
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
                <?php
                foreach ($qry as $row) {
                    ?>
                    <tr>
                        <td>From User</td>
                        <td><?php echo $UserName = getName($cn, getDBMain(), 'UserName', 'userinfo', $row['FromUserID']); ?></td>
                    </tr>
                    <tr>
                        <td>To User</td>
                        <td><?php echo $UserName = getName($cn, getDBMain(), 'UserName', 'userinfo', $row['ToUserID']); ?></td>
                    </tr>
                    <tr>
                        <td>Notification</td>
                        <td><?php echo $row->Notification; ?></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td><?php echo $row->DataEntryDate; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
