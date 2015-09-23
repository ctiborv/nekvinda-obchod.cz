<?php

// nazev odkazu je tvoren indexem pole

// $mn['Sortiment'] = "'text','link','title','','ikona pred rozbalenim','ikona po rozbaleni'";
// mytree.add(1, 0, 'My node', 'node.html', 'node title', 'mainframe', 'img/musicfolder.gif');

// spolecne - melo by byt ve vsech aplikacich
$mn0[''] = "''";//$mnA[$apps[$_GET['app']]] = "''";
$mn0[$dct['logout_link']] = "'".S_LOGIN."?logout','','','icons/tree/logout.gif',''";

$mn0[$dct['setting_edit']] = "'".MAIN_LINK."&f=setting&a=setting','','','',''";

$mn0[$dct['kurz']] = "'".MAIN_LINK."&f=kurz','','','',''";

// e-shop

$mn0['Akční nabídky'] = "'','','','','','','',''";
    $mn['Akční nabídky']['Seznam nabídek'] = "'".MAIN_LINK."&f=akce&a=list','','','',''";
    $mn['Akční nabídky']['Přidat nabídku'] = "'".MAIN_LINK."&f=akce&a=add','','','',''";

// Modul statistiky.
$mn0['Statistiky'] = "'','','','','','','',''";
  $mn['Statistiky']["Od - Do"] = "'".MAIN_LINK."&f=statistics&a=from-to','','','',''";
  $mn['Statistiky']["Roční"] = "'".MAIN_LINK."&f=statistics&a=year','','','',''";

$mn0[$dct['mn_sortiment']] = "'','','','icons/tree/goods.gif','icons/tree/goods.gif','','','',''";
  	$mn[$dct['mn_sortiment']]['Doprava'] = "'".MAIN_LINK."&f=doprava&a=list','','','',''";
      $mn[$dct['mn_sortiment']]['Kody výrobků'] = "'".MAIN_LINK."&f=products_kody&a=kody','','','',''";
		$mn[$dct['mn_sortiment']][$dct['mn_pridat_produkt']] = "'".MAIN_LINK."&f=products&a=add','','','',''";
		$mn[$dct['mn_sortiment']]['Cenové kategorie'] = "'".MAIN_LINK."&f=cena_cat&a=list','','','',''";
		// oddělení a skupiny
// $mn0[$dct['mn_kategorie_obchodu']] = "'','','','',''";
		$mn[$dct['mn_sortiment']][$dct['mn_seznam_kategorii']] = "'".MAIN_LINK."&f=categories&a=list','','','',''";
// 		$mn[$dct['mn_sortiment']][$dct['mn_pridat_kategorii']] = "'".MAIN_LINK."&f=categories&a=add','','','',''";
// 		$mn[$dct['mn_sortiment']][$dct['mn_presun_zbozi']] = "'".MAIN_LINK."&f=categories&a=move','','','',''";
		
		// objednávky
		$mn[$dct['mn_sortiment']][$dct['mn_orders_list']] = "'".MAIN_LINK."&f=orders&a=list','','','',''";
		
		//".MAIN_LINK."&f=products_variants&a=add&Pid=".$form_data['id']

		// produktove listy
		$mn[$dct['mn_sortiment']][$dct['mn_parameters_list']] = "'".MAIN_LINK."&f=products_parameters&a=list','','','',''";
		
		// adresy - registrovani uzivatele
		$mn[$dct['mn_sortiment']][$dct['mn_customers_list']] = "'".MAIN_LINK."&f=customers&a=list','','','',''";
		
		$mn[$dct['mn_sortiment']]['Nezařazené aktivní'] = "'".MAIN_LINK."&f=products&a=nocategory&hidden=0','','','',''";	
		$mn[$dct['mn_sortiment']]['Nezařazené neaktivní'] = "'".MAIN_LINK."&f=products&a=nocategory&hidden=1','','','',''";
		$mn[$dct['mn_sortiment']]['Položky s vlastní cenou e-shop'] = "'".MAIN_LINK."&f=products&a=cena_eshop','','','',''";
		// adresy - registrovani uzivatele
