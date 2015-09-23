<?php

// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto ködu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


/*
include_once "../_mysql.php";
include_once '../login.php';
include_once '../_functions.php';

include_once '../_nastaveni.php';
*/




function form($form_data,$dct) {
	
	/*// pouziti editoru pro popis kategorii
	include("./FCKeditor/fckeditor.php") ;
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// pro spravnou fci filemanageru nastavit $Config['UserFilesPath'] = cesta 
	// k adresari UserFiles v souboru 
	// FCKeditor/editor/filemanager/browser/default/connectors/php/config.php
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	
	// Automatically calculates the editor base path based on the _samples directory.
	// This is usefull only for these samples. A real application should use something like this:
	// $oFCKeditor->BasePath = '/FCKeditor/' ;	// '/FCKeditor/' is the default value.
	// $sBasePath = $_SERVER['PHP_SELF'] ;
	// $sBasePath = substr( $sBasePath, 0, strpos( $sBasePath, "_samples" ) ) ;
	
	// http://localhost/FCKeditor/editor/fckeditor.html?InstanceName=FCKeditor1&Toolbar=Default
	
	$sBasePath = dirname($_SERVER['PHP_SELF'])."/FCKeditor/";
	
	$oFCKeditor = new FCKeditor('descr');
	$oFCKeditor->BasePath = $sBasePath;
	$oFCKeditor->Value = $form_data['descr'];
	$editor = $oFCKeditor->Create();
	
	$editor = "
	<tr>
		<td colspan=\"2\">
			<br />".$dct['cat_f_text']."
			$editor
		</td>
	</tr>";
	// pouziti editoru pro popis kategorii
	*/
	
	
	
	
	if(empty($form_data['products'])) $form_data['products'] = 10; // pocet produktu na stranku
	
	
	// muzeme aplikovat nekolik zpusobu zarazovani kategorii vzajemne do sebe
	
	// 1) razeni kategorii vzajemne do sebe, libovolny pocet vnorenych urovni
	// $form_data['id_parent'] je select vytvoreny mimo fci, je do fce vlozen jiz jako vygenerovany prvek
	// - zatim neni uspokojive vyresena navigace ve verejne casti, proto zatim nebudeme pouzivat
	/*
	$zarazeni = "
	<tr>
		<td>".$dct['cat_f_zaradit']."</td>
		<td>".$form_data['id_parent']."</td>
	</tr>";
	*/
	// 1) konec
	
	
	// 2) pocitame pouze s jedinou urovni kategorii, zadne vnorene urovne neexistuji
	// takze vsechny kategorie maji id_parent nastaveno na 0
	/*
	$zarazeni = "
	<input type=\"hidden\" name=\"id_parent\" value=\"0\">";
	*/
	// 2) konec
	
	
	
	// 3) pocitame s max. dvemi urovnemi zarazovani
	// select si vytvorime primo zde ve fci
	
	// id name hidden descr lang products id_parent position
	$q = "SELECT id, name
	FROM ".T_FOTO_KATEG." WHERE ".SQL_C_LANG." 
	ORDER BY position, name, id ";
	$v = my_DB_QUERY($q,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		if($z['id'] == $_SESSION['last_id_parent'] || $z['id'] == $form_data['C_id_parent']) $selected = 'selected';
		else $selected = '';
		
		$res .= "
			<option value=\"".$z['id']."\" $selected>".$z['name']."</option>";
	
	}
	
	
	$select = "
		<select name=\"id_parent\" class=\"f10\" style=\"width: 100%;\">
			<option value=\"0\">".$dct['cat_nejvyssi_uroven']."</option>
			$res
		</select>";
	
	
	$zarazeni = "
	<tr>
		<td>".$dct['cat_f_zaradit']."</td>
		<td>".$select."</td>
	</tr>";
	// 3) konec
	
	
	
	
	
	
	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.name.value == \"\") { alert(\"".$dct['cat_js_odd']."\"); form1.name.focus(); return false; }
		else if (form1.products.value == \"\") { alert(\"".$dct['cat_js_poc']."\"); form1.products.focus(); return false; }
		".$form_data['js']."
		else return true;
	
	}
	
	
	function del() {
	
		if (confirm(\"".$dct['opravdu_odstranit']."\"))
			{ location = \"".$form_data['link']."&delete=".$form_data['id']."\"; }
	
	}
	// -->
	</SCRIPT>
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	<!--<input type=\"hidden\" name=\"lang\" value=\"".C_LANG."\">-->
	
	<table class='admintable nobg' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	<tr>
		<td>
			".$dct['cat_f_nazev']." <span class=\"f10\">
			".$dct['cat_f_max_255']."</span></td>
		<td width=\"330\">
			<input type=\"text\" name=\"name\" value=\"".$form_data['name']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>
			".$dct['cat_f_text']." <span class=\"f10\">
			".$dct['cat_f_max_255']."</span></td>
		<td width=\"330\">
			<input type=\"text\" name=\"text\" value=\"".$form_data['text']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>	
	
	
	$zarazeni
	
	
	
	
	<tr>
		<td>".$dct['cat_f_skryt']."</td>
		<td>
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" 
			".$form_data['hidden']."></td>
	</tr>
	
	
	
	
	<!--
	<tr>
		<td>".$dct['cat_f_pocet']."</td>
		<td>
			<input type=\"text\" name=\"products\" value=\"".$form_data['products']."\" 
			size=\"5\" class=\"f10\"></td>
	</tr>
	-->
	<input type=\"hidden\" name=\"products\" value=\"".$form_data['products']."\">
	
	
	
	
	<tr>
		<td>".$dct['cat_f_poradi']."</td>
		<td>
			<input type=\"text\" name=\"position\" value=\"".$form_data['position']."\" 
			size=\"5\" class=\"f10\"></td>
	</tr>
	
	
	$editor
	
	
	<tr>
		<td colspan=\"2\"><br><br>
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	</form>";
	
	return $form;

}





function category_pos() {

	// spousti se po kazde akci s daty kategorii
	// projde kategorie shopu v DB a vytvori souvisle rady z poradi 
	// v jednotlivych kategoriich a podkategoriich
	$posititon = 0;
	
	$query = "SELECT id FROM ".T_FOTO_KATEG." 
	WHERE ".SQL_C_LANG." ORDER BY position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		if ($last_parent == $z['id_parent']) {
			$posititon++;
		}
		else {
			$posititon = 1;
			$last_parent = $z['id_parent'];
		}
		
		$query2 = "UPDATE ".T_FOTO_KATEG." SET position = $posititon 
		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
		my_DB_QUERY($query2,__LINE__,__FILE__);
	
	}

}


function foto_pos($id_kateg) {

	// spousti se po kazde akci s daty kategorii
	// projde kategorie shopu v DB a vytvori souvisle rady z poradi 
	// v jednotlivych kategoriich a podkategoriich
	$posititon = 0;
	
	$query = "SELECT id FROM ".T_FOTO." 
	WHERE id_kateg=".$id_kateg." ORDER BY pos";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {

		$posititon++;


		$query2 = "UPDATE ".T_FOTO." SET pos = $posititon 
		WHERE id = ".$z['id']." ";
		my_DB_QUERY($query2,__LINE__,__FILE__);
		
		}

	}




function hidden_categories($id,$hidden) {

	// projde kategorie od zadaneho ID dolu, vyhleda vsechny 
	// podrizene urovne a nastavi jim parametr skryti/neskryti
	
	$query = "UPDATE ".T_FOTO_KATEG." SET hidden = $hidden 
	WHERE id = $id AND ".SQL_C_LANG." ";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	
	$query = "SELECT id FROM ".T_FOTO_KATEG." 
	WHERE id_parent = $id AND ".SQL_C_LANG." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		hidden_categories($z['id'],$hidden);
	}

}






// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST)) { // echo $_POST['id']."<br />";

if ($_POST["sect"]=='insfoto')
{


	if(!empty($_FILES['foto']['name'])) 
  {
	
	// uploadovany obrazek (original) je umisten do adresare $dir_orig
	// jsou vytvoreny kopie o max. povolenych rozmerech
	// potrebujeme-li zmenit nazev (napr. podle ID produktu, time() ...), uvedeme 
	// jej do promenne $nm, jinak zustane stejny
	
	
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
  global $WWW_root;
	$original_dir = $WWW_root.IMG_F_O; // cesta pro original (zaloha?)
	
	$kopie_dir[] = $WWW_root.IMG_F_S; // cesta pro nahled
	$kopie_dir[] = $WWW_root.IMG_F_M; // cesta pro detailni obrazek
	
	$w_max[] = 150; // max. sirka nahledu
	$w_max[] = 600; // max. sirka detailu
	
	$h_max[] = 120; // max. vyska nahledu
	$h_max[] = 500; // max. vyska detailu
	
	$komprese[] = ""; // komprese nahledu
	$komprese[] = ""; // komprese detailu
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	
	
	
	
	// zjistime rozmery originalu a typ souboru
	$r = getimagesize($_FILES['foto']['tmp_name']);
	
	$w_orig = $r[0]; // sirka originalu
	$h_orig = $r[1]; // sirka originalu
	$typ = $r[2]; // typ souboru

	
	
	// kontrola typu souboru - povolime jen jpg, png
	if($typ != 2 && $typ != 3) {
	
		$_SESSION['alert_js'] = "Nesprávný formát obrázku. ";
		$_SESSION['alert_js'] .= "Použijte formát JPG nebo PNG.\\n\\n";
		$_SESSION['alert_js'] .= "Nic nebylo uloženo.";
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	}
	else { // zjistime priponu
	
		$x1 = explode (".", $_FILES['foto']['name']); // roztrhame nazev souboru - delicem je tecka
		$x2 = count($x1) - 1; // index posledniho prvku pole
		$e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
	

	$query = "INSERT INTO ".T_FOTO." VALUES(0,".$_POST['id_kateg'].",'".$e."','".$_POST['name']."', 99999)";
	my_DB_QUERY($query,__LINE__,__FILE__);
  $nm = mysql_insert_id($conn);
  $_SESSION['alert_js'] = $dct['zaznam_ulozen'];
	}
	
	
	if(!empty($nm)) $file_name = $nm.".".$e; // menime nazev obrazku
	else $file_name = $_FILES['foto']['name']; // nazev obrazku zustava stejny
	
	
	$original = $original_dir.$file_name;
	
	
	
	// umistime original souboru
	// move_uploaded_file($_FILES['foto']['tmp_name'],$original);
	copy($_FILES['foto']['tmp_name'],$original);
	chmod($original,0777);
	
	
	
	for($x = 0; $x < count($kopie_dir); $x++) {
	
		// kontrola rozmeru:
		// b = oba, w = sirka, h = vyska
		
		$sledovany_rozm = "b";
		
		img_resize($original,$kopie_dir[$x].$file_name,$w_max[$x],$h_max[$x],$komprese[$x],$sledovany_rozm);
	
	}
	 
   $_SESSION['last_id_kateg_insert'] = $_POST['id_kateg'];
	}
  else
  {

		
  }
   foto_pos($_POST['id_kateg']);
}

