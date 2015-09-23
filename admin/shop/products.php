<?php

//vlo·ení SEO
include ('seo.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_functions.php');

define('SBEZDPH','bez DPH');



$query = "SELECT id FROM ".T_CATEGORIES." WHERE ( name LIKE '_NOVE_IMPORTOVANE_POLOZKY' OR name LIKE '_ODSTRANENE_IMPORTOVANE_POLOZKY' OR name LIKE '_IGNOROVANE_IMPORTOVANE' )AND ".SQL_C_LANG." ";
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


// Hromadné operace.
if(!empty($_POST['presun']))
{
  if(!empty($_POST['id_parent']) && !empty($_POST['preradit'])) {
  
  	if(!empty($_GET['cat'])){
  		foreach($_POST['preradit'] as $id_good){
			$query='delete from '.T_GOODS_X_CATEGORIES.' where id_good='.$id_good.' and id_cat='.$_GET['cat'];
			my_DB_QUERY($query,__LINE__,__FILE__);
		}
	}
  
	foreach($_POST['id_parent'] as $id_cat){
		foreach($_POST['preradit'] as $id_good){
			$query="select * from ".T_GOODS_X_CATEGORIES." where id_good=$id_good and id_cat=$id_cat and lang=".C_LANG."";
			$v=my_DB_QUERY($query,__LINE__,__FILE__);
			if(mysql_num_rows($v)==0){ 
				$query="insert into ".T_GOODS_X_CATEGORIES."(id_good,id_cat,lang) values(".$id_good.",".$id_cat.",".C_LANG.")";
				my_DB_QUERY($query,__LINE__,__FILE__);
			}	 	
		}	
	}
    
    $_SESSION['alert_js'] = "Záznamy byly přesunuty";
    header("location: ".$_SERVER['HTTP_REFERER']);
    exit;
  }
  else
  {
    $_SESSION['alert_js'] = "Vyberte produkty a kategorii";
    header("location: ".$_SERVER['HTTP_REFERER']);
    exit;
  }
}


if(!empty($_POST['kopirovani']))
{
  if(!empty($_POST['id_parent']) && !empty($_POST['preradit']))
  {
	foreach($_POST['id_parent'] as $id_cat)
  {
		foreach($_POST['preradit'] as $id_good)
    {
			$query="select * from ".T_GOODS_X_CATEGORIES." where id_good=$id_good and id_cat=$id_cat and lang=".C_LANG."";
			$v=my_DB_QUERY($query,__LINE__,__FILE__);
			if(mysql_num_rows($v)==0)
      { 
				$query="insert into ".T_GOODS_X_CATEGORIES."(id_good,id_cat,lang) values(".$id_good.",".$id_cat.",".C_LANG.")";
				my_DB_QUERY($query,__LINE__,__FILE__);
			}	 	
		}	
	}
    
    $_SESSION['alert_js'] = "Záznamy byly zkopírovány";
    header("location: ".$_SERVER['HTTP_REFERER']);
    exit;
  }
  else
  {
    $_SESSION['alert_js'] = "Vyberte produkty a kategorii do které mají být zkopírovány";
    header("location: ".$_SERVER['HTTP_REFERER']);
    exit;
  }
}


if(isset($_POST['submit_uprednostnit_zbozi']) AND !empty($_POST['submit_uprednostnit_zbozi']))
{ // Nastavení pro feed hromadně.
  $query = "
  UPDATE ".T_GOODS."
  SET
  uprednostnit_zbozi = '".intval($_POST["uprednostnit_zbozi"])."'
  WHERE id IN (".implode(", ", $_POST["preradit"]).")
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);

  $_SESSION['alert_js'] = "Uloženo";
  header("location: ".$_SERVER['HTTP_REFERER']);
  exit;
}
// END Hromadné operace.


