<?php
// error_reporting(1);











error_reporting(0);
ini_set('display_errors', 0);
















require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();






















function includeModal($UserDBID, $UserName, $UserPassword, $UserFullName, $UserMobileNo, $UserEmail, $IsActive, $UserStatus) {
    return "
    <div class='modal fade' id='editDataModal$UserDBID' tabindex='-1' aria-labelledby='editDataModalLabel' aria-hidden='true'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-header'>
            <h5 class='modal-title' id='editDataModalLabel'>Edit Form</h5>
            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
          </div>
          <div class='modal-body'>
            <form>
              <div class='form-group'>
                <label>Username</label>
                <input type='text' class='form-control bg-light' id='UserName$UserDBID' value='$UserName' readonly>
              </div>
              <div class='form-group'>
                <label>Password</label>
                <input type='text' class='form-control' id='UserPass$UserDBID' value='$UserPassword'>
              </div>
              <div class='form-group'>
                <label>Full Name</label>
                <input type='text' class='form-control' id='FullName$UserDBID' value='$UserFullName'>
              </div>
              <div class='form-group'>
                <label>Mobile No</label>
                <input type='text' class='form-control' id='MobileNo$UserDBID' value='$UserMobileNo'>
              </div>
              <div class='form-group'>
                <label>Email</label>
                <input type='text' class='form-control' id='Email$UserDBID' value='$UserEmail'>
              </div>
              <div class='form-group'>
                <label>Status</label>
                <select id='Status$UserDBID' class='form-control'>
                  <option value='$IsActive' selected>$UserStatus</option>
                  <option value='1'>Active</option>
                  <option value='0'>Inactive</option>
                </select>
              </div>
              <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                <button type='button' class='btn btn-primary' onclick=\"
                  EditItem('$UserDBID',
                    document.getElementById('UserName$UserDBID').value,
                    document.getElementById('UserPass$UserDBID').value,
                    document.getElementById('FullName$UserDBID').value,
                    document.getElementById('MobileNo$UserDBID').value,
                    document.getElementById('Email$UserDBID').value,
                    document.getElementById('Status$UserDBID').value
                  );
                \">Save changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>";
}
























if ($_REQUEST['par'] != '') {
    $param = $app->cleanInput($_REQUEST['par']);
}

if ($_REQUEST['ui'] != '') {
    $UserID = $app->cleanInput($_REQUEST['ui']);
}

if ($_REQUEST['lun'] != '') {
    $LoggedUserName = $app->cleanInput($_REQUEST['lun']);
}

if ($_REQUEST['luid'] != '') {
    $LoggedUserID = $app->cleanInput($_REQUEST['luid']);
}

if ($_REQUEST['lci'] != '') {
    $LoggedUserCompanyID = $app->cleanInput($_REQUEST['lci']);
}




// --- NEW: Detect if logged user is a Supervisor ---
// --- Detect if logged user is Supervisor ---
$SuperID = 0;
if (!empty($LoggedUserID)) {
    $qrySupervisor = "SELECT id FROM assignsupervisor WHERE SupervisorID = ?";
    $rsSupervisor = $app->getDBConnection()->fetch($qrySupervisor, $LoggedUserID);
    if ($rsSupervisor && isset($rsSupervisor->id)) {
        $SuperID = $rsSupervisor->id;
    }
}







