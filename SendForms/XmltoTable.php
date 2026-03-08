<?php
require_once '../Config/config.php';

$UserID = $_GET['UserID'];
$UserID = 1;

$cn = ConnectDB();
echo "connected  ";
echo "";
//mysql_select_db(getDBMain());
echo "getdbms";
$xml=simplexml_load_file(base_url() .'SendForms\Form One_2015-10-19_16-24-26.xml');
  
 



echo "  string   ";
echo $xml->getName() . "<br>";
echo "get name";
echo "";
foreach ($xml->children() as $child) 
{

$param1 = $child->getName();
$param2 =  $child; 

 echo "TAG:";
echo $child->getName() . "<br>";
echo "Value:";
echo $child . "<br>";

  

echo "";
$qry    = "INSERT INTO masterdatarecord (ColumnName, ColumnValue ) VALUES  ('$param1','$param2' )";
echo $qry."<br/";
$rs = db_query($qry, $cn);

if (!$rs )
{
    echo "error";
}
else 
{
    echo "   Record added....";
	echo  "<br>";
	echo "  ";
}
}
db_close($cn);

?>




=======
<?php
require_once '../Config/config.php';

$UserID = $_GET['UserID'];
$UserID = 1;

$cn = ConnectDB();
echo "connected  ";
echo "";
//mysql_select_db(getDBMain());
echo "getdbms";
$xml=simplexml_load_file(base_url() .'SendForms\Form One_2015-10-19_16-24-26.xml');

echo "  string   ";
echo $xml->getName() . "<br>";
echo "get name";
echo "";
foreach ($xml->children() as $child) 
{

$param1 = $child->getName();
$param2 =  $child; 

 echo "TAG:";
echo $child->getName() . "<br>";
echo "Value:";
echo $child . "<br>";

  

echo "";
echo $qry    = "INSERT INTO masterdatarecord (ColumnName, ColumnValue ) VALUES  ('$param1','$param2' )";

$rs = db_query($qry, $cn);

if (!$rs )
{
    echo "error";
}
else 
{
    echo "   Record added....";
	echo  "<br>";
	echo "  ";
}
}
db_close($cn);

    


?>
>>>>>>> 77cfe5d21315b17eb68f7aee652894275aead444
