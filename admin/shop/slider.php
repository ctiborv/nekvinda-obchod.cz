<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


$query_instal = "
CREATE TABLE IF NOT EXISTS `".T_SLIDER."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `default_slider` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;
";
my_DB_QUERY($query_instal,__LINE__,__FILE__);

$query_instal = "
CREATE TABLE IF NOT EXISTS `".T_SLIDER_FOTO."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_slider` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `poradi` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_slider` (`id_slider`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;
";
my_DB_QUERY($query_instal,__LINE__,__FILE__);

$trans = array("'" => "&#39;");


function select_slider($id_foto)
{
  $query = "SELECT id_slider FROM ".T_SLIDER_FOTO." WHERE id = '".intval($id_foto)."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);
	
  return $z['id_slider'];
}


function max_order($id_slider)
{
  $query = "SELECT MAX(poradi) as max FROM ".T_SLIDER_FOTO." WHERE id_slider = '".intval($id_slider)."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);

  return $z['max'];
}


function select_order($id_foto)
{
  $query = "SELECT poradi FROM ".T_SLIDER_FOTO." WHERE id = '".intval($id_foto)."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);
	
  return $z['poradi'];
}


/* Nahraje obrazek na server */
function upload_foto($file , $cesta)
{
  $r = getimagesize($file['tmp_name']);	 // zjistime rozmery originalu a typ souboru
  $w_orig = $r[0]; // sirka originalu
  $h_orig = $r[1]; // sirka originalu
	$typ = $r[2]; // typ souboru


	if($typ != 2 && $typ != 3) 
  {  // kontrola typu souboru - povolime jen jpg, png
	  $_SESSION['alert_js'] = "Nesprávný formát obrázku. ";
	  $_SESSION['alert_js'] .= "Použijte formát JPG nebo PNG.\\n\\n";
	  $_SESSION['alert_js'] .= "Nic nebylo uloženo.";
	  
	  return 0;
	}
	else
	{
    // uprava nazvu pro korektni pouziti na webu
    $file_name_array = explode (".", $file['name']); // roztrhame nazev souboru - delicem je tecka
		$pripona_index = count($file_name_array) - 1; // index posledniho prvku pole (pripona)
		$pripona = $file_name_array[$pripona_index]; // pripona
		
		// sestaveni nazvu bez pripony
		$file_name = "";
		
    for($index = 0; $index != $pripona_index; $index++)
    { // dalsi slova oddelena teckami pridame do nazvu
      $file_name .= $file_name_array[$index];
        
      if($index != $pripona_index - 1)
      { // tecky nahradime za "-" vynechame pouze posledni tedku (oddeluje priponu od nazvu)
        $file_name .= "-";
      }
    }

    //$WWW_root = dirname(__FILE__).DIRECTORY_SEPARATOR;

	  $file_name = strtolower($file_name); // zmenseni velkych pismen
	  $file_name = text_in_url($file_name); // upravime nazev pro pouziti v URL adrese
	  $pripona = strtolower($pripona); // zmenseni pismen v pripone
	  $file_name .= "." . $pripona; // pridani pripony do nazvu
	  $original = $cesta.$file_name; // kompletni cesta kam soubor ulozit
    
	  $upload = move_uploaded_file($file['tmp_name'] , $original); // umistime original souboru
	}
  
  return $file_name;
}
/* Nahraje obrazek na server */


/* Smaze fotku ze serveru*/
function delete_foto($id)
{
  $id = intval($id);

  $query = "SELECT name FROM ".T_SLIDER_FOTO." WHERE id = '".$id."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);
	$img = $z['name'];
	
  $query = "SELECT COUNT(name) as pocet FROM ".T_SLIDER_FOTO." WHERE name = '".$img."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);
  $pocet = $z["pocet"];	

  if($pocet == 1)
  { // fotka je jen u jednoho slideru muzeme ji smazat
	  unlink($_SERVER['DOCUMENT_ROOT'].'/UserFiles/slider/'.$img); // cesta pro nahled
  }
}
/* Smaze fotku ze serveru*/


/* Smaze fotku slideru z DB*/
function delete_slider_foto($id)
{
  delete_foto($id);
	
  $query = "DELETE FROM ".T_SLIDER_FOTO." WHERE id = '".intval($id)."'";
	my_DB_QUERY($query,__LINE__,__FILE__);	
	my_OPTIMIZE_TABLE(T_SLIDER_FOTO);
}
/* Smaze fotku slideru z DB*/


/* Odstrani fotku ze slideru */
if(isset($_GET['delimg']) AND !empty($_GET['delimg'])) 
{ // odstraneni fotky slideru
  $id = $_GET['delimg'];
  delete_slider_foto($id);
	
	$_SESSION['alert_js'] = "Obrázek odstraněn";
	Header("Location: ".$_SERVER['HTTP_REFERER']."");
	exit;
}
/* Odstrani fotku ze slideru */


/* zmena poradi fotky */
if(isset($_GET['zmena_poradi']) AND !empty($_GET['zmena_poradi']))
{
  if(isset($_GET['id_foto']) AND !empty($_GET['id_foto']))
  {
    $id_foto = $_GET['id_foto']; 
    $id_slider = select_slider($id_foto); 
    $poradi = select_order($id_foto);
        
    if(isset($_GET['up']) AND $_GET['up'] == 1)
    {
      $new_poradi = $poradi + 1; 
      $query = "UPDATE ".T_SLIDER_FOTO." SET poradi=$poradi WHERE id_slider=".$id_slider." AND poradi=".$new_poradi."";
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
      $query = "UPDATE ".T_SLIDER_FOTO." SET poradi=".$new_poradi." WHERE id=$id_foto";
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
    }
  
    if(isset($_GET['down']) AND $_GET['down'] == 1)
    {
      $new_poradi = $poradi - 1;
      $query = "UPDATE ".T_SLIDER_FOTO." SET poradi=$poradi WHERE id_slider=".$id_slider." AND poradi=".$new_poradi."";
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
      $query = "UPDATE ".T_SLIDER_FOTO." SET poradi=".$new_poradi." WHERE id=$id_foto";
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
    }
  }
  
	Header("Location: ".$_SERVER['HTTP_REFERER']."");
	exit;	  
} 
/* zmena poradi fotky */


/* Odstrani slider */
if(isset($_GET["delete"]) AND !empty($_GET["delete"]))
{ // odstranit slider
  $id_slider = $_GET["delete"];
  $query = "SELECT id FROM ".T_SLIDER_FOTO." WHERE id_slider=".$id_slider;
  $v = my_DB_QUERY($query , __LINE__ , __FILE__);
  
  while($z = mysql_fetch_array($v))
  {
    $id = $z["id"];
    delete_slider_foto($id);
  }
  
  $query = "DELETE FROM ".T_SLIDER." WHERE id=".$id_slider;
  my_DB_QUERY($query , __LINE__ , __FILE__);
  my_OPTIMIZE_TABLE(T_SLIDER);

  $_SESSION['alert_js'] = "Slider odstraněn";
  
	Header("Location: ".MAIN_LINK."&a=list");
	exit;	  
}
/* Odstrani slider */


/* Seznam sliderů */
if($_GET["a"] == "list")
{
  if(isset($_POST) AND !empty($_POST["pridat"]))
  {
    if(!empty($_POST["name"]))
    {
      $name = $_POST["name"];
      $name  = mysql_real_escape_string($name);
      $name  = strtr($name  , $trans);      
    
      $query = "INSERT INTO ".T_SLIDER." (name) VALUES ('$name')";
      my_DB_QUERY($query , __LINE__ , __FILE__);  
      $_SESSION['alert_js'] = "Vložen nový slider";  
    }
    else
    {
      $_SESSION['alert_js'] = "Vyplňte jméno";
    }

    Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
  }  
  
  $nadpis = 'Slidery';
  
  $query = "SELECT id , name FROM ".T_SLIDER;
  $v = my_DB_QUERY($query , __LINE__ , __FILE__);

  $seznam = '';
  
  while($z = mysql_fetch_assoc($v))
  {
    $id = $z["id"];
    $name = $z["name"];

    $seznam .= '
      <tr '.TABLE_ROW.'>
        <td>'.$name.'</td><td class="td_edit" width="15">'.ico_edit(MAIN_LINK."&amp;f=slider&amp;a=edit&amp;id=".$id,'Upravit záznam').'</td>
      </tr>';
  }
  
  $data = '
    <h3>Přidat slider</h3>
    <form method="post" action="">
      <table class="admin_table_form">
      <tr>
        <td style="width:150px;">
          Název 
        </td>
        <td style="width:410px;">
          <input style="width:400px;" type="text" name="name"  value="" >
        </td> 
        <td>
          <input type="submit" name="pridat" value="Přidat" >
        </td>
      </tr>
      </table>           
    </form>
    
    <h3>Seznam sliderů</h3>
    <table class="list">
      '.$seznam.'
    </table>';
}
/* Seznam sliderů */


/* Editace slideru */
if($_GET["a"] == "edit")
{
  if(isset($_POST) AND !empty($_POST))
  { // upravy 
    if(!empty($_FILES['foto']['name'])) 
    {
      $file_name = upload_foto($_FILES['foto'] , $_SERVER['DOCUMENT_ROOT']."/UserFiles/slider/");
    }
	
    // Společné informace pro insert i update.
    $set = "
    link = '".mysql_real_escape_string(trim($_POST["link"]))."',
    title = '".mysql_real_escape_string(trim($_POST["title"]))."',
    text = '".mysql_real_escape_string(trim($_POST["text"]))."'
    ";

    if(!empty($_POST["id"]) AND !empty($file_name))
    { // ukladame novou fotku
		  $id = $_POST["id"];

      $poradi = max_order($id) + 1;

      $query = "
      INSERT INTO ".T_SLIDER_FOTO."
      SET
      ".$set." ,
      id_slider = '".$id."' ,
      name = '".$file_name."' ,
      poradi = '".$poradi."'";
		  my_DB_QUERY($query,__LINE__,__FILE__);
	  }
	  
	  if(!empty($_POST["id_foto"]))
	  { // aktualizujeme fotku
	    $id_foto = intval($_POST["id_foto"]);
      
      $query = "SELECT name FROM ".T_SLIDER_FOTO." WHERE id = '".$id_foto."'";
      $v = my_DB_QUERY($query , __LINE__ , __FILE__);
      $z = mysql_fetch_array($v);
      $name = $z["name"];

      if($file_name != "")
      { // nova fotla (starou mazeme)
        delete_foto($id_foto);
      }
      else
      {
        $file_name = $name;
      }
                  
      $query = "
      UPDATE ".T_SLIDER_FOTO."
      SET
      ".$set." ,
      name = '".$file_name."'
      WHERE id = '".$id_foto."'";
      my_DB_QUERY($query,__LINE__,__FILE__);
    }
    
    if(!empty($_POST["id"]))
    {
      $id = intval($_POST["id"]);
      
      if(!empty($_POST["name"]))
      { // jmeno slideru
        $query = "
        UPDATE ".T_SLIDER."
        SET
        name = '".mysql_real_escape_string(trim($_POST["name"]))."'
        WHERE id = '".intval($id)."'
        ";
  	    my_DB_QUERY($query,__LINE__,__FILE__); 
      }   
    }

	  Header("Location: ".$_SERVER['HTTP_REFERER']."");
	  exit;
  }
  
  $id = $_GET["id"];
  
  /* serazeni zaznamu */
  $query = "SET @line_num=0"; 
  my_DB_QUERY($query , __LINE__ , __FILE__);
  $query = "UPDATE ".T_SLIDER_FOTO." SET poradi=@line_num := @line_num +1 WHERE id_slider='".$id."' ORDER BY poradi";
  my_DB_QUERY($query , __LINE__ , __FILE__);
  /* serazeni zaznamu */


  $query = "
  SELECT id , name , title , text, link , poradi
  FROM ".T_SLIDER_FOTO."
  WHERE id_slider='".$id."'
  ORDER BY poradi";
  $v = my_DB_QUERY($query , __LINE__ , __FILE__);
  $fotky = '';
  
  while($z = mysql_fetch_array($v))
  {
    $id_foto = $z["id"];
    $name = $z["name"];
    $link = $z["link"];
    $poradi = $z["poradi"];

    $fotky .= '
      <div style="border:1px dotted lightgrey; padding:10px; margin-bottom:20px;">
        <div>
          <img src="http://'.$_SERVER['SERVER_NAME'].'/UserFiles/slider/'.$name.'" alt="'.$name.'" >
        </div>
        <br>
        <form action="" method="post" enctype="multipart/form-data">
          <div>
            <input type="hidden" name="id_foto" value="'.$id_foto.'" >
          </div>

          <table style="width:100%;">
            <tr>
              <td style="width:100px; vertical-align:top;">Upravit fotku</td>
              <td>
                <table>
                  <tr>
                    <td colspan="2">
                      <span style="float:right;">
                        <a href="'.MAIN_LINK.'&amp;f=slider&amp;id_foto='.$id_foto.'&amp;zmena_poradi=1&amp;up=1"><img src="icons/arr_down.gif" title="dolů" alt="dolů" ></a>
                        <a href="'.MAIN_LINK.'&amp;f=slider&amp;id_foto='.$id_foto.'&amp;zmena_poradi=1&amp;down=1"><img src="icons/arr_up.gif" title="nahoru" alt="nahoru" ></a>
                      </span>
                    </td>
                  </tr>
                  <tr><td style="width:100px;">Titulek: </td><td><input style="width:600px;" type="text" name="title" value="'.$z["title"].'"></td></tr>
                  <tr><td style="vertical-align:top;">Text: </td><td><textarea style="width:600px;" name="text" cols="10" rows="3">'.$z["text"].'</textarea></td></tr>
                  <tr><td>Odkaz: </td><td><input style="width:600px;" type="text" name="link" value="'.$link.'"></td></tr>
                  <tr>
                    <td colspan="2">
                      <input type="file" name="foto" >
                      <span style="float:right;">
                        <input class="butt_red" type="button" onClick="del_foto('.$id_foto.');" name="odstranit" value="Odstranit">
                        <input class="butt_green" type="submit" name="ulozit" value="Uložit">
                      </span>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </form>      
      </div>'; 
  }
  
  if(empty($fotky))
  {
    $fotky = 'Žádné fotky.<br><br>';
  }

  $query = "
  SELECT name FROM ".T_SLIDER." WHERE id = '".intval($id)."';
  ";
  $v = my_DB_QUERY($query , __LINE__ , __FILE__);
  $z = mysql_fetch_array($v);
  $name_slider = $z["name"];

  $nadpis = 'Editace slideru';
  $data = '
    <script type="text/javascript">  
	  function del() 
    { // odstraneni zaznamu
		  if(confirm("Opravdu chcete odstranit slider?"))
      { 
        location = "slider.php?delete='.$id.'"; 
      }
	  }  
	  
	  function del_foto(id_foto) 
    { // odstraneni fotky
		  if(confirm("Opravdu chcete odstranit fotku?"))
      { 
        location = "'.MAIN_LINK.'&f=slider&delimg="+id_foto;
      }
	  }    
    </script>
    
    <form method="post" action="" enctype="multipart/form-data">
      <div>
       <input type="hidden" name="id" value="'.$id.'" >
      </div>
      
      <div style="border:1px dotted lightgrey; padding:10px; margin-bottom:20px;">
      <table style="width:100%;">
        <tr>
          <td style="width:150px;">Název slideru</td>
          <td>
            <input style="width:400px;" type="text" name="name" value="'.$name_slider.'"> <span style="float:right; padding-top:10px;">'.DELETE_BUTTON.' '.SAVE_BUTTON.'</span>
          </td>
        </tr>
      </table>
      </div>

      <div style="border:1px dotted lightgrey; padding:10px; margin-bottom:20px;">
      <table style="width:100%;">
        <tr>
          <td style="width:100px; vertical-align:top;">Přidat fotku<br><span class="f10i">772 x 200px</span></td>
          <td>
            <table>
              <tr><td style="width:100px;">Titulek: </td><td><input style="width:600px;" type="text" name="title" value=""></td></tr>
              <tr><td style="vertical-align:top;">Text: </td><td><textarea style="width:600px;" name="text" cols="10" rows="3"></textarea></td></tr>
              <tr><td>Odkaz: </td><td><input style="width:600px;" type="text" name="link" value=""></td></tr>
              <tr><td colspan="2"><input type="file" name="foto"><input style="float:right;" class="butt_green" type="submit" name="pridat" value="Přidat"></td></tr>
            </table>
          </td>
        </tr>
      </table>
      </div>
    </form>
    <br>
    '.$fotky;
}
/* Editace slideru */


?>