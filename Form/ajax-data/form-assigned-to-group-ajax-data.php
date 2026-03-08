<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();


$qryGetData = "SELECT aftg.id, dcf.FormName, dcfg.FormGroupName FROM  assignformtoformgroup aftg 
            INNER JOIN datacollectionform dcf ON aftg.FormId = dcf.id 
            INNER JOIN datacollectionformgroup dcfg ON aftg.FormGroupId = dcfg.id 
            ORDER BY dcf.FormName ASC  ";

$rsQryGetData = $app->getDBConnection()->fetchAll($qryGetData);

$data = array();

foreach ($rsQryGetData as $row) {
    $Id = $row->id;
    $FormName = $row->FormName;
    $FormGroupName = $row->FormGroupName;

    $SubData = array();

    $SubData[] = $Id;
    $SubData[] = $FormName;
    $SubData[] = $FormGroupName;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$Id');\"><i class=\"far fa-trash-alt\"></i></button>
                </div>";

    $SubData[] = $actions;
    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

