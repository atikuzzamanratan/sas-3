<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['par'] != '') {
    $param = $app->cleanInput($_REQUEST['par']);
}

if ($_REQUEST['id'] != '') {
    $CompanyID = $app->cleanInput($_REQUEST['id']);
}

if ($param === '1') {
    $qry = "SELECT doc.CompanyName, pck.description, phtr.amount, phtr.createdDate, phtr.paymentType 
    FROM payment_history phtr
    JOIN packages pck ON (pck.id = phtr.packageId) 
    JOIN dataownercompany doc ON (doc.id = phtr.companyId) 
    ORDER BY phtr.id DESC";
    $resQry = $app->getDBConnection()->fetchAll($qry);
} else {
    $qry = "SELECT doc.CompanyName, pck.description, phtr.amount, phtr.createdDate, phtr.paymentType 
    FROM payment_history phtr
    JOIN packages pck ON (pck.id = phtr.packageId) 
    JOIN  dataownercompany doc ON (doc.id = phtr.companyId) 
    WHERE phtr.companyId = ? 
    ORDER BY phtr.id DESC";
    $resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID);
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $CompanyName = $row->CompanyName;
    $PackageName = $row->description;
    $Amount = $row->amount;
    $CreateDate = date_format($row->createdDate, "d-m-Y");

    if ($row->paymentType == PACKAGE_PAYMENT_TYPE_ID) {
        $PaymentType = "Package purchase";
    } else {
        $PaymentType = "Upload Credit purchase";
    }

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $CompanyName;
    $SubData[] = $PackageName;
    $SubData[] = $Amount;
    $SubData[] = $PaymentType;
    $SubData[] = $CreateDate;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

