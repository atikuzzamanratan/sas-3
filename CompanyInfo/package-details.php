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
            <div class="col-lg-2 mb-0"></div>
            <div class="col-lg-8 mb-0">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Package Details</h2>
                    </header>
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Company Select<span
                                            class="required">*</span></label>
                                <div class="col-lg-6">
                                    <select data-plugin-selectTwo class="form-control populate" name="CompanyID"
                                            id="CompanyID" required>
                                        <optgroup label="Select Company">
                                            <?PHP
                                            foreach ($rsQryCompanyName as $row) {
                                                echo '<option value="' . $row->id . '">' . $row->CompanyName . '</option>';
                                            }
                                            ?>
                                        </optgroup>
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
                if (isset($_POST['show'])) {
                    $CompanyID = $_POST['CompanyID'];

                    $qryPackageInfo = "SELECT pck.name, pck.description, cpck.packageId, pck.maxUserNo, pck.amount, cpck.modifiedOn, cpck.uploadCredit, 
                    pck.storage, pck.formPerAcc, cpck.validityDate, cpck.createdOn, doc.CompanyName 
                    FROM company_packages cpck
                    JOIN packages pck ON (pck.id = cpck.packageId) 
                    JOIN dataownercompany doc ON (doc.id = ?)
                    WHERE cpck.companyId = ?";

                    /*AND cpck.validityDate >= GETDATE();*/

                    $resQryPackageInfo = $app->getDBConnection()->fetch($qryPackageInfo, $CompanyID, $CompanyID);

                    $usedCredits = getValue('xformrecord', 'COUNT(*)', "CompanyId = '$CompanyID'");
                    $usedUsers = getValue('userinfo', 'COUNT(*)', "CompanyID = '$CompanyID'");
                    $usedForms = getValue('assignformtoformgroup', 'COUNT(*)', "CompanyID = '$CompanyID'");

                    $availableCredits = $resQryPackageInfo->uploadCredit - $usedCredits;
                    $availableUsers = $resQryPackageInfo->maxUserNo - $usedUsers;
                    $availableForms = $resQryPackageInfo->formPerAcc - $usedForms;

                    $PackageID = $resQryPackageInfo->packageId;
                    ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="card">
                                <header class="card-header">
                                    <h2 class="card-title">Current Status</h2>
                                </header>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                                            <thead>
                                            <tr>
                                                <th>Features</th>
                                                <th>Current Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Company Name</td>
                                                <td><?php echo $resQryPackageInfo->CompanyName; ?> </td>
                                            </tr>
                                            <tr>
                                                <td>Package Name</td>
                                                <td><?php echo $resQryPackageInfo->description; ?> </td>
                                            </tr>
                                            <tr>
                                                <td>Available Users</td>
                                                <td><?php echo $availableUsers; ?> </td>
                                            </tr>
                                            <tr>
                                                <td>Available Forms</td>
                                                <td><?php echo $availableForms ?> </td>
                                            </tr>
                                            <tr>
                                                <td>Available Upload Credits</td>
                                                <td><?php echo $availableCredits ?> </td>
                                            </tr>
                                            <tr>
                                                <td>Validity Date</td>
                                                <td><?php echo $resQryPackageInfo->validityDate; ?> </td>
                                            </tr>
                                            <tr>
                                                <td>Created Date</td>
                                                <td><?php echo $resQryPackageInfo->createdOn; ?> </td>
                                            </tr>
                                            <tr>
                                                <td>Modified Date</td>
                                                <td><?php echo $resQryPackageInfo->modifiedOn; ?> </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="col-lg-2 mb-0"></div>
        </div>
        <!-- end: page -->
    </section>
</div>