if ($_POST["sect"]=='new_name')
{
 
  $kn = trim($_POST['name_kateg']);
 if (strlen($kn)>0)
 {
	$query = "UPDATE ".T_FOTO_KATEG." SET name='".$kn."'
  WHERE id=".$_POST['id_kat']." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
 }
 else
 {
  $_SESSION['alert_js'] = "Musíte zadat nový název kategorie.";
 }	
	
 	$_SESSION['alert_js'] = "Název fotogalerie byl změněn.";
  Header("Location: ".$_SERVER['HTTP_REFERER']);
  exit;
}

// KATEGORIE
if ($_POST["sect"]=='ins_kateg')
{
/*
	$par = trim($_POST['id_parent']); // ID nadrazene kategorie
*/
	
	// text kategorie
	$descr = trim($_POST['descr']);


/*
	

	if(!empty($_POST['id'])) {
	
		// kontrola zda nezarazujeme kategorii samu do sebe 
		// nebo do sobe podrizene kategorie
		// jisteno pred odeslanim pomoci js, ale pro pocit...
		unset($ch_cat);
		children_in_category($_POST['id'],$ch_cat);
		
		if(in_array($par, $ch_cat)) {
		
			$trans = array ("\\n" => "\n"); // 
			$_SESSION['alert'] = strtr($dct['chld'], $trans);
			
			Header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		
		}
	
	}
	
*/	
	
	

	// nastavime pozice
	$pos = trim($_POST['position']);
	if (empty($pos))
  {
  
  $q1 = 'SELECT (MAX(position)+1) FROM '.T_FOTO_KATEG.' WHERE lang='.C_LANG;
	// $v1 = my_DB_QUERY($q1,__LINE__,__FILE__);
  $pos = (int) mysql_result(mysql_query("$q1"), 0); 
  
  } 
  
  
  
	
	

	$query = "SELECT id, position FROM ".T_FOTO_KATEG." 
	WHERE 
	(position = $pos OR position > $pos) 
	AND ".SQL_C_LANG." 
	ORDER BY position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) 
  {
	
		$query2 = "UPDATE ".T_FOTO_KATEG." SET 
		position = (position + 1)
		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
		my_DB_QUERY($query2,__LINE__,__FILE__);
	
	}


	
	// nastaveni skryti kategorie a ji podrizenych
	$hidden = $_POST['hidden'];
	if ($hidden != 1) $hidden = 0;
	
