<?php



$addRecord = "<a href=\"".MAIN_LINK."&f=products_parameters&a=add\" class=\"\">Přidat nový</a><br /><br />";


// T_PARAMETRY1
// id  nazev  lang  pozn

// T_PARAMETRY2
// id  id_karta  nazev  jednotka  poradi

// T_PARAMETRY3
// id_parametr  hodnota  id_kp

// T_PARAMETRY4
// id  id_karta  id_produkt






// *****************************************************************************
// formular pro editaci vzoru
// *****************************************************************************
function form_karta($form_data,$dct) {
	
	$formPole1 = '
			<input type="checkbox" CHECK name="N___hidden[###hid]" value="1" class="f10" style="margin-right: 40px;">
			<input type="text" name="N___poradi[###por]" value="###porV" style="width: 25px;" class="f10">
			<input type="text" name="N___parametr[###par]" value="###parV" style="width: 190px;" class="f10">
			<input type="text" name="N___jednotka[###jed]" value="###jedV" style="width: 30px;" class="f10">
			';
	
	$dParametr = '<input type="checkbox" name="delete[###del]" value="###del" class="f10">odstranit<br />
	';
	
	$formPole='';
	$poradi = 0;
	
	
	if(!empty($form_data['id'])) { // editace existujiciho
	
		$form_data['deletebutton'] = button('button','Zrušit vzor produktového listu','class="butt_red" onclick="return del()"');
		
		
		// id  nazev  lang  pozn
		$query = "SELECT nazev, pozn 
		FROM ".T_PARAMETRY1." WHERE id = ".$form_data['id']." LIMIT 0,1";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
		
			$form_data['nazev'] = $z['nazev'];
			$form_data['pozn'] = $z['pozn'];
		
		}
		
		// id  id_karta  nazev  jednotka  poradi
		$query = "SELECT id, nazev, hidden, jednotka, poradi 
		FROM ".T_PARAMETRY2." WHERE id_karta = ".$form_data['id']." ORDER BY poradi";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		

		
		while ($z = mysql_fetch_array($v)) {
		
			$id = $z['id'];
			$parametr = $z['nazev'];
			$jednotka = $z['jednotka'];
			$poradi = $z['poradi'];
			$check =  $z['hidden'];
			
			if($check==1)$check='checked="checked"';
			else $check=''; 
			
			$trans = array('###hid'=>$id,'CHECK'=>$check,'###del'=>$id,'N___'=>'','###por'=>$id,'###porV'=>$poradi,'###par'=>$id,'###parV'=>$parametr,'###jed'=>$id,'###jedV'=>$jednotka);
			$formPole .= strtr($formPole1, $trans);
			$formPole .= strtr($dParametr, $trans);
		
		}
	
	}
	
	
	
	for ($i = $poradi + 1; $i <= $poradi + 10; $i++) {
	
		$trans = array('###por'=>$i,'CHECK'=>'','###porV'=>$i,'###par'=>$i,'###parV'=>'','###jed'=>$i,'###jedV'=>'');
		$formPole .= strtr($formPole1, $trans).'<br />';
	
	}
	
	
	$formPole .= '<br />Další parametry můžete přidat později.';
	
	
	
	
	if(empty($_GET['P']))$_GET['P']='';
	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	
	
	function validate(form1) {
	
		if (form1.nazev.value == \"\") { alert(\"Vyplňte název produktového listu\"); form1.nazev.focus(); return false; }
		// else if (form1.id_parent.value == \"0\") { alert(\"Musíte vybrat některou z vnořených úrovní\"); form1.id_parent.focus(); return false; }
		// else if (form1.dph.value == \"\") { alert(\"Zatrhněte výši DPH\"); form1.dph.focus(); return false; }
		else return true;
	
	}
	
	// odstraneni zaznamu
	function del() {
	
		if (!confirm(\"Opravdu zrušit vzor produktového listu?\"))
			return false;
		else
			location = \"".MAIN_LINK."&f=products_parameters&deleteT=".$form_data['id']."&P=".$_GET['P']."\";
	
	}
	
	
	
	// -->
	</SCRIPT>
	
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" enctype=\"multipart/form-data\" 
		onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	
	<table class='admintable nobg' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	<tr>
		<td>
			Název<br />
			<span class=\"f10i\">(max. 255 znaků)</span></td>
		<td>
			<input type=\"text\" name=\"nazev\" value=\"".$form_data['nazev']."\" 
			style=\"width: 100%;\" class=\"f10\"></td>
	</tr>
	
	
	<tr>
		<td>Poznámka</td>
		<td>
			<textarea name=\"pozn\" style=\"width: 100%; height: 60px;\" title=\"\">".$form_data['pozn']."</textarea></td>
	</tr>
	
	<tr>
		<td><br /><br /></td>
		<td><br /><br />nezobrazovat veřejně na detailu produktu</td>		
	</tr>         	
	
	<tr>
		<td valign=\"top\">Pořadí - parametr - měrná jednotka<br /><br />
			<span class=\"f10\">např.<br />1 - délka - mm<br />2 - šířka - mm<br />3 - hmotnost - kg</span></td>
		<td>$formPole</td>
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
// formular pro editaci vzoru
// *****************************************************************************




// *****************************************************************************
// formular pro editaci produkt. listu
// *****************************************************************************
function form_ProdList($form_data,$dct) {

	// T_PARAMETRY1
	// id  nazev  lang  pozn
	
	// T_PARAMETRY2
	// id  id_karta  nazev  jednotka  poradi
	
	// T_PARAMETRY3
	// id_parametr  hodnota  id_kp
	
	// T_PARAMETRY4
	// id  id_karta  id_produkt
	$query = "SELECT ".T_PARAMETRY1.".nazev, ".T_PARAMETRY1.".pozn, 
	".T_PARAMETRY4.".id AS idPxK, ".T_PARAMETRY4.".id_karta AS ListID 
	FROM ".T_PARAMETRY1.", ".T_PARAMETRY4." 
	WHERE ".T_PARAMETRY4.".id_produkt = ".$form_data['ProdID']." 
	AND ".T_PARAMETRY1.".id = ".T_PARAMETRY4.".id_karta 
	LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$ListID='';
	
	while ($z = mysql_fetch_array($v)) {
	
		$idPxK = $z['idPxK'];
		$ListID = $z['ListID'];
		
		$form_data['nazev'] = $z['nazev'];
		$form_data['pozn'] = $z['pozn'];
	
	}
	
	
	
	// ***************************************************************************
	// roleta vzoru
	// ***************************************************************************
	// id  nazev  lang  pozn
	$query = "SELECT id, nazev FROM ".T_PARAMETRY1." 
	WHERE ".SQL_C_LANG." ORDER BY nazev";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$select2='';
	while ($z = mysql_fetch_array($v)) {
	
		$sid = $z['id'];
		
		if($sid == $ListID) {
		
			$selected = "selected";
			$selectedName = $z['nazev'];
		
		} else {
		
			$selected = '';
			$selectedName = '';
		
		}
		
		
		$select2 .= "
		<option value=\"$sid\" ".$selected.">".$z['nazev']."</option>";// - ".$sid."
	
	}
	
	if (!empty($select2)) {
	
		$select2 = "
		<select name=\"vzor\" class=\"f10\" style=\"width: 210px;\">
		<option value=\"0\">žádný</option>
		$select2
		</select>
		
		".button('','Nastavit','class="butt_red" onclick="return zmena_vzoru()"')."
		
		";
	
	} else $select2 = "Žádné typy produktových listů nebyly v databázi nalezeny.<br /><br />";
	
	
	
	// ***************************************************************************
	// roleta vzoru
	// ***************************************************************************
	
	
	
	
	
	if(!empty($idPxK)) {
	
		// hodnoty parametru pro produkt
		// id_parametr  hodnota  id_kp  img
		$query = "SELECT id_parametr, hodnota, id_kp, img FROM ".T_PARAMETRY3." WHERE id_kp = $idPxK";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v)) {
		
			$hodnoty[$z['id_parametr']] = $z['hodnota'];
			$symboly[$z['id_parametr']] = '
					<input type="hidden" name="symboly['.$z['id_parametr'].']" value="'.$z['img'].'">';
			
			if(!empty($z['img'])) {
			
				$img1 = IMG_I_S.$z['img'];
				$img2 = IMG_I_O.$z['img'];
				
				$odstranitSymbol[$z['id_kp'].'-'.$z['id_parametr']] = '
					'.showimg($img1,$img2,$width,$height,$border,$title,$next_params,$timestamp).'
					<input type="checkbox" name="deleteImg['.$z['id_parametr'].']" value="'.$z['img'].'" /> 
								odstranit symbol '.$z['img'].'';
			
			}else{
        $odstranitSymbol[$z['id_kp'].'-'.$z['id_parametr']]='';
      }
		
		}
		
		
		
		
		// pole pro editaci hodnot
		// id  id_karta  nazev  jednotka  poradi
		$query = "SELECT id, nazev, jednotka, poradi, hidden 
		FROM ".T_PARAMETRY2." WHERE id_karta = ".$ListID." ORDER BY hidden desc,poradi";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);              
		
		$parametry='';
		
		while ($z = mysql_fetch_array($v)) {
		
			$idParametr = $z['id'];
			$parametr = $z['nazev'];
			$jednotka = $z['jednotka'];
			$poradi = $z['poradi'];
			$hidden=$z['hidden'];
			
			if($hidden==1){
				$add=' <strong>(nezobrazuje se na detailu)</strong>';
			}else{
				$add='';
			}
			
			
			$parametry .= '
			
			<tr>
				<td valign="top">'.$parametr.' '.$add.'</td>
				<td valign="top">
					<input type="text" name="parametry['.$idParametr.']" value="'.$hodnoty[$idParametr].'" style="width: 250px;" class="f10"> '.$jednotka.'
					<!--<br />
					<input type="file" name="newImg['.$idParametr.']" class="f10" style="width: 250px;" title="Zde můžete pro tento parametr vložit obrazový symbol" />
					<br />-->
					'.$symboly[$idParametr].'
					'.$odstranitSymbol[$idPxK.'-'.$idParametr].'
				</td>
			</tr>
			
			<tr>
				<td colspan=\"2\">&nbsp;</td>
			</tr>';// - '.$idParametr.' - '.$idPxK.'
		
		}
		
		
		if(empty($form_data['deletebutton']))$form_data['deletebutton']='';
		
		$buttony = SAVE_BUTTON."
			
			".$form_data['deletebutton']."";
	
	} else {
	
		$parametry = '
			
			<tr>
				<td>&nbsp;</td>
				<td>
					Produktový list pro tento výrobek není založen.
				</td>
			</tr>';
	
	}
	
	
	if(empty($form_data['pozn']))$form_data['pozn']='';
	if(empty($buttony))$buttony='';
	if(empty($idPxK))$idPxK='';
	
	 
	
	
	//onSubmit=\"return validate(this)\"
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	
	function zmena_vzoru() {
	
		if (!confirm('Změnou Vzoru produktového listu se zruší původní produktový list\\n' + 
			'a bude třeba jej vyplnit znovu podle nově nastaveného vzoru.\\n\\n' + 
			'původní Produktový list: $selectedName\\n\\n' + 
			'OK - pokračovat\\nSTORNO - zruší akci\\n'))
		return false;
	
	}
	// -->
	</SCRIPT>
	
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\">
	
	
	<input type=\"hidden\" name=\"Pid\" value=\"".$_GET['Pid']."\">
	
	
	<table class='admintable nobg' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	<tr>
		<td>Použít vzor</td>
		<td>".$select2."</td>
	</tr>
	
	
	<tr>
		<td>Poznámka</td>
		<td class=\"f10\">".$form_data['pozn']."</td>
	</tr>
	
	</table>
	
	
	</form>
	
	
	
	
	
	
	
	
	<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">
	
	
	<input type=\"hidden\" name=\"idPxK\" value=\"".$idPxK."\">
	
	
	
	<table class='admintable nobg' border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	
	$parametry
	
	
	<tr>
		<td colspan=\"2\"><br /><br /><br />
			
			$buttony
		
		</td>
	</tr>
	
	</table>
	
	
	</form>";
	
	return $form;//

}
// *****************************************************************************
// formular pro editaci produkt. listu
// *****************************************************************************









// *****************************************************************************
// odstraneni grafickych symbolu u parametru
// *****************************************************************************
function deleteSymboly($q) {

	// hodnoty parametru pro produkt
	$v111 = my_DB_QUERY($q,__LINE__,__FILE__);
	while ($z111 = mysql_fetch_array($v111)) {
	
		unlink(IMG_I_S.$z111['img']);
		unlink(IMG_I_O.$z111['img']);
	
	}

}
// *****************************************************************************
// odstraneni grafickych symbolu u parametru
// *****************************************************************************









// *****************************************************************************
// seznam sablon
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_parameters_list'];
	
	
	
	
	// id  nazev  lang  pozn
	$query = "SELECT id, nazev FROM ".T_PARAMETRY1." 
	WHERE ".SQL_C_LANG." ORDER BY nazev";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$res='';
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$nazev = $z['nazev'];
		
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td class=\"td1\">".$nazev."</td>
			
			<td width=\"35\" class=\"td2\">
				".ico_edit(MAIN_LINK."&f=products_parameters&a=edit&id=".$id,"Upravit")."</td>
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
	
	
	if(empty($data)) $data = "<br /><br />$addRecord".$dct['zadny_zaznam'];

}
// *****************************************************************************
// seznam sablon
// *****************************************************************************









