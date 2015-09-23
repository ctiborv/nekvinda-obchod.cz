<?php


include ('seo.php'); //vložení SEO
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/shop/slider_functions.php');


// *****************************************************************************
// formular pro editaci
// *****************************************************************************
function form($form_data,$dct) {
	
	$js='';
	if(empty($form_data['text']))$form_data['text']='';
	if(empty($form_data['im_0']))$form_data['im_0']='';
	if(empty($form_data['im_1']))$form_data['im_1']='';
	if(empty($form_data['im_2']))$form_data['im_2']='';
	if(empty($form_data['im_3']))$form_data['im_3']='';
	if(empty($form_data['id']))$form_data['id']='';
	if(empty($form_data['position']))$form_data['position']='';
	if(empty($form_data['title']))$form_data['title']='';
	if(empty($form_data['hp']))$form_data['hp']='';
	if(empty($form_data['select_foto']))$form_data['select_foto']='';
	if(empty($form_data['link']))$form_data['link']='';
	if(empty($form_data['menu_pos']))$form_data['menu_pos']='';
	if(empty($form_data['hidden']))$form_data['hidden']='';
	if(empty($form_data['deletebutton']))$form_data['deletebutton']='';
	if(empty($ei_text))$ei_text='';


	
	$editor="
      <textarea name='text'>".$form_data['text']."</textarea>
          
      <script type='text/javascript'>
                //<![CDATA[
                CKEDITOR.replace('text', {
                	height: '600px'
                });
                //]]>
      </script>
  ";
	
	
	// vygenerujeme seznam s odkazy na stranky pro moznost zkopirovani a vlozeni 
	// do textu ve strance
	// id title content in_menu menu_pos homepage hidden
	$query = "SELECT id,title FROM ".T_CONT_PAGES." 
	WHERE hidden = 0 AND ".SQL_C_LANG." 
	ORDER BY in_menu DESC, menu_pos ASC, title ASC";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$links='';
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$title = $z['title'];

		$title = diakritika_utf($title);
		$title = text_in_url($title);
		$title = str_replace(" ", "-", $title);		
		
		$x = str_replace("admin/", "", $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
		$x = "index.php";
		/*$links .= "
		stránka: <strong>$title</strong><br />
		adresa: $x?n=".text_in_url(strtoL($title))."&amp;go=page&amp;idp=$id<br /><br />";*/
    $links .= "
		stránka: <strong>$title</strong><br />
		adresa: /clanek/$id-".$title.".html<br /><br />";
		
		
	
	}
	
	// skryvany obsah
	$sc = 1; // pocitadlo pro skryvany obsah
	
	
	if(!empty($links)) {
	
		$links = "
		
		<table border=\"0\" cellspacing=\"2\" cellpadding=\"0\">
		
		<tr>
			<td width=\"15\" valign=\"middle\">
				<img src=\"icons/ico_arr_down.gif\" alt=\"$ei_text\" title=\"$ei_text\" 
				border=\"0\" height=\"15\" width=\"15\" class=\"expandcontent\" 
				onclick=\"expandcontent('sc$sc')\">
			</td>
			
			<td valign=\"middle\" align=\"left\">
				<span class=\"expandcontent\" onclick=\"expandcontent('sc$sc')\">seznam odkazů na stránky pro použití v textu</span>
			</td>
		</tr>
			
		
		<tr>
			<td width=\"15\" valign=\"middle\">
				&nbsp;
			</td>
			<td nowrap>
				<div class=\"switchcontent\" id=\"sc$sc\">
					<span class=\"f10\">(zkopírujte adresu a vložte jako URL pomocí editoru)</span>
					<br /><br />
					$links
				</div>
			</td>
		</tr>
		
		</table>";
			
	}
	
	
	// skryvany obsah
	
	//vložení SEO
$SEO=form_seo($form_data,$sc);
	
	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.title.value == \"\") { alert(\"Vyplňte název stránky\"); form1.title.focus(); return false; }
		else if (form1.title.value.length > 255) {
			alert(\"Název stránky je dlouhý (\" + form1.title.value.length + \") - upravte jej na max. 255 znaky.\"); form1.title.focus(); return false;
		}
		else return true;
	
	}
	
	
	// odstraneni zaznamu
	function del() {
	
		if (confirm(\"Opravdu odstranit?\"))
			{ location = \"".$form_data['link']."&delete=".$form_data['id']."\"; }
	
	}
	// -->
	</SCRIPT>
	
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" enctype=\"multipart/form-data\" 
		onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	
	<table width=\"650\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	<tr>
		<td width=\"160\">
			Název <span class=\"f10i\">(max. 255 znaků)</span></td>
		<td width=\"340\">
			<input type=\"text\" name=\"title\" value=\"".$form_data['title']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	
	<tr>
		<td>&nbsp;</td>
		<td width=\"330\">
			<input type=\"checkbox\" name=\"homepage\" value=\"1\" id=\"hp\" ".$form_data['hp'].">
			<label for=\"hp\">úvodní stránka</label></td>
	</tr>
	
	
	<tr>
		<td>Způsob zobrazení</td>
		<td width=\"330\">
			<select name=\"in_menu\" class=\"f10\" style=\"width: 100%;\">
			<option value=\"0\" ".$form_data['im_0'].">Nikde</option>
			<option value=\"1\" ".$form_data['im_1'].">Horizontální menu</option>
			<!--<option value=\"2\" ".$form_data['im_2'].">BOX 2 (druhá vodorovná část)</option>   -->
			<option value=\"3\" ".$form_data['im_3'].">BOX DOWNLOAD </option>
		</select></td>
	</tr>
	
	
	<tr>
		<td>Pořadí v	menu</td>
		<td width=\"330\">
			<input type=\"text\" name=\"menu_pos\" value=\"".$form_data['menu_pos']."\" class=\"f10\" size=\"3\"></td>
	</tr>
	
	
	<tr>
		<td>Nezobrazovat stránku</td>
		<td>
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" ".$form_data['hidden']."> 
			<span class=\"f10i\">Stránka nebude zobrazena</span></td>
	</tr>
	
	
	<tr>
		<td colspan=\"2\">
			<br /><br />Obsah<br />
			$editor
		</td>
	</tr>
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>	
	
	<tr>
		<td class=\"tdleft\" valign=\"top\"><a href=\"\" class=\"click\" onclick=\"s('divfotocat'); return false;\">Připojit fotogalerie</a> &raquo;</td>
		<td class=\"tdright\" ><div id=\"divfotocat\">".$form_data['select_foto']."</div></td>
	</tr>

	<tr>
		<td class=\"tdleft\" valign=\"top\"><a href=\"\" class=\"click\" onclick=\"s('divslider'); return false;\">Slidery</a> &raquo;</td>
		<td class=\"tdright\" >
      <div style=\"display:none;\" id=\"divslider\">
        ".slider_shortcode()."
      </div>
    </td>
	</tr>
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	
	<tr>
		<td colspan=\"2\"><br /><br /><br />
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	
	
	<br /><br />
	
	
	
	$links
	
	
	
	$SEO
	
	</form>
  
  <script type=\"text/javascript\">
	<!--
   document.getElementById('divfotocat').style.display='none';
  ".$js."
	-->
	</script>";
	
	return $form;//

}
// *****************************************************************************
// formular pro editaci
// *****************************************************************************






// souvisla rada
function souvisla_rada($start_pos,$in_menu) {

	$query = "SELECT in_menu FROM ".T_CONT_PAGES." WHERE ".SQL_C_LANG." GROUP BY in_menu";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	if(mysql_num_rows($v) > 0) {
	while ($z = mysql_fetch_array($v)) {
	
		if($z['in_menu']  == 1 || $z['in_menu'] == 2 OR $z['in_menu'] == 3) $arr[] = $z['in_menu'];
	
	}
	
	
	
	
	if(!empty($arr)) {
	while ($pa = each($arr)) {
	
		$i = $start_pos;
		
		$query = "SELECT id FROM ".T_CONT_PAGES." WHERE ".SQL_C_LANG." AND in_menu = ".$pa['value']." ORDER BY menu_pos";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		while ($z = mysql_fetch_array($v)) {
		
			$query = "UPDATE ".T_CONT_PAGES." SET menu_pos = $i WHERE id = " . $z['id'];
			my_DB_QUERY($query,__LINE__,__FILE__);
			
			$i++;
		
		}
	
	}
	}
	
	
	
	
	$query = "UPDATE ".T_CONT_PAGES." SET menu_pos = 0 WHERE ".SQL_C_LANG." AND in_menu = 0";
	my_DB_QUERY($query,__LINE__,__FILE__);
	}

}




// posun pozic nahoru
function move_up($menu_pos,$in_menu) {

	$query = "UPDATE ".T_CONT_PAGES." SET 
	menu_pos = menu_pos + 1 WHERE ".SQL_C_LANG." AND in_menu = $in_menu 
	AND (menu_pos = $menu_pos or menu_pos > $menu_pos)";
	my_DB_QUERY($query,__LINE__,__FILE__);

}


// posun pozic dolu
function move_down($menu_pos,$in_menu) {

	$query = "UPDATE ".T_CONT_PAGES." SET 
	menu_pos = menu_pos - 1 WHERE ".SQL_C_LANG." AND in_menu = $in_menu 
	AND (menu_pos = $menu_pos or menu_pos < $menu_pos)";
	my_DB_QUERY($query,__LINE__,__FILE__);

}







// *****************************************************************************
// odstraneni zaznamu
// *****************************************************************************
if (!empty($_GET['delete'])) {

	$query = "SELECT in_menu FROM ".T_CONT_PAGES." WHERE id = " . $_GET['delete']." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$in_menu = mysql_result($v, 0, 0);
	
	
	// id  title  content  in_menu  menu_pos 
	$query = "DELETE FROM ".T_CONT_PAGES." WHERE id = " . $_GET['delete'];
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_CONT_PAGES);
	
	// zrusime připojené fotogalerie
 	$query = "DELETE FROM ".T_FOTO_CONT_PAGES." WHERE id_page = ".$_GET['delete']." ";
	my_DB_QUERY($query,__LINE__,__FILE__);

 	my_OPTIMIZE_TABLE(T_FOTO_CONT_PAGES);
	
	
	souvisla_rada(1,$in_menu);
  //vložení SEO
  delete_seo($_GET['delete'],1);//kcemu ... 1-clanek,2-kategorie,3-produkt
	
	Header("Location: ".MAIN_LINK."&f=pages&a=list");
	exit;

}
// *****************************************************************************
// odstraneni zaznamu
// *****************************************************************************








// *****************************************************************************
// editace / pridani zaznamu
// *****************************************************************************
if (!empty($_POST)) {

	$title = trim($_POST['title']);
	$text = trim($_POST['text']);
	$text=addslashes($text);
	
	
	// osetrime hodnoty poradi
	if (empty($_POST['menu_pos']) || $_POST['menu_pos'] < 1) $menu_pos = '100000';
	else $menu_pos = $_POST['menu_pos'];
	
	
	// ma se zobrazit jako odkaz v menu?
	if (empty($_POST['in_menu'])) $in_menu = $menu_pos = '0';
	else $in_menu = $_POST['in_menu'];
	
	if($in_menu == '0') $menu_pos = '0';
	
	
	// skryta stranka
	if (empty($_POST['hidden'])) $hidden = '0'; // = $in_menu = $menu_pos
	else $hidden = $_POST['hidden'];
	
	if($hidden == '1') $menu_pos = '0';
	
	
	
	
	// stranka je nastavena jako homepage
	if (empty($_POST['homepage'])) $homepage = '0';
	else {
	
		$homepage = '1';
		$menu_pos = '1'; // homepage bude vzdy 1. v poradi
		$query = "UPDATE ".T_CONT_PAGES." SET homepage = 0 WHERE ".SQL_C_LANG." AND homepage = 1";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	
	
	
	// id  title  content  in_menu  menu_pos  homepage  hidden
	if (!empty($_POST['id'])) { // editace existujiciho
	  $id=$_POST['id'];
		$query = "SELECT menu_pos FROM ".T_CONT_PAGES." 
		WHERE id = ".$_POST['id']."";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$pos = mysql_result($v, 0, 0);
		
		
		// kdyz puvodne nebyla stranka soucasti menu
		if ($pos == '0' && $menu_pos > 0) $pos = $menu_pos + 1;
		
		
		if ($in_menu != '0') {
		
			if ($menu_pos == $pos) move_up($menu_pos,$in_menu);
			if ($menu_pos > $pos) move_down($menu_pos,$in_menu);
			if ($menu_pos < $pos) move_up($menu_pos,$in_menu);
		
		}
		
		
		$query = "UPDATE ".T_CONT_PAGES." SET 
		title = '$title', 
		content = '$text', 
		in_menu = $in_menu, 
		menu_pos = $menu_pos, 
		homepage = $homepage, 
		hidden = $hidden 
		WHERE id = ".$_POST['id'];
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	else { // novy zaznam
	
	
	  //echo $menu_pos;
	  //echo '<br />';
	  //echo $in_menu;
	  //exit;
		//move_up($menu_pos,$in_menu);  
		
		// id  title  content  in_menu  menu_pos  homepage
		$query = "INSERT INTO ".T_CONT_PAGES." 
		VALUES('', '$title','$text',$in_menu,$menu_pos,$homepage,$hidden,".C_LANG.")";
		my_DB_QUERY($query,__LINE__,__FILE__);
    
    $query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$id = mysql_result($v, 0, 0);
		
    //vložení SEO
		$_POST['novy_zaznam']=$id;

	}
	
	     // ***************************************************************************	
	// fotogalerie
	// zrusime puvodni fotogalerie
	
	
  

	if (!empty($_POST['id'])) {
	
  	$query = "DELETE FROM ".T_FOTO_CONT_PAGES." WHERE id_page = ".$id." ";
  	my_DB_QUERY($query,__LINE__,__FILE__);
  	
  	my_OPTIMIZE_TABLE(T_FOTO_CONT_PAGES);
  
  }

	// jsou-li prirazeny nejake fotogaleri, ulozime je do DB
	if(!empty($_POST['fotogalerie'])) {
	
		if (!in_array("NO", $_POST['fotogalerie'])) {
  	
  		reset($_POST['fotogalerie']);
  		while ($pV = each($_POST['fotogalerie'])) {
  		
  			if(!empty($pV['value'])) {
  			
  				$query = "INSERT INTO ".T_FOTO_CONT_PAGES." VALUES(0,$id,".$pV['value'].")";
  				my_DB_QUERY($query,__LINE__,__FILE__);
  			
  			}
  		
  		}
  		
  	}
	
	}	
	
	souvisla_rada(1,$in_menu);
	//vložení SEO
  uloz_seo($_POST,1);//kcemu ... 1-clanek,2-kategorie,3-produkt  	
	
	
	$_SESSION['alert_js'] = "Záznam uložen";
	
	$back = $_SERVER['HTTP_REFERER'];
	Header("Location: ".$back);
	exit;

}
// *****************************************************************************
// editace / pridani zaznamu
// *****************************************************************************







// *****************************************************************************
// fce pro generovani tabulek se seznamem stranek
// *****************************************************************************
function table($query) {

	global $homepage_is_set;
  // id  title  content  in_menu  menu_pos  homepage
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$hidden_style=$tbl=$menu_pos=$title=$in_menu_new='';
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$title = $z['title'];
		$homepage = $z['homepage'];
		
		$link = "index.php?p=$id";
		
		$in_menu = $z['in_menu'];
		$menu_pos = $z['menu_pos'];
		$hidden = $z['hidden'];
  	
  	
		/*
		if ($in_menu_new != $in_menu_old)
			$tbl .= "
				<tr>
		    	<td colspan=\"3\" style=\"background: #ffffff;\">&nbsp;</td>
		    </tr>";
		*/
		
		
		if ($in_menu == 0) $menu_pos = "nezobrazuje se";
		
		if ($hidden == 1) $menu_pos = "skrytá";
		
		
		
		if ($homepage == 1) {
		
				$title = "$title <strong>(úvodní stránka)</strong>";
		    $homepage_is_set = "ok";
		
		}
		
		
		
		
		
		$tbl .= "
		<tr ".TABLE_ROW.">
    	<td class=\"td1\" $hidden_style>$title</td>
    	<td width=\"110\" class=\"td1\">$menu_pos</td>
    	<td width=\"15\" class=\"td1\">
				".ico_edit(MAIN_LINK."&f=pages&a=edit&id=$id",'Upravit stránku')."</td>
    </tr>";
		//".ico_edit("prew&id=$id",$dct['cont_page_edit'])."
		
		
		$in_menu_old = $in_menu_new;
	
	}
	
	
	return $tbl;

}
// *****************************************************************************
// fce pro generovani tabulek se seznamem stranek
// *****************************************************************************







// *****************************************************************************
// seznam stranek
// *****************************************************************************
if ($_GET['a'] == "list") {

	$nadpis = $dct['mn_cont_seznam'];
	
	$tbl='';
	
	/*// homepage
	$tbl1 = table ("SELECT id,title,in_menu,menu_pos,homepage,hidden FROM ".T_CONT_PAGES." 
	WHERE homepage = 1");
	
	if(!empty($tbl1)) {
	
			$tbl = "
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl1
			
			</table>";
	
	}*/
	
	/*if ($homepage_is_set != "ok") 
		$errorText = "<div class='error'><br />NENÍ NASTAVEN OBSAH ÚVODNÍ STRÁNKY !!! 
		Vyberte stránku a nastavte ji jako úvodní.<br /><br /></div><br />";*/
	
	
	// stranky uvedene v menu 2 - lista v prave casti stranky
	$tbl2_1 = table("SELECT id,title,in_menu,menu_pos,homepage,hidden FROM ".T_CONT_PAGES." 
	WHERE ".SQL_C_LANG." AND hidden = 0 AND in_menu = 2 ORDER BY menu_pos, title");
	
	if(!empty($tbl2_1)) {
	
			$tbl .= "<br />
			<br />
			BOX 1 (první vodorovná část)<br />
			<br />
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl2_1
			
			</table>";
	
	}
	
	// stranky uvedene v menu 1 - leva strana
	$tbl2_2 = table("SELECT id,title,in_menu,menu_pos,homepage,hidden FROM ".T_CONT_PAGES." 
	WHERE ".SQL_C_LANG." AND hidden = 0 AND in_menu = 1 ORDER BY menu_pos, title");
	
	if(!empty($tbl2_2)) {
	
			$tbl .= "
			<br /><br />
			Horizontální menu<br />
			<br />
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl2_2
			
			</table>";
	
	}
	
	
	// ostatni stranky neuvedene v menu
	$tbl2a = table("SELECT id,title,in_menu,menu_pos,homepage,hidden FROM ".T_CONT_PAGES." 
	WHERE ".SQL_C_LANG." AND hidden = 0 AND in_menu = 3 ORDER BY menu_pos, title");
	
	if(!empty($tbl2a)) {
	
			$tbl .= "
			<br /><br />
			BOX DOWNLOAD<br />
			<br />
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl2a
			
			</table>";
	
	}
	
	
	
	// ostatni stranky neuvedene v menu
	$tbl3 = table("SELECT id,title,in_menu,menu_pos,homepage,hidden FROM ".T_CONT_PAGES." 
	WHERE ".SQL_C_LANG." AND hidden = 0 AND in_menu = 0 
	ORDER BY title");
	
	if(!empty($tbl3)) {
	
			$tbl .= "
			<br /><br />
			NEZAŘAZENÉ<br />
			<br />
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl3
			
			</table>";
	
	}
	
	
	// ostatni stranky neuvedene v menu
	$tbl4 = table("SELECT id,title,in_menu,menu_pos,homepage,hidden FROM ".T_CONT_PAGES." 
	WHERE ".SQL_C_LANG." AND hidden = 1 
	ORDER BY title");
	
	if(!empty($tbl4)) {
	
			$tbl .= "
			<br /><br />
			SKRYTÉ<br />
			<br />
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl4
			
			</table>";
	
	}
	
	
	
	
	
	if (empty($tbl)) $data = "
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
			
			<tr>
				<td colspan=\"3\">žádný záznam</td>
			</tr>
			
			</table>";
	else $data = "
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
			
			<tr>
				<td class=\"td1\"><b>název stránky</b></td>
				<td width=\"110\" class=\"td1\"><b>pořadí</b></td>
				<td width=\"15\" class=\"td2\">&nbsp;</td>
			</tr>
			
			</table>
			
			
			$tbl";

}
// *****************************************************************************
// seznam stranek
// *****************************************************************************





// *****************************************************************************
// editace (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = $dct['cont_page_edit'];
	$form_data['link'] = MAIN_LINK."&f=pages";
	$form_data['deletebutton'] = DELETE_BUTTON;
	
	// id  title  content  in_menu  menu_pos 
	$query = "SELECT * FROM ".T_CONT_PAGES." 
	WHERE id = ".$_GET['id']." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {

		$form_data['id'] = $z['id'];
		$form_data['title'] = $z['title'];
		$form_data['text'] = stripslashes($z['content']);
		
		
		
		if ($z['hidden'] == 1) $form_data['hidden'] = "checked";
		else $form_data['hidden'] = "";
		
		
		
		if ($z['in_menu'] > 0) {
		
			$form_data['im_'.$z['in_menu']] = "selected";
			$form_data['menu_pos'] = $z['menu_pos'];
		
		}
		else {
		
			$form_data['im'] = "";
			$form_data['menu_pos'] = "";
		
		}
		
		if ($z['homepage'] == 1) {
		
			$form_data['hp'] = "checked";
		
		}
    //vložení SEO
    $SEO_data=nacti_seo($z['id'],1);//kcemu ... 1-clanek,2-kategorie,3-produkt
    $form_data['seo_title']=$SEO_data['seo_title'];
    $form_data['seo_keywords']=$SEO_data['seo_keywords'];
    $form_data['seo_description']=$SEO_data['seo_description'];
    $form_data['seo_foot']=$SEO_data['seo_foot'];
	}
	
	if ($_GET['id'] > 0) {
 	  /*echo $_GET['id'];
 	  exit;*/
   	$query = "SELECT id_kateg FROM ".T_FOTO_CONT_PAGES." 
   	WHERE id_page = ".$_GET['id']."";
   	$v = my_DB_QUERY($query,__LINE__,__FILE__);
   	while ($z = mysql_fetch_array($v)) {
   	
   		$var_selected_foto[$z['id_kateg']] = "selected";
   	
   	}
   	}
 	
	
	// seznam fotogalerií, ktere lze priradit k produktu
	// id skupina varianta lang pozn
	$query = "
  SELECT fk.id, fk.name 
  FROM ".T_FOTO_KATEG." fk
	WHERE fk.".SQL_C_LANG." ORDER BY id";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$select_foto_item = "<option value=\"NO\">žádná</option>";
  while ($z = mysql_fetch_assoc($v))
  {
    $xid = $z['id'];
  
    if(isset($var_selected_foto[$xid])) $select_foto_item .= "<option value=\"$xid\" ".$var_selected_foto[$xid].">".$z['name']."</option>";
    else $select_foto_item .= "<option value=\"$xid\">".$z['name']."</option>";
	}

	if (!empty($select_foto_item)) {
	
		$form_data['select_foto'] = "
		<select name=\"fotogalerie[]\" class=\"f10 adminselect\" size=\"6\" multiple=\"multiple\">
  		$select_foto_item
		</select>
		
		<span class=\"f10i\"><br />
			více variant lze označit přidržením klávesy Shift nebo Ctrl a zároveň 
			kliknutím myši na název souboru.
		</span>
    ";
	
	} else $form_data['select_foto'] = "Žádné fotogalerie nebyly v databázi nalezeny.";
	// seznam fotogalerii ktere lze priradit k produktu
	// **********************************************************
	
	
  
	
	$data = form($form_data,$dct);

}
// *****************************************************************************
// editace (form)
// *****************************************************************************










// *****************************************************************************
// pridani (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = $dct['cont_page_add'];
	
	
		// seznam fotogalerií, ktere lze priradit k produktu
	// id skupina varianta lang pozn
	$query = "
  SELECT fk.id, fk.name 
  FROM ".T_FOTO_KATEG." fk
	WHERE fk.".SQL_C_LANG." ORDER BY id";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$select_foto_item = "<option value=\"NO\">žádná</option>";
  while ($z = mysql_fetch_array($v)) {

  $xid = $z['id'];
  
  $select_foto_item .= "<option value=\"$xid\" ".$var_selected_foto[$xid].">".$z['name']."</option>";

	}

	if (!empty($select_foto_item)) {
	
		$form_data['select_foto'] = "
		<select name=\"fotogalerie[]\" class=\"f10 adminselect\" size=\"6\" multiple=\"multiple\">
		$select_foto_item
		</select>
		
		<span class=\"f10i\"><br />
			více variant lze označit přidržením klávesy Shift nebo Ctrl a zároveň 
			kliknutím myši na název souboru.
		</span>
    ";
	
	}
	else $form_data['select_foto'] = "Žádné fotogalerie nebyly v databázi nalezeny.";
	// seznam fotogalerii ktere lze priradit k produktu
	// **********************************************************
	
	$data = form($form_data,$dct);
	
	unset($_SESSION['last_parent']);

}
// *****************************************************************************
// pridani (form)
// *****************************************************************************




// if (empty($_GET['edit']) && !isset($_GET['new'])) {
// 
// 	$data = $tbl;
// 
// }
// else {
// 
// 	if (empty($_GET['edit'])) $editorID = time();
// 	else $editorID = $_GET['edit'];
// 
// }
?>
