<?php

error_reporting(E_ALL);

require '../../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../../Config/config.php";
include "../../Lib/lib.php";

$RecordID = xss_clean($_REQUEST['id']);
$DataFromID = xss_clean($_REQUEST['dataFromID']);
$IsApproved = xss_clean($_REQUEST['status']);
$PSU = xss_clean($_REQUEST['psu']);
$LoggedUserID = xss_clean($_REQUEST['loggedUserID']);
$AgentID = xss_clean($_REQUEST['agentID']);
$XFormsFilePath = xss_clean($_REQUEST['XFormsFilePath']);

$LoggedUserName = getValue('userinfo', 'UserName', "id = $LoggedUserID");
$AgentFullName = getValue('userinfo', 'FullName', "id = $AgentID");
$AgentUserName = getValue('userinfo', 'UserName', "id = $AgentID");

$SupervisorPermission = "SELECT EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ? AND UserID = ?";
$RowSupervisorPermission = $app->getDBConnection()->fetch($SupervisorPermission, $LoggedUserID, $AgentID);
$EditPermission = $RowSupervisorPermission->EditPermission;
$DeletePermission = $RowSupervisorPermission->DeletePermission;
$ApprovePermission = $RowSupervisorPermission->ApprovePermission;

if ($IsApproved == 0) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelUnApproved_LineType $RecordID";
    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Pending WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 1) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelUnApproved_LineType $RecordID";
    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Approved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 2) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelUnApproved_LineType $RecordID";
    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_UnApproved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
}

$t = 0;
$startTime = '';
$endTime = '';
foreach ($MasterDataTimeRS as $row) {
    if ($t == 1) {
        $startTime = $row->ColumnValue;
    } else {
        $endTime = $row->ColumnValue;
    }
    $t++;
}

$start = strtotime($startTime);
$end = strtotime($endTime);
try {
    $start_date = new DateTime($startTime);
} catch (Exception $e) {
}
try {
    $since_start = $start_date->diff(new DateTime($endTime));
} catch (Exception $e) {
}

$Duration = '';
if ($since_start->d) {
    $Duration = $since_start->d . ' Days ';
}elseif ($since_start->h) {
    $Duration = $since_start->h . ' hours ';
}elseif ($since_start->i) {
    $Duration = $since_start->i . ' minutes ';
}elseif ($since_start->s) {
    $Duration = $since_start->s . ' seconds ';
}

function isUnicodeLetterOrMark($ch)
{
    if ($ch === '') return false;
    $code = mb_ord($ch, 'UTF-8');

    // Letter ranges (Lu, Ll, Lt, Lm, Lo)
    // These ranges are straight from the Unicode specification.
    if (
        ($code >= 0x0041 && $code <= 0x005A) ||   // A-Z
        ($code >= 0x0061 && $code <= 0x007A) ||   // a-z
        ($code >= 0x0900 && $code <= 0x097F) ||   // Indic scripts
        ($code >= 0x0980 && $code <= 0x09FF) ||   // Bengali
        ($code >= 0x0A00 && $code <= 0x0A7F) ||   // Gurmukhi
        ($code >= 0x0A80 && $code <= 0x0AFF) ||   // Gujarati
        ($code >= 0x0B00 && $code <= 0x0B7F) ||   // Oriya
        ($code >= 0x0B80 && $code <= 0x0BFF) ||   // Tamil
        ($code >= 0x0C00 && $code <= 0x0C7F) ||   // Telugu
        ($code >= 0x0C80 && $code <= 0x0CFF) ||   // Kannada
        ($code >= 0x0D00 && $code <= 0x0D7F) ||   // Malayalam
        ($code >= 0x0E00 && $code <= 0x0E7F) ||   // Thai
        ($code >= 0x0E80 && $code <= 0x0EFF) ||   // Lao
        ($code >= 0x10A0 && $code <= 0x10FF) ||   // Georgian
        ($code >= 0x1200 && $code <= 0x137F) ||   // Ethiopic
        ($code >= 0x1E00 && $code <= 0x1EFF) ||   // Latin Extended
        ($code >= 0x3040 && $code <= 0x309F) ||   // Hiragana
        ($code >= 0x30A0 && $code <= 0x30FF) ||   // Katakana
        ($code >= 0x4E00 && $code <= 0x9FFF) ||   // CJK Unified Ideographs
        ($code >= 0xAC00 && $code <= 0xD7AF)      // Hangul
    ) {
        return true;
    }

    // MARK ranges (Mn, Mc, Me)
    if (
        ($code >= 0x0300 && $code <= 0x036F) ||   // Combining diacritics
        ($code >= 0x1AB0 && $code <= 0x1AFF) ||
        ($code >= 0x1DC0 && $code <= 0x1DFF) ||
        ($code >= 0x20D0 && $code <= 0x20FF) ||
        ($code >= 0xFE20 && $code <= 0xFE2F)
    ) {
        return true;
    }

    return false;
}

