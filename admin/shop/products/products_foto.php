<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Administrace - <?php echo $_SERVER['SERVER_NAME'];?></title>
	<meta http-equiv="pragma" content="no-cache" >
	<meta http-equiv="cache-control" content="no-cache, must-revalidate" >
	<meta http-equiv="expires" content="0" >
	<meta http-equiv="last-modified" content="" >

	<meta name="robots" content="noindex,nofollow" >
	<meta name="robots" content="noarchive" >

  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="blazevsky, info@netaction.cz" >
	
  <link href="./css/style.css" rel="stylesheet" type="text/css" />
  <link href="/admin/css/form.css" rel="stylesheet" type="text/css" />
  
  <?php
	// hlaseni do js alertu
	/**/
	if(!empty($_SESSION['alert_js'])) {
	
		echo "
		<script language=\"javascript\">
		function show_alert() {
			alert('".$_SESSION['alert_js']."');
		}
		</script>";
		
		$onload = "onload=\"show_alert()\"";
		
		unset($_SESSION['alert_js']);
	
	}
?>
  
  
</head>

<body>

<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.

define('VSTUP' , 'admin');


include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_functions.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/_security.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_functions.php');


function select_order($id)
{
  $query = "SELECT position FROM ".T_FOTO_ZBOZI." WHERE id = ".$id;
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);

  return $z['position'];
}


function souvisla_rada($id_good)
{
  $query = "SET @line_num = 0";
  my_DB_QUERY($query,__LINE__,__FILE__);

  $query = "UPDATE ".T_FOTO_ZBOZI." SET position = @line_num := @line_num + 1 WHERE id_good = '".$id_good."' ORDER BY position";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  return 1;
}


/* zmena poradi fotky smerem nahoru */
function move_up($id_foto, $id_good)
{ // prehozeni poradi s vedlejsi fotkou
  $position = select_order($id_foto); // pozice aktualni fotky
  $new_position = $position - 1;

  $query = "UPDATE ".T_FOTO_ZBOZI." SET position = '".$position."' WHERE id_good = '".$id_good."' AND position = '".$new_position."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $query = "UPDATE ".T_FOTO_ZBOZI." SET position = '".$new_position."' WHERE id_good = '".$id_good."' AND id = '".$id_foto."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  souvisla_rada($id_good); // seradi sloupec pösition u fotek daného produktu

  return 1;
}


/* zmena position fotky smerem dolu */
function move_down($id_foto, $id_good)
{ // prehozeni poradi s vedlejsi fotkou
  $position = select_order($id_foto); // pozice aktualni fotky
  $new_position = $position + 1;

  $query = "UPDATE ".T_FOTO_ZBOZI." SET position = '".$position."' WHERE id_good = '".$id_good."' AND position = '".$new_position."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $query = "UPDATE ".T_FOTO_ZBOZI." SET position = '".$new_position."' WHERE id_good = '".$id_good."' AND id = '".$id_foto."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  souvisla_rada($id_good); // seradi sloupec pösition u fotek daného produktu

  return 1;
}

// zmena poradi obrazku
if(isset($_GET['a']) AND $_GET['a'] == 'poradi')
{ // zmena poradi
  if(isset($_GET['id']) AND !empty($_GET['id']) AND isset($_GET['id_good']) AND !empty($_GET['id_good']))
  { // id musi byt zadane
    if(isset($_GET['smer']) AND $_GET['smer'] == 'up')
    { // posun o pozici nahoru
      move_up($_GET['id'] , $_GET['id_good']);
    }
    if(isset($_GET['smer']) AND $_GET['smer'] == 'down')
    { // posun o pozici dolu
      move_down($_GET['id'] , $_GET['id_good']);
    }
  }
  
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}


// odstraneni obrazku
if(isset($_GET['a']) AND $_GET['a'] == 'delete')
{ // zmena poradi
  if(isset($_GET['id']) AND !empty($_GET['id']))
  { // id musi byt zadane
    delete_product_foto($_GET['id']);
  }

	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}


