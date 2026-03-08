<?php
require 'vendor/autoload.php';

/* START code to execute for password encryption */

$dsn = "sqlsrv:server=127.0.01 ; Database=DataCollector";
$user = "sa";
$password = "bmWfjg88";

/*$dsn = env('DB_URL');
$loggedUserName = env('DB_USERNAME');
$password = env('DB_PASSWORD');*/

echo $dsn;
exit();
$database = new Nette\Database\Connection($dsn, $user, $password);

$users = $database->query('select id, Password, enc_passw from userinfo where enc_passw = NULL');


foreach ($users as $user){
    $result = $database->query('UPDATE userinfo SET', [
        'enc_passw' => password_hash($user->Password,PASSWORD_DEFAULT)
    ], 'WHERE id = ? && enc_passw = NULL', $user->id);

    echo $user->Password . " " . $user->enc_passw . "<br/>";

    //echo $result . "<br/>";
}



/* END code to execute for password encryption */
//-----------------------------------------------------------------------------------------------

////// The below scripts will be blocked when run the script for Password Encryption/////

/*//Password verification process

// The hashed password retrieved from database
$hash = "$2y$10\$BC/4SjPtDqaVq6vFMR3aDe.xL24FjZEtgojBKhV8P0VFsl53Bl5I6";

// Verify the hash against the password entered
$verify = password_verify('@datafalgun', $hash);

// Print the result depending if they match
if ($verify) {
    echo 'Password Verified!';
} else {
    echo 'Incorrect Password!';
}*/