// 1️⃣ Helper: detect if label already starts with number
function labelStartsWithValue($label, $choiceValue)
{
    $label = ltrim($label);
    $value = (string)$choiceValue;

    if (strncmp($label, $value, strlen($value)) !== 0) {
        return false;
    }

    $rest = substr($label, strlen($value));
    $rest = ltrim($rest);

    if ($rest === '') return false;

    $first = mb_substr($rest, 0, 1, 'UTF-8');
    return ($first === '-' || $first === '.');
}

// 2️⃣ Helper: replace standalone numbers using full unicode-aware scanner
function replaceStandaloneNumbers($text, $map)
{
    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $len   = count($chars);

    $out = '';
    $i = 0;

    $isLetter = function($ch) {
        return isUnicodeLetterOrMark($ch);
    };

    while ($i < $len) {

        if (ctype_digit($chars[$i])) {
            $start = $i;

            while ($i < $len && ctype_digit($chars[$i])) {
                $i++;
            }

            $number = implode('', array_slice($chars, $start, $i - $start));

            $prev = ($start > 0) ? $chars[$start - 1] : '';
            $next = ($i < $len) ? $chars[$i] : '';
            $next2 = ($i + 1 < $len) ? $chars[$i + 1] : '';

            $validBefore = !$isLetter($prev);
            $validAfter  = !$isLetter($next);

            // NEW: detect date/time segments
            $isDateMiddle =
                (($next === '-' || $next === ':' || $next === '/') && ctype_digit($next2));

            // NEW: detect last time segment like :10 or :05
            $isTimeEnding =
                ($prev === ':' && !$isLetter($next));

            $looksLikeDateOrTime = ($isDateMiddle || $isTimeEnding);

            if ($looksLikeDateOrTime) {
                // keep the number as-is
                $out .= $number;
            } elseif ($validBefore && $validAfter) {
                if (isset($map[$number])) {
                    $out .= $map[$number];
                } else {
                    $out .= $number;
                }
            } else {
                $out .= $number;
            }
        } else {
            $out .= $chars[$i];
            $i++;
        }
    }

    return $out;
}

// 3️⃣ Main function
function convertNumbersToLabels($comment, $columnName, $formId, $app)
{
    $sql = "SELECT ChoiceListName FROM xformcolumnname WHERE FormId=? AND ColumnName=?";
    $row = $app->getDBConnection()->fetch($sql, $formId, $columnName);

    if (!$row || !$row->ChoiceListName) {
        return $comment;
    }

    $list = $row->ChoiceListName;

    $sql2 = "SELECT ChoiceValue, ChoiceLabel FROM ChoiceInfo WHERE FormId=? AND ChoiceListName=?";
    $rows = $app->getDBConnection()->fetchAll($sql2, $formId, $list);

    $map = [];

    foreach ($rows as $r) {

        $label = ltrim($r->ChoiceLabel);

        $num = '';
        $hasNumberPrefix = false;

        for ($i = 0; $i < strlen($label); $i++) {
            if (ctype_digit($label[$i])) {
                $num .= $label[$i];
            } else {
                break;
            }
        }

        if ($num !== '' && isset($label[strlen($num)]) && ($label[strlen($num)] === '-' || $label[strlen($num)] === '.')) {
            $hasNumberPrefix = true;
        }

        // FINAL RULE:
        // If label already includes a number → keep it
        // If not → keep it without adding numbers
        $map[$r->ChoiceValue] = $r->ChoiceLabel;
    }

    $comment = replaceStandaloneNumbers($comment, $map);

    // Add line breaks between multiple mapped values (allowed small regex)
    $comment = preg_replace('/[\h\v]+(?=\d+-)/u', "<br>", $comment);

    return $comment;
}

