<?php
error_reporting(E_ALL);
include "../Components/header-includes.php";

require '../vendor/autoload.php';

use Solvers\Dsql\Application;

$app = new Application();

include "../Config/config.php";
include "../Lib/lib.php";

$xID = xss_clean($_REQUEST['xID']);

//$ReceivedID = xss_clean($_REQUEST['ReceivedID']);
$RecordID = $xID;

/*$SplitReceivedID = explode('|||', decryptValue($ReceivedID));
$RecordID = $SplitReceivedID[0];
$LoggedUserID = $SplitReceivedID[1];
$AgentID = $SplitReceivedID[2];
*/
//echo "$RecordID, $LoggedUserID, $AgentID";

/*if (!is_numeric($RecordID) or !is_numeric($LoggedUserID) or !is_numeric($AgentID)) {
    MsgBox2('You are not permitted in this page 1!');
    exit();
} else {
    $LoggedUserName = getValue('userinfo', 'UserName', "id = $LoggedUserID");
    if (strpos($LoggedUserName, 'admin') === false) {
        MsgBox2('You are not permitted in this page 2!');
        exit();
    }
}*/

$MasterDataQuery = "EXEC EditDetailDataWithLabel $RecordID";
$MasterDataQueryRS = $app->getDBConnection()->fetchAll($MasterDataQuery);