// *****************************************************************************
// produktovy list produktu - vyplnujeme konkretni parametry
// *****************************************************************************
if(isset($_POST['idPxK'])) {

	// id_parametr  hodnota  id_kp  img
	$query = 'DELETE FROM '.T_PARAMETRY3.' WHERE id_kp = '.$_POST['idPxK'].'';
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_PARAMETRY3);
	
	
	reset($_POST['parametry']);
  while ($p = each($_POST['parametry'])) {
  
  	$n = $p['key'];
  	$h = $p['value'];
  	
  	
  	
		if(!empty($_POST['deleteImg'][$n])) { // symbol je urcen ke smazani
		
			// smazeme img
			@unlink(IMG_I_S.$_POST['deleteImg'][$n]); // odstranime zmenseninu
			@unlink(IMG_I_O.$_POST['deleteImg'][$n]); // odstranime original
			
			$_POST['symboly'][$n] = ''; // vyprazdnime bunku v DB
		
		}
  	
  	
  	
  	// pokud posilame obrazek
  	if(!empty($_FILES['newImg']['tmp_name'][$n])) {
		
			// ulozime novy img
			$e = img_upload2($_POST['idPxK'].'-'.$n,$n);
			
			$_POST['symboly'][$n] = $_POST['idPxK'].'-'.$n.'.'.$e.''; // aktualizujeme bunku v DB
		
		}
  	
		// id_parametr hodnota id_kp  img
  	$query = "INSERT INTO ".T_PARAMETRY3." VALUES($n,'$h',".$_POST['idPxK'].",'".$_POST['symboly'][$n]."')";
  	my_DB_QUERY($query,__LINE__,__FILE__);
  
  }
  
  
	
	$_SESSION['alert_js'] = "Záznam uložen";
	
	$back = $_SERVER['HTTP_REFERER'];
	Header("Location: ".$back);
	exit;

}
// *****************************************************************************
// *****************************************************************************







// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
function parametry($delete,$hidden,$poradi,$parametry,$jednotky,$akce,$idKarta) {

	// pracuje s parametry ve Vzoru produktoveho listu
	// $akce urcuje zda bude INSERT nebo UPDATE
	
	// odstranujeme
	if(!empty($delete)) {
	
		reset($delete);
		while ($pD = each($delete)) {
		
			$D = $pD['key'];
			
			// id  id_karta  nazev  jednotka  poradi
			$query = 'DELETE FROM '.T_PARAMETRY2.' WHERE id = '.$D.' LIMIT 1';
			my_DB_QUERY($query,__LINE__,__FILE__);
			
			my_OPTIMIZE_TABLE(T_PARAMETRY2);
			
			
			deleteSymboly("SELECT img FROM ".T_PARAMETRY3." WHERE id_parametr = $D AND img != ''");
			
			// id_parametr  hodnota  id_kp
			$query = 'DELETE FROM '.T_PARAMETRY3.' WHERE id_parametr = '.$D.'';
			my_DB_QUERY($query,__LINE__,__FILE__);
			
			my_OPTIMIZE_TABLE(T_PARAMETRY3);
		
		}
	
	}
	
	
	
	// zpracujeme parametry
	if(!empty($parametry)) {
		
		// aktualizujeme nebo pridavame
		reset($parametry);
		while ($p = each($parametry)) {
		
			$K = $p['key'];
			
			if(!empty($parametry[$K]) && empty($delete[$K])) {
			
				$hi = trim($hidden[$K]);
				$pa = addslashes(trim($parametry[$K]));
				$je = addslashes(trim($jednotky[$K]));
				$po = trim($poradi[$K]);
				
				if(empty($hi))$hi=0;
				
				if($akce == 'update') {
				
					// id  id_karta  nazev  jednotka  poradi
					$query = "UPDATE ".T_PARAMETRY2." SET 
					nazev = '$pa', 
					hidden = '$hi',
					jednotka = '$je', 
					poradi = '$po' 
					WHERE id = $K";
				
				}
				
				if($akce == 'insert') {
				
					// id  id_karta  nazev  jednotka  poradi
					$query = "INSERT INTO ".T_PARAMETRY2." VALUES(NULL,$idKarta,'$pa','$hi','$je','$po')";
				
				}
				
				
				my_DB_QUERY($query,__LINE__,__FILE__);
			
			}
		
		}
	
	}

}