$dataViewTable = "
<div class=\"modal-header\">
    <h5 class=\"modal-title\" id=\"editDataModalLabel\">Data Detail View</h5>
    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
</div>
<div class=\"modal-body\">
<form action='' name='CommentsFields' id='CommentsFields' >
<table align=\"left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-striped table-bordered datatables\" id=\"example\">
    <thead>";

if ($EditPermission == 1 || (strpos($LoggedUserName, 'val') !== false) || strpos($LoggedUserName, 'cpsadmin') !== false) {
    $dataViewTable .= "<tr role=\"row\">
        <th width=\"40%\">Column Lebel</th>
        <th width=\"40%\">Column Value</th>
		<th width=\"20%\">Comments</th>
    </tr>
    </thead>
    <tbody>
    <tr align=\"left\" class=\"textRpt\">
        <td><b>Record ID</b></td>
        <td><b>$RecordID</b></td>
		<td>&nbsp;</td>
    </tr>
    <tr align=\"left\" class=\"textRpt\">
        <td><b>PSU</b></td>
        <td><b>$PSU</b></td>
		<td>&nbsp;</td>
    </tr>
    <tr align=\"left\" class=\"textRpt\">
        <td style='color: red'><b>Data Collection Duration</b></td>
        <td style='color: red'><b>$Duration</b></td>
		<td>&nbsp;</td>
    </tr>";
} else {
    $dataViewTable .= "<tr role=\"row\">
        <th width=\"50%\">Column Lebel</th>
        <th width=\"50%\">Column Value</th>
    </tr>
    </thead>
    <tbody>
    <tr align=\"left\" class=\"textRpt\">
        <td><b>Record ID</b></td>
        <td><b>$RecordID</b></td>
    </tr>
    <tr align=\"left\" class=\"textRpt\">
        <td><b>PSU</b></td>
        <td><b>$PSU</b></td>
    </tr>
    <tr align=\"left\" class=\"textRpt\">
        <td style='color: red'><b>Data Collection Duration</b></td>
        <td style='color: red'><b>$Duration</b></td>
    </tr>";
}

$dataViewTable .= "</tbody></table>";

$AllModuleInfoQuery = "SELECT ModuleName, ColumnName, ColumnLabel, Gender, QuestionType  FROM ModuleInfoForLineReport WHERE FormId = $DataFromID ORDER BY id ASC";

$MasterDataQueryRS = $app->getDBConnection()->fetchAll($MasterDataQuery);
$AllModuleInfoRS = $app->getDBConnection()->fetchAll($AllModuleInfoQuery);

usort($MasterDataQueryRS, function($a, $b) {
    // Extract numeric parts for natural sorting
    preg_match_all('/\d+/', $a->ColumnNameOriginal, $matchesA);
    preg_match_all('/\d+/', $b->ColumnNameOriginal, $matchesB);
    
    $numsA = $matchesA[0];
    $numsB = $matchesB[0];
    
    // Compare each numeric part
    $minLen = min(count($numsA), count($numsB));
    for ($i = 0; $i < $minLen; $i++) {
        if ($numsA[$i] != $numsB[$i]) {
            return $numsA[$i] - $numsB[$i];
        }
    }
    return strnatcasecmp($a->ColumnNameOriginal, $b->ColumnNameOriginal);
});