/*	
	
	if(!empty($_POST['id'])) { // aktualizace
	
		$id = $_POST['id'];
		
		$query = "UPDATE ".T_CATEGORIES." SET 
		name = '".trim($_POST['name'])."', 
		hidden = $hidden, 
		descr = '$descr', 
		products = ".trim($_POST['products']).", 
		id_parent = $par, 
		position = $pos 
		WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
		
		$back = $_SERVER['HTTP_REFERER'];//MAIN_LINK."&f=categories&a=list";
	
	} else { // novy zaznam
*/	
		$query = "INSERT INTO ".T_FOTO_KATEG."  
		VALUES(0, '".trim($_POST['name'])."', $hidden, '".trim($_POST['text'])."', 
		'".C_LANG."', $pos )";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$id = mysql_result($v, 0, 0);
		
		// prednastavime nadrazenou kategorii pri vkladani novych kategorii
		$_SESSION['last_id_kateg_insert'] = $id;
		
		$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
		

/*	
	}

// category_pos();
*/
}	

if ($_POST["sect"]=='update_foto')
{

$id_kateg = $_POST["id_kateg"];

  foreach ($_POST["name"] as $id_akt => $name) 
  {
    $name = trim($name);
    $pos  = (int)trim($_POST["pos"]["$id_akt"]);
    		
  	$query = "UPDATE ".T_FOTO." SET name='".$name."', pos=".$pos."  WHERE id = ".$id_akt." ";
  	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  	
  }




 foto_pos($id_kateg);

}	
	
	// hidden_categories($id,$hidden);
	
	$back = $_SERVER['HTTP_REFERER'];	
	
	
	// exit;
	
	Header("Location: ".$back);
	exit;
}
// *****************************************************************************
// ulozeni zanamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// seznam kategorii
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_seznam_foto'];

	$q = "SELECT id, name
	FROM ".T_FOTO_KATEG."
  WHERE ".SQL_C_LANG."
	ORDER BY position, name, id ";
	$v = my_DB_QUERY($q,__LINE__,__FILE__);
	
	$select_item='';
  
  while ($z = mysql_fetch_array($v)) {
	
		if($z['id'] == $_SESSION['last_id_kateg_insert']) $selected = 'selected';
		else $selected = '';
		
		$select_item .= "<option value=\"".$z['id']."\" $selected>".$z['name']."</option>";
	
	}
	
	
	$select = "<select name=\"id_kateg\" class=\"f10\" style=\"width: 100%;\">
	 	         $select_item	
		         </select>";

  $form_ins_foto = '
  <form name="ins_foto" action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="sect" value="insfoto" />
  <h3 style="margin-bottom: 4px;">Přidat fotografii:</h3>
  <table class="admintable nobg">
  <tr><td style="width: 130px;">Popis fotografie:</td><td><input type="text" name="name" class="f10" style="width: 100%;" /></td></tr>
  <tr><td>Vložit do fotogalerie:</td><td>'.$select.'</td></tr>
  <tr><td>Vyberte fotografii:</td><td><input type="file" name="foto" class="f10" size="50" style="width: 100%;" /></td></tr>
  <tr><td>&nbsp;</td><td>'.SAVE_BUTTON.'</td></tr>
  </table>
  </form><br /><br />
  ';
  
  $hidden_style = "style=\"color: #939393;\"";
  
	// id name hidden descr lang products id_parent position
	$query = "SELECT COUNT(f.id) AS pocet_foto, fk.id, fk.name, fk.hidden, fk.lang, fk.position 
	FROM ".T_FOTO_KATEG." fk 
  LEFT JOIN  ".T_FOTO." f ON fk.id=f.id_kateg
  WHERE ".SQL_C_LANG."
  GROUP BY fk.id 
	ORDER BY position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$res='';
	
	while ($z = mysql_fetch_array($v)) 
  {
	
	$id = $z['id'];
  $pocet_foto = $z['pocet_foto'];
  
  	
   	$res .="
			<tr ".TABLE_ROW.">
				<td class=\"td1\" $hidden_style nowrap> 
          <form name=\"ins_foto\" action=\"\" method=\"post\" style=\"display:inline;\">
          <input type=\"hidden\" name=\"sect\" value=\"new_name\" />
          <input type=\"hidden\" name=\"id_kat\" value=\"".$id."\" />
          <input type=\"text\" name=\"name_kateg\"class=\"f10\" value=\"".$z['name']."\" size=\"28\" />
          <input type=\"submit\" value=\"Upravit název\" class=\"butt_green\">
          </form>
          </td>
				
				<td width=\"160\" class=\"td2\">
					".ico_edit(MAIN_LINK."&f=foto&a=edit&id=$id",$dct['cat_cat_edit'])."&nbsp;zobrazit fotografie (".$pocet_foto.")</td>
				<td width=\"15\" class=\"td2\">
					".ico_delete(MAIN_LINK."&f=foto&a=del&s=kateg&id=$id",$dct['mn_fotog_vymazat'],MAIN_LINK )."</td>					
			</tr>";
	
	}

	