// *****************************************************************************
// formular pro editaci
// *****************************************************************************
function form($form_data,$dct) {
	
	if(empty($form_data['text']))$form_data['text']='';
	
	$editor="
      <textarea name='text'>".$form_data['text']."</textarea>
          
      <script type='text/javascript'>
                //<![CDATA[
                CKEDITOR.replace('text', {
                	height: '400px'
                });
                //]]>
      </script>
  ";
	
	if(empty($form_data['id'])) { // novy zaznam
	
		$form_data['dph21.000'] = "checked"; // prednastavime DPH
	
	} else { // editace existujiciho
	
		// soubory ke stazeni
		// id_good id_file lang
		$query = "SELECT id_file FROM ".T_GOODS_X_DOWNLOAD." 
		WHERE id_good = ".$form_data['id']." AND ".SQL_C_LANG."";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
		
			$files_selected[$z['id_file']] = "selected";
		
		}
		
		//dodaci lhuta
		$query = "SELECT id_dodani FROM ".T_GOODS." where id=".$form_data['id'];
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
		
			$dodaci_lhuta_selected[$z['id_dodani']] = "selected";
		
		}
	
	}
	
	
  	$dodaniOptions="";
  	
  	// seznam dodacich lhut
  	$query="select * from ".T_DODACI_LHUTA." where hidden=0 and ".SQL_C_LANG." order by position";
  	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  	while ($z = mysql_fetch_array($v)) {
      if(!empty($dodaci_lhuta_selected[$z['id']])){
        $dodaniOptions.='<option value="'.$z['id'].'" '.$dodaci_lhuta_selected[$z['id']].'>'.$z['nazev'].'</option>';
      }else{
        $dodaniOptions.='<option value="'.$z['id'].'">'.$z['nazev'].'</option>';
      }  	
  	}	
  	
  	if(!empty($dodaniOptions)){
      $dodaniOptions="<select class=\"f10\" style=\"width: 100%\" name='dodaci_lhuta'><option value='0'>neurčena</option>".$dodaniOptions."</select>";
    	}	
	
	
	// **********************************************************
	// seznam se soubory ktere lze priradit k produktu ke stazeni
	$query = "SELECT id, odkaz, mime FROM ".T_DOWNLOAD." 
	WHERE ".SQL_C_LANG." ORDER BY odkaz";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
    
    $mime = $z['mime'];
    $fid = $z['id'];
    
    if($mime=='video/x-ms-wmv' OR $mime=='application/octet-stream' OR $mime=='video/mpeg')
      $selectVideo .= "
		  <option value=\"$fid\" ".$files_selected[$fid].">".$z['odkaz']."</option>";	
    else
    	$select .= "
		  <option value=\"$fid\" ".$files_selected[$fid].">".$z['odkaz']."</option>";
	
	}
	
	
	if (!empty($select)) {
	
		$select = "
		<select name=\"files[]\" class=\"f10 adminselect\" size=\"6\" multiple=\"multiple\">
		$select
		</select>
		
		<span class=\"f10i\">
			více souborů lze označit přidr·ením klávesy Shift nebo Ctrl a zároveň 
			kliknutím myši na název souboru.
		</span>";
	
	}
	else $select = "žádné soubory nebyly v databázi nalezeny.";
	
	if (!empty($selectVideo)) {
	
		$selectVideo = "
		<select name=\"videofiles[]\" class=\"f10 adminselect\" size=\"6\" multiple=\"multiple\">
		$selectVideo
		</select>
		
		<span class=\"f10i\">
			více video souborů lze označit přidr·ením klávesy Shift nebo Ctrl a zároveň 
			kliknutím myši na název souboru.
		</span>";
	
	}
	else $selectVideo = "žádné video soubory nebyly v databázi nalezeny.";
	// seznam se soubory ktere lze priradit k produktu ke stazeni
	// **********************************************************
	
	
	
	
	
	// **********************************************************
	// seznam produktu pro prirazeni jako pribuzne a pro vytvoreni skupiny
	// T_GOODS_PRIBUZNE id_good  id_pribuzne
	
	$Qpribuzne='';
  if(!empty($form_data['id'])) {
	
		$query = "SELECT id_pribuzne FROM ".T_GOODS_PRIBUZNE." 
		WHERE id_good = ".$form_data['id']."";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
		
			$x_selected[$z['id_pribuzne']] = 'selected';
		
		}
		
		$Qpribuzne = ' AND id != '.$form_data['id'].'';
	
	}
	

	global $SQL_ADD_nezmenit;
	// id id_cat name img text hidden akce cena dph lang kod id_vyrobce anotace dop_cena
	$query = "SELECT id, name, kod FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES." cx
	WHERE ".T_GOODS.".".SQL_C_LANG." $Qpribuzne  
  AND cx.id_good=".T_GOODS.".id AND cx.id_cat NOT ".$SQL_ADD_nezmenit."
  GROUP BY ".T_GOODS.".id
  ORDER BY name";
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
		<select name=\"pribuzne[]\" class=\"f10 adminselect\" size=\"20\" multiple=\"multiple\">
		<option value=\"NO\">žádné</option>
		$select_P
		</select>
		
		$dod";
	
	} else $select_P = "žádné produkty nebyly v databázi nalezeny.";

	// seznam produktu pro prirazeni jako pribuzne
	// **********************************************************
	
	
	
	if(empty($form_data['dop_cena']) || $form_data['dop_cena'] < 1) $form_data['dop_cena'] = '';
  if(empty($form_data['cena_eshop'])OR $form_data['cena_eshop'] == 0) $form_data['cena_eshop'] = '';
	
	
	
	
	// kopie *********************************************************************
	if($_GET['a'] == "copy") {
	
		$form_data['id'] = '';//-1000
		$form_data['copy_button'] = '';
		$form_data['deletebutton'] = '';
	
	} else if ($_GET['a'] == "add") { // novy ************************************
	
		$form_data['copy_button'] = '';
	
	} else { // editace ************************************
	
		$editList = '
			<a href="'.MAIN_LINK.'&f=products_parameters&a=parameters&Pid='.$form_data['id'].'" class="">
			Vystavit/upravit produktový list</a><br />';// '.$form_data['name'].'
		
		$form_data['copy_button'] = button('button','Vytvořit kopii','class="butt_ostatni" onclick="location=\''.MAIN_LINK.'&f=products&id='.$form_data['id'].'&a=copy\'"');
		
		
	// $form_data['deletebutton'] = 
	
	}
	
	
	if(empty($form_data['deletebutton']))$form_data['deletebutton']='';
	if(empty($form_data['copy_button']))$form_data['copy_button']='';
	if(empty($_GET['cat']))$_GET['cat']='';
	if(empty($editList))$editList='';
	
	
	$buttony = "".SAVE_BUTTON." ".$form_data['deletebutton']." ".$form_data['copy_button']."";
	
	
	//vložení SEO
  $SEO=form_seo($form_data,1);
	
	
	if(empty($form_data['id']))$form_data['id']='';
	if(empty($form_data['add_del']))$form_data['add_del']='';
	if(empty($form_data['link']))$form_data['link']='';
	if(empty($form_data['akce']))$form_data['akce']='';
	if(empty($form_data['doporucujeme']))$form_data['doporucujeme']='';
	if(empty($form_data['prednost']))$form_data['prednost']='';
	if(empty($form_data['novinka']))$form_data['novinka']='';
	if(empty($form_data['hidden']))$form_data['hidden']='';
	if(empty($form_data['name']))$form_data['name']='';
	if(empty($form_data['poradi']))$form_data['poradi']='';
	if(empty($form_data['hmotnost']))$form_data['hmotnost']='';
	if(empty($form_data['kod']))$form_data['kod']='';
  if(empty($form_data['kod2']))$form_data['kod2']='';
	if(empty($form_data['id_vyrobce']))$form_data['id_vyrobce']='';
	if(empty($form_data['cena']))$form_data['cena']='';
  if(empty($form_data['cena_eshop']))$form_data['cena_eshop']='';
	if(empty($form_data['dph21.000']))$form_data['dph21.000']='';
	if(empty($form_data['dph15.000']))$form_data['dph15.000']='';
	if(empty($form_data['dop_cena']))$form_data['dop_cena']='';
	if(empty($form_data['imgdata']))$form_data['imgdata']='';
	if(empty($form_data['anotace']))$form_data['anotace']='';
  if(empty($form_data['nejprodavanejsi']))$form_data['nejprodavanejsi']='';
	if(empty($pid))$pid='';
	if(empty($js))$js='';	

	// fotky k produktu do noveho okna
    $foto_produktu = '<a class="fancybox_foto" href="/admin/shop/products/products_foto.php?id='.$form_data['id'].'">Fotky produktu </a> &raquo;';

	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		var x = form1.cena.value;
		x = x.replace(/,/g,'.'); // nahrada carky za tecku
		x = x.replace(/ /g,''); // nahrada (zde jen odstraneni) mezery
		
		if (form1.name.value == \"\") { alert(\"Vyplňte název produktu nebo skupiny produktů\"); form1.name.focus(); return false; }
// 		else if (!(x > 0)) { alert(\"Cena musí být vyšší než 0\"); form1.cena.focus(); return false; }
		//else if (form1.id_parent.value == \"\") { alert(\"Musíte vybrat některou z vnořených úrovní\"); form1.id_parent.focus(); return false; }
		else return true;
	
	}
	
	
	// odstraneni zaznamu
	function del() {
	
		if (confirm(\"Opravdu odstranit?\"))
			{ location = \"".$form_data['link']."&delete=".$form_data['id']."&cat=".$_GET['cat'].$form_data['add_del']."\"; }
	
	}
	
	
	// -->
	</SCRIPT>
	
	
	<form action=\"\" method=\"post\" enctype=\"multipart/form-data\" 
		onSubmit=\"return validate(this)\">
	
	".$buttony."
	
	<br /><br />
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	<input type=\"hidden\" name=\"oldParams\" value=\"".$pid."\">
	<input type=\"hidden\" name=\"action\" value=\"".$_GET['a']."\">
	
	
	
	<table class='admintable nobg' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">

  <tr><td colspan=\"2\"><strong>Nastavení feedu</strong></td></tr>

	<tr>
		<td class=\"tdleft\">Neupřednostňovat na zbozi.cz</td>
		<td class=\"tdright\"><input type=\"checkbox\" name=\"uprednostnit_zbozi\" value=\"1\" ".$form_data['uprednostnit_zbozi']."></td>
	</tr>

	<tr>
		<td class=\"tdleft\">CPC zbozi.cz</td>
		<td class=\"tdright\"><input type=\"text\" name=\"cpc_zbozi\" value=\"".$form_data['cpc_zbozi']."\" style=\"width: 50px;\" class=\"f10\"> <span class=\"f10i\">1 až 500 Kč.</span></td>
	</tr>

	<tr>
		<td class=\"tdleft\">CPC heureka.cz</td>
		<td class=\"tdright\"><input type=\"text\" name=\"cpc_heureka\" value=\"".$form_data['cpc_heureka']."\" style=\"width: 50px;\" class=\"f10\"> <span class=\"f10i\">Maximální cena za klik je 100 Kč.</span></td>
	</tr>


  <tr><td colspan=\"2\"><br /></td></tr>


	<tr>
		<td width=\"180\"></td>
		<td width=\"320\">
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" ".$form_data['hidden']."> Nezobrazovat produkt
		</td>
	</tr>

	<tr>
		<td width=\"180\"></td>
		<td width=\"320\">
			<input type=\"checkbox\" name=\"zobraz_pocet_kusu\" value=\"1\" ".$form_data['zobraz_pocet_kusu']."> Nezobrazovat pocet kusů
		</td>
	</tr>

	<tr><td colspan=\"2\"><br /></td></tr>

	<tr>
	     <td width=\"180\"></td>
	     <td width=\"320\">
         <input type=\"checkbox\" name=\"nejprodavanejsi\" value=\"1\" ".$form_data['nejprodavanejsi']."> Nejprodávanější<br />
<!--         <input type=\"checkbox\" name=\"prednost\" value=\"1\" ".$form_data['prednost']."> Přednost v kategorii<br />
-->
	       <input type=\"checkbox\" name=\"akce\" value=\"1\" ".$form_data['akce']."> Akční nabídka<br />
	       <input type=\"checkbox\" name=\"novinka\" value=\"1\" ".$form_data['novinka']."> Novinka<br />
	       <input type=\"checkbox\" name=\"doporucujeme\" value=\"1\" ".$form_data['doporucujeme']."> Doporučujeme<br />
	     </td>
	</tr>
	
  <tr><td colspan=\"2\"><br /></td></tr>
	
	<tr>
		<td class=\"tdleft\">Název</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"name\" value=\"".$form_data['name']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td class=\"tdleft\">Hmotnost výrobku v gramech<br /> (pro počítání dopravného)</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"hmotnost\" value=\"".$form_data['hmotnost']."\" 
			style=\"width: 50px;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td class=\"tdleft\">Pořadí výrobku</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"poradi\" value=\"".$form_data['poradi']."\" 
			style=\"width: 50px;\" class=\"f10\"></td>
	</tr>

	<tr>
		<td class=\"tdleft\">Kód</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"kod\" value=\"".$form_data['kod']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>

	<tr>
		<td class=\"tdleft\">Kód 2</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"kod2\" value=\"".$form_data['kod2']."\"
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td class=\"tdleft\">Výrobce</td>
		<td class=\"tdright\">".$form_data['id_vyrobce']."</td>
	</tr>
	
	<tr>
		<td width=\"180\">
	  Dodací lhůta 
	  </td>
	  <td width=\"320\">
	  $dodaniOptions     <br />
	  /nezadáno = defaultně 2 dny/
		</td>
	</tr>
	
	
	<tr>
		<td class=\"tdleft\">
			Cena ".SBEZDPH."<br />
      <span class=\"f10i\">Cena z iSoftu.</span>
    </td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"cena\" value=\"".$form_data['cena']."\" style=\"width: 100px;\" class=\"f10\">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			DPH:
      <input type=\"radio\" name=\"dph\" value=\"21\" ".$form_data['dph21.000']."> 21%
			<input type=\"radio\" name=\"dph\" value=\"15\" ".$form_data['dph15.000']."> 15%
		</td>
	</tr>

	<tr>
		<td class=\"tdleft\">
			Cena ne eshopu ".SBEZDPH."<br />
      <span class=\"f10i\">Použije se místo ceny z iSoftu.</span>
    </td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"cena_eshop\" value=\"".$form_data['cena_eshop']."\" style=\"width: 100px;\" class=\"f10\">
		</td>
	</tr>
	
<!--
	<tr>
		<td class=\"tdleft\">&nbsp;</td>
		<td width=\"302\" class=\"f10i\">
			Neuvedete-li cenu u Skupiny výrobků, nebude možné produkty v ní objednat jako celou skupinu ale pouze jednotlivě.
		</td>
	</tr>
-->
	
	
	<tr>
		<td class=\"tdleft\">
			Běžná cena ".SBEZDPH."<br />
      <span class=\"f10i\">Pro výpočet slevy.</span>
    </td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"dop_cena\" value=\"".$form_data['dop_cena']."\" style=\"width: 100px;\" class=\"f10\">
		</td>
	</tr>
	
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	
	
	<tr>
		<td class=\"tdleft\" valign=\"top\">
			".$foto_produktu."</td>
	</tr>
		
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	
	
	<tr>
		<td class=\"tdleft\" valign=\"top\"><a href=\"\" class=\"click\" onclick=\"s('divfiles'); return false;\">Připojit soubory ke stažení</a> &raquo;</td>
		<td class=\"tdright\">
      <div id=\"divfiles\">
			$select
			</div>
		</td>
	</tr>
	
	<!--<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	<tr>
		<td class=\"tdleft\" valign=\"top\"><a href=\"\" class=\"click\" onclick=\"s('divvideofiles'); return false;\">Připojit video soubory</a> &raquo;</td>
		<td class=\"tdright\">
      <div id=\"divvideofiles\">
			$selectVideo
			</div>
		</td>
	</tr>-->
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>	
		
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>

	<tr>
		<td class=\"tdleft\" valign=\"top\"><a href=\"\" class=\"click\" onclick=\"s('divpribuzne'); return false;\">Příbuzné (související) výrobky</a> &raquo;</td>
		<td class=\"tdright\"><div id=\"divpribuzne\">$select_P</div></td>
	</tr>
	
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	
	
	<tr>
		<td class=\"tdleft\" valign=\"top\">Produktový list</td>
		<td class=\"tdright\">$editList</td>
	</tr>
	
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	
	
	<tr>
		<td colspan=\"2\">
			<br /><br />
			Krátký popis / anotace<br />
			<textarea name=\"anotace\" style=\"width: 100%; height: 60px;\" 
			class=\"f11\">".$form_data['anotace']."</textarea></td>
	</tr>
	
	
	<tr>
		<td colspan=\"2\">
			<br />Dlouhý popis<br />
			$editor
		</td>
	</tr>
	
	
	<tr>
		<td colspan=\"2\"><br /><br /><br />
			
			".$buttony."
		
		</td>
	</tr>
	
	</table>
	
	
	
	<div class=\"kategorie_zarazeni\">
		<b>Zařadit(upřednostnit) do kategorie</b><br /><br />
		".$form_data['id_parent']."<br /><br />
	</div>
	<br /><br />
  $SEO
	</form>
  
  <script type=\"text/javascript\">
	<!--
   document.getElementById('divpribuzne').style.display='none';
   document.getElementById('divfiles').style.display='none';
   document.getElementById('divvideofiles').style.display='none';
  ".$js."
	-->
	</script>
  
  
  ";
	
	return $form;//

}
// *****************************************************************************
// formular pro editaci
// *****************************************************************************


// *****************************************************************************
// vyhledavani
// *****************************************************************************
function search1($search,$column,$found_points,$line,$points,$found_names,$addWhere) {

	// $search - hledany vyraz
	// $column - prohledavany sloupec tabulky
	// $found_points - pole s ulozenymi body za nalez fraze v urcitem sloupci tabulky - kazdemu 
	// sloupci lze priradit jinou vahu vyjadrenou prave poctem bodu $points
	// $points - pocet bodu za nalez - urcuje tak prioritu/vahu
	// zjistujeme pocet vyskytu v nalezenem zaznamu
	
	
	global $found_points,$found_names;
	
	
	// prevedeme hledanou frazi na mala pismena
	$search = strtoL($search);
	
	
	
	// hledame ve sloupci $column, mimo zaznamy $addWhere
	// id_good id_cat lang
	// id id_cat name img text hidden akce cena dph lang kod id_vyrobce anotace dop_cena
	$query = "SELECT id, name, $column AS $column 
	FROM ".T_GOODS." WHERE ".SQL_C_LANG." ";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while($z = mysql_fetch_array($v)) {
	
		$sID = $z['id'];
		$c = $z[$column];
		$nazev = $z['name'];
		
		$p = @substr_count(strtoL($c), $search); // kolikrat se v sloupci vyraz vyskytuje
		$add_points = $p * $points; // tolik pricteme bodu
		
		// if($p > 0)
		// echo "$sID - $nazev - $p<br />";
		
		
		
		if($add_points > 0) {
		
			if(!empty($found_points[$sID])) {
			
				unset($found_names[$found_points[$sID]][$sID]);
				$found_points[$sID] = $found_points[$sID] + $add_points;
			
			}
			if(empty($found_points[$sID])) $found_points[$sID] = $add_points;
			
			$found_names[$found_points[$sID]][$sID] = $nazev;
		
		}
	
	}
		

}






// kompletní hledání
function search2($search,$found_points,$found_names,$addWhere) {

	global $found_points, $found_names;
	
	
	
	// zkousime najit celou zadanou frazi
	// id id_cat name img text hidden akce cena dph lang kod id_vyrobce anotace dop_cena
	search1($search,"name",$found_points,__LINE__,1000,$found_names,$addWhere);
	
	
	// rozdelime na slova
	$slovo = split("[[:blank:]]|(,)|(\.)|(:)|(\?)|(!)|(;)|(\")|(\()|(\))|(\[)|(\])", $search);//(-)|(')|(…)|(_)|
	
	
	
	for($y = 0; $y < count($slovo); $y++) {
	
		$bonus = 0;
		
		$sl = strtoL($slovo[$y]);
		
		// id id_cat name img text hidden akce cena dph lang kod id_vyrobce anotace dop_cena
		search1($slovo[$y],"name",$found_points,__LINE__,1,$found_names,$addWhere);
		search1($slovo[$y],"kod",$found_points,__LINE__,1,$found_names,$addWhere);
		search1($slovo[$y],"anotace",$found_points,__LINE__,1,$found_names,$addWhere);
		search1($slovo[$y],"text",$found_points,__LINE__,1,$found_names,$addWhere);
	
	}

}








if (isset($_POST['search'])) { //


	$nadpis = "Vyhledávání \"".$_POST['search']."\"";
	
	$search = trim($_POST['search']);
	
	if(empty($addWhere))$addWhere=null;
	if(empty($found_names))$found_names=null;
	if(empty($found_points))$found_points=null;
	
	$t = str_replace(" ", "", $search);
	$poc_znaku = strlen($t);
	
	if($poc_znaku > 2) {
	
		search2($search,$found_points,$found_names,$addWhere);
		
		// byly nalezeny polozky
		if(count($found_points) > 0) {
		
			// seradime podle poctu bodu ktere byly prirazeny pri hledani
			@asort($found_points); // cisele serazeni
			@reset($found_points);
			
			/*while ($p = each($found_points)) {
			
				$n = $p['key'];
				$h = $p['value'];
				// echo "$n - $h<br />";// (".$found_names[$n].")
			
			}*/
			
			//echo "xxxxx";exit;
			
			// seradime a prevedeme na vysledne pole $found
			krsort($found_names);
			reset($found_names);
			while ($p = each($found_names)) {
			
				$n = $p['key'];
				$h = $p['value'];
				//echo "$h ...... $n<br />";// - $h (".$found_names[$n].")
				
				if(!empty($found_names[$n])) {
				
					natcasesort($found_names[$n]);
					reset($found_names[$n]);
					while ($p2 = each($found_names[$n])) {
					
						$n2 = $p2['key'];//echo "<br />".
						$h2 = $p2['value'];//echo" ".
						
						// echo "$n... $h2 ...$n2<br />";// - $h (".$found_names[$n].")// 
						$found[] = $n2;
					
					}
				
				}
				
				
				
			
			}//exit;
			
			
			
			
			$x = 0;
			$data='';
			reset($found);
			while ($p = each($found)) {
			
				//$ID = $p['key'];
				$ID = $p['value'];
				
				
// 				if($x >= $sql_od && $x < $sql_do) {
				
					// zbozi z db
					// id id_cat name img text hidden akce cena dph lang kod id_vyrobce
					$query = "SELECT name,kod,hidden FROM ".T_GOODS." WHERE id = $ID";
					$v = my_DB_QUERY($query,__LINE__,__FILE__);
					
					
					
					while ($z = mysql_fetch_array($v)) {
					
						$name = $z['name'].' '.$z['kod'];
						
						
						if($z['hidden'] == 1) $pstyle = "class=\"gray\"";
						else $pstyle = "";
						
						
						$data .= "<a href=\"".MAIN_LINK."&f=products&id=$ID&a=edit&cat=".$_GET['cat']."\" 
						title=\"Upravit\" $pstyle>$name</a><br />";
					
					}
				
// 				}
				
				$x++;
			
			}
		
		}
	
	}
	else $data = "Prosíme upravte hledanou frázi tak, aby obsahovala nejméně 3 znaky.";
	
	
	if(empty($data)) $data = "Hledanému výrazu neodpovídá žádný záznam.";
	
// 	echo $data;
// exit;

}
// *****************************************************************************
// vyhledavani
// *****************************************************************************







// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if(isset($_GET['a']) && ($_GET['a']=='edit' || $_GET['a']=='add') && !empty($_POST) && !isset($_POST['search']))
{
  $dph = $_POST['dph'];
	if(empty($dph))
  {
		$_SESSION['alert'] = "Nebyla uvedena výše DPH, nic nebylo uloženo.";
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	}

	if(empty($_POST['id_parent']))
  {
		$_SESSION['alert'] = "Zařaďte produkt do některé z kategorií. Nic nebylo uloženo.";
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	}

	$name = trim($_POST['name']);
	$kod = trim($_POST['kod']);
  $kod2 = trim($_POST['kod2']);
	$hmotnost = trim($_POST['hmotnost']);
	$id_vyrobce = $_POST['id_vyrobce'];
	$id_dodani = $_POST['dodaci_lhuta'];
	$id_karta = $_POST['params']; // novy vzor produkt. listu
	$oldParams = $_POST['oldParams']; // stary vzor produkt. listu
	
	// nastaveni skryti poctu produktu na sklade
	$zobraz_pocet_kusu = $_POST['zobraz_pocet_kusu'];
	if ($zobraz_pocet_kusu != 1) $zobraz_pocet_kusu = 0;
	
	// nastaveni skryti produktu
	$hidden = $_POST['hidden'];
	if ($hidden != 1) $hidden = 0;
	
	$cena = trim($_POST['cena']);
	if(empty($cena)) $cena = 0;
	$trans = array (" " => "", "," => ".");
	$cena = strtr($cena, $trans);

	$cena_eshop = strtr(trim($_POST['cena_eshop']), $trans);
	
	// doporucena cena
	$dop_cena = trim($_POST['dop_cena']);
	if(empty($dop_cena)) $dop_cena = 0;
	$trans = array (" " => "", "," => ".");
	$dop_cena = strtr($dop_cena, $trans);
	
	if(empty($hmotnost)) $hmotnost = 0;

	// akcni nabidka
	$akce = $_POST['akce'];
	if ($akce != 1) $akce = 0;
	$doporucujeme = $_POST['doporucujeme'];
	if ($doporucujeme != 1) $doporucujeme = 0;
	$prednost = $_POST['prednost'];
	if ($prednost != 1) $prednost = 0;
	$novinka = $_POST['novinka'];
	if ($novinka != 1) $novinka = 0;

  $poradi = trim($_POST['poradi']);
	if(empty($poradi))$poradi=0;

	$text = trim(addslashes($_POST['text']));
	
	$anotace = trim($_POST['anotace']);
	

  // Společné hodnoty pro insert i update.
  $query_set = "
	name = '".$name."',
	text = '".$text."',
	hidden = '".intval($hidden)."',
	akce = '".intval($akce)."',
	doporucujeme = '".intval($doporucujeme)."',
	prednost = '".intval($prednost)."',
	novinka = '".intval($novinka)."',
	poradi = '".intval($poradi)."',
	id_dodani = '".intval($id_dodani)."',
	cena = '".$cena."',
  cena_eshop = '".$cena_eshop."',
	hmotnost = '".intval($hmotnost)."',
	dop_cena = '".$dop_cena."',
	dph = '".$dph."',
	id_vyrobce = '".intval($id_vyrobce)."',
	kod = '".$kod."',
  kod2 = '".$kod2."',
	anotace = '".$anotace."',
	zobraz_pocet_kusu = '".intval($zobraz_pocet_kusu)."',
  cpc_zbozi = '".trim($_POST["cpc_zbozi"])."',
  cpc_heureka = '".trim($_POST["cpc_heureka"])."',
  uprednostnit_zbozi = '".intval($_POST["uprednostnit_zbozi"])."',
  nejprodavanejsi = '".intval($_POST["nejprodavanejsi"])."'
  ";
	
	if(!empty($_POST['id']))
  { // aktualizace
		$id = intval($_POST['id']);

		$query = "
    UPDATE ".T_GOODS."
    SET 
    ".$query_set."
		WHERE id = '".$id."'
    AND ".SQL_C_LANG;
		my_DB_QUERY($query,__LINE__,__FILE__);
	}
  else
  { // novy zaznam
		//$query = "INSERT INTO ".T_GOODS." VALUES(NULL,'$name','','$text',$hidden,$hmotnost,$akce,$doporucujeme,$prednost,$novinka,$poradi,'$id_dodani',$cena,$dph,'".C_LANG."','$kod','$kod2',$id_vyrobce,'$anotace',$dop_cena,'',NULL,'0','-1' , '".$zobraz_pocet_kusu."')";
		$query = "
    INSERT INTO ".T_GOODS."
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
		
		$_SESSION['last_id_vyrobce'] = $id_vyrobce;
		
		$_SESSION['last_id_parent'] = $_POST['id_parent'];
	
	}
	
	
	
	
	// vymazeme puvodni zarazeni do kategorii
	// id_good id_cat lang
	$query = "DELETE FROM ".T_GOODS_X_CATEGORIES." 
	WHERE id_good = $id AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_X_CATEGORIES);
	
	
	// ***************************************************************************
	// pokud je moznost zarazeni do vice kategorii, posila z formu se pole
	foreach($_POST['id_parent'] as $id_cat){
	
		$query = "INSERT INTO ".T_GOODS_X_CATEGORIES." VALUES($id,$id_cat,'".C_LANG."',0)";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}

	foreach($_POST['id_upr'] as $id_cat){	
		$query = "UPDATE ".T_GOODS_X_CATEGORIES." SET uprednostnit=1 where id_good=$id and id_cat=$id_cat";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	// zarazeni pouze do jedne kategorie
// 	$query = "INSERT INTO ".T_GOODS_X_CATEGORIES." VALUES($id,".$_POST['id_parent'].",'".C_LANG."')";
// 	my_DB_QUERY($query,__LINE__,__FILE__);
	// ***************************************************************************
	
	

	
	// prirazene soubory ke stazeni
	// zrusime puvodni prirazeni souboru
	$query = "DELETE FROM ".T_GOODS_X_DOWNLOAD." 
	WHERE id_good = $id AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_X_DOWNLOAD);
	
	// jsou-li prirazeny nejake soubory, ulozime je do DB
	if(!empty($_POST['files'])) {
	
		reset($_POST['files']);
		while ($p = each($_POST['files'])) {
		
			$query = "INSERT INTO ".T_GOODS_X_DOWNLOAD." VALUES($id,".$p['value'].",'".C_LANG."')";
			my_DB_QUERY($query,__LINE__,__FILE__);
		
		}
	
	}
	if(!empty($_POST['videofiles'])) {
	
		reset($_POST['videofiles']);
		while ($p = each($_POST['videofiles'])) {
		
			$query = "INSERT INTO ".T_GOODS_X_DOWNLOAD." VALUES($id,".$p['value'].",'".C_LANG."')";
			my_DB_QUERY($query,__LINE__,__FILE__);
		
		}
	
	}
	
	
	// pribuzne produkty
	// zrusime puvodni
	// T_GOODS_PRIBUZNE id_good  id_pribuzne
	$query = "DELETE FROM ".T_GOODS_PRIBUZNE." 
	WHERE id_good = $id";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_PRIBUZNE);
	
	// jsou-li prirazeny nejake soubory, ulozime je do DB
	if(!empty($_POST['pribuzne'])) {
	
		reset($_POST['pribuzne']);
		while ($p = each($_POST['pribuzne'])) {
		
			// T_GOODS_PRIBUZNE id_good  id_pribuzne
			if($p['value'] != 'NO') {
			
				$query = "INSERT INTO ".T_GOODS_PRIBUZNE." VALUES($id,".$p['value'].")";
				my_DB_QUERY($query,__LINE__,__FILE__);
			
			}
		
		}
	
	}

	
	$_SESSION['alert_js'] = "Záznam uložen";
	
	
	if($_POST['action'] == 'copy') $back = MAIN_LINK.'&f=products&id='.$id.'&a=edit';
	elseif(!empty($_POST['novy_zaznam'])) $back = MAIN_LINK.'&f=products&id='.$id.'&a=edit'; 
	else $back = $_SERVER['HTTP_REFERER'];
	
  //vložení SEO
  uloz_seo($_POST,3);//kcemu ... 1-clanek,2-kategorie,3-produkt  	
	
	Header("Location: ".$back);
	exit;

}
// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// editace produktu (form)
// *****************************************************************************
if(isset($_GET['a']) && ($_GET['a'] == "edit" || $_GET['a'] == "copy")) {

	$timestamp=time().microtime(); // refresh obrazku
	
	// id id_cat name img text hidden akce cena dph id_vyrobce anotace dop_cena
	$query = "SELECT *, 
  DATE_FORMAT(last_update,'%d.%m.%Y %H:%i:%s') as datum_format
  FROM ".T_GOODS." 
	WHERE id = ".$_GET['id']." AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_assoc($v))
  {
		$form_data['name'] = $z['name'];
		$form_data['text'] = $z['text'];
		$form_data['kod'] = $z['kod'];
    $form_data['kod2'] = $z['kod2'];
		$form_data['poradi'] = $z['poradi'];
		$form_data['hmotnost'] = $z['hmotnost'];
		$form_data['anotace'] = $z['anotace'];

    $form_data['cpc_zbozi'] = $z['cpc_zbozi'];
    if($form_data['cpc_zbozi'] == 0) $form_data['cpc_zbozi'] = "";

    if($z['uprednostnit_zbozi'] == 1) $form_data['uprednostnit_zbozi'] = 'checked="checked"';
    else $form_data['uprednostnit_zbozi'] = "";

    if($z['nejprodavanejsi'] == 1) $form_data['nejprodavanejsi'] = 'checked="checked"';
    else $form_data['nejprodavanejsi'] = "";

    $form_data['cpc_heureka'] = $z['cpc_heureka'];
    if($form_data['cpc_heureka'] == 0) $form_data['cpc_heureka'] = "";
		
		if($z['zobraz_pocet_kusu'] == 1) $form_data['zobraz_pocet_kusu'] = 'checked="checked"';
		else $form_data['zobraz_pocet_kusu'] = "";

		if($z['hidden'] == 1) $form_data['hidden'] = 'checked="checked"';
		else $form_data['hidden'] = "";

		if($z['akce'] == 1) $form_data['akce'] = 'checked="checked"';
		else $form_data['akce'] = "";

		if($z['doporucujeme'] == 1) $form_data['doporucujeme'] = 'checked="checked"';
		else $form_data['doporucujeme'] = "";

		if($z['prednost'] == 1) $form_data['prednost'] = 'checked="checked"';
		else $form_data['prednost'] = "";

		if($z['novinka'] == 1) $form_data['novinka'] = 'checked="checked"';
		else $form_data['novinka'] = "";


		if($_GET['a'] == "copy") {
		
			$nadpis = '<strong style="color: red;">[ KOPIE ] </strong>'.$form_data['name'].'';
		
		} else {
		
			$nadpis = $form_data['name'];
		
		}
		
		
		

		
		$form_data['cena'] = number_format($z['cena'],2,","," ");
    $form_data['cena_eshop'] = number_format($z['cena_eshop'],2,","," ");
		$form_data['dop_cena'] = number_format($z['dop_cena'],2,","," ");
		
		$dph_n = "dph".$z['dph'];
		$form_data[$dph_n] = "checked";
		
		// roleta vyrobcu
		$form_data['id_vyrobce'] = producers_select($z['id_vyrobce'],$dct);
	}
	
	
	
	if(!empty($form_data)) {
	
	  if(isset($_SESSION['last_id_parent'])){
      $cat="&cat=".$_SESSION['last_id_parent'];
    }else{
      $cat='';
    } 
     	
		$form_data['id'] = $_GET['id'];
		$form_data['link'] = MAIN_LINK."&f=products".$cat;
		$form_data['deletebutton'] = DELETE_BUTTON;
		$form_data['movebutton'] = MOVE_BUTTON;
		
		// zjistime do kterych kategorii produkt patri
		// id_good id_cat lang
		$query = "SELECT id_cat FROM ".T_GOODS_X_CATEGORIES." 
		WHERE id_good = ".$_GET['id']." AND ".SQL_C_LANG."";
		$v2 = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$par_array = array();
		while ($z2 = mysql_fetch_array($v2)) {
		
			$par_array[$z2['id_cat']] = $z2['id_cat'];
		
		}
		
		
		// vygenerujeme pole s kategoriemi
		if(empty($cat_array)) {
		
			$cat_array = array();
			categories_array($parent_id=0,$cat_array,$level=0);
		
		}
		
		
		$parrents=zarazeniProduktu($form_data['id']);
		
		// kategorie
		$form_data['id_parent'] = categories_checkbox($cat_array,$par_array,$dct,$parrents,$_GET['id']); // categories_select
		
    //vložení SEO
    $SEO_data=nacti_seo($_GET['id'],3);//kcemu ... 1-clanek,2-kategorie,3-produkt
    $form_data['seo_title']=$SEO_data['seo_title'];
    $form_data['seo_keywords']=$SEO_data['seo_keywords'];
    $form_data['seo_description']=$SEO_data['seo_description'];
    $form_data['seo_foot']=$SEO_data['seo_foot'];
        
		$data = form($form_data,$dct);
	
	}
	else $data = "Záznam nenalezen";

}
// *****************************************************************************
// editace produktu (form)
// *****************************************************************************










