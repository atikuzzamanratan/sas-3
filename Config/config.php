<?PHP

//echo "from Config.";

error_reporting(0);

function get_base_url()
{
    return env('APP_URL');
}

$projectName = 'Sustainable Agriculture Statistics';
$projectDescription= 'Sustainable Agriculture Statistics (SAS) - 2026';
$projectDescription2= 'টেকসই কৃষি পরিসংখ্যান (এসএএস) ২০২৬';

$adminUserID = '2';

$AuthTokenValue = "Mwgq0LcFGSHvYdVzb1Ifq3L9lhWmi4IXBDWcQZR9hUt1q7UboELrUFVJZO244Ujo";
$unAuthorizedMsg = "Unauthorized Access!";

$sampleHHColumnName = 'A_01';

$btnTitleView = 'View Data';
$btnTitleEdit = 'Edit Data';
$btnTitleDelete = 'Delete Data';
$btnTitleNotice = 'Send Message';

$dataCollectorNamePrefix = 'cd';
$testDataCollectorLastDBId = 840;

$formTypeMain = 'Main';
$formTypeListing = 'Listing';
$formTypeFarm = 'Farm';

$supervisorNamePrefix = 'cs';
$testSupervisorLastDBId = 839;

$testingUserIDs = "(68, 69)";

$columnNameToUpdateValueForMainData = 'A_02';
$columnNameToUpdateValueForListingData = 'SampleHHNo';

$distCoordinatorNamePrefix = 'dist';

$formIdSamplingData = 2;
$formIdMainData = 3;
$formIdFarmData = 4;

$maxNumberOfHHForSampling = 20;
$maxNumberOfHHForListing = 165;

$todayDate = date("Y-m-d");
$sevenDaysBeforToday = date('Y-m-d', strtotime('-7 days'));

$mainFormLastEntryDate = '2028-03-28';
$listingFormLastEntryDate = '2028-01-26';

$defaultStartTimeString = ' 00:00:00';
$defaultEndTimeString = ' 23:59:59';

$googleMapApiKey = 'AIzaSyCh0aR_e-GOQpWjDUSwxOMTCyRtAdaHrYI';

$formDir = "GetForms/";

$xFromPermittedExt = "xml";

$xFormDefaultProvisionDate = "2050-12-31";

$batchDeletePlaceHolderText = "Comma separated ID (Ex: 1100XX, 1100YY, 7800ZZ)";

$baseURL = get_base_url();

//$cn = ConnectDB();

//$conn = PDOConnectDB();

function getDBMain(): string
{
    return "SAS_3";
}

function ConnectDB()
{
    // $Pass = "bmWfjg88";

    $Server = env('DB_HOST');
    $Pass = env('DB_PASSWORD');

    $User = env('DB_USERNAME');
    $DBName = env('DB_DATABASE');
    $cn = odbc_connect("Driver={SQL Server};Server=$Server;Database=$DBName", "$User", "$Pass");
    if (!$cn) {
        die('Cannot connect to DataBase');
        return FALSE;
    } else {
        return $cn;
    }
}

function PDOConnectDB()
{
    $serverName = env('DB_HOST');
    $database = env('DB_DATABASE');
    $uid = env('DB_USERNAME');
    $pwd = env('DB_PASSWORD');
    try {
        return new PDO(
            "sqlsrv:server=$serverName;Database=$database", $uid, $pwd, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            )
        );
    } catch (PDOException $e) {
        die("Error connecting to SQL Server: " . $e->getMessage());
        return FALSE;
    }
}

function db_query($qry, $cn)
{
    return odbc_exec($cn, $qry);
}

function db_fetch_array($rs)
{
    return odbc_fetch_array($rs);
}

function db_fetch_row($rs)
{
    return odbc_fetch_row($rs);
}

function db_fetch_object($rs)
{
    return odbc_fetch_array($rs);
}

function db_num_rows($rs)
{
    return odbc_num_rows($rs);
}

function db_close($cn)
{
    return odbc_close($cn);
}

function MsgBox($msg)
{
    echo '<script language="JavaScript">
   alert("' . $msg . '");
   </script>';
}

