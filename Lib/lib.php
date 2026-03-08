<?PHP
function getValue($table, $field, $cond)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT $field AS tt FROM $table ";
    $SQL .= "WHERE $cond";
    $stmt = $conn->query($SQL);
    $row = $stmt->fetch();
    return $row['tt'];
}

function getValueMultiple($table, $field, $cond)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT $field AS tt FROM $table ";
    $SQL .= "WHERE $cond";
    foreach ($conn->query($SQL) as $row) {
        $rowValue = $row['tt'];
    }
    return $rowValue;
}

function getURL($parent)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT MenuURL FROM menudefine WHERE MenuId = '$parent'";
    $stmt = $conn->query($SQL);
    $row = $stmt->fetch();

    return $row['MenuURL'];
}

function Save($table, $field, $value): bool
{
    $conn = PDOConnectDB();
    $SQL = "INSERT INTO [$table]($field)";
    $SQL .= " VALUES($value)";
    $stmt = $conn->query($SQL);
    if ($stmt)
        return true;
    else
        return false;
}

function InsertData($query): bool
{
    $conn = PDOConnectDB();
    $stmt = $conn->query($query);
    if ($stmt)
        return true;
    else
        return false;
}

function Edit($table, $param, $cond): bool
{
    $conn = PDOConnectDB();
    $SQL = "UPDATE $table SET ";
    $SQL .= "$param ";
    $SQL .= "WHERE $cond";
    $stmt = $conn->query($SQL);
    if ($stmt)
        return true;
    else
        return false;
}

function Delete($table, $cond): bool
{
    $conn = PDOConnectDB();
    $SQL = "DELETE FROM $table ";
    $SQL .= "WHERE $cond";
    $stmt = $conn->query($SQL);
    if ($stmt)
        return true;
    else
        return false;
}

function getName($GetFieldName, $TableName, $TableID)
{
    $conn = PDOConnectDB();
    $qry = "SELECT $GetFieldName FROM $TableName WHERE id='$TableID'";
    $stmt = $conn->query($qry);
    $row = $stmt->fetch();
    return $row[$GetFieldName];
}

function View_Menu(): array
{
    $conn = PDOConnectDB();
    $SQL = "SELECT MenuId, MenuLavel FROM menudefine ORDER BY MenuOrder";
    $rsSQL = $conn->query($SQL);

    $data = array();

    foreach ($rsSQL as $row) {
        $temp = array('MenuId' => $row->MenuId, 'MenuLavel' => $row->MenuLavel);
        $data[] = $temp;
    }
    return $data;
}

function ddlData($table, $value, $caption, $cond, $selct)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT DISTINCT $value,$caption FROM $table";
    $SQL .= ("$cond");
    foreach ($conn->query($SQL) as $row) {
        if ($row[$value] == $selct) {
            echo "<option value='" . $row[$value] . "' selected>" . $row[$caption] . "</option>";
        } else {
            echo "<option value='" . $row[$value] . "'>" . $row[$caption] . "</option>";
        }
    }
}

function ddlDataAgentToAgent($table, $value, $caption, $cond, $selct)
{
    $SQL = "SELECT DISTINCT $value,$caption FROM $table $cond";

    $rs = db_query($SQL, $cn);
    while ($row = db_fetch_array($rs)) {
        if ($row[$value] == $selct) {
            echo "<option value='" . $row[$value] . "' selected>" . $row[$caption] . "</option>";
        } else {
            echo "<option value='" . $row[$value] . "'>" . $row[$caption] . "</option>";
        }
    }
}

function CompanyIDToUserID($table, $value, $caption)
{
    $conn = PDOConnectDB();
    $SQL = "select DISTINCT $value, $caption, FullName FROM $table WHERE IsActive = '1'";
    foreach ($conn->query($SQL) as $row) {
        echo '<option value="' . $row['id'] . '">' . $row['UserName'] . ' | ' . substr($row['FullName'], 0, 102) . '</option>';
    }
}

