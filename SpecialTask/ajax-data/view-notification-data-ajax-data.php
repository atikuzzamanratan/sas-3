<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

$cn = ConnectDB();

// if (!empty($_POST)) {
if (isset($_POST)) {
    $request = $_REQUEST;

    $SelectedUserID = xss_clean($_REQUEST['DataUserID']);
    $SelectedDataStatus = xss_clean($_REQUEST['SelectedDataStatus']);
    $SelectedStartDate = xss_clean($_REQUEST['DataStartDate']);
    $SelectedEndDate = xss_clean($_REQUEST['DataEndDate']);
    $SelectedCheckAll = xss_clean($_REQUEST['DataChkAll']);
    $LoggedCompanyID = xss_clean($_REQUEST['LoggedCompanyID']);
    $LoggedUserID = xss_clean($_REQUEST['LoggedUserID']);

    $col = array(
        0 => 'id',                 // SL (just placeholder)
        1 => 'id',                 // ID
        2 => 'FromUserID',         // Sender
        3 => 'ToUserID',           // Recipient
        4 => 'Notification',       // Message
        5 => 'DataEntryDate',      // Send Time
        6 => 'NotificationReadTime', // Read Time
        7 => 'Status'              // Status
    );


    // if ($SelectedCheckAll == '1') {
    //     if ($SelectedDataStatus == '1' || $SelectedDataStatus == '0') {
    //         $qry = "SELECT ntf.id, ntf.ToUserID, ui.UserName, ui.FullName, ntf.FromUserID, 
    //        (SELECT UserName FROM userinfo WHERE id = ntf.FromUserID) FromUserName, 
    //        (SELECT FullName FROM userinfo WHERE id = ntf.FromUserID) FromUserFullName, 
    //        ntf.Notification, ntf.Status, ntf.DataEntryDate, ntf.NotificationReadTime 
    //        FROM Notification ntf JOIN userinfo ui ON ntf.ToUserID = ui.id 
    //        WHERE ntf.CompanyID = $LoggedCompanyID AND ntf.Status = '$SelectedDataStatus'";
    //     } else {
    //         $qry = "SELECT ntf.id, ntf.ToUserID, ui.UserName, ui.FullName, ntf.FromUserID, 
    //        (SELECT UserName FROM userinfo WHERE id = ntf.FromUserID) FromUserName, 
    //        (SELECT FullName FROM userinfo WHERE id = ntf.FromUserID) FromUserFullName, 
    //        ntf.Notification, ntf.Status, ntf.DataEntryDate, ntf.NotificationReadTime 
    //        FROM Notification ntf JOIN userinfo ui ON ntf.ToUserID = ui.id 
    //        WHERE ntf.CompanyID = $LoggedCompanyID";
    //     }
    // } else {
    //     if (!empty($SelectedUserID)) {
    //         if (!empty($SelectedStartDate) && !empty($SelectedEndDate)) {
    //             $qry = "SELECT ntf.id, ntf.ToUserID, ui.UserName, ui.FullName, ntf.FromUserID, 
    //            (SELECT UserName FROM userinfo WHERE id = ntf.FromUserID) FromUserName, 
    //            (SELECT FullName FROM userinfo WHERE id = ntf.FromUserID) FromUserFullName, 
    //            ntf.Notification, ntf.Status, ntf.DataEntryDate, ntf.NotificationReadTime
    //            FROM Notification ntf JOIN userinfo ui ON ntf.ToUserID = ui.id 
    //            WHERE ntf.CompanyID = $LoggedCompanyID AND (ntf.DataEntryDate BETWEEN '$SelectedStartDate' AND '$SelectedEndDate') 
    //            AND ntf.ToUserID = $SelectedUserID";
    //         } else {
    //             $qry = "SELECT ntf.id, ntf.ToUserID, ui.UserName, ui.FullName, ntf.FromUserID, 
    //            (SELECT UserName FROM userinfo WHERE id = ntf.FromUserID) FromUserName, 
    //            (SELECT FullName FROM userinfo WHERE id = ntf.FromUserID) FromUserFullName, 
    //            ntf.Notification, ntf.Status, ntf.DataEntryDate, ntf.NotificationReadTime
    //            FROM Notification ntf JOIN userinfo ui ON ntf.ToUserID = ui.id 
    //            WHERE ntf.CompanyID = $LoggedCompanyID AND ntf.ToUserID = $SelectedUserID";
    //         }
    //     }
    // }














    if ($SelectedCheckAll == '1') {

        // ✅ All users mode
        $qry = "SELECT ntf.id, ntf.ToUserID, ui.UserName, ui.FullName, ntf.FromUserID,
            (SELECT UserName FROM userinfo WHERE id = ntf.FromUserID) FromUserName,
            (SELECT FullName FROM userinfo WHERE id = ntf.FromUserID) FromUserFullName,
            ntf.Notification, ntf.Status, ntf.DataEntryDate, ntf.NotificationReadTime
            FROM Notification ntf
            JOIN userinfo ui ON ntf.ToUserID = ui.id
            WHERE ntf.CompanyID = $LoggedCompanyID";

        if ($SelectedDataStatus !== '' && $SelectedDataStatus !== null) {
            $qry .= " AND ntf.Status = '$SelectedDataStatus'";
        }

    } else {

        // ✅ Specific user mode
        if (!empty($SelectedUserID)) {
            $qry = "SELECT ntf.id, ntf.ToUserID, ui.UserName, ui.FullName, ntf.FromUserID,
                (SELECT UserName FROM userinfo WHERE id = ntf.FromUserID) FromUserName,
                (SELECT FullName FROM userinfo WHERE id = ntf.FromUserID) FromUserFullName,
                ntf.Notification, ntf.Status, ntf.DataEntryDate, ntf.NotificationReadTime
                FROM Notification ntf
                JOIN userinfo ui ON ntf.ToUserID = ui.id
                WHERE ntf.CompanyID = $LoggedCompanyID
                AND ntf.ToUserID = $SelectedUserID";

            // ✅ Add date range filter if present
            if (!empty($SelectedStartDate) && !empty($SelectedEndDate)) {
                $qry .= " AND (ntf.DataEntryDate BETWEEN '$SelectedStartDate' AND '$SelectedEndDate')";
            }

            // ✅ Add status filter even if it's '0'
            if ($SelectedDataStatus !== '' && $SelectedDataStatus !== null) {
                $qry .= " AND ntf.Status = '$SelectedDataStatus'";
            }
        }
    }















    if (!empty($request['search']['value'])) {
        $qry .= " AND (ntf.id like'" . $request['search']['value'] . "%'";
        $qry .= " OR ntf.ToUserID like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ui.UserName like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ui.FullName like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ntf.FromUserID like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ntf.Status like'%" . $request['search']['value'] . "%'";
        $qry .= " OR ntf.Notification like'%" . $request['search']['value'] . "%')";
    }

    $rs = db_query($qry, $cn);
    $TotalData = db_num_rows($rs);
    $totalFilter = $TotalData;

    if ($request['length'] < 0) {
        $qry .= " ORDER BY " . $col[$request['order'][0]['column']] . " " . $request['order'][0]['dir'];
    } else {
        $qry .= " ORDER BY " . $col[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . " OFFSET " . $request['start'] . " ROWS FETCH NEXT " . $request['length'] . " ROWS ONLY";
    }

    // file_put_contents(__DIR__ . '/_debug_notification_qry.txt',
    // date('Y-m-d H:i:s') . " => " . $qry . "\n", FILE_APPEND);

    $resQry = $app->getDBConnection()->fetchAll($qry);

    $data = array();
    $il = 1;

    foreach ($resQry as $row) {
        $RecordID = $row->id;

        $ToUserID = $row->ToUserID;
        $ToUserName = $row->UserName;
        $ToUserFullName = $row->FullName;
        $RecipientData = "$ToUserFullName ($ToUserName)";

        $SenderUserID = $row->FromUserID;
        $SenderUserName = $row->FromUserName;
        $SenderUserFullName = $row->FromUserFullName;
        $SenderData = "$SenderUserFullName ($SenderUserName)";

        $Message = $row->Notification;

        $EntryDate = date_format($row->DataEntryDate, 'd/m/Y h:i a');
        if (empty($row->NotificationReadTime)) {
            $ReadDate = "";
        } else {
            $ReadDate = date_format($row->NotificationReadTime, 'd/m/Y h:i a');
        }

        $Status = $row->Status;

        if ($Status == 1) {
            $DataStatus = "<font style='color: green'>Read</font>";
        } else {
            $DataStatus = "<font style='color: red'>Unread</font>";
        }

        $SubData = array();

        $SubData[] = $il;
        $SubData[] = $RecordID;
        $SubData[] = $SenderData;
        $SubData[] = $RecipientData;
        $SubData[] = $Message;
        $SubData[] = $EntryDate;
        $SubData[] = $ReadDate;
        $SubData[] = $DataStatus;

        $il++;

        $data[] = $SubData;
    }

    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => $TotalData,
        "recordsFiltered" => $totalFilter,
        "data" => $data
    );

    echo json_encode($json_data);
}