// *****************************************************************************
// seznam produktu v kategorii
// *****************************************************************************
if((!empty($_GET['cat']) && !isset($_POST['search'])) && !isset($_GET['id'])) {

	$query = "SELECT name FROM ".T_CATEGORIES." 
	WHERE id = ".$_GET['cat']." AND ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		$cat_name = $z['name'];
	
	}
	
	
	
	$nadpis = "Produkty ($cat_name)";
	
	
	
	$ch_cat=null; // prednastavime $ch_cat
	children_in_category($_GET['cat'],$ch_cat);
	
	
	// vygenerujeme dotaz na kategorie
	if(!empty($ch_cat)) {
	
		reset($ch_cat);
		$Qcat='';
		while ($p = each($ch_cat)) {
		
			$Qcat .= "".T_GOODS_X_CATEGORIES.".id_cat = ".$p['value']." OR ";
		
		}
	
	}
	
	
	if(!empty($Qcat)) $Qcat = "(".substr($Qcat, 0, -4).")";

  	if(!empty($_GET['cat']) && notHaveChildren($_GET['cat'])){
  	  $button="      
      <input type=\"submit\" name=\"presun\" value=\"Přesunout označené do vybraných kategorií\" class=\"butt_ostatni\">
      <br />
      ";

  	}else{
      $button="";
    }
  	


	
	$query="SELECT ".T_GOODS_X_CATEGORIES.".id_good AS id 
  	from ".T_GOODS_X_CATEGORIES." 
	WHERE $Qcat 
	AND ".T_GOODS_X_CATEGORIES.".".SQL_C_LANG." 
	group by ".T_GOODS_X_CATEGORIES.".id_good";
	
