
<?php

require_once "../Config/config.php";

if (!empty($_POST)) {
//if (1) {

//$UserName = $_GET['username'];
//$Password = $_GET['password'];

$UserName = $_POST['username'];
$Password = $_POST['password'];


$cn = ConnectDB();

 $UserQuery = "SELECT id,UserName FROM [dbo].[userinfo] WHERE UserName='$UserName' AND Password='$Password' AND IsActive = '1'";

$UserExists = db_fetch_array(db_query($UserQuery, $cn));

$UserID = $UserExists['id'];
$UserNameVal = $UserExists['UserName'];

//print_r($UserExists);

if ($UserExists['id'] > 0) {
    $qry1 = "UPDATE userinfo SET IsOnline='1' Where id = $UserID";
//         exit;
    $rr = db_query($qry1, $cn);
    $response["success"] = $UserID;
    $response["message"] = "Hello ".$UserNameVal.". Your Login is successful!";
}
else {

    $response["success"] = 0;
    $response["message"] = "Login Failed!Invalid User Name or Password!";
}



die(json_encode($response));

//$val["success"] = 0;
//$val["message"]=$RequestingPara;
//die(json_encode($val));

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

