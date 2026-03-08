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
$loggedUserCompanyID = $_SESSION['loggedUserCompanyID'];

$ShowFunction = $_REQUEST['ShowFunction'];
$RequestingValue = $_REQUEST['RequestingValue'];
$NextCallFunction = $_REQUEST['NextCallFunction'];
$SelectedValue = $_REQUEST['SelectedValue'];

if ($ShowFunction == "ShowUserOnCompany")
    ShowUserOnCompany($app, $RequestingValue);

else if ($ShowFunction == "ShowFormGroup")
    ShowFormGroup($app, $RequestingValue);

else if ($ShowFunction == "ShowForm")
    ShowForm($app, $RequestingValue);

else if ($ShowFunction == "ShowColumnName")
    ShowColumnName($app, $RequestingValue);

else if ($ShowFunction == "ShowDistrict")
    ShowDistrict($app, $RequestingValue, $NextCallFunction, $loggedUserID, $SelectedValue);

else if ($ShowFunction == "ShowUpazila")
    ShowUpazila($app, $RequestingValue, $SelectedValue);

else if ($ShowFunction == "ShowUnionWard")
    ShowUnionWard($app, $RequestingValue, $SelectedValue);

else if ($ShowFunction == "ShowMauza")
    ShowMauza($app, $RequestingValue, $SelectedValue);

else if ($ShowFunction == "ShowVillage")
    ShowVillage($app, $RequestingValue, $SelectedValue);


function ShowUserOnCompany($app, $RequestingValue)
{
    $query = "SELECT id, UserName, FullName FROM userinfo WHERE CompanyID = ? ORDER BY UserName ASC";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $RequestingValue);
    ?>
    <label class="col-lg-3 control-label text-lg-end pt-2">User Select<span class="required">*</span></label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="SelectedUserID" id="SelectedUserID"
                title="Please select a user" required
                onchange="ShowDropDown('SelectedUserID', 'ShowFormGroupDiv', 'ShowFormGroup', 'ShowForm')">
            <option value="">Choose a User</option>
            <?php
            foreach ($rsQuery as $row) {
                echo '<option value="' . $row->id . '"' . '>' . $row->UserName . ' | ' . substr($row->FullName, 0, 102) . '</option>';
            }
            ?>
        </select>
    </div>
    <?php
}

function ShowFormGroup($app, $RequestingValue)
{
    $query = "SELECT dcfg.id, dcfg.FormGroupName FROM datacollectionformgroup dcfg LEFT JOIN userinfo ui ON dcfg.CompanyID = ui.CompanyID WHERE ui.id = ? 
    GROUP BY dcfg.id, dcfg.FormGroupName ORDER BY dcfg.FormGroupName ASC";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $RequestingValue);
    ?>
    <label class="col-lg-3 control-label text-lg-end pt-2">Form Group Select<span
                class="required">*</span></label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="FormGroupId" id="FormGroupId"
                title="Please select a group" required
                onchange="ShowDropDown1('company', 'SelectedUserID', 'FormGroupId', 'ShowFormDiv', 'ShowForm', '')">
            <option value="">Choose Form Group</option>
            <?php
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->id . "\">" . $row->FormGroupName . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
}

