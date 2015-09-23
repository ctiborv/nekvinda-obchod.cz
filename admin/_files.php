<?php


function form($form_data,$dct) {
  
  $vyberte_soubor=$pozn='';

	if($_GET['a'] == "add") {
	
		$pozn = "<span class=\"f10i\">(".$dct['files_prepsat'].")</span><br /><br />";
		
		$vyberte_soubor = "else if (form1.soubor.value == \"\") { alert(\"".$dct['files_js_soubor']."\"); form1.soubor.focus(); return false; }";
	
	}
	
	
	if(!empty($form_data['id'])) {
	
		$form_data['soubor'] = "
	<tr>
		<td height=\"30\">".$dct['files_zdroj']."</td>
		<td>
			<a href=\"".FILES_UPL.$form_data['soubor']."\" target=\"_blank\">".FILES_UPL.$form_data['soubor']."</a>
		</td>
	</tr>
	
	
	
	<tr>
		<td height=\"30\">".$dct['files_zobrazeni']."</td>
		<td>
			<a href=\"".FILES_UPL.$form_data['soubor']."\" target=\"_blank\">".$form_data['odkaz']."</a>
		</td>
	</tr>";
	
	}
	
	
	
	$form = "
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.odkaz.value == \"\") { alert(\"".$dct['files_js_nazev']."\"); form1.odkaz.focus(); return false; }
		$vyberte_soubor
		else return true;
	
	}
	
	
	function del() {
	
		if (confirm(\"".$dct['opravdu_odstranit']."\"))
			{ location = \"".MAIN_LINK."&f=files&delete=".$form_data['id']."\"; }
	
	}
	// -->
	</SCRIPT>
	
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" enctype=\"multipart/form-data\" 
			onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\" class='admintable nobg'>
	
	
	
	<tr>
		<td width=\"160\">".$dct['files_soubor']."<br />
			<span class=\"f10i\">(".$dct['files_max_size'].": ".
			ini_get('upload_max_filesize').")</span><br /><br /></td>
		<td width=\"340\">
			<input type=\"file\" name=\"soubor\" style=\"width: 100%;\" class=\"f10\" />
			$pozn
		</td>
	</tr>
	
	
	
	<tr>
		<td height=\"30\">".$dct['files_odkaz']."</td>
		<td>
			<input type=\"text\" name=\"odkaz\" value=\"".$form_data['odkaz']."\" style=\"width: 100%;\" class=\"f10\">
		</td>
	</tr>
	
	
	
	".$form_data['soubor']."
	
	
	
	<tr>
		<td>".$dct['files_text']."</td>
		<td>
			<textarea name=\"text\" style=\"width: 100%; height: 60px;\">".$form_data['text']."</textarea>
		</td>
	</tr>
	
	
	
	<tr>
		<td>".$dct['files_pozn']."<br />
			<span class=\"f10i\">(".$dct['files_pozn_admin'].")</span></td>
		<td>
			<textarea name=\"pozn\" style=\"width: 100%; height: 60px;\">".$form_data['pozn']."</textarea>
		</td>
	</tr>
	
	
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	
	
	
	<tr>
		<td colspan=\"2\">
		
			<br><br>
			
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

	$odkaz = trim($_POST['odkaz']);
	$text = trim($_POST['text']);
	$pozn = trim($_POST['pozn']);
	
	
  
  $mime = $_FILES["soubor"]["type"];
   

	
	if(!empty($_POST['id'])) { // aktualizace
	
		$id = $_POST['id'];
		
		// id  soubor  odkaz  text  pozn  lang
		$query = "UPDATE ".T_DOWNLOAD." SET 
		odkaz = '$odkaz', 
		text = '$text', 
		pozn = '$pozn',
    mime = '$mime' 
		WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	else { // novy zaznam
	
		// id  soubor  odkaz  text  pozn  lang
		$query = "INSERT INTO ".T_DOWNLOAD."  
		VALUES('', '$soubor', '$odkaz', '$text', '$pozn','$mime', '".C_LANG."')";
		
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$id = mysql_result($v, 0, 0);
	
	}
	
	
	
	if($_FILES['soubor']['name'] != "") {
	
		// najdeme nazev souboru pridruzeny k ID a odstranime
		$query = "SELECT soubor FROM ".T_DOWNLOAD." 
		WHERE id = $id AND ".SQL_C_LANG."";
		
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
			@unlink(FILES_UPL . $z['soubor']);
		
		}
		
		
		// nebudeme provadet kontrolu zda soubor existuje, pred originalni 
		// nazev souboru pridame id
		
		$new = $soubor = "[" . $id . "]_" . strtr($_FILES['soubor']['name'],"áäčďéěëíňóöřšťúůüýžÁÄČĎÉĚËÍŇÓÖŘŠŤÚŮÜÝŽ -","aacdeeeinoorstuuuyzAACDEEEINOORSTUUUYZ__");
		
		$new = FILES_UPL . $new;
		
		// if(!$ulozit = move_uploaded_file($_FILES['soubor']['tmp_name'],$new)) {
		if(!$ulozit = copy($_FILES['soubor']['tmp_name'],$new)) {
		
			$_SESSION['alert_js'] = $dct['files_chyba_upload'];
			
			$_SESSION['alert'] = $dct['files_chyba_upload'];
			
			Header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		
		}
		
		
		// id  soubor  odkaz  text  pozn  lang
		// zaktualizujeme zaznam o skutecny nazev souboru
		$query = "UPDATE ".T_DOWNLOAD." SET 
		soubor = '$soubor' WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	
	
	
	
	$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// seznam
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_seznam_files'];
	
	
	// id  soubor  odkaz  text  pozn  lang
	$query = "SELECT id, soubor, odkaz FROM ".T_DOWNLOAD." 
	WHERE ".SQL_C_LANG." ORDER BY odkaz";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$res='';
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		
		$odkaz = "".$z['odkaz']."<a href=\"".FILES_UPL . $z['soubor']."\" target=\"_blank\"></a>";
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td class=\"td1\" nowrap>$odkaz</td>
			
			<td width=\"15\" class=\"td2\">
				".ico_edit(MAIN_LINK."&f=files&a=edit&id=$id",$dct['files_edit'])."</td>
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
// seznam
// *****************************************************************************









// *****************************************************************************
// editace (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = $dct['files_edit'];
	
	
	
	$query = "SELECT * FROM ".T_DOWNLOAD." 
	WHERE id = ".$_GET['id']." AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$form_data['soubor'] = $z['soubor'];
		$form_data['odkaz'] = $z['odkaz'];
		$form_data['text'] = $z['text'];
		$form_data['pozn'] = $z['pozn'];
	
	}
	
	
	if(!empty($form_data)) {
	
		$form_data['id'] = $_GET['id'];
		$form_data['link'] = MAIN_LINK."&f=files";
		$form_data['deletebutton'] = DELETE_BUTTON;
		
		$data = form($form_data,$dct);
	
	}
	else $data = $dct['zaznam_nenalezen'];

}
// *****************************************************************************
// editace (form)
// *****************************************************************************









// *****************************************************************************
// pridani souboru (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = $dct['mn_pridat_files'];
	
	$form_data=null;
	
	$data = form($form_data,$dct);

}
// *****************************************************************************
// pridani souboru (form)
// *****************************************************************************









// *****************************************************************************
// odstranit
// *****************************************************************************
if(!empty($_GET['delete'])) {

	// odstraneni zaznamu o prirazenych souborech X produkty
	$query = "DELETE FROM ".T_GOODS_X_DOWNLOAD." 
	WHERE id_file = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS_X_DOWNLOAD);
	
	
	
	$query = "SELECT soubor FROM ".T_DOWNLOAD." 
	WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG." LIMIT 0,1";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		@unlink(FILES_UPL . $z['soubor']);
	
	}
	
	$query = "DELETE FROM ".T_DOWNLOAD." 
	WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_GOODS);
	
	
	$_SESSION['alert_js'] = "Záznam odstraněn";
	
	Header("Location: ".MAIN_LINK."&f=files&a=list");
	exit;

}
// *****************************************************************************
// odstranit
// *****************************************************************************








?>