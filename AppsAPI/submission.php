<?php
session_start();
date_default_timezone_set('Asia/Dhaka');

require '../vendor/autoload.php';

use Solvers\Dsql\Application;
//use Solvers\Dsql\ODKAPIClient;

$app = new Application();

require_once '../Config/config.php';
require_once '../Lib/lib.php';

//https://sasbbsbd.com/AppsAPI/submission.php?deviceID=nWhaiFJYuZLz6C7l&UserID=69&FormID=2&DataName=SAS%20Listing_PSU_801_HH_1&SampleHHNo=1&PSUNo=801

$baseURL = get_base_url();

$conn = PDOConnectDB();

//2-Listing form
//3-Main form
//3-Farm form

//Read Current Form Version
$currentFormVersion = '';
$fh = fopen('CurrentFormVersion.txt', 'r');
$currentFormVersion = fgets($fh);
fclose($fh);

$date = date('Ymd');
$CurrentDateTime = date('Y-m-d H:i:s');

LogWriter($_REQUEST);

$deviceID = $_GET['deviceID'];
$deviceID = trim(str_replace(['imei:', 'collect:'], ' ', $deviceID));
$UserID = $_GET['UserID'];
$FormID = $_GET['FormID'];
$DataName = $_GET['DataName'];
$SampleHHNo = $_GET['SampleHHNo'];
$PSU = $_GET['PSUNo'];

//2-Listing form
/*if($FormID==$formIdSamplingData){
    $currentFormVersion = 5;
}else{
    $currentFormVersion = $currentFormVersion;
}*/

//3-Main form
if($FormID==$formIdMainData){
    //list_no
    $list_no=getValue('SampleMapping', 'SampleHHNumber', "PSU = $PSU and UserID = $UserID and MainHHNumber = $SampleHHNo");
}else{
    //list_no
    $list_no=$SampleHHNo;
}

if (($UserID == NULL) || ($UserID == "")) {
    $UserID = "9999";
}

$NameArray = array();
$ValueArray = array();


$dir_path = "../SendForms/$UserID/$FormID/";

if (!is_dir($dir_path)) {
    $old = umask(0);
    mkdir($dir_path, 0777, true);
    umask($old);
}

$ActualFileName = NULL;

$resp = 201;

if ($_SERVER['REQUEST_METHOD'] === "HEAD")
    $resp = 204;
elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
    foreach ($_FILES as $file) {
        $FileName = $file['name'];
        LogWriter($FileName);
        $FileExtention = end((explode(".", $FileName)));
        if ($FileExtention == "xml") {
            $file['name'] = str_replace(" ", "_", $file['name']);
            $ActualFileName = $file['name'];
        }
        move_uploaded_file($file['tmp_name'], $dir_path . $file['name']);
    }
}
//echo $ActualFileName;
//exit();

