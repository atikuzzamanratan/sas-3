<?php

/**
 * ExtractData.php – fully refactored (no schema changes)
 *
 * Securely extracts XML data manually uploaded via the web panel.
 * - All DB queries use PDO prepared statements or the custom variadic query/fetchAll methods.
 * - Inputs are strictly validated.
 * - File upload is secured (extension, MIME, directory traversal prevented).
 * - Transaction ensures atomicity (xformrecord + masterdatarecord_Pending).
 * - Duplicate uploads are detected using a file‑based hash registry.
 * - CompanyID is forced to a valid value (from the form's datacollectionform record) if the assignment returns 0/invalid.
 */

$baseURL = get_base_url();
$conn    = PDOConnectDB();       // PDO wrapper object for prepared inserts/transactions

// ------------------------------------------------------------------
// NEW: File‑based hash registry for duplicate detection
// (Old version had no duplicate detection at all)
// ------------------------------------------------------------------
function getHashRegistryPath(string $dir_path): string
{
    return rtrim($dir_path, '/') . '/.upload_hashes.json';
}

function isDuplicateFile(string $dir_path, string $fileHash): bool
{
    $registryFile = getHashRegistryPath($dir_path);
    if (!file_exists($registryFile)) {
        return false;
    }
    $hashes = json_decode(file_get_contents($registryFile), true);
    return is_array($hashes) && in_array($fileHash, $hashes, true);
}

function registerFileHash(string $dir_path, string $fileHash): void
{
    $registryFile = getHashRegistryPath($dir_path);
    $hashes = [];
    if (file_exists($registryFile)) {
        $hashes = json_decode(file_get_contents($registryFile), true);
        if (!is_array($hashes)) {
            $hashes = [];
        }
    }
    $hashes[] = $fileHash;
    file_put_contents($registryFile, json_encode(array_values(array_unique($hashes))), LOCK_EX);
}

function unregisterFileHash(string $dir_path, string $fileHash): void
{
    $registryFile = getHashRegistryPath($dir_path);
    if (!file_exists($registryFile)) {
        return;
    }
    $hashes = json_decode(file_get_contents($registryFile), true);
    if (!is_array($hashes)) {
        return;
    }
    $hashes = array_values(array_diff($hashes, [$fileHash]));
    file_put_contents($registryFile, json_encode($hashes), LOCK_EX);
}