function MsgBox2($msg)
{
    echo '<script language="JavaScript">
   alert("' . $msg . '");
   window.close();
   </script>';
}

function ReDirect($src)
{
    echo '<script language="JavaScript">
   window.location="' . $src . '";
   </script>';
}

function ReloadPage()
{
    echo '<script language="JavaScript">
   window.location.href;
   </script>';
}

function whatsAppLink($mobileNo): string
{
    $UserMobileNo = $mobileNo;
    $MobileNumber = substr($UserMobileNo, -10);
    $MobileNumber = "880$MobileNumber";
    return "<a href='https://api.whatsapp.com/send?phone=$MobileNumber' target='_blank'><img align='left' src='../img/whatsapp_logo.png' width='24'>". $UserMobileNo. "</a>";
}

function encryptValue($value)
{
// Store the cipher method
    $ciphering = "AES-128-CTR";

// Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

// Non-NULL Initialization Vector for encryption
    $encryption_iv = '1234567891011121';

// Store the encryption key
    $encryption_key = "solversDCpanel";

    return openssl_encrypt($value, $ciphering,
        $encryption_key, $options, $encryption_iv);
}

function decryptValue($value)
{
    // Store the cipher method
    $ciphering = "AES-128-CTR";

    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

// Non-NULL Initialization Vector for decryption
    $decryption_iv = '1234567891011121';

// Store the decryption key
    $decryption_key = "solversDCpanel";

// Use openssl_decrypt() function to decrypt the data
    return openssl_decrypt($value, $ciphering,
        $decryption_key, $options, $decryption_iv);
}

function NullChecker($val): string
{
    if (!is_null($val)) {
        return trim($val);
    } else {
        return 'NULL';
    }
}

function HeaderPath(): string
{
    return "/SMS_Shimu/WebPanel/images/";
}

function csvFilePath(): string
{
    return "/SMS_Shimu/WebPanel/CSV/";
}

const PACKAGE_TYPE_ID_STANDARD = 1;
const PACKAGE_TYPE_ID_ADVANCE = 2;
const PACKAGE_TYPE_ID_ENTERPRISE = 3;
const PACKAGE_TYPE_STANDARD_AMOUNT = "10000";
const PACKAGE_TYPE_ADVANCE_AMOUNT = "25000";
const PACKAGE_TYPE_ENTERPRISE_AMOUNT = "50000";
const PACKAGE_TYPE_STANDARD_MAX_NO_USERS = "10";
const PACKAGE_TYPE_ADVANCE_MAX_NO_USERS = "50";
const PACKAGE_TYPE_ENTERPRISE_MAX_NO_USERS = "10000000";
const PACKAGE_TYPE_STANDARD_UPLOAD_CREDIT = "5000";
const PACKAGE_TYPE_ADVANCE_UPLOAD_CREDIT = "10000";
const PACKAGE_TYPE_ENTERPRISE_UPLOAD_CREDIT = "20000";
const PACKAGE_TYPE_STANDARD_STORAGE = "5 GB";
const PACKAGE_TYPE_ADVANCE_STORAGE = "10 GB";
const PACKAGE_TYPE_ENTERPRISE_STORAGE = "25 GB";
const PACKAGE_TYPE_STANDARD_FORM_PER_ACCOUNT = "10";
const PACKAGE_TYPE_ADVANCE_FORM_PER_ACCOUNT = "25";
const PACKAGE_TYPE_ENTERPRISE_FORM_PER_ACCOUNT = "50";
const PACKAGE_TYPE_ADVANCE_VALIDITY_DAYS = "30";
const PACKAGE_TYPE_ENTERPRISE_VALIDITY_DAYS = "30";
const PACKAGE_TYPE_STANDARD_VALIDITY_DAYS = "30";

const ERROR_CODE_TYPE_ID_LIMIT_OVER_UPLOAD_CREDIT = "405";
const ERROR_CODE_TYPE_ID_LIMIT_OVER_STORAGE = "406";
const ERROR_CODE_TYPE_ID_PAYMENT_REQUIRED = "402";
const PACKAGE_PAYMENT_TYPE_ID = "1";
const UPLOAD_CREDIT_PAYMENT_TYPE_ID = "2";

