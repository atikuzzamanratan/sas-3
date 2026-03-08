<?php
error_reporting(E_ALL);
include "../Components/header-includes.php";

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$xID = xss_clean($_REQUEST['xID']);

$qryDataValue = "SELECT TOP(1) UserID, FormId, DataName, FormGroupId, CompanyId, EntryDate, IsApproved, PSU FROM masterdatarecord_UnApproved WHERE XFormRecordId = ? AND IsApproved = 2";
$rowDataValue = $app->getDBConnection()->fetch($qryDataValue, $xID);

$UserID = $rowDataValue->UserID;
$FormId = $rowDataValue->FormId;
$DataName = $rowDataValue->DataName;
$FormGroupId = $rowDataValue->FormGroupId;
$CompanyId = $rowDataValue->CompanyId;
$EntryDate = $rowDataValue->EntryDate;
$IsApproved = $rowDataValue->IsApproved;
$PSU = $rowDataValue->PSU;
if (!$FormId) {
    $FormId = 0;
}

//$qry = "EXEC [dbo].[EditDetailDataWithLabel_SpecialEdit_New_for_test] $FormId, $xID";
$qry = "EXEC [dbo].[EditDetailDataWithLabel_SpecialEdit] $FormId, $xID";

//$rsResult = $conn->query($qry);
$rsResult = $app->getDBConnection()->fetchAll($qry);

$previousValueArray = array();
$previousNodeArray = array();
$newValueArray = array();
?>

<script src="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css" rel="stylesheet">