// 		$mn[$dct['mn_sortiment']][$dct['mn_xml_report']] = "'".MAIN_LINK."&f=xml_report&a=list','','','',''";
		
			
		
		// vygenerujeme pole s kategoriemi
		if(empty($cat_array)) {
		
			$cat_array = array();
			
			categories_array($parent_id=0,$cat_array,$level=0);
		
		}
		
		// mame vytvoreno pole $cat_array s hodnotami kategorii pro dalsi zpracovani 
		// ve tvaru level|pozice|nadrazena kat.|nazev|skryta|jazyk. verze|ID
		// kategorie jdou v presnem poradi tak jak maji, staci jen dosadit hodnoty pro js
		if(!empty($cat_array)) {
		
			$i=0;
			      		
			reset($cat_array);
			
      while ($p = each($cat_array)) {
			
				$n = $p['key'];
				$h = $p['value']; 
				
				$name = "";
				$ttl = "";
				                 	
				     
				list ($level,$position,$par_id,$name,$hidden,$lang,$id) = explode ("|", $h);
				
				
				// zjistime zda nasledujici prvek pole neni podrizenou kategorii 
				// aktualne zpracovavaneho prvku
				if(!empty($cat_array[$i+1])){
					list ($level2,$position2,$par_id2,$name2,$hidden2,$lang2,$id2) = explode ("|", $cat_array[$i+1]);
				}
				
				
				
				// nasleduje indikace skrytych kategorii, vcetne podkategorii 
				// (u podkategorii bez ohledu na skutecne nastaveni)
				// styl pro skryte polozky
				$hidden_style = "style=\"color: #939393;\"";
				
				
				if(empty($h_parent[$par_id]))$h_parent[$par_id]=null;
				
				// zacatek skrytych kategorii (prvni v hierarchii)
				if ($hidden == 1 && $par_id != $h_parent[$par_id]) {
					// $name = "<span $hidden_style>$name</span>";
					// $name = "[S] $name";
					$ttl = $dct['cat_zobrazeni_nepovoleno'];
				}
				// dalsi (vnorene) skryte kategorie
				else if ($par_id == $h_parent[$par_id]) {
					// $name = "<span $hidden_style>$name</span>";
					// $name = "[S] $name";
					$ttl = $dct['cat_zobrazeni_nepovoleno'];
				}
				// neskryte kategorie
				else {
					// $name = $name;
					$ttl = "";
				}
				
				
				$name = lenght_of_string(60,$name); // max pocet znaku, string
				
				// echo "$n - $h<br />";				
				$nadrazene[$id] = $name;

				
				
				if($par_id == 0){ // nejvyssi urovne
					$mn[$dct['mn_sortiment']][$name."-".$id] = "'".MAIN_LINK."&f=products&cat=$id','','','icons/tree/folder.gif','icons/tree/folder.gif','','','',''";
// 				}else if($id == $par_id2){
// 					$mn[$nadrazene[$par_id]."-".$par_id][$name."-".$id] = "'".MAIN_LINK."&f=products&cat=$id','','','icons/tree/folder.gif','icons/tree/folder.gif','','','',''";
				}else{
					$mn[$nadrazene[$par_id]."-".$par_id][$name."-".$id] = "'".MAIN_LINK."&f=products&cat=$id','','','icons/tree/folder.gif','icons/tree/folder.gif','','','',''";
				}
				
				// echo "$n<br />";
				
// 				$old_level = $level;
// 				$old_hidden = $hidden;
				
				$i++;
			
			}
		
		}
		// generovane konec







// oddělení a skupiny
// $mn0[$dct['mn_kategorie_obchodu']] = "'','','','',''";
// 		$mn[$dct['mn_kategorie_obchodu']][$dct['mn_seznam_kategorii']] = "'".MAIN_LINK."&f=categories&a=list','','','',''";
// 		$mn[$dct['mn_kategorie_obchodu']][$dct['mn_pridat_kategorii']] = "'".MAIN_LINK."&f=categories&a=add','','','',''";
// 		$mn[$dct['mn_kategorie_obchodu']][$dct['mn_presun_zbozi']] = "'".MAIN_LINK."&f=categories&a=move','','','',''";


// vyrobci
$mn0[$dct['mn_vyrobci']] = "'','','','',''";
		$mn[$dct['mn_vyrobci']][$dct['mn_seznam_vyrobcu']] = "'".MAIN_LINK."&f=producers&a=list','','','',''";
		$mn[$dct['mn_vyrobci']][$dct['mn_pridat_vyrobce']] = "'".MAIN_LINK."&f=producers&a=add','','','',''";


// soubory ke stazeni
$mn0[$dct['mn_files']] = "'','','','',''";
		$mn[$dct['mn_files']][$dct['mn_seznam_files']] = "'".MAIN_LINK."&f=files&a=list','','','',''";
		$mn[$dct['mn_files']][$dct['mn_pridat_files']] = "'".MAIN_LINK."&f=files&a=add','','','',''";

// ankety
// $mn0[$dct['mn_ankety']] = "'','','','',''";
// 		$mn[$dct['mn_ankety']][$dct['mn_seznam_anket']] = "'".MAIN_LINK."&f=inquiries&a=list','','','',''";
// 		$mn[$dct['mn_ankety']][$dct['mn_pridat_anketu']] = "'".MAIN_LINK."&f=inquiries&a=add','','','',''";

// sprava obsahu
$mn0[$dct['mn_cont']] = "'','','','',''";
		$mn[$dct['mn_cont']][$dct['mn_cont_seznam']] = "'".MAIN_LINK."&f=pages&a=list','','','',''";
		$mn[$dct['mn_cont']][$dct['mn_cont_add']] = "'".MAIN_LINK."&f=pages&a=add','','','',''";
		
