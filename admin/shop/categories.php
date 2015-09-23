<?php


include_once($_SERVER['DOCUMENT_ROOT'].'/admin/shop/slider_functions.php');


$query = "SELECT id FROM ".T_CATEGORIES." WHERE ( name LIKE '_NOVE_IMPORTOVANE_POLOZKY' OR name LIKE '_ODSTRANENE_IMPORTOVANE_POLOZKY' OR name LIKE '_IGNOROVANE_IMPORTOVANE' )AND ".SQL_C_LANG." ";

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



$addRecord = "<a href=\"".MAIN_LINK."&f=categories&a=add\">Přidat kategorii</a><br /><br />";

//vložení SEO
include ('seo.php');

function form($form_data,$dct) {
  
  if(empty($form_data['descr']))$form_data['descr']='';
  if(empty($form_data['link']))$form_data['link']='';
  if(empty($form_data['js']))$form_data['js']='';
  if(empty($form_data['id']))$form_data['id']='';
  if(empty($form_data['name']))$form_data['name']='';
  if(empty($form_data['menu_name']))$form_data['menu_name']='';
  if(empty($form_data['hidden']))$form_data['hidden']='';
  if(empty($form_data['export']))$form_data['export']='';
  if(empty($form_data['position']))$form_data['position']='';
  if(empty($form_data['code']))$form_data['code']='';
  if(empty($form_data['deletebutton']))$form_data['deletebutton']='';
  if(empty($form_data['nezobrazovat_ks']))$form_data['nezobrazovat_ks']='';
  
  
  
  switch($form_data['view']){
  	case 1:{
  			$view[1]=' checked="checked"';
  			$view[2]='';
	     	break;
	  }	
	  
	case 2:{
  			$view[1]='';
  			$view[2]=' checked="checked"';	
	     	break;
	  }
	  
	  default:{
  			$view[1]=' checked="checked"';
  			$view[2]='';
	     	break;  	
	  }	
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
	// pouziti editoru pro popis kategorii
	
	
	
	
	if(empty($form_data['products'])) $form_data['products'] = 10; // pocet produktu na stranku
	
	
	// muzeme aplikovat nekolik zpusobu zarazovani kategorii vzajemne do sebe
	
	// 1) razeni kategorii vzajemne do sebe, libovolny pocet vnorenych urovni
	// $form_data['id_parent'] je select vytvoreny mimo fci, je do fce vlozen jiz jako vygenerovany prvek
	// - zatim neni uspokojive vyresena navigace ve verejne casti, proto zatim nebudeme pouzivat
	
	$zarazeni = "
	<tr>
		<td>".$dct['cat_f_zaradit']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>".$form_data['id_parent']."</td>
	</tr>";
	
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
	/*global $SQL_ADD_nezmenit;
	// id name hidden descr lang products id_parent position
	$q = "SELECT id, name 
	FROM ".T_CATEGORIES." WHERE ".SQL_C_LANG." 
	AND id_parent = 0 AND id NOT ".$SQL_ADD_nezmenit."
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
		<td width=\"30\">&nbsp;</td>
		<td>".$select."</td>
	</tr>";
	// 3) konec*/
	
	
	
	
	//vložení SEO
$SEO=form_seo($form_data,'');
	
	
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

		// odstraneni fotografie
	function del2() {

		if (!confirm(\"Opravdu odstranit?\")) return false;

	}
	// -->
	</SCRIPT>
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" onSubmit=\"return validate(this)\" enctype=\"multipart/form-data\">

	<div>
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	<!--<input type=\"hidden\" name=\"lang\" value=\"".C_LANG."\">-->
  </div>

	<table width=\"650\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	<tr>
		<td colspan=\"3\">
			".SAVE_BUTTON."
			".$form_data['deletebutton']."
      <br><br>
		</td>
	</tr>


  <tr>
    <td colspan=\"3\"><strong>Nastavení feedu</strong></td>
  </th>
	<tr>
		<td>Zakázat export na zbozi.cz</td>
    <td style=\"width:30px;\"></td>
		<td><input type=\"checkbox\" name=\"export_zbozi\" value=\"1\" ".$form_data['export_zbozi']."></td>
	</tr>
	<tr>
		<td>CPC zbozi.cz</td>
    <td></td>
		<td><input style=\"width:50px;\" class=\"f10\" type=\"text\" name=\"cpc_zbozi\" value=\"".$form_data['cpc_zbozi']."\"> <span class=\"f10i\">1 až 500 Kč.</span></td>
	</tr>
	<tr>
		<td>Zakázat export na heureka.cz</td>
    <td></td>
		<td><input type=\"checkbox\" name=\"export_heureka\" value=\"1\" ".$form_data['export_heureka']."></td>
	</tr>
	<tr>
		<td>CPC heureka.cz</td>
    <td></td>
		<td><input style=\"width:50px;\" class=\"f10\" type=\"text\" name=\"cpc_heureka\" value=\"".$form_data['cpc_heureka']."\"> <span class=\"f10i\">Maximální cena za klik je 100 Kč.</span></td>
	</tr>
	<tr><td colspan=\"3\"><br></td></tr>

	<tr>
		<td>
			".$dct['nezobrazovat_ks']."
		<td></td>
		<td>
			<input type=\"checkbox\" name=\"nezobrazovat_ks\" value=\"1\" ".$form_data['nezobrazovat_ks'].">
    </td>
	</tr>
  <tr><td colspan=\"3\"><br></td></tr>

	<tr>
		<td>
			".$dct['sleva_na_cat']."
		<td></td>
		<td>
			<input type=\"text\" name=\"sleva\" value=\"".$form_data['sleva']."\" style=\"width: 50px;\" class=\"f10\"> %
    </td>
	</tr>
	<tr>
		<td>
			".$dct['navysit_cenu_cat']."
		<td></td>
		<td>
			<input type=\"text\" name=\"navysit\" value=\"".$form_data['navysit']."\" style=\"width: 50px;\" class=\"f10\"> %
    </td>
	</tr>
  <tr><td colspan=\"3\"><br></td></tr>

	<tr>
		<td>
			".$dct['cat_f_nazev']." <span class=\"f10\">
			".$dct['cat_f_max_255']."</span></td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"name\" value=\"".$form_data['name']."\" style=\"width: 250px;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>
			Nadpis pro menu <span class=\"f10\">
			".$dct['cat_f_max_255']."</span></td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"menu_name\" value=\"".$form_data['menu_name']."\" style=\"width: 250px;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>
			Celý kód kategorie<br /><span class=\"f10\">například 0100100101 pro letní pneu na osobní vozy</span></td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"code\" value=\"".$form_data['code']."\" style=\"width: 250px;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td valign=\"top\">
			<br />Zobrazení výrobků na výpisu kategorie:<br /><br /></td>
		<td width=\"30\">&nbsp;</td>
		<td> <br />
			<input type=\"radio\" name=\"view\" ".$view['1']." value=\"1\" class=\"f10\"> výpis do boxů<br />
			<input type=\"radio\" name=\"view\" ".$view['2']." value=\"2\" class=\"f10\"> řádkový výpis<br /><br />
		</td>
	</tr>		
	
	
	
	
	$zarazeni
	
	
	
	
	<tr>
		<td>".$dct['cat_f_skryt']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" 
			".$form_data['hidden']."></td>
	</tr>
	

  <!--
	<tr>
		<td>povolit export zboží do xml<br /><i class=\"f9\">(kat. nesmí být skrytá)</i></td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"checkbox\" name=\"export\" value=\"1\" 
			".$form_data['export']."></td>
	</tr>
	-->

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
	

  <!--
	<tr>
		<td class=\"tdleft\" valign=\"top\">
			<br />Fotografie
			".$form_data['imgdata']."</td>
			<td width=\"30\">&nbsp;</td>
		<td class=\"tdright\">
			<br />".$form_data['img']."</td>
	</tr>

	<tr>
		<td class=\"tdleft\" valign=\"top\">Nová fotografie</td>
		<td width=\"30\">&nbsp;</td>
		<td class=\"tdright\">
			<input type=\"file\" name=\"foto\" style=\"width: 100%;\" class=\"f10\" />
			<span class=\"f10i\">(odesláním fotografie přepíšete původní)<br />Pokud produkt nemá svoji fotografii, bude použita fotografie kategorie, do které je produkt zařazen</span></td>
	</tr>
  -->


	$editor
	
	<tr>
		<td class=\"tdleft\" valign=\"top\"><a href=\"\" class=\"click\" onclick=\"s('divslider'); return false;\">Slidery</a> &raquo;</td>
		<td class=\"tdright\" colspan=\"2\">
      <div style=\"display:none;\" id=\"divslider\">
        ".slider_shortcode()."
      </div>
    </td>
	</tr>

	
	<tr>
		<td colspan=\"3\">
      <br><br>
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





function category_pos() {

	// spousti se po kazde akci s daty kategorii
	// projde kategorie shopu v DB a vytvori souvisle rady z poradi 
	// v jednotlivych kategoriich a podkategoriich
	$posititon = 0;
	
	$query = "SELECT id, id_parent FROM ".T_CATEGORIES." 
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
		
		$query2 = "UPDATE ".T_CATEGORIES." SET position = $posititon 
		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
		my_DB_QUERY($query2,__LINE__,__FILE__);
	
	}

}





function hidden_categories($id,$hidden) {

	// projde kategorie od zadaneho ID dolu, vyhleda vsechny 
	// podrizene urovne a nastavi jim parametr skryti/neskryti


	$query = "UPDATE ".T_CATEGORIES." SET hidden = $hidden 
	WHERE id = $id AND ".SQL_C_LANG." ";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	
	$query = "SELECT id FROM ".T_CATEGORIES." 
	WHERE id_parent = $id AND ".SQL_C_LANG." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		hidden_categories($z['id'],$hidden);
	}
	


}






// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST) AND !in_array($_POST['id'], $id_nezmenit)) {

	$par = trim($_POST['id_parent']); // ID nadrazene kategorie

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


	// nastavime pozice
	$pos = trim($_POST['position']);
	if (empty($pos)) $pos = 100000000;
	
	$query = "SELECT id, position FROM ".T_CATEGORIES." 
	WHERE id_parent = $par 
	AND (position = $pos OR position > $pos) 
	AND ".SQL_C_LANG." 
	ORDER BY position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$query2 = "UPDATE ".T_CATEGORIES." SET 
		position = (position + 1) 
		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
		my_DB_QUERY($query2,__LINE__,__FILE__);
	
	}
	

  // Společné hodnoty pro insert i update.
  $query_set = "
	name = '".trim($_POST['name'])."',
	menu_name = '".trim($_POST['menu_name'])."',
	code = '".trim($_POST['code'])."',
	view = '".intval($_POST['view'])."',
	hidden = '".intval($_POST['hidden'])."',
	descr = '".trim($_POST['descr'])."',
	products = '".intval($_POST['products'])."',
	id_parent = '".intval($par)."',
	position = '".intval($pos)."',
	export = '".intval($_POST['export'])."',
  export_zbozi = '".intval($_POST['export_zbozi'])."',
  export_heureka = '".intval($_POST['export_heureka'])."',
  cpc_zbozi = '".trim($_POST['cpc_zbozi'])."',
  cpc_heureka = '".trim($_POST['cpc_heureka'])."',
  sleva = '".intval($_POST['sleva'])."',
  navysit = '".intval($_POST['navysit'])."',
  nezobrazovat_ks = '".intval($_POST['nezobrazovat_ks'])."'
  ";
	
	if(!empty($_POST['id']))
  { // aktualizace
		$id = intval($_POST['id']);
		
		$query = "
    UPDATE ".T_CATEGORIES."
    SET 
    ".$query_set."
		WHERE id = '".intval($id)."'
    AND ".SQL_C_LANG;
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
		$back = $_SERVER['HTTP_REFERER'];//MAIN_LINK."&f=categories&a=list";
	}
  else
  { // novy zaznam
		//$query = "INSERT INTO ".T_CATEGORIES."  VALUES(NULL, '".trim($_POST['name'])."', '".trim($_POST['menu_name'])."', '".trim($_POST['code'])."', '', '".trim($_POST['view'])."', $hidden, '".trim($_POST['descr'])."', '".C_LANG."', ".trim($_POST['products']).", $par, $pos, '".intval($_POST['export'])."' )";
		$query = "
    INSERT INTO ".T_CATEGORIES."
    SET
    ".$query_set.",
    lang = '".intval(C_LANG)."'
    ";
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


	// fotografie
	if(!empty($_FILES['foto']['name'])) {

		$img = img_cat_upload($nm=$id);


		$query = "UPDATE ".T_CATEGORIES." SET img = '$img' WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);

	}

  
  // nastavime moznost exportu i pro vnorene kategorie
  if(!empty($_POST['id'])) {
	
  	$query = "SELECT id FROM ".T_CATEGORIES." WHERE id_parent = $id";
  	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  	
  	while ($z = mysql_fetch_array($v)) {
  	
  		$query2 = "UPDATE ".T_CATEGORIES." SET export = '".intval($_POST['export'])."'
  		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
  		my_DB_QUERY($query2,__LINE__,__FILE__);
  	
  	}
	
	}
	
	// hidden_categories($id,$hidden);
	
	
	category_pos();
	
	// exit;
	
  //vložení SEO
  uloz_seo($_POST,2);//kcemu ... 1-clanek,2-kategorie,3-produkt  	
  
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

	$nadpis = $dct['mn_seznam_kategorii'];
	
	
	
	// vygenerujeme pole s kategoriemi
	if(empty($cat_array)) {
	
		$cat_array = array();
		categories_array($parent_id=0,$cat_array,$level=0);
	
	}
	
	
	
	
	if(!empty($cat_array)) {
	
		reset ($cat_array);
		while ($p = each($cat_array)) {
		
			list ($level,$position,$par_id,$name,$hidden,$lang,$id,$code) = explode ("|", $p['value']);
			
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
				<a href=\"".MAIN_LINK."&f=categories&a=hidden&id=$id&hidden=$set_hidden\">$h_img</a>";
			
			}
			else if ($h == 3) { // je skryta
			
				$h_img = $h_img;
			
			}
			
			if (!in_array($id, $id_nezmenit))
			{
      $edit_link = ico_edit(MAIN_LINK."&f=categories&a=edit&id=$id",$dct['cat_cat_edit']);
      }
      else
      {
      $edit_link = '';
      }
      
      $sipka='';
      
      
      $query='SELECT max(position) as maximum FROM '.T_CATEGORIES.' WHERE id_parent='.$par_id;
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
      $maximum=mysql_fetch_array($v);
      $maximum=$maximum['maximum'];

      if($position==$maximum && $maximum==1){
        $sipka='';
      }else{
        
        switch($position){
          case 1: {
                  $sipka='<a href="?C_lang='.C_LANG.'&app=shop&f=categories&a=changepos&ord=down&id='.$id.'" title="níže"><img style="border: 0;" src="/admin/img/arrow_down.gif" alt="šipka dolů" /></a>&nbsp;<img src="/admin/img/arrow_no.gif" />';break;
          }
          case $maximum:{
                  $sipka='<img src="/admin/img/arrow_no.gif" />&nbsp;<a href="?C_lang='.C_LANG.'&app=shop&f=categories&a=changepos&ord=upp&id='.$id.'" title="výše"><img style="border: 0;" src="/admin/img/arrow_up.gif" alt="šipka nahoru" /></a>';break;
          }
          default:{
                  $sipka='<a href="?C_lang='.C_LANG.'&app=shop&f=categories&a=changepos&ord=down&id='.$id.'" title="níže"><img style="border: 0;" src="/admin/img/arrow_down.gif" alt="šipka dolů" /></a>&nbsp;<a href="?C_lang='.C_LANG.'&app=shop&f=categories&a=changepos&ord=upp&id='.$id.'" title="výše"><img style="border: 0;" src="/admin/img/arrow_up.gif" alt="šipka nahoru" /></a>';break;
          }
        }
      }
      
      if(!empty($code))$code=' - '.$code;
      else $code='';
      
      if(!isset($res)) $res = "";
		
			// [$id]
			$res .= "
			<tr ".TABLE_ROW.">
				<td class=\"td1\" $hidden_style nowrap>
				  <a name='kat$id'>&nbsp;</a>
					$indent $h_img ".$name." ".$code."</td>
				<!--<td>$position</td>--><td style='text-align: center;'>$sipka</td>
				<td width=\"15\" class=\"td2\">
					$edit_link </td>
			</tr>";
		}
		
		
		
		
		if (!empty($res)) {
		
			$data = "
			".SEARCH_PANEL."
			
			$addRecord
			
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
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

	$nadpis = $dct['cat_cat_edit'];
	
	
	
	// vygenerujeme pole s kategoriemi
	if(empty($cat_array)) {
		$cat_array = array();
		categories_array($parent_id=0,$cat_array,$level=0);
	}
	
	
	$query = "
  SELECT *
  FROM ".T_CATEGORIES." 
	WHERE id = '".intval($_GET['id'])."'
  AND ".SQL_C_LANG."
  LIMIT 1
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while($z = mysql_fetch_assoc($v))
  {
    $form_data = $z;
		$C_id_parent = $form_data['C_id_parent'] = $z['id_parent'];

		if($form_data['hidden'] == 1) $form_data['hidden'] = "checked";
		else $form_data['hidden'] = "";
		
		if($form_data['export'] == 1) $form_data['export'] = "checked";
		else $form_data['export'] = "";

		if($form_data['export_zbozi'] == 1) $form_data['export_zbozi'] = "checked";
		else $form_data['export_zbozi'] = "";

		if($form_data['export_heureka'] == 1) $form_data['export_heureka'] = "checked";
		else $form_data['export_heureka'] = "";

		if($form_data['nezobrazovat_ks'] == 1) $form_data['nezobrazovat_ks'] = "checked";
		else $form_data['nezobrazovat_ks'] = "";

		if($form_data['cpc_zbozi'] == 0) $form_data['cpc_zbozi'] = "";
		if($form_data['cpc_heureka'] == 0) $form_data['cpc_heureka'] = "";

    if($form_data['sleva'] == 0) $form_data['sleva'] = "";
    if($form_data['navysit'] == 0) $form_data['navysit'] = "";


    $form_data['imgdata'] = "";
  	if(!empty($z['img'])) {

  			$img = $_GET['id'].".".$z['img'];
  			$img1 = IMG_C_S.$img; // cesta pro nahled
  			$img2 = IMG_C_O.$img; // cesta pro detailni obrazek

  			$form_data['img'] = showimg($img1,$img2,'','','','','',$timestamp);
  			$form_data['img'] .= "<br /><a href=\"".MAIN_LINK."&f=categories&delimg=".$_GET['id']."\"
  														class=\"f10\" title=\"Odstraní obrázek\" onclick=\"return del2();\">odstranit obrázek</a><br /><br />";

  			// rozmery a velikost nahledu
  			@$r_img1 = getimagesize($img1);


  			// rozmery a velikost velkeho obrazku
  			@$r_img2 = getimagesize($img2);


  			$form_data['imgdata'] = "<br /><br />";
  			$form_data['imgdata'] .= "<span class=\"f10\">";
  			$form_data['imgdata'] .= "náhled:<br />";
  			$form_data['imgdata'] .= "".$r_img1[0]."x".$r_img1[1]."px, ".file_size($img1)."<br /><br />";
  			$form_data['imgdata'] .= "velký:<br />";
  			$form_data['imgdata'] .= "".$r_img2[0]."x".$r_img2[1]."px, ".file_size($img2)."";
  			$form_data['imgdata'] .= "</span>";

  	}
	}
	
	
	if(!empty($form_data)) {
	
		$form_data['id'] = $_GET['id'];
		$form_data['link'] = MAIN_LINK."&f=categories";
		$form_data['deletebutton'] = DELETE_BUTTON;
		
		
		// js kontrola zda nezarazujeme kategorii samu do sebe 
		// nebo do sobe podrizene kategorie
		$ch_cat=null;
		children_in_category($_GET['id'],$ch_cat);
		
		
		reset ($ch_cat);
		
		$form_data['js']='';
    while (current($ch_cat)) {
		
			$p = current($ch_cat);
			$form_data['js'] .= "
			else if (form1.id_parent.value == \"$p\") {
				alert(\"".$dct['cat_chld']."\");
				form1.id_parent.focus();
				return false;
			}";
			next($ch_cat);
		
		}
		
		
		// roleta pro prirazeni kategorie do nadrazene
		// $form_data['id_parent'] = categories($tree, $names, $hidden, "list", $products, $url, $C_id_parent, $dct);
		$form_data['id_parent'] = categories_select($cat_array,$C_id_parent,$dct);
		
    //vložení SEO
    $SEO_data=nacti_seo($_GET['id'],2);//kcemu ... 1-clanek,2-kategorie,3-produkt
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

	$nadpis = $dct['mn_pridat_kategorii'];
	
	
	
	// vygenerujeme pole s kategoriemi
	// vygenerujeme pole s kategoriemi
	if(empty($cat_array)) {
	
		$cat_array = array();
		categories_array($parent_id=0,$cat_array,$level=0);
	
	}
	
	
	if(empty($_SESSION['last_id_parent']))$_SESSION['last_id_parent']='';
	
	// roleta pro prirazeni kategorie do nadrazene
	// $form_data['id_parent'] = categories($tree, $names, $hidden, "list", $products, $url, $_SESSION['last_id_parent'], $dct);
	$form_data['id_parent'] = categories_select($cat_array,$_SESSION['last_id_parent'],$dct);
	
	$data = form($form_data,$dct);
	
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
 	hidden_categories($_GET['id'],$hidden);
	
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

	// odstrani kategorii s id = $_GET['delete'] a kategorie do ni vnorene 
	
	function go_back($status,$dct) {
	
		my_OPTIMIZE_TABLE(T_CATEGORIES);
		
		if(empty($status)) $status = $dct['zaznam_neodstranen'];
		
		// id name hidden descr lang products id_parent position 
		category_pos();
		
		$_SESSION['alert_js'] = $status;
		
		Header("Location: ".MAIN_LINK."&f=categories&a=list");
		exit;
	
	}
	
	// vyhledame id vnorenych kategorii
	$ch_cat=array();
	children_in_category($_GET['delete'],$ch_cat);
	
	print_r($ch_cat);

	// mame id kategorii ktere je treba odstranit
	if(!empty($ch_cat)) {
	
		krsort($ch_cat); 
		// obratime poradi, tim mame zajisten postup zdola nahoru, 
		// ne zcela uplne, ale pro nasledujici popisovane postupy to staci
		
		reset($ch_cat);
		
		while ($p = each($ch_cat)) {
		
			$id_cat = $p['value'];
			
			// odstranime ze spojovaci tabulky na zbozi		  
			$query = "DELETE FROM ".T_GOODS_X_CATEGORIES." 
			WHERE id_cat = $id_cat AND ".SQL_C_LANG."";
			if(!$d = my_DB_QUERY($query,__LINE__,__FILE__)) echo '<br />chyba5'; //go_back($status,$dct);
			my_OPTIMIZE_TABLE(T_GOODS_X_CATEGORIES);      				

			// odstranime samotnou kategorii 
			$query = "DELETE FROM ".T_CATEGORIES." 
			WHERE id = $id_cat AND ".SQL_C_LANG."";
			if(!$d = my_DB_QUERY($query,__LINE__,__FILE__)) go_back($status,$dct);
		
		}

	}
	
	 //vložení SEO
  	delete_seo($_GET['delete'],2);//kcemu ... 1-clanek,2-kategorie,3-produkt
  
	$status = $dct['zaznam_odstranen'];
	go_back($status,$dct);

}
// *****************************************************************************
// odstranit kategorii
// *****************************************************************************





// *****************************************************************************
// upravit pozici
// *****************************************************************************
if($_GET['a']=='changepos' && !empty($_GET['id']) && !empty($_GET['ord'])){
  $id=$_GET['id'];
  $query='select position,id_parent from '.T_CATEGORIES.' where id='.$id;
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $position=mysql_fetch_array($v);
  $id_parent=$position['id_parent'];
  $position=$position['position'];
  
  switch($_GET['ord']){
    case "down": $newPosition=$position+2;break;
    case "upp": $newPosition=$position-1;break;
  }
  
  $query = "SELECT id, position FROM ".T_CATEGORIES." 
	WHERE id_parent = $id_parent 
	AND (position = $newPosition OR position > $newPosition) 
	AND ".SQL_C_LANG." 
	ORDER BY position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$query = "UPDATE ".T_CATEGORIES." SET 
		position = (position + 5) 
		WHERE id = ".$z['id']." AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
  
  $query='update '.T_CATEGORIES.' set position='.$newPosition.' where id='.$id;
  my_DB_QUERY($query,__LINE__,__FILE__);
  
  
  
  category_pos();  

  Header("Location: ".$_SERVER['HTTP_REFERER']."#kat".$_GET['id']);
  exit;
}
// *****************************************************************************
// upravit pozici
// *****************************************************************************







// *****************************************************************************
// odstranit obrazek produktu
// *****************************************************************************
if(!empty($_GET['delimg'])) {

	delete_img_cat($_GET['delimg']);

	$query = "UPDATE ".T_CATEGORIES." SET img = ''
	WHERE id = ".$_GET['delimg']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);

	$_SESSION['alert_js'] = "Obrázek odstraněn";

	Header("Location: ".$_SERVER['HTTP_REFERER']."");
	exit;

}
// *****************************************************************************
// odstranit obrazek produktu
// *****************************************************************************




// *****************************************************************************
// odstraneni obrazku produktu
// *****************************************************************************
function delete_img_cat($id) {

	$query = "SELECT img FROM ".T_CATEGORIES." 
	WHERE id = $id AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$img = $id.".".$z['img'];
	
	}
	
	@unlink(IMG_C_S.$img); // cesta pro nahled
	@unlink(IMG_C_M.$img); // cesta pro detail
	@unlink(IMG_C_O.$img); // cesta pro original

}
// *****************************************************************************
// odstraneni obrazku produktu
// *****************************************************************************
?>