$AllModuleNames = [];
foreach ($AllModuleInfoRS as $row) {
    $AllModuleNames[$row->ModuleName] = true;
}
$AllModuleNames['Others'] = true;  // always include
$ModuleGroupData = [];
foreach ($AllModuleNames as $m => $_) {
    $ModuleGroupData[$m] = [];  // ← ensures module always exists
}

// ==========================================================================================
// Populating $ColumnLabelMap variable using the Stored Procedure result
// ==========================================================================================
$ColumnLabelMap = [];
foreach ($MasterDataQueryRS as $row) {
    $colNameOriginal = $row->ColumnNameOriginal;
    // For line-type columns, get base column (everything before last _n)
    $baseCol = preg_replace('/_\d+$/', '', $colNameOriginal);
    $ColumnLabelMap[$baseCol] = $row->ColumnLabelModified;
}

foreach ($AllModuleInfoRS as $row) {
    $colName = $row->ColumnName;
    $matchingLabels = [];
    foreach ($MasterDataQueryRS as $md) {
        if (str_starts_with($md->ColumnNameOriginal, $colName)) {
            $matchingLabels[] = $md->ColumnLabelModified;
        }
    }
    if ($matchingLabels) {
        $row->ColumnLabelModified = $matchingLabels[0];
    } else {
        $row->ColumnLabelModified = null;
    }
}

$ModuleModifiedColumnValue = [];
$RowGender = array();

/**
 * Build helper maps from ModuleInfo:
 *  - $ColumnToModule : ColumnName   → ModuleName
 *  - $LineColumns    : ColumnName   → true  (only for QuestionType='line')
 *  - $RowGender      : ColumnName   → Gender==2 (for highlighting)
 */

// 1) Build maps
$ColumnToModule = [];
$LineColumns    = [];
$RowGender      = [];

// Build ColumnName → ModuleName, and line columns, gender map
foreach ($AllModuleInfoRS as $row) {
    $ColumnToModule[$row->ColumnName] = $row->ModuleName;
    $baseCol = preg_replace('/_\d+$/', '', $row->ColumnName);
    $ColumnToModule[$baseCol] = $row->ModuleName;
    if ($row->QuestionType == 'line') {
        $LineColumns[$row->ColumnName] = true;
    }
    if ($row->Gender == 2) {
        $RowGender[$row->ColumnName] = $row->Gender;
    }
}

/**
 * Build $ModuleGroupData WITHOUT line-type questions.
 * For line-type columns (C_01_1, C_01_2, ...), we strip the last
 * numeric part to get the “base” column (C_01) and then:
 *  - if that base column is marked as line-type in $LineColumns
 *    → we SKIP it here (already shown in “Line Type Data” table)
 *  - otherwise we assign it to its real module via $ColumnToModule
 */
$ModuleGroupData = [];
$isEditedAry     = array();

// Loop through each returned SP row
foreach ($MasterDataQueryRS as $MDRrow) {
    $colName = $MDRrow->ColumnNameOriginal;

    // Skip line-type repeating rows — handled separately
    foreach ($LineColumns as $lineBase => $_) {
        if ($colName === $lineBase || strpos($colName, $lineBase . '_') === 0) {
            continue 2;
        }
    }

    // Determine module by first letter
    $prefix = substr($colName, 0, 1);

    // if (in_array($prefix, ['A','B','C','D','E'])) {
    //     $ModuleName = $prefix;
    // } else {
    //     $ModuleName = 'Others';
    // }

    // Determine module dynamically using the ColumnToModule mapping
    $ModuleName = 'Others'; // Default value

    // Try to find the module for this column
    if (isset($ColumnToModule[$colName])) {
        $ModuleName = $ColumnToModule[$colName];
    } else {
        // If not found, try to find by base column name (without _n suffix)
        $baseCol = preg_replace('/_\d+$/', '', $colName);
        if (isset($ColumnToModule[$baseCol])) {
            $ModuleName = $ColumnToModule[$baseCol];
        } else {
            // If still not found, try to extract module from column name pattern
            // This handles columns like "A_01", "B_02", etc.
            if (preg_match('/^([A-Z]+)_/', $colName, $matches)) {
                $modulePrefix = $matches[1];
                // Check if this module exists in AllModuleNames
                if (isset($AllModuleNames[$modulePrefix])) {
                    $ModuleName = $modulePrefix;
                }
            }
        }
    }

    $ModuleGroupData[$ModuleName][] = $MDRrow;
    if ($MDRrow->IsEdited > 0) {
        $isEditedAry[$ModuleName] = $MDRrow->IsEdited;
    }
}

