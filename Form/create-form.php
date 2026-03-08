<?php
$qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
$rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);
?>
    <div class="inner-wrapper">
        <section role="main" class="content-body">
            <header class="page-header">
                <h2><?php echo $MenuLebel; ?></h2>

                <?php include_once 'Components/header-home-button.php'; ?>
            </header>

            <!-- start: page -->
            <div class="row">
                <div class="col-lg-2 mb-0"></div>
                <div class="col-lg-8 mb-0">
                    <section class="card">
                        <header class="card-header">
                            <h2 class="card-title">Data Collection Form</h2>
                        </header>
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post"
                                  enctype="multipart/form-data">
                                <div class="form-group row pb-4">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="formName">Name<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="formName" name="formName"
                                               placeholder="form name" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2"
                                           for="formDescription">Description<span class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="formDescription"
                                               name="formDescription"
                                               placeholder="form description" required>
                                    </div>
                                </div>
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-sm-end pt-2">Company<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo id="company" name="company"
                                                class="form-control populate" title="Please select at least one company"
                                                required>
                                            <option value="">Choose a Company</option>
                                            <?PHP
                                            foreach ($rsQryCompany as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                            }
                                            ?>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row pb-4">
                                    <label class="col-lg-3 control-label text-lg-end pt-2">File Upload</label>
                                    <div class="col-lg-6">
                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                            <div class="input-append">
                                                <div class="uneditable-input">
                                                    <i class="fas fa-file fileupload-exists"></i>
                                                    <span class="fileupload-preview"></span>
                                                </div>
                                                <span class="btn btn-default btn-file">
                                                <span class="fileupload-exists">Change</span>
                                                <span class="fileupload-new">Select file</span>
                                                <input type="file" id="uploadingFile" name="uploadingFile"/>
                                            </span>
                                                <a href="#" class="btn btn-default fileupload-exists"
                                                   data-dismiss="fileupload">Remove</a>
                                            </div>
                                            <span>
                                            <p style="font-style: italic"><strong>Note:</strong> Only <strong
                                                        style="color: red">.xml</strong> format is allowed.</p>
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <footer class="card-footer">
                                    <div class="row justify-content-end">
                                        <div class="col-lg-9">
                                            <input class="btn btn-primary" name="create" type="submit" id="create"
                                                   value="Create">
                                        </div>
                                    </div>
                                </footer>
                            </form>
                        </div>
                    </section>
                </div>
                <div class="col-lg-2 mb-0"></div>
            </div>
            <!-- end: page -->
        </section>
    </div>

<?php
if (isset($_POST['create']) && isset($_POST['formName']) && isset($_POST['formDescription']) && isset($_POST['company'])) {
    $FormName = $_POST['formName'];
    $FormDescription = $_POST['formDescription'];
    $FormCompany = $_POST['company'];

    if (isset($_FILES["uploadingFile"]) && $_FILES["uploadingFile"]["error"] == 0) {
        $file_tmp = $_FILES["uploadingFile"]['tmp_name'];
        $file_name_base = $_FILES["uploadingFile"]['name'];
        $ext = pathinfo($file_name_base, PATHINFO_EXTENSION);

        $file_name = str_replace(' ', '_', $file_name_base);

        if ($ext === $xFromPermittedExt) {
            $my_file = $formDir . $file_name;
            if (file_exists($my_file)) {
                $errMsg = "Sorry same name file already exist!";
                MsgBox($errMsg);
            } else {
                move_uploaded_file($file_tmp, $formDir . $file_name);
            }
        } else {
            $errMsg = "There was a problem, file type $ext not supported!";
            MsgBox($errMsg);
        }
    } else {
        $dbFilePathName = str_replace(' ', '_', $FormName);

        $my_file = $formDir . $dbFilePathName . $xFromPermittedExt;
        $handle = fopen($my_file, 'w') or die('Cannot open file:  ' . $my_file);

        fwrite($handle, "Demo XML file");

        $path_to_file = $my_file;
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace("inputt", "input", $file_contents);
        file_put_contents($path_to_file, $file_contents);
        $file_contentss = file_get_contents($path_to_file);
        $file_contentss = str_replace("TestForm", $FormName, $file_contentss);
        file_put_contents($path_to_file, $file_contentss);
        fclose($file);
    }

    $FormFilePath = $my_file;

    $Field = "CompanyID, FormName, FormDescription, FormFilePath, ProvisionEndDate, CreatedBy";
    $Value = "'$FormCompany', '$FormName', '$FormDescription', '$FormFilePath', '$xFormDefaultProvisionDate', '$loggedUserName'";

    if ($FormCompany != "" && $FormName != "" && $FormDescription != "" && $FormFilePath != "" && $loggedUserName != "") {

        $totalExistingForm = isExist('datacollectionform', "FormName = '$FormName' AND CompanyID = $FormCompany");

        if ($totalExistingForm <> 0) {
            $errMsg = "Sorry same name form already exist!";
            MsgBox($errMsg);
        } else {
            if (Save('datacollectionform', $Field, $Value)) {
                MsgBox('Saved Successfully.');
                ReDirect($baseURL . 'index.php?parent=DataCollectionFormView');
            }
        }

    } else {
        MsgBox('Some information is missing!');
    }
}
