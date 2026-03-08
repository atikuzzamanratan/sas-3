<?php
// $cn = ConnectDB();

$qryString = "SELECT nt.id, nt.FromUserID, ui.UserName, ui.FullName, nt.ToUserID, nt.Notification, nt.Status, nt.DataEntryDate, nt.NotificationReadTime
FROM Notification nt JOIN userinfo ui ON nt.FromUserID = ui.id
WHERE nt.ToUserID = ? AND Status = ? AND nt.CompanyID = ?
ORDER BY nt.DataEntryDate DESC";

$notifications = $app->getDBConnection()->fetchAll($qryString, $loggedUserID, 0, $loggedUserCompanyID);
$CountNotification = count($notifications);
?>

<!doctype html>
<html class="fixed has-top-menu" lang="">
<head>

    <!-- Basic -->
    <meta charset="UTF-8">

    <title><?php echo $projectName; ?></title>

    <meta name="keywords" content="Data Collector Solution"/>
    <meta name="description" content="Solvers Data Collector Solution">
    <meta name="author" content="solversbd.com">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

    <?php
    include_once 'header-includes.php';
    ?>

</head>
<body>
<section class="body">

    <!-- start: header -->
    <header class="header header-nav-menu header-nav-top-line">
        <div class="logo-container">
            <!-- <a href="<?php echo $baseURL; ?>" class="logo">
                <img src="../img/cps-logo-web.png" width="125" alt="CPS Admin"/>
            </a> -->

            <a href="<?php echo $baseURL; ?>" class="logo">
                <img src="../img/sas_logo.png" width="50" alt="SAS Admin"/>
            </a>

            <button class="btn header-btn-collapse-nav d-lg-none" data-bs-toggle="collapse"
                    data-bs-target=".header-nav">
                <i class="fas fa-bars"></i>
            </button>

            <!-- start: header nav menu -->
            <div class="header-nav collapse">
                <div class="header-nav-main header-nav-main-effect-1 header-nav-main-sub-effect-1 header-nav-main-square">
                    <nav>
                        <ul class="nav nav-pills" id="mainNav">
                            <?php
                            include 'left-menu.php';
                            ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <!-- end: header nav menu -->
        </div>

        <!-- start: search & user box -->
        <div class="header-right">
            <span class="separator"></span>

            <ul class="notifications">
                <li>
                    <a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
                        <i class="bx bx-bell" title="Unread message"></i>
                        <?php
                        if ($CountNotification > 0) {
                            ?>
                            <span class="badge"><?php echo $CountNotification; ?></span>
                            <?php
                        }
                        ?>
                    </a>

                    <div class="dropdown-menu notification-menu" style="width: 300px;">
                        <div class="notification-title">
                            <span class="float-end badge badge-default"><?php echo $CountNotification; ?></span>
                            Unread messages
                        </div>

                        <div class="scrollable" data-plugin-scrollable style="height: 350px; width: 350px;">
                            <div class="scrollable-content">
                                <ul>
                                    <?php
                                    foreach ($notifications as $row) {
                                        $Notification = $row->Notification;
                                        $ReceivedDate = $row->DataEntryDate;
                                        $Sender = $row->UserName;
                                        $FullName = $row->FullName;
                                        $Sender = "$FullName ($Sender)";
                                        $noticeId = $row->id;
                                        ?>
                                        <li>
                                            <a href="#modalAnim"
                                               class="clearfix mb-1 mt-1 me-1 modal-with-zoom-anim ws-normal"
                                               data-toggle="modal" data-target="#modalAnim" style="padding: 10px"
                                               onclick="updateNoticeStatus('<?php echo $noticeId; ?>')">
                                                <div class="image" style="padding-right: 10px">
                                                    <i class="fas fa-bell" style="width: 2px;height: 2px"></i>
                                                </div>
                                                <input type="hidden" id="spnNoticeId" name="spnNoticeId"
                                                       value="<?php echo $noticeId; ?>">
                                                <input type="hidden" id="spnNoticeSender" name="spnNoticeSender"
                                                       value="<?php echo $Sender; ?>">
                                                <input type="hidden" id="spnNoticeText" name="spnNoticeText"
                                                       value="<?php echo $Notification; ?>">
                                                <input type="hidden" id="spnNoticeSendDate" name="spnNoticeSendDate"
                                                       value="<?php echo $ReceivedDate; ?>">

                                                <span class="card-subtitle"
                                                      style="color: #6b0392">Sender: <?php echo $Sender; ?></span><br>
                                                <span class="message"
                                                      style="color: #0b0b0b; font-size: small;"><?php echo substr($Notification, 0, 100); ?></span>
                                                <span class="message"><?php echo date("d/m/Y h:i A", strtotime($ReceivedDate)); ?></span>
                                            </a>
                                            <!-- Modal Animation -->
                                            <div id="modalAnim"
                                                 class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
                                                <section class="card">
                                                    <header class="card-header">
                                                        <h2 class="card-title">Message Detail</h2>
                                                    </header>
                                                    <div class="card-body">
                                                        <div class="modal-wrapper">
                                                            <div class="card-title" style="color: #6b0392">
                                                                Sender: <?php echo $Sender; ?></div>
                                                            <br>
                                                            <div class="card-subtitle" style="color: black">Receive
                                                                time: <?php echo date("d/m/Y h:i A", strtotime($ReceivedDate)); ?></div>
                                                            <br>
                                                            <div class="modal-content">
                                                                <p class="mb-0"
                                                                   style="font-size: medium; color: #0b0b0b"><?php echo $Notification; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <footer class="card-footer">
                                                        <div class="row">
                                                            <div class="col-md-12 text-end">
                                                                <button class="btn btn-primary modal-confirm"
                                                                        onclick="location.href = 'index.php?parent=SendNotification';">
                                                                    Reply
                                                                </button>
                                                                <button class="btn btn-default modal-dismiss"
                                                                        onclick="location.reload()">OK
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </footer>
                                                </section>
                                            </div>
                                        </li>
                                        <hr>
                                        <?php
                                    }
                                    ?>
                                </ul>

                                <hr/>
                                <script type="text/javascript">
                                    function updateNoticeStatus(noticeid, data) {
                                        //alert(noticeid);
                                        $.ajax({
                                            url: "Components/update-notice-status.php",
                                            method: "GET",
                                            datatype: "json",
                                            data: {
                                                noticeid: noticeid
                                            },
                                            success: function (response) {
                                                //alert(response);
                                                //window.location.reload();
                                            }
                                        });
                                    }
                                </script>

                            </div>
                        </div>
                        <div class="text-end" style="padding: 10px">
                            <a href="<?php echo get_base_url(); ?>index.php?parent=ViewNotification"
                               class="view-more" style="color: blue">View All</a>
                        </div>
                    </div>
                </li>
            </ul>

            <script type="text/javascript">
                $("#viewDetailMsg").click(function () {
                    let noticeId = $("#spnNoticeId").val();
                    let noticeFrom = $("#spnNoticeSender").val();
                    let noticeText = $("#spnNoticeText").val();
                    let noticeSendDate = $("#spnNoticeSendDate").val();
                    $("#modal_body").html(text);
                });
            </script>

            <span class="separator"></span>

            <div id="userbox" class="userbox">
                <a href="#" data-bs-toggle="dropdown">
                    <figure class="profile-picture">
                        <!-- <img src="../img/cps_logo.png" alt="Joseph Doe" class="rounded-circle"
                             data-lock-picture="../img/cps_logo.png"/> -->

                        <img src="../img/sas_logo.png" alt="Joseph Doe" class="rounded-circle"
                             data-lock-picture="../img/sas_logo.png"/>

                    </figure>
                    <div class="profile-info" data-lock-name="<?php echo $loggedUserName; ?>" data-lock-email="#">
                        <span class="name"><?php echo $loggedUserName; ?></span>
                        <span class="role"><?php echo $loggedUserFullName; ?></span>
                    </div>

                    <i class="fa custom-caret"></i>
                </a>

                <div class="dropdown-menu">
                    <ul class="list-unstyled">
                        <li class="divider"></li>
                        <li>
                            <a role="menuitem" tabindex="-1"
                               href="<?php echo $baseURL . 'index.php?parent=password' ?>"><i
                                        class="bx bx-lock-alt"></i> Change Password</a>
                        </li>
                        <li>
                            <a role="menuitem" tabindex="-1" href="<?php echo $baseURL . 'index.php?parent=logout' ?>">
                                <i class="bx bx-power-off"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- end: search & user box -->
    </header>
    <!-- end: header -->