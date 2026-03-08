<?php

require_once "../Config/config.php";

if (!empty($_POST)) {
$UserName = $_POST['username'];
$Password = $_POST['password'];

$cn = ConnectDB();

$UserQuery = "SELECT id,UserName,Password, (SELECT TOP (1) id FROM PSUList WHERE PSUUserID IN(SELECT id FROM userinfo WHERE UserName='$UserName')) as PSUUserID FROM [dbo].[userinfo] WHERE UserName='$UserName' AND IsActive = '1'";

$UserExists = db_fetch_array(db_query($UserQuery, $cn));

$UserID = $UserExists['id'];
$UserNameVal = $UserExists['UserName'];
$DBPassword = $UserExists['Password'];
$PSUUserID = $UserExists['PSUUserID'];

if ($UserExists['id'] > 0) {
    if($DBPassword==$Password){
        if(!is_null($PSUUserID)){
            $qry1 = "UPDATE userinfo SET IsOnline='1' Where id = $UserID";
                $rr = db_query($qry1, $cn);
                $response["success"] = $UserID;
                $response["message"] = "Hello ".$UserNameVal.". Your Login is successful!";
        }else{
            $response["success"] = 0;
            $response["message"] = "Login Failed! You are not assigned to any PSU!";
        }
        
    }else{
            $response["success"] = 0;
            $response["message"] = "Login Failed! Invalid Password!";
    }
}
else {

    $response["success"] = 0;
    $response["message"] = "Login Failed! Invalid User Name or User not active!";
}



die(json_encode($response));

}else {
?>
            <h1>Login</h1> 
            <form action="AppAuthChecker.php" method="post"> 
                UserName:<br /> 
                <input type="text" name="username" placeholder="username" /> 
                <br /><br /> 
                Password:<br /> 
                <input type="Password" name="password" placeholder="password" value="" /> 
                <br /><br /> 
                <input type="submit" value="Login" /> 
            </form> 
		
  <?php
}
?>

