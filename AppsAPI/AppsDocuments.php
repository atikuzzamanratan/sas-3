<?php
require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";

$AuthToken = $_GET['authToken'];

if ($AuthToken != $AuthTokenValue) {
    echo $unAuthorizedMsg;
    exit();
}

include_once '../Components/header-includes.php';
?>

    <section class="card">
        <header class="card-header">
            <div class="card-title"><h4>Useful Documents</h4></div>
        </header>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <td><a href="../Documents/SAS-Mobile-Application-Manual-v3"><i class="bi bi-phone"></i>মোবাইল এপ্লিকেশন ম্যানুয়াল</a></td>
                </tr>
                <tr>
                    <td><a href="../Documents/SAS-Listing-Form.pdf"><i class="bi bi-phone"></i>লিস্টিং প্রশ্নপত্র</a>
                    </td>
                </tr>
                <tr>
                    <td><a href="../Documents/SAS-Main-Survey-Form.pdf"><i class="bi bi-phone"></i>মূল সার্ভে প্রশ্নপত্র</a>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </section>


<?php
include_once "../Components/footer-includes.php";
?>