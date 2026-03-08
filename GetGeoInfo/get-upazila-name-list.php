<?php
error_reporting(E_ALL);

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

include "../Config/config.php";
include "../Lib/lib.php";
//require_once '../Components/header-includes.php';
//include_once '../Components/footer-includes.php';

$app = new Application();

$DivisionCode = xss_clean($_REQUEST["divisionCode"]);

$qry = "SELECT DISTINCT(DistrictCode), DistrictName FROM GeoInformation WHERE DivisionCode = ? ORDER BY DistrictName";
$resQry = $app->getDBConnection()->fetchAll($qry, $DivisionCode);

$SelectOption = '
<label class="col-lg-3 control-label text-sm-end pt-2">District Select<span class="required">*</span></label>
    <div class="col-lg-6">
        <select data-plugin-selectTwo class="form-control populate" name="districtName" id="districtName" required>
            <option value="">Choose district</option>';

foreach ($resQry as $row) {
    $SelectOption .= '<option value="' . $row->DistrictCode . '">' . $row->DistrictName . '</option>';
}
$SelectOption .= '
        </select>
    </div>';

echo $SelectOption;

