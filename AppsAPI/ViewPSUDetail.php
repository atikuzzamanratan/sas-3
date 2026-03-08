<?PHP
//error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$PSU = $app->cleanInput($_REQUEST['PSU']);
$UserID = $app->cleanInput($_REQUEST['UserID']);

$AuthToken = $_GET['authToken'];


if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
    exit();
}

$MasterDataQuery = "SELECT  id, PSU, DivisionName, DivisionCode, DistrictName, DistrictCode, CityCorporationName, CityCorporationCode, UpazilaName, UpazilaCode, MunicipalityName,  MunicipalityCode, UnionWardName, UnionWardCode, RMO, MauzaName, MauzaCode, VillageName, VillageCode, PSUUserID, NumberOfRecord, NumberOfRecordForMainSurvey, CompanyID,EnumerationArea, EnumerationCode, FarmName FROM PSUList WHERE PSU = ? AND PSUUserID = ?";
$MDRrow = $app->getDBConnection()->fetch($MasterDataQuery, $PSU, $UserID);


include_once '../Components/header-includes.php';

if (count($MDRrow) > 0) {
    $PsuUserDB = $MDRrow->PSUUserID;
    $UserName = getValue('userinfo', 'UserName', "id = $PsuUserDB");
    $UserFullName = getValue('userinfo', 'FullName', "id = $PsuUserDB");
    $UserInfo = "$UserFullName ($UserName/$PsuUserDB)"
    ?>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-responsive-lg table-bordered table-striped table-sm mb-0">
                            <thead>
                            <tr>
                                <th>GEO Information Name</th>
                                <th>GEO Information Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($MDRrow->FarmName) {
                                ?>

                                <tr>
                                    <td>Farm Name</td>
                                    <td><?php echo $MDRrow->FarmName; ?></td>
                                </tr>

                                <?php
                            }
                            ?>
                            <tr>
                                <td>PSU</td>
                                <td><?php echo $MDRrow->PSU; ?></td>
                            </tr>
                            <tr>
                                <td>Division Name</td>
                                <td><?php echo $MDRrow->DivisionName; ?></td>
                            </tr>
                            <tr>
                                <td>Division Code</td>
                                <td><?php echo $MDRrow->DivisionCode; ?></td>
                            </tr>
                            <tr>
                                <td>District Name</td>
                                <td><?php echo $MDRrow->DistrictName; ?></td>
                            </tr>
                            <tr>
                                <td>District Code</td>
                                <td><?php echo $MDRrow->DistrictCode; ?></td>
                            </tr>
                            <tr>
                                <td>City Corporation Name</td>
                                <td><?php echo $MDRrow->CityCorporationName; ?></td>
                            </tr>
                            <tr>
                                <td>City Corporation Code</td>
                                <td><?php echo $MDRrow->CityCorporationCode; ?></td>
                            </tr>
                            <tr>
                                <td>Upazila Name</td>
                                <td><?php echo $MDRrow->UpazilaName; ?></td>
                            </tr>
                            <tr>
                                <td>Upazila Code</td>
                                <td><?php echo $MDRrow->UpazilaCode; ?></td>
                            </tr>
                            <tr>
                                <td>Municipality Name</td>
                                <td><?php echo $MDRrow->MunicipalityName; ?></td>
                            </tr>
                            <tr>
                                <td>Municipality Code</td>
                                <td><?php echo $MDRrow->MunicipalityCode; ?></td>
                            </tr>
                            <tr>
                                <td>Union/Ward Name</td>
                                <td><?php echo $MDRrow->UnionWardName; ?></td>
                            </tr>
                            <tr>
                                <td>Union/Ward Code</td>
                                <td><?php echo $MDRrow->UnionWardCode; ?></td>
                            </tr>
                            <tr>
                                <td>RMO</td>
                                <td><?php echo $MDRrow->RMO; ?></td>
                            </tr>
                            <tr>
                                <td>Mauza Name</td>
                                <td><?php echo $MDRrow->MauzaName; ?></td>
                            </tr>
                            <tr>
                                <td>Mauza Code</td>
                                <td><?php echo $MDRrow->MauzaCode; ?></td>
                            </tr>
                            <tr>
                                <td>Village Name</td>
                                <td><?php echo $MDRrow->VillageName; ?></td>
                            </tr>
                            <tr>
                                <td>Village Code</td>
                                <td><?php echo $MDRrow->VillageCode; ?></td>
                            </tr>
                            <tr>
                                <td>PSU User</td>
                                <td><?php echo $UserInfo; ?></td>
                            </tr>
                            <tr>
                                <td>Number Of Record For Listing</td>
                                <td><?php echo $MDRrow->NumberOfRecord; ?></td>
                            </tr>
                            <tr>
                                <td>Number Of Record For Main Survey</td>
                                <td><?php echo $MDRrow->NumberOfRecordForMainSurvey; ?></td>
                            </tr>
                            <tr>
                                <td>Enumeration Area</td>
                                <td><?php echo $MDRrow->EnumerationArea; ?></td>
                            </tr>
                            <tr>
                                <td>Enumeration Area Code</td>
                                <td><?php echo $MDRrow->EnumerationCode; ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <?php
} else {
    ?>
    <div class="container">
        <h4 style="alignment: center">Sorry, information for PSU <b><?php echo $PSU; ?></b> not found!</h4>
    </div>
    <?php
}
