<?php

$query = "SELECT id FROM ".T_AKCE." WHERE ( name LIKE '_NOVE_IMPORTOVANE_POLOZKY' OR name LIKE '_ODSTRANENE_IMPORTOVANE_POLOZKY' OR name LIKE '_IGNOROVANE_IMPORTOVANE' )AND ".SQL_C_LANG." ";
// echo $query;
$v = my_DB_QUERY($query,__LINE__,__FILE__);
while ($z = mysql_fetch_array($v))
{
  if ($z["id"]>0)
  {
    $id_nezmenit[] = $z["id"]; 
  }
}
// print_r($id_nezmenit);
$id_nezmenit[] = '0';

if (count($id_nezmenit)>0)
{
$SQL_ADD_nezmenit = ' IN('.implode(',', $id_nezmenit).') ';
}



$addRecord = "<a href=\"".MAIN_LINK."&f=akce&a=add\">Přidat akci</a><br /><br />";

//vložení SEO
include ('seo.php');








function form($form_data,$dct) {

if(empty($form_data)){
  $form_data['id']='';
  $form_data['C_id_parent']='';
  $form_data['name']='';
  $form_data['hidden']='';
  $form_data['export']='';
  $form_data['js']='';
  $form_data['link']='';
  $form_data['position']='';
  $form_data['deletebutton']='';
  $form_data['descr']='';
}

	
	$editor="
      <textarea name='descr'>".$form_data['descr']."</textarea>
          
      <script type='text/javascript'>
                //<![CDATA[
                CKEDITOR.replace('descr', {
                	height: '400px'
                });
                //]]>
      </script>
  ";
	
	$editor = "
	<tr>
		<td colspan=\"3\">
			<br />".$dct['cat_f_text']."
			$editor
		</td>
	</tr>";

	
	
	
	if(!empty($form_data['id'])) {
	
		$query = "SELECT id_good FROM ".T_GOODS_X_AKCE." 
		WHERE id_cat = ".$form_data['id']."";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
		
			$x_selected[$z['id_good']] = 'selected';		
		}
	}
	
	// id id_cat name img text hidden akce cena dph lang kod id_vyrobce anotace dop_cena
	$query = "SELECT id, kod, name FROM ".T_GOODS."
	WHERE ".T_GOODS.".".SQL_C_LANG." ORDER BY name";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$select_P=$select_S='';
	
  while ($z = mysql_fetch_array($v)) {
	
		$xid = $z['id'];
		
		if(empty($x_selected[$xid]))$x_selected[$xid]='';
		if(empty($S_selected[$xid]))$S_selected[$xid]='';
		
		$select_P .= "
		<option value=\"$xid\" ".$x_selected[$xid].">".$z['name']." ".$z['kod']."</option>";// - pribuzne
		
		$select_S .= "
		<option value=\"$xid\" ".$S_selected[$xid].">".$z['name']." ".$z['kod']."</option>";// - skupiny
	
	}
	
	$dod = "
		
		<span class=\"f10i\">
			více souborů lze označit přidržením klávesy Shift nebo Ctrl a zároveň 
			kliknutím myši na název souboru.
		</span>";
	
	
	if (!empty($select_P)) {
	
		$select_P = "
		<select name=\"akcni[]\" class=\"f10 adminselect\" size=\"20\" multiple=\"multiple\">
		<option value=\"NO\">žádné</option>
		$select_P
		</select>
		
		$dod";
	
	} else $select_P = "žádné produkty nebyly v databázi nalezeny.";
	
	
	if (!empty($select_S)) {
	
		$select_S = "
		<select name=\"skupina[]\" class=\"f10\" size=\"6\" multiple=\"multiple\" style=\"width: 100%;\">
		<option value=\"NO\">žádné</option>
		$select_S
		</select>
		
		$dod";
	
	} else $select_P = $select_S = "žádné produkty nebyly v databázi nalezeny.";
	
	
	
	// seznam produktu pro prirazeni jako pribuzne
	// **********************************************************
	
	
	
	
	if(empty($form_data['products'])) $form_data['products'] = 10; // pocet produktu na stranku
	
	
	// muzeme aplikovat nekolik zpusobu zarazovani kategorii vzajemne do sebe
	
	// 1) razeni kategorii vzajemne do sebe, libovolny pocet vnorenych urovni
	// $form_data['id_parent'] je select vytvoreny mimo fci, je do fce vlozen jiz jako vygenerovany prvek
	// - zatim neni uspokojive vyresena navigace ve verejne casti, proto zatim nebudeme pouzivat
	/*
	$zarazeni = "
	<tr>
		<td>".$dct['cat_f_zaradit']."</td>
		<td width=\"30\">&nbsp;</td>
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
	global $SQL_ADD_nezmenit;
	// id name hidden descr lang products id_parent position
	$q = "SELECT id, name 
	FROM ".T_AKCE." WHERE ".SQL_C_LANG." 
	AND id_parent = 0 AND id NOT ".$SQL_ADD_nezmenit."
	ORDER BY position, name, id ";
	$v = my_DB_QUERY($q,__LINE__,__FILE__);
	
	
	$res='';
	
	while ($z = mysql_fetch_array($v)) {
	
		if((isset($_SESSION['last_id_parent']) && $z['id'] == $_SESSION['last_id_parent']) || $z['id'] == $form_data['C_id_parent']) $selected = 'selected';
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
		<td width=\"30\">&nbsp;</td>
		<td>".$select."</td>
	</tr>";
	// 3) konec
	
	
	
	
  //vložení SEO
	if(empty($sc))$sc='';
  $SEO=form_seo($form_data,$sc);
	
	
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
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"name\" value=\"".$form_data['name']."\" 
			style=\"width: 250px;\" class=\"f10\"></td>
	</tr>
	
	
	
	
	<!--$zarazeni-->
	<input type=\"hidden\" name=\"id_parent\" value=\"0\" />
	
	
	
	
	<tr>
		<td>".$dct['cat_f_skryt']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" 
			".$form_data['hidden']."></td>
	</tr>
	
	
	
	
	<!--<tr>
		<td>povolit export zboží do xml<br /><i class=\"f9\">(kat. nesmí být skrytá)</i></td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"checkbox\" name=\"export\" value=\"1\" 
			".$form_data['export']."></td>
	</tr>-->
	
	
	
	
	<!--
	<tr>
		<td>".$dct['cat_f_pocet']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"products\" value=\"".$form_data['products']."\" 
			size=\"5\" class=\"f10\"></td>
	</tr>
	-->
	<input type=\"hidden\" name=\"products\" value=\"".$form_data['products']."\">
	
	
	
	
	<tr>
		<td>".$dct['cat_f_poradi']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"position\" value=\"".$form_data['position']."\" 
			size=\"5\" class=\"f10\"></td>
	</tr>
	
	
	$editor
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	<tr>
		<td width=\"200\" valign=\"top\">Produkty v akci</td>
		<td width=\"30\">&nbsp;</td>
		<td>$select_P</td>
	</tr>
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	<tr>
		<td colspan=\"3\"><br><br>
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
		</table>
	
  <br /><br />
  
  $SEO
  
	</form>";
	
	return $form;

}





function akce_pos() {

	// spousti se po kazde akci s daty kategorii
	// projde kategorie shopu v DB a vytvori souvisle rady z poradi 
	// v jednotlivych kategoriich a podkategoriich
	$posititon = 0;
	
	$query = "SELECT id, id_parent FROM ".T_AKCE." 
	WHERE ".SQL_C_LANG." ORDER BY id_parent, position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		if ($last_parent == $z['id_parent']) {
			$posititon++;
		}
		else {
			$posititon = 1;
			$last_parent = $z['id_parent'];
		}
		
		$query2 = "UPDATE ".T_AKCE." SET position = $posititon 
		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
		my_DB_QUERY($query2,__LINE__,__FILE__);
	
	}

}





function hidden_akce($id,$hidden) {

	// projde kategorie od zadaneho ID dolu, vyhleda vsechny 
	// podrizene urovne a nastavi jim parametr skryti/neskryti


	$query = "UPDATE ".T_AKCE." SET hidden = $hidden 
	WHERE id = $id AND ".SQL_C_LANG." ";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	
	$query = "SELECT id FROM ".T_AKCE." 
	WHERE id_parent = $id AND ".SQL_C_LANG." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		hidden_akce($z['id'],$hidden);
	}
	


}






// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST) AND !in_array($_POST['id'], $id_nezmenit)) { // echo $_POST['id']."<br />";

	$par = trim($_POST['id_parent']); // ID nadrazene kategorie
	
	// text kategorie
	$descr = trim($_POST['descr']);
	

	
	if(!empty($_POST['id'])) {
	
		// kontrola zda nezarazujeme kategorii samu do sebe 
		// nebo do sobe podrizene kategorie
		// jisteno pred odeslanim pomoci js, ale pro pocit...
		unset($ch_akce);
		children_in_akce($_POST['id'],$ch_akce);
		
		if(in_array($par, $ch_akce)) {
		
			$trans = array ("\\n" => "\n"); // 
			$_SESSION['alert'] = strtr($dct['chld'], $trans);
			
			Header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		
		}
	
	}
	
	
	
	
	
	
	

	// nastavime pozice
	$pos = trim($_POST['position']);
	if (empty($pos)) $pos = 100000000;
	
	$query = "SELECT id, position FROM ".T_AKCE." 
	WHERE id_parent = $par 
	AND (position = $pos OR position > $pos) 
	AND ".SQL_C_LANG." 
	ORDER BY position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$query2 = "UPDATE ".T_AKCE." SET 
		position = (position + 1) 
		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
		my_DB_QUERY($query2,__LINE__,__FILE__);
	
	}
	
	
	
	// nastaveni skryti kategorie a ji podrizenych
	$hidden = $_POST['hidden'];
	if ($hidden != 1) $hidden = 0;
	
	
	$export = $_POST['export'];
	if ($export != 1) $export = 0;
	
	
	if(!empty($_POST['id'])) { // aktualizace
	
		$id = $_POST['id'];
		
		$query = "UPDATE ".T_AKCE." SET 
		name = '".trim($_POST['name'])."', 
		hidden = $hidden, 
		descr = '$descr', 
		products = ".trim($_POST['products']).", 
		id_parent = $par, 
		position = $pos, 
		export = $export 
		WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
		
		$back = $_SERVER['HTTP_REFERER'];//MAIN_LINK."&f=akce&a=list";
	
	} else { // novy zaznam
	
		$query = "INSERT INTO ".T_AKCE."  
		VALUES('', '".trim($_POST['name'])."', $hidden, 
		'$descr', '".C_LANG."', 
		".trim($_POST['products']).", $par, $pos, $export )";
		my_DB_QUERY($query,__LINE__,__FILE__);
    
		
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$id = mysql_result($v, 0, 0);
		
    //vložení SEO
		$_POST['novy_zaznam']=$id;
    
		// prednastavime nadrazenou kategorii pri vkladani novych kategorii
		$_SESSION['last_id_parent'] = $par;
		
		$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
		
		$back = $_SERVER['HTTP_REFERER'];
	
	}
	
  
  // akcní produkty
	// zrusime puvodni prirazeni souboru
	$query = "DELETE FROM ".T_GOODS_X_AKCE." 
	WHERE id_cat = $id AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_X_DOWNLOAD);
	
	// jsou-li prirazeny nejake soubory, ulozime je do DB
	if(!empty($_POST['akcni'])) {
	
		reset($_POST['akcni']);
		while ($p = each($_POST['akcni'])) {
		  if($p['value']!='NO') { 
			 $query = "INSERT INTO ".T_GOODS_X_AKCE." VALUES(".$p['value'].",".$id.",'".C_LANG."')";
			 my_DB_QUERY($query,__LINE__,__FILE__);
		  }
		}
	
	}
  
  // nastavime moznost exportu i pro vnorene kategorie
  if(!empty($_POST['id'])) {
	
  	$query = "SELECT id FROM ".T_AKCE." WHERE id_parent = $id";
  	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  	
  	while ($z = mysql_fetch_array($v)) {
  	
  		$query2 = "UPDATE ".T_AKCE." SET export = $export 
  		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
  		my_DB_QUERY($query2,__LINE__,__FILE__);
  	
  	}
	
	}
	
	// hidden_akce($id,$hidden);
	
	
	akce_pos();
	
	// exit;
	
  //vložení SEO
  uloz_seo($_POST,4);//kcemu ... 1-clanek,2-kategorie,3-produkt  	
  
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

	$nadpis = "Seznam akcí";
	
	
	
	// vygenerujeme pole s kategoriemi
	if(empty($akce_array)) {
	
		$akce_array = array();
		akce_array($parent_id=0,$akce_array,$level=0);
	
	}
	
	
	
	
	if(!empty($akce_array)) {
	  $res='';
		reset ($akce_array);
		while ($p = each($akce_array)) {
		
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
			$hidden_style = "style=\"color: #939393;\"";
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
			else if (isset($h_parent[$par_id]) && $par_id == $h_parent[$par_id]) {
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
			
				/*$h_img = "
				<img src=\"./icons/hidden_$h.gif\" alt=\"$alt_h\" title=\"$alt_h\" 
				border=\"0\" height=\"10\" width=\"13\">";*/
				$h_img = "
				<a href=\"".MAIN_LINK."&f=akce&a=hidden&id=$id&hidden=$set_hidden\">$h_img</a>";
			
			}
			else if ($h == 3) { // je skryta
			
				$h_img = $h_img;
			
			}
			
			if (!in_array($id, $id_nezmenit))
			{
      $edit_link = ico_edit(MAIN_LINK."&f=akce&a=edit&id=$id",$dct['cat_cat_edit']);
      }
      else
      {
      $edit_link = '';
      }
			
			// [$id]
			$res .= "
			<tr ".TABLE_ROW.">
				<td class=\"td1\" $hidden_style nowrap>
					$indent $h_img ".$name."</td>
				
				<td width=\"15\" class=\"td2\">
					$edit_link </td>
			</tr>";
		
		}
		
		
		
		
		if (!empty($res)) {
		
			$data = "
			".SEARCH_PANEL."
			
			$addRecord
			
			<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
			$res
			</table>";
		
		}
	
	}
	
	
	
	if(empty($data)) $data = "<br /><br />$addRecord".$dct['zadny_zaznam'];

}
// *****************************************************************************
// seznam kategorii
// *****************************************************************************









