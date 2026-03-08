<?php
$qrySupervisor = "SELECT id, EditPermission, DeletePermission, ApprovePermission FROM assignsupervisor WHERE SupervisorID = ?";
$resQrySupervisor = $app->getDBConnection()->fetch($qrySupervisor, $loggedUserID);
$SuperID = $resQrySupervisor->id;

if ($loggedUserName == 'admin') {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);
} else {
    $qryCompnay = "SELECT id, CompanyName FROM dataownercompany WHERE id = ?";
    $rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay, $loggedUserCompanyID);
}

if (strpos($loggedUserName, 'dist') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = $loggedUserCompanyID AND a.DistCoordinatorID = $loggedUserID";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery);
} elseif (strpos($loggedUserName, 'div') !== false) {
    $divQuery = "SELECT DISTINCT p.DivisionName, p.DivisionCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = $loggedUserCompanyID AND a.DivCoordinatorID = $loggedUserID";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery);

    $distQuery = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList AS p 
    JOIN assignsupervisor AS a ON p.PSUUserID = a.UserID 
    WHERE  p.CompanyID = $loggedUserCompanyID AND a.DivCoordinatorID = $loggedUserID";
    $rsDistQuery = $app->getDBConnection()->fetchAll($distQuery);
} else {
    $divQuery = "SELECT DISTINCT DivisionName , DivisionCode FROM PSUList WHERE CompanyID = ? ORDER BY DivisionName ASC";
    $rsDivQuery = $app->getDBConnection()->fetchAll($divQuery, $loggedUserCompanyID);
}

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
                        <form class="form-horizontal form-bordered" action="" method="post" enctype="multipart/form-data">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Project<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="company" name="Project"
                                            class="form-control populate" title="Please select a Project" required>
                                        <optgroup label="Choose a Project">
                                            <?PHP
                                            foreach ($rsQryCompany as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <?php if (strpos($loggedUserName, 'admin') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">Division Select
                                        <span class="required">*</span>
                                    </label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="DivisionCode"
                                                id="DivisionCode"
                                                required
                                                onchange="ShowDropDown('DivisionCode', 'DistrictDiv', 'ShowDistrict', '')">
                                            <option value="">Choose division</option>
                                            <?PHP
                                            foreach ($rsDivQuery as $row) {
                                                echo '<option value="' . $row->DivisionCode . '" ' . (isset($DivisionCode) && $DivisionCode == $row->DivisionCode ? 'selected' : '') . '>' . $row->DivisionName . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="geoDiv" style="display: none">
                                    <div class="form-group row pb-3" id="DistrictDiv"></div>
                                </div>
                            <?php } ?>
                            <?php if (strpos($loggedUserName, 'div') !== false) { ?>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">District Select
                                        <span class="required">*</span>
                                    </label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="DistrictCode"
                                                id="DistrictCode"
                                                required>
                                            <option value="">Choose district</option>
                                            <?PHP
                                            foreach ($rsDistQuery as $row) {
                                                echo '<option value="' . $row->DistrictCode . '" ' . (isset($DistrictCode) && $DistrictCode == $row->DistrictCode ? 'selected' : '') . '>' . $row->DistrictName . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Upload xlsx File<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" type="file" name="myFile" accept=".xlsx" required />
                                </div>
                            </div>
                            
                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="upload" type="submit" id="upload"
                                               value="Upload">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['upload'] === 'Upload') {
                    $companyID = xss_clean($_REQUEST['Project']);
                    $selectedDivision = xss_clean($_POST['DivisionCode']);
                    $selectedDistrict = xss_clean($_POST['DistrictCode']);

                    if ($selectedDivision === "") {
                        if (strpos($loggedUserName, 'div') !== false) {
                            $sql = "SELECT DISTINCT p.DivisionCode 
                                    FROM PSUList p 
                                        JOIN assignsupervisor asp ON asp.UserID=p.PSUUserID
                                    WHERE asp.DivCoordinatorID=$loggedUserID";
                            $rsDivQuery = $app->getDBConnection()->fetchAll($sql);
                            $selectedDivision = $rsDivQuery[0]->DivisionCode;
                        }
                        if (strpos($loggedUserName, 'dist') !== false) {
                            $sql = "SELECT DISTINCT p.DivisionCode 
                                    FROM PSUList p 
                                        JOIN assignsupervisor asp ON asp.UserID=p.PSUUserID
                                    WHERE asp.DistCoordinatorID=$loggedUserID";
                            $rsDivQuery = $app->getDBConnection()->fetchAll($sql);
                            $selectedDivision = $rsDivQuery[0]->DivisionCode;
                        }
                    }

                    if ($selectedDistrict === "") {
                        if (strpos($loggedUserName, 'dist') !== false) {
                            $sql = "SELECT DISTINCT p.DistrictCode 
                                    FROM PSUList p 
                                        JOIN assignsupervisor asp ON asp.UserID=p.PSUUserID
                                    WHERE asp.DistCoordinatorID=$loggedUserID";
                            $rsDivQuery = $app->getDBConnection()->fetchAll($sql);
                            $selectedDistrict = $rsDivQuery[0]->DistrictCode;
                        }
                    }

                    //Uploaded Files Section
                    $filepath = $_FILES['myFile']['tmp_name'];
                    $fileSize = filesize($filepath);
                    $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
                    $filetype = finfo_file($fileinfo, $filepath);
                ?>
                <section class="card">
                    <div class="card-header">
                        <div class="card-subtitle"></div>
                        <div class="card-subtitle"></div>
                        <div class="card-subtitle"></div>
                        <div class="card-title">Upload Result</div>
                        <div class="card-subtitle"></div>
                    </div>
                    <div class="card-body">
                    <?php
                        //echo "File Type: ". $filetype."<br />";

                        if ($fileSize === 0) {
                            die("The file is empty.");
                        }

                        if ($fileSize > 3145728) { // 3 MB (1 byte * 1024 * 1024 * 3 (for 3 MB))
                            die("The file is too large");
                        }

                        $allowedTypes = [
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
                        ];

                        if(!in_array($filetype, array_keys($allowedTypes))) {
                            die("File not allowed.");
                         }

                        $filename = md5(date('m/d/Y h:i:s a', time()).$loggedUserID); //basename($filepath);
                        $extension = $allowedTypes[$filetype];
                        
                        $targetDirectory = __DIR__ . "/../Uploads"; // __DIR__ is the directory of the current PHP file

                        $newFilepath = $targetDirectory . "/" . $filename . "." . $extension;
                        //echo "Original File: ".$filepath."<br />";
                        //echo "New File: ". $newFilepath . "<br />";

                        if (!copy($filepath, $newFilepath )) { // Copy the file, returns false if failed
                            die("Can't move file.");
                        }
                        @unlink($filepath); // Delete the temp file
                         
                        //echo "File uploaded successfully :)";

                        // Role
                        $sql = "SELECT RoleId FROM roleinfo WHERE RoleName = N'data collector' AND CompanyID = $companyID";
                        $result = $app->getDBConnection()->fetchAll($sql);
                        if (COUNT($result)==0) {
                            //There is no such Role. So Create it
                            $sql = "INSERT INTO roleinfo (RoleId, RoleName, CompanyID) VALUES(N'datacollector', N'data collector', $companyID)";
                            $app->getDBConnection()->query($sql);

                            $sql = "SELECT RoleId FROM roleinfo WHERE RoleName = N'data collector' AND CompanyID = $companyID";
                            $result = $app->getDBConnection()->fetchAll($sql);
                            $userRoleId = $result[0]->RoleId;
                        } else {
                            $userRoleId = $result[0]->RoleId;
                        }

                        $sql = "SELECT RoleId FROM roleinfo WHERE RoleName = N'supervisor' AND CompanyID = $companyID";
                        $result = $app->getDBConnection()->fetchAll($sql);
                        if (COUNT($result)==0) {
                            //There is no such Role. So Create it
                            $sql = "INSERT INTO roleinfo (RoleId, RoleName, CompanyID) VALUES(N'supervisor', N'supervisor', $companyID)";
                            $app->getDBConnection()->query($sql);

                            $sql = "SELECT RoleId FROM roleinfo WHERE RoleName = N'supervisor' AND CompanyID = $companyID";
                            $result = $app->getDBConnection()->fetchAll($sql);
                            $superRoleId = $result[0]->RoleId;
                        } else {
                            $superRoleId = $result[0]->RoleId;
                        }

                        date_default_timezone_set('Asia/Dhaka');
                        require_once dirname(__FILE__) . '/../Classes/PHPExcel/IOFactory.php';
                        //echo dirname(__FILE__) . '/../Classes/PHPExcel/IOFactory.php';
                        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                        //echo "Reader Created<br>";
                        $objPHPExcel = $objReader->load($newFilepath);
                        //echo "Uploaded file Opened<br>";
                        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                            //var_dump($worksheet);
                            $supAry = array();
                            foreach ($worksheet->getRowIterator(2) as $row) {
                                $dataAry = array();

                                $cellIterator = $row->getCellIterator('B');
                                $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                                //var_dump($cellIterator);exit;
                                foreach ($cellIterator as $cell) {
                                    if (is_null($cell->getCalculatedValue())) {
                                        if ($cell->getColumn() == "B") {
                                            break 2;
                                        }
                                    }

                                    $dataValue = $cell->getCalculatedValue();
                                    if (in_array($cell->getColumn(), array("C", "G"))) {
                                        $dataValue = str_pad($dataValue, 11, "0", STR_PAD_LEFT);
                                    }
                                    $dataAry[$cell->getColumn()] = $dataValue;

                                    if (in_array($cell->getColumn(), array("F", "G"))) {
                                        if (!is_null($cell->getCalculatedValue())) {
                                            $supAry[$cell->getColumn()] = $dataValue;
                                        }
                                    }

                                    if (in_array($cell->getColumn(), array("H"))) {
                                        $supAry[$cell->getColumn()] = $dataValue;
                                    }
                                }
                                //echo "<pre>";
                                //var_dump($dataAry);
                                //echo "</pre>";

                                //Check if there is any such Supervisor
                                $sql = "SELECT id AS numSup FROM userinfo WHERE MobileNumber = N'$supAry[G]'";
                                //echo $sql;exit;
                                $result = $app->getDBConnection()->fetchAll($sql);
                                if (COUNT($result)==0) {
                                    //Create this Supervisor
                                    $sql = "SELECT (COALESCE(COUNT(id),0)+1) AS numSup FROM userinfo WHERE UserName LIKE 'sup%'";
                                    $result = $app->getDBConnection()->fetchAll($sql);
                                    $num = str_pad($result[0]->numSup, 4, "0", STR_PAD_LEFT);
                                    $userName = "sup".$num;
                                    $password = str_pad($userName, 10, "0", STR_PAD_RIGHT);

                                    $sql = "INSERT INTO userinfo (UserName, Password, enc_passw, CompanyID, MobileNumber, EmailAddress, FullName) ";
                                    $sql .= "VALUES(";
                                    $sql .= "'$userName',";
                                    $sql .= "'$password',";
                                    $sql .= "'".password_hash($password, PASSWORD_DEFAULT)."',";
                                    $sql .= $companyID.",";
                                    $sql .= "'".$supAry['G']."',";
                                    $sql .= "'".$supAry['H']."',";
                                    $sql .= "'".$supAry['F']."'";
                                    $sql .= ")";
                                    //echo $sql;exit;
                                    $app->getDBConnection()->query($sql);

                                    $sql = "SELECT id AS numSup FROM userinfo WHERE MobileNumber = N'$supAry[G]'";
                                    $result = $app->getDBConnection()->fetchAll($sql);
                                    $supAry['id'] = $result[0]->numSup;

                                    $sql = "INSERT INTO userrole (UserId, RoleId) VALUES($supAry[id], N'$superRoleId')";
                                    $app->getDBConnection()->query($sql);

                                    echo "Supervisor ID: ".$userName."(".$supAry['id'].") created.<br>";
                                } else {
                                    $supAry['id'] = $result[0]->numSup;
                                }
                                //var_dump($supAry);

                                $sql = "SELECT id AS numSup FROM userinfo WHERE MobileNumber = N'$dataAry[C]'";
                                //echo $sql;exit;
                                $result = $app->getDBConnection()->fetchAll($sql);
                                if (COUNT($result)==0) {
                                    //Create Data Collector
                                    $sql = "SELECT (COALESCE(COUNT(id),0)+1) AS numSup FROM userinfo WHERE UserName LIKE 'dc%'";
                                    $result = $app->getDBConnection()->fetchAll($sql);
                                    $num = str_pad($result[0]->numSup, 4, "0", STR_PAD_LEFT);
                                    $userName = "dc".$num;
                                    $password = str_pad($userName, 10, "0", STR_PAD_RIGHT);

                                    $sql = "INSERT INTO userinfo (UserName, Password, enc_passw, CompanyID, MobileNumber, EmailAddress, FullName) ";
                                    $sql .= "VALUES(";
                                    $sql .= "'$userName',";
                                    $sql .= "'$password',";
                                    $sql .= "'".password_hash($password, PASSWORD_DEFAULT)."',";
                                    $sql .= $companyID.",";
                                    $sql .= "'".$dataAry['C']."',";
                                    $sql .= "'".$dataAry['D']."',";
                                    $sql .= "'".$dataAry['B']."'";
                                    $sql .= ")";
                                    //echo $sql;exit;
                                    $app->getDBConnection()->query($sql);

                                    $sql = "SELECT id AS numSup FROM userinfo WHERE MobileNumber = N'$dataAry[C]'";
                                    //echo $sql;exit;
                                    $result = $app->getDBConnection()->fetchAll($sql);
                                    $userID = $result[0]->numSup;
                                    //echo $userID;exit;

                                    $sql = "INSERT INTO userrole (UserId, RoleId) VALUES($userID, N'$userRoleId')";
                                    $app->getDBConnection()->query($sql);

                                    //Assign Supervisor for this User
                                    $sql = "INSERT INTO assignsupervisor (CompanyID, SupervisorID, UserID, EditPermission, DeletePermission, ApprovePermission)";
                                    $sql .= "VALUES(";
                                    $sql .= $companyID.",";
                                    $sql .= $supAry['id'].",";
                                    $sql .= $userID.",";
                                    $sql .= "1,";
                                    $sql .= "1,";
                                    $sql .= "1";
                                    $sql .= ")";
                                    //echo $sql;exit;
                                    $app->getDBConnection()->query($sql);

                                    echo "User ID: ".$userName."(".$userID.") created and assigned to Supervisor.<br>";

                                    //PSU Table Update
                                    $sql = "UPDATE PSUList SET PSUUserID = $userID WHERE PSU IN ($dataAry[E])";
                                    $app->getDBConnection()->query($sql);

                                    echo "User ID: ".$userName."(".$userID.") added in PSU ($dataAry[E]).<br>";
                                }
                            }
                        }

                        echo "Users Created Successfully";
                    ?>
                    </div>
                </section>
                <?php
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

