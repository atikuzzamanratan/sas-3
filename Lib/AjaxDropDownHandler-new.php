<?php
session_start();
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Components/header-includes.php';

require_once "../Config/config.php";

require_once "../Lib/lib.php";

$loggedUserID = $_SESSION['UserID'];
$loggedUserName = $app->cleanInput($_SESSION['User']);
$loggedUserCompanyID = $_SESSION['loggedUserCompanyID'];

$ShowFunction = $_REQUEST['ShowFunction'];
$RequestingValue = $_REQUEST['RequestingValue'];
$NextCallFunction = $_REQUEST['NextCallFunction'];

if ($ShowFunction == "DistrictUser")
    ShowDistrictUser($app, $RequestingValue, $loggedUserID);
else if ($ShowFunction == "ShowUser")
    ShowUser($app, $loggedUserName, $loggedUserID);
else if ($ShowFunction == "ShowUpazilaUser")
    ShowUpazilaUser($app, $RequestingValue);
else if ($ShowFunction == "ShowUnionWardUser")
    ShowUnionWardUser($app, $RequestingValue);
else if ($ShowFunction == "ShowMauzaUser")
    ShowMauzaUser($app, $RequestingValue);
else if ($ShowFunction == "ShowVillageUser")
    ShowVillageUser($app, $RequestingValue);

function ShowUser($app, $loggedUserName, $loggedUserID)
{
    $DivisionCode = $_REQUEST['DivisionCode'] ?? null;
    $DistrictCode = $_REQUEST['DistrictCode'] ?? null;
    $UpazilaCode = $_REQUEST['UpazilaCode'] ?? null;
    $UnionWardCode = $_REQUEST['UnionWardCode'] ?? null;
    $MauzaCode = $_REQUEST['MauzaCode'] ?? null;
    $VillageCode = $_REQUEST['VillageCode'] ?? null;
	$RequiredUser = $_REQUEST['RequiredUser'] ?? null;
	
    $query = "SELECT DISTINCT u.id, 
				u.UserName, 
				u.FullName 
			FROM userinfo AS u 
				JOIN PsuList AS p ON u.id = p.PSUUserID ";
	if (strpos($loggedUserName, 'val') !== false) {
		if (strpos($loggedUserName, 'cval') === false) {
			$query .= " JOIN assignsupervisor ap ON ap.UserID = u.id AND ap.ValidatorID = $loggedUserID ";
		}
	}
	$query .= " WHERE 1=1 ";
	if ($DivisionCode !== null) {
		$query .= " AND p.DivisionCode = $DivisionCode ";
	}
	if ($DistrictCode !== null) {
		$query .= " AND p.DistrictCode = $DistrictCode "; 
	}
	if ($UpazilaCode !== null) {
		$query .= " AND p.UpazilaCode= $UpazilaCode "; 
	}
	if ($UnionWardCode !== null) {
		$query .= " AND p.UnionWardCode = $UnionWardCode "; 
	}
	if ($MauzaCode !== null) {
		$query .= " AND p.MauzaCode = $MauzaCode "; 
	}
	if ($VillageCode !== null) {
		$query .= " AND p.VillageCode = $VillageCode ";
	}

	$rsQuery = $app->getDBConnection()->fetchAll($query);


?>
    <label class="col-lg-3 control-label text-lg-end pt-2"><?=(!$RequiredUser ? "or " : "")?>User Select<?php if ($RequiredUser) { ?><span class="required">*</span><?php } else { ?><span class="required"></span><?php } ?></label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID" id="SelectedUserID" <?=($RequiredUser ? " required " : "")?>
            title="Please select a user">
            <option value="">Choose a User</option>
            <?php
            foreach ($rsQuery as $row) {
                echo '<option value="' . $row->id . '">' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
            }
            ?>
        </select>
    </div>
<?php
}

function ShowDistrictUser($app, $RequestingValue, $UserID)
{
    $loggedUserName = getValue('userinfo', 'UserName', "id = $UserID");

    if (strpos($loggedUserName, 'dist') !== false) {
        $query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE a.DistCoordinatorID = ?";
        $rsQuery = $app->getDBConnection()->fetchAll($query, $UserID);
    } else if (strpos($loggedUserName, 'cs') !== false) {
        $query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE a.SupervisorID = ?";
        $rsQuery = $app->getDBConnection()->fetchAll($query, $UserID);
    } else if (strpos($loggedUserName, 'val') !== false) {
		if (strpos($loggedUserName, 'cval') === false) {
			$query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE a.ValidatorID = ? AND p.DivisionCode = ?";
			$rsQuery = $app->getDBConnection()->fetchAll($query, $UserID, $RequestingValue);
		} else {
			$query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE p.DivisionCode = ?";
			$rsQuery = $app->getDBConnection()->fetchAll($query, $RequestingValue);
		}
    } else {
        $query = "SELECT DISTINCT DistrictName, DistrictCode FROM PSUList WHERE DivisionCode = $RequestingValue order by DistrictName asc";
        $rsQuery = $app->getDBConnection()->fetchAll($query);
    }
    //echo $loggedUserName;
	$RequiredUser = $_REQUEST['RequiredUser'] ?? 0;
    $NextCallFunction = "ShowDropDown4('DistrictCode','UpazilaDiv','userDiv','ShowUpazilaUser',['DivisionCode','DistrictCode'], {'RequiredUser':'$RequiredUser'})";
?>

    <label class="col-lg-3 control-label text-sm-end pt-2">District Select
        <?php if (strpos($loggedUserName, 'admin') === false) { ?>
            <span class="required">*</span>
        <?php } ?>
    </label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="DistrictCode" id="DistrictCode"
            onchange="<?php echo $NextCallFunction; ?>"
            <?php if (strpos($loggedUserName, 'admin') === false) { ?>
            required
            <?php } ?>>
            <?php
            echo "<option selected value=\"\">Select district</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->DistrictCode . "\">" . $row->DistrictName . "</option>";
            }
            ?>
        </select>
    </div>
