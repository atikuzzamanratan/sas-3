<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

$UserId = $app->cleanInput($_REQUEST['userid']);

$query = "Declare @Numbers AS varchar(500)
SELECT @Numbers = COALESCE(@Numbers + ', ', '') + CONVERT(varchar(500),PSU) FROM PSUList where PSUUserID = ?
select @Numbers as PSUNumbers";

$psuList = $app->getDBConnection()->query($query, $UserId);

foreach ($psuList as $row) {
    $UserAssignedPSUs = $row->PSUNumbers;
    echo '{"success": "' . $UserAssignedPSUs . '"}';
}