function CompanyIDToSupervisor($table, $caption, $cond, $selct)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT $caption FROM $table $cond ";
    foreach ($conn->query($SQL) as $row) {
        if ($row[$caption] == $selct) {
            echo "<option value='" . $row[$caption] . "' selected>" . getName('UserName', 'userinfo', $row[$caption]) . "</option>";
        } else {
            echo "<option value='" . $row[$caption] . "'>" . getName('UserName', 'userinfo', $row[$caption]) . "</option>";
        }
    }
}

function companyName($table, $value, $caption, $cond)
{
    $conn = PDOConnectDB();
    $SQL = "select distinct $value,$caption from [$table] $cond";
    foreach ($conn->query($SQL) as $row) {
        echo "<option value='" . $row[$value] . "'>" . $row[$caption] . "</option>";
    }
}

function getCompanyName($CompanyID)
{

    $conn = PDOConnectDB();
    $SQL = "SELECT CompanyName FROM dataownercompany WHERE id='$CompanyID' ";
    $stmt = $conn->query($SQL);
    $row = $stmt->fetch();

    return $row['CompanyName'];
}

function ActivitycompanyName($table, $value, $caption, $selct)
{
    $conn = PDOConnectDB();
    $SQL = "select distinct $value,$caption from [$table] WHERE IsActive = '1'";

    foreach ($conn->query($SQL) as $row) {
        if ($row[$value] == $selct) {
            echo "<option value='" . $row[$value] . "' selected>" . $row[$caption] . "</option>";
        } else {
            echo "<option value='" . $row[$value] . "'>" . $row[$caption] . "</option>";
        }
    }
}

function ActivitycompanyNameforuser($table, $value, $caption)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT DISTINCT $value,$caption, FullName FROM [$table] WHERE IsActive = '1'";

    foreach ($conn->query($SQL) as $row) {
        echo '<option value="' . $row['id'] . '">' . $row['UserName'] . ' | ' . substr($row['FullName'], 0, 102) . '</option>';
    }
}

function DeleteAgentForm($table, $cond): bool
{
    $conn = PDOConnectDB();
    $SQL = "DELETE FROM $table ";
    $SQL .= "WHERE $cond";
    $stmt = $conn->query($SQL);
    if ($stmt)
        return true;
    else
        return false;
}

function isExist($table, $cond)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT count(*) AS tt FROM $table ";
    $SQL .= "WHERE $cond";
    $stmt = $conn->query($SQL);
    $row = $stmt->fetch();
    return $row['tt'];
}

function isMenuPermission($menuid, $roleid)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT Permission FROM rolemenu WHERE RoleId = '$roleid' AND MenuId = '$menuid'";
    foreach ($conn->query($SQL) as $row) {
        $permision = $row['Permission'];
    }
    return $permision;
}

function isMenuPermissionn($FormID)
{
    $conn = PDOConnectDB();
    $SQL = "SELECT FormID FROM assignformtoagent WHERE FormID='$FormID'";
    foreach ($conn->query($SQL) as $row) {
        $permision = $row['FormID'];
    }
    return $permision;
}

function Edit_MenuPer($roleid, $menuPer, $userid): bool
{
    $conn = PDOConnectDB();
    foreach ($menuPer as $menuid) {
        $SQL = "INSERT INTO rolemenu(RoleId, MenuId, Permission, CreatedBy, CreateDate) VALUES ('$roleid', '$menuid', 1, '$userid', getdate())";
        $conn->query($SQL);
    }
    return true;
}

function Edit_MenuPerr($menuPer, $FormGroupName, $CompanyID, $uID, $userid, $provisionEndDate): bool
{
    $conn = PDOConnectDB();
    foreach ($menuPer as $menuid) {
        $SQL = "INSERT INTO assignformtoagent(FormID, FormGroupId, CompanyID, UserID, ProvisionEndDate, CreatedBy, CreatedDate) 
        VALUES ('$menuid', '$FormGroupName', '$CompanyID', '$uID', '$provisionEndDate', '$userid', GETDATE())";
        $conn->query($SQL);
    }
    return true;
}

function getTotalPerPage(): int
{
    return 20;
}

$units = explode(' ', 'B KB MB GB TB PB');

