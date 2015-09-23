<?php


function form($form_data,$dct) {

	if(!empty($form_data['id'])) {
	
		// T_DEALERS_X_REGIONS - id_dealer id_region lang hidden pozn 
		$query = "SELECT id_region, hidden, pozn FROM ".T_DEALERS_X_REGIONS." 
		WHERE id_dealer = ".$form_data['id']." AND ".SQL_C_LANG." ";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
		
			$regions[$z['id_region']] = "selected";
			
			if($z['hidden'] == 1) $hidden[$z['id_region']] = "checked";
			else $hidden[$z['id_region']] = "";
			
			$pozn[$z['id_region']] = $z['pozn'];
		
		}
	
	}
	

	$sc = 1; // pocitadlo pro skryvany obsah
	
	// id name hidden lang
	$query = "SELECT id,name FROM ".T_REGIONS." ORDER BY name";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$name = $z['name'];
		
		// vicenasobny seznam
		/*if(!empty($regions[$id])) $selected = $regions[$id];
		else $selected = "";
		$form_data['region'] .= "<option value=\"$id\" $selected>$name</option>";*/
		
		// checkboxy
		if(!empty($regions[$id])) $checked = "checked";
		else $checked = "";
		// $form_data['region'] .= "<input type=\"checkbox\" name=\"region[]\" value=\"$id\" $checked/> $name<br />";
		
		
		
		// skryvany obsah
		
		$form_data['region_data'] .= "
		
	<tr>
		<td valign=\"top\">
			<input type=\"checkbox\" name=\"region[]\" value=\"$id\" $checked/> $name<br />
		</td>
		<td>
			<input type=\"checkbox\" name=\"hidden[$id]\" value=\"1\" 
			".$hidden[$id]."> ".$dct['deals_skryt']."
			
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			<img src=\"icons/ico_arr_down.gif\" alt=\"$ei_text\" title=\"$ei_text\" 
			border=\"0\" height=\"15\" width=\"15\" class=\"expandcontent\" 
			onclick=\"expandcontent('sc$sc')\">
			
			<span class=\"f10\" onclick=\"expandcontent('sc$sc')\">".$dct['deals_pozn']."</span></td>
	</tr>
		
	<tr class=\"switchcontent\" id=\"sc$sc\">
		<td valign=\"top\" colspan=\"2\">
				<textarea name=\"pozn[$id]\" style=\"width: 100%; height: 40px;\">"
				.$pozn[$id]."</textarea>
			<br /></td>
	</tr>";
		
		
		
		$sc++;
	
	
// 	if(!empty($links)) {
// 	
// 		$links = "
// 		
// 		<table border=\"0\" cellspacing=\"2\" cellpadding=\"0\">
// 		
// 		<tr>
// 			<td width=\"15\" valign=\"middle\">
// 			</td>
// 			
// 			<td valign=\"middle\" align=\"left\">
// 				<span class=\"expandcontent\" onclick=\"expandcontent('sc$sc')\">seznam odkazů na stránky pro použití v textu</span>
// 			</td>
// 		</tr>
// 			
// 		
// 		<tr>
// 			<td width=\"15\" valign=\"middle\">
// 				&nbsp;
// 			</td>
// 			<td nowrap>
// 				<div class=\"switchcontent\" id=\"sc$sc\">
// 					<span class=\"f10\">(zkopírujte adresu a vložte jako URL pomocí editoru)</span>
// 					<br /><br />
// 					$links
// 				</div>
// 			</td>
// 		</tr>
// 		
// 		</table>";
// 		
// 		$sc++;
// 	
// 	}
		
	
	}
	
	
	
	if(!empty($form_data['region'])) {
	
		// vicenasobny seznam
		/*$form_data['region'] = "
		<select name=\"region[]\" size=\"6\" multiple=\"multiple\" class=\"f10\" style=\"width: 100%;\">
			".$form_data['region']."
		</select>";*/
		
		// checkboxy
		$form_data['region'] = "
		<div style=\"position: absolute; top: 90px; left: 570px; width: 240px;\">
			<b>".$dct['deals_region']."</b><br /><br />".$form_data['region']."
		</div>";
	
	}
	
	
	
	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.jmeno.value == \"\") { alert(\"".$dct['deals_js_jmeno']."\"); form1.jmeno.focus(); return false; }
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
	
	<table class='admintable' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	<tr>
		<td width=\"200\">".$dct['deals_jmeno']."</td>
		<td>
			<input type=\"text\" name=\"jmeno\" value=\"".$form_data['jmeno']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>".$dct['deals_gsm']."</td>
		<td>
			<input type=\"text\" name=\"gsm\" value=\"".$form_data['gsm']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>".$dct['deals_tel']."</td>
		<td>
			<input type=\"text\" name=\"tel\" value=\"".$form_data['tel']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>".$dct['deals_fax']."</td>
		<td>
			<input type=\"text\" name=\"fax\" value=\"".$form_data['fax']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>".$dct['deals_email']."</td>
		<td>
			<input type=\"text\" name=\"email\" value=\"".$form_data['email']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	
	
	".$form_data['region_data']."
	
	
	<tr>
		<td colspan=\"2\"><br><br>
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	".$form_data['region']."
	
	</form>";
	
	return $form;

}






// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST)) { // echo $_POST['id']."<br />";

	$jmeno = trim($_POST['jmeno']);
	$gsm = trim($_POST['gsm']);
	$tel = trim($_POST['tel']);
	$fax = trim($_POST['fax']);
	$email = trim($_POST['email']);
	
	$hidden = $_POST['hidden']; // pole
	$pozn = $_POST['pozn']; // pole
	
	
	$trans = array (" " => ""); // vyhazeme mezery z tel, cisel
	
	$gsm = strtr($gsm, $trans);
	$tel = strtr($tel, $trans);
	$fax = strtr($fax, $trans);
	
	
	
	// id  jmeno  gsm  tel  fax  email  region  lang  hidden  pozn
	if(!empty($_POST['id'])) { // aktualizace
	
		$id = $_POST['id'];
		
		$query = "UPDATE ".T_DEALERS." SET 
		jmeno = '$jmeno', 
		gsm = '$gsm', 
		tel = '$tel', 
		fax = '$fax', 
		email = '$email' 
		WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	else { // novy zaznam
	
		// id jmeno gsm tel fax email region lang hidden pozn
		// id jmeno gsm tel fax email region lang hidden pozn 
		$query = "INSERT INTO ".T_DEALERS."  
		VALUES('', '$jmeno', '$gsm', '$tel', '$fax', '$email', 0, '".C_LANG."',0,'')";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$id = mysql_result($v, 0, 0);
	
	}
	
	
	
	// prirazen dealeru k regionu
	// zrusime puvodni prirazeni dealera k regionu
	// T_DEALERS_X_REGIONS - id_dealer id_region lang
	$query = "DELETE FROM ".T_DEALERS_X_REGIONS." 
	WHERE id_dealer = $id AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_DEALERS_X_REGIONS);
	
	// jsou-li prirazeny nejake regiony, ulozime je do DB
	// T_DEALERS_X_REGIONS - id_dealer id_region lang
	if(!empty($_POST['region'])) {
	
		reset($_POST['region']);
		while ($p = each($_POST['region'])) {
		
			$regID = $p['value'];
			
			if($hidden[$regID] != 1) $hidden[$regID] = 0;
			
			
			
			$pozn[$regID] = trim($pozn[$regID]);
			
			if(empty($pozn[$regID])) $pozn[$regID] = 'NULL';
			else $pozn[$regID] = "'".$pozn[$regID]."'";
			
			
			// echo "<br />$regID - ".$hidden[$regID]." - ".$pozn[$regID];
			
			$query = "INSERT INTO ".T_DEALERS_X_REGIONS." 
			VALUES($id,$regID,'".C_LANG."', ".$hidden[$regID].", ".$pozn[$regID].")";
			my_DB_QUERY($query,__LINE__,__FILE__);
		
		}
	
	}
	
	
	
	$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// ulozeni zanamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// seznam
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_deals_seznam'];
	
	
	// id  jmeno  gsm  tel  fax  email  region  lang  hidden  pozn
	$query = "SELECT * FROM ".T_DEALERS." WHERE ".SQL_C_LANG." ORDER BY jmeno";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$jmeno = $z['jmeno'];
		$hidden = $z['hidden'];
		
		if($hidden == 1) $hidden_style = "style=\"color: #939393;\"";
		else $hidden_style = "";
		
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td class=\"td1\" $hidden_style nowrap>".$jmeno."</td>
			
			<td width=\"15\" class=\"td2\">
				".ico_edit(MAIN_LINK."&f=dealers&a=edit&id=$id",$dct['deals_edit'])."</td>
		</tr>";
	
	}
	
	
	if (!empty($res)) {
	
		$data = "
		".SEARCH_PANEL."
		
		<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
		$res
		</table>";
	
	}
	
	
	
	if(empty($data)) $data = "<br /><br />".$dct['zadny_zaznam'];

}
// *****************************************************************************
// seznam
// *****************************************************************************








// *****************************************************************************
// editace (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = $dct['deals_edit'];
	
	
	// id  jmeno  gsm  tel  fax  email  region  lang  hidden  pozn
	$query = "SELECT * FROM ".T_DEALERS." 
	WHERE id = ".$_GET['id']." AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$form_data['jmeno'] = $z['jmeno'];
		$form_data['gsm'] = $z['gsm'];
		$form_data['tel'] = $z['tel'];
		$form_data['fax'] = $z['fax'];
		$form_data['email'] = $z['email'];
		$form_data['d_region'] = $z['region'];
		$form_data['hidden'] = $z['hidden'];
		$form_data['pozn'] = $z['pozn'];
		
		if($form_data['hidden'] == 1) $form_data['hidden'] = "checked";
		else $form_data['hidden'] = "";
	
	}
	
	
	if(!empty($form_data)) {
	
		$form_data['id'] = $_GET['id'];
		$form_data['link'] = MAIN_LINK."&f=dealers";
		$form_data['deletebutton'] = DELETE_BUTTON;
		
		$data = form($form_data,$dct);
	
	}
	else $data = $dct['zaznam_nenalezen'];

}
// *****************************************************************************
// editace (form)
// *****************************************************************************










// *****************************************************************************
// pridani kategorie (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = $dct['mn_deals_add'];
	
	$data = form($form_data,$dct);
	
	unset($_SESSION['last_region']);

}
// *****************************************************************************
// pridani vyrobce (form)
// *****************************************************************************












// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
if(!empty($_GET['delete'])) {

	$query = "DELETE FROM ".T_DEALERS." 
	WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG." LIMIT 1";
	my_DB_QUERY($query,__LINE__,__FILE__);
	my_OPTIMIZE_TABLE(T_DEALERS);
	
	
	// T_DEALERS_X_REGIONS - id_dealer id_region lang
	$query = "DELETE FROM ".T_DEALERS_X_REGIONS." 
	WHERE id_dealer = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	my_OPTIMIZE_TABLE(T_DEALERS_X_REGIONS);
	
	
	
	$_SESSION['alert_js'] = "Záznam odstraněn";
	
	$status = $dct['zaznam_odstranen'];
	
	Header("Location: ".MAIN_LINK."&f=dealers&a=list");
	exit;

}
// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
?>
