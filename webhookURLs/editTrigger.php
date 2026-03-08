<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
include "../Config/config.php";
include "../Lib/lib.php";

use Solvers\Dsql\Application;
use Solvers\Dsql\ODKAPIClient;

$app = new Application();

$requestAry = explode("/", $_SERVER['REQUEST_URI']);

$instance_id = end($requestAry);
$xmlFormId = $requestAry[count($requestAry)-2];

//Read Current Form Version
$currentFormVersion = '';
$fh = fopen('../AppsAPI/CurrentFormVersion.txt', 'r');
$currentFormVersion = fgets($fh);
fclose($fh);

date_default_timezone_set("Asia/Dhaka");
$date = date('Ymd');
$CurrentDateTime = date('Y-m-d_H-i-s');

$conn = PDOConnectDB();
$cn = ConnectDB();

$odkClient = new ODKAPIClient("sse1.sakib@gmail.com", 'DkPas@12345678');
$projectId = 4;  // ← your active ODK project

$odkClient->getDataFromServer("projects/$projectId/forms/$xmlFormId/submissions/uuid:$instance_id.xml", "");

if (empty($odkClient->result)) {
    echo "<pre>❌ ODK Central returned empty or 404 response. Check project ID and permissions.</pre>";
    exit;
}

$xml = @simplexml_load_string($odkClient->result);
if ($xml === false) {
    echo "<pre>❌ Failed to parse XML from Central. Response was not valid XML.</pre>";
    exit;
}

$xmlAry = json_decode(json_encode(simplexml_load_string($odkClient->result)), true);
$xFormRecordId = $xmlAry["@attributes"]['xFormRecordId'];

//echo "<pre>";
//var_dump($xFormRecordId);
//echo "</pre>";exit;

$sql = "SELECT xfr.UserID, 
			xfr.FormId, 
			xfr.DataName, 
			xfr.FormGroupId, 
			xfr.CompanyId, 
			xfr.PSU, 
			xfr.SampleHHNo,
			xfr.IsApproved 
		FROM xformrecord xfr 
		WHERE xfr.id=$xFormRecordId";
$row = $app->getDBConnection()->fetch($sql);

$userName = getValue("userinfo", "UserName", "id=$row->UserID");

$dirPath = "../EditForms/$row->UserID/$row->FormId/";

if (!is_dir($dirPath)) {
    $old = umask(0);
    mkdir($dirPath, 0777, true);
    umask($old);
}

$fileName = $dirPath . "xFormrecordID_".$xFormRecordId."_".$CurrentDateTime.".xml";
$myfile = fopen($fileName, "w") or die("Unable to create file!");
fwrite($myfile, $xml->AsXML());
fclose($myfile);

//echo "<pre>";
//var_dump($xFormRecordId, $row->UserID, $row->FormId);
//echo "</pre>";exit;

//MsgBox('Change is ready to update.');
/*
$backupQry = "SET IDENTITY_INSERT Edit_masterdatarecord ON;
				INSERT INTO Edit_masterdatarecord 
					(
						id, 
						XFormRecordId, 
						UserID, 
						FormId, 
						DataName, 
						FormGroupId, 
						CompanyId, 
						ColumnTitle, 
						ColumnName, 
						ColumnValue, 
						EntryDate, 
						IsApproved, 
						PSU, 
						Comments,
						IsCorrected,
						IsEdited 
					)
				SELECT id, 
					XFormRecordId, 
					UserID, 
					FormId, 
					DataName, 
					FormGroupId, 
					CompanyId, 
					ColumnTitle, 
					ColumnName, 
					ColumnValue, 
					EntryDate, 
					IsApproved, 
					PSU, 
					Comments, 
					IsCorrected,
					IsEdited												
				FROM masterdatarecord_UnApproved 
				WHERE XFormRecordId = $xFormRecordId;
			SET IDENTITY_INSERT Edit_masterdatarecord OFF;";
			*/


			
$backupQry = "INSERT INTO Edit_masterdatarecord 
					(			 
						XFormRecordId, 
						UserID, 
						FormId, 
						DataName, 
						FormGroupId, 
						CompanyId, 
						ColumnTitle, 
						ColumnName, 
						ColumnValue, 
						EntryDate, 
						IsApproved, 
						PSU, 
						SampleHHNo, 
						Comments,
						IsCorrected,
						IsEdited 
					)
				SELECT  
					XFormRecordId, 
					UserID, 
					FormId, 
					DataName, 
					FormGroupId, 
					CompanyId, 
					ColumnTitle, 
					ColumnName, 
					ColumnValue, 
					EntryDate, 
					IsApproved, 
					PSU, 
					SampleHHNo, 
					Comments, 
					IsCorrected,
					IsEdited												
				FROM masterdatarecord_UnApproved 
				WHERE XFormRecordId = $xFormRecordId; ";
			

