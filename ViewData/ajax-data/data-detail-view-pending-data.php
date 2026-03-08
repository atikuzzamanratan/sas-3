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

//$Permissions = "A: $ApprovePermission | E: $EditPermission | D:$DeletePermission";

if ($IsApproved == 0) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelPending $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Pending WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 1) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelApproved $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_Approved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
} elseif ($IsApproved == 2) {
    $MasterDataQuery = "EXEC ViewDetailDataWithLabelUnApproved $RecordID";

    $MasterDataTimeQuery = "SELECT ColumnName, ColumnValue FROM masterdatarecord_UnApproved WHERE XFormRecordId = ? AND (ColumnName = 'surveyStartDate' OR ColumnName = 'surveyEndDate') ORDER BY ColumnName ASC  ";
    $MasterDataTimeRS = $app->getDBConnection()->fetchAll($MasterDataTimeQuery, $RecordID);
}
//die($MasterDataQuery);
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

/**
 * ------------------------------------------------------------
 *  Number-to-Label Conversion Helpers (Final Behavior Version)
 * ------------------------------------------------------------
 *
 * These functions convert numeric codes inside comment text into
 * human-readable labels taken from the ChoiceInfo table. The
 * system works WITHOUT adding any artificial numbering. Labels
 * are displayed exactly as they appear in the database.
 *
 * NEW BEHAVIOR:
 * -------------
 * ✔ If a ChoiceLabel already begins with a number (e.g., "1- শ্রমিক…")
 *      → That number is kept and shown.
 * ✔ If a ChoiceLabel does NOT contain any numeric prefix
 *      → No number is added from ChoiceValue.
 *
 * This prevents unintended prefixing (e.g., "1- শ্রমিক…") and
 * ensures the system always reflects the exact text stored in
 * ChoiceLabel.
 *
 *
 * FUNCTION PURPOSES:
 * ------------------
 *
 * 1. isUnicodeLetterOrMark($ch)
 *    Detects whether a character is a Unicode letter or combining
 *    mark using raw codepoint ranges. This is needed to simulate
 *    the original regex lookaround logic and avoid matching
 *    numbers that are attached to letters.
 *
 *    Examples:
 *      কমর1  → number ignored
 *      A1    → number ignored
 *      1     → number matched
 *
 *
 * 2. replaceStandaloneNumbers()
 *    Scans the given text character-by-character and replaces only
 *    standalone numeric tokens (1, 2, 101, etc.) with the final
 *    mapped labels. This avoids all heavy regex usage and prevents
 *    environment-specific regex failures.
 *
 *
 * 3. convertNumbersToLabels()
 *    - Loads ChoiceLabel values for the requested ChoiceListName.
 *    - Builds a mapping based ONLY on the actual label text from DB.
 *      (No numbering is added automatically.)
 *    - Replaces standalone numbers in comments via the map.
 *    - Adds line breaks between multiple selected labels using a
 *      small, safe regex (the only regex allowed).
 *
 *
 * WHY THIS APPROACH EXISTS:
 * -------------------------
 * - Some servers failed on Unicode-aware regex, causing UI modals
 *   to freeze or block.
 * - The Intl extension may not be available everywhere, so all
 *   Unicode detection is done manually.
 * - The system must support Bengali, English, and other scripts
 *   without depending on environment-specific features.
 *
 *
 * IMPORTANT FOR FUTURE DEVELOPERS:
 * --------------------------------
 * ✔ Do NOT reintroduce automatic "ChoiceValue-" prefixing —
 *   numbering must come from the database label only.
 *
 * ✔ Do NOT depend on IntlChar unless all production servers are
 *   guaranteed to support it.
 *
 * ✔ The standalone number detection logic is sensitive; ensure any
 *   modification still prevents replacing numbers embedded inside
 *   text.
 *
 * ✔ Only ONE regex is allowed: the final line-break insertion.
 *
 * ------------------------------------------------------------
 */

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
    $sql = "SELECT ChoiceListName FROM xformcolumnname 
            WHERE FormId=? AND ColumnName=?";
    $row = $app->getDBConnection()->fetch($sql, $formId, $columnName);

    if (!$row || !$row->ChoiceListName) {
        return $comment;
    }

    $list = $row->ChoiceListName;

    $sql2 = "SELECT ChoiceValue, ChoiceLabel 
             FROM ChoiceInfo 
             WHERE FormId=? AND ChoiceListName=?";
    $rows = $app->getDBConnection()->fetchAll($sql2, $formId, $list);

    $map = [];

    foreach ($rows as $r) {

        $label = ltrim($r->ChoiceLabel);

        // Detect number prefix
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

$dataViewTable .= "
    </tbody>
</table>";

$AllModuleInfoQuery = "SELECT ModuleName, ColumnName, Gender  FROM ModuleInfo WHERE FormId = $DataFromID ORDER BY id ASC";

$MasterDataQueryRS = $app->getDBConnection()->fetchAll($MasterDataQuery);
$AllModuleInfoRS = $app->getDBConnection()->fetchAll($AllModuleInfoQuery);

$ModuleModifiedColumnValue = [];
$RowGender = array();

foreach ($AllModuleInfoRS as $row) {
    //$ModuleModifiedColumnValue[$row['ColumnName']] = $row['ModuleName'];
    $ModuleModifiedColumnValue[substr($row['ColumnName'], 0, 2)] = $row['ModuleName'] . '_';
    if ($row['Gender'] == 2) {
        $RowGender[$row['ColumnName']] = $row['Gender'];
        //var_dump($RowGender['C_01_2']);exit;
    }
}

$ModuleGroupData = [];
$isEditedAry = array();
foreach ($MasterDataQueryRS as $MDRrow) {
    //$ModuleName = $ModuleModifiedColumnValue[$MDRrow->ColumnNameOriginal] != '' ? $ModuleModifiedColumnValue[$MDRrow->ColumnNameOriginal] : 'Others';
    $ModuleName = substr($MDRrow->ColumnNameOriginal, 1, 1) == '_' ? substr($MDRrow->ColumnNameOriginal, 0, 1) : 'Others';
    $ModuleGroupData[$ModuleName][] = $MDRrow;
    if ($MDRrow->IsEdited > 0) {
        $isEditedAry[$ModuleName] = $MDRrow->IsEdited;
    }
}
//var_dump($ModuleGroupData);exit;
$dataViewTable .= "<div class=\"accordion\" id=\"accordionExample\">";

foreach ($ModuleGroupData as $ModuleName => $ModuleData) {
    $dataViewTable .= "
        <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"Accordion-$ModuleName\">
                    <button class=\"accordion-button collapsed px-3 py-0\" 
								type=\"button\" 
								data-bs-toggle=\"collapse\" 
								data-bs-target=\"#collapse-$ModuleName\" 
								aria-expanded=\"true\" 
								aria-controls=\"collapse-$ModuleName\" ".((!empty($isEditedAry[$ModuleName]) && $isEditedAry[$ModuleName] == 2) ? 'style="background-color: #fff192;"' : ((!empty($isEditedAry[$ModuleName]) && $isEditedAry[$ModuleName] == 1) ? 'style="background-color: #FBC6C2;"' : '')).">
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

    $dataViewTable .= getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName, $RowGender, $app, $DataFromID);

    $dataViewTable .= "</tbody>
                </table>
                </div>
            </div>
        </div>
    ";
}

