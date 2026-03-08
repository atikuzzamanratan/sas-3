<script src="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css" rel="stylesheet">
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
                            <div class="form-group row pb-4">
                                <label class="col-lg-3 control-label text-lg-end pt-2">Search Record</label>
                                <div class="col-lg-6">
                                    <div class="input-group mb-3">
                                        <input type="number" placeholder="input record id" class="form-control"
                                               id="recordID" name="recordID" required>
                                        <input class="btn btn-success" type="submit" id="btnSearchRecord"
                                               name="btnSearchRecord" value="Search">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
                <?php

                if ($_REQUEST['btnSearchRecord'] === 'Search') {
                    $RecordID = $_REQUEST['recordID'];

                    if (empty($RecordID) && empty($RecordID)) {
                        MsgBox('Please select an option.');
                        ReloadPage();
                    } else {
                        $dataURL = $baseURL . "ViewData/ajax-data/view-single-data-ajax-data.php?recordid=$RecordID&LoggedUserID=$loggedUserID";
                        ?>
                        <section class="card">
                            <div class="card-body">
                                <table class="table table-bordered table-striped" id="datatable-ajax"
                                       data-url="<?php echo $dataURL; ?>">
                                    <thead>
                                    <tr>
                                        <th>Actions</th>
                                        <th>Record ID</th>
                                        <th>Status</th>
                                        <th>User</th>
                                        <th>Mobile</th>
                                        <th>Survey</th>
                                        <th>HH No</th>
                                        <th>PSU</th>
                                        <th>Division</th>
                                        <th>District</th>
                                        <th>Entry Date</th>
                                        <th>Device ID</th>
                                    <!--    <th>Is Checked</th>
                                        <th>Validator</th>
                                        <th>Validation Date</th>
                                        <th>Is Edited</th>-->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    function UnapproveDataRecord(id, sendTo, data) {
        let cause = prompt("Are you sure to un-approve this data?", "Cause of un-approve: ");
        var CommentsFields = JSON.stringify($('#CommentsFields').serializeArray());
        if (cause) {
            $.ajax({
                url: "ViewData/unapprove-data.php",
                method: "POST",
                datatype: "json",
                data: {
                    id: id,
                    tbl: 'xformrecord',
                    SendTo: sendTo,
                    cause: cause,
                    CommentsFields: CommentsFields,
                    FromState: 'Pending',
                    sendFrom: '<?php echo $loggedUserID; ?>',
                    companyID: '<?php echo $loggedUserCompanyID; ?>',
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        }
        return false;
    }
</script>

<script type="text/javascript">
    function SendNotification(senderID, toID, message, companyID, data) {
            $.ajax({
                url: "ViewData/send-notification.php",
                method: "POST",
                datatype: "json",
                data: {
                    senderID: senderID,
                    toID: toID,
                    message: message,
                    companyID: companyID
                },
                success: function(response) {
                    alert(response);
                    window.location.reload();
                }
            });
        return false;
    }
</script>

