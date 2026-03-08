<?php

use Solvers\Dsql\Application;

session_start();

require '../vendor/autoload.php';

$app = new Application();

if (!is_ajax()) {
    echo 'Sorry, Page not found';
    exit(0);
}

$userId = $_SESSION['UserID'];
$companyId = $_SESSION['loggedUserCompanyID'];

$ShowFunction = empty($_POST['ShowFunction']) ? '' : $app->cleanInput($_POST['ShowFunction']);
$RequestingValue = empty($_POST['RequestingValue']) ? '' : $app->cleanInput($_POST['RequestingValue']);
$NextCallFunction = empty($_POST['NextCallFunction']) ? '' : $app->cleanInput($_POST['NextCallFunction']);

if ($ShowFunction === 'ShowFormGroup') {
    $sql = "SELECT id,FormGroupName FROM datacollectionformgroup WHERE ActivityID=? GROUP BY id,FormGroupName ORDER BY FormGroupName ASC";
    $results = $app->getDBConnection()->fetchAll($sql, $RequestingValue);

    $NextCallFunction = "ShowDropDown('FormGroupId','FormDiv',$NextCallFunction,'NO')";

} else if ($ShowFunction === 'NewOneForm') {
    $sql = "SELECT datacollectionform.id as FormID,datacollectionform.FormName as FormName 
FROM  assignformtoformgroup 
    INNER JOIN datacollectionform ON assignformtoformgroup.FormId = datacollectionform.id 
WHERE FormGroupId=?";

    $results = $app->getDBConnection()->fetchAll($sql, $RequestingValue);
    $str = '<select class="form-control" name="SingleFormName" id="SingleFormID" onchange="' . $NextCallFunction . '"><option value="">Select Form Name</option>';
    foreach ($results as $result) {
        $str .= "<option value='" . $result->FormID . "'>" . $result->FormName . "</option>";
    }

    echo $str . "</select>";
}