// sprava mailu
$mn0['Newsletter'] = "'','','','',''";
		$mn['Newsletter']['Adresáře'] = "'".MAIN_LINK."&f=mail_adresare&a=list','','','',''";
    $mn['Newsletter']['Seznam zpráv'] = "'".MAIN_LINK."&f=mailnews&a=list','','','',''";
		$mn['Newsletter']['Vytvořit novou zprávu'] = "'".MAIN_LINK."&f=mailnews&a=add','','','',''";
		$mn['Newsletter']['Importovat adresy'] = "'".MAIN_LINK."&f=mailnews&a=import','','','',''";		

// dealeri
// $mn0[$dct['mn_deals']] = "'','','','',''";
// 		$mn[$dct['mn_deals']][$dct['mn_deals_seznam']] = "'".MAIN_LINK."&f=dealers&a=list','','','',''";
// 		$mn[$dct['mn_deals']][$dct['mn_deals_add']] = "'".MAIN_LINK."&f=dealers&a=add','','','',''";

// inzerenti
$mn0['Reklamní plocha'] = "'','','','',''";
		$mn['Reklamní plocha']['Seznam reklam'] = "'".MAIN_LINK."&f=inzerenti&a=list','','','',''";
		$mn['Reklamní plocha']['Přidat reklamu'] = "'".MAIN_LINK."&f=inzerenti&a=add','','','',''";

// fotogalerie
$mn0['Fotogalerie'] = "'','','','','','','',''";
  $mn['Fotogalerie']['Seznam fotogalerií'] = "'".MAIN_LINK."&f=foto&a=list','','','',''";
  $mn['Fotogalerie']['Přidat fotogalerií'] = "'".MAIN_LINK."&f=foto&a=add','','','',''";

// Slidery
$mn0['Slidery'] = "'".MAIN_LINK."&f=slider&a=list','','','',''";


// aktuality
/*
$mn0[$dct['mn_news']] = "'','','','',''";
		$mn[$dct['mn_news']][$dct['mn_news_seznam']] = "'".MAIN_LINK."&f=news&a=list','','','',''";
		$mn[$dct['mn_news']][$dct['mn_news_add']] = "'".MAIN_LINK."&f=news&a=add','','','',''";
*/

// statistiky jako samostatna aplikace s vlastnim loginem
// $mn0[$dct['mn_stats']] = "'counter/stat.php?p=graf','','_blank','',''";
// statistiky nacitana do rozhrani administrace
// $mn0[$dct['mn_stats']] = "'index.php?C_lang=".C_LANG."&app=counter&f=stat&a=graf','','','',''";


// objednavky
// $mn0[$dct['mn_objednavky']] = "'','','','icons/tree/orders.gif','icons/tree/orders.gif'";
// 		$mn[$dct['mn_objednavky']][$dct['list']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_objednavky']][$dct['mn_hledani']] = "'".MAIN_LINK."&f=neco','','','icons/tree/search.gif','icons/tree/search.gif'";
// 
// // novinky, kratke zpravy
// $mn0[$dct['mn_aktuality']] = "'','','','icons/tree/news.gif','icons/tree/news.gif'";
// 		$mn[$dct['mn_aktuality']][$dct['list']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_aktuality']][$dct['mn_pridat_aktualitu']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_aktuality']][$dct['mn_hledani']] = "'".MAIN_LINK."&f=neco','','','icons/tree/search.gif','icons/tree/search.gif'";
// 
// // obch. partneri
// $mn0[$dct['mn_odberatele']] = "'','','','icons/tree/partners.gif','icons/tree/partners.gif'";
// 		$mn[$dct['mn_odberatele']][$dct['list']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_odberatele']][$dct['mn_pridat_kontakt']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_odberatele']][$dct['mn_poslat_email']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_odberatele']][$dct['mn_hledani']] = "'".MAIN_LINK."&f=neco','','','icons/tree/search.gif','icons/tree/search.gif'";
// 
// // prodejni mista
// $mn0[$dct['mn_prodejni_mista']] = "'','','','icons/tree/resellers.gif','icons/tree/resellers.gif'";
// 		$mn[$dct['mn_prodejni_mista']][$dct['list']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_prodejni_mista']][$dct['mn_pridat_misto']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_prodejni_mista']][$dct['mn_poslat_email']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_prodejni_mista']][$dct['mn_pridat_mesto']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_prodejni_mista']][$dct['mn_pridat_region']] = "'".MAIN_LINK."&f=neco','','','',''";

// // nastaveni e-Shopu
// $mn0[$dct['mn_nastaveni']] = "'','','','icons/tree/settings.gif','icons/tree/settings.gif'";
// 		$mn[$dct['mn_nastaveni']][$dct['mn_adresa']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_nastaveni']][$dct['mn_kontakty']] = "'".MAIN_LINK."&f=neco','','','',''";
// 		$mn[$dct['mn_nastaveni']][$dct['mn_zobrazeni']] = "'".MAIN_LINK."&f=neco','','','',''";
// 
// $mn0[$dct['mn_help']] = "'".MAIN_LINK."&f=neco','','','icons/tree/help.gif',''";


?>
