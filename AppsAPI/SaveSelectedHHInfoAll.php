<?php
// PHP 7.4 script using Nette Database to generate one CSV per row from SampleMapping query
// Each CSV is named downloaded_hh_info_{MainHHNumber}.csv and zipped into multiple_csvs.zip

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Config/config.php';
include '../Lib/lib.php';

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'D:\wwwroot\SAS_2\AppsAPI\php_errors.log');

// Verify PHP is executing
if (!defined('PHP_VERSION')) {
    header('Content-Type: text/plain');
    http_response_code(500);
    echo "Error: PHP is not properly configured on the server";
    exit;
}

// Validate query parameters
if (!isset($_REQUEST['userid'], $_REQUEST['psuNo'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: userid, psuNo']);
    exit;
}

// Sanitize inputs
$userID = xss_clean($_REQUEST['userid']);
$psuNo = xss_clean($_REQUEST['psuNo']);

if (!$userID || !$psuNo) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameter values']);
    exit;
}

// Query to fetch rows
$query = "SELECT PSU, UserID, HHeadName, MobileNumber, HHAddress, MainHHNumber, SampleHHNumber, geopoint 
          FROM SampleMapping 
          WHERE UserID = ? AND PSU = ?";

try {
    // Execute query using Nette Database
    $result = $app->getDBConnection()->query($query, $userID, $psuNo)->fetchAll();

    // Temporary directory for CSVs
    $tempDir = sys_get_temp_dir() . '/csv_export_' . uniqid();
    if (!mkdir($tempDir)) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create temporary directory']);
        error_log('Failed to create temp directory: ' . $tempDir);
        exit;
    }

    $csvFiles = [];
    $headers = ['SL', 'PSU', 'UserID', 'HHeadName', 'MobileNumber', 'HHAddress', 'MainHHNumber', 'SampleHHNumber', 'geopoint'];
    $sl = 1;

    // Generate one CSV per row
    foreach ($result as $row) {
        $mainHHNumber = filter_var($row->MainHHNumber, FILTER_SANITIZE_NUMBER_INT);
        if (!$mainHHNumber) {
            error_log('Invalid MainHHNumber for row: ' . json_encode($row));
            continue;
        }

        $csvFileName = "downloaded_hh_info_$mainHHNumber.csv";
        $csvFilePath = $tempDir . '/' . $csvFileName;
        $output = fopen($csvFilePath, 'w');
        if ($output === false) {
            error_log('Failed to open CSV file: ' . $csvFilePath);
            continue;
        }

        // Add UTF-8 BOM
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, $headers);

        // Write row with SL
        $data = [
            $sl,
            $row->PSU,
            $row->UserID,
            $row->HHeadName,
            $row->MobileNumber,
            $row->HHAddress,
            $row->MainHHNumber,
            $row->SampleHHNumber,
            $row->geopoint
        ];
        fputcsv($output, $data);

        fclose($output);
        $csvFiles[] = $csvFilePath;
    }

    if (empty($csvFiles)) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'No data found for UserID: ' . $userID . ', PSU: ' . $psuNo]);
        error_log('No data for UserID: ' . $userID . ', PSU: ' . $psuNo);
        array_map('unlink', glob($tempDir . '/*'));
        rmdir($tempDir);
        exit;
    }

    // Create ZIP file
    $zipFile = $tempDir . '/multiple_csvs.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create ZIP file']);
        error_log('Failed to create ZIP: ' . $zipFile);
        array_map('unlink', glob($tempDir . '/*'));
        rmdir($tempDir);
        exit;
    }

    foreach ($csvFiles as $csvFile) {
        $zip->addFile($csvFile, basename($csvFile));
    }
    $zip->close();

    // Set headers for ZIP download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="multiple_csvs.zip"');
    header('Content-Length: ' . filesize($zipFile));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output ZIP file
    readfile($zipFile);

    // Clean up
    array_map('unlink', glob($tempDir . '/*'));
    rmdir($tempDir);
    exit;

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    error_log('Exception: ' . $e->getMessage());
    if (isset($tempDir)) {
        array_map('unlink', glob($tempDir . '/*'));
        rmdir($tempDir);
    }
    exit;
}
?>
