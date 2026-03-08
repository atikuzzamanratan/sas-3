<?php
session_start();

error_reporting(E_ALL);
set_time_limit(0);

require '../vendor/autoload.php';
include "../Config/config.php";
include "../Lib/lib.php";

use Solvers\Dsql\Application;

$app = new Application();

$baseURL = get_base_url();

$loggedUserID = $app->cleanInput($_SESSION['UserID']);
$loggedUserName = $app->cleanInput($_SESSION['User']);
$loggedUserFullName = $app->cleanInput($_SESSION['FullName']);
$loggedUserCompanyID = $app->cleanInput($_SESSION['loggedUserCompanyID']);
$loggedUserCompanyName = getValue("dataownercompany", "CompanyName", "id='$loggedUserCompanyID'");

$MenuLebel = "DB Helper";
include_once '../Components/header.php';
?>

<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $MenuLebel; ?></h2>

            <?php include_once '../Components/header-home-button.php'; ?>
        </header>
		<div class="row">
            <div class="col-lg-12 mb-0">
                <section class="card">
                    <div class="card-body">
                        <form class="form-horizontal form-bordered" action="" method="post">
                            <div class="form-group row pb-3">
                                <label class="col-lg-3 control-label text-sm-end pt-2">Your Query<span
                                        class="required">*</span></label>
                                <div class="col-lg-6">
                                    <textarea class="form-control populate" name="query" id="query" rows="15"></textarea>
                                </div>
                            </div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary" name="show" type="submit" id="show"
                                            value="Show">
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
                <?php
                if ($_REQUEST['show'] === 'Show') {
                    $query = $_REQUEST['query'];

					$app->getDBConnection()->fetchAll($query);
                ?>

                        
                <?php
                    }
                ?>
            </div>
        </div>
	</section>
</div>

<?php
include_once '../Components/footer.php';