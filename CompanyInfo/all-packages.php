<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $MenuLebel; ?></h2>

            <?php include_once 'Components/header-home-button.php'; ?>
        </header>

        <!-- start: page -->
        <div class="row">
            <div class="col-lg-12">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Package Details</h2>
                    </header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                            <thead>
                            <tr>
                                <th>Features</th>
                                <th>Standard Package</th>
                                <th>Advance Package</th>
                                <th>Enterprise Package</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Amount per month</td>
                                <td><?php echo PACKAGE_TYPE_STANDARD_AMOUNT; ?> BDT/Month</td>
                                <td><?php echo PACKAGE_TYPE_ADVANCE_AMOUNT; ?>  BDT/Month</td>
                                <td><?php echo PACKAGE_TYPE_ENTERPRISE_AMOUNT; ?>  BDT/Month</td>
                            </tr>
                            <tr>
                                <td>Max Number of Users</td>
                                <td><?php echo PACKAGE_TYPE_STANDARD_MAX_NO_USERS; ?> </td>
                                <td><?php echo PACKAGE_TYPE_ADVANCE_MAX_NO_USERS; ?></td>
                                <td>Unlimited</td>
                            </tr>
                            <tr>
                                <td>Forms per account</td>
                                <td><?php echo PACKAGE_TYPE_STANDARD_FORM_PER_ACCOUNT; ?> </td>
                                <td><?php echo PACKAGE_TYPE_ADVANCE_FORM_PER_ACCOUNT; ?></td>
                                <td><?php echo PACKAGE_TYPE_ENTERPRISE_FORM_PER_ACCOUNT; ?></td>
                            </tr>
                            <tr>
                                <td>Upload Credits/Month (1 credit = 1 successful form send)</td>
                                <td><?php echo PACKAGE_TYPE_STANDARD_UPLOAD_CREDIT ?> </td>
                                <td><?php echo PACKAGE_TYPE_ADVANCE_UPLOAD_CREDIT; ?></td>
                                <td><?php echo PACKAGE_TYPE_ENTERPRISE_UPLOAD_CREDIT; ?></td>
                            </tr>
                            <tr>
                                <td>Upload credits Cost for additional </td>
                                <td>1,000 credits for 3,000 BDT </td>
                                <td>1,000 credits for 3,000 BDT</td>
                                <td>1,000 credits for 3,000 BDT</td>
                            </tr>
                            <tr>
                                <td>Online storage</td>
                                <td><?php echo PACKAGE_TYPE_STANDARD_STORAGE; ?> </td>
                                <td><?php echo PACKAGE_TYPE_ADVANCE_STORAGE; ?></td>
                                <td><?php echo PACKAGE_TYPE_ENTERPRISE_STORAGE; ?></td>
                            </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>