// 	echo $query;
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$rows=mysql_num_rows($v);
	
	$naStrane=40;
	if(empty($_GET['page']))$_GET['page']=1;
	
	$od=($_GET['page']*$naStrane)-$naStrane;
	
	
	$strankovani=strankovani($rows,$naStrane,'http://'.$_SERVER['HTTP_HOST'].'/admin/index.php?C_lang=1&app=shop&f=products&cat='.$_GET['cat']);
	
	
// 	$query = "SELECT * FROM ".T_GOODS." 
// 	WHERE id_cat > 0 AND ".SQL_C_LANG." 
// 	ORDER BY name";
	
	
	$query = "SELECT ".T_GOODS_X_CATEGORIES.".id_good AS id, 
	".T_GOODS.".hidden AS hidden, 
	".T_GOODS.".name AS name,
  ".T_GOODS.".kod AS kod,
  ".T_GOODS.".poradi AS poradi, 
  ".T_GOODS.".akce AS akce,
  ".T_GOODS.".doporucujeme AS doporucujeme,
  ".T_GOODS.".prednost AS prednost,
  ".T_GOODS.".novinka AS novinka 
	FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES." 
	WHERE $Qcat 
	AND ".T_GOODS.".id = ".T_GOODS_X_CATEGORIES.".id_good 
	AND ".T_GOODS_X_CATEGORIES.".".SQL_C_LANG." 
	group by ".T_GOODS_X_CATEGORIES.".id_good
	ORDER BY ".T_GOODS.".poradi,".T_GOODS.".name limit $od,$naStrane";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$poc_x = $od;
	$seznam='';
  while ($z = mysql_fetch_array($v)) {
	
	$poc_x++;
	
		if($z['hidden'] == 1) $pstyle = "class=\"gray\"";
		else $pstyle = "";
		$dop='';
	  if($z['akce'] == 1) $dop.= "<span title='Akční nabídka' style=\"color:red\">[A]</span>";
		if($z['doporucujeme'] == 1) $dop.= "<span title='Doporučujeme' style=\"color:red\">[D]</span>"; 
		if($z['prednost'] == 1) $dop.= "<span title='Přednostní zobrazení' style=\"color:red\">[P]</span>";
		if($z['novinka'] == 1) $dop.= "<span title='Novinka' style=\"color:red\">[N]</span>";
		if($z['hidden'] == 1) $hid='<strong>(Neaktivní)</strong>';
		else $hid='';

    $seznam .= "
			<input type=\"checkbox\" name=\"preradit[]\" value=\"".$z['id']."\">
			<a style='position: relative; top: 3px; left: 0;' href=\"".MAIN_LINK."&f=products&id=".$z['id']."&a=showcomments&cat=".$_GET['cat']."\" title=\"Zobrazit komentáře\" $pstyle><img src='./icons/ikona_files.gif' /></a> 
			<!--(".$poc_x.")--> 
			<span style='color: #b40000;'>[".$z['poradi']."]</span> 
			".$dop." 
    			<a href=\"".MAIN_LINK."&f=products&id=".$z['id']."&a=edit&cat=".$_GET['cat']."\" title=\"Upravit\" $pstyle>
				".$z['name']." ".$z['kod']."
			</a> 
			$hid
		<br />";

/*		
		$seznam .= "
		<a href=\"".MAIN_LINK."&f=products&id=".$z['id']."&a=edit&cat=".$_GET['cat']."\" 
		title=\"Upravit\" $pstyle>".$z['name']."</a><br />";
*/	
	}
	
	
	
	
	if(!empty($seznam)) {
		
		
		// vygenerujeme pole s kategoriemi
		if(empty($cat_array)) {
		
			$cat_array = array();
			categories_array($parent_id=0,$cat_array,$level=0);
		
		}
	
	   
	  if(empty($par_array))$par_array=null;
	
    $buttonCopy = '<input type="submit" name="kopirovani" value="Kopírovat do vybraných kategorií" class="butt_ostatni"><br />';
    $button_uprednostnit_zbozi = '
    <div style="text-align:left; float:right; margin-right:150px; width:260px; overflow:hidden;">
      <strong>Nastavení feedu</strong><br />
      <input type="checkbox" name="uprednostnit_zbozi" value="1"> Neupřednostňovat označené na zbozi.cz<br />
      <input style="float:right; margin-top:10px;" type="submit" name="submit_uprednostnit_zbozi" value="Uložit" class="butt_ostatni">
    </div>
    ';
		
    $check_all = '
    <script type="text/javascript">
    function checkall(dep)
    {
      var prerad = document.myform[\'preradit[]\'];

      for (var i = 0; i < prerad.length; i++)
      {
        prerad[i].checked = dep.checked ? true : false;
      }
    }
    </script>

    <input type="checkbox" name="checkit" value="1" onclick="checkall(this);"> Zaškrtnout vše<br />
    <br />
    ';

    $seznam = "
    
    <form id=\"myForm\" action=\"\" method=\"post\"  name=\"myform\">
    ".$button_uprednostnit_zbozi."
    $strankovani<br />

  	<div style=\"position: absolute; top: 90px; left: 700px; width: 280px;\">
  		<b>Zařadit do kategorie</b><br /><br />
  		".categories_checkbox($cat_array,$par_array,$dct)."<br /><br />
  	</div>
    
    ".$check_all."
    ".$seznam."
    ".$check_all."
  
    <br /><br />
    $strankovani
    <br />
    <span style=\"color:#b40000\">[nr.]</span> Pořadí v e-shopu. <br />
    <span style=\"color:red\">[D]</span> Doporučujeme. <br />
    <span style=\"color:red\">[P]</span> Přednost v kategorii.<br />
    <span style=\"color:red\">[N]</span> Novinka.<br /> 
    <span style=\"color:red\">[A]</span> Akční nabídka.<br />
    <br />

    
    $button <br />
    $buttonCopy
    
    </form>";
    $data = SEARCH_PANEL.$seznam;//."$query<br /><br />"
	}
  else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	unset($_SESSION['last_id_parent']);	
	
	
	

	
	
	/*
	if(!empty($seznam)) $data = SEARCH_PANEL.$seznam;//."$query<br /><br />"
	else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	unset($_SESSION['last_id_parent']);
*/

}
// *****************************************************************************
// seznam produktu v kategorii
// *****************************************************************************


