<?php

// --- Master Error Capture Setup ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');   // All PHP errors go to debug.log
error_reporting(E_ALL | E_STRICT);              // Catch everything, including deprecated/warnings
set_time_limit(0);

// --- Global error handler ---
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $msg = "[" . date('c') . "] [PHP-$errno] $errstr ($errfile:$errline)\n";
    @file_put_contents(__DIR__ . '/debug.log', $msg, FILE_APPEND);
    echo "<pre style='color:red;'>$msg</pre>";  // also print in browser
    return false; // let PHP also handle normally
});

// --- Global exception handler ---
set_exception_handler(function ($e) {
    $msg = "[" . date('c') . "] [UNCAUGHT] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    @file_put_contents(__DIR__ . '/debug.log', $msg, FILE_APPEND);
    echo "<pre style='color:darkred;'>$msg</pre>";
    return false;
});

// --- Shutdown handler for fatal errors ---
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err) {
        $msg = "[" . date('c') . "] [SHUTDOWN] {$err['message']} ({$err['file']}:{$err['line']})\n";
        @file_put_contents(__DIR__ . '/debug.log', $msg, FILE_APPEND);
        echo "<pre style='color:maroon;'>$msg</pre>";
    }
});

require '../vendor/autoload.php';
include "../Config/config.php";
include "../Lib/lib.php";

use Solvers\Dsql\Application;
use Solvers\Dsql\ODKAPIClient;

// --- Clear debug log ---
file_put_contents(__DIR__ . "/debug.log", "");

// --- Initialize application ---
$app = new Application();

function log_dbg($msg)
{
    @file_put_contents(__DIR__ . "/debug.log", "[" . date('c') . "] " . $msg . "\n", FILE_APPEND);
}

// --- Random UUID generator ---
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    $charactersLength = strlen($characters);
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getInstanceID()
{
    return generateRandomString(8) . "-" . generateRandomString(4) . "-" .
        generateRandomString(4) . "-" . generateRandomString(4) . "-" .
        generateRandomString(12);
}

// --- Fetch info from URL / database ---
$xFormId = $_GET['xFormId'] ?? null;
if (!$xFormId) exit("xFormId not provided");

$userId    = getValue("xformrecord", "UserID", "id=$xFormId");
$userName  = getValue("userinfo", "UserName", "id=$userId");
$formId    = getValue("xformrecord", "FormId", "id=$xFormId");
$companyId = getValue("xformrecord", "CompanyId", "id=$xFormId");
$xmlFormId = getValue("datacollectionform", "ODKFormID", "id=$formId");
$projectId = getenv('ODK_PROJECT_ID') ?: (function_exists('env') ? env('ODK_PROJECT_ID') : null);

if (!$projectId) exit("ODK_PROJECT_ID not configured.");

$sql = "SELECT 
            xg.GroupName, 
            COALESCE(xgp.GroupName, '-') AS ParentGroupName,
            xfrg.ColumnName AS DataName, 
            COALESCE(un.ColumnValue, '') AS ColumnValue,
            CASE 
                -- Hide carried-over or resolved comments
                WHEN un.IsEdited = 0 
                     AND (un.Comments LIKE '%Edited at%' 
                          OR un.Comments LIKE '%Says at%' 
                          OR un.Comments LIKE '%Previous Value:%') 
                THEN ''
                WHEN un.IsCorrected = 1 THEN ''
                ELSE COALESCE(un.Comments, '')
            END AS Comments
        FROM xformColumnNameForGroup xfrg
        JOIN xformGroupName xg 
            ON xfrg.GroupId = xg.id
        LEFT JOIN xformGroupName xgp 
            ON xg.parent_id = xgp.id
        LEFT JOIN masterdatarecord_UnApproved un 
            ON xfrg.ColumnName = un.ColumnName 
            AND xfrg.FormId = un.FormId 
            AND un.XFormRecordId = $xFormId
        WHERE xfrg.FormId = $formId 
          AND xfrg.CompanyId = $companyId
        ORDER BY xg.id, xfrg.id";

$xfrs = $app->getDBConnection()->fetchAll($sql);

// --- Merge duplicate rows (keep comment if present) ---
$merged = [];
foreach ($xfrs as $row) {
    $col = $row->DataName ?? $row['DataName'];

    // if not seen before → save directly
    if (!isset($merged[$col])) {
        $merged[$col] = $row;
    }
    // if seen before → prefer one that has a non-empty comment
    else {
        $existingComment = trim($merged[$col]->Comments ?? $merged[$col]['Comments'] ?? '');
        $newComment      = trim($row->Comments ?? $row['Comments'] ?? '');

        if ($existingComment === '' && $newComment !== '') {
            $merged[$col] = $row;  // replace with the one that has comment
        }
        // if both have comments, keep the first one (no change)
    }
}
$xfrs = array_values($merged);
log_dbg("Merged duplicates; total unique fields = " . count($xfrs));