/* MZ
	// vygenerujeme pole s kategoriemi
	if(empty($cat_array)) {
	
		$cat_array = array();
		categories_array($parent_id=0,$cat_array,$level=0);
	
	}
*/	
	
/* MZ
	
	if(!empty($cat_array)) {
	
		reset ($cat_array);
		while ($p = each($cat_array)) {
		
			list ($level,$position,$par_id,$name,$hidden,$lang,$id) = explode ("|", $p['value']);
			
			// pokud je nastavena kat. jako skryta, projdeme vnorene kategorie 
			// a nastavime je take jako skryte
// 			if ($hidden == 1) $hidden_array[$id] = $id;
			
			
			// odsazeni podle levelu
			$indent = "";
			for ($i = 0; $i < $level; $i++) {
				$indent .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			if ($level > 0) $indent = $indent;
			else $indent = "";
			// odsazeni podle levelu
			
			
			
			
			// nasleduje indikace skrytych kategorii, vcetne podkategorii 
			// (u podkategorii bez ohledu na skutecne nastaveni)
			
			// styl pro skryte polozky
			$hidden_style = HIDDEN_STYLE;
			// prednastavena hodnota pro nastaveni zobrazovani primo 
			// ze seznamu kategorii
			$set_hidden = 0;
			
			// zacatek skrytych kategorii (prvni v hierarchii)
			if ($hidden == 1 && $par_id != $h_parent[$par_id]) {
				$h = 1;
				$h_parent[$id] = $id; // children are hidden
				$alt_h = $dct['cat_zobrazeni_nepovoleno']." - ".$dct['cat_povolit_zobrazeni'];
			}
			// dalsi (vnorene) skryte kategorie
			else if ($par_id == $h_parent[$par_id]) {
				$h = 3;
				$h_parent[$id] = $id; // children are hidden
				$alt_h = $dct['cat_zobrazeni_nepovoleno'];
			}
			// neskryte kategorie
			else {
				$h = 0;
				$alt_h = $dct['cat_zobrazeni_povoleno']." - ".$dct['cat_zakazat_zobrazeni'];
				$hidden_style = "";
				$set_hidden = 1;
			}
			
			
			
			$h_img = "<img src=\"./icons/hidden_$h.gif\" alt=\"$alt_h\" 
				title=\"$alt_h\" border=\"0\" height=\"10\" width=\"13\" align=\"absmiddle\">";
			
			
			if ($h == 1 || $h == 0) { // neni skryta
			

				$h_img = "
				<a href=\"".MAIN_LINK."&f=categories&a=hidden&id=$id&hidden=$set_hidden\">$h_img</a>";
			
			}
			else if ($h == 3) { // je skryta
			
				$h_img = $h_img;
			
			}
			
			// [$id]
			$res .= "
			<tr ".TABLE_ROW.">
				<td class=\"td1\" $hidden_style nowrap>
					$indent $h_img ".$name."</td>
				
				<td width=\"15\" class=\"td2\">
					".ico_edit(MAIN_LINK."&f=categories&a=edit&id=$id",$dct['cat_cat_edit'])."</td>
			</tr>";

		}
		
	
		
		

	
	}
*/	
	
	
		if (!empty($res)) {
		
			$data = "
			".SEARCH_PANEL."
			$form_ins_foto
			<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
			$res
			</table>";
		
		}	
	
	
	
	if(empty($data)) $data = "Žádný záznam.";

}
// *****************************************************************************
// seznam kategorii
// *****************************************************************************