if ($app->getDBConnection()->query($backupQry)) {
	//$IsValidVersion = 0;
	$xmlIterator = new SimpleXMLIterator($xml->AsXML());

//echo "<pre>";	
//var_dump($xmlIterator);
//echo "</pre>";exit;

	$exclusionAry = array(
		"instanceID",
		"deprecatedID",
		"form_version_no"
	);
	
	$sql = "SELECT xcol.ColumnName 
			FROM xformColumnNameForGroup xcol 
			WHERE xcol.CompanyId=$row->CompanyId 
				AND xcol.FormId=$row->FormId 
				AND xcol.IsEditable = 0";
	$ary = $app->getDBConnection()->fetchAll($sql);
	foreach ($ary as $k => $v) {
		$exclusionAry[] = $v['ColumnName'];
	}
	
	for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
		if ($xmlIterator->hasChildren()) {
			foreach ($xmlIterator->getChildren() as $name => $data) {
				if (count($data) > 0) {
					foreach ($data as $nameChild => $dataChild) {
						if (!in_array($nameChild, $exclusionAry) && (strpos($name, 'Note') === false) && (strpos($name, '_cal') === false)) {
							$dataChild = str_replace("'", " ", $dataChild);
							$NameArray[] = $nameChild;
							$ValueArray[] = "N'" . $dataChild . "'";
						}
						if (($nameChild == "form_version_no") && ($dataChild == $currentFormVersion))
							$IsValidVersion = 1;
					}
				} else {

					if (($name == "form_version_no") && ($data == $currentFormVersion))
						$IsValidVersion = 1;

					if (!in_array($name, $exclusionAry) && (strpos($name, 'Note') === false) && (strpos($name, '_cal') === false)) {
						$data = str_replace("'", " ", $data);
						$NameArray[] = $name;
						$ValueArray[] = "N'" . $data . "'";

					}
				}
			}
		}
	}
	
	$oldNameArray = array();
	$oldValueArray = array();
	$sql = "SELECT xfrg.ColumnName AS DataName, 
				COALESCE(un.ColumnValue, '') AS ColumnValue
			FROM xformColumnNameForGroup xfrg
				JOIN masterdatarecord_UnApproved un ON xfrg.ColumnName = un.ColumnName AND xfrg.FormId=un.FormId AND un.XFormRecordId = $xFormRecordId 
				JOIN xformGroupName xg ON xfrg.GroupId  = xg.id
				LEFT JOIN xformGroupName xgp ON xg.parent_id = xgp.id
			WHERE xfrg.FormId = $row->FormId 
			ORDER BY xg.id";
	$xfrs = $app->getDBConnection()->fetchAll($sql);
	foreach ($xfrs as $xfr) {
		$oldNameArray[] = $xfr->DataName;
		$data = str_replace("'", " ", $xfr->ColumnValue);
		$oldValueArray[] = "N'" . $data . "'";
	}
	
	$definationArray = array();
	$sql = "SELECT xcn.ColumnName FROM xformColumnNameForGroup xcn WHERE xcn.FormId=$row->FormId";
	$xcns = $app->getDBConnection()->fetchAll($sql);
	foreach ($xcns as $xcn) {
		$definationArray[] = $xcn->ColumnName;
	}
	
	$nonEditable = array();
	$sql = "SELECT xcn.ColumnName FROM xformColumnNameForGroup xcn WHERE xcn.FormId=$row->FormId AND xcn.IsEditable=0";
	$xcns = $app->getDBConnection()->fetchAll($sql);
	foreach ($xcns as $xcn) {
		$nonEditable[] = $xcn->ColumnName;
	}
	
	$adding = array_diff($NameArray, $oldNameArray);
	$modified = array_diff($NameArray, $adding);
	$deleted = array_diff($oldNameArray, $modified);
	$deletable = array_diff($deleted, $nonEditable);
	$removeCal = array_diff($adding, $definationArray);
	$added = array_diff($adding, $removeCal);

