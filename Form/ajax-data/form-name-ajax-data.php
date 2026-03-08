<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['par'] != '') {
    $param = $app->cleanInput($_REQUEST['par']);
}

if ($_REQUEST['fi'] != '') {
    $FormID = $app->cleanInput($_REQUEST['fi']);
}

if ($param === '1') {
    $qryFromDetail = "SELECT dcf.id, dcf.FormName, dcf.FormDescription, dcf.FormFilePath, doc.CompanyName, dcf.Status, dcf.CreatedDate FROM  datacollectionform dcf 
        INNER JOIN dataownercompany doc ON dcf.CompanyID = doc.id ORDER BY dcf.id DESC";
} else {
    $qryFromDetail = "SELECT dcf.id, dcf.FormName, dcf.FormDescription, dcf.FormFilePath, doc.CompanyName, dcf.Status, dcf.CreatedDate FROM  datacollectionform dcf 
        INNER JOIN dataownercompany doc ON dcf.CompanyID = doc.id WHERE dcf.id = '$FormID' ORDER BY dcf.id DESC";
}

$rsQryFromDetail = $app->getDBConnection()->fetchAll($qryFromDetail);

$data = array();
$il = 1;

foreach ($rsQryFromDetail as $row) {
    $FormId = $row->id;
    $FormName = $row->FormName;
    $Description = $row->FormDescription;
    $Company = $row->CompanyName;
    $Status = $row->Status;
    $CreatedDate = date_format($row->CreatedDate, "d-m-Y");
    $FormFilePath = $row->FormFilePath;
    $ActualFilePath = $baseURL . $FormFilePath;

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $FormName;
    $SubData[] = $Description;
    $SubData[] = $Company;
    $SubData[] = $Status;
    $SubData[] = $CreatedDate;

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleView\" type=\"button\" class=\"btn btn-outline-success\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal$FormId\"><i class=\"fas fa-eye\"></i></button>
                    <button title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$FormId\"><i class=\"fas fa-pencil-alt\"></i></button>
                    <button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$FormId','$FormFilePath');\"><i class=\"far fa-trash-alt\"></i></button>
                </div> 
                
                <!-- Modal View-->
                <div class=\"modal fade\" id=\"viewDataModal$FormId\" tabindex=\"-1\" aria-labelledby=\"viewDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"viewDataModalLabel\">$FormName</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\" style=\"height: 90%\">
                            <div class=\"col-lg-12\">
                                <iframe class=\"image-frame-wrapper\" style=\"width: 100%;\" id=\"frame$FormId\" src=\"$FormFilePath\" scrolling=\"yes\"></iframe>
                          </div>
                      </div>
                      <div class=\"modal-footer\">
                        <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                      </div>
                    </div>
                  </div>
                </div>  
                
                 <!-- Modal Edit-->
                <div class=\"modal fade\" id=\"editDataModal$FormId\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog\">
                    <div class=\"modal-content\">
                      <div class=\"modal-header\">
                      <h5 class=\"modal-title\" id=\"editDataModalLabel\">Edit Form</h5>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                      </div>
                      <div class=\"modal-body\">
                        <form id=\"editForm\" method=\"POST\" action=\"\">
                            <div class=\"form-group\">
                                <label for=\"FormName\">Name</label>
                                <input type=\"text\" class=\"form-control\" name=\"FormName\" id=\"FormName$FormId\" value=\"$FormName\" readonly>
                            </div>
                            <div class=\"form-group\">
                                <label for=\"FormDesc\">Description<span class=\"required\">*</span></label>
                                <input type=\"text\" class=\"form-control\" name=\"FormDesc\" id=\"FormDesc$FormId\" value=\"$Description\" required>
                            </div>
                            
                            <div class=\"form-group\">
                                <label for=\"Status\">Status</label>
                                <select name=\"Status\" id=\"Status$FormId\" class=\"form-control\">
                                    <option selected>$Status</option>
                                    <option value=\"Active\">Active</option>
                                    <option value=\"InActive\">InActive</option>
                                </select>
                            </div>
                            
                            <!--<div class=\"form-group row pb-4\">
                                <label class=\"col-lg-2 control-label text-lg-end pt-2\">File</label>
                                <div class=\"col-lg-10\">
                                    <div class=\"fileupload fileupload-new\" data-provides=\"fileupload\">
                                        <div class=\"input-append\">
                                            <div class=\"uneditable-input\">
                                                <i class=\"fas fa-file fileupload-exists\"></i>
                                                <span class=\"fileupload-preview\"></span>
                                            </div>
                                            <span class=\"btn btn-default btn-file\">
                                            <span class=\"fileupload-exists\">Change</span>
                                            <span class=\"fileupload-new\">Select file</span>
                                            <input type=\"file\" id=\"uploadingFile\" name=\"uploadingFile\"/>
                                        </span>
                                            <a href=\"#\" class=\"btn btn-default fileupload-exists\"
                                               data-dismiss=\"fileupload\">Remove</a>
                                        </div>
                                        <span>
                                        <p style=\"font-style: italic\"><strong>Note:</strong> Only <strong
                                                    style=\"color: red\">.xml</strong> format is allowed.</p>
                                    </span>
                                    </div>
                                </div>
                            </div>-->
                            
                            <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
                                <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
                                onclick= \"
                                var fDesc = document.getElementById('FormDesc$FormId').value;
                                var fStatus = document.getElementById('Status$FormId').value;

                                EditItem('$FormId', fDesc, fStatus);
                                \">
                                Save changes
                                </button>
                             </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>";

    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