<?php
}
function ShowUpazilaUser($app, $RequestingValue)
{
    $query = "SELECT DISTINCT UpazilaName, UpazilaCode FROM PSUList WHERE DistrictCode = ? order by UpazilaName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $RequestingValue);

    $RequiredUser = $_REQUEST['RequiredUser'] ?? 0;
    $NextCallFunction = "ShowDropDown4('UpazilaCode','UnionWardDiv','userDiv','ShowUnionWardUser',['DivisionCode','DistrictCode','UpazilaCode'], {'RequiredUser':'$RequiredUser'})";
?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Upazila Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="UpazilaCode" id="UpazilaCode"
            onchange="<?php echo $NextCallFunction; ?>">
            <?php
            echo "<option selected value=\"\">Select upazila</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->UpazilaCode . "\">" . $row->UpazilaName . "</option>";
            }
            ?>
        </select>
    </div>
<?php
}
function ShowUnionWardUser($app, $RequestingValue)
{
    $DistrictCode = $_REQUEST['DistrictCode'];
    $query = "SELECT DISTINCT UnionWardName, UnionWardCode FROM PSUList WHERE DistrictCode = ? AND UpazilaCode = ? order by UnionWardName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $DistrictCode, $RequestingValue);

    $RequiredUser = $_REQUEST['RequiredUser'] ?? 0;
    $NextCallFunction = "ShowDropDown4('UnionWardCode','MauzaDiv','userDiv','ShowMauzaUser',['DivisionCode','DistrictCode','UpazilaCode','UnionWardCode'], {'RequiredUser':'$RequiredUser'})";
?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Union/Ward Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="UnionWardCode" id="UnionWardCode"
            onchange="<?php echo $NextCallFunction; ?>">
            <?php
            echo "<option selected value=\"\">Select Ward</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->UnionWardCode . "\">" . $row->UnionWardName . "</option>";
            }
            ?>
        </select>
    </div>
<?php
}
function ShowMauzaUser($app, $RequestingValue)
{
    $DistrictCode = $_REQUEST['DistrictCode'];
    $UpazilaCode = $_REQUEST['UpazilaCode'];
    $query = "SELECT DISTINCT MauzaName, MauzaCode FROM PSUList WHERE DistrictCode = ? AND UpazilaCode = ? AND UnionWardCode = ? order by MauzaName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $DistrictCode, $UpazilaCode, $RequestingValue);

    $RequiredUser = $_REQUEST['RequiredUser'] ?? 0;
    $NextCallFunction = "ShowDropDown4('MauzaCode','VillageDiv','userDiv','ShowVillageUser',['DivisionCode','DistrictCode','UpazilaCode','UnionWardCode','MauzaCode'], {'RequiredUser':'$RequiredUser'})";
?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Mauza Name Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="MauzaCode" id="MauzaCode"
            onchange="<?php echo $NextCallFunction; ?>">
            <?php
            echo "<option selected value=\"\">Select Mauza Name</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->MauzaCode . "\">" . $row->MauzaName . "</option>";
            }
            ?>
        </select>
    </div>
<?php
}

function ShowVillageUser($app, $RequestingValue)
{
    $DistrictCode = $_REQUEST['DistrictCode'];
    $UpazilaCode = $_REQUEST['UpazilaCode'];
    $UnionWardCode = $_REQUEST['UnionWardCode'];
    $query = "SELECT DISTINCT VillageName, VillageCode FROM PSUList WHERE DistrictCode = ? AND UpazilaCode = ? AND UnionWardCode = ? AND MauzaCode = ? order by VillageName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $DistrictCode, $UpazilaCode, $UnionWardCode, $RequestingValue);

?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Village Name Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="VillageCode" id="VillageCode">
            <?php
            echo "<option selected value=\"\">Select VillageName Name</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->VillageCode . "\">" . $row->VillageName . "</option>";
            }
            ?>
        </select>
    </div>
<?php
}