if (!empty($_POST['nazev'])) { // upravujeme nebo pridavame zaznam

	$nazev = trim($_POST['nazev']);
	$pozn = trim($_POST['pozn']);
	
	// aktualizovane
	$hidden = $_POST['hidden'];
	$poradi = $_POST['poradi'];
	$parametry = $_POST['parametr'];
	$jednotky = $_POST['jednotka'];
	// k odstraneni
	$delete = $_POST['delete'];
	// nove
	$N___hidden = $_POST['N___hidden'];
	$N___poradi = $_POST['N___poradi'];
	$N___parametry = $_POST['N___parametr'];
	$N___jednotky = $_POST['N___jednotka'];
	
	
	if(!empty($_POST['id'])) { // aktualizace
	
		$id = $_POST['id'];
		
		// id  nazev  lang  pozn
		$query = "UPDATE ".T_PARAMETRY1." SET 
		nazev = '$nazev', pozn = '$pozn' 
		WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		
		// zpracujeme parametry - aktualizace puvodnich
		parametry($delete,$hidden,$poradi,$parametry,$jednotky,$akce='update',$id);
	
	} else { // novy zaznam
	
		// id  nazev  lang  pozn
		$query = "INSERT INTO ".T_PARAMETRY1." VALUES('','$nazev','".C_LANG."','$pozn')";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$id = mysql_result($v, 0, 0);
	
	}
	
	
	
	// zpracujeme parametry - pridani novych
	parametry($delete,$N___hidden,$N___poradi,$N___parametry,$N___jednotky,$akce='insert',$id);
	
	
	
	
	$_SESSION['alert_js'] = "Záznam uložen";
	
	$back = $_SERVER['HTTP_REFERER'];
	Header("Location: ".$back);
	exit;

}
// *****************************************************************************
// ulozeni zanamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// upravujeme sablonu prod. listu
// *****************************************************************************
if($_GET['a'] == 'edit' && !empty($_GET['id'])) {

	$form_data['id'] = $_GET['id'];
	
	$nadpis = 'Upravit vzor produktového listu';
	
	$data = form_karta($form_data,$dct);
	
	$data = $addRecord.$data;

}
// *****************************************************************************
// *****************************************************************************









