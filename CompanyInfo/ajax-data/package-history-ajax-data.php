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
    $qry = "SELECT cpck.id, pck.name, pck.description, cpck.packageId, cpck.companyId, pck.maxUserNo, pck.amount, 
    cpck.uploadCredit, pck.storage, pck.formPerAcc, cpck.validityDate, cpck.createdOn, doc.CompanyName 
    FROM company_packages cpck
    JOIN packages pck ON (pck.id = cpck.packageId) 
    JOIN dataownercompany doc ON (doc.id = cpck.companyId) 
    ORDER BY cpck.id DESC";
    $resQry = $app->getDBConnection()->fetchAll($qry);
} else {
    $qry = "SELECT cpck.id, pck.name, pck.description, cpck.packageId, cpck.companyId, pck.maxUserNo, pck.amount, 
    cpck.uploadCredit, pck.storage, pck.formPerAcc, cpck.validityDate, cpck.createdOn, doc.CompanyName 
    FROM company_packages cpck
    JOIN packages pck ON (pck.id = cpck.packageId) 
    JOIN dataownercompany doc ON (doc.id = ?)
    WHERE companyId = ?";
    $resQry = $app->getDBConnection()->fetchAll($qry, $CompanyID);
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $Id = $row->id;
    $CompanyName = $row->CompanyName;
    $PackageName = $row->description;
    $Amount = $row->amount;
    $Validity = date_format($row->validityDate, "d-m-Y");
    $CreateDate = date_format($row->createdOn, "d-m-Y");

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $CompanyName;
    $SubData[] = $PackageName;
    $SubData[] = $Amount;
    $SubData[] = $Validity;
    $SubData[] = $CreateDate;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