if ($ActualFileName != NULL) {
    $cn = ConnectDB();
    $FormQry = "SELECT id, FormGroupId, CompanyID FROM assignformtoagent WHERE UserID = '$UserID' AND FormID = '$FormID' AND Status='Active'";
    LogWriter($FormQry);
    $rs = db_fetch_array(db_query($FormQry, $cn));
    $IDValue = $rs['id'];
    $FormGroupId = $rs['FormGroupId'];
    $CompanyID = $rs['CompanyID'];
    $CompanyID = 2;

    if (($IDValue > 0) && ($resp == 201)) {
        try {

            $NameArray[] = "PSU";
            $ValueArray[] = "N'" . $PSU . "'";
						
			$NameArray[] = "list_no";
            $ValueArray[] = "N'" . $list_no . "'";			
						
			if($FormID==2){
			  $NameArray[] = "SampleHHNo";
              $ValueArray[] = "N'" . $SampleHHNo . "'";			
			}
			

            $db_file_path = "SendForms/$UserID/$FormID/$ActualFileName";
            $FormInsertQry = "INSERT INTO xformrecord(UserID, FormId, DataName, FormGroupId, CompanyId, DeviceID, XFormsFilePath, PSU, SampleHHNo) VALUES 
            ('$UserID', '$FormID', N'$DataName', '$FormGroupId', '$CompanyID', '$deviceID', '$db_file_path', '$PSU', '$SampleHHNo')";

            db_query($FormInsertQry, $cn);
            LogWriter($FormInsertQry);

            $LastIDRS = db_fetch_array(db_query("select @@IDENTITY as LastID", $cn));

            $xFormRecordID = $LastIDRS['LastID'];

            $ActualFilePath = $baseURL . $db_file_path;

            $KeyValueArray = array();

            $IsValidVersion = 0;
			//$instanceID = "";

            $xmlIterator = new SimpleXMLIterator(file_get_contents($ActualFilePath));
            for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
                if ($xmlIterator->hasChildren()) {
                    foreach ($xmlIterator->getChildren() as $name => $data) {
                        if (count($data) > 0) {
                            foreach ($data as $nameChild => $dataChild) {
                                if (($nameChild != 'instanceID') && (strpos($name, 'Note') === false) && (strpos($name, '_cal') === false)) {
                                    $dataChild = str_replace("'", " ", $dataChild);
                                    $NameArray[] = $nameChild;
                                    $ValueArray[] = "N'" . $dataChild . "'";
                                }
								/*
								if ($nameChild == "instanceID") {
									$instanceID = substr($nameChild, 5);
								}
								*/
                                if (($nameChild == "form_version_no") && ($dataChild == $currentFormVersion))
                                    $IsValidVersion = 1;
                            }
                        } else {

                            if (($name == "form_version_no") && ($data == $currentFormVersion))
                                $IsValidVersion = 1;

                            if (($name != 'instanceID') && (strpos($name, 'Note') === false) && (strpos($name, '_cal') === false)) {
                                $data = str_replace("'", " ", $data);
                                $NameArray[] = $name;
                                $ValueArray[] = "N'" . $data . "'";

                            }
							/*
							if ($name == "instanceID") {
								$instanceID = substr($name, 5);
							}
							*/
                        }
                    }
                }
            }


            if ($IsValidVersion == 1) {

                $SqlQry = "INSERT INTO masterdatarecord_Pending (XFormRecordId, UserID, FormId, DataName, FormGroupId, CompanyId, 
                ColumnTitle, ColumnName, ColumnValue, PSU, SampleHHNo)
                SELECT t.[XFormRecordId], t.[UserID], t.[FormId], t.[DataName], t.[FormGroupId], t.[CompanyId], 
                t.[ColumnTitle], t.[ColumnName], t.[ColumnValue], t.[PSU],t.[SampleHHNo]
                FROM (VALUES ";

                $k = 0;
                $Sql2 = "";
                foreach ($NameArray as $NameData) {
                    if ($k == 0) {
                        $Sql2 = "('$xFormRecordID','$UserID','$FormID','$DataName','$FormGroupId','$CompanyID','','$NameData',$ValueArray[$k],'$PSU','$SampleHHNo')";
                    } else {
                        $Sql2 .= "," . "('$xFormRecordID','$UserID','$FormID','$DataName','$FormGroupId','$CompanyID','','$NameData',$ValueArray[$k],'$PSU','$SampleHHNo')";
                    }

                    $k++;
                }
                $SqlQry .= $Sql2;
                $SqlQry .= ") AS t([XFormRecordId], [UserID], [FormId], [DataName], [FormGroupId], [CompanyId], [ColumnTitle], [ColumnName], [ColumnValue], [PSU], [SampleHHNo])";
                $conn->query($SqlQry);
                LogWriter($SqlQry);
				
				$xml = simplexml_load_file("../".$db_file_path);
				//$attr = json_decode(json_encode($xml->meta->instanceID), true);
				//$instanceID = substr($attr[0],5);
				
				if($FormID==$formIdSamplingData){
			       $StatusUpdateQuery = "UPDATE [dbo].[xformrecord] SET [IsApproved] = 1 WHERE FormId=$formIdSamplingData AND id='$xFormRecordID'";
                   $conn->query($StatusUpdateQuery);				   
			    }
				
				/*
				//Start of ODK Server Code
				$userName = getValue('userinfo', 'UserName', "id=$UserID");
				$odkClient = new ODKAPIClient(trim($userName)."@gmail.com", trim($userName));
				
				$attr = $xml->attributes();
				$xmlFormId = str_replace(" ", "", $attr['id']);
				$odkClient->getDataFromServer("projects/1/forms/$xmlFormId");
				$xml->addAttribute("version", $odkClient->result['version']);
				$xml->attributes()->id = $xmlFormId;
				$xml->addAttribute("xFormRecordId", $xFormRecordID);
				
				$odkClient->postXMLToServer("projects/1/forms/$xmlFormId/submissions", $xml);
				*/
            } else {
                $FormDeletetQry = "DELETE FROM  xformrecord where id ='$xFormRecordID'";
                db_query($FormDeletetQry, $cn);
                LogWriter($FormDeletetQry);
                //$resp="Your form version is not correct.";
                $resp = 207;
            }

        } catch (Exception $e) {
            //$resp = $e->getMessage();
            $resp = 209;
        }
    } else {
        //$resp = "You are not permitted to send the data";
        $resp = 208;
    }
    //echo $resp;
}


header("X-OpenRosa-Version: 1.0");
header("X-OpenRosa-Accept-Content-Length: 2000000");
header("Date: " . date('r'), false, $resp);

function LogWriter($log_message)
{
    $LogEnable = 0;
    if ($LogEnable == 1) {
        $time_value = (date_default_timezone_set("Asia/Dhaka") * 120);
        $current_time = date("H:i:s", time() + $time_value);
        $hour = substr($current_time, 0, 2);
        $text_file_name = date('d-m-Y') . " " . $hour . ".txt";
        $current_date_time = date('Y-m-d') . " " . $current_time;
        $fp = fopen($text_file_name, 'a');
        $writing_info = "Time: " . $current_date_time . "|| Message: " . $log_message . "\r\n";
        fwrite($fp, $writing_info);
        fclose($fp);
    }
}

/*
function getInstanceID(){
	//8a5680dc-d30a-4d89-a953-9fdc43415538
	$id = generateRandomString(8);
	$id .= "-".generateRandomString(4);
	$id .= "-".generateRandomString(4);
	$id .= "-".generateRandomString(4);
	$id .= "-".generateRandomString(12);
	
	return $id;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}
*/