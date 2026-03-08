<?PHP
session_start();

error_reporting(E_ALL);
set_time_limit(0);

require 'vendor/autoload.php';
include "Config/config.php";
include "Lib/lib.php";

use Solvers\Dsql\Application;

$app = new Application();

$baseURL = get_base_url();

$loggedUserID = $app->cleanInput($_SESSION['UserID']);
$loggedUserName = $app->cleanInput($_SESSION['User']);
$loggedUserFullName = $app->cleanInput($_SESSION['FullName']);
$loggedUserCompanyID = $app->cleanInput($_SESSION['loggedUserCompanyID']);
$loggedUserCompanyName = getValue("dataownercompany", "CompanyName", "id='$loggedUserCompanyID'");


if ($_GET['parent'] != '') {
    $parent = $app->cleanInput($_GET['parent']);
    $MenuLebel = getValue("menudefine", "MenuLavel", "MenuId='$parent'");
}

if ($parent === "logout")
    $_SESSION["Login"] = "False";

if (isset($_SESSION['Login']) && $_SESSION['Login'] === "True") {
    include_once 'Components/header.php';

    if (!empty($parent)) {
        include_once getURL($parent);
        if ($parent === "home") {
            include_once "welcome.php";
        } else if ($parent === "password") {
            include_once "Authentication/change-password.php";
        }
    } else {
        include_once "welcome.php";
    }

} else {
    include_once "Authentication/signin.php";
}

include_once 'Components/footer.php';