$count = count($MasterDataQueryRS);

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
                        <h2 class="card-title">Edit Data Form</h2>
                    </header>
                    <div class="card-body">
                        <form name="editForm" class="form-horizontal form-bordered" action="" method="post">
							<input type="hidden" name="updateAction" value="Update" />
                            <?php
                            if ($count) {
                                ?>
                                <div class="form-group row pb-4">
                                    <label class="col-lg-9 control-label text-lg-start pt-2"><b>Record ID
                                            : <?php echo $RecordID; ?></b></label>
                                </div>
                                <?php
                                foreach ($MasterDataQueryRS as $row) {
                                    $Id = $row->ID;
                                    $ColumnName = $row->ColumnNameOriginal;
                                    $ColumnNameModified = $row->ColumnLabelModified;
                                    $ColumnValue = $row->ColumnValueOriginal;
                                    $ColumnValueLabel = $row->ColumnValueModified;
                                    $ColumnDataType = $row->ColumnDataType;
                                    $ColumnChoiceValueAll = $row->ColumnChoiceValueAll;
                                    $ColumnChoiceLabelAll = $row->ColumnChoiceLabelAll;
                                    $PassingValue = $ColumnName . "|" . $Id . "|" . $ColumnDataType;
                                    $previousValueArray[] = $ColumnValue;
                                    $previousNodeArray[] = $ColumnName;
									echo $IsEditable = $row->IsEditable;

                                    $previousValueArrayStr = implode(',', $previousValueArray);
                                    ?>
                                    <div class="form-group row pb-3">
                                        <label class="col-lg-9 control-label text-lg-start pt-2"
                                               for="" <?php if ($row->Comments != '' && $row->IsCorrected) { echo 'style="display: inline !important;width: auto;background-color: #fff192;"';}?> ><b><?php echo $ColumnNameModified; ?></b></label>
                                    <?php
										if ($row->Comments != '' && $row->IsCorrected) {
											echo '<img class="jBoxTip imgComments" src="../img/Comments.png" border=0 style="cursor: pointer;width: 90px;height:60px;" width="90px" height="60px" title=\''.$row->Comments.'\'>';
											echo '<img class="correctIdentifier" src="../img/Correct.png" style="width: 90px;height:50px;display:none;" border="0" width="90px" height="50px">';
										}
									?>    
									<?php
										if ($IsEditable == 0) {
											echo '<div>'.$ColumnValue.'</div>';
										} else {
											if (trim($ColumnDataType) == 'select_one') {
												$ColumnChoiceValueAllArray = explode('|', $ColumnChoiceValueAll);
												$ColumnChoiceLabelAllArray = explode('|', $ColumnChoiceLabelAll);
												?>
												<select name="<?php echo $PassingValue; ?>" id="UserName"
														class="form-control">
													<?php
													$i = 0;
													foreach ($ColumnChoiceValueAllArray as $ColumnChoiceValueData) {
														if ($ColumnValue == $ColumnChoiceValueData) {
															echo "<option value=\"$ColumnChoiceValueData\" selected>$ColumnChoiceLabelAllArray[$i]</option>";
														} else {
															echo "<option value=\"$ColumnChoiceValueData\">$ColumnChoiceLabelAllArray[$i]</option>";
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
														class="form-control" multiple>
													<?php
													$j = 0;
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
											} elseif ($ColumnName == $sampleHHColumnName) {
												echo "<input type=\"text\" class=\"form-control\" name=\"$PassingValue\" id=\"$PassingValue\" value=\"$ColumnValue\" readonly>";
											} elseif ($ColumnName === "SelectedPerson_LineNo") {
												echo "<input type=\"text\" class=\"form-control\" name=\"$PassingValue\" id=\"$PassingValue\" value=\"$ColumnValue\" readonly>";
											} elseif ($ColumnName === "SelectedPerson_Name") {
												echo "<input type=\"text\" class=\"form-control\" name=\"$PassingValue\" id=\"$PassingValue\" value=\"$ColumnValue\" readonly>";
											} else {
												echo "<input type=\"text\" class=\"form-control\" name=\"$PassingValue\" id=\"$PassingValue\" value=\"$ColumnValue\">";
											}
										}
                                        
                                        //echo "<label for=\"UserName\">$ColumnValueLabel</label>";
											echo "<input type='hidden' name='$PassingValue|hidden' value='$ColumnValue'>";
                                        ?>
                                    </div>
                                    <?php
                                }
                            } else {
                                ?>
                                <div class="col-lg-9">
                                    Only UN-APPROVED data can be edited. Your data ID: <?php echo $RecordID; ?> is not
                                    in
                                    UN-APPROVED Stage.
                                </div>
                                <?php
                            }
                            ?>

                            <footer class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-lg-9">
                                        <input class="btn btn-primary nonSmbButton" type="submit" value="Update" name="update" id="update" style="display:none;">
                                        <input class="btn btn-primary jBoxTip smbButton" value="Update" name="update" title="Please modify the Commented value(s) First">
                                        <button type="button" class="btn btn-secondary"
                                                onclick="window.open('', '_self', ''); window.close();">Close
                                        </button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
					<?php
                    if ($_REQUEST['update'] === 'Update') {
                        $FirstArray = $_REQUEST;
                        $sqlQry = "";
						
						$sql = "SELECT ui.UserName FROM userinfo ui JOIN xformrecord xfr ON xfr.UserID = ui.id AND xfr.id=$RecordID";
						$conn = PDOConnectDB();
						$stmt = $conn->query($sql);
						$row = $stmt->fetch();
						$currentUserName = $row['UserName'];

                        unset($FirstArray['FormId'], $FirstArray['ReceivedID'], $FirstArray['update']);
                        $FilteredArray = $FirstArray;
						
						$newAry = array();
						$prevAry = array();

                        foreach ($FilteredArray as $key => $value) {
                            $ReceivingValue = explode("|", $key);
							if ($ReceivingValue[count($ReceivingValue)-1] != "hidden") {
								$ColumnName = $ReceivingValue[0];
								$MainID = $ReceivingValue[1];
								$ColumnDataType = $ReceivingValue[2];
								if (trim($ColumnDataType) == 'select_multiple') {
									sort($value);
									$value = trim(implode(' ', $value));
								}
								$newValueArray[] = $value;
								$newAry[$key] = $value;
							} else {
								//Construct Prev Value Array
								$prevAry[substr($key, 0 , -7)] = $value;
							}
                        }

                        $newValueData = implode("", $newValueArray);
                        $previousValueData = implode("", sort($previousValueArray));
                        if ($newValueData === $previousValueData) {
                            MsgBox('Nothing to update.');
                        } else {
                            //MsgBox('Change is ready to update.');
                            $backupQry = "SET IDENTITY_INSERT Edit_masterdatarecord ON;
											INSERT INTO Edit_masterdatarecord 
												(
													id, 
													XFormRecordId, 
													UserID, 
													FormId, 
													DataName, 
													FormGroupId, 
													CompanyId, 
													ColumnTitle, 
													ColumnName, 
													ColumnValue, 
													EntryDate, 
													IsApproved, 
													PSU, 
													Comments,
													IsCorrected,
													IsEdited 
												)
											SELECT id, 
												XFormRecordId, 
												UserID, 
												FormId, 
												DataName, 
												FormGroupId, 
												CompanyId, 
												ColumnTitle, 
												ColumnName, 
												ColumnValue, 
												EntryDate, 
												IsApproved, 
												PSU, 
												Comments, 
												IsCorrected,
												IsEdited												
											FROM masterdatarecord_UnApproved 
											WHERE XFormRecordId = $RecordID;
										SET IDENTITY_INSERT Edit_masterdatarecord OFF;";

                            if ($app->getDBConnection()->query($backupQry)) {
								date_default_timezone_set("Asia/Dhaka");
                                foreach ($newAry as $key => $value) {
                                    $ReceivingValue = explode("|", $key);
									$ColumnName = $ReceivingValue[0];
                                    $MainID = $ReceivingValue[1];
                                    $ColumnDataType = $ReceivingValue[2];
                                    /*
									if (trim($ColumnDataType) == 'select_multiple') {
										sort($value);
										$value = trim(implode(' ', $value));
									}
									*/
									$DataValue = $prevAry[$key];
									if ($value != $DataValue) {
										if ($value != '') {
											$EntryDate = date('d-m-Y H:i:s');
											$comm = "<b>$currentUserName</b> Edited at $EntryDate:<br />&nbsp;&nbsp;&nbsp;<b style=\"color:red;\">Previous Value:</b> ".xss_clean($DataValue)."<br />&nbsp;&nbsp;&nbsp;<b style=\"color:green;\">Edited Value:</b> ".xss_clean($value)."<br />";
											$sqlQry .= " UPDATE masterdatarecord_Pending SET ColumnValue = N'$value', Comments=COALESCE(Comments, '')+N'$comm', IsEdited=(COALESCE(IsEdited, 0)+1) WHERE id = '$MainID'; ";
										}
										if ($ColumnName == "PSU") {
											$sqlQry .= " UPDATE masterdatarecord_Pending SET PSU = '$value' WHERE XFormRecordId = '$RecordID'; ";
											$sqlQry .= " UPDATE xformrecord SET PSU = '$value' WHERE id = '$RecordID'; ";
										}
										/*if ($ColumnName == $sampleHHColumnName) {
											$sqlQry .= " UPDATE masterdatarecord_Pending SET SampleHHNo = '$value' WHERE XFormRecordId = '$RecordID'; ";
											$sqlQry .= " UPDATE xformrecord SET SampleHHNo = '$value' WHERE id = '$RecordID'; ";
										}*/
									}
                                }

                                $Qry1 = "UPDATE xformrecord SET IsApproved = 0, IsChecked = 0, IsEdited = (COALESCE(IsEdited, 0)+1) WHERE id = '$RecordID'";
                                $app->getDBConnection()->query($Qry1);

                                if ($app->getDBConnection()->query($sqlQry)) {
									//die("SQL:".$sqlQry);
									//MsgBox2('Data updated successfully.');
									echo "<script>window.location.href='/AppsAPI/editNotification.php?msg=Data updated successfully.';</script>";
                                    //ReDirect($baseURL . "AppsAPI/editNotificationAlert.php?msg=Data updated successfully.");
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
	  
	$(document).ready(function(){
		var images = body.find(".imgComments");
		
		if (images.length > 0) {
			body.find(".smbButton").show(0);
			body.find(".nonSmbButton").hide(0);
		} else {
			body.find(".smbButton").hide(0);
			body.find(".nonSmbButton").show(0);
		}
	});
	
	body.find(".smbButton").click(function(e){
		body.find('.correctIdentifier').each(function () {      // check if elment is display none.
			var _this = $(this);
			
			var adjacentTextBox = _this.siblings("input[type=text]"),
				adjacentSelect = _this.siblings("select");
			if (_this.css('display') == 'none')  {
				adjacentTextBox.css('border-color', 'red').focus();
				adjacentSelect.css('border-color', 'red').focus();
			} else {
				adjacentTextBox.css('border-color', '');
				adjacentSelect.css('border-color', '');
			}
		})
	});
	
	body.find("select").change(function(e){
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
					if (_this.css('display') == 'none')  {
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

	body.find("input[type=text]").keyup(function(e){
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
					if (_this.css('display') == 'none')  {
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
