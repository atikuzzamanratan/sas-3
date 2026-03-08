<?php
$qryCompnay = "SELECT id, CompanyName FROM dataownercompany ORDER BY id DESC";
$rsQryCompany = $app->getDBConnection()->fetchAll($qryCompnay);

$dataURL = $baseURL . 'Authentication/ajax-data/role-list-ajax-data.php?cid=' . $loggedUserCompanyID;
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
                    <header class="card-header">
                        <h2 class="card-title">Role Form</h2>
                    </header>
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2"
                                       for="roleId">Role ID<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="roleId" name="roleId"
                                           placeholder="role id" required>
                                </div>
                            </div>
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2"
                                       for="roleName">Role Name<span class="required">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="roleName" name="roleName"
                                           placeholder="role name" required>
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
                                               value="Create">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Create') {
                    $roleIdRaw = strtolower($_REQUEST['roleId']);
                    $roleName = $_REQUEST['roleName'];
                    $CompanyID = $_REQUEST['company'];
                    $roleId = preg_replace('/\s+/', '', $roleIdRaw);

                    $Field = "RoleId, RoleName, CompanyID, CreatedBy, CreatedDate";
                    $Value = "'$roleId', '$roleName', '$CompanyID', '$loggedUserName', GETDATE()";

                    $cond = "RoleId = '$roleId' AND CompanyID = $CompanyID";
                    $totalExist = isExist('roleinfo', $cond);

                    if ($totalExist <> 0) {
                        $msg = "Sorry username already exist!";
                    } else {
                        if (Save('roleinfo', $Field, $Value)) {
                            $msg = 'Created successfully.';
                        } else
                            $msg = 'Failed to create!';
                    }
                    MsgBox($msg);
                    ReDirect($baseURL . 'index.php?parent=AddRole');
                }
                ?>
                <section class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="datatable-ajax"
                               data-url="<?php echo $dataURL; ?>">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Role ID</th>
                                <th>Role Name</th>
                                <th>Company Name</th>
                                <th>Actions</th>
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
    function EditItem(id, name, data) {
        if (confirm("Are you sure to update this data?")) {
            $.ajax({
                url: "Authentication/role-info-edit.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    name: name
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

<script type="text/javascript">
    function DeleteItem(id, data) {
        if (confirm("Are you sure to delete this item?")) {
            $.ajax({
                url: "Authentication/role-delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'roleinfo'
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