// *****************************************************************************
// nastavujeme parametry na produktovem listu produktu
// *****************************************************************************
if(!empty($_GET['Pid']) || isset($_POST['vzor'])) {

	//list($form_data['ListID'],$form_data['ProdID']) = explode ('-', $_GET['target']);
	
	
	
	if(isset($_POST['vzor'])) { // prenastavujeme vzor produkt. listu
	
		$ProdID = $_POST['Pid'];
		
		// id  id_karta  id_produkt
		$query = "SELECT id FROM ".T_PARAMETRY4." WHERE id_produkt = ".$ProdID;
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		@$PxK = mysql_result($v, 0, 0);
		
		// echo('xxxxxxxxxxxxxx '.$PxK.' xxxxxxxxxxxxxx');exit;
		
		if($PxK) {
		
			deleteSymboly("SELECT img FROM ".T_PARAMETRY3." WHERE id_kp = $PxK AND img != ''");
			
			
			// id_parametr  hodnota  id_kp
			$query = "DELETE FROM ".T_PARAMETRY3." 
			WHERE id_kp = $PxK";// AND ".SQL_C_LANG."
			my_DB_QUERY($query,__LINE__,__FILE__);
			my_OPTIMIZE_TABLE(T_PARAMETRY3);
			
		
			// id  id_produkt  id_karta
			$query = "UPDATE ".T_PARAMETRY4." SET id_karta = ".$_POST['vzor']." WHERE id_produkt = ".$ProdID;
			my_DB_QUERY($query,__LINE__,__FILE__);
		
		} else {
		
			// id  id_produkt  id_karta
			$query = "INSERT INTO ".T_PARAMETRY4." VALUES ('',".$ProdID.",".$_POST['vzor'].")";
			my_DB_QUERY($query,__LINE__,__FILE__);
		
		}
		
		
		
		if($_POST['vzor'] == 0) { // rusime produktovy list vyrobku
		
			// id  id_produkt  id_karta
			$query = "DELETE FROM ".T_PARAMETRY4." WHERE id_produkt = $ProdID LIMIT 1";
			my_DB_QUERY($query,__LINE__,__FILE__);
			my_OPTIMIZE_TABLE(T_PARAMETRY4);
		
		}
		
		
		//$_SESSION['alert_js'] = "";
		
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	}
	
		
	
	
	$form_data['ProdID'] = $_GET['Pid'];
	
	
	
	
	// id id_cat name img text hidden akce cena dph id_vyrobce
	$query = "SELECT name FROM ".T_GOODS." 
	WHERE id = ".$form_data['ProdID']." AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$Pname = $z['name'];
	
	}
	
	
	$nadpis = ''.$Pname.'<br />produktový list';
	
	$data = form_ProdList($form_data,$dct);

}
// *****************************************************************************
// *****************************************************************************









