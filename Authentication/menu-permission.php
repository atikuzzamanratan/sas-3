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
                                <label class="col-lg-3 control-label text-sm-end pt-2">Role Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo id="SelectedRoleID" name="SelectedRoleID"
                                            class="form-control populate" required>
                                        <option value="">Choose a role</option>
                                        <?PHP
                                        if ($loggedUserName == 'admin') {
                                            $qryRole = $app->getDBConnection()->query("SELECT RoleId, RoleName FROM roleinfo WHERE CompanyID <> ? ORDER BY CompanyID DESC", $loggedUserCompanyID);
                                        }
                                        foreach ($qryRole as $row) {
                                            echo '<option value="' . $row->RoleId . '">' . $row->RoleName . '</option>';
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                               value="Show">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['show'] === 'Show') {
                    $SelectedRoleID = $_REQUEST['SelectedRoleID'];

                    $SQL = "SELECT MenuId, MenuLavel FROM menudefine ORDER BY MenuOrder";
                    $rsSQL = $app->getDBConnection()->query($SQL);

                    $data = array();
                    foreach ($rsSQL as $row) {
                        $temp = array('MenuId' => $row['MenuId'], 'MenuLavel' => $row['MenuLavel']);
                        $data[] = $temp;
                    }
                    ?>
                    <section class="card">
                        <div class="card-body">
                            <form action="" method="post" name="menuPer">
                                <div class="table-responsive">
                                    <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                        <thead>
                                        <th>Menu ID</th>
                                        <th>Menu Lebel</th>
                                        <th>
                                            <label>
                                                <input type="checkbox" name="MPAll" value="checkbox" id="MPAll"
                                                       onclick="checkAllMP(document.menuPer.MP)"/>
                                            </label>
                                            Permissions
                                        </th>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($data as $val) {
                                            extract($val);
                                            ?>
                                            <tr>
                                                <td><?PHP echo $val['MenuId']; ?></td>
                                                <td><?PHP echo $val['MenuLavel']; ?></td>
                                                <td>
                                                    <input type="checkbox" name="MP[]" id="MP"
                                                           value="<?PHP echo $MenuId; ?>"
                                                        <?php if (isMenuPermission($MenuId, $SelectedRoleID) == 1)
                                                            echo "checked='checked'"; ?>
                                                    />
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="2">
                                                <input type="hidden" name="roleId"
                                                       value="<?PHP echo $SelectedRoleID; ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" align="center">
                                                <label>
                                                    <!--<input class="btn btn-primary" name="Update" type="submit" class="from"
                                                           id="Update" value="Permission"/>-->
                                                    <input class="btn btn-primary" name="Update" type="submit"
                                                           id="Update"
                                                           value="Save Permissions">
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </section>
                    <?php
                }

                if ($_REQUEST['Update'] === 'Save Permissions') {
                    $perMenu = $_REQUEST["MP"];
                    $roleId = $_REQUEST["roleId"];
                    $cond = "RoleId = '$roleId'";
                    if (Delete('rolemenu', $cond)) {
                        if (Edit_MenuPer($roleId, $perMenu, $loggedUserName)) {
                            MsgBox("Permissions saved successfully.");
                            ReDirect($baseURL . 'index.php?parent=MenuPermission');
                        } else {
                            MsgBox("Failed to save permission!");
                        }
                    } else {
                        MsgBox("Failed to save permission!");
                    }
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    function checkAllMP(field) {
        isall = document.getElementById('MPAll').checked;
        for (i = 0; i < field.length; i++) {
            if (isall === true)
                field[i].checked = true;
            else
                field[i].checked = false;
        }
    }
</script>