// *****************************************************************************
// editace kategorie (form)
// *****************************************************************************
if($_GET['a'] == "edit" AND !in_array($_GET['id'], $id_nezmenit)) {

	$nadpis = "Upravit akci";
	
	
	
	// vygenerujeme pole s kategoriemi
	if(empty($akce_array)) {
		$akce_array = array();
		akce_array($parent_id=0,$akce_array,$level=0);
	}
	
	
	
	$query = "SELECT * FROM ".T_AKCE." 
	WHERE id = ".$_GET['id']." AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$form_data['name'] = $z['name'];
		$form_data['hidden'] = $z['hidden'];
		$form_data['descr'] = $z['descr'];
		$form_data['lang'] = $z['lang'];
		$form_data['products'] = $z['products'];
		$C_id_parent = $form_data['C_id_parent'] = $z['id_parent'];
		$form_data['position'] = $z['position'];
		$form_data['export'] = $z['export'];
		
		if($form_data['hidden'] == 1) $form_data['hidden'] = "checked";
		else $form_data['hidden'] = "";
		
		if($form_data['export'] == 1) $form_data['export'] = "checked";
		else $form_data['export'] = "";
	
	}
	
	
	if(!empty($form_data)) {
	
		$form_data['id'] = $_GET['id'];
		$form_data['link'] = MAIN_LINK."&f=akce";
		$form_data['deletebutton'] = DELETE_BUTTON;
		
		
		// js kontrola zda nezarazujeme kategorii samu do sebe 
		// nebo do sobe podrizene kategorie
		unset($ch_akce);
		children_in_akce($_GET['id'],'');
		
		
		reset ($ch_akce);
		$form_data['js']='';
		while (current($ch_akce)) {
		
			$p = current($ch_akce);
			$form_data['js'] .= "
			else if (form1.id_parent.value == \"$p\") {
				alert(\"".$dct['cat_chld']."\");
				form1.id_parent.focus();
				return false;
			}";
			next($ch_akce);
		
		}
		
		
		// roleta pro prirazeni kategorie do nadrazene
		// $form_data['id_parent'] = akce($tree, $names, $hidden, "list", $products, $url, $C_id_parent, $dct);
		//$form_data['id_parent'] = akce_select($cat_array,$C_id_parent,$dct);
		
    //vložení SEO
    $SEO_data=nacti_seo($_GET['id'],4);//kcemu ... 1-clanek,2-kategorie,3-produkt
    $form_data['seo_title']=$SEO_data['seo_title'];
    $form_data['seo_keywords']=$SEO_data['seo_keywords'];
    $form_data['seo_description']=$SEO_data['seo_description'];
    $form_data['seo_foot']=$SEO_data['seo_foot'];
    
		$data = form($form_data,$dct);
	
	}
	else $data = $dct['zaznam_nenalezen'];
	
	
	
	$data = $addRecord.$data;

}
// *****************************************************************************
// editace kategorie (form)
// *****************************************************************************