//
// Build per-module line-type rows so we can render them inside each module accordion.
// Structure produced:
//   $ModuleLineTypeRows[$moduleName][$lineNo][$baseCol] = value
//
$ModuleLineTypeRows = [];           // module => lineNo => [ baseCol => value ]
$ModuleLineBases     = [];          // module => [ baseCol1, baseCol2, ... ]

// Build list of line-base columns grouped by ModuleName (from ModuleInfo)
foreach ($AllModuleInfoRS as $row) {
    if ($row->QuestionType == 'line') {
        if (!isset($ModuleLineBases[$row->ModuleName])) {
            $ModuleLineBases[$row->ModuleName] = [];
        }
        $ModuleLineBases[$row->ModuleName][] = $row->ColumnName;
    }
}

// Create a map of column names to labels from ModuleInfoForLineReport
$moduleInfoLabelMap = [];
foreach ($AllModuleInfoRS as $row) {
    $moduleInfoLabelMap[$row->ColumnName] = $row->ColumnLabel;
}

// Iterate returned SP rows and assign only line-type rows into per-module structure
foreach ($MasterDataQueryRS as $r) {
    $colName = $r->ColumnNameOriginal;
    
    // Check if this is a line-type column (ends with _number)
    if (!preg_match('/_\d+$/', $colName)) {
        continue;
    }
    
    $baseCol = preg_replace('/_\d+$/', '', $colName);
    
    // Find which module this belongs to
    $foundModule = null;
    foreach ($ModuleLineBases as $moduleName => $bases) {
        if (in_array($baseCol, $bases)) {
            $foundModule = $moduleName;
            break;
        }
    }
    
    if (!$foundModule) {
        continue;
    }
    
    $parts = explode('_', $colName);
    $lineNo = is_numeric(end($parts)) ? end($parts) : 0;
    
    // Get label from ModuleInfoForLineReport if available
    if (isset($moduleInfoLabelMap[$baseCol])) {
        $cleanLabel = $moduleInfoLabelMap[$baseCol];
    } else {
        // Fallback to stored procedure label
        $cleanLabel = preg_replace('/\s*\([A-Za-z0-9_]+\)\s*$/', '', $r->ColumnLabelModified);
    }
    
    if (!isset($ModuleLineTypeRows[$foundModule])) {
        $ModuleLineTypeRows[$foundModule] = [];
    }
    if (!isset($ModuleLineTypeRows[$foundModule][$lineNo])) {
        $ModuleLineTypeRows[$foundModule][$lineNo] = [];
    }
    
    $ModuleLineTypeRows[$foundModule][$lineNo][$baseCol] = [
        'label' => $cleanLabel,
        'value' => $r->ColumnValueModified
    ];
}


$dataViewTable .= "<div class=\"accordion\" id=\"accordionExample\">";

