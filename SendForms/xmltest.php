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

echo "";
  echo "  xml";


echo "   simple.";
echo $xml->getName() . "<br>";
 echo "get name";
 echo "";
foreach ($xml->children() as $child) 
{
 echo "for each";
$param1 = $child->getName();
echo $child->getName() . "<br>";  
$param2 = $child->ColumnValue; 
 echo $child->getName() . "value:<br>";
echo $child->ColumnValue . "<br>";
echo "";
$qry    = "INSERT INTO masterdatarecord (ColumnName, ColumnValue ) VALUES  ('$param1','$param2' )";
echo "insert";
$rs = db_query($qry, $cn);
echo "qry";
if (!$rs )
{
    echo "error";
}
else 
{
    echo "Record added";
}
}
db_close($cn);

    


?>