// *****************************************************************************
// pridani kategorie (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = "Přidat akci";
	
	
	
	// vygenerujeme pole s kategoriemi
	// vygenerujeme pole s kategoriemi
	if(empty($akce_array)) {
	
		$akce_array = array();
		akce_array($parent_id=0,$akce_array,$level=0);
	
	}
	
	
	
	// roleta pro prirazeni kategorie do nadrazene
	// $form_data['id_parent'] = akce($tree, $names, $hidden, "list", $products, $url, $_SESSION['last_id_parent'], $dct);
	//$form_data['id_parent'] = akce_select($cat_array,$_SESSION['last_id_parent'],$dct);
	
	$data = form('',$dct);
	
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

if (!in_array($_GET['id'], $id_nezmenit))
{
 	hidden_akce($_GET['id'],$hidden);
	
	$_SESSION['alert_js'] = $dct['zaznam_upraven'];

}
else
{
	$_SESSION['alert_js'] = 'Nelze změnit';
}	
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// skryta/neskryta kategorie
// *****************************************************************************









// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
if(!empty($_GET['delete'])  AND !in_array($_POST['id'], $id_nezmenit)) {

	// odstrani kategorii s id = $_GET['delete'] a kategorie do ni vnorene, 
	// produkty techto kategorii a jejich obrazky
	
	function go_back($status,$dct) {
	
		my_OPTIMIZE_TABLE(T_AKCE);
		
		if(empty($status)) $status = $dct['zaznam_neodstranen'];
		
		// id name hidden descr lang products id_parent position 
		akce_pos();
		
		$_SESSION['alert_js'] = $status;
		
		Header("Location: ".MAIN_LINK."&f=akce&a=list");
		exit;
	
	}

			
	// odstranime samotnou kategorii 
	$query = "DELETE FROM ".T_AKCE." 
	WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG."";
	if(!$d = my_DB_QUERY($query,__LINE__,__FILE__)) go_back($status,$dct);

	
	// akcní produkty
	// zrusime puvodni prirazeni produktu
	$query = "DELETE FROM ".T_GOODS_X_AKCE." 
	WHERE id_cat = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_X_DOWNLOAD);
	
	
	 //vložení SEO
  	delete_seo($_GET['delete'],4);//kcemu ... 1-clanek,2-kategorie,3-produkt
  
	$status = $dct['zaznam_odstranen'];
	go_back($status,$dct);

}
// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
?>