// *****************************************************************************
// seznam nezarazenych
// *****************************************************************************
if(isset($_GET['a']) && $_GET['a']=='nocategory') {
	
	$nadpis = "Produkty nezařazené";
	
	if(empty($_GET['hidden']))$hidden = '';
	else $hidden='and a.hidden='.$_GET['hidden'];
	
	$query="select id FROM ".T_GOODS." a
	LEFT JOIN ".T_GOODS_X_CATEGORIES." b ON a.id = b.id_good
	WHERE b.id_cat IS NULL 
	$hidden
	GROUP BY a.id
	ORDER BY a.name";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$rows=mysql_num_rows($v);
	
	$naStrane=40;
	if(empty($_GET['page']))$_GET['page']=1;
	
	$od=($_GET['page']*$naStrane)-$naStrane;
	
  $button='<input type="submit" name="presun" value="Přesunout označené do vybraných kategorií" class="butt_ostatni">
          <br />
          ';
	
	$buttonCopy='<input type="submit" name="kopirovani" value="Kopírovat do vybraných kategorií" class="butt_ostatni">
          <br />
          ';
	
	$strankovani=strankovani($rows,$naStrane,'http://'.$_SERVER['HTTP_HOST'].'/admin/index.php?C_lang=1&app=shop&f=products&a=nocategory&hidden='.$_GET['hidden']);
	
	
	$query = "SELECT * 
	FROM ".T_GOODS." a
	LEFT JOIN ".T_GOODS_X_CATEGORIES." b ON a.id = b.id_good
	WHERE b.id_cat IS NULL 
	$hidden
	GROUP BY a.id
	ORDER BY a.name
  limit $od,$naStrane";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$poc_x = $od;
	while ($z = mysql_fetch_array($v)) {
	
	$poc_x++;
	
		if($z['hidden'] == 1) $pstyle = "class=\"gray\"";
		else $pstyle = "";
		$dop='';
	  if($z['akce'] == 1) $dop.= "<span title='Doporučujeme' style=\"color:red\">[D]</span>";
		if($z['akced'] == 1) $dop.= "<span title='Akční nabídka' style=\"color:red\">[A]</span>";
		if($z['prednost'] == 1) $dop.= "<span title='Přednostní zobrazení' style=\"color:red\">[P]</span>";
		if($z['novinka'] == 1) $dop.= "<span title='Novinka' style=\"color:red\">[N]</span>";
	

    $seznam .= "<input type=\"checkbox\" name=\"preradit[]\" value=\"".$z['id']."\">(".$poc_x.") ".$dop." 
    <a href=\"".MAIN_LINK."&f=products&id=".$z['id']."&a=edit\" 
		title=\"Upravit\" $pstyle>".$z['name']."</a><br />";

/*		
		$seznam .= "
		<a href=\"".MAIN_LINK."&f=products&id=".$z['id']."&a=edit&cat=".$_GET['cat']."\" 
		title=\"Upravit\" $pstyle>".$z['name']."</a><br />";
*/	
	}
	
	if(!empty($seznam)) {
		
		
		// vygenerujeme pole s kategoriemi
		if(empty($cat_array)) {
		
			$cat_array = array();
			categories_array($parent_id=0,$cat_array,$level=0);
		
		}
	
		
    $seznam = "
    
    <br />$strankovani<br /><br />
    
    
    <form id=\"myForm\" action=\"\" method=\"post\">
 	
  	<div style=\"position: absolute; top: 90px; left: 700px; width: 280px;\">
  		<b>Zařadit do kategorie</b><br /><br />
  		".categories_checkbox($cat_array,$par_array,$dct)."<br /><br />
  	</div>
  
    $seznam
  
    <br />
    <br />
    $strankovani
    <br /><br />
    <span style=\"color:red\">[D]</span> Doporučujeme. <br />
    <span style=\"color:red\">[P]</span> Přednost v kategorii.<br />
    <span style=\"color:red\">[N]</span> Novinka.<br /> 
    <span style=\"color:red\">[A]</span> Akční nabídka.<br />
    <br />

    
    $button
    <br />
    $buttonCopy
    
    </form>";
    $data = SEARCH_PANEL.$seznam;//."$query<br /><br />"
	}
  else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	unset($_SESSION['last_id_parent']);	
	
	
	/*
	if(!empty($seznam)) $data = SEARCH_PANEL.$seznam;//."$query<br /><br />"
	else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	unset($_SESSION['last_id_parent']);
*/

}
// *****************************************************************************
// seznam nezarazenych
// *****************************************************************************