if ($param == '1') {
    if ($LoggedUserName == 'admin') {
        $qry = "SELECT ui.id, ui.UserName, ui.Password, ui.MobileNumber, ui.EmailAddress, doc.CompanyName, ui.SupportID, ui.FullName, ui.IsActive 
        FROM userinfo ui JOIN dataownercompany doc ON ui.CompanyID = doc.id 
        WHERE ui.UserName <> ? ORDER BY ui.CompanyID DESC";
        $resQry = $app->getDBConnection()->fetchAll($qry, $LoggedUserName);
    } else if (strpos($LoggedUserName, 'dist') !== false) {
        $qry = "SELECT ui.id, ui.UserName, ui.Password, ui.MobileNumber, ui.EmailAddress, doc.CompanyName, ui.SupportID, ui.FullName, ui.IsActive 
        FROM userinfo ui JOIN dataownercompany doc ON ui.CompanyID = doc.id 
            JOIN assignsupervisor a ON a.UserID = ui.id
        WHERE ui.IsActive = 1 AND a.DistCoordinatorID = ? ORDER BY ui.CompanyID DESC";
        $resQry = $app->getDBConnection()->fetchAll($qry, $LoggedUserID);
    } else if (strpos($LoggedUserName, 'div') !== false) {
        $qry = "SELECT ui.id, ui.UserName, ui.Password, ui.MobileNumber, ui.EmailAddress, doc.CompanyName, ui.SupportID, ui.FullName, ui.IsActive 
        FROM userinfo ui JOIN dataownercompany doc ON ui.CompanyID = doc.id 
            JOIN assignsupervisor a ON a.UserID = ui.id
        WHERE ui.IsActive = 1 AND a.DivCoordinatorID = ? ORDER BY ui.CompanyID DESC";
        $resQry = $app->getDBConnection()->fetchAll($qry, $LoggedUserID);
    } else {
        $qry = "SELECT ui.id, ui.UserName, ui.Password, ui.MobileNumber, ui.EmailAddress, doc.CompanyName, ui.SupportID, ui.FullName, ui.IsActive 
        FROM userinfo ui JOIN dataownercompany doc ON ui.CompanyID = doc.id 
        WHERE ui.UserName <> ? AND ui.CompanyID = ? ORDER BY ui.CompanyID DESC";
        $resQry = $app->getDBConnection()->fetchAll($qry, $LoggedUserName, $LoggedUserCompanyID);
    }
} else {
    $qry = "SELECT ui.id, ui.UserName, ui.Password, ui.MobileNumber, ui.EmailAddress, doc.CompanyName, ui.SupportID, ui.FullName, ui.IsActive 
    FROM userinfo ui JOIN dataownercompany doc ON ui.CompanyID = doc.id WHERE ui.id = ?";
    $resQry = $app->getDBConnection()->fetchAll($qry, $UserID);
}

$data = array();
$il = 1;

