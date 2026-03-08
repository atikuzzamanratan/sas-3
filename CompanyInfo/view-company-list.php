<?php
$qryCompanyName = "SELECT id, CompanyName FROM dataownercompany ORDER BY CompanyName ASC";
$rsQryCompanyName = $app->getDBConnection()->fetchAll($qryCompanyName);

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
                                <label class="col-lg-3 control-label text-lg-end pt-2">Company Select</label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="CompanyID"
                                            id="CompanyID">
                                        <optgroup label="Select Company">
                                            <?PHP
                                            foreach ($rsQryCompanyName as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
                                    </select><br/>
                                    <div class="col-sm-9">
                                        <div class="checkbox-custom checkbox-warning">
                                            <input id="chkAll" value="chkAll" type="checkbox" name="chkAll"/>
                                            <label for="chkAll">All</label>
                                        </div>
                                    </div>
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
                    $CompanyID = $_REQUEST['CompanyID'];
                    $check = $_REQUEST['chkAll'];

                    if ($check == "chkAll") {
                        $dataURL = $baseURL . 'CompanyInfo/ajax-data/company-info-ajax-data.php?par=1';
                    } else {
                        $dataURL = $baseURL . 'CompanyInfo/ajax-data/company-info-ajax-data.php?par=0&id=' . $CompanyID;
                    }

                    ?>
                    <section class="card">
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatable-ajax"
                                   data-url="<?php echo $dataURL; ?>">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Contact Person</th>
                                    <th>Addesss</th>
                                    <th>Phone</th>
                                    <th>Status</th>
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
                }
                ?>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>

<script type="text/javascript">
    function EditItem(id, name, contactPerson, address, phone, status, data) {
        if (confirm("Are you sure to update this data?")) {
            $.ajax({
                url: "CompanyInfo/company-info-edit.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    name: name,
                    contactPerson: contactPerson,
                    address: address,
                    phone: phone,
                    status: status
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
        if (confirm("Are you sure to delete this data?")) {
            $.ajax({
                url: "CompanyInfo/delete.php",
                method: "GET",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'dataownercompany'
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
