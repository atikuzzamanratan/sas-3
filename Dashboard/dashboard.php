<?php
/*if ($_GET['FormID'] != '') {
    $FormID = $app->cleanInput($_GET['FormID']);
}*/

$FormID = $formIdMainData;

if (is_null($FormID)) {
    ?>

    <div class="inner-wrapper">
        <section role="main" class="content-body">
            <header class="page-header">
                <h2><?php echo $MenuLebel; ?></h2>

                <?php include_once 'Components/header-home-button.php'; ?>
            </header>

            <!-- start: page -->
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8 mb-3">
                    <section class="card">
                        <div class="card-body">
                            <form class="form-horizontal form-bordered" action="" method="post">
                                <div class="form-group row pb-3">
                                    <label class="col-lg-3 control-label text-lg-end pt-2">Form Select<span
                                                class="required">*</span></label>
                                    <div class="col-lg-6">
                                        <select data-plugin-selectTwo class="form-control populate" name="FormID"
                                                id="FormID" required>
                                            <option value="">Select Form</option>
                                                <?PHP
                                                $userForms = $app->getDBConnection()->query('select distinct id, FormName from datacollectionform WHERE CompanyID = ?', $loggedUserCompanyID);

                                                foreach ($userForms as $row) {
                                                    echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
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
                </div>
                <div class="col-lg-2"></div>
            </div>
            <!-- end: page -->
        </section>
    </div>
    <?php

    if ($_REQUEST['show'] === 'Show') {
        $FormID = $_REQUEST['FormID'];
        ReDirect("index.php?parent=ViewDashboard&FormID=$FormID");
    }
} else {
    $_SESSION["FORMID"] = $FormID;
    include 'show-dashboard.php';
}