$dataViewTable .= "</div></form>";

$dataViewTable .= "<div class=\"modal-footer\">";
if ((strpos($LoggedUserName, 'admin') !== false) or $ApprovePermission == 1 or (strpos($LoggedUserName, 'dist') !== false) || (strpos($LoggedUserName, 'val') !== false)) {
    $dataViewTable .= "<button type=\"button\" class=\"btn btn-success\" name=\"update\" id=\"update\" value=\"Update\" 
                        onclick= \"ApproveDataRecord('$RecordID');\"> Approve</button>";
}

if ((strpos($LoggedUserName, 'admin') !== false) or $EditPermission == 1 or (strpos($LoggedUserName, 'dist') !== false) or (strpos($LoggedUserName, 'val') !== false)) {
    $dataViewTable .= "<button type=\"button\" class=\"btn btn-warning\" name=\"update\" id=\"update\" value=\"Update\" 
                        onclick= \"UnapproveDataRecord('$RecordID', '$AgentID', 'CommentsFields');\"> Un-approve</button>";
}

if (((strpos($LoggedUserName, 'admin') !== false) or $DeletePermission == 1) and (strpos($LoggedUserName, 'dist') === false)) {
    $dataViewTable .= "<button type=\"button\" class=\"btn btn-danger\" name=\"delete\" id=\"delete\" value=\"Delete\" 
                        onclick= \"DeleteDataRecord('$RecordID', '$AgentID');\"> Delete</button>";
}

$dataViewTable .= "<button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Close</button>";

$dataViewTable .= "</div>";

echo $dataViewTable;

function getAccordionTableData($ModuleData, $XFormsFilePath, $baseURL, $googleMapApiKey, $LoggedUserName, $EditPermission, $IsEditable, $AgentFullName, $AgentUserName, $RowGender, $app, $formId) {
    $dataViewTable = "";

    // echo "<pre>";
    // echo "ModuleData";
    // print_r($ModuleData);
    // echo "<pre>";
    
    
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

        // if ($MDRrow->Comments != '') {
        //     $dataViewTable .= "<span style='float: right;'><img class=\"jBoxTip\" src=\"../../img/Comments.png\" border=0 style=\"cursor: pointer;width: 50px;\" title='$MDRrow->Comments'></span>";
        // }

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

        $dataViewTable .= "
        </tr>";
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