<div class="inner-wrapper">
    <section role="main" class="content-body">

        <!-- start: page -->
        <div class="row">
            <div class="col-lg-2 mb-0"></div>
            <div class="col-lg-8 mb-0">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Edit All Information</h2>
                    </header>
                    <div class="card-body">
                        <form name="editForm" class="form-horizontal form-bordered" action="" method="post">
                            <!--<input type="hidden" name="updateAction" value="Update"/>-->
                            <?php
                            if ($rsResult) {
                                ?>
                                <div class="form-group row pb-4">
                                    <label class="col-lg-9 control-label text-lg-start pt-2"><b>Record ID
                                            : <?php echo $xID; ?></b></label>
                                </div>
                                <?php
                                foreach ($rsResult as $row) {
                                    $Id = $row->ID;
                                    $MasterDataID = $row->ColumnRecordID;
                                    $ColumnName = $row->ColumnNameOriginal;
                                    $isEditable = getValue('xformcolumnname', 'IsEditable', "FormId = $FormId AND ColumnName = '$ColumnName'");
                                    if ($isEditable == 0) {
                                        $isReadonly = ' readonly style="background-color:#f8f9f9;"';
                                        //$isDisabled = ' disabled="true"';
                                        $isDisabled = ' style="pointer-events: none; background-color:#f8f9f9;"';
                                    } else {
                                        $isReadonly = '';
                                        $isDisabled = '';
                                    }
                                    $ColumnNameModified = $row->ColumnLabelModified;
                                    $ColumnValue = $row->ColumnValueOriginal;
                                    $ColumnValueLabel = $row->ColumnValueModified;
                                    $ColumnDataType = $row->ColumnDataType;
                                    $ColumnChoiceValueAll = $row->ColumnChoiceValueAll;
                                    $ColumnChoiceLabelAll = $row->ColumnChoiceLabelAll;
                                    $PassingValue = $ColumnName . "|" . $Id . "|" . $ColumnDataType . "|" . $MasterDataID;
                                    if (!is_null($ColumnValue)) {
                                        $previousNodeArray[] = $ColumnName;
                                        $previousValueArray[] = $ColumnValue;
                                    }
                                    ?>
                                    <div class="form-group row pb-3">
                                        <label class="col-lg-9 control-label text-lg-start pt-2"
                                               for="" <?php if ($row->Comments != '' && $row->IsCorrected) {
                                            echo 'style="display: inline !important;width: auto;background-color: #fff192;"';
                                        } ?> ><b><?php echo $ColumnNameModified; ?></b></label>
                                        <?php
                                        if ($row->Comments != '' && $row->IsCorrected) {
                                            echo '<img class="jBoxTip imgComments" src="../img/Comments.png" border=0 style="cursor: pointer;width: 90px;height:60px;" width="90px" height="60px" title=\'' . $row->Comments . '\'>';
                                            echo '<img class="correctIdentifier" src="../img/Correct.png" style="width: 90px;height:50px;display:none;" border="0" width="90px" height="50px">';
                                        }
                                        ?>
                                        <?php
                                        if (trim($ColumnDataType) == 'select_one') {
                                            $ColumnChoiceValueAllArray = explode('|', $ColumnChoiceValueAll);
                                            $ColumnChoiceLabelAllArray = explode('|', $ColumnChoiceLabelAll);
                                            ?>
                                            <select name="<?php echo $PassingValue; ?>" id="UserName"
                                                    class="form-control" <?php echo $isDisabled; ?>>
                                                <?php
                                                echo "<option value='NULL'>select answer</option>";

                                                $i = 0;

                                                if (is_null($ColumnValue)) {
                                                    echo "<option value='' selected>select answer</option>";
                                                }/*else{
                                                    echo "<option value=''>select answer</option>";
                                                }*/
                                                foreach ($ColumnChoiceValueAllArray as $ColumnChoiceValueData) {

                                                    if ($ColumnValue == $ColumnChoiceValueData) {
                                                        echo "<option value=" . $ColumnChoiceValueData . " selected>" . $ColumnChoiceLabelAllArray[$i] . "</option>";
                                                    } else {
                                                        echo "<option value=" . $ColumnChoiceValueData . " >" . $ColumnChoiceLabelAllArray[$i] . "</option>";
                                                    }

                                                    $i++;
                                                }
                                                ?>
                                            </select>
                                            <?php
                                        } elseif (trim($ColumnDataType) == 'select_multiple') {
                                            $ColumnChoiceValueAllArray = explode('|', $ColumnChoiceValueAll);
                                            $ColumnChoiceLabelAllArray = explode('|', $ColumnChoiceLabelAll);
                                            $ColumnValueArrayMultiple = explode(' ', $ColumnValue);
                                            ?>
                                            <select name="<?php echo $PassingValue; ?>[]" id="UserName"
                                                    class="form-control" multiple <?php echo $isDisabled; ?>>
                                                <?php

                                                $j = 0;
                                                if (is_null($ColumnValue)) {
                                                    echo "<option value='' selected>select answer</option>";
                                                }else{
                                                    echo "<option value='NULL'>select answer</option>";
                                                }
                                                foreach ($ColumnChoiceValueAllArray as $ColumnChoiceValueData) {
                                                    if (in_array($ColumnChoiceValueData, $ColumnValueArrayMultiple)) {
                                                        echo "<option value=" . $ColumnChoiceValueData . " selected>" . $ColumnChoiceLabelAllArray[$j] . "</option>";
                                                    } else {
                                                        echo "<option value=" . $ColumnChoiceValueData . ">" . $ColumnChoiceLabelAllArray[$j] . "</option>";
                                                    }

                                                    $j++;
                                                }
                                                ?>
                                            </select>
                                            <?php
                                        } else {
                                            ?>
                                            <input class="form-control" name="<?PHP echo $PassingValue; ?>" type="text"
                                                   id="UserName"
                                                   value="<?PHP echo $ColumnValue; ?>" <?php echo $isReadonly; ?>>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <label for="focusedinput" style="text-align: left"
                                           class="col-sm-4 control-label"><?PHP echo $ColumnValueLabel; ?></label>

                                    <?php
                                }
                            } else {
                                ?>
                                <div class="col-lg-9">
                                    Only UN-APPROVED data can be edited. Record ID: <?php echo $xID; ?> is not in UN-APPROVED Stage.
                                </div>
                                <?php
                            }
                            ?>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary nonSmbButton" name="Save" type="submit" id="Save" value="Submit" style="display:none;">
                                        <input class="btn btn-primary jBoxTip smbButton" value="Submit" name="Save" title="Please modify the Commented value(s) First">
                                        <button type="button" class="btn btn-secondary"
                                                onclick="window.open('', '_self', ''); window.close();">Close
                                        </button>
                                    </div>
                                </div>
                            </footer>

                </section>
            </div>
            <?php
            if ($_REQUEST['Save'] === 'Submit') {
                $FirstArray = $_REQUEST;

                $sqlQry = "";

                $sql = "SELECT ui.UserName FROM userinfo ui JOIN xformrecord xfr ON xfr.UserID = ui.id AND xfr.id=$xID";
                $conn = PDOConnectDB();
                $stmt = $conn->query($sql);
                $row = $stmt->fetch();
                $currentUserName = $row['UserName'];

                unset($FirstArray['FormId'], $FirstArray['xID'], $FirstArray['Save']);
                $FilteredArray = $FirstArray;

                $newAry = array();
                $prevAry = array();

                foreach ($FilteredArray as $key => $value) {
                    $ReceivingValue = explode("|", $key);
                    if ($ReceivingValue[count($ReceivingValue) - 1] != "hidden") {
                        $ColumnName = $ReceivingValue[0];
                        $MainID = $ReceivingValue[1];
                        $ColumnDataType = $ReceivingValue[2];
                        if (trim($ColumnDataType) == 'select_multiple') {
                            sort($value);
                            $value = implode(' ', $value);
                        }
                        $newValueArray[] = $value;
                        $newAry[$key] = $value;
                    } else {
                        //Construct Prev Value Array
                        $prevAry[substr($key, 0, -7)] = $value;
                    }
                }

                $newValueData = implode("", $newValueArray);
                //echo '<br>';
                $previousValueData = implode("", $previousValueArray);
                if ($newValueData === $previousValueData) {
                    MsgBox('Nothing to update.');
                } else {
                    //MsgBox('Change is ready to update.');
                    //exit();
                    $backupQry = "SET IDENTITY_INSERT Edit_masterdatarecord ON;
                            INSERT INTO Edit_masterdatarecord (id, XFormRecordId, UserID, FormId, DataName, FormGroupId, CompanyId, ColumnTitle, ColumnName, ColumnValue, EntryDate, IsApproved, PSU, Comments, IsCorrected, IsEdited )
                            SELECT id, XFormRecordId, UserID, FormId, DataName, FormGroupId, CompanyId, ColumnTitle, ColumnName, ColumnValue, EntryDate, IsApproved, PSU, Comments, IsCorrected, IsEdited
                            FROM masterdatarecord_UnApproved WHERE XFormRecordId = $xID;
                            SET IDENTITY_INSERT Edit_masterdatarecord OFF;";

                    if ($app->getDBConnection()->query($backupQry)) {
                        date_default_timezone_set("Asia/Dhaka");

                        foreach ($newAry as $key => $value) {
                            $ReceivingValue = explode("|", $key);

                            $ColumnName = $ReceivingValue[0];
                            $MainID = $ReceivingValue[1];
                            $ColumnDataType = $ReceivingValue[2];
                            $MasterDataID = $ReceivingValue[3];
                            $position = array_search($ColumnName, $previousNodeArray, true);

                            /*if (trim($ColumnDataType) == 'select_multiple') {
                                $value = implode(' ', $value);
                            }*/
                            if ($position > -1) {
                                $DataValue = $previousValueArray[$position];
                                if ($value != $DataValue) {
                                    //$sqlQry .= " UPDATE masterdatarecord_Pending SET ColumnValue='$value' WHERE id='$MasterDataID'; ";
                                    if ($value != '') {
                                        $EntryDate = date('d-m-Y H:i:s');
                                        $comm = "<b>$currentUserName</b> Edited at $EntryDate:<br />&nbsp;&nbsp;&nbsp;<b style=\"color:red;\">Previous Value:</b> ".xss_clean($DataValue)."<br />&nbsp;&nbsp;&nbsp;<b style=\"color:green;\">Edited Value:</b> ".xss_clean($value)."<br />";
                                         $sqlQry .= " UPDATE masterdatarecord_UnApproved SET ColumnValue = N'$value', Comments=COALESCE(Comments, '')+N'$comm', IsEdited=(COALESCE(IsEdited, 0)+1) WHERE id = '$MasterDataID'; ";

                                    }
                                    if ($ColumnName == "PSU") {
                                        $sqlQry .= " UPDATE masterdatarecord_UnApproved SET PSU='$value' WHERE XFormRecordId='$xID'; ";
                                        $sqlQry .= " UPDATE xformrecord SET PSU='$value' WHERE id='$xID'; ";
                                    }
                                }
                            } else {
                                if (strlen(trim($value)) > 0) {
                                    $sqlQry .= "INSERT INTO masterdatarecord_Pending (XFormRecordId, UserID, FormId, DataName, FormGroupId, CompanyId, ColumnTitle, ColumnName, ColumnValue, PSU, IsEdited) VALUES ('$xID','$UserID','$FormId','$DataName','$FormGroupId','$CompanyId','','$ColumnName', N'$value','$PSU', 1);";
                                }
                            }
                        }

                        //echo $sqlQry;
                        //exit();

                        if ($app->getDBConnection()->query($sqlQry)) {
							//$Qry1 = "UPDATE xformrecord SET IsApproved = 0 WHERE id = '$xID'";
							$Qry1 = "UPDATE xformrecord SET IsApproved = 0, IsChecked = 0, IsEdited = (COALESCE(IsEdited, 0)+1) WHERE id = '$xID'";
							$app->getDBConnection()->query($Qry1);
							
                            //MsgBox2('Data updated successfully.');
                            //ReDirect($baseURL . "AppsAPI/editNotificationAlert.php?msg=Data updated successfully.");
                            echo "<script>window.location.href='/AppsAPI/editNotification.php?msg=Data updated successfully.';</script>";
                        } else {
                            MsgBox2('Failed to update data!');
                            //ReDirect($baseURL . "AppsAPI/editNotificationAlert.php?msg=Failed to update data!");
                        }
                    }
                }
            }
            ?>
    </section>
</div>
<div class="col-lg-2 mb-0"></div>
</div>
<!-- end: page -->
</section>
</div>

<script type="text/javascript">
    var body = $("body");

    new jBox('Tooltip', {
        attach: '.jBoxTip',
        theme: 'TooltipDark',
        animation: 'zoomOut',
        adjustDistance: {
            top: 62 + 8,
            right: 5,
            bottom: 5,
            left: 5
        },
        zIndex: 90
    });

    $(document).ready(function () {
        var images = body.find(".imgComments");

        if (images.length > 0) {
            body.find(".smbButton").show(0);
            body.find(".nonSmbButton").hide(0);
        } else {
            body.find(".smbButton").hide(0);
            body.find(".nonSmbButton").show(0);
        }
    });

    body.find(".smbButton").click(function (e) {
        body.find('.correctIdentifier').each(function () {      // check if elment is display none.
            var _this = $(this);

            var adjacentTextBox = _this.siblings("input[type=text]"),
                adjacentSelect = _this.siblings("select");
            if (_this.css('display') == 'none') {
                adjacentTextBox.css('border-color', 'red').focus();
                adjacentSelect.css('border-color', 'red').focus();
            } else {
                adjacentTextBox.css('border-color', '');
                adjacentSelect.css('border-color', '');
            }
        })
    });

    body.find("select").change(function (e) {
        var _this = $(this),
            currVal = '',
            prevVal = _this.siblings("input[type=hidden]").val(),
            correctIdentifier = _this.siblings(".correctIdentifier"),
            isHidden = 0,
            images = body.find(".imgComments"),
            foundNullValue = 0;
        if (_this.prop("multiple")) {
            if (_this.val() === null || typeof _this.val() === "undefined") {
                foundNullValue = 1;
            } else {
                currVal = _this.val().join(" ");
            }
        } else {
            var currVal = _this.val();
        }

        if (foundNullValue) {
            //Cant Edit
            console.log("I am deselected");
            correctIdentifier.hide(0);
            body.find(".smbButton").show(0);
            body.find(".nonSmbButton").hide(0);
            _this.css('border-color', 'red');
        } else {
            _this.css('border-color', '');
            if (images.length > 0) {
                if (currVal == prevVal) {
                    correctIdentifier.hide(0);
                } else {
                    correctIdentifier.show(0);
                }

                body.find('.correctIdentifier').each(function () {      // check if elment is display none.
                    var _this = $(this);
                    var adjacentSelect = _this.siblings("select");
                    if (_this.css('display') == 'none') {
                        isHidden = 1;
                        adjacentSelect.css('border-color', 'red');
                    } else {
                        adjacentSelect.css('border-color', '');
                    }
                });

                if (isHidden == 0) {
                    body.find(".smbButton").hide(0);
                    body.find(".nonSmbButton").show(0);
                } else {
                    body.find(".smbButton").show(0);
                    body.find(".nonSmbButton").hide(0);
                }
            } else {
                body.find(".smbButton").hide(0);
                body.find(".nonSmbButton").show(0);
            }
        }
    });

    body.find("input[type=text]").keyup(function (e) {
        var _this = $(this),
            currVal = _this.val(),
            prevVal = _this.siblings("input[type=hidden]").val(),
            correctIdentifier = _this.siblings(".correctIdentifier"),
            isHidden = 0,
            images = body.find(".imgComments");

        if (currVal == '') {
            correctIdentifier.hide(0);
            body.find(".smbButton").show(0);
            body.find(".nonSmbButton").hide(0);
            _this.css('border-color', 'red');
        } else {
            _this.css('border-color', '');
            if (images.length > 0) {
                if (currVal == prevVal) {
                    correctIdentifier.hide(0);
                } else {
                    correctIdentifier.show(0);
                }

                body.find('.correctIdentifier').each(function () {      // check if elment is display none.
                    var _this = $(this);
                    var adjacentTextBox = _this.siblings("input[type=text]");
                    if (_this.css('display') == 'none') {
                        isHidden = 1;
                        adjacentTextBox.css('border-color', 'red');
                    } else {
                        adjacentTextBox.css('border-color', '');
                    }
                });

                if (isHidden == 0) {
                    body.find(".smbButton").hide(0);
                    body.find(".nonSmbButton").show(0);
                } else {
                    body.find(".smbButton").show(0);
                    body.find(".nonSmbButton").hide(0);
                }
            } else {
                body.find(".smbButton").hide(0);
                body.find(".nonSmbButton").show(0);
            }
        }
    });
</script>

