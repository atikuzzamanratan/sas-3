<?php
$baseURL = get_base_url();

$conn = PDOConnectDB();
?>
<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $MenuLebel; ?></h2>

            <?php include_once 'Components/header-home-button.php'; ?>
        </header>

        <!-- start: page -->
        <div class="row">
            <div class="col-lg-12 mb-0">
                <section class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post"
                              enctype="multipart/form-data">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedFormID" name="SelectedFormID"
                                            class="form-control populate" required>
                                        <optgroup label="Select Form">
                                            <?PHP
                                            $qryForm = $app->getDBConnection()->query("SELECT id, FormName FROM datacollectionform WHERE CompanyID = ?", $loggedUserCompanyID);

                                            foreach ($qryForm as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                            }
                                            ?>
                                        </optgroup>

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">User Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate"
                                            name="SelectedUserID"
                                            id="SelectedUserID" title="Please select user" required>
                                        <option value="">Choose user</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser);
                                        } else if (strpos($loggedUserName, 'admin') !== false) {
                                            $qryDistUser = "SELECT id, UserName, FullName FROM userinfo WHERE IsActive = 1 AND UserName LIKE '$dataCollectorNamePrefix%' AND CompanyID = ? ORDER BY UserName ASC";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserCompanyID);
                                        } else if ($SuperID) {
                                            $qryDistUser = "SELECT u.id, u.UserName, u.FullName FROM assignsupervisor as a JOIN userinfo as u ON a.UserID = u.id WHERE u.IsActive = 1 AND u.UserName LIKE '$dataCollectorNamePrefix%' AND a.SupervisorID = ?";
                                            $resQryDistUser = $app->getDBConnection()->fetchAll($qryDistUser, $loggedUserID);
                                        } else if (strpos($loggedUserName, 'dist') !== false) {
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

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Data Name<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="DataName" type="text" id="DataName" required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">PSU<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="PSUNo" type="number" id="PSUNo" required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">SampleHHNo<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="SampleHHNo" type="number" id="SampleHHNo"
                                           required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Device ID<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" name="deviceID" type="text" id="deviceID" required>
                                </div>
                            </div>

                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">File Upload<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <!--<div class="fileupload fileupload-new" data-provides="fileupload">
                                        <div class="input-append">
                                            <div class="uneditable-input">
                                                <i class="fas fa-file fileupload-exists"></i>
                                                <span class="fileupload-preview"></span>
                                            </div>
                                            <span class="btn btn-default btn-file">
                                                <span class="fileupload-exists">Change</span>
                                                <span class="fileupload-new">Select file</span>
                                                <input type="file" id="fileName" name="fileName" required/>
                                            </span>
                                            <a href="#" class="btn btn-default fileupload-exists"
                                               data-dismiss="fileupload">Remove</a>
                                        </div>
                                        <span>
                                            <p style="font-style: italic"><strong>Note:</strong> Only <strong
                                                        style="color: red">.xml</strong> format is allowed.</p>
                                        </span>
                                    </div>-->
                                    <input class="form-control" name="name" type="file" id="name" required>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                               value="Submit">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Submit') {

                    $FormID = $_REQUEST['SelectedFormID'];
                    $UserID = $_REQUEST['SelectedUserID'];

                    //$CompanyID = getValue('datacollectionform', 'CompanyID', "id = $FormID");
                    //$FormGroupID = getValue('assignformtoagent', 'FormGroupId', "FormID = $FormID and UserID = $UserID");

                    //Read Current Form Version
                    $currentFormVersion = '';
                    $fh = fopen('AppsAPI/CurrentFormVersion.txt', 'r');
                    $currentFormVersion = fgets($fh);
                    fclose($fh);

                    $date = date('Ymd');
                    $CurrentDateTime = date('Y-m-d H:i:s');

                    $deviceID = $_REQUEST['deviceID'];
                    $DataName = $_REQUEST['DataName'];
                    $SampleHHNo = $_REQUEST['SampleHHNo'];
                    $PSU = $_REQUEST['PSUNo'];

                    //2-Main form
                    if ($FormID == 2) {
                        //list_no
                        $list_no = getValue('SampleMapping', 'SampleHHNumber', "PSU = $PSU and UserID = $UserID and MainHHNumber = $SampleHHNo");
                    }
                    //3-listing form
                    if ($FormID == 3) {
                        //list_no
                        $list_no = $SampleHHNo;
                    }

                    //echo "$FormID|$UserID|$currentFormVersion|$CurrentDateTime|$deviceID|$DataName|$SampleHHNo|$PSU|$list_no";

                    //$target_dir = "uploads/";
                    //$target_file = $target_dir . basename($_FILES["fileName"]["name"]);

                    $NameArray = array();
                    $ValueArray = array();

                    //$dir_path = "D:/wwwroot/cps/SpecialTask/uploads/$UserID/$FormID/";
                    //$dir_path = "D:/wwwroot/newCPSWebPanel/SpecialTask/uploads/$UserID/$FormID/";
                    //$dir_path = $baseURL . "SpecialTask/uploads/$UserID/$FormID/";
                    $dir_path = "SpecialTask/uploads/$UserID/$FormID/";

                    if (!is_dir($dir_path)) {
                        $old = umask(0);
                        mkdir($dir_path, 0777, true);
                        umask($old);
                    }

                    $ActualFileName = NULL;

                    $resp = 201;

                    if ($_SERVER['REQUEST_METHOD'] === "HEAD")
                        $resp = 204;
                    elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
                        foreach ($_FILES as $file) {
                            $FileName = $file['name'];
                            LogWriter($FileName);
                            $FileExtention = end((explode(".", $FileName)));
                            if ($FileExtention == "xml") {
                                $file['name'] = str_replace(" ", "_", $file['name']);
                                $ActualFileName = $file['name'];
                            }
                            move_uploaded_file($file['tmp_name'], $dir_path . $file['name']);
                        }
                    }
                    //$UploadedFile = file_get_contents($_FILES['fileName']['tmp_name']);
                    //$ActualFileName = $UploadedFile;
                    //$xmlIterator = new SimpleXMLIterator(file_get_contents($_FILES['fileName']['tmp_name']));


                    //$resp = 201;

                    //echo $ActualFileName;

                    if ($ActualFileName != NULL) {
                        $cn = ConnectDB();
                        $FormQry = "SELECT id, FormGroupId, CompanyID FROM assignformtoagent WHERE UserID = '$UserID' AND FormID = '$FormID' AND Status='Active'";
                        LogWriter($FormQry);
                        $rs = db_fetch_array(db_query($FormQry, $cn));
                        $IDValue = $rs['id'];
                        $FormGroupId = $rs['FormGroupId'];
                        $CompanyID = $rs['CompanyID'];


                        if (($IDValue > 0) && ($resp == 201)) {
                            try {

                                $NameArray[] = "PSU";
                                $ValueArray[] = "N'" . $PSU . "'";

                                $NameArray[] = "list_no";
                                $ValueArray[] = "N'" . $list_no . "'";

                                if ($FormID == 2) {
                                    $NameArray[] = "SampleHHNo";
                                    $ValueArray[] = "N'" . $SampleHHNo . "'";
                                }


                                $db_file_path = "SpecialTask/uploads/$UserID/$FormID/$ActualFileName";
                                //$db_file_path = "";
                                $FormInsertQry = "INSERT INTO xformrecord(UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, PSU, SampleHHNo) VALUES  ('$UserID', '$FormID', N'$DataName', '$FormGroupId', '$CompanyID', '$deviceID', '$db_file_path', '$PSU', '$SampleHHNo')";

                                db_query($FormInsertQry, $cn);
                                LogWriter($FormInsertQry);

                                $LastIDRS = db_fetch_array(db_query("select @@IDENTITY as LastID", $cn));

                                $xFormRecordID = $LastIDRS['LastID'];

                                $ActualFilePath = $baseURL . $db_file_path;

                                $KeyValueArray = array();

                                $IsValidVersion = 0;

                                $xmlIterator = new SimpleXMLIterator(file_get_contents($ActualFilePath));
                                //$xmlIterator = new SimpleXMLIterator($UploadedFile);
                                for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
                                    if ($xmlIterator->hasChildren()) {
                                        foreach ($xmlIterator->getChildren() as $name => $data) {
                                            if (count($data) > 0) {
                                                foreach ($data as $nameChild => $dataChild) {
                                                    if (($nameChild != 'instanceID') && (strpos($name, 'Note') === false) && (strpos($name, '_cal') === false)) {
                                                        $dataChild = str_replace("'", " ", $dataChild);
                                                        $NameArray[] = $nameChild;
                                                        $ValueArray[] = "N'" . $dataChild . "'";
                                                    }
                                                    if (($nameChild == "form_version_no") && ($dataChild == $currentFormVersion))
                                                        $IsValidVersion = 1;
                                                }
                                            } else {

                                                if (($name == "form_version_no") && ($data == $currentFormVersion))
                                                    $IsValidVersion = 1;

                                                if (($name != 'instanceID') && (strpos($name, 'Note') === false) && (strpos($name, '_cal') === false)) {
                                                    $data = str_replace("'", " ", $data);
                                                    $NameArray[] = $name;
                                                    $ValueArray[] = "N'" . $data . "'";

                                                }

                                            }
                                        }
                                    }
                                }

                                //echo $IsValidVersion;


                                if ($IsValidVersion == 1) {

                                    $SqlQry = "INSERT INTO masterdatarecord_Pending (XFormRecordId, UserID, FormId, DataName, FormGroupId, CompanyId, ColumnTitle, ColumnName, ColumnValue, PSU, SampleHHNo) SELECT t.[XFormRecordId], t.[UserID], t.[FormId], t.[DataName], t.[FormGroupId], t.[CompanyId], t.[ColumnTitle], t.[ColumnName], t.[ColumnValue], t.[PSU],t.[SampleHHNo] FROM (VALUES ";

                                    $k = 0;
                                    $Sql2 = "";
                                    foreach ($NameArray as $NameData) {
                                        if ($k == 0) {
                                            $Sql2 = "('$xFormRecordID','$UserID','$FormID','$DataName','$FormGroupId','$CompanyID','','$NameData',$ValueArray[$k],'$PSU','$SampleHHNo')";
                                        } else {
                                            $Sql2 .= "," . "('$xFormRecordID','$UserID','$FormID','$DataName','$FormGroupId','$CompanyID','','$NameData',$ValueArray[$k],'$PSU','$SampleHHNo')";
                                        }

                                        $k++;
                                    }
                                    $SqlQry .= $Sql2;
                                    $SqlQry .= ") AS t([XFormRecordId], [UserID], [FormId], [DataName], [FormGroupId], [CompanyId], [ColumnTitle], [ColumnName], [ColumnValue], [PSU], [SampleHHNo])";
                                    //$conn->query($SqlQry);

                                    //echo $SqlQry;

                                    if($conn->query($SqlQry)){
                                        MsgBox('Data extract successful.');
                                        ReDirect($baseURL . 'index.php?parent=ShowDataPending');
                                    }else{
                                        MsgBox('Data extract failed!');
                                    }



                                    LogWriter($SqlQry);

                                    if ($FormID == 3) {
                                        $StatusUpdateQuery = "UPDATE [dbo].[xformrecord] SET [IsApproved] = 1 WHERE FormId='3' and id='$xFormRecordID'";
                                        $conn->query($StatusUpdateQuery);
                                    }




                                } else {
                                    $FormDeletetQry = "DELETE FROM  xformrecord where id ='$xFormRecordID'";
                                    db_query($FormDeletetQry, $cn);
                                    LogWriter($FormDeletetQry);
                                    //$resp="Your form version is not correct.";
                                    $resp = 207;
                                }

                            } catch (Exception $e) {
                                //$resp = $e->getMessage();
                                $resp = 209;
                            }
                        } else {
                            //$resp = "You are not permitted to send the data";
                            $resp = 208;
                        }
                        //echo $resp;
                    }

                }
                ?>
            </div>
        </div>
    </section>
</div>

<?php
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