function SendSMS($SendTo, $CompanyID, $Msg, $MsgCount)
{

    $conn = PDOConnectDB();
    $qry = "SELECT   id,UserName, NumberOfSMS, AuthToken FROM  SMSInfo where  loggedUserCompanyID='$CompanyID'";

    $stmt = $conn->query($qry);
    $row = $stmt->fetch();

    $InitialMSISDN = substr($SendTo, -10);
    $SendTo = "880" . $InitialMSISDN;

    $SendFrom = $row['UserName'];
    $AuthToken = $row['AuthToken'];
    $InMsgID = $SendTo . date('YmdHis') . rand(1, 99);
    $NumberOfSMS = $row['NumberOfSMS'];

    //http://116.212.108.50/BulkSMSAPI/BulkSMSExtAPI.php?SendFrom=eSMS&SendTo=8801913390332&InMSgID=8801913390332201909051224111&AuthToken=VGVzdFVzZXJ8QmxhaDQ=&Msg=This+is+a+Test+Message+from+Sender.

    if ($NumberOfSMS > 0) {

        $SMSSendingLink = "http://116.212.108.50/BulkSMSAPI/BulkSMSExtAPI.php?";
        $SMSURL = $SMSSendingLink . "SendFrom=$SendFrom&SendTo=$SendTo&InMSgID=$InMsgID&AuthToken=$AuthToken&Msg=$Msg";
        $SMSURL = str_replace(" ", "+", $SMSURL);

        // echo $SMSURL;exit;

        $UpdateSMSQuery = "UPDATE SMSInfo set NumberOfSMS=NumberOfSMS-'$MsgCount' where loggedUserCompanyID='$CompanyID' ";

        $conn->query($UpdateSMSQuery);
        file_get_contents($SMSURL);
    }
}

function Ratio($firstNumber, $SecondNumber): string
{
    // $Ratio = number_format((($firstNumber / $SecondNumber) * 100), 2);

    // return $Ratio . " %";


    // Prevent division by zero or invalid inputs
    if (empty($SecondNumber) || $SecondNumber == 0) {
        return "0 %";
    }
    $Ratio = ($firstNumber / $SecondNumber) * 100;
    return number_format($Ratio, 2) . " %";
}

function GetDataStatus($status): string
{
    if ($status == '0') {
        $DataStatus = 'Pending';
    } elseif ($status == '1') {
        $DataStatus = 'Approved';
    } elseif ($status == '2') {
        $DataStatus = 'Un-approved';
    } elseif ($status == '3') {
        $DataStatus = 'Deleted';
    }
    return $DataStatus;
}


function encryptValueLib($value)
{
// Store the cipher method
    $ciphering = "AES-128-CTR";

// Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

// Non-NULL Initialization Vector for encryption
    $encryption_iv = '1234567891011121';

// Store the encryption key
    $encryption_key = "solversDCpanel";

    return openssl_encrypt($value, $ciphering,
        $encryption_key, $options, $encryption_iv);
}

function decryptValueLib($value)
{
    // Store the cipher method
    $ciphering = "AES-128-CTR";

    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

// Non-NULL Initialization Vector for decryption
    $decryption_iv = '1234567891011121';

// Store the decryption key
    $decryption_key = "solversDCpanel";

// Use openssl_decrypt() function to decrypt the data
    return openssl_decrypt($value, $ciphering,
        $decryption_key, $options, $decryption_iv);
}


function updateListingHHNumber($formID, $recordID, $numberToUpdate): bool
{
    //$totalRecord = getValue('xformrecord', 'COUNT(id)', "id=$recordID");
    $recordStatus = getValue('xformrecord', 'IsApproved', "id=$recordID");
    $recordDataName = getValue('xformrecord', 'DataName', "id=$recordID");

    $contents = explode('_', $recordDataName);
    $existingHHNo = end($contents);
    $existingString = "HH_$existingHHNo";

    $newHHString = "HH_$numberToUpdate";

    $newDataName = str_replace($existingString, $newHHString, $recordDataName);

    $param = "SampleHHNo = $numberToUpdate, DataName = '$newDataName'";

    $condForXFormRecord = "id = $recordID";
    $tableForXFormRecord = 'xformrecord';

    $condForMasterData = "XFormRecordId = $recordID";
    if ($recordStatus == 0) {
        //echo "Ready to update pending table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_Pending';
    } elseif ($recordStatus == 1) {
        //echo "Ready to update approve table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_Approved';
    } elseif ($recordStatus == 2) {
        //echo "Ready to update un-approve table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_UnApproved';
    }

    if (Edit($tableForXFormRecord, $param, $condForXFormRecord) && Edit($tableForMasterData, $param, $condForMasterData)) {
        $msg = true;
    } else {
        $msg = false;
    }
    return $msg;

}

