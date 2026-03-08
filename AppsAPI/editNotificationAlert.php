<?php
require_once "../Config/config.php";
require_once "../Lib/lib.php";
//print_r($_REQUEST);exit;
// $FormId = $_REQUEST["FormId"];
$msg = $_REQUEST["msg"];
//MsgBox($msg);
?>

<link rel="stylesheet" href="../assets/css/styles.css?=121">
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600' rel='stylesheet' type='text/css'>

<link href='../assets/demo/variations/default.css' rel='stylesheet' type='text/css' media='all' id='styleswitcher'>
<link href='../assets/demo/variations/default.css' rel='stylesheet' type='text/css' media='all' id='headerswitcher'>

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries. Placeholdr.js enables the placeholder attribute -->
<!--[if lt IE 9]>
<link rel="stylesheet" href="../assets/css/ie8.css">
        <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/respond.js/1.1.0/respond.min.js"></script>
<script type="text/javascript" src="../assets/plugins/charts-flot/excanvas.min.js"></script>
<![endif]-->

<!-- The following CSS are included as plugins and can be removed if unused-->

<link rel='stylesheet' type='text/css' href='../assets/plugins/form-daterangepicker/daterangepicker-bs3.css' />
<link rel='stylesheet' type='text/css' href='../assets/plugins/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='../assets/plugins/form-markdown/css/bootstrap-markdown.min.css' />
<link rel='stylesheet' type='text/css' href='../assets/plugins/codeprettifier/prettify.css' />
<link rel='stylesheet' type='text/css' href='../assets/plugins/form-toggle/toggles.css' />


<div class="container">
    <div class="panel panel-sky">

        <div class="panel-heading">
            <h4>Edit Information</h4>
        </div>
        <div class="panel-body">
            <div class="panel-body collapse in">

                <style>
                    .container {
                        text-align: center;
                        /* border: 7px solid red; */
                        /* width: 300px;
                        height: 200px; */
                        /* padding-top: 100px; */
                    }

                    #Close {
                        /* font-size: 25px; */
                    }
                </style>

                <div class="form-group">
                    
                    <div class="col-sm-12">

                        <center>
                            <h1> <?php echo $msg; ?> </h1>
                        </center>

                    </div>
                </div>

                <div class="form-group">
                    
                    <div class="col-sm-12">

                        <h1><input class="btn btn-primary" name="Close" type="submit" id="Close" value="Close" onClick="window.close()"></h1>
                    </div>
                </div>




            </div>

        </div> <!-- row -->
    </div> <!-- container -->
</div> <!-- page-content -->


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<script>
    !window.jQuery && document.write(unescape('%3Cscript src="../assets/js/jquery-1.10.2.min.js"%3E%3C/script%3E'))
</script>
<script type="text/javascript">
    !window.jQuery.ui && document.write(unescape('%3Cscript src="../assets/js/jqueryui-1.10.3.min.js'))
</script>


<script type='text/javascript' src='../assets/js/jquery-1.10.2.min.js'></script>
<script type='text/javascript' src='../assets/js/jqueryui-1.10.3.min.js'></script>
<script type='text/javascript' src='../assets/js/bootstrap.min.js'></script>
<script type='text/javascript' src='../assets/js/enquire.js'></script>
<script type='text/javascript' src='../assets/js/jquery.cookie.js'></script>
<script type='text/javascript' src='../assets/js/jquery.nicescroll.min.js'></script>
<script type='text/javascript' src='../assets/plugins/codeprettifier/prettify.js'></script>
<script type='text/javascript' src='../assets/plugins/easypiechart/jquery.easypiechart.min.js'></script>
<script type='text/javascript' src='../assets/plugins/sparklines/jquery.sparklines.min.js'></script>
<script type='text/javascript' src='../assets/plugins/form-toggle/toggle.min.js'></script>
<script type='text/javascript' src='../assets/plugins/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript' src='../assets/plugins/form-daterangepicker/daterangepicker.min.js'></script>
<script type='text/javascript' src='../assets/plugins/form-daterangepicker/moment.min.js'></script>
<script type='text/javascript' src='../assets/plugins/charts-flot/jquery.flot.min.js'></script>
<script type='text/javascript' src='../assets/plugins/charts-flot/jquery.flot.resize.min.js'></script>
<script type='text/javascript' src='../assets/plugins/charts-flot/jquery.flot.orderBars.min.js'></script>
<script type='text/javascript' src='../assets/plugins/pulsate/jQuery.pulsate.min.js'></script>
<script type='text/javascript' src='../assets/demo/demo-index.js'></script>
<script type='text/javascript' src='../assets/js/placeholdr.js'></script>
<script type='text/javascript' src='../assets/js/application.js'></script>
<script type='text/javascript' src='../assets/demo/demo.js'></script>
<script language="JavaScript" src="../js/form_validatorv31.js" type="text/javascript"></script>
<script language="JavaScript" src="../js/global.js" type="text/javascript"></script>