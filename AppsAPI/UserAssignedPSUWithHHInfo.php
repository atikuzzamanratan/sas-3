<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include '../Config/config.php';

//https://sasbbsbd.com/AppsAPI/UserAssignedPSUWithHHInfo.php?userid=69&psuNo=801

$UserId = $_REQUEST['userid'];
$PSUNo = $_REQUEST['psuNo'];

$query = "
IF object_id('tempdb..##TempSMData') is not null
	BEGIN
		drop table ##TempSMData;
	END
CREATE TABLE ##TempSMData(MainHHNo INT, SampleHHNo VARCHAR(50), PSUNo VARCHAR(50), HHNameNo nvarchar(max), MobileNo nvarchar(50));

INSERT ##TempSMData (MainHHNo, SampleHHNo, PSUNo, HHNameNo, MobileNo)
SELECT CAST(MainHHNumber AS INT), SampleHHNumber, PSU, RTRIM(LTRIM(HHeadName)) HHeadName, MobileNumber FROM SampleMapping WHERE UserID = '$UserId' AND PSU = '$PSUNo' 
AND MainHHNumber NOT IN (SELECT SampleHHNo FROM xformrecord WHERE FormId=$formIdMainData AND UserID = '$UserId' AND PSU = '$PSUNo') ORDER BY id ASC;";
$app->getDBConnection()->Query($query);

$query2 = "Declare @Numbers AS nvarchar(max);
SELECT @Numbers = COALESCE(@Numbers + '###', '') + CONCAT('SN_', MainHHNo, '_LN_', SampleHHNo, '_PSU_', PSUNo, '_HH_', HHNameNo, '_MOB_', MobileNo) 
FROM ##TempSMData ORDER BY MainHHNo ASC; 
select @Numbers as InstNumbers";

$psuList = $app->getDBConnection()->query($query2);

foreach ($psuList as $row) {
    $UserAssignedPSUs = $row->InstNumbers;
    echo $UserAssignedPSUs;
}