foreach ($AllModuleNames as $ModuleName => $_) {
    $ModuleData = $ModuleGroupData[$ModuleName];
    $hasLineTypeData = !empty($ModuleLineTypeRows[$ModuleName]);
    // Skip modules with no data (both regular and line-type)
    if (empty($ModuleData) && !$hasLineTypeData) {
        continue;
    }
    $dataViewTable .= "
        <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"Accordion-$ModuleName\">
                    <button class=\"accordion-button collapsed px-3 py-0\" 
                                type=\"button\" 
                                data-bs-toggle=\"collapse\" 
                                data-bs-target=\"#collapse-$ModuleName\" 
                                aria-expanded=\"true\" 
                                aria-controls=\"collapse-$ModuleName\" ".((!empty($isEditedAry[$ModuleName]) && $isEditedAry[$ModuleName] == 2) ? 'style=\"background-color: #fff192;\"' : ((!empty($isEditedAry[$ModuleName]) && $isEditedAry[$ModuleName] == 1) ? 'style=\"background-color: #FBC6C2;\"' : '')).">
                        $ModuleName - Module
                    </button>
                </h2>
            <div id=\"collapse-$ModuleName\" class=\"accordion-collapse collapse\" aria-labelledby=\"Accordion-$ModuleName\" data-bs-parent=\"#accordionExample\" style=\"overflow: auto;\">
                <div class=\"accordion-body\">
                <table align=\"left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"table table-striped table-bordered datatables\" id=\"example\">
                    <thead>
                        <tr role=\"row\">
                            <th width=\"40%\">Column Lebel</th>
                            <th width=\"40%\">Column Value</th>
                            <th width=\"20%\">Comments</th>
                        </tr>
                    </thead>
                    <tbody>";

    // existing straight-type rows (unchanged)
    $dataViewTable .= getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName, $RowGender, $app, $DataFromID);

    $dataViewTable .= "</tbody></table>";

    // --- NEW: render line-type table for THIS module (if exists) ---
    if (!empty($ModuleLineTypeRows[$ModuleName])) {

        // Build header columns (deterministic order - use first line's keys)
        $firstLine = reset($ModuleLineTypeRows[$ModuleName]);
        // $cols = array_keys($firstLine);

        $cols = [];
        foreach ($firstLine as $colBase => $arr) {
            $cols[$colBase] = $arr['label'];  // label here
        }
        
        $dataViewTable .= "
            <h5 style='margin-top:12px;'>Line Type Data</h5>
            <div style='width:100%; overflow-x:auto; overflow-y:hidden; white-space:nowrap; border:1px solid #ddd; padding:5px; margin-bottom:15px;'>
                <table class='table table-bordered table-striped table-sm' style='min-width:1200px;'>
                    <thead><tr><th>Line No</th>";

        foreach ($cols as $c) {
            $dataViewTable .= "<th>{$c}</th>";
        }
        $dataViewTable .= "</tr></thead><tbody>";

        // rows sorted by numeric line no ascending
        ksort($ModuleLineTypeRows[$ModuleName], SORT_NUMERIC);
        foreach ($ModuleLineTypeRows[$ModuleName] as $ln => $rowCols) {
            $dataViewTable .= "<tr><td>{$ln}</td>";
            // maintain column order from $cols
            foreach ($cols as $colBase => $label) {
                $val = isset($rowCols[$colBase]['value']) ? $rowCols[$colBase]['value'] : '';
                $dataViewTable .= "<td>" . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . "</td>";
            }
            $dataViewTable .= "</tr>";
        }

        $dataViewTable .= "</tbody></table></div>";
    }

    // close accordion item
    $dataViewTable .= "</div></div></div>";
}


$dataViewTable .= "</div></form>";
$dataViewTable .= "<div class=\"modal-footer\">";
$dataViewTable .= "<button type='button' class='btn btn-primary' id='openViewDataModalBtn'>Open Comment Enabled View</button>";
$dataViewTable .= "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Close</button>";
$dataViewTable .= "</div>";

echo $dataViewTable;

function getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName, $RowGender, $app, $formId) {
    $dataViewTable = "";    
    foreach ($ModuleData as $MDRrow) {
        $originalColumnName = $MDRrow->ColumnNameOriginal;
        $ModifiedColumnLabel = $MDRrow->ColumnLabelModified;
        $originalColumnValue = $MDRrow->ColumnValueOriginal;
        $ModifiedColumnValue = $MDRrow->ColumnValueModified;
        $IsEditable = $MDRrow->IsEditable;
        $IsEdited = $MDRrow->IsEdited;

        if (empty($RowGender[$originalColumnName])) {
            $Gender = 0;
        } else {
            $Gender = $RowGender[$originalColumnName];
        }

        $dataViewTable .= "
        <tr>
            <td style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ($Gender==2 ? 'background-color:#ead1dc;' : '')))."\">$ModifiedColumnLabel";

            // --- TOOLTIP SECTION --- //
            if ($MDRrow->Comments != '') {
                $decoded = convertNumbersToLabels($MDRrow->Comments, $originalColumnName, $formId, $app);
                // Escape for HTML attribute
                $safe = htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
                // Replace newlines with &#10; so tooltip keeps line breaks
                $safe = str_replace(["\r", "\n"], '&#10;', $safe);
                $dataViewTable .= "<span style='float: right;'><img class=\"jBoxTip\" src=\"../../img/Comments.png\" border=0 style=\"cursor: pointer;width: 50px;\" title='$safe'></span>";
            }

            $dataViewTable .= "</td>";

            if (strtoupper($originalColumnName) == 'PICTURE' or strtoupper($originalColumnName) == 'IMAGE' or (strpos(strtoupper($originalColumnName), "IMAGE") !== false) or (strpos(strtoupper($originalColumnName), "PICTURE") !== false)) {
                $ImageFilePathArray = explode("/", $XFormsFilePath);
                $ImageFilePath = $baseURL . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ImageFilePathArray[3] . "/" . $ImageFilePathArray[4] . "/" . $ModifiedColumnValue;
                $ImageFilePath = $baseURL . $ImageFilePathArray[0] . "/" . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ModifiedColumnValue;

                $dataViewTable .= "
                <td ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : ($Gender==2 ? 'style="background-color:#ead1dc;"' : '')))."><img src=\"" . $ImageFilePath . "\" width=200 height=200 ></td>";
            } else if ($originalColumnName == 'audio') {
                $ImageFilePathArray = explode("/", $XFormsFilePath);
                $ImageFilePath = $baseURL . $ImageFilePathArray[0] . "/" . $ImageFilePathArray[1] . "/" . $ImageFilePathArray[2] . "/" . $ModifiedColumnValue;
                $dataViewTable .= "
                <td ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : ($Gender==2 ? 'style="background-color:#ead1dc;"' : '')))."><audio preload=\"none\" controls><source src=\"" . $ImageFilePath . "\" type=\"audio/mpeg\"></audio></td>";
            } else if ($originalColumnName == 'geopoint') {
                $TotalLatLong = explode(' ', $ModifiedColumnValue);
                $lat = $TotalLatLong[0];
                $long = $TotalLatLong[1];
                $dataViewTable .= "
                <td colspan='2' ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : ($Gender==2 ? 'style="background-color:#ead1dc;"' : ''))).">
                    <div id=\"map\" style=\"width: 100%;height:400px;\"></div>
                    <script> 
                    function initMap(lat, lon) { 
                        var uluru = {lat: lat, lng: lon}; 
                        var map = new google.maps.Map(document.getElementById('map'), { 
                        zoom: 11, 
                        center: uluru 
                        }); 
                        var marker = new google.maps.Marker({ 
                        position: uluru, 
                        map: map,
                        title: \"Sender: $AgentFullName ($AgentUserName) | Geopoint: $lat, $long\"
                        }); 
                    } 
                    </script> 
                    <script async defer 
                    src= \"https://maps.googleapis.com/maps/api/js?key=$googleMapApiKey&callback=initMap($lat, $long)\"> 
                    </script> 
                </td>";
            } else if ($originalColumnName == 'geoshape') {
                $geoshape_str_arr = explode(";", substr($ModifiedColumnValue, 0, -1));
                $geoshape_latlan_init = "";
                foreach ($geoshape_str_arr as $single_shape) {
                    $single_shape_arr = explode(" ", $single_shape);
                    $single_lat = $single_shape_arr[0];
                    $single_lng = $single_shape_arr[1];
                    $geoshape_latlan_init .= "{ lat: " . $single_lat . ", lng: " . $single_lng . " },";

                    $lastLat = $single_lat;
                    $lastLng = $single_lng;
                }
                $geoshape_final_coordinates = $geoshape_latlan_init;
                $dataViewTable .= "
                <td style=\"word-wrap: break-word\">$ModifiedColumnValue</td>";
            } else if ($originalColumnName == 'surveyStartDate' || $originalColumnName == 'surveyEndDate') {
                $date_time = str_replace("T", " ", $ModifiedColumnValue);

                $dataViewTable .= "
                <td colspan='2' style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ($Gender==2 ? 'background-color:#ead1dc;' : '')))."\">$date_time</td>";
            } else if ($originalColumnName == 'Is_Eligible') {
                $dataViewTable .= "
                <td  colspan='2' style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ($Gender==2 ? 'background-color:#ead1dc;' : '')))."\">$ModifiedColumnValue</td>";
            } else {
                if ($IsEditable == 0) {
                    $dataViewTable .= "
                        <td colspan='2' style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ($Gender==2 ? 'background-color:#ead1dc;' : '')))."\">$ModifiedColumnValue</td>";
                } else {
                    $dataViewTable .= "
                        <td style=\"word-wrap: break-word;".(($IsEdited == 1) ? 'background-color: #FBC6C2;' : (($IsEdited == 2) ? 'background-color: #fff192;' : ($Gender==2 ? 'background-color:#ead1dc;' : '')))."\">$ModifiedColumnValue</td>";
                }
            }

            if (strpos($ModifiedColumnLabel, "geopoint") === false
                && $originalColumnName != 'surveyStartDate'
                && $originalColumnName != 'surveyEndDate'
                && $originalColumnName != 'Is_Eligible'
                && $IsEditable != 0
                && ($EditPermission == 1
                    || (strpos($LoggedUserName, 'val') !== false)
                    || strpos($LoggedUserName, 'cpsadmin') !== false)) {

                    $dataViewTable .= "<td ".(($IsEdited == 1) ? 'style="background-color: #FBC6C2;"' : (($IsEdited == 2) ? 'style="background-color: #fff192;"' : ($Gender==2 ? 'style="background-color:#ead1dc;"' : '')))."><input type='text' class='form-control' name='CommentsFields[$originalColumnName]'></td>";
            }
        $dataViewTable .= "</tr>";
    }

    return $dataViewTable;
}
?>

<script>
    new jBox('Tooltip', {
        attach: '.jBoxTip',
        theme: 'TooltipDark',
        animation: 'zoomOut',
        adjustDistance: {
            top: 62 + 8,
            right: 5,
            bottom: 5,
            left: 5
        },
        zIndex: 9999
    });
</script>
<script>
    $(document).on('click', '#openViewDataModalBtn', function() {
        // Close the "View Only" modal
        $('#viewDataModalForViewOnly').modal('hide');

        // Pass same parameters to main modal
        var dataFromID = '<?= $DataFromID ?>';
        var recordID = '<?= $RecordID ?>';
        var isApproved = '<?= $IsApproved ?>';
        var psu = '<?= $PSU ?>';
        var loggedUserID = '<?= $LoggedUserID ?>';
        var userID = '<?= $AgentID ?>';
        var XFormsFilePath = '<?= $XFormsFilePath ?>';

        // Load content via AJAX
        ShowDataDetail(dataFromID, recordID, isApproved, psu, loggedUserID, userID, XFormsFilePath);

        // Open the main modal
        $('#viewDataModal').modal('show');
    });
</script>
