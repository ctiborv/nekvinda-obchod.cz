<?php









// *****************************************************************************
// odstranit
// *****************************************************************************
if(!empty($_GET['delete'])) {

	list($id,$c_obj) = explode ("|", $_GET['delete']);
	
	
	// odstraneni objednanych produktu
	//T_ORDERS_PRODUCTS - id_obj id_produkt nazev_produkt cena dph ks
	$query = "DELETE FROM ".T_ORDERS_PRODUCTS." 
	WHERE id_obj = $id";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_ORDERS_PRODUCTS);
	
	
	// odstraneni objednavky
	//T_ORDERS_ADDRESS - id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
	$query = "DELETE FROM ".T_ORDERS_ADDRESS." 
	WHERE id = $id AND c_obj = $c_obj";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_ORDERS_ADDRESS);
	
	
	$_SESSION['alert_js'] = "Objednávka byla odstraněna";
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// odstranit
// *****************************************************************************









// *****************************************************************************
// datumy
// *****************************************************************************
function date_to_timestamp($datum) {

	// prevede datum z formatu DD.MM. RRRR na time()
	// mezery mezi DD, MM, RRRR mohou byt a nemusi
	
	// vyhazeme vsechny mezery z datumu
	$trans = array (" " => "");
	$datum = strtr($datum, $trans);
	
	
	//list($datum['d'],$datum['m'],$datum['r']) = explode (".", $datum);
	$datum = explode(".", $datum);
	
	
	if(empty($datum[0]) || empty($datum[1]) || empty($datum[2])) {
	
		$_SESSION['alert'] = "Chybný formát datumu";
		
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	}
	
	
	return $datum;

}
// *****************************************************************************
// datumy
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
	//T_ORDERS_PRODUCTS - id_obj id_produkt nazev_produkt cena dph ks
	//T_ORDERS_ADDRESS - id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
	$query = "SELECT id,f_jmeno, $column AS $column 
	FROM ".T_ORDERS_ADDRESS." $addWhere ";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while($z = mysql_fetch_array($v)) {
	
		$sID = $z['id'];
		$c = $z[$column];
		$nazev = $z['f_jmeno'];
		
		$p = substr_count(strtoL($c), $search); // kolikrat se v sloupci vyraz vyskytuje
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
	// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
	search1($search,"c_obj",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_jmeno",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_adresa",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_psc",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_mesto",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_ico",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_dic",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_mail",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_tel",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_jmeno",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_adresa",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_psc",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_mesto",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"pozn",$found_points,__LINE__,1,$found_names,$addWhere);
	
	
	// rozdelime na slova
	$slovo = split("[[:blank:]]|(,)|(\.)|(:)|(\?)|(!)|(;)|(\")|(\()|(\))|(\[)|(\])", $search);//(-)|(')|(…)|(_)|
	
	
	
	for($y = 0; $y < count($slovo); $y++) {
	
		$bonus = 0;
		
		$sl = strtoL($slovo[$y]);
		
	// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
	search1($search,"c_obj",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_jmeno",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_adresa",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_psc",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_mesto",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_ico",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_dic",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_mail",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"f_tel",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_jmeno",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_adresa",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_psc",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"p_mesto",$found_points,__LINE__,1,$found_names,$addWhere);
	search1($search,"pozn",$found_points,__LINE__,1,$found_names,$addWhere);
	
	}

}








