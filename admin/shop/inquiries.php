<?php

function date_to_timestamp($datum,$alert) {

	// prevede datum z formatu DD.MM. RRRR na time()
	// mezery mezi DD, MM, RRRR mohou byt a nemusi
	
	// vyhazeme vsechny mezery z datumu
	$trans = array (" " => "");
	$datum = strtr($datum, $trans);
	
	
	list($d,$m,$r) = explode (".", $datum);
	
	if(!empty($d) && !empty($m) && !empty($r)) {
	
		$datum = mktime (0,0,0,$m, $d, $r);
		
		return $datum;
	
	}
	else {
	
		$trans = array ("\\n" => "\n");
		$_SESSION['alert'] = strtr($alert, $trans);
		
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	}

}





function form($form_data,$answers,$dct) {

	if(!empty($answers)) {
	
		natcasesort($answers);
		reset($answers);
		while ($p = each($answers)) {
		
			$id_ans = $p['key']; //nazev promenne ($nazev)
			
			list($pos,$ans) = explode ("###", $p["value"]);
			
			$form_ans .= "
			<tr>
				<td>
					<input type=\"text\" name=\"ans[$id_ans]\" value=\"$ans\" size=\"74\" class=\"f10\">
					".ico_delete(MAIN_LINK."&f=inquiries&delete_ans=$id_ans",$dct['inq_odp_smazat'],"onclick=\"return del2()\"")."
				</td>
			</tr>";
		
		}
	
	}
	
	
	// pole pro pridani nove odpovedi
	// pokud editujeme anketu, zobrazime jednou
	// pokud zadavame novou anketu, zobrazime vicekrat
	$new_ans = "
	<tr>
		<td><input type=\"text\" name=\"n_ans[]\" value=\"\" size=\"74\" class=\"f10\"></td>
	</tr>";
	
	
	// nazvy sloupcu pro odpovedi
	$head_new_ans = "
	<tr>
		<td class=\"f10\">&nbsp;<!--<b>".$dct['inq_varianta_odpovedi']."</b>--></td>
	</tr>";
	
	
	if(empty($form_data['id'])) { // pridavame anketu
	
			for($i = 1; $i <= 5; $i++) { // prednastavime pole pro odpovedi
			
				$form_new_ans .= $new_ans;
			
			}
			
			$form_new_ans = $head_new_ans.$form_new_ans;
	
	}
	else { // upravujeme anketu
	
		$form_new_ans .= $new_ans;
		
		$form_new_ans = $form_new_ans.$head_new_ans;
		
		// odstrani zaznam (anketu vcetne odpovedi)
		$deletebutton = DELETE_BUTTON;
		
		// vynuluje hlasovani
		$resetbutton = button("button",$dct['inq_vynulovat_hlasy'],
									"class=\"butt_red\" onclick=\"return res()\"");
	
	}
	
	
	
	
	$form = "
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.name.value == \"\") { alert(\"".$dct['inq_js_nazev_otazky']."\"); form1.name.focus(); return false; }
		else return true;
	
	}
	
	
	function del() {
	
		if (confirm(\"".$dct['opravdu_odstranit']."\"))
			{ location = \"".MAIN_LINK."&f=inquiries&delete=".$form_data['id']."\"; }
	
	}
	
	
	
	function del2() {
	
		if (!confirm(\"".$dct['opravdu_odstranit']."\")) {
			return false;
		}
	
	}
	
	
	function res() {
	
		if (confirm(\"".$dct['inq_opravdu_vynulovat']."\"))
			{ location = \"".MAIN_LINK."&f=inquiries&reset=".$form_data['id']."\"; }
	
	}
	// -->
	</SCRIPT>
	
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	
	<tr>
		<td>".$dct['inq_zneni_otazky']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"name\" value=\"".$form_data['name']."\" size=\"48\" class=\"f10\">
		</td>
	</tr>
	
	
	
	<tr>
		<td>".$dct['inq_datum_spusteni']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"start\" value=\"".$form_data['start']."\" size=\"10\" class=\"f10\"> 
			<span class=\"f10i\">".$dct['inq_datum_pozn']."</span>
		</td>
	</tr>
	
	
	
	<tr>
		<td>".$dct['inq_datum_ukonceni']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"end\" value=\"".$form_data['end']."\" size=\"10\" class=\"f10\"> 
			<span class=\"f10i\">".$dct['inq_datum_pozn']."</span>
		</td>
	</tr>
	
	
	
	<tr>
		<td>".$dct['inq_aktivni']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"checkbox\" name=\"active\" value=\"y\" ".$form_data['active']."> 
			<span class=\"f10i\">".$dct['inq_aktivni_pozn']."</span>
		</td>
	</tr>
	
	
	
	<tr>
		<td colspan=\"3\">&nbsp;</td>
	</tr>
	
	</table>
	
	
	<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	<tr>
		<td>".$dct['inq_pridat_odp']."<b></b></td>
	</tr>
	
	
	$form_new_ans
	
	$form_ans
	
	
	<tr>
		<td><!-- colspan=\"2\"-->
		
			<br><br>
			
			".SAVE_BUTTON."
			
			$deletebutton
			
			$resetbutton
		
		</td>
	</tr>
	
	</table>
	
	</form>";
	
	return $form;

}





// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST['name'])) {

	// datum spusteni
	if(!empty($_POST['start']))
		$start = date_to_timestamp($_POST['start'],$dct['inq_datstart']);
	else $start = 0;
	
	
	// datum ukonceni
	if(!empty($_POST['end']))
		$end = date_to_timestamp($_POST['end'],$dct['inq_datend']);
	else $end = 0;
	
	
	// aktivni
	if(empty($_POST['active'])) $active = 0;
	else {
	
		$active = 1;
		
		$query = "UPDATE ".T_INQUIRIES_SHOP." SET active = 0 WHERE active = 1 AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	
	
	
	// editace zaznamu
	if(!empty($_POST['id'])) {
	
		$id = $_POST['id'];
		
		$query = "UPDATE ".T_INQUIRIES_SHOP." SET name = '".trim($_POST['name'])."', 
		start = $start, end = $end, active = $active 
		WHERE id = $id AND ".SQL_C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		
		// aktualizace odpovedi
		if($_POST['ans']) {//!empty()
		
			reset($_POST['ans']);
			while ($p = each($_POST['ans'])) {
			
				$id_a = $p['key'];
				$txt = trim($p['value']);
				$pos_a = $_POST['pos'][$id_a];
				
				if(empty($pos_a)) $pos_a = 0;
				
				if(!empty($txt)) {
				
					// id answer votes id_inquiry position
					$query = "UPDATE ".T_INQUIRIES_ANS_SHOP." SET answer = '$txt', position = $pos_a WHERE id = $id_a";
					my_DB_QUERY($query,__LINE__,__FILE__);
				
				}
				else {
				
					// id answer votes id_inquiry position
					$query = "DELETE FROM ".T_INQUIRIES_ANS_SHOP." WHERE id = $id_a";
					my_DB_QUERY($query,__LINE__,__FILE__);
					my_OPTIMIZE_TABLE(T_INQUIRIES_ANS_SHOP);
				
				}
			
			}
		
		}
				
		$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
		
		// $back = MAIN_LINK."&f=inquiries&a=edit&id=$id";
		$back = $_SERVER['HTTP_REFERER'];
	
	}
	else { // novy zaznam
	
		// id name start end active
		$query = "INSERT INTO ".T_INQUIRIES_SHOP." 
		VALUES('','".trim($_POST['name'])."',$start,$end,$active,'".C_LANG."')";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$id = mysql_result($v, 0, 0);
		
		$_SESSION['alert_js'] = $dct['zaznam_ulozen'];
		
		$back = MAIN_LINK."&f=inquiries&a=list"; // nasmerovat na editaci otazek k nove ankete
	
	}
	
	
	
	
	// nova odpoved k teto ankete
	if(!empty($_POST['n_ans'])) {
	
		reset($_POST['n_ans']);
		while ($p = each($_POST['n_ans'])) {
		
			$k = $p['key'];
			$txt = trim($p['value']);
			$pos_a = $_POST['n_pos'][$k];
			
			if(empty($pos_a)) $pos_a = 0;
			
			if(!empty($txt)) {
			
				// id answer votes id_inquiry position
				$query = "INSERT INTO ".T_INQUIRIES_ANS_SHOP." VALUES('','$txt',0,$id,$pos_a)";
				my_DB_QUERY($query,__LINE__,__FILE__);
			
			}
		
		}
	
	}
	
	
	
	Header("Location: ".$back);
	exit;
}
// *****************************************************************************
// ulozeni zanamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// seznam anket
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_seznam_anket'];
	
	$query = "SELECT * FROM ".T_INQUIRIES_SHOP." WHERE ".SQL_C_LANG." ORDER BY active DESC, name";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$name = $z['name'];
		$start = $z['start'];
		$end = $z['end'];
		
		
		
		if($start > 0) $start = date("d.m.Y",$start);
		else $start = "-";
		
		
		if($end > 0 && time() > $end) $end_style = " style=\"color: #FF0000;\"";
		else $end_style = "";
		
		if($end > 0) $end = date("d.m.Y",$end);
		else $end = "-";
		
		
		//<span id=\"alert\">".$dct['ano']."</span>
		if($z['active'] == 1) $active = "<img src=\"icons/ico_yes.gif\" border=\"0\" height=\"15\" width=\"15\">";
		else $active = "";
		
		
		
		$data .= "
		<tr ".TABLE_ROW.">
			<td class=\"td2\">$active</td>
			<td class=\"td1\">$name</td>
			<td class=\"td2\">$start</td>
			<td class=\"td2\" $end_style>$end</td>
			<td class=\"td2\">
				".ico_edit($ei_link="".MAIN_LINK."&f=inquiries&a=edit&id=$id",$ei_text=$dct['inq_upravit_anketu'])."
			</td>
		</tr>";//<input type=\"checkbox\" name=\"active\" value=\"y\" $active>
	
	}
	
	if(!empty($data)) $data = "
	
	".SEARCH_PANEL."
	
	<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
	
		<tr>
			<td class=\"td3\">".$dct['inq_tbl_aktivni']."</td>
			<td class=\"td3\">".$dct['inq_tbl_otazka']."</td>
			<td class=\"td3\">".$dct['inq_tbl_spustit']."</td>
			<td class=\"td3\">".$dct['inq_tbl_ukoncit']."</td>
			<td class=\"td3\">&nbsp;</td>
		</tr>
		
		$data
	
	</table>";
	else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	
/*
// *****************************************************************************
// vystup na web ***************************************************************
// *****************************************************************************
if(!empty($_POST['vote'])) {

	// kontrola a zamezeni vicenasobneho hlasovani za 24 hodin???
	
	$query = "UPDATE ".T_INQUIRIES_ANS_SHOP." SET votes = votes+1 WHERE id = ".$_POST['vote'];
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}




$today = time(); // dnesni time

// id name start end active
$query = "SELECT id, name FROM ".T_INQUIRIES_SHOP." 
WHERE active = 1 
AND (start = 0 or start < $today) 
AND (end = 0 or end > $today) 
AND ".SQL_C_LANG."";
$v = my_DB_QUERY($query,__LINE__,__FILE__);

while ($z = mysql_fetch_array($v)) {

	$idA = $z['id'];
	$name = $z['name'];

}



if(!empty($idA)) {

		$query = "SELECT SUM(votes), MAX(votes) FROM ".T_INQUIRIES_ANS_SHOP." WHERE id_inquiry = $idA";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$sum = mysql_result($v, 0, 0); // soucet hlasu
		$max = mysql_result($v, 0, 1); // nejvyssi hlasy u odpovedi pro vypocet max. delky grafu
		
		$procento = $sum/100;
		
		$max_graf = 100; // max. delka sloupce grafu v px, 
		// muze byt pouzito take jako sirka tabulky s anketou
		
		$answers = "<b>$name</b><br /><br />";
		
		// id  answer  votes  id_inquiry  position
		$query = "SELECT * FROM ".T_INQUIRIES_ANS_SHOP." WHERE id_inquiry = $idA ORDER BY votes desc, answer";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		while ($z = mysql_fetch_array($v)) {
		
			$id_ans = $z['id'];
			$answer = $z['answer'];
			$votes = $z['votes'];
			$ans_pos = $z['position'];
			
			
			if($procento > 0) {
			
				$proc = round($votes / $procento);//round($votes / $procento, 1)
				$d = round(($max_graf * $proc)/100);
			
			}
			else {
			
				$proc = $procento;
				$d = 1;
			
			}
			
			
			
			$answers .= "
			$answer <nobr>$proc% ($votes)</nobr><br />
			<input type=\"radio\" name=\"vote\" value=\"$id_ans\">
			<img src=\"img/g01.gif\" height=\"10\" width=\"$d\" border=\"0\"><br />";
		
		}
		
		
		$answers .= "";
		
		
		
		$data .= "<br /><br /><br /><br />
							
							<div style=\"width: 200px;\">
							
								<form action=\"\" method=\"post\" class=\"f10\">
								
								<input type=\"hidden\" name=\"id_inquiry\" value=\"$idA\">
								
								$answers
								
								<br />
								
								<input type=\"submit\" value=\"hlasovat\" class=\"f10\">
								
								<br /><br />hlasujících: $sum
								
								</div>
							
							</form>";

}
// *****************************************************************************
// vystup na web ***************************************************************
// *****************************************************************************
*/
	

}
// *****************************************************************************
// seznam anket
// *****************************************************************************










// *****************************************************************************
// editace ankety (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = $dct['inq_upravit_anketu'];
	
	$query = "SELECT * FROM ".T_INQUIRIES_SHOP." WHERE id = ".$_GET['id']." AND ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$form_data['id'] = $z['id'];
		$form_data['name'] = $z['name'];
		
		
		
		if($z['start'] > 0) $form_data['start'] = date("d.m.Y",$z['start']);
		else $form_data['start'] = "";
		
		
		if($z['end'] > 0) $form_data['end'] = date("d.m.Y",$z['end']);
		else $form_data['end'] = "";
		
		
		if($z['active'] == 1) $form_data['active'] = "checked";
		else $form_data['active'] = "";
	
	}
	
	
	
	// id  answer  votes  id_inquiry  position
	$query = "SELECT * FROM ".T_INQUIRIES_ANS_SHOP." WHERE id_inquiry = ".$_GET['id']."
	ORDER BY position, answer";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$id_ans = $z['id'];
		$answer = $z['answer'];
		$ans_pos = $z['position'];
		
		$answers[$id_ans] = "$ans_pos###$answer";
	
	}
	
	if(empty($answers))
		$_SESSION['alert'] = $dct['inq_odpovedi'];
	
	
	
	$data = form($form_data,$answers,$dct);
	
	
	
	// vyhodnoceni ***************************************************************
	$query = "SELECT SUM(votes), MAX(votes) 
	FROM ".T_INQUIRIES_ANS_SHOP." WHERE id_inquiry = ".$form_data['id']."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$sum = mysql_result($v, 0, 0); // soucet hlasu
	$max = mysql_result($v, 0, 1); // nejvyssi hlasy u odpovedi pro vypocet max. delky grafu
	
	
	$procento = $sum/100;
	$max_graf = 400; // max. delka sloupce grafu v px
	
	
	$answers = "".$dct['inq_vyhodnoceni'].":<br /><br />
	".$dct['inq_hlasujicich'].": $sum<br /><br /><br />";
	
	
	
	// id  answer  votes  id_inquiry  position
	$query = "SELECT answer, votes FROM ".T_INQUIRIES_ANS_SHOP." WHERE id_inquiry = ".$form_data['id']." 
	ORDER BY votes desc, answer";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$answer = $z['answer'];
		$votes = $z['votes'];
		
		if($procento > 0) {
		
			$proc = round($votes / $procento, 2);//round($votes / $procento)
			$d = round(($max_graf * $proc)/100);
		
		}
		else {
		
			$proc = $procento;
			$d = 1;
		
		}
		
		
		if($proc > 0)
		
			$img = "<img src=\"".APP."/img/graf01.gif\" height=\"7\" width=\"$d\" border=\"0\"> ";
		
		else
		
			$img = "";
		
		
		
		$p2 = $p2 + $proc;
		
		$answers .= "
		$answer <nobr style=\"color: #cc0000; font-size: 10px; \">$proc% ($votes)</nobr><br />
		$img
		<br />";
	
	}
	
	
	$answers .= "";
	
	
	
	$data .= "<br /><br /><br /><br />
						
						<div style=\"width: ".$max_graf."px;\">
							
							$answers
							
						</div>";
	
	// vyhodnoceni ***************************************************************

}
// *****************************************************************************
// editace ankety (form)
// *****************************************************************************