if($_GET['a'] == "del") {
global $WWW_root;
if ($_GET['s']=='kateg')
{

	$query = "
  SELECT f.id, f.img
  FROM ".T_FOTO." f
  WHERE id_kateg=".$_GET['id']." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  // $_SESSION['alert'] .= $query;
	while ($z = mysql_fetch_array($v)) 
  {

	$orig   = $WWW_root.IMG_F_O.$z["id"].'.'.$z["img"]; // cesta pro original
	@unlink($orig);
  // $_SESSION['alert'] .= $orig.'<br />';
	
  $detail = $WWW_root.IMG_F_M.$z["id"].'.'.$z["img"]; // cesta pro detail
	@unlink($detail);
	// $_SESSION['alert'] .= $detail.'<br />';
	
  $nahled = $WWW_root.IMG_F_S.$z["id"].'.'.$z["img"]; // cesta pro nahled
  @unlink($nahled);
  // $_SESSION['alert'] .= $nahled.'<br />';
  
	}
	

  $query = "DELETE FROM ".T_FOTO_KATEG." WHERE id=".$_GET['id']." AND ".SQL_C_LANG." ";
  // $_SESSION['alert'] .= $query.'<br />';
  my_DB_QUERY($query,__LINE__,__FILE__);
  
  $query = "DELETE FROM ".T_FOTO_ZBOZI." WHERE id_kateg=".$_GET['id']." ";
  // $_SESSION['alert'] .= $query.'<br />';
  my_DB_QUERY($query,__LINE__,__FILE__);
  
  $query = "DELETE FROM ".T_FOTO_CONT_PAGES." WHERE id_kateg=".$_GET['id']." ";
  // $_SESSION['alert'] .= $query.'<br />';
  my_DB_QUERY($query,__LINE__,__FILE__);
	
  $query = "DELETE FROM ".T_FOTO." WHERE id_kateg=".$_GET['id']." ";
  // $_SESSION['alert'] .= $query.'<br />';
   my_DB_QUERY($query,__LINE__,__FILE__);

  
  $_SESSION['alert_js'] = $dct['zaznam_odstranen'];
	
}

if ($_GET['s']=='foto')
{

	$query = "
  SELECT f.id, f.img
  FROM ".T_FOTO." f
  WHERE id=".$_GET['id']." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  // $_SESSION['alert'] .= $query;
	while ($z = mysql_fetch_array($v)) 
  {
   $id = $z["id"];

	$orig   = $WWW_root.IMG_F_O.$id.'.'.$z["img"]; // cesta pro original
	@unlink($orig);
  // $_SESSION['alert'] .= $orig.'<br />';
	
  $detail = $WWW_root.IMG_F_M.$id.'.'.$z["img"]; // cesta pro detail
	@unlink($detail);
	// $_SESSION['alert'] .= $detail.'<br />';
	
  $nahled = $WWW_root.IMG_F_S.$id.'.'.$z["img"]; // cesta pro nahled
  @unlink($nahled);
  // $_SESSION['alert'] .= $nahled.'<br />';

  }

  $query = "DELETE FROM ".T_FOTO." WHERE id=".$id." ";
  // $_SESSION['alert'] .= $query.'<br />';
   my_DB_QUERY($query,__LINE__,__FILE__);


	$_SESSION['alert_js'] = $dct['zaznam_odstranen'];
	
}



  my_OPTIMIZE_TABLE(T_FOTO);
  my_OPTIMIZE_TABLE(T_FOTO_KATEG);
  my_OPTIMIZE_TABLE(T_FOTO_ZBOZI);
  my_OPTIMIZE_TABLE(T_FOTO_CLANEK);

	$back = $_SERVER['HTTP_REFERER'];	
	Header("Location: ".$back);
	exit;


}