// *****************************************************************************
// seznam nezarazenych
// *****************************************************************************
if(isset($_GET['a']) && $_GET['a']=='cena_eshop') {
	
	$nadpis = "Produkty s vlastní cenou pro e-shop  (přepisuje cenu z Isoftu)";
	
	$query="select id FROM ".T_GOODS." a
	WHERE a.cena_eshop > 0 
	ORDER BY a.name";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$rows=mysql_num_rows($v);
	
	$naStrane=40;
	if(empty($_GET['page']))$_GET['page']=1;
	
	$od=($_GET['page']*$naStrane)-$naStrane;
	
  $button='';
	
	$buttonCopy='';
	
	$strankovani=strankovani($rows,$naStrane,'http://'.$_SERVER['HTTP_HOST'].'/admin/index.php?C_lang=1&app=shop&f=products&a=cena_eshop');
	
	
	
	$query="select * FROM ".T_GOODS." a
	WHERE a.cena_eshop > 0 
	ORDER BY a.name
    limit $od,$naStrane";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$poc_x = $od;
	$seznam = null;
	while ($z = mysql_fetch_array($v)) {
	
	$poc_x++;
	
		if($z['hidden'] == 1) $pstyle = "class=\"gray\"";
		else $pstyle = "";
		$dop='';
	  if(isset($z['akce']) AND $z['akce'] == 1) $dop.= "<span title='Doporučujeme' style=\"color:red\">[D]</span>";
		if(isset($z['akced']) AND $z['akced'] == 1) $dop.= "<span title='Akční nabídka' style=\"color:red\">[A]</span>";
		if(isset($z['prednost']) AND $z['prednost'] == 1) $dop.= "<span title='Přednostní zobrazení' style=\"color:red\">[P]</span>";
		if(isset($z['novinka']) AND $z['novinka'] == 1) $dop.= "<span title='Novinka' style=\"color:red\">[N]</span>";
	

    $seznam .= "(".$poc_x.") ".$dop." 
    <a href=\"".MAIN_LINK."&f=products&id=".$z['id']."&a=edit\" 
		title=\"Upravit\" $pstyle>".$z['name']."</a><br />";

/*		
		$seznam .= "
		<a href=\"".MAIN_LINK."&f=products&id=".$z['id']."&a=edit&cat=".$_GET['cat']."\" 
		title=\"Upravit\" $pstyle>".$z['name']."</a><br />";
*/	
	}
	
	if(!empty($seznam)) {
			
    $seznam = "
    
    <br />$strankovani<br /><br />
    
    
    <form id=\"myForm\" action=\"\" method=\"post\">
 	  
    $seznam
  
    <br />
    <br />
    $strankovani
    <br /><br />
    <span style=\"color:red\">[D]</span> Doporučujeme. <br />
    <span style=\"color:red\">[P]</span> Přednost v kategorii.<br />
    <span style=\"color:red\">[N]</span> Novinka.<br /> 
    <span style=\"color:red\">[A]</span> Akční nabídka.<br />
    <br />

    
    $button
    <br />
    $buttonCopy
    
    </form>";
    $data = SEARCH_PANEL.$seznam;//."$query<br /><br />"
	}
  else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	unset($_SESSION['last_id_parent']);	
	
	
	/*
	if(!empty($seznam)) $data = SEARCH_PANEL.$seznam;//."$query<br /><br />"
	else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	unset($_SESSION['last_id_parent']);
*/

}
// *****************************************************************************
// seznam nezarazenych
// *****************************************************************************