// *****************************************************************************
// pridani zaznamu (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = "Přidat vzor produktového listu";
  
  $form_data=null;
  	
	$data = form_karta($form_data,$dct);

}
// *****************************************************************************
// pridani zaznamu (form)
// *****************************************************************************








// T_PARAMETRY1
// id  nazev  lang  pozn

// T_PARAMETRY2
// id  id_karta  nazev  jednotka  poradi

// T_PARAMETRY3
// id_parametr  hodnota  id_kp

// T_PARAMETRY4
// id  id_karta  id_produkt

// *****************************************************************************
// odstranit zaznam
// *****************************************************************************
if(!empty($_GET['deleteT'])) {

	// id  id_karta  id_produkt
	$query = "SELECT id FROM ".T_PARAMETRY4." 
	WHERE id_karta = ".$_GET['deleteT']."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		// hodnoty parametru pro produkt
		deleteSymboly("SELECT img FROM ".T_PARAMETRY3." WHERE id_kp = ".$z['id']." AND img != ''");
		
		$q = "DELETE FROM ".T_PARAMETRY3." 
		WHERE id_kp = ".$z['id'];// AND ".SQL_C_LANG."
		my_DB_QUERY($q,__LINE__,__FILE__);
		my_OPTIMIZE_TABLE(T_PARAMETRY3);
	
	}
	
	
	
	// id_parametr  hodnota  id_kp
	$query = "DELETE FROM ".T_PARAMETRY1." WHERE id = ".$_GET['deleteT']."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	my_OPTIMIZE_TABLE(T_PARAMETRY1);
	
	
	$query = "DELETE FROM ".T_PARAMETRY2." WHERE id_karta = ".$_GET['deleteT']."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	my_OPTIMIZE_TABLE(T_PARAMETRY2);
	
	
	$_SESSION['alert_js'] = "Záznam odstraněn";
	
	Header("Location: ".MAIN_LINK."&f=products_parameters&a=list");
	exit;

}
// *****************************************************************************
// odstranit zaznam
// *****************************************************************************
?>