function updateListingHHNumberNew($recordID, $numberToUpdate, $tableForMasterData, $ColumnNameToUpdate): bool
{
    //$totalRecord = getValue('xformrecord', 'COUNT(id)', "id=$recordID");
    //$recordStatus = getValue('xformrecord', 'IsApproved', "id=$recordID");
    $recordDataName = getValue('xformrecord', 'DataName', "id=$recordID");

    $contents = explode('_', $recordDataName);
    $existingHHNo = end($contents);
    $existingString = "HH_$existingHHNo";

    $newHHString = "HH_$numberToUpdate";

    $newDataName = str_replace($existingString, $newHHString, $recordDataName);
    $param = "SampleHHNo = $numberToUpdate, DataName = '$newDataName'";

    $condForXFormRecord = "id = $recordID";
    $tableForXFormRecord = 'xformrecord';

    $condForMasterData = "XFormRecordId = $recordID";
    /*if ($recordStatus == 0) {
        //echo "Ready to update pending table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_Pending';
    } elseif ($recordStatus == 1) {
        //echo "Ready to update approve table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_Approved';
    } elseif ($recordStatus == 2) {
        //echo "Ready to update un-approve table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_UnApproved';
    }*/

    if (Edit($tableForXFormRecord, $param, $condForXFormRecord) && Edit($tableForMasterData, $param, $condForMasterData)) {
        $par = "ColumnValue = $numberToUpdate";
        $cond = "XFormRecordId = $recordID and ColumnName='$ColumnNameToUpdate'";

        if (Edit($tableForMasterData, $par, $cond)) {
            $msg = true;
        } else {
            $msg = false;
        }
    } else {
        $msg = false;
    }
    return $msg;

}

function updatePSUNumber($recordID, $numberToUpdate): bool
{
    //$totalRecord = getValue('xformrecord', 'COUNT(id)', "id=$recordID");
    $recordStatus = getValue('xformrecord', 'IsApproved', "id=$recordID");
    $recordDataName = getValue('xformrecord', 'DataName', "id=$recordID");

    $contents = explode('_', $recordDataName);
    $existingHHNo = end($contents);
    $existingFormName = $contents[0];
    //$existingString = "HH_$existingHHNo";

    //$newHHString = "HH_$numberToUpdate";
    $newDataName = $existingFormName . '_PSU_' . $numberToUpdate . '_HH_' . $existingHHNo;

    //exit();

    //$newDataName = str_replace($existingString, $newHHString, $recordDataName);
    $param = "PSU = $numberToUpdate, DataName = '$newDataName'";

    $condForXFormRecord = "id = $recordID";
    $tableForXFormRecord = 'xformrecord';

    $condForMasterData = "XFormRecordId = $recordID";
    if ($recordStatus == 0) {
        //echo "Ready to update pending table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_Pending';
    } elseif ($recordStatus == 1) {
        //echo "Ready to update approve table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_Approved';
    } elseif ($recordStatus == 2) {
        //echo "Ready to update un-approve table and $existingString AND new DataName: $newDataName";
        $tableForMasterData = 'masterdatarecord_UnApproved';
    }

    if (Edit($tableForXFormRecord, $param, $condForXFormRecord) && Edit($tableForMasterData, $param, $condForMasterData)) {
        $par = "ColumnValue = $numberToUpdate";
        $cond = "XFormRecordId = $recordID and ColumnName='PSU'";

        if (Edit($tableForMasterData, $par, $cond)) {
            $msg = true;
        } else {
            $msg = false;
        }
    } else {
        $msg = false;
    }
    return $msg;

}