// ✅ PROOF STEP 1: dump prefill data retrieved from DB
file_put_contents(__DIR__ . "/last_prefill_dump.json", json_encode($xfrs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if (count($xfrs) == 0) {
    log_dbg("No rows for xFormId=$xFormId");
    exit("Record not found under Un-Approved Section");
}

// --- User or Admin login based on role ---
$odk = new ODKAPIClient();

if ($userName === 'admin') {
    // keep existing admin behavior
    try {
        $odk->adminLogin();
        log_dbg("Logged in as ADMIN");
    } catch (\Throwable $e) {
        log_dbg("Admin login failed: " . $e->getMessage());
        exit("ODK API error: " . $e->getMessage());
    }
} else {
    // normal user login for form view
    try {
        // fetch from DB
        $userEmail = getValue("userinfo", "EmailAddress", "id=$userId");
        $userPassword = getValue("userinfo", "Password", "id=$userId");

        // log what we got
        log_dbg("Fetched from DB: userId=$userId, userEmail='$userEmail', userPassword='$userPassword'");

        // fallback if no email in DB
        if (empty($userEmail)) {
            $userEmail = strtolower($userName) . "@gmail.com";
            log_dbg("No EmailAddress found in DB — fallback to $userEmail");
        }

        // ensure password length >= 10
        if (strlen($userPassword) < 10) {
            $userPassword = str_pad($userPassword, 10, "0", STR_PAD_RIGHT);
            log_dbg("User password padded to 10 chars for ODK: $userPassword");
        }

        // attempt ODK login or create
        $odk->loginOrCreateUser($userEmail, $userPassword);
        log_dbg("Logged in as normal user $userEmail");
    } catch (\Throwable $e) {
        log_dbg("User login failed: " . $e->getMessage());
        exit("ODK user login failed: " . $e->getMessage());
    }
}

// --- Get form version ---
$version = $odk->getFormVersion($projectId, $xmlFormId);
log_dbg("Form version for $xmlFormId: " . var_export($version, true));

// ✅ Build canonical casing map for groups and parent groups
$canonGroup = [];
$canonParent = [];

foreach ($xfrs as $r) {
    $gk = strtolower($r->GroupName);
    if (!isset($canonGroup[$gk]) || preg_match('/^[A-Z]/', (string)$r->GroupName)) {
        $canonGroup[$gk] = $r->GroupName;
    }

    if ($r->ParentGroupName !== '-') {
        $pk = strtolower($r->ParentGroupName);
        if (!isset($canonParent[$pk]) || preg_match('/^[A-Z]/', (string)$r->ParentGroupName)) {
            $canonParent[$pk] = $r->ParentGroupName;
        }
    }
}

// --- Build submission XML ---
$instanceUUID = getInstanceID();

$xml = "<?xml version='1.0' encoding='UTF-8'?>";
// $xml .= "<data id='$xmlFormId'" . ($version ? " version='" . htmlspecialchars($version, ENT_QUOTES) . "'" : "") . " ";

// remove spaces to match real xmlFormId
$xml .= "<data id='" . htmlspecialchars($xmlFormId, ENT_QUOTES) . "'" .
    ($version ? " version='" . htmlspecialchars($version, ENT_QUOTES) . "'" : "") .
    " xFormRecordId='$xFormId' ";

$xml .= "xmlns:ev='http://www.w3.org/2001/xml-events' ";
$xml .= "xmlns:orx='http://openrosa.org/xforms' ";
$xml .= "xmlns:odk='http://www.opendatakit.org/xforms' ";
$xml .= "xmlns:h='http://www.w3.org/1999/xhtml' ";
$xml .= "xmlns:xsd='http://www.w3.org/2001/XMLSchema' ";
$xml .= "xmlns:jr='http://openrosa.org/javarosa'>";


// --- Build submission XML dynamically like CPSMain ---
$currentGroup = '';
$currentParentGroup = '';

// --- Loop through fields ---
foreach ($xfrs as $xfr) {
    $groupName       = $xfr->GroupName;
    $parentGroupName = $xfr->ParentGroupName;

    // ✅ Normalize groups to canonical casing
    $groupNameKey = strtolower($xfr->GroupName);
    $parentGroupNameKey = strtolower($xfr->ParentGroupName);

    $groupName = $canonGroup[$groupNameKey] ?? $xfr->GroupName;
    $parentGroupName = ($xfr->ParentGroupName !== '-')
        ? ($canonParent[$parentGroupNameKey] ?? $xfr->ParentGroupName)
        : '-';

    $columnName      = $xfr->DataName;
    $columnValue     = $xfr->ColumnValue;
    $comments        = $xfr->Comments;

    // Close previous groups if needed
    if ($currentGroup != '' && $currentGroup != $groupName) {
        $xml .= "</$currentGroup>";
        $currentGroup = '';
    }
    if ($currentParentGroup != '' && $currentParentGroup != $parentGroupName && $parentGroupName != '-') {
        $xml .= "</$currentParentGroup>";
        $currentParentGroup = '';
    }

    // Open parent group if needed
    if ($parentGroupName != '-' && $currentParentGroup != $parentGroupName) {
        $appearance = isset($groupHighlight[$parentGroupName]) ? " class='highlight'" : "";
        $xml .= "<$parentGroupName$appearance>";
        $currentParentGroup = $parentGroupName;
    }

    // Open current group
    if ($currentGroup != $groupName) {
        $appearance = isset($groupHighlight[$groupName]) ? " class='highlight'" : "";
        $xml .= "<$groupName$appearance>";
        $currentGroup = $groupName;
    }

    // Add field
    $xml .= "<$columnName>" . htmlspecialchars($columnValue) . "</$columnName>";

    // // Add comment note if exists
    // if ($comments !== '') {
    //     // Strip HTML and normalise spaces
    //     $cleanComment = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($comments))));

    //     // Format it nicely: "Comment: Hello"
    //     $formattedComment = "Comment: " . $cleanComment;

    //     // Match XLSForm note field naming convention
    //     $commentNode = "{$columnName}_comment";

    //     // Insert into XML
    //     $xml .= "<$commentNode>" . htmlspecialchars($formattedComment) . "</$commentNode>";

    //     log_dbg("Injected comment into field '$commentNode': $formattedComment");
    // }

    // Add latest comment note if exists
    if ($comments !== '') {
        // Decode and normalize HTML entities and line breaks
        $decoded = html_entity_decode($comments, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = str_ireplace(['<br>', '<br/>', '<br />'], "\n", $decoded);

        // Split by user markers like "Says at" or "Edited at"
        $commentParts = preg_split('/(?=\b\S+\s+(Says|Edited)\s+at\b)/i', $decoded, -1, PREG_SPLIT_NO_EMPTY);

        // Take only the latest comment block
        $latestCommentBlock = trim(end($commentParts));

        // Extract only the text after the last timestamp colon
        if (preg_match('/\d{2}:\d{2}:\d{2}:\s*(.+)$/s', $latestCommentBlock, $match)) {
            // e.g. matches "16:41:57:   Grameen" → "Grameen"
            $latestComment = trim($match[1]);
        } else {
            // fallback if no timestamp found
            $latestComment = trim(strip_tags($latestCommentBlock));
        }

        // Clean leftover HTML tags and normalize spaces
        $latestComment = trim(preg_replace('/\s+/', ' ', strip_tags($latestComment)));

        // Format for display in XML
        $formattedComment = "Comment: " . $latestComment;

        // Match XLSForm note field naming convention
        $commentNode = "{$columnName}_comment";

        // Insert into XML
        $xml .= "<$commentNode>" . htmlspecialchars($formattedComment) . "</$commentNode>";

        log_dbg("Injected latest clean comment into field '$commentNode': $formattedComment");
    }
}

// Close any remaining groups
if ($currentGroup != '') $xml .= "</$currentGroup>";
if ($currentParentGroup != '') $xml .= "</$currentParentGroup>";

// --- Meta info ---
$xml .= "<meta><instanceID>uuid:$instanceUUID</instanceID>";
$xml .= "<submittedBy>$userName</submittedBy></meta></data>";

// ✅ PROOF STEP 2: dump final prefilled instance XML before validation
file_put_contents(__DIR__ . "/last_instance.xml", $xml);

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
if (!$dom->loadXML($xml)) {
    log_dbg("❌ XML is not well-formed: " . print_r(libxml_get_errors(), true));
} else {
    log_dbg("✅ XML validated successfully before submission");
}


log_dbg("Built XML length=" . strlen($xml) . " instanceUUID=$instanceUUID");
file_put_contents(__DIR__ . "/generated.xml", $xml);
// --- Submit XML ---
$submitted = $odk->submitXML($projectId, $xmlFormId, $xml);
if (!$submitted && $odk->httpcode !== 409) {
    log_dbg("Submission failed: HTTP {$odk->httpcode} msg=" . $odk->errorMessage);
    exit("Submission failed: " . $odk->errorMessage);
}

// --- Get Enketo edit URL ---
$instanceIdForEdit = "uuid:" . $instanceUUID;
$editUrl = $odk->getEnketoEditUrl($projectId, $xmlFormId, $instanceIdForEdit);

$baseUrl = env('APP_URL');
$returnUrl = $baseUrl . "webhookURLs/editTrigger.php/$xmlFormId/$instanceUUID";

if (strpos($editUrl, '?') !== false) {
    $editUrl .= "&return_url=" . urlencode($returnUrl);
} else {
    $editUrl .= "?return_url=" . urlencode($returnUrl);
}


// ? Attach the UUID as query parameter for Enketo JS to read
if (strpos($editUrl, '?') !== false) {
    $editUrl .= "&review_uuid=" . urlencode($instanceUUID);
} else {
    $editUrl .= "?review_uuid=" . urlencode($instanceUUID);
}

$publicLink = "https://sdcedit.com/f/xVcn0bWv8100GtjSDvfhoPZNB1fW5W1";

if (!empty($editUrl)) {
    log_dbg("Redirecting to Enketo edit URL: $editUrl");
    header("Location: " . $editUrl);
    exit;
}

// Fallback public link
log_dbg("No edit URL or edit failed, fallback to public link");
header("Location: " . $publicLink);
exit;
