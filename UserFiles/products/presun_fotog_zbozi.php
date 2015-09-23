<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_functions.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/_security.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_functions.php');



	      
      $q = "SELECT  ".T_FOTO_KATEG.".id as cat_id, ".T_FOTO_KATEG.".name as cat_name, ".T_FOTO_ZBOZI.".id_good, ".T_FOTO.".id, ".T_FOTO.".name, ".T_FOTO.".img  
      FROM ".T_FOTO_ZBOZI.",".T_FOTO_KATEG.",".T_FOTO."   
      WHERE  ".T_FOTO_KATEG.".id = ".T_FOTO_ZBOZI.".id_kateg
      AND ".T_FOTO_KATEG.".hidden =0
      AND ".T_FOTO.".id_kateg = ".T_FOTO_KATEG.".id
      AND ".T_FOTO_KATEG.".".SQL_C_LANG."  ORDER BY ".T_FOTO_KATEG.".position, ".T_FOTO.".pos  ";
      $vq = my_DB_QUERY($q,__LINE__,__FILE__);
      

	 
      while ($zvg = mysql_fetch_array($vq)) {
		$name = $foto_name = $zvg['name'];
		$foto_name=lenght_of_string(35,$foto_name,' ');
		//zkopírujeme fotky
        $img1  = copy(IMG_F_S_RELATIV.$zvg['id'].'.'.$zvg['img'],IMG_P_S_RELATIV.'foto'.$zvg['id'].'.'.$zvg['img']);
		$img2  = copy(IMG_F_M_RELATIV.$zvg['id'].'.'.$zvg['img'],IMG_P_M_RELATIV.'foto'.$zvg['id'].'.'.$zvg['img']);
		$img3  = copy(IMG_F_O_RELATIV.$zvg['id'].'.'.$zvg['img'],IMG_P_O_RELATIV.'foto'.$zvg['id'].'.'.$zvg['img']);
		
		//založíme záznamy do tabulky
		
		$query = "
          SELECT MAX(position) as max_position FROM ".T_FOTO_ZBOZI."
          WHERE id_good = '".$zvg['id_good']."'
          ";
          $v = my_DB_QUERY($query,__LINE__,__FILE__);
          $z = mysql_fetch_array($v);
          $max_position = $z['max_position'];
          $pozice = $max_position + 1; 
		
          $query = 'INSERT INTO '.T_FOTO_ZBOZI.' SET id_good = '.$zvg['id_good'].' , name = "foto'.$zvg['id'].'.'.$zvg['img'].'", time = '.time().', position = '.$pozice;
          my_DB_QUERY($query,__LINE__,__FILE__);
          
          $query = 'UPDATE '.T_FOTO_ZBOZI.' SET id_kateg = "" WHERE id_good = '.$zvg['id_good'].' AND id_kateg = '.$zvg['cat_id'].'';
          my_DB_QUERY($query,__LINE__,__FILE__);
            
		
      }





?>
