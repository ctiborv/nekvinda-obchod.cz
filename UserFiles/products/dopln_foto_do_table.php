<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_functions.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/_security.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_functions.php');



	      
      $query = "SELECT  id FROM ".T_GOODS." "; 
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
      $pocet = 0;
      while ($z = mysql_fetch_array($v)) {
         $aktProdukt = $z['id'];
         // relativni cesta
          $small_relative = IMG_P_S_RELATIV.$aktProdukt.'.jpg';
          // echo "<br />small_relative: ".$small_relative;
          $middle_relative = IMG_P_M_RELATIV.$aktProdukt.'.jpg';
          // echo "<br />middle_relative: ".$middle_relative;
          $original_relative = IMG_P_O_RELATIV.$aktProdukt.'.jpg';
         if(file_exists($small_relative) AND file_exists($middle_relative) AND file_exists($original_relative)) {
            //obrázek existuje, zkontrolujeme v tabulce
             $query2 = "SELECT  id_good, name FROM ".T_FOTO_ZBOZI."  
              WHERE id_good = ".$aktProdukt."";
              $v2 = my_DB_QUERY($query2,__LINE__,__FILE__);
              $nalezeno = 0;
              $nalezeno = mysql_num_rows($v2);
              if( $nalezeno < 1 ) {
                  $pocet++;
                  //je záznam v tabulce        		
                  $query3 = 'INSERT INTO '.T_FOTO_ZBOZI.' SET id_good = '.$aktProdukt.' , name = "'.$aktProdukt.'.jpg", time = '.time().', position = 1';
                  my_DB_QUERY($query3,__LINE__,__FILE__);
                  
                  
              }
              
          }
            
      }
      echo "pridano ".$pocet." vet";
	 
      





?>
