<?PHP
include_once '../Components/header-includes.php';

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";

$UserID = $app->cleanInput($_REQUEST['UserID']);

$AuthToken = $_GET['authToken'];

if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
    exit();
}

$qry = "SELECT id, CompanyID FROM userinfo Where id = ?";
$row = $app->getDBConnection()->fetch($qry, $UserID);
$CompanyID = $row->CompanyID;

$qryForm = "SELECT dcf.id, dcf.FormName FROM assignformtoagent aftu
JOIN datacollectionform dcf on dcf.id = aftu.FormID 
WHERE aftu.UserID = ? order by dcf.id DESC";
$rsForm = $app->getDBConnection()->query($qryForm, $UserID);

$qryPSU = "SELECT id, PSU FROM PSUList WHERE PSUUserID = ? AND IsSampleChecked = 1";
$rsPSU = $app->getDBConnection()->query($qryPSU, $UserID);
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
                                <optgroup label="Select Form">
                                <?PHP
                                foreach ($rsForm as $row) {
                                    echo '<option value="' . $row->id . '">' . $row->FormName . '</option>';
                                }
                                ?>
								</optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row pb-3">
                        <label class="col-lg-3 control-label text-lg-end pt-2">PSU Select<span
                                    class="required">*</span></label>
                        <div class="col-lg-6">
                            <select data-plugin-selectTwo class="form-control populate" name="selectedPSU"
                                    id="selectedPSU">
                                <option value="">Select PSU</option>
                                <?PHP
                                foreach ($rsPSU as $row) {
                                    echo "<option value=\"" . $row->PSU . "\">" . $row->PSU . "</option>";
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
</div>
<?php
if ($_REQUEST['show'] === 'Show') {
    $FormID = $_REQUEST['FormName'];
    $selectedPSU = $_REQUEST['selectedPSU'];

    $dataURL = $baseURL . "AppsAPI/view-sample-list-test.php?ui=$UserID&psu=$selectedPSU&FormID=$FormID";
    ?>

    <section class="card">
        <header class="card-header">
            <div class="card-title">PSU : <?php echo $selectedPSU; ?></div>
        </header>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="datatable-ajax"
                   data-url="<?php echo $dataURL; ?>">
                <thead>
                <tr>             					
					<th>নমুনা খানা নম্বর</th>
                    <th>লিস্টিং খানা নম্বর</th>
                    <th>অবস্থা</th>
                    <th>খানা প্রধানের নাম</th>
                    <th>মোবাইল</th>
                    <th>ঠিকানা </th>
                    <th>খানার অবস্থান(GPS)</th>					
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