// *****************************************************************************
// pridani produktu (form)
// *****************************************************************************
if(isset($_GET['a']) && $_GET['a'] == "add") {

	$nadpis = "Přidat produkt";
	
	
	// vygenerujeme pole s kategoriemi
	if(empty($cat_array)) {
	
		$cat_array = array();
		categories_array($parent_id=0,$cat_array,$level=0);
	
	}
	
	
	if(empty($_SESSION['last_id_parent']))$_SESSION['last_id_parent']='';
	
	// kategorie
	$form_data['id_parent'] = categories_checkbox($cat_array,$_SESSION['last_id_parent'],$dct,1); // categories_select
	
  
  if(empty($_SESSION['last_id_vyrobce']))$_SESSION['last_id_vyrobce']='';
    	
	// roleta vyrobcu
	$form_data['id_vyrobce'] = producers_select($_SESSION['last_id_vyrobce'],$dct);
	
	$data = form($form_data,$dct);
	
	unset($_SESSION['last_id_parent']);
	unset($_SESSION['last_id_vyrobce']);

}
// *****************************************************************************
// pridani produktu (form)
// *****************************************************************************









// *****************************************************************************
// odstranit produkt
// *****************************************************************************
if(!empty($_GET['delete'])) {

if ($_GET["xml"]==1)
{

    // *****************************************************************************
    
    $LANG = C_LANG;
    
    $query = "SELECT id FROM ".T_CATEGORIES." WHERE name LIKE '_ODSTRANENE_IMPORTOVANE_POLOZKY' AND lang=".$LANG." ";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);
    $z = mysql_fetch_assoc($v);
    if ($z["id"]>0)
    {
      $id_kateg_odstranene = $z["id"]; 
    }
    else
    {
    
    $query = 
    "
    INSERT INTO `fla_shop_kategorie` (`id` , `name` ,                          `hidden` ,`descr` ,`lang` ,`products` ,`id_parent` ,`position` ,`export`)
                                                VALUES (NULL , '_ODSTRANENE_IMPORTOVANE_POLOZKY', '1',      '',       $LANG, '0', '0', '-2', '0')
    ";
    my_DB_QUERY($query,__LINE__,__FILE__);
    $id_kateg_odstranene = mysql_insert_id();
    
    }
    
    // *****************************************************************************
    

    $query = "DELETE FROM ".T_GOODS_X_CATEGORIES." WHERE id_good=".$_GET['delete']." ";
    my_DB_QUERY($query,__LINE__,__FILE__);

    $query = "INSERT INTO  ".T_GOODS_X_CATEGORIES." VALUES(".$_GET['delete'].", ".$id_kateg_odstranene.",".$LANG.")";
    my_DB_QUERY($query,__LINE__,__FILE__);

    $_SESSION['alert_js'] = "Záznam byl přesunut do odstraněných";

}
else
{

	delete_img_good($_GET['delete']); // odstraneni obrazku
	
	// detail produktu prislo
	// odstraneni zaznamu o prirazenych souborech
	$query = "DELETE FROM ".T_GOODS_X_DOWNLOAD." 
	WHERE id_good = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_X_DOWNLOAD);
	
	
	$query = "DELETE FROM ".T_GOODS." 
	WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS);
	
	
	$query = "DELETE FROM ".T_GOODS_X_CATEGORIES." 
	WHERE id_good = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_X_CATEGORIES);
	
	//vložení SEO
  delete_seo($_GET['delete'],3);//kcemu ... 1-clanek,2-kategorie,3-produkt
  
	$_SESSION['alert_js'] = "Záznam odstraněn";

}
	
	Header("Location: ".MAIN_LINK."&f=products&cat=".$_GET['cat']."");
	exit;

}
// *****************************************************************************
// odstranit produkt
// *****************************************************************************