foreach ($resQry as $row) {
    $UserDBID = $row->id;
    $UserName = $row->UserName;
    $UserPassword = $row->Password;
    $UserFullName = $row->FullName;
    $UserMobileNo = $row->MobileNumber;
    $UserEmail = $row->EmailAddress;
    $UserProject = $row->CompanyName;
    $UserSupportID = $row->SupportID;
    $IsActive = $row->IsActive;
    if ($IsActive == '1') {
        $UserStatus = "Active";
    } else {
        $UserStatus = "Inactive";
    }

    $SubData = array();

    $SubData[] = $il;
    $SubData[] = $UserDBID;
    $SubData[] = $UserName;
    $SubData[] = $UserPassword;
    $SubData[] = $UserFullName;
    $SubData[] = $UserMobileNo;
    $SubData[] = $UserEmail;
    $SubData[] = $UserProject;
    $SubData[] = $UserStatus;

    // if (strpos($LoggedUserName, 'admin') !== false) {
    //     $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
    //                 <button title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$UserDBID\"><i class=\"fas fa-pencil-alt\"></i></button>
    //                 <button title=\"$btnTitleDelete\" type=\"button\" class=\"btn btn-outline-danger\" style=\"display: inline-block\" onclick=\"DeleteItem('$UserDBID');\"><i class=\"far fa-trash-alt\"></i></button>
    //             </div>
                
    //              <!-- Modal Edit-->
    //             <div class=\"modal fade\" id=\"editDataModal$UserDBID\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
    //               <div class=\"modal-dialog\">
    //                 <div class=\"modal-content\">
    //                   <div class=\"modal-header\">
    //                   <h5 class=\"modal-title\" id=\"editDataModalLabel\">Edit Form</h5>
    //                     <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
    //                   </div>
    //                   <div class=\"modal-body\">
    //                     <form id=\"editForm\" method=\"POST\" action=\"\">
    //                         <div class=\"form-group\">
    //                             <label for=\"UserName\">Username</label>
    //                             <input type=\"text\" class=\"form-control\" name=\"UserName\" id=\"UserName$UserDBID\" value=\"$UserName\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"UserPass\">Password<span class=\"required\">*</span></label>
    //                             <input type=\"text\" class=\"form-control\" name=\"UserPass\" id=\"UserPass$UserDBID\" value=\"$UserPassword\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"FullName\">Full Name<span class=\"required\">*</span></label>
    //                             <input type=\"text\" class=\"form-control\" name=\"FullName\" id=\"FullName$UserDBID\" value=\"$UserFullName\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"MobileNo\">Mobile No<span class=\"required\">*</span></label>
    //                             <input type=\"text\" class=\"form-control\" name=\"MobileNo\" id=\"MobileNo$UserDBID\" value=\"$UserMobileNo\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"Email\">Email</label>
    //                             <input type=\"text\" class=\"form-control\" name=\"Email\" id=\"Email$UserDBID\" value=\"$UserEmail\">
    //                         </div>
                            
    //                         <div class=\"form-group\">
    //                             <label for=\"Status\">Status</label>
    //                             <select name=\"Status\" id=\"Status$UserDBID\" class=\"form-control\">
    //                                 <option value=\"$IsActive\" selected>$UserStatus</option>
    //                                 <option value=\"1\">Active</option>
    //                                 <option value=\"0\">Inactive</option>
    //                             </select>
    //                         </div>
    //                         <div class=\"modal-footer\">
    //                             <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
    //                             <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
    //                             onclick= \"
    //                             var uName = document.getElementById('UserName$UserDBID').value;
    //                             var uPass = document.getElementById('UserPass$UserDBID').value;
    //                             var uFullName = document.getElementById('FullName$UserDBID').value;
    //                             var uMobileNo = document.getElementById('MobileNo$UserDBID').value;
    //                             var uEmail = document.getElementById('Email$UserDBID').value;
    //                             var fStatus = document.getElementById('Status$UserDBID').value;

    //                             EditItem('$UserDBID', uName, uPass, uFullName, uMobileNo, uEmail, fStatus);
    //                             \">
    //                             Save changes
    //                             </button>
    //                          </div>
    //                     </form>
    //                   </div>
    //                 </div>
    //               </div>
    //             </div>";
    // }else if (strpos($LoggedUserName, 'dist') !== false) {
    //     $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
    //                 <button title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$UserDBID\"><i class=\"fas fa-pencil-alt\"></i></button>
                    
    //             </div>
                
    //              <!-- Modal Edit-->
    //             <div class=\"modal fade\" id=\"editDataModal$UserDBID\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
    //               <div class=\"modal-dialog\">
    //                 <div class=\"modal-content\">
    //                   <div class=\"modal-header\">
    //                   <h5 class=\"modal-title\" id=\"editDataModalLabel\">Edit Form</h5>
    //                     <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
    //                   </div>
    //                   <div class=\"modal-body\">
    //                     <form id=\"editForm\" method=\"POST\" action=\"\">
    //                         <div class=\"form-group\">
    //                             <label for=\"UserName\">Username</label>
    //                             <input type=\"text\" class=\"form-control\" name=\"UserName\" id=\"UserName$UserDBID\" value=\"$UserName\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"UserPass\">Password<span class=\"required\">*</span></label>
    //                             <input type=\"text\" class=\"form-control\" name=\"UserPass\" id=\"UserPass$UserDBID\" value=\"$UserPassword\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"FullName\">Full Name<span class=\"required\">*</span></label>
    //                             <input type=\"text\" class=\"form-control\" name=\"FullName\" id=\"FullName$UserDBID\" value=\"$UserFullName\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"MobileNo\">Mobile No<span class=\"required\">*</span></label>
    //                             <input type=\"text\" class=\"form-control\" name=\"MobileNo\" id=\"MobileNo$UserDBID\" value=\"$UserMobileNo\" required>
    //                         </div>
    //                         <div class=\"form-group\">
    //                             <label for=\"Email\">Email</label>
    //                             <input type=\"text\" class=\"form-control\" name=\"Email\" id=\"Email$UserDBID\" value=\"$UserEmail\">
    //                         </div>
                            
    //                         <div class=\"form-group\">
    //                             <label for=\"Status\">Status</label>
    //                             <select name=\"Status\" id=\"Status$UserDBID\" class=\"form-control\">
    //                                 <option value=\"$IsActive\" selected>$UserStatus</option>
    //                                 <option value=\"1\">Active</option>
    //                                 <option value=\"0\">InActive</option>
    //                             </select>
    //                         </div>
    //                         <div class=\"modal-footer\">
    //                             <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
    //                             <button type=\"button\" class=\"btn btn-primary\" name=\"Save\" id=\"Save\" value=\"Update\" 
    //                             onclick= \"
    //                             var uName = document.getElementById('UserName$UserDBID').value;
    //                             var uPass = document.getElementById('UserPass$UserDBID').value;                
    //                             var uFullName = document.getElementById('FullName$UserDBID').value;
    //                             var uMobileNo = document.getElementById('MobileNo$UserDBID').value;
    //                             var uEmail = document.getElementById('Email$UserDBID').value;
    //                             var fStatus = document.getElementById('Status$UserDBID').value;

    //                             EditItem('$UserDBID', uName, uPass, uFullName, uMobileNo, uEmail, fStatus);
    //                             \">
    //                             Save changes
    //                             </button>
    //                          </div>
    //                     </form>
    //                   </div>
    //                 </div>
    //               </div>
    //             </div>";
    // }else{
    //     $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
    //                 <button title=\"$btnTitleEdit\" type=\"button\" class=\"btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$UserDBID\"><i class=\"fas fa-pencil-alt\"></i></button>
                    
    //             </div>";
    // }
















    // --- FIXED: Action buttons and modal handling (Supervisor enabled) ---
    if (strpos($LoggedUserName, 'admin') !== false) {
        // Admins: edit + delete
        $actions = "<div style=\"display:flex;align-items:center;justify-content:center;\">
                        <button type=\"button\" class=\"btn btn-outline-primary\" style=\"margin:0 1px;\" 
                            data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$UserDBID\">
                            <i class=\"fas fa-pencil-alt\"></i>
                        </button>
                        <button type=\"button\" class=\"btn btn-outline-danger\" style=\"margin:0 1px;\" 
                            onclick=\"DeleteItem('$UserDBID');\">
                            <i class=\"far fa-trash-alt\"></i>
                        </button>
                    </div>";
        $actions .= includeModal($UserDBID, $UserName, $UserPassword, $UserFullName, $UserMobileNo, $UserEmail, $IsActive, $UserStatus);
    }

    else if (strpos($LoggedUserName, 'dist') !== false || !empty($SuperID)) {
        // District Coordinators OR Supervisors: edit only
        $actions = "<div style=\"display:flex;align-items:center;justify-content:center;\">
                        <button type=\"button\" class=\"btn btn-outline-primary\" style=\"margin:0 1px;\" 
                            data-bs-toggle=\"modal\" data-bs-target=\"#editDataModal$UserDBID\">
                            <i class=\"fas fa-pencil-alt\"></i>
                        </button>
                    </div>";
        $actions .= includeModal($UserDBID, $UserName, $UserPassword, $UserFullName, $UserMobileNo, $UserEmail, $IsActive, $UserStatus);
    }

    else {
        // Everyone else: no modal
        $actions = "<div style=\"text-align:center;\">—</div>";
    }

























    $SubData[] = $actions;

    $il++;

    $data[] = $SubData;
}


/*$SubData[] = "$LoggedUserName|$LoggedUserCompanyID|$qry";

$data[] = $SubData;*/

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