if (isset($_POST['search'])) { //

	$nadpis = "Vyhledávání \"".$_POST['search']."\"";
	
	
	
	$search = trim($_POST['search']);
	
	
	
	// echo "search - $search<br /><br />";
	
	
	// datumy a timestampy *******************************************************
	
	unset($_SESSION['od']);
	unset($_SESSION['do']);
	
	
	if(!empty($_POST['od'])) {
	
		$od = date_to_timestamp($_POST['od']);
		$stamp_od = mktime(0,0,0,$od[1],$od[0],$od[2]);// timestamp pro hledani v DB
		
		//$_SESSION['od'] = date("d.m.Y",$stamp_od);// upravime pro zobrazeni ve vyhledavani
		// echo "$stamp_od - ".date("d.m.Y H:i:s",$stamp_od)."<br /><br />";
		
		$addWhere_od = "c_obj >= $stamp_od";
	
	}
	
	if(!empty($_POST['do'])) {
	
		$do = date_to_timestamp($_POST['do']);
		$stamp_do = mktime(0,0,0,$do[1],$do[0]+1,$do[2]);// timestamp pro hledani v DB
		
		//$_SESSION['do'] = date("d.m.Y",mktime(0,0,0,$do[1],$do[0],$do[2]));// upravime pro zobrazeni ve vyhledavani
		// echo "$stamp_do - ".date("d.m.Y H:i:s",$stamp_do)."<br /><br />";
		
		$addWhere_do = "c_obj <= $stamp_do";
	
	}
	
	
	if(!empty($addWhere_od) && !empty($addWhere_do)) $addWhere = $addWhere_od." AND ".$addWhere_do;
	elseif(empty($addWhere_od) && !empty($addWhere_do)) $addWhere = $addWhere_do;
	elseif(!empty($addWhere_od) && empty($addWhere_do)) $addWhere = $addWhere_od;
	else $addWhere = SQL_C_LANG;
	
	if(!empty($addWhere)) $addWhere = "WHERE $addWhere AND ".SQL_C_LANG." ";
	
	 //echo "addWhere - $addWhere<br /><br />";
	
	
	// datumy a timestampy *******************************************************
	
	
	
	
	$res='';
	
	
	
	
	 //exit;
	
	
	
	if(empty($search) && !empty($addWhere)) {
	
			// nalezene zaznamy
			// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
			$query = "SELECT id, c_obj, time, f_jmeno, f_mesto 
			FROM ".T_ORDERS_ADDRESS." $addWhere ORDER BY id DESC";
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			
			while ($z = mysql_fetch_array($v)) {
			
				$id = $z['id'];
				$c_obj = $z['c_obj'];
				$time = $z['time'];
				$f_jmeno = $z['f_jmeno'];
				$f_mesto = $z['f_mesto'];
				
				$datum = date("d.m. Y",$time);
				$ei_text = "Zobrazit / Vytisknout objednávku";
				
				$res .= "
				<tr ".TABLE_ROW.">
					<td class=\"td1\" width=\"60\">$c_obj</td>
					
					<td class=\"td1\" width=\"70\">$datum</td>
					
					<td class=\"td1\" nowrap>$f_jmeno, $f_mesto</td>
					
					<td width=\"55\" class=\"td2\">
						<a href=\"shop/orders.php?a=print&id=$id|$c_obj\" target=\"_blank\" title=\"$ei_text\" class=\"f10\">
						<img src=\"icons/ico_print.gif\" alt=\"$ei_text\" title=\"$ei_text\" 
						border=\"0\" height=\"15\" width=\"15\"></a>
						".ico_delete(MAIN_LINK."&f=orders&delete=$id|$c_obj",$dct['orders_smazat'],"onclick=\"return del2()\"")."
						<a href=\"index.php?C_lang=1&amp;app=shop&amp;f=orders&amp;a=edit&amp;id=$id\" title=\"Detail objednávky\" class=\"f10\">
						<img src=\"icons/ico_edit.gif\" alt=\"Detail objednávky\" title=\"Detail objednávky\" border=\"0\" height=\"15\" width=\"15\"></a>
						</td>
				</tr>";//".ico_edit(MAIN_LINK."&f=orders&a=print&id=$id|$c_obj","Zobrazit objednávku")."
			
			}
			
			
			if (!empty($res)) {
			
				$data = "
				<SCRIPT LANGUAGE=\"JavaScript\">
				<!--
				function del2() {
				
					if (!confirm(\"".$dct['opravdu_odstranit']."\")) {
						return false;
					}
				
				}
				// -->
				</SCRIPT>
				
				
				".SEARCH_PANEL."
				
				<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
				$res
				</table>";
				
			
			}
	
	}
	else {
	
			$min_poc_znaku = 2;
			
			$t = str_replace(" ", "", $search);
			$poc_znaku = strlen($t);
			
			
			if($poc_znaku > $min_poc_znaku) {
			
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
					reset($found);
					while ($p = each($found)) {
					
						//$ID = $p['key'];
						$ID = $p['value'];
						
						
		// 				if($x >= $sql_od && $x < $sql_do) {
						
						// nalezene zaznamy
						// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
						$query = "SELECT id, c_obj, time, f_jmeno, f_mesto 
						FROM ".T_ORDERS_ADDRESS." where id = $ID ORDER BY id DESC";
						$v = my_DB_QUERY($query,__LINE__,__FILE__);
						
						while ($z = mysql_fetch_array($v)) {
						
							$id = $z['id'];
							$c_obj = $z['c_obj'];
							$time = $z['time'];
							$f_jmeno = $z['f_jmeno'];
							$f_mesto = $z['f_mesto'];
							
							$datum = date("d.m. Y",$time);
							$ei_text = "Zobrazit / Vytisknout objednávku";
							
							$res .= "
							<tr ".TABLE_ROW.">
								<td class=\"td1\" width=\"60\">$c_obj</td>
								
								<td class=\"td1\" width=\"70\">$datum</td>
								
								<td class=\"td1\" nowrap>$f_jmeno, $f_mesto</td>
								
								<td width=\"55\" class=\"td2\">
									<a href=\"shop/orders.php?a=print&id=$id|$c_obj\" target=\"_blank\" title=\"$ei_text\" class=\"f10\">
									<img src=\"icons/ico_print.gif\" alt=\"$ei_text\" title=\"$ei_text\" 
									border=\"0\" height=\"15\" width=\"15\"></a>
									".ico_delete(MAIN_LINK."&f=orders&delete=$id|$c_obj",$dct['orders_smazat'],"onclick=\"return del2()\"")."
									<a href=\"index.php?C_lang=1&amp;app=shop&amp;f=orders&amp;a=edit&amp;id=$id\" title=\"Detail objednávky\" class=\"f10\">
									<img src=\"icons/ico_edit.gif\" alt=\"Detail objednávky\" title=\"Detail objednávky\" border=\"0\" height=\"15\" width=\"15\"></a>									
									</td>
							</tr>";//".ico_edit(MAIN_LINK."&f=orders&a=print&id=$id|$c_obj","Zobrazit objednávku")."
						
						}
						
						
						if (!empty($res)) {
						
							$data = "
							<SCRIPT LANGUAGE=\"JavaScript\">
							<!--
							function del2() {
							
								if (!confirm(\"".$dct['opravdu_odstranit']."\")) {
									return false;
								}
							
							}
							// -->
							</SCRIPT>
							
							
							".SEARCH_PANEL."
							
							<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
							$res
							</table>";
							
						
						}
						
						$x++;
					
					}
				
				}
			
			}
			else $data = "Prosíme upravte hledanou frázi tak, aby obsahovala nejméně 3 znaky.";
	
	}
	
	if(empty($data)) $data = "Hledanému výrazu neodpovídá žádný záznam.";

}
// *****************************************************************************
// vyhledavani
// *****************************************************************************










