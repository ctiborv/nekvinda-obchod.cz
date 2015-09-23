<?php
include_once 'admin/_mysql.php';
include_once '_functions.php';


$query = "SELECT id,name FROM ".T_GOODS." WHERE akce=1 AND lang=2 ";  
$sql=my_DB_QUERY($query,__LINE__,__FILE__);
$pocet=mysql_num_rows($sql);
echo 'nalezeno '.$pocet.' polozek v akci webu 2 - http://www.naradi-obchod.czbr <br />';
while($z=mysql_fetch_array($sql)) {
  echo $z['id'].''.$z['name'].'<br />';
  $q = "INSERT INTO ".T_GOODS_X_AKCE." VALUES(".$z['id'].",'8','2')";
	my_DB_QUERY($q,__LINE__,__FILE__);
	$q2 = "UPDATE ".T_GOODS." SET akce = 0 WHERE id = ".$z['id']." ";
  my_DB_QUERY($q2,__LINE__,__FILE__);
  }




$query = "SELECT id,name FROM ".T_GOODS." WHERE akce=1 AND lang=3 ";  
$sql=my_DB_QUERY($query,__LINE__,__FILE__);
$pocet=mysql_num_rows($sql);
echo '<br /><br />nalezeno '.$pocet.' polozak v akci webu 3 - http://www.zahradni-naradi.cz<br />';
while($z=mysql_fetch_array($sql)) {
echo $z['id'].''.$z['name'].'<br />';
  $q = "INSERT INTO ".T_GOODS_X_AKCE." VALUES(".$z['id'].",'9','3')";
	my_DB_QUERY($q,__LINE__,__FILE__);
	$q2 = "UPDATE ".T_GOODS." SET akce = 0 WHERE id = ".$z['id']." ";
  my_DB_QUERY($q2,__LINE__,__FILE__);
  }
?>
