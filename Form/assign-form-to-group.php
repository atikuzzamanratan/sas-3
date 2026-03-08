<?php
if ($loggedUserName == 'admin') {
    $qryFormName = "SELECT id, FormName FROM datacollectionform ORDER BY id DESC";
    $rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName);

    $qryFormGroup = "SELECT id, FormGroupName FROM datacollectionformgroup";
    $rsQryFormGroup = $app->getDBConnection()->fetchAll($qryFormGroup);
} else {
    $qryFormName = "SELECT id, FormName FROM datacollectionform WHERE Status = ? AND CompanyID = ? ORDER BY id DESC";
    $rsQryFormName = $app->getDBConnection()->fetchAll($qryFormName, 'Active', $loggedUserCompanyID);

    $qryFormGroup = "SELECT id, FormGroupName FROM datacollectionformgroup WHERE Status = ? AND CompanyID = ?";
    $rsQryFormGroup = $app->getDBConnection()->fetchAll($qryFormGroup, 'Active', $loggedUserCompanyID);
}

$qryCompnay = "SELECT id, CompanyName FROM dataownercompany";
$rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);

$dataURL = $baseURL . 'Form/ajax-data/form-assigned-to-group-ajax-data.php';
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
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Form Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                            id="FormID" title="Please select a form" required>
                                        <option value="">Choose a Form</option>
                                        <?PHP
                                        foreach ($rsQryFormName as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Group Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="GroupID"
                                            id="GroupID" title="Please select a group" required>
                                        <option value="">Choose a Group</option>
                                        <?PHP
                                        foreach ($rsQryFormGroup as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->FormGroupName . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Company<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="company" name="company"
                                            class="form-control populate" title="Please select a company" required>
                                        <option value="">Choose a Company</option>
                                        <?PHP
                                        foreach ($rsQryCompany as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                               value="Assign">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Assign') {
                    $FormID = $_REQUEST['FormID'];
                    $GroupID = $_REQUEST['GroupID'];
                    $CompanyID = $_REQUEST['company'];

                    $currentFromNo = getValue("assignformtoformgroup", "COUNT(id)", "CompanyID = $CompanyID");

                    $currentUserInfo = "SELECT formPerAcc FROM company_packages JOIN packages ON (packages.id = company_packages.packageId)
                                        WHERE companyId = ? AND company_packages.validityDate >= getdate()";
                    $resUserMaxAssain = $app->getDBConnection()->fetch($currentUserInfo, $CompanyID);

                    $maxFormNo = $resUserMaxAssain->formPerAcc;

                    if ($maxFormNo === NULL) {
                        MsgBox("Please purchase a package first.");
                        exit;
                    }

                    if ($maxFormNo <= $currentFromNo) {
                        MsgBox("Your company's max form limit is over!");
                        exit;
                    }

                    $Field = "FormId, FormGroupId, CompanyID, CreatedBy";
                    $Value = "'$FormID', '$GroupID', '$CompanyID', '$loggedUserName'";

                    if (Save('assignformtoformgroup', $Field, $Value)) {
                        MsgBox('Saved Successfully.');
                        ReDirect($baseURL . 'index.php?parent=AssignFormGroup');
                    } else
                        MsgBox('Failed to save!');
                }
                ?>
                <section class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="datatable-ajax"
                               data-url="<?php echo $dataURL; ?>">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Form Name</th>
                                <th>Group Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </section>
                <!-- end: page -->
                <?php

                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    function DeleteItem(id, data) {
        if (confirm("Are you sure to delete this item?")) {
            $.ajax({
                url: "Form/delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'assignformtoformgroup'
                },
                success: function (response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>