// *****************************************************************************
// editace kategorie (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = $dct['cat_cat_edit'];
	
	
	/*
	// vygenerujeme pole s kategoriemi
	if(empty($cat_array)) {
		$cat_array = array();
		categories_array($parent_id=0,$cat_array,$level=0);
	}*/
	
	$data .='<form action="" method="post">
  <input type="hidden" name="sect" value="update_foto" />
  <input type="hidden" name="id_kateg" value="'.$_GET['id'].'" />
  
  <div>';	
	
	$query = "SELECT * FROM ".T_FOTO." 
	WHERE id_kateg = ".$_GET['id']." order by pos";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while ($z = mysql_fetch_array($v)) {
	
  $data .= '
  <div style="float: left; width: 190px; height: 210px; margin: 10px; border: 1px solid #aaa; position: relative;">
  <table cellpading="0" cellspacing="0" style="width: 100%; height: 170px;"><tr><td style="text-align: center; vertical-align: center;">
  <img src="/'.IMG_F_S2.$z["id"].'.'.$z["img"].'" />
  <a href="'.MAIN_LINK.'&f=foto&a=del&s=foto&id='.$z["id"].'" title="'.$dct['mn_foto_vymazat'].'"><img src="/admin/icons/ico_delete.gif" style="position: absolute; top :148px; right: 1px; border: 0px;" /></a>
  </td></tr></table>
  <div style="height: 40px; background-color: #eee;">
  
  <div style="padding: 4px; padding-top: 12px; text-align: center;">
  <input type="text" name="name['.$z["id"].']" value="'.$z["name"].'" class="f10" style="width: 80%;" />
  <input type="text" name="pos['.$z["id"].']" value="'.$z["pos"].'" class="f10" style="width: 16%;" />
  </div>
  
  </div>
  
  </div>';