function ShowForm($app, $RequestingValue)
{
    $RequestingValueArray = explode("|", $RequestingValue);
    //$UserCompanyId = $RequestingValueArray[0];
    $FormUserID = $RequestingValueArray[1];
    $FormGroupID = $RequestingValueArray[2];
    $queryformid = "SELECT id, FormID FROM assignformtoagent WHERE UserID = ?";
    $rss = $app->getDBConnection()->fetchAll($queryformid, $FormUserID);

    foreach ($rss as $row) {
        $FormID .= "|" . $row->FormID;
    }

    $FormIDArray = explode("|", $FormID);
    $query = "SELECT dcf.id, dcf.FormName FROM assignformtoformgroup afg
    INNER JOIN datacollectionform dcf ON afg.FormId = dcf.id 
    WHERE dcf.Status='Active' and FormGroupId = ? ORDER BY id DESC";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $FormGroupID);
    ?>
    <label class="col-lg-3 control-label text-lg-end pt-2">Form Select<span
                class="required">*</span></label>
    <div class="col-lg-6">
        <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered datatables"
               id="example">
            <tr>
                <?php
                echo "</tr>";
                echo "<tr>";
                foreach ($rsQuery as $row) {
                    if (in_array($row->id, $FormIDArray))
                        $FormAvailable = 1;
                    else
                        $FormAvailable = 0;

                    echo "<td width=\"273\">";
                    echo $row['FormName'];
                    echo "</td>";
                    ?>
                    <td width="131">
                        <input class="checkbox1" name="MP[]" id="MP" type="checkbox"
                               value="<?php echo $row->id; ?>" <?php if ($FormAvailable == 1) echo "checked='checked'" ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>
        </table>
    </div> <?php
}

function ShowDistrict($app, $RequestingValue, $NextCallFunction, $UserID, $SelectedValue)
{
    $loggedUserName = getValue('userinfo', 'UserName', "id = $UserID");
    if (strpos($loggedUserName, 'dist') !== false) {
        $query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE a.DistCoordinatorID = ?";
        $rsQuery = $app->getDBConnection()->fetchAll($query, $UserID);
    } else if (strpos($loggedUserName, 'div') !== false) {
        $query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE a.DivCoordinatorID = ? AND p.DivisionCode = ?";
//die("User Id: ".$RequestingValue);        
		$rsQuery = $app->getDBConnection()->fetchAll($query, $UserID, $RequestingValue);
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

    $NextCallFunction = "ShowDropDown('DistrictCode','UpazilaDiv','$NextCallFunction','Yes')";
    ?>

    <label class="col-lg-3 control-label text-sm-end pt-2">District Select
        <?php if (strpos($loggedUserName, 'admin') === false && strpos($loggedUserName, 'div') === false) { ?>
            <span class="required">*</span>
        <?php } ?>
    </label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="DistrictCode" id="DistrictCode"
                onchange="<?php echo $NextCallFunction; ?>"
            <?php if (strpos($loggedUserName, 'admin') === false && strpos($loggedUserName, 'div') === false) { ?>
                required
            <?php } ?>>
            <?php
            echo "<option selected value=\"\">Select district</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->DistrictCode . "\"" . (isset($SelectedValue) && !empty($SelectedValue) && $row->DistrictCode == $SelectedValue ? ' selected' : '') . ">" . $row->DistrictName . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
}

function ShowDistrictRec($app, $RequestingValue, $NextCallFunction, $UserID, $SelectedValue)
{
    $loggedUserName = getValue('userinfo', 'UserName', "id = $UserID");
    if (strpos($loggedUserName, 'dist') !== false) {
        $query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE a.DistCoordinatorID = ?";
        $rsQuery = $app->getDBConnection()->fetchAll($query, $UserID);
    } else if (strpos($loggedUserName, 'div') !== false) {
        $query = "SELECT DISTINCT p.DistrictName, p.DistrictCode FROM PSUList as p JOIN assignsupervisor as a ON p.PSUUserID = a.UserID WHERE a.DivCoordinatorID = ? AND p.DivisionCode = ?";
//die("User Id: ".$RequestingValue);        
		$rsQuery = $app->getDBConnection()->fetchAll($query, $UserID, $RequestingValue);
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

    $NextCallFunction = "ShowDropDown('DistrictCode','UpazilaDiv','$NextCallFunction','Yes')";
    ?>

    <label class="col-lg-3 control-label text-sm-end pt-2">District Select
        <span class="required">*</span>
    </label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="DistrictCode" id="DistrictCode"
                onchange="<?php echo $NextCallFunction; ?>"
                required
            >
            <?php
            echo "<option selected value=\"\">Select district</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->DistrictCode . "\"" . (isset($SelectedValue) && !empty($SelectedValue) && $row->DistrictCode == $SelectedValue ? ' selected' : '') . ">" . $row->DistrictName . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
}

function ShowUpazila($app, $RequestingValue, $SelectedValue)
{
    $query = "SELECT DISTINCT UpazilaName, UpazilaCode FROM PSUList WHERE DistrictCode = ? order by UpazilaName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $RequestingValue);

    $NextCallFunction = "ShowDropDown1('DistrictCode','UpazilaCode','DivisionCode','UnionWardDiv','ShowUnionWard','ShowMauza')";
    ?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Upazila Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="UpazilaCode" id="UpazilaCode"
                onchange="<?php echo $NextCallFunction; ?>">
            <?php
            echo "<option selected value=\"\">Select upazila</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->UpazilaCode . "\"" . (isset($SelectedValue) && !empty($SelectedValue) && $row->UpazilaCode == $SelectedValue ? ' selected' : '') . ">" . $row->UpazilaName . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
}

function ShowUnionWard($app, $RequestingValue, $SelectedValue)
{
    $requestValueArray = explode('|', $RequestingValue);
    $RequestingValue1 = $requestValueArray[0];
    $RequestingValue2 = $requestValueArray[1];
    //echo "$RequestingValue1 || $RequestingValue2";
    $query = "SELECT DISTINCT UnionWardName, UnionWardCode FROM PSUList WHERE DistrictCode = $RequestingValue1 AND UpazilaCode = $RequestingValue2 order by UnionWardName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query);

    $NextCallFunction = "ShowDropDown1('DistrictCode','UpazilaCode','UnionWardCode','MauzaDiv','ShowMauza')";
    ?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Union/Ward Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" class="form-control" name="UnionWardCode"
                id="UnionWardCode" onchange="<?php echo $NextCallFunction; ?>">
            <?php
            echo "<option selected value=\"\">Select union/ward</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->UnionWardCode . "\"" . (isset($SelectedValue) && !empty($SelectedValue) && $row->UnionWardCode == $SelectedValue ? ' selected' : '') . ">" . $row->UnionWardName . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
}

function ShowMauza($app, $RequestingValue, $SelectedValue)
{
    $requestValueArray = explode('|', $RequestingValue);
    $query = "SELECT DISTINCT MauzaName, MauzaCode FROM PSUList WHERE DistrictCode = ? AND UpazilaCode = ? AND UnionWardCode = ? order by MauzaName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $requestValueArray[0], $requestValueArray[1], $requestValueArray[2]);

    $NextCallFunction = "ShowDropDown3('DistrictCode','UpazilaCode','UnionWardCode','MauzaCode','VillageDiv','ShowVillage')";
    ?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Mauza Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" class="form-control" name="MauzaCode" id="MauzaCode"
                onchange="<?php echo $NextCallFunction; ?>">
            <?php
            echo "<option selected value=\"\">Select mauza</option>";
            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->MauzaCode . "\"" . (isset($SelectedValue) && !empty($SelectedValue) && $row->MauzaCode == $SelectedValue ? ' selected' : '') . ">" . $row->MauzaName . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
}

function ShowVillage($app, $RequestingValue, $SelectedValue)
{
    $requestValueArray = explode('|', $RequestingValue);
    $RequestingValue1 = $requestValueArray[0];
    $RequestingValue2 = $requestValueArray[1];
    $RequestingValue3 = $requestValueArray[2];
    $RequestingValue4 = $requestValueArray[3];

    $query = "SELECT DISTINCT VillageName, VillageCode FROM PSUList WHERE DistrictCode = ? AND UpazilaCode = ? AND UnionWardCode = ? AND MauzaCode = ? order by VillageName asc";
    $rsQuery = $app->getDBConnection()->fetchAll($query, $RequestingValue1, $RequestingValue2, $RequestingValue3, $RequestingValue4);
    ?>

    <label class="col-lg-3 control-label text-sm-end pt-2">Village Select</label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" class="form-control" name="VillageCode"
                id="VillageCode">
            <?php
            echo "<option selected value=\"\">Select village</option>";

            foreach ($rsQuery as $row) {
                echo "<option value=\"" . $row->VillageCode . "\"" . (isset($SelectedValue) && !empty($SelectedValue) && $row->VillageCode == $SelectedValue ? ' selected' : '') . ">" . $row->VillageName . "</option>";
            }
            ?>
        </select>
    </div>
    <?php
}