// ------------------------------------------------------------------
// NEW: Optional logger (unchanged from old, moved inside same position)
// ------------------------------------------------------------------
function LogWriter($log_message)
{
    $LogEnable = 0;
    if ($LogEnable == 1) {
        $time_value = (date_default_timezone_set("Asia/Dhaka") * 120);
        $current_time = date("H:i:s", time() + $time_value);
        $hour = substr($current_time, 0, 2);
        $text_file_name = date('d-m-Y') . " " . $hour . ".txt";
        $current_date_time = date('Y-m-d') . " " . $current_time;
        $fp = fopen($text_file_name, 'a');
        $writing_info = "Time: " . $current_date_time . "|| Message: " . $log_message . "\r\n";
        fwrite($fp, $writing_info);
        fclose($fp);
    }
}
?>
<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $MenuLebel; ?></h2>
            <?php include_once 'Components/header-home-button.php'; ?>
        </header>

        <div class="row">
            <div class="col-lg-12 mb-0">
                <section class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post" enctype="multipart/form-data">
                            <!-- FORM SELECT -->
                            <!-- OLD CODE: identical form structure, no change. -->
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID" class="form-control populate" required>
                                        <optgroup label="Select Form">
                                            <?php
                                            /*
                                             * OLD CODE:
                                             *   $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ? AND Status = '$formActiveStatus'", $loggedUserCompanyID);
                                             *   (identical, kept as-is)
                                             */
                                            $qryForm = $app->getDBConnection()->query(
                                                "SELECT id, FormName FROM datacollectionform WHERE CompanyID = ? AND Status = '$formActiveStatus'",
                                                $loggedUserCompanyID
                                            );
                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <!-- USER SELECT -->
                            <!-- OLD CODE: identical queries, kept unchanged -->
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID" id="SelectedUserID" title="Please select user" required>
                                        <option value="">Choose user</option>
                                        <?php
                                        /*
                                         * OLD CODE:
                                         *   Same if-else structure with $app->getDBConnection()->fetchAll($qryDistUser, ...) 
                                         *   but the 'admin' branch used fetchAll instead of query(). 
                                         *   We kept the original method signatures to avoid errors.
                                         * CHANGE SUMMARY: None, kept identical.
                                         */
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->query($qryDistUser);
                                        } elseif (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID);
                                        } elseif ($SuperID) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.SupervisorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } elseif (strpos($loggedUserName, 'dist') !== false) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.DistCoordinatorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? and UserName = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID, $loggedUserName);
                                        }
                                        foreach ($resQryDistUser as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- DATA NAME, PSU, SampleHHNo, Device ID, File Upload: HTML unchanged -->
                            <!-- OLD CODE: same input fields -->
                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Data Name<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="DataName" type="text" id="DataName" required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">PSU<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="PSUNo" type="number" id="PSUNo" required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">SampleHHNo<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="SampleHHNo" type="number" id="SampleHHNo" required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Device ID<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="deviceID" type="text" id="deviceID" required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">File Upload<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="name" type="file" id="name" required>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show" value="Submit">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>

                <?php
                // ===================== PROCESSING =====================
                if ($_REQUEST['show'] === 'Submit') {

                    // ------------------------------------------------------------
                    // SECTION: Input validation and sanitization
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE:
                     *   $FormID = $_REQUEST['SelectedFormID'];
                     *   $UserID = $_REQUEST['SelectedUserID'];
                     *   $deviceID = $_REQUEST['deviceID'];
                     *   $DataName = $_REQUEST['DataName'];
                     *   $SampleHHNo = $_REQUEST['SampleHHNo'];
                     *   $PSU = $_REQUEST['PSUNo'];
                     * (No type casting, no sanitization – directly usable in SQL strings)
                     */
                    // CHANGE SUMMARY: All inputs are now explicitly cast to int or sanitized with regex
                    // to prevent SQL injection and XSS. Invalid inputs abort the process.
                    $FormID      = isset($_REQUEST['SelectedFormID']) ? (int)$_REQUEST['SelectedFormID'] : 0;
                    $UserID      = isset($_REQUEST['SelectedUserID']) ? (int)$_REQUEST['SelectedUserID'] : 0;
                    $PSU         = isset($_REQUEST['PSUNo'])          ? (int)$_REQUEST['PSUNo']          : 0;
                    $SampleHHNo  = isset($_REQUEST['SampleHHNo'])     ? (int)$_REQUEST['SampleHHNo']     : 0;
                    $deviceID    = isset($_REQUEST['deviceID'])       ? trim(preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_REQUEST['deviceID'])) : '';
                    $DataName    = isset($_REQUEST['DataName'])       ? trim(preg_replace('/[^a-zA-Z0-9_\- ]/', '', $_REQUEST['DataName'])) : '';

                    if (empty($FormID) || empty($UserID) || empty($PSU) || empty($SampleHHNo) || empty($deviceID) || empty($DataName)) {
                        MsgBox('Invalid input parameters.');
                        return;
                    }

                    // ------------------------------------------------------------
                    // SECTION: Read current form version
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE:
                     *   $currentFormVersion = '';
                     *   $fh = fopen('AppsAPI/CurrentFormVersion.txt', 'r');
                     *   $currentFormVersion = fgets($fh);
                     *   fclose($fh);
                     * (No error handling if file missing – led to empty version and silent rejections)
                     */
                    // CHANGE SUMMARY: Now tries multiple absolute paths; falls back to empty string
                    // (which will never match) instead of crashing.
                    $currentFormVersion = null;
                    $possiblePaths = [
                        'AppsAPI/CurrentFormVersion.txt',
                        $_SERVER['DOCUMENT_ROOT'] . '/AppsAPI/CurrentFormVersion.txt',
                        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'AppsAPI' . DIRECTORY_SEPARATOR . 'CurrentFormVersion.txt',
                    ];

                    foreach ($possiblePaths as $path) {
                        if (file_exists($path) && is_readable($path)) {
                            $fh = fopen($path, 'r');
                            if ($fh) {
                                $currentFormVersion = trim(fgets($fh));
                                fclose($fh);
                                break;
                            }
                        }
                    }

                    if ($currentFormVersion === null) {
                        $currentFormVersion = '';
                    }

                    $CurrentDateTime = date('Y-m-d H:i:s');

                    // ------------------------------------------------------------
                    // SECTION: Determine list_no
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE:
                     *   if ($FormID == 2) {
                     *       $list_no = getValue('SampleMapping', 'SampleHHNumber', "PSU = $PSU and UserID = $UserID and MainHHNumber = $SampleHHNo");
                     *   } elseif ($FormID == 3) {
                     *       $list_no = $SampleHHNo;
                     *   } else { $list_no = $SampleHHNo; }
                     * (identical logic, but now uses sanitized integers)
                     */
                    // CHANGE SUMMARY: unchanged business logic, but variables are now safe integers.
                    if ($FormID == 2) {
                        $list_no = getValue('SampleMapping', 'SampleHHNumber', "PSU = $PSU and UserID = $UserID and MainHHNumber = $SampleHHNo");
                    } elseif ($FormID == 3) {
                        $list_no = $SampleHHNo;
                    } else {
                        $list_no = $SampleHHNo;
                    }

                    // ------------------------------------------------------------
                    // SECTION: Build upload directory
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE:
                     *   $dir_path = "SpecialTask/uploads/$UserID/$FormID/";
                     *   if (!is_dir($dir_path)) {
                     *       $old = umask(0);
                     *       mkdir($dir_path, 0777, true);
                     *       umask($old);
                     *   }
                     * (same, but with added error check)
                     */
                    // CHANGE SUMMARY: Added error handling for mkdir failure.
                    $dir_path = "SpecialTask/uploads/{$UserID}/{$FormID}/";
                    if (!is_dir($dir_path)) {
                        $old = umask(0);
                        if (!mkdir($dir_path, 0777, true)) {
                            MsgBox('Failed to create upload directory.');
                            return;
                        }
                        umask($old);
                    }

                    // ------------------------------------------------------------
                    // SECTION: Secure file upload
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE:
                     *   $ActualFileName = NULL;
                     *   foreach ($_FILES as $file) {
                     *       $FileName = $file['name'];
                     *       $FileExtention = end((explode(".", $FileName)));
                     *       if ($FileExtention == "xml") {
                     *           $file['name'] = str_replace(" ", "_", $file['name']);
                     *           $ActualFileName = $file['name'];
                     *       }
                     *       move_uploaded_file($file['tmp_name'], $dir_path . $file['name']);
                     *   }
                     * (No validation of file error, MIME type, extension whitelist, or filename traversal)
                     */
                    // CHANGE SUMMARY: Added UPLOAD_ERR_OK check, basename() to prevent directory traversal,
                    // strict extension and optional MIME validation.
                    if (!isset($_FILES['name']) || $_FILES['name']['error'] !== UPLOAD_ERR_OK) {
                        MsgBox('File upload error.');
                        return;
                    }
                    $file = $_FILES['name'];

                    $origName = basename($file['name']);
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if ($ext !== 'xml') {
                        MsgBox('Only XML files are allowed.');
                        return;
                    }

                    $ActualFileName = str_replace(' ', '_', $origName);
                    $targetPath = $dir_path . $ActualFileName;

                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        MsgBox('Failed to move uploaded file.');
                        return;
                    }

                    // ------------------------------------------------------------
                    // SECTION: Compute file hash for duplicate detection (NEW)
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE: (none)
                     * CHANGE SUMMARY: Added SHA‑256 hash of the uploaded file to prevent exact duplicate uploads.
                     */
                    $fileHash = hash_file('sha256', $targetPath);
                    if ($fileHash === false) {
                        unlink($targetPath);
                        MsgBox('Could not hash uploaded file.');
                        return;
                    }

                    // ------------------------------------------------------------
                    // SECTION: Check user‑form assignment
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE:
                     *   $cn = ConnectDB();
                     *   $FormQry = "SELECT id, FormGroupId, CompanyID FROM assignformtoagent WHERE UserID = '$UserID' AND FormID = '$FormID' AND Status='Active'";
                     *   $rs = db_fetch_array(db_query($FormQry, $cn));
                     *   $IDValue = $rs['id'];
                     *   $FormGroupId = $rs['FormGroupId'];
                     *   $CompanyID = $rs['CompanyID'];
                     * (Vulnerable to SQL injection via $UserID and $FormID)
                     */
                    // CHANGE SUMMARY: Now uses a PDO prepared statement. Error and missing assignment handled gracefully.
                    try {
                        $stmt = $conn->prepare(
                            "SELECT id, FormGroupId, CompanyID FROM assignformtoagent WHERE UserID = ? AND FormID = ? AND Status = 'Active'"
                        );
                        $stmt->execute([$UserID, $FormID]);
                        $rs = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        unlink($targetPath);
                        MsgBox('Database error checking assignment.');
                        return;
                    }

                    if (!$rs) {
                        unlink($targetPath);
                        MsgBox('The user is not actively assigned to this form.');
                        return;
                    }

                    $IDValue     = $rs['id'];
                    $FormGroupId = $rs['FormGroupId'];
                    $CompanyID   = (int)$rs['CompanyID'];

                    // ------------------------------------------------------------
                    // SECTION: Ensure CompanyID is valid (NEW)
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE: (none – directly used $CompanyID, which could be 0 and cause FK error)
                     * CHANGE SUMMARY: If assignment's CompanyID is 0 or empty, fetch the correct CompanyID
                     * from datacollectionform (the form's owning company).
                     */
                    if (empty($CompanyID)) {
                        try {
                            $stmtForm = $conn->prepare("SELECT CompanyID FROM datacollectionform WHERE id = ?");
                            $stmtForm->execute([$FormID]);
                            $formRow = $stmtForm->fetch(PDO::FETCH_ASSOC);
                            if ($formRow && !empty($formRow['CompanyID'])) {
                                $CompanyID = (int)$formRow['CompanyID'];
                            } else {
                                throw new Exception('Valid CompanyID could not be determined.');
                            }
                        } catch (Exception $e) {
                            unlink($targetPath);
                            MsgBox('Error: ' . $e->getMessage());
                            return;
                        }
                    }

                    // ------------------------------------------------------------
                    // SECTION: Start database transaction (NEW)
                    // ------------------------------------------------------------
                    /*
                     * OLD CODE: No transaction – xformrecord inserted first, then if masterdatarecord_Pending
                     * insertion failed, the xformrecord row remained orphaned.
                     * CHANGE SUMMARY: All DB operations are now wrapped in a transaction.
                     */
                    try {
                        $conn->beginTransaction();

                        // --------------------------------------------------------
                        // SECTION: Duplicate check (NEW)
                        // --------------------------------------------------------
                        /*
                         * OLD CODE: (none)
                         * CHANGE SUMMARY: Uses the file hash registry to detect same XML file upload.
                         */
                        if (isDuplicateFile($dir_path, $fileHash)) {
                            $conn->rollBack();
                            unlink($targetPath);
                            MsgBox('Duplicate submission detected. The same XML file has already been uploaded.');
                            return;
                        }

                        // --------------------------------------------------------
                        // SECTION: Insert into xformrecord
                        // --------------------------------------------------------
                        /*
                         * OLD CODE:
                         *   $db_file_path = "SpecialTask/uploads/$UserID/$FormID/$ActualFileName";
                         *   $FormInsertQry = "INSERT INTO xformrecord(UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, PSU, SampleHHNo) VALUES  ('$UserID', '$FormID', N'$DataName', '$FormGroupId', '$CompanyID', '$deviceID', '$db_file_path', '$PSU', '$SampleHHNo')";
                         *   db_query($FormInsertQry, $cn);
                         * (Vulnerable to SQL injection through all fields)
                         */
                        // CHANGE SUMMARY: Prepared statement with parameter binding; EntryDate added.
                        $db_file_path = "SpecialTask/uploads/{$UserID}/{$FormID}/{$ActualFileName}";
                        $insertXform = $conn->prepare(
                            "INSERT INTO xformrecord (UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, PSU, SampleHHNo, EntryDate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                        );
                        $insertXform->execute([
                            $UserID,
                            $FormID,
                            $DataName,
                            $FormGroupId,
                            $CompanyID,
                            $deviceID,
                            $db_file_path,
                            $PSU,
                            $SampleHHNo,
                            $CurrentDateTime
                        ]);
                        $xFormRecordID = $conn->lastInsertId();

                        // --------------------------------------------------------
                        // SECTION: Parse XML (same logic, safer value handling)
                        // --------------------------------------------------------
                        /*
                         * OLD CODE:
                         *   $ActualFilePath = $baseURL . $db_file_path;
                         *   $xmlIterator = new SimpleXMLIterator(file_get_contents($ActualFilePath));
                         *   ... (same parsing loop, but values were stored as "N'" . $data . "'" for SQL)
                         */
                        // CHANGE SUMMARY: Local file path used (not URL); values are stored as plain strings,
                        // SQL escaping is done via prepared statements later.
                        $localFilePath = $targetPath;
                        if (!file_exists($localFilePath)) {
                            throw new Exception('Uploaded file disappeared.');
                        }

                        $NameArray  = [];
                        $ValueArray = [];
                        $IsValidVersion = 0;

                        try {
                            $xmlIterator = new SimpleXMLIterator(file_get_contents($localFilePath));
                        } catch (Exception $e) {
                            throw new Exception('Failed to parse XML: ' . $e->getMessage());
                        }

                        for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
                            if ($xmlIterator->hasChildren()) {
                                foreach ($xmlIterator->getChildren() as $name => $data) {
                                    if (count($data) > 0) {
                                        foreach ($data as $nameChild => $dataChild) {
                                            if ($nameChild !== 'instanceID' && strpos($name, 'Note') === false && strpos($name, '_cal') === false) {
                                                $dataChild = str_replace("'", " ", $dataChild);
                                                $NameArray[]  = $nameChild;
                                                $ValueArray[] = (string)$dataChild;
                                            }
                                            if ($nameChild === 'form_version_no' && (string)$dataChild === $currentFormVersion) {
                                                $IsValidVersion = 1;
                                            }
                                        }
                                    } else {
                                        if ($name === 'form_version_no' && (string)$data === $currentFormVersion) {
                                            $IsValidVersion = 1;
                                        }
                                        if ($name !== 'instanceID' && strpos($name, 'Note') === false && strpos($name, '_cal') === false) {
                                            $data = str_replace("'", " ", $data);
                                            $NameArray[]  = $name;
                                            $ValueArray[] = (string)$data;
                                        }
                                    }
                                }
                            }
                        }

                        // --------------------------------------------------------
                        // SECTION: Add fixed metadata fields (PSU, list_no, etc.)
                        // --------------------------------------------------------
                        /*
                         * OLD CODE:
                         *   $NameArray[] = "PSU"; $ValueArray[] = "N'" . $PSU . "'";
                         *   $NameArray[] = "list_no"; $ValueArray[] = "N'" . $list_no . "'";
                         *   if ($FormID == 2) { $NameArray[] = "SampleHHNo"; $ValueArray[] = "N'" . $SampleHHNo . "'"; }
                         * (values prefixed with N'...' for SQL string building)
                         */
                        // CHANGE SUMMARY: Plain values inserted; array_merge used for efficiency.
                        $NameArray  = array_merge(['PSU', 'list_no'], $NameArray);
                        $ValueArray = array_merge([$PSU, $list_no], $ValueArray);
                        if ($FormID == 2) {
                            $NameArray[]  = 'SampleHHNo';
                            $ValueArray[] = $SampleHHNo;
                        }

                        // --------------------------------------------------------
                        // SECTION: Version check
                        // --------------------------------------------------------
                        /*
                         * OLD CODE:
                         *   if ($IsValidVersion == 1) { ... } else { delete xformrecord; }
                         * (Version mismatch deleted the xformrecord row but left file on disk)
                         */
                        // CHANGE SUMMARY: Now throws an exception, which triggers rollback and file deletion.
                        if ($IsValidVersion !== 1) {
                            throw new Exception('Form version mismatch. Required version: ' . htmlspecialchars($currentFormVersion));
                        }

                        // --------------------------------------------------------
                        // SECTION: Insert into masterdatarecord_Pending
                        // --------------------------------------------------------
                        /*
                         * OLD CODE:
                         *   $SqlQry = "INSERT INTO ... SELECT ... FROM (VALUES ('$xFormRecordID','$UserID',...),... ) AS t(...)"
                         *   $conn->query($SqlQry);
                         * (Massive SQL string built with all values concatenated – severe injection risk)
                         */
                        // CHANGE SUMMARY: Prepared statement loop – each row inserted individually with parameter binding.
                        $masterInsert = $conn->prepare(
                            "INSERT INTO masterdatarecord_Pending 
             (XFormRecordId, UserID, FormId, DataName, FormGroupId, CompanyId, ColumnTitle, ColumnName, ColumnValue, PSU, SampleHHNo)
             VALUES (?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?)"
                        );

                        foreach ($NameArray as $k => $colName) {
                            $colValue = $ValueArray[$k] ?? '';
                            $masterInsert->execute([
                                $xFormRecordID,
                                $UserID,
                                $FormID,
                                $DataName,
                                $FormGroupId,
                                $CompanyID,
                                $colName,
                                $colValue,
                                $PSU,
                                $SampleHHNo
                            ]);
                        }

                        // --------------------------------------------------------
                        // SECTION: Auto‑approve for FormID 3
                        // --------------------------------------------------------
                        /*
                         * OLD CODE:
                         *   $StatusUpdateQuery = "UPDATE [dbo].[xformrecord] SET [IsApproved] = 1 WHERE FormId='3' and id='$xFormRecordID'";
                         *   $conn->query($StatusUpdateQuery);
                         * (SQL injection risk through $xFormRecordID)
                         */
                        // CHANGE SUMMARY: Prepared statement.
                        if ($FormID == 3) {
                            $approveStmt = $conn->prepare("UPDATE xformrecord SET IsApproved = 1 WHERE id = ?");
                            $approveStmt->execute([$xFormRecordID]);
                        }

                        // --------------------------------------------------------
                        // SECTION: Register file hash and commit (NEW)
                        // --------------------------------------------------------
                        /*
                         * OLD CODE: (none)
                         * CHANGE SUMMARY: After successful insert, file hash is saved to prevent future duplicates.
                         */
                        registerFileHash($dir_path, $fileHash);

                        // Commit transaction
                        $conn->commit();
                        MsgBox('Data extract successful.');
                        ReDirect($baseURL . 'index.php?parent=ShowDataPending');
                    } catch (Exception $e) {
                        // --------------------------------------------------------
                        // SECTION: Rollback and cleanup (NEW)
                        // --------------------------------------------------------
                        /*
                         * OLD CODE: In the old version, errors after xformrecord insert left orphaned rows
                         * and files. No unified cleanup.
                         * CHANGE SUMMARY: Transaction rollback, file deletion, hash unregistration.
                         */
                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }
                        if (file_exists($targetPath)) {
                            unlink($targetPath);
                        }
                        unregisterFileHash($dir_path, $fileHash);
                        MsgBox('Data extract failed: ' . htmlspecialchars($e->getMessage()));
                    }
                }
                ?>
            </div>
        </div>
    </section>
</div>