//		$form_data['id'] = $_GET['id'];
//		$form_data['link'] = MAIN_LINK."&f=categories";
//		$form_data['deletebutton'] = DELETE_BUTTON;
	
	}
	
  
  $data .='<div class="clear"></div>
  
  <div style="text-align: center;clear:both"><br ú><br /><input type="submit" value="Uložit změny popisů a pořadí" class="butt_green"></dvi>
  
  </form></div>';	
	// $data = $dct['zaznam_nenalezen'];
	
	
	
//	$data = $addRecord.$data;

}
// *****************************************************************************
// editace kategorie (form)
// *****************************************************************************










// *****************************************************************************
// pridani kategorie (form)
// *****************************************************************************
if($_GET['a'] == "add") {

  $form_data=null;

	$nadpis = $dct['mn_pridat_foto'];
	
	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.name.value == \"\") { alert(\"".$dct['cat_js_odd']."\"); form1.name.focus(); return false; }
		else if (form1.products.value == \"\") { alert(\"".$dct['cat_js_poc']."\"); form1.products.focus(); return false; }
		".$form_data['js']."
		else return true;
	
	}
	
	
	function del() {
	
		if (confirm(\"".$dct['opravdu_odstranit']."\"))
			{ location = \"".$form_data['link']."&delete=".$form_data['id']."\"; }
	
	}
	// -->
	</SCRIPT>
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"sect\" value=\"ins_kateg\">
	
	<!--<input type=\"hidden\" name=\"lang\" value=\"".C_LANG."\">-->
	
	<table class='admintable nobg' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	<tr>
		<td>
			".$dct['cat_f_nazev']." <span class=\"f10\">
			".$dct['cat_f_max_255']."</span></td>
		<td width=\"330\">
			<input type=\"text\" name=\"name\" value=\"".$form_data['name']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>
			".$dct['cat_f_text']." <span class=\"f10\">
			".$dct['cat_f_max_255']."</span></td>
		<td width=\"330\">
			<input type=\"text\" name=\"text\" value=\"".$form_data['text']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>	
	
  <!--<tr>
		<td>".$dct['cat_f_skryt']."</td>
		<td>
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" 
			".$form_data['hidden']."></td>
	</tr>-->
	
	
	
	<!--<tr>
		<td>".$dct['cat_f_poradi']."</td>
		<td>
			<input type=\"text\" name=\"position\" value=\"".$form_data['position']."\" 
			size=\"5\" class=\"f10\"></td>
	</tr>-->
	
	
	<tr>
		<td colspan=\"2\"><br><br>
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	</form>";	
	

	
	$data = $form;
  // $data = form($form_data,$dct);
	
	unset($_SESSION['last_id_parent']);

}
// *****************************************************************************
// pridani kategorie (form)
// *****************************************************************************









// *****************************************************************************
// skryta/neskryta kategorie
// *****************************************************************************
if($_GET['a'] == "hidden") {

	$hidden = $_GET['hidden'];
	if ($hidden != 1) $hidden = 0;
	
// 	hidden_categories($_GET['id'],$hidden);
	
	$_SESSION['alert_js'] = $dct['zaznam_upraven'];
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// skryta/neskryta kategorie
// *****************************************************************************





/*
include_once './_template.php';
*/







?>