// *****************************************************************************
// odstranit obrazek produktu
// *****************************************************************************
if(!empty($_GET['delimg'])) {

	delete_img_good($_GET['delimg']);
	
	$query = "UPDATE ".T_GOODS." SET img = '' 
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
// zobrazit komentare
// *****************************************************************************

if(isset($_GET['a']) && $_GET['a']=="showcomments"){

	$query = "SELECT * FROM ".T_GOODS." 
	WHERE id = ".$_GET['id']." AND ".SQL_C_LANG." LIMIT 0,1";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$vyrobek=mysql_fetch_array($v);
	
	$nadpis='Komentáře k výrobku '.$vyrobek['name'];
	
	$prispevky='';
	     
 	$query = "SELECT *,DATE_FORMAT(datetime,'%d.%m.%Y %H:%i') as dateform from ".T_COMMENTS." where id_produkt=".$_GET['id']." order by datetime";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while($row=mysql_fetch_array($v)){
	
		if(!empty($row['jmeno']))$row['jmeno']=$row['jmeno'].',';

			// styl pro skryte polozky
			$hidden_style = "style=\"color: #939393; padding: 2px 8px;\"";
			// prednastavena hodnota pro nastaveni zobrazovani primo 
			// ze seznamu kategorii
			$set_hidden = 0;
			
			// zacatek skrytych kategorii (prvni v hierarchii)
			if ($row['hidden'] == 1) {
				$h = 1;
				$alt_h = $dct['cat_zobrazeni_nepovoleno']." - ".$dct['cat_povolit_zobrazeni'];
			}// neskryte kategorie
			else {
				$h = 0;
				$alt_h = $dct['cat_zobrazeni_povoleno']." - ".$dct['cat_zakazat_zobrazeni'];
				$hidden_style = "style=\"padding: 2px 8px;\"";
				$set_hidden = 1;
			}

			$h_img = "<img src=\"./icons/hidden_$h.gif\" alt=\"$alt_h\"	title=\"$alt_h\" border=\"0\" height=\"10\" width=\"13\" align=\"absmiddle\">";		
               $h_img = "<a href=\"".MAIN_LINK."&f=products&a=hiddencomments&id=".$row['id']."&hidden=$set_hidden\">$h_img</a>";
	
		$prispevky.='<tr style="background: #FFFFFF;"><td '.$hidden_style.'>'.$h_img.' '.$row['dateform'].' '.$row['jmeno'].' '.$row['email'].'</td><td '.$hidden_style.' width="40">
					'.ico_delete(MAIN_LINK."&f=products&deletecomments=".$row['id']."&id_produkt=".$_GET['id'],"Smazat komentář","onclick='return del2()'").'
					<a href="index.php?C_lang=1&amp;app=shop&amp;f=products&amp;a=editcomments&amp;id='.$row['id'].'" title="Detail komentáře" class="f10">
					<img src="./icons/ico_edit.gif" alt="Detail komentáře" title="Detail komentáře" border="0" height="15" width="15"></a>
		</td></tr>';	
	} 
	
	
	if(!empty($prispevky)){
		$data="<SCRIPT LANGUAGE=\"JavaScript\">
				<!--
				function del2() {
				
					if (!confirm(\"".$dct['opravdu_odstranit']."\")) {
						return false;
					}
				
				}
				// -->
		  </SCRIPT>".'
		  <table class="admintable" border="0" cellspacing="1" cellpadding="0">'.$prispevky.'</table>';
	}else{
	     $data="Žádné komentáře k tomuto výrobku nejsou k dispozici.";
	}
		
}

// *****************************************************************************
// zobrazit komentare
// *****************************************************************************





// *****************************************************************************
// skryt komentar
// *****************************************************************************
if(isset($_GET['a']) && $_GET['a']=="hiddencomments" && !empty($_GET['id']) && isset($_GET['hidden'])){
	

	$query="update ".T_COMMENTS." set hidden=".$_GET['hidden']." where id=".$_GET['id'];	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$_SESSION['alert_js'] = "Záznam upraven!";	
	
	Header('Location: '.$_SERVER['HTTP_REFERER']);
	exit;
	
}
// *****************************************************************************
// skryt komentar
// *****************************************************************************






// *****************************************************************************
// editovat komentar
// *****************************************************************************
if(isset($_GET['a']) && $_GET['a']=="editcomments" && !empty($_GET['id'])){

	$nadpis='Editace komentáře k výrobku';
	
	if(!empty($_POST)){
	
		$jmeno=$_POST['jmeno'];
		$email=$_POST['email'];
		$dotaz=$_POST['dotaz'];
		$hidden=$_POST['hidden'];
		
		if(empty($hidden))$hidden=0;
		
		$query="update ".T_COMMENTS." set jmeno='$jmeno', email='$email', dotaz='$dotaz', hidden=$hidden where id=".$_GET['id'];
			          
// 		echo $query;
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
		$_SESSION['alert_js'] = "Záznam uložen!";
		
		Header('Location: '.$_SERVER['HTTP_REFERER']);
		exit;
		
	}else{
	
		$query="select ".T_COMMENTS.".*, ".T_GOODS.".name, ".T_GOODS.".id as id_produkt, DATE_FORMAT(datetime,'%d.%m.%Y %H:%i') as dateform 
			   from ".T_COMMENTS.",".T_GOODS." where ".T_GOODS.".id=".T_COMMENTS.".id_produkt AND ".T_COMMENTS.".id=".$_GET['id'];
			          
// 		echo $query;
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$row=mysql_fetch_array($v);
		
		if($row['hidden']==1)$checked='checked="checked"';
		else $checked='';
	
		$data='
		<script language="javascript">
		<!--		
			function del() {
	
				if (confirm("Opravdu odstranit?"))
					{ location = "'.MAIN_LINK."&f=products&deletecomments=".$row['id']."&id_produkt=".$row['id_produkt'].'"; }
			
			}
		</script>
		
			<form action="" method="post">
				<table class="admintable nobg" width="650">
					<tr>
						<td style="padding: 4px 0;">Odeslán ze stránky</td>
						<td width="460"><a href="'.$row['zestranky'].'">'.substr($row['zestranky'],0,50).'...</a></td>
					</tr>	
					<tr>
						<td style="padding: 4px 0;">Příchod na web</td>
						<td>'.$row['referer'].'</td>
					</tr>
					<tr>
						<td style="padding: 4px 0;">Čas odeslání</td>
						<td>'.$row['dateform'].'</td>
					</tr>
					<tr>
						<td style="padding: 4px 0;">K produktu<br /><br /><br /><br /></td>
						<td>'.$row['name'].'<br /><br /><br /><br /></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="checkbox" name="hidden" value="1" '.$checked.' /> Nezobrazovat</td>
					</tr>
					<tr>
						<td>Jméno</td>
						<td><input style="width: 100%;" type="text" name="jmeno" value="'.$row['jmeno'].'" /></td>
					</tr>
					<tr>
						<td>E-mail</td>
						<td><input style="width: 100%;" type="text" name="email" value="'.$row['email'].'" /></td>
					</tr>
					<tr>
						<td valign="top">Dotaz</td>
						<td><textarea style="width: 100%; height: 100px;" name="dotaz">'.$row['dotaz'].'</textarea></td>
					</tr>
					<tr>
						<td></td>
						<td>'.SAVE_BUTTON.' <input type="button" value="Odstranit záznam" class="butt_red" onclick="return del();"></td>
					</tr>	
				</table>
			</form>
		';
	}
	
}
// *****************************************************************************
// editovat komentar
// *****************************************************************************





// *****************************************************************************
// oddelat komentar
// *****************************************************************************
if(!empty($_GET['deletecomments'])){

	$query="delete from ".T_COMMENTS." where id=".$_GET['deletecomments'];	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$_SESSION['alert_js'] = "Záznam odstraněn!";	
	
	Header('Location: '.MAIN_LINK."&f=products&id=".$_GET['id_produkt']."&a=showcomments");
	exit;
}
// *****************************************************************************
// oddelat komentar
// *****************************************************************************
?>