//echo "<pre>";
//var_dump($deleted, $modified, $added);
//echo "</pre>";
//exit;

	$IsValidVersion = 1;
	if ($IsValidVersion == 1) {
		if (count($deletable) > 0){
			foreach ($deletable as $k => $v) {
				$sqlQry = "DELETE FROM masterdatarecord_UnApproved 
							WHERE XFormRecordId = $xFormRecordId 
									AND UserID = $row->UserID 
									AND FormId = $row->FormId 
									AND DataName = N'$row->DataName' 
									AND FormGroupId = $row->FormGroupId 
									AND CompanyId = $row->CompanyId 
									AND PSU = $row->PSU 
									AND SampleHHNo = $row->SampleHHNo 
									AND ColumnName = N'$v'";
				$conn->query($sqlQry);
			}
		}
		
		if (count($modified) > 0) {
			foreach ($modified as $k => $v) {
				$key = array_search($v, $oldNameArray);
//echo "<pre>";
//var_dump($ValueArray[$k], $oldValueArray[$key]);
//echo "</pre>";
//exit;			
				$oldValueAry = explode(" ", trim(substr($oldValueArray[$key], 2, -1)));
				sort($oldValueAry);
				$myOldValue = implode(" ", $oldValueAry);
				
				$oldValueAry = explode(" ", trim(substr($ValueArray[$k], 2, -1)));
				sort($oldValueAry);
				$myNewValue = implode(" ", $oldValueAry);
				
				if ($myNewValue != $myOldValue) {
					$EntryDate = date('d-m-Y H:i:s');
					$comm = "<b>$userName</b> Edited at $EntryDate:<br />&nbsp;&nbsp;&nbsp;<b style=\"color:red;\">Previous Value:</b> ".trim(substr($oldValueArray[$key], 2, -1))."<br />&nbsp;&nbsp;&nbsp;<b style=\"color:green;\">Edited Value:</b> ".trim(substr($ValueArray[$k], 2, -1))."<br />";
											
					$sqlQry = "UPDATE masterdatarecord_UnApproved SET 
									ColumnValue = $ValueArray[$k], 
									Comments = COALESCE(Comments, '')+N'$comm', 
									IsEdited=2
								WHERE XFormRecordId = $xFormRecordId 
										AND UserID = $row->UserID 
										AND FormId = $row->FormId 
										AND DataName = N'$row->DataName' 
										AND FormGroupId = $row->FormGroupId 
										AND CompanyId = $row->CompanyId 
										AND PSU = $row->PSU 
										AND SampleHHNo = $row->SampleHHNo 
										AND ColumnName = N'$v'";
//echo $sqlQry;exit;
					$conn->query($sqlQry);
				}
			}
		}

		if (count($added) > 0) {
			foreach ($added as $k => $v) {
				$EntryDate = date('d-m-Y H:i:s');
				$comm = "<b>$userName</b> Edited at $EntryDate:<br />&nbsp;&nbsp;&nbsp;<b style=\"color:red;\">Previous Value:</b> - <br />&nbsp;&nbsp;&nbsp;<b style=\"color:green;\">Edited Value:</b> ".trim(substr($ValueArray[$k], 2, -1))."<br />";
					
				$sql = "INSERT INTO masterdatarecord_UnApproved 
							(XFormRecordId, 
								UserID, 
								FormId, 
								DataName, 
								FormGroupId, 
								CompanyId, 
								ColumnName, 
								ColumnValue, 
								EntryDate, 
								IsApproved, 
								PSU, 
								SampleHHNo, 
								Comments, 
								IsEdited) 
							VALUES($xFormRecordId, 
								$row->UserID, 
								$row->FormId, 
								N'$row->DataName', 
								$row->FormGroupId, 
								$row->CompanyId, 
								N'$v', 
								$ValueArray[$k], 
								CURRENT_TIMESTAMP,
								2, 
								$row->PSU, 
								$row->SampleHHNo, 
								N'$comm', 
								1
							)";
				$conn->query($sql);
			}
		}
		
		$StatusUpdateQuery = "UPDATE [dbo].[xformrecord] SET [IsApproved] = 0, IsEdited = (COALESCE(IsEdited, 0)+1) WHERE id='$xFormRecordId'";
		$conn->query($StatusUpdateQuery);
		
		$odkClient->deleteDataFromServer("projects/1/forms/$xmlFormId/submissions/uuid:".$instance_id);
	}
}

// echo "<script>window.location.href='https://sasbbs.com/-/thanks'</script>";

$successPage = "https://sasbbsbd.com/thankyou.html";
$errorPage   = "https://sasbbsbd.com/error.html";

echo "<script>
	if (/Mobi|Android/i.test(navigator.userAgent)) {
		// ✅ On mobile: redirect to Thank You page
		window.location.href = '$successPage';
	} else {
		// ✅ On desktop: show thank you in a new tab or popup
		window.location.href = '$successPage';
	}
</script>";