if(!empty($_POST) AND isset($_POST['ulozit']) AND !empty($_POST['ulozit']) AND isset($_POST['title']) AND !empty($_POST['title']) AND count($_POST['title']) > 0)
{ // ulozeni popisu k fotkam
  foreach($_POST['title'] as $id => $title)
  {
    $title = mysql_real_escape_string(trim($title));

    $query = "
    UPDATE ".T_FOTO_ZBOZI." SET
    title = '".$title."'
    WHERE id = '".intval($id)."'
    ";
    
    my_DB_QUERY($query,__LINE__,__FILE__);
  }

	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}


if(!empty($_POST) AND isset($_POST['upload']) AND $_POST['upload'] == "Nahrát")
{ // fotografie ulozeni
  $ID = intval($_POST['id']);
  echo "tady";
  echo "<br />".$ID;
 
  // zjistime poradi posledniho obrazku
  $query = "
  SELECT MAX(position) as max_position FROM ".T_FOTO_ZBOZI."
  WHERE id_good = '".$ID."'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);
  $max_position = $z['max_position'];

  echo "<br />".$max_position;
  
  if(empty($max_position))
  { // zatim nebyl vlozen obrazek k produktu
    $max_position = 0;
  }

  $position = $max_position + 1; // obrazek ukladame na posledni pozici

  $error = array();

  // Obrázky z URL.
  if(isset($_POST["foto_url"]) AND !empty($_POST["foto_url"]))
  { // Nahrání fotek z url do eshopu.
    $img_url = explode("," , trim($_POST["foto_url"])); 

    foreach($img_url as $url)
    {
      $url = trim($url);
      $path_parts = pathinfo($url);
      if(!isset($path_parts["extension"]))
      { // Chybí přípona (nepovolíme kopírování).
        $error[] = 'Foto z URL "'.$url.'" nemá příponu (podporované formáty jsou JPG a PNG).';
        continue;
      }

      $img = img_upload_new($url);

      if($img != -1)
      { // obrazek se podarilo nahrat.
        $query = "
        INSERT INTO ".T_FOTO_ZBOZI."
        SET
        name = '".mysql_real_escape_string($img)."',
        name_original = '".mysql_real_escape_string($url)."',
        id_good = '".$ID."',
        position = '".$position."'
        ";
        my_DB_QUERY($query,__LINE__,__FILE__);

        $position++;
      }
      else
      { // Chybné URL.
        $error[] = 'Foto z URL "'.$url.'" se nepodařilo nahrát.';
      }
    }
  }

  // Obrázky z PC.
  $pocet_souboru = count($_FILES['foto']['name']); 
  if($pocet_souboru > 0)
  {
  
    echo "<br />".$pocet_souboru;

    for($index_souboru = 0; $index_souboru < $pocet_souboru; $index_souboru++)
    { // multiupload
      echo "<br />multiupload";
      
      if(empty($_FILES['foto']['name'][$index_souboru])) continue;

      $soubor = explode ('.' , $_FILES['foto']['name'][$index_souboru]); // rozdelime nazev souboru na jemno a priponu (delicem je tecka)

      $soubor_array['name'] = $_FILES['foto']['name'][$index_souboru];
      $soubor_array['tmp_name'] = $_FILES['foto']['tmp_name'][$index_souboru];
       
      print_r($soubor_array);

      $img_name = img_upload_new($soubor_array); // nahrani obrazku na server a uprava do pozadovanych velikosti
      echo "<br />".$img_name;
      //exit;
      
      
      if($img_name != -1)
      { // nahrani probehlo v poradku
        // ulozeni obrazku do databaze
        $query =
        "INSERT INTO ".T_FOTO_ZBOZI." SET
        id_good = '".$ID."',
        name = '".$img_name."',
        position = '".$position."'
        ";
        my_DB_QUERY($query,__LINE__,__FILE__);
      }
      else
      { // chyba pri nahravani fotek
        $error[] = 'Foto s názvem "'.$soubor_array['name'].'" se nepodařilo nahrát.';
      }

      $position++;
    }
  }

  if(isset($error) AND count($error) > 0)
  {
    $_SESSION["admin"]["error"] = "";
    while(list( , $value) = each($error))
    {
      $_SESSION["admin"]["error"] .= $value . '<br />';
    }
  }

  Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}

