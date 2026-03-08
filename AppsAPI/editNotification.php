<?php
error_reporting(E_ALL);
include "../Components/header-includes.php";

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$msg = xss_clean($_REQUEST['msg']);


?>

<div class="inner-wrapper">
    <section role="main" class="content-body">

        <!-- start: page -->
        <div class="row">
            <div class="col-lg-2 mb-0"></div>
            <div class="col-lg-8 mb-0">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Edit Data Form</h2>
                    </header>
                    <div class="card-body">
                        <form name="editForm" class="form-horizontal form-bordered" action="" method="post">

                            <div class="col-lg-9">
								<?=$msg?>
							</div>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <!--<button type="button" class="btn btn-secondary"
                                                onclick="window.open('', '_self', ''); window.close();">Close
                                        </button>-->
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
            </div>
            <div class="col-lg-2 mb-0"></div>
        </div>
        <!-- end: page -->
    </section>
</div>