// *****************************************************************************
// seznam
// *****************************************************************************
if($_GET['a'] == "list" && !isset($_POST['search'])) {

	$nadpis = $dct['mn_orders_list'];
	
	
	// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
	$query = "SELECT id, c_obj, time, f_jmeno, f_mesto 
	FROM ".T_ORDERS_ADDRESS." WHERE ".SQL_C_LANG." ORDER BY id DESC";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$c_obj = $z['c_obj'];
		$time = $z['time'];
		$f_jmeno = $z['f_jmeno'];
		$f_mesto = $z['f_mesto'];
		
		$datum = date("d.m. Y",$time);
		$ei_text = "Zobrazit / Vytisknout objednávku";
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td class=\"td1\" width=\"60\">$c_obj</td>
			
			<td class=\"td1\" width=\"70\">$datum</td>
			
			<td class=\"td1\" nowrap>$f_jmeno, $f_mesto</td>
			
			<td width=\"55\" class=\"td2\">
				<a href=\"shop/orders.php?a=print&C_lang=".$_GET['C_lang']."&id=$id\" target=\"_blank\" title=\"$ei_text\" class=\"f10\">
				<img src=\"icons/ico_print.gif\" alt=\"$ei_text\" title=\"$ei_text\" 
				border=\"0\" height=\"15\" width=\"15\"></a>
				".ico_delete(MAIN_LINK."&f=orders&delete=$id|$c_obj",$dct['orders_smazat'],"onclick=\"return del2()\"")."
				<a href=\"index.php?C_lang=1&app=shop&f=orders&a=edit&id=$id\" title=\"Detail objednávky\" class=\"f10\">
				<img src=\"icons/ico_edit.gif\" alt=\"Detail objednávky\" title=\"Detail objednávky\" border=\"0\" height=\"15\" width=\"15\"></a></td>
		</tr>";//".ico_edit(MAIN_LINK."&f=orders&a=print&id=$id|$c_obj","Zobrazit objednávku")."
	
	}
	
	
	if (!empty($res)) {
	
		$data = "
		<SCRIPT LANGUAGE=\"JavaScript\">
		<!--
		function del2() {
		
			if (!confirm(\"".$dct['opravdu_odstranit']."\")) {
				return false;
			}
		
		}
		// -->
		</SCRIPT>
		
		
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
// objednavka
// *****************************************************************************
if($_GET['a'] == "print") {
	include_once "../_mysql.php";
	
	include_once '../_functions.php';
	
	
		
	if(empty($_SESSION['S_user_id']) || empty($_SESSION['S_user_name'])){
	
		echo "<center><br /><br /><br />Nejste přihlášen!</center>";
		exit;
	
	}
	
	
	    $id=$_GET['id'];
	
		  $staty=unserialize(STATY);
			
			// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
			$query = "SELECT * FROM ".T_ORDERS_ADDRESS." 
			WHERE id = $id LIMIT 0,1";
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			
			while ($z = mysql_fetch_array($v)) {
			
				$c_obj=$z['c_obj'];
				$time=$z['time'];
				$H1 = "Detail objednávky číslo: ".$c_obj;

				$f_jmeno = $z['f_jmeno'];
				$f_adresa = $z['f_adresa'];
				$f_psc = $z['f_psc'];
				$f_mesto = $z['f_mesto'];
				$f_stat = $z['f_stat'];
				$f_ico = $z['f_ico'];
				$f_dic = $z['f_dic'];
				$f_mail = $z['f_mail'];
				$f_tel = $z['f_tel'];
				$p_jmeno = $z['p_jmeno'];
				$p_adresa = $z['p_adresa'];
				$p_psc = $z['p_psc'];
				$p_mesto = $z['p_mesto'];
				$p_stat = $z['p_stat'];
				$pozn = nl2br(stripslashes($z['pozn']));
				
				if(empty($p_jmeno))$p_jmeno=$f_jmeno;
				if(empty($p_adresa))$p_adresa=$f_adresa;
				if(empty($p_psc))$p_psc=$f_psc;
				if(empty($p_mesto))$p_mesto=$f_mesto;
				if(empty($p_stat))$p_stat=$f_stat;
				
				
				if(!empty($f_mail)) $f_mail = "<a href=\"mailto:$f_mail\">$f_mail</a>";
				
				
				$vystaveno = date("d.m.Y H:i:s", $time);
				
				$tbl_params = "width=\"650\" cellpadding=\"0\" cellspacing=\"0\"";
				
				$data = "
						<table $tbl_params>
						
						<tr>
							<td colspan=\"2\" class=\"f13\"><b>OBJEDNÁVKA č. ".$c_obj."</b></td>
						</tr>
						
						<tr>
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<strong>Dodavatel:</strong><br />
								".S_FIRMA."<br />
								".S_ULICE."<br />
								".S_PSC." ".S_MESTO."<br />
								IČO: ".S_ICO."<br />
								DIČ: ".S_DIC."
							</td>
							
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<strong>Fakturační adresa</strong>:<br />
								$f_jmeno<br />
								$f_adresa<br />
								$f_psc $f_mesto / ".$staty[$f_stat]."<br />
								
								IČO: $f_ico, DIČ: $f_dic<br />
								e-mail: $f_mail / tel.: $f_tel
							</td>
						</tr>
						
						<tr>
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<a href=\"http://".S_WEB."\" target=\"_blank\">".S_WEB."</a>, 
								<a href=\"mailto:".S_MAIL_SHOP."\">".S_MAIL_SHOP."</a>
							</td>
							
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<strong>Poštovní adresa</strong>:<br />
								$p_jmeno<br />
								$p_adresa<br />
								$p_psc $p_mesto / ".$staty[$p_stat]."<br />
								<br />
														
								<strong>Datum objednávky</strong>: $vystaveno
							</td>
						</tr>
						
						</table>
						";
			
			}
			
			
			if (!empty($data)) {
		  
				// id_obj id_produkt nazev_produkt cena dph ks 
				$query = "SELECT * FROM ".T_ORDERS_PRODUCTS." 
				WHERE id_obj = $id";
				$v = my_DB_QUERY($query,__LINE__,__FILE__);
				$soucty=array('zaklad'=>0,'dph'=>0,'cenovka'=>0);
				
				$polozky='';
				
				while ($z = mysql_fetch_array($v)) {
					$id_produkt = $z['id_produkt'];
					$nazev_produkt = $z['nazev_produkt'];
					$cena = $z['cena']; 
					$dph = $z['dph'];
					$ks = $z['ks'];
					$kod = $z['kod'];
					
					// generujeme ceny - fce vraci pole s ruznymi tvary cen
					$ceny = ceny2($cena,$dph,$ks); 
										
					$soucty = ceny_soucty($soucty,$ceny[2],$ceny[6],$ceny[2]+$ceny[6]);
					
					$polozky .= "
							<tr>
								<td valign=\"top\">$ks</td>
								<td valign=\"top\" nowrap>$kod&nbsp;</td>
								<td valign=\"top\">$nazev_produkt</td>
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['K1']]."</td>
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['K2']]."</td>
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['K3']]."</td>
							</tr>";
				}
			
				$total = ceny_total($soucty['zaklad'],$soucty['dph'],$soucty['cenovka']);

				$data = "
						$data
						<br /><br />

						<table $tbl_params>
						
						<tr>&nbsp;
							<td width=\"22\"><strong>ks</strong>&nbsp;</td>
							<td><strong>Kód</strong>&nbsp;</td>
							<td><strong>Položka</strong>&nbsp;</td>
							<td align=\"right\"><strong>DPH</strong>&nbsp;</td>
							<td align=\"right\"><strong>Cena/ks</strong>&nbsp;</td>
							<td align=\"right\"><strong>Celkem</strong></td>
						</tr>
						
						
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
						</tr>
						
						
						$polozky
						
						
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
						</tr>
						
						
						<tr>
							<td colspan=\"5\">
								<strong>Základ</strong><br />
								<strong>DPH</strong><br />
								<strong>Celkem s DPH</strong></td>
							<td align=\"right\" nowrap>
								<strong>".$total['zaklad']."</strong><br />
								<strong>".$total['dph']."</strong><br />
								<strong>".$total['total']."</strong></td>
						</tr>
						
						</table>
						
						<br />
						
						<table $tbl_params>
						
						<tr>
							<td>
								<strong>Poznámka k objednávce:</strong><br /><br />
								$pozn</td>
						</tr>
						
						</table>";
			
			}
			
			
			
			if(empty($data)) $data = "<br /><br />".$dct['zadny_zaznam'];
			
			
			echo "
		<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
		<html>
		<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
			<title>OBJEDNÁVKA č. $c_obj</title>
			<style media=\"print\">
				.print_button {visibility: hidden; display: none;}
			</style>
			<STYLE>
				body, a, TD {font-size: 11px; color: #000000; font-family: Verdana, 'Arial CE', 'Helvetica CE', Arial, Helvetica, sans-serif;}
				TD {padding-left: 5px; padding-right: 5px;}
				.f11 {font-size: 11px;}
				.f12 {font-size: 12px;}
				.f13 {font-size: 13px;}
				.box1 {border: 1px solid #000000; padding: 7px;}
				.box2 {padding: 7px;}
				.box3 {border: 2px solid #000000; padding: 7px; font-size: 13px;}
			</STYLE>
			
		</head>
		<body onload=\"window.print()\">
		
			<center>
			
				$data
				
				<br /><br /><br />
				
			
			</center>
		
		</body>
		</html>";
			
			exit;

}
// *****************************************************************************
// objednavka
// *****************************************************************************









if($_GET['a']=="edit"){
			
			$id=$_GET['id'];

		  	$staty=unserialize(STATY);
		
			// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
			$query = "SELECT * FROM ".T_ORDERS_ADDRESS." 
			WHERE id = $id LIMIT 0,1";
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			
			while ($z = mysql_fetch_array($v)) {
			
				$c_obj=$z['c_obj'];
				$time=$z['time'];
				$nadpis = "Detail objednávky číslo: ".$c_obj;

				$f_jmeno = $z['f_jmeno'];
				$f_adresa = $z['f_adresa'];
				$f_psc = $z['f_psc'];
				$f_mesto = $z['f_mesto'];
				$f_stat = $z['f_stat'];
				$f_ico = $z['f_ico'];
				$f_dic = $z['f_dic'];
				$f_mail = $z['f_mail'];
				$f_tel = $z['f_tel'];
				$p_jmeno = $z['p_jmeno'];
				$p_adresa = $z['p_adresa'];
				$p_psc = $z['p_psc'];
				$p_mesto = $z['p_mesto'];
				$p_stat = $z['p_stat'];
				$pozn = nl2br(stripslashes($z['pozn']));
				
				if(empty($p_jmeno))$p_jmeno=$f_jmeno;
				if(empty($p_adresa))$p_adresa=$f_adresa;
				if(empty($p_psc))$p_psc=$f_psc;
				if(empty($p_mesto))$p_mesto=$f_mesto;
				if(empty($p_stat))$p_stat=$f_stat;
				
				
				if(!empty($f_mail)) $f_mail = "<a href=\"mailto:$f_mail\">$f_mail</a>";
				
				
				$vystaveno = date("d.m.Y H:i:s", $time);
				
				$tbl_params = "width=\"650\" cellpadding=\"0\" cellspacing=\"0\"";
				
				$data = " <br /><br /><br /><br /><br />
						========================================================================				
						<br /><br /><br /><br /><br />
						<table $tbl_params>
						
						<tr>
							<td colspan=\"2\" class=\"f13\"><b>OBJEDNÁVKA č. ".$c_obj."</b></td>
						</tr>
						
						<tr>
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<strong>Dodavatel:</strong><br />
								".S_FIRMA."<br />
								".S_ULICE."<br />
								".S_PSC." ".S_MESTO."<br />
								IČO: ".S_ICO."<br />
								DIČ: ".S_DIC."
							</td>
							
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<strong>Fakturační adresa</strong>:<br />
								$f_jmeno<br />
								$f_adresa<br />
								$f_psc $f_mesto / ".$staty[$f_stat]."<br />
								
								IČO: $f_ico, DIČ: $f_dic<br />
								e-mail: $f_mail / tel.: $f_tel
							</td>
						</tr>
						
						<tr>
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<a href=\"http://".S_WEB."\" target=\"_blank\">".S_WEB."</a>, 
								<a href=\"mailto:".S_MAIL_SHOP."\">".S_MAIL_SHOP."</a>
							</td>
							
							<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
								<strong>Poštovní adresa</strong>:<br />
								$p_jmeno<br />
								$p_adresa<br />
								$p_psc $p_mesto / ".$staty[$p_stat]."<br />
								<br />
														
								<strong>Datum objednávky</strong>: $vystaveno
							</td>
						</tr>
						
						</table>
						";
			
			}
			
			
			if (!empty($data)) {
		  
				// id_obj id_produkt nazev_produkt cena dph ks 
				$query = "SELECT * FROM ".T_ORDERS_PRODUCTS." 
				WHERE id_obj = $id";
				$v = my_DB_QUERY($query,__LINE__,__FILE__);
				$soucty=array('zaklad'=>0,'dph'=>0,'cenovka'=>0);
				
				$polozky='';
				
				while ($z = mysql_fetch_array($v)) {
					$id_produkt = $z['id_produkt'];
					$nazev_produkt = $z['nazev_produkt'];
					$cena = $z['cena']; 
					$dph = $z['dph'];
					$ks = $z['ks'];
					$kod = $z['kod'];
					
					// generujeme ceny - fce vraci pole s ruznymi tvary cen
					$ceny = ceny2($cena,$dph,$ks); 

					$soucty = ceny_soucty($soucty,$ceny[2],$ceny[6],$ceny[2]+$ceny[6]);
					
					$polozky .= "
							<tr>
								<td valign=\"top\">$ks</td>
								<td valign=\"top\" nowrap>$kod&nbsp;</td>
								<td valign=\"top\">$nazev_produkt</td>
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['K1']]."</td>
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['ks_bez_dph']]."</td>
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['K3']]."</td>
							</tr>";
				}
			
				$total = ceny_total($soucty['zaklad'],$soucty['dph'],$soucty['cenovka']);

				$data = "
						$data
						<br /><br />

						<table $tbl_params>
						
						<tr>&nbsp;
							<td width=\"22\"><strong>ks</strong>&nbsp;</td>
							<td><strong>Kód</strong>&nbsp;</td>
							<td><strong>Položka</strong>&nbsp;</td>
							<td align=\"right\"><strong>DPH</strong>&nbsp;</td>
							<td align=\"right\"><strong>Cena/ks bez DPH</strong>&nbsp;</td>
							<td align=\"right\"><strong>Celkem s DPH</strong></td>
						</tr>
						
						
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
						</tr>
						
						
						$polozky
						
						
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
							<td align=\"right\">&nbsp;</td>
						</tr>
						
						
						<tr>
							<td colspan=\"5\">
								<strong>Základ</strong><br />
								<strong>DPH</strong><br />
								<strong>Celkem s DPH</strong></td>
							<td align=\"right\" nowrap>
								<strong>".$total['zaklad']."</strong><br />
								<strong>".$total['dph']."</strong><br />
								<strong>".$total['total']."</strong></td>
						</tr>
						
						</table>
						
						<br />
						
						<table $tbl_params>
						
						<tr>
							<td>
								<strong>Poznámka k objednávce:</strong><br /><br />
								$pozn</td>
						</tr>
						
						</table>
						<br /><br /><br /><br /><br />
						========================================================================				
						<br /><br /><br /><br /><br />
						";
			
			}
			
			
			
			if(empty($data)) $data = "<br /><br />".$dct['zadny_zaznam'];

}







?>
