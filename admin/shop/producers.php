<?php

// TODO: pri odstraneni vyrobce zkontrolovat produkty a upozornit na odstraneni 
// i techto vyrobku z DB


function form($form_data,$dct) {
	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.name.value == \"\") { alert(\"".$dct['vyr_js_odd']."\"); form1.name.focus(); return false; }
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
			style=\"width: 250px;\" class=\"f10\">
    </td>
	</tr>
	
	<tr>
		<td>skrytý</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" 
			".$form_data['hidden']."></td>
	</tr>
	<tr>
	  <td>Dodací lhůta (pro XML)</td>
		<td width=\"30\">&nbsp;</td>
	  <td>
			<input type=\"text\" name=\"dodani\" value=\"".$form_data['dodani']."\" 
			style=\"width: 25px;\" class=\"f10\"> &nbsp;<i>(počet dnů)</i>
    </td>
  </tr>
	<tr>
		<td colspan=\"3\"><br><br>
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	</form>";
	
	return $form;

}






// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST)) { // echo $_POST['id']."<br />";

  $name = trim($_POST['name']);
  
	$hidden = $_POST['hidden'];
	if ($hidden != 1) $hidden = 0;
	
	$dodani = trim($_POST['dodani']);
	if(empty($dodani))$dodani=0;
	
	// id name hidden lang
	if(!empty($_POST['id'])) { // aktualizace
	
		$query = "UPDATE ".T_PRODS." SET 
		name = '".$name."', 
		hidden = $hidden,
    dodani = $dodani
		WHERE id = ".$_POST['id']." AND ".SQL_C_LANG."";
		
	
	}
	else { // novy zaznam
	
		$query = "INSERT INTO ".T_PRODS."  
		VALUES(NULL, '".$name."', $hidden, $dodani,'".C_LANG."')";
	
	}
	
	
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	
	$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// ulozeni zanamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// seznam vyrobcu
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_seznam_vyrobcu'];
	
	
	// id name hidden lang
	$query = "SELECT * FROM ".T_PRODS." WHERE ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$res='';
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$name = $z['name'];
		$hidden = $z['hidden'];
		
		if($hidden == 1) $hidden_style = "style=\"color: #939393;\"";
		else $hidden_style = "";
		
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td class=\"td1\" $hidden_style nowrap>".$name."</td>
			
			<td width=\"15\" class=\"td2\">
				".ico_edit(MAIN_LINK."&f=producers&a=edit&id=$id",'Upravit výrobce')."</td>
		</tr>";
	
	}
	
	
	if (!empty($res)) {
	
		$data = "
		".SEARCH_PANEL."
		
		<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
		$res
		</table>";
	
	}
	
	
	
	if(empty($data)) $data = "<br /><br />".$dct['zadny_zaznam'];

}
// *****************************************************************************
// seznam vyrobcu
// *****************************************************************************








// *****************************************************************************
// editace (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = $dct['vyr_edit'];
	
	
	
	$query = "SELECT * FROM ".T_PRODS." 
	WHERE id = ".$_GET['id']." AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$form_data['name'] = $z['name'];
		$form_data['hidden'] = $z['hidden'];
		$form_data['dodani'] = $z['dodani'];
		
		if($form_data['hidden'] == 1) $form_data['hidden'] = "checked";
		else $form_data['hidden'] = "";
	
	}
	
	
	if(!empty($form_data)) {
	
		$form_data['id'] = $_GET['id'];
		$form_data['link'] = MAIN_LINK."&f=producers";
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

	$nadpis = $dct['mn_pridat_vyrobce'];
  
  $form_data=null;
  	
	$data = form($form_data,$dct);
	
	unset($_SESSION['last_id_parent']);

}
// *****************************************************************************
// pridani vyrobce (form)
// *****************************************************************************












// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
if(!empty($_GET['delete'])) {

	$query = "DELETE FROM ".T_PRODS." 
	WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_PRODS);
	
	$_SESSION['alert_js'] = "Záznam odstraněn";
	
	$status = $dct['zaznam_odstranen'];
	
	Header("Location: ".MAIN_LINK."&f=producers&a=list");
	exit;

}
// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
?>
