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
            <div class="card-title"><h4>Help & Support</h4></div>
        </header>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>ROBI Help Line</th>
                </tr>
                <tr>
                    <td>
						<a href="tel:+8801880004420"><i class="bi bi-phone"></i>ROBI Govt. Project Hotline Number<br>+8801880004420</a> 
						<br/>[যখন ফোন করবেন তখন বলবেন `আপনি বি বি এস থেকে ফোন করছেন` বলে সমস্যার কথা জানাবেন]
                    </td>
                </tr>
                <tr>
                    <td>ডাটা (MB) চেক *3#</td>
                </tr>
                <tr>
                    <td>ব্যলান্স চেক/বকেয়া বিল *1#</td>
                </tr>
                <tr>
                    <th>Project Head quarter</th>
                </tr>
                <tr>
                    <td><a href="tel:+880255007045"><i class="bi bi-phone"></i>
                            Muhammad Rafiqul Islam<br>
                            Project Director,<br>
                            +880255007045</a>
                    </td>
                </tr>
                <tr>
                    <td><a href="tel:+8801639818744"><i class="bi bi-phone"></i>
                            Md. Mostafizur Rahman<br>
                            Project Staff,<br>
                            +8801639818744</a>
                    </td>
                </tr>
                <tr>
                    <th>Solvers Support (9:15 A.M to 5:00 P.M)</th>
                </tr>
                <tr>
                    <td><a href="tel:+8801720540859"><i class="bi bi-phone"></i>1st Line Support
                            +8801720540859</a>
                    </td>
                </tr>
                <tr>
                    <td><a href="tel:+8801810096285"><i class="bi bi-phone"></i>2nd Line Support
                            +8801810096285</a>
                    </td>
                </tr>
				<tr>
                    <td><a href="tel:+8801329684382"><i class="bi bi-phone"></i>3rd Line Support
                            +8801329684382</a>
                    </td>
                </tr>
				<tr>
                    <td><a href="tel:+8801841190330"><i class="bi bi-phone"></i>4th Line Support
                            +8801841190330</a>
                    </td>
                </tr>
            </table>
        </div>
    </section>


<?php
include_once "../Components/footer-includes.php";
?>