if(isset($_GET['id']) AND !empty($_GET['id']))
{

  //echo "jsme tady";
  $ID = intval($_GET['id']);
  $foto = get_product_fotos($ID , TRUE);
//   print_r($foto);
//   exit;
  $fotos = '';
  $count_foto = count($foto); // Počet fotek u produktu.
  
  if(!empty($foto))
  {
    for($index = 0; $index < $count_foto; $index++)
    {
      $fotos .= '
      <div class="foto">
        <div class="img">
          <img src="'.$foto[$index]['small'].'">
        </div>

        <div class="title">
          <input type="text" name="title['.$foto[$index]['id'].']" value="'.$foto[$index]['title'].'">
        </div>
        
        <div style="float:left;">
          <a href="#" onclick="return del('.$foto[$index]['id'].');"><img class="button" src="./img/delete.png" alt="Odstranit" title="Odstranit"></a>
        </div>
        
        <div style="float:right;">
          '.(
              ($index == 0) // První fotka.
              ? ''
              : '<a href="products_foto.php?a=poradi&amp;id='.$foto[$index]['id'].'&amp;id_good='.$ID.'&amp;smer=up"><img class="button" src="./img/previous.png" alt="<"></a>'
          ).'
          '.(
              ($index == $count_foto - 1) // Poslední fotka.
              ? ''
              : '<a href="products_foto.php?a=poradi&amp;id='.$foto[$index]['id'].'&amp;id_good='.$ID.'&amp;smer=down"><img class="button" src="./img/next.png" alt=">"></a>'
          ).'
        </div>
      </div>
      ';
    }
  }

  if(isset($_SESSION["admin"]["error"]))
  { // Zobrazení chyby.
    if(!empty($_SESSION["admin"]["error"]))
    {
      $error = '
      <div class="error">
        '.$_SESSION["admin"]["error"].'
      </div>
      ';
    }

    unset($_SESSION["admin"]["error"]);
  }

  $form_foto = '
  '.((isset($error) AND !empty($error)) ? $error : '').'

	<script language="javascript" type="text/javascript">
	function del(id)
  {
		if(confirm("Odstranit fotku?"))
		{
      location = "products_foto.php?a=delete&id=" + id;
    }
	}
	</script>

  <form name="foto" action="" method="post" enctype="multipart/form-data">
    <div>
      <input type="hidden" name="id" value="'.$ID.'" />
    </div>

    <h2>Nahrát fotky</h2>
    <table style="width:100%;">
      <tr><td style="width:120px;">Z počítače: </td><td><input type="file" name="foto[]" multiple="" /></td></tr>
      <tr><td>Z url adresy: </td><td><input style="width:96%;" type="text" name="foto_url" value="" /> '.get_info('Více odkazů oddělte čárkou.').'</td></tr>
      <tr><td></td><td style="text-align:right;"><input type="submit" name="upload" value="Nahrát" /></td></tr>
    </table>

  </form>

  <form name="foto_title" action="" method="post" enctype="multipart/form-data">
    '.$fotos.'
    <div class="clear"></div>
    <br>
    <br>
    <div style="text-align:right;">
      <input class="butt_green" type="submit" name="ulozit" value="Uložit popisy" />
    </div>
  </form>
  ';

  echo $form_foto;
}
else
{
  echo "Nejdříve produkt uložte.";
}
?>

</body>
</html>
