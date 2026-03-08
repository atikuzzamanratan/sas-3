<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

$UserId = $app->cleanInput($_REQUEST['userid']);

/*$query = "Declare @Numbers AS varchar(500)
SELECT @Numbers = COALESCE(@Numbers + ', ', '') + CONVERT(varchar(500),PSU) FROM PSUList where PSUUserID = ?
select @Numbers as PSUNumbers";
$psuList = $app->getDBConnection()->query($query, $UserId);*/


$query = "SELECT
    STRING_AGG(CAST(PSU AS VARCHAR(10)), ', ') 
        WITHIN GROUP (ORDER BY PSU) AS PSUNumbers
FROM PSUList
WHERE PSUUserID = ?
  AND (
        -- Keep all non-4-digit PSUs
        PSU < 1000 OR PSU > 9999
        -- OR keep 4-digit PSUs that this user (140) has NOT entered yet
        OR NOT EXISTS (
            SELECT 1 
            FROM XFormRecord xr 
            WHERE xr.PSU = PSUList.PSU 
              AND xr.UserID = ?   -- ← Fixed: hardcode or use variable
        )
      );";
$psuList = $app->getDBConnection()->query($query, $UserId, $UserId);

foreach ($psuList as $row) {
    $UserAssignedPSUs = $row->PSUNumbers;
    echo $UserAssignedPSUs;
}