// *****************************************************************************
// pridani ankety (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = $dct['mn_pridat_anketu'];
	
	$data = form($form_data,$answers,$dct);

}
// *****************************************************************************
// pridani ankety (form)
// *****************************************************************************






// *****************************************************************************
// odstranit anketu
// *****************************************************************************
if(!empty($_GET['delete'])) {

	$query = "DELETE FROM ".T_INQUIRIES_SHOP." WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG."";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_INQUIRIES_SHOP);
	
	
	$query = "DELETE FROM ".T_INQUIRIES_ANS_SHOP." WHERE id_inquiry = ".$_GET['delete'];
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_INQUIRIES_ANS_SHOP);
	
	
	$_SESSION['alert_js'] = $dct['zaznam_odstranen'];
	
	Header("Location: ".MAIN_LINK."&f=inquiries&a=list");
	exit;

}
// *****************************************************************************
// odstranit anketu
// *****************************************************************************






// *****************************************************************************
// vynulovat hlasovani
// *****************************************************************************
if(!empty($_GET['reset'])) {

	$query = "UPDATE ".T_INQUIRIES_ANS_SHOP." SET votes = 0 WHERE id_inquiry = ".$_GET['reset'];
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	$_SESSION['alert_js'] = $dct['inq_vynulovano'];
	
	Header("Location: ".MAIN_LINK."&f=inquiries&a=edit&id=".$_GET['reset']);
	exit;

}
// *****************************************************************************
// vynulovat hlasovani
// *****************************************************************************






// *****************************************************************************
// odstranit odpoved ankety
// *****************************************************************************
if(!empty($_GET['delete_ans'])) {

	$query = "DELETE FROM ".T_INQUIRIES_ANS_SHOP." WHERE id = ".$_GET['delete_ans'];
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_INQUIRIES_ANS_SHOP);
	
	$_SESSION['alert_js'] = $dct['zaznam_odstranen'];
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// odstranit odpoved ankety
// *****************************************************************************
?>
