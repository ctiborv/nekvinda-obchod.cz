<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_functions.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/_security.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_functions.php');



	      
      $query = "SELECT  id,id_good,name FROM ".T_FOTO_ZBOZI." "; 
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
      $pocet = 0;
      while ($z = mysql_fetch_array($v)) {
         $aktProdukt = $z['id'];
         if(!empty($z['name'])) $aktName = $z['name'];
         else $aktName = "";
         $aktIdGood = $z['id_good'];
         
         if($aktName == "") { //name je prázdný provedeneme doplnìní podle id_good
             $newName = $aktIdGood.".jpg";
             $query3 = 'UPDATE '.T_FOTO_ZBOZI.' SET name = "'.$newName.'", time = '.time().', position = 1 WHERE id = '.$aktProdukt;
             my_DB_QUERY($query3,__LINE__,__FILE__);
             $pocet++;
         }
      }
      echo "pridano ".$pocet." vet";
	 
      





?>
