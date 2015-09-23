<?php
$addRecord = "<a href=\"".MAIN_LINK."&f=cena_cat&a=add\" class=\"\">Přidat novou</a><br /><br />";
// *****************************************************************************
// formular pro editaci
// *****************************************************************************
function form($form_data,$dct) {

	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.nazev.value == \"\") { alert(\"Vyplňte název cenové kategorie\"); form1.nazev.focus(); return false; }
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
	
	
	<table class='admintable nobg' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	<tr>
		<td width=\"160\">
			Název </td>
		<td width=\"340\">
			<input type=\"text\" name=\"nazev\" value=\"".$form_data['nazev']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>	
	
	<tr>
		<td width=\"160\">
			Sleva </td>
		<td width=\"340\">
			<input type=\"text\" name=\"sleva\" value=\"".$form_data['sleva']."\" 
			style=\"width: 30;\" class=\"f10\">%</td>
	</tr>
		
	<tr>
		<td width=\"160\">
			Popis </td>
		<td width=\"340\">
			<textarea name=\"popis\" style=\"width: 100%;\" class=\"f10\">".$form_data['anotace']."</textarea> 
</td>
	</tr>	
	
	
	<tr>
		<td colspan=\"2\"><br /><br /><br />
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	
	</form>";
	
	return $form;//

}
// *****************************************************************************
// formular pro editaci
// *****************************************************************************


// *****************************************************************************
// odstraneni zaznamu
// *****************************************************************************
if (!empty($_GET['delete'])) {
    $query2 = "SELECT COUNT(".T_ADRESY_F.".id) 
		FROM ".T_ADRESY_F."  WHERE cenovka=".$_GET['delete']." ";
		$v2 = my_DB_QUERY($query2,__LINE__,__FILE__);
		$count_records2 = mysql_result($v2,0,0);
    if ($count_records2>0){
     $_SESSION['alert_js'] ='Nelze smazat ! \nCenová kategorie je uvedena u '.$count_records2.' zákazníků.';
     Header("Location: ".MAIN_LINK."&f=cena_cat&a=list");
     exit;
      }
     else {
  
	
	// id  title  content  in_menu  menu_pos 
	$query = "DELETE FROM ".T_CENY." WHERE id = " . $_GET['delete']. " " ;
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_CENY);
  
  //dodělat smazání z tabulky zbozi_x_ceny
  $querycena="SELECT id FROM ".T_ZBOZI_X_CENY." WHERE id_cena=".$_GET['delete']." ";
  $vcena = my_DB_QUERY($querycena,__LINE__,__FILE__);
  while ($zcena = mysql_fetch_array($vcena)) {
		    $querycena = "DELETE FROM ".T_ZBOZI_X_CENY." WHERE  id=".$zcena['id']." ";
        my_DB_QUERY($querycena,__LINE__,__FILE__);
      }
  my_OPTIMIZE_TABLE(T_ZBOZI_X_CENY);    
	
	Header("Location: ".MAIN_LINK."&f=cena_cat&a=list");
	exit;

}
}
// *****************************************************************************
// odstraneni zaznamu
// *****************************************************************************








// *****************************************************************************
// editace / pridani zaznamu
// *****************************************************************************
if (!empty($_POST)) {

	$nazev = trim($_POST['nazev']);
	$sleva = trim($_POST['sleva']);
	$popis = trim($_POST['popis']);
	
	// echo exit;
	
	
	
	// id  title  content  in_menu  menu_pos  homepage  hidden
	if (!empty($_POST['id'])) { // editace existujiciho
	
			
		
		$query = "UPDATE ".T_CENY." SET 
		nazev = '$nazev', 
		anotace = '$popis',
		sleva = '$sleva'
		WHERE id = ".$_POST['id'];
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	else { // novy zaznam
	  $query = "INSERT INTO ".T_CENY." 
		VALUES('', '$nazev','$popis',$sleva,0)";
		my_DB_QUERY($query,__LINE__,__FILE__);
    
/*    $query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$id = mysql_result($v, 0, 0);
		
    //vložení SEO
		$_POST['novy_zaznam']=$id;
*/
	}
	
	
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
	
  $tbl='';	
	
  while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$nazev = $z['nazev'];
		$popis = $z['anotace'];
		$sleva = $z['sleva'];
	
		
		$tbl .= "
		<tr ".TABLE_ROW.">
    	<td width=\"150\" class=\"td1\" >$nazev</td>
    	<td class=\"td1\">$popis</td>
    	<td width=\"30\" class=\"td1\">$sleva%</td>
    	<td width=\"15\" class=\"td1\">
				".ico_edit(MAIN_LINK."&f=cena_cat&a=edit&id=$id",'Editovat')."</td>
    </tr>";
		//".ico_edit("prew&id=$id",$dct['cena_edit'])."
		
			
	}

	return $tbl;

}
// *****************************************************************************
// fce pro generovani tabulek se seznamem stranek
// *****************************************************************************







// *****************************************************************************
// seznam stranek
// *****************************************************************************
if (isset($_GET['a']) && $_GET['a'] == "list") {

	$nadpis = $dct['mn_cena_seznam'];
	
	
	// stranky uvedene v menu 2 - lista v prave casti stranky
	$tbl2_1 = table("SELECT * FROM ".T_CENY." ");
	
	$tbl='';
	
	if(!empty($tbl2_1)) {
	
			$tbl .= "
			
			<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl2_1
			
			</table>";
	
	}
	

	if (empty($tbl)) $data = "
			".$addRecord."Žádný záznam";
	else $data = "
			<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
			".$addRecord."<br />
			<tr>
				<td width=\"150\" class=\"td1\"><b>název kategorie</b></td>
				<td width=\"225\" class=\"td1\"><b>popis</b></td>
				<td class=\"td1\"><b>zvýhodnění</b></td>
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

	$nadpis = $dct['mn_cena_edit'];
	$form_data['link'] = MAIN_LINK."&f=cena_cat";
	$form_data['deletebutton'] = DELETE_BUTTON;
	
	// id  title  content  in_menu  menu_pos 
	$query = "SELECT * FROM ".T_CENY." 
	WHERE id = ".$_GET['id']." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {

		$form_data['id'] = $z['id'];
		$form_data['nazev'] = $z['nazev'];
		$form_data['anotace'] = $z['anotace'];
		$form_data['sleva'] = $z['sleva'];
  }
	
	$data = form($form_data,$dct);

}
// *****************************************************************************
// editace (form)
// *****************************************************************************










// *****************************************************************************
// pridani (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = $dct['mn_cena_add'];
	
	$data = form(null,$dct);
	
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