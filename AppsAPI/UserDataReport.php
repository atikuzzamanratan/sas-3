<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$UserID = $_GET['UserID'];
$AuthToken = $_GET['authToken'];

if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
    exit();
}

$qryForm = "SELECT dcf.id, dcf.FormName FROM assignformtoagent aftu
JOIN datacollectionform dcf on dcf.id = aftu.FormID 
WHERE aftu.UserID = ?";
$qryFormResult = $app->getDBConnection()->fetchAll($qryForm, $UserID);

include_once '../Components/header-includes.php';
?>

    <div class="row">
        <div class="col-lg-12 mb-3">
            <section class="card">
                <div class="card-body">
                    <form class="form-horizontal form-bordered" action="" method="post">
                        <div class="form-group row pb-3">
                            <label class="col-lg-3 control-label text-lg-end pt-2">Form Select<span
                                        class="required">*</span></label>
                            <div class="col-lg-6">
                                <select data-plugin-selectTwo class="form-control populate" name="FormName"
                                        id="FormName">
                                    <option value="">Select Form</option>
                                        <?PHP
                                        foreach ($qryFormResult as $row) {
                                            echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                        }
                                        ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row pb-3">
                            <label class="col-lg-3 control-label text-lg-end pt-2">Status Select<span
                                        class="required">*</span></label>
                            <div class="col-lg-6">
                                <select data-plugin-selectTwo class="form-control populate" name="selectedStatus"
                                        id="selectedStatus">
                                    <option value="">Select Status</option>
                                        <option value="0">Pending</option>
                                        <option value="1">Approved</option>
                                        <option value="2">Un-Approved</option>
                                        <option value="3">Deleted</option>
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
    </div>

<?php
if ($_REQUEST['show'] === 'Show') {
    $FormID = $_REQUEST['FormName'];
    $selectedStatus = $_REQUEST['selectedStatus'];

    if ($selectedStatus == 0) {
        $status = "Pending";
    } elseif ($selectedStatus == 1) {
        $status = "Approved";
    } elseif ($selectedStatus == 2) {
        $status = "Un-Approved";
    } else {
        $status = "Deleted";
    }

    $dataURL = $baseURL . "AppsAPI/view-data-list.php?ui=$UserID&formId=$FormID&status=$selectedStatus";
    ?>

    <section class="card">
        <header class="card-header">
            <div class="card-title"><?php echo getValue('datacollectionform', 'FormName', "id = $FormID"); ?></div>
            <div class="card-subtitle">Status: <?php echo $status; ?></div>
        </header>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="datatable-ajax"
                   data-url="<?php echo $dataURL; ?>">
                <thead>
                <tr>
                    <th>SL</th>
                    <th>Record ID</th>
                    <th>Data Name</th>
                    <th>Send Time</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </section>

    <?php
}
?>

<?php
include_once "../Components/footer-includes.php";
?>