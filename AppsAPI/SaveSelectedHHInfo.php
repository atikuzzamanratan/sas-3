<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Config/config.php';
include '../Lib/lib.php';

$userID = xss_clean($_REQUEST['UserID']);
$psuNo = xss_clean($_REQUEST['PsuNo']);
$mainHHNumber = xss_clean($_REQUEST['MainHHNumber']);

// Prepare and execute query with parameter binding
$query = "SELECT PSU, UserID, HHeadName, MobileNumber, HHAddress, MainHHNumber, SampleHHNumber, geopoint 
FROM SampleMapping WHERE UserID = $userID AND PSU = $psuNo AND MainHHNumber = $mainHHNumber";
$queryRes = $app->getDBConnection()->fetch($query);

// Prepare CSV data
$data = [['SL', 'PSU', 'UserID', 'HHeadName', 'MobileNumber', 'HHAddress', 'MainHHNumber', 'SampleHHNumber', 'geopoint']]; // Headers

$data[] = [
	1,
	$queryRes->PSU,
	$queryRes->UserID,
	$queryRes->HHeadName,
	$queryRes->MobileNumber,
	$queryRes->HHAddress,
	$queryRes->MainHHNumber,
	$queryRes->SampleHHNumber,
	$queryRes->geopoint
];


if (count($data) === 1) { // Only headers, no data
	header('Content-Type: application/json');
	http_response_code(404);
	echo json_encode(['error' => 'No data found for the specified parameters']);
	error_log('No data for UserID: ' . $userId . ', sampleHHNo: ' . $sampleHHNo . ', psuNo: ' . $psuNo);
	exit;
}

// Set headers to force CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="downloaded_hh_info.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Create a file pointer for output stream
$output = fopen('php://output', 'w');
if ($output === false) {
	header('Content-Type: application/json');
	http_response_code(500);
	echo json_encode(['error' => 'Server error: Unable to open output stream']);
	error_log('Failed to open php://output');
	exit;
}

// Add UTF-8 BOM for Excel and ODK compatibility
fwrite($output, "\xEF\xBB\xBF");

// Write data to CSV
foreach ($data as $row) {
	if (fputcsv($output, $row) === false) {
		header('Content-Type: application/json');
		http_response_code(500);
		echo json_encode(['error' => 'Server error: Failed to write CSV']);
		error_log('Failed to write row to CSV');
		fclose($output);
		exit;
	}
}

// Close the output stream
fclose($output);
exit;
