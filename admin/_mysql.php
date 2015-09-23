<?php

if(!isset($_SESSION)) session_start();

// z parametru GET odstranime HTML tagy
if(isset($_GET) AND !empty($_GET))
{
  foreach($_GET as $key => $value)
  {
    if(!is_array($value))
    { // pouze pokud neni parametr pole
      $_GET[$key] = strip_tags($value);
    }
  }
}


//$arrayStaty=array(1 => 'Česká republika', 2 => 'Slovensko', 3 => 'Polsko', 4 => 'Maďarsko'); //asociativni pole pro selectbox se statama
$arrayStaty=array(1 => 'Česká republika', 2 => 'Slovensko', 3 => 'Magyar/Hungary'); //asociativni pole pro selectbox se statama
define('STATY',serialize($arrayStaty));

define('SHOWING_CATEGORIES',false);   //pokud true, staci kategorii poopis a nadpis aby se vypsala




if (empty($_SESSION['C_LANG'])) $_SESSION['C_LANG'] = 1;


if (!empty($_GET['C_lang'])){
	define('C_LANG', $_GET['C_lang']);
}else {
	define('C_LANG', $_SESSION['C_LANG']);
}

define('SQL_C_LANG', "lang = ".C_LANG.""); // pro sql dotazy



$NAZEV_SHOPX[1] = "Nekvinda-obchod.cz";

$MEJLX[1] = 'nekvinda@nekvinda.cz';

$WEBX[1] = 'www.nekvinda-obchod.cz';


define('NAZEV_SHOP',$NAZEV_SHOPX[C_LANG]);
define('S_MAIL_SHOP',$MEJLX[C_LANG]);

define('PODPIS_DOTAZ','Nekvinda zemědělská technika a.s.');


define('S_FIRMA','Nekvinda - Zemědělská technika a.s.');
define('S_ULICE','Pražská 2133/36');
define('S_PSC','568 02');
define('S_MESTO','Svitavy, Česká republika');
define('S_TEL','+420 461 534 442, +420 461 533 255');
define('S_FAX','+420 461 530 335');
define('S_WEB',$WEBX[C_LANG]);
define('S_ICO','25974246');
define('S_DIC','CZ25974246');











// *****************************************************************************
// osetreni chyb - v ostrem provozu potlacime/presmerujeme vystup s chybovymi hlaskami
// *****************************************************************************
function my_DB_ERROR($query,$error,$line,$file) {

	// $line = time().";".$line.";".$file.";".$error.";".$query;
	// dale muzeme $line ukladat do logu, nebo pri vyskytu zaslat e-mail ...
	
	$DEBUG = 0; //zapni pro zobrazení plne chyby na veřejném webu - nezapomen vypnout
    
	$zprava_full = '
    <strong>CHYBA:</strong><br /><br />
	<strong>RADEK:</strong> '.$line.' v '.$file.'<br /><br />
	<strong>URL:</strong> '.$_SERVER['REQUEST_URI'].'<br /><br />
	<strong>REFERER:</strong> '.$_SERVER['HTTP_REFERER'].'<br /><br />
	<strong>MYSQL DOTAZ:</strong><br /><br />'.
	$query.'<br /><br />
	<strong>MySQL ERROR:</strong><br /><br /> '.$error;
	
	$zprava_mini = '
    MySQL:<br /><br />
	Vyskytla se chyba v chodu aplikace.';

    if($_SERVER['SERVER_NAME'] != 'localhost' ) {
	   $subject = 'ROBOT - '.$_SERVER['SERVER_NAME'].' - Chyba MySql';
	   $zprava = 'Tento email byl odeslán automaticky systémem '.$_SERVER['SERVER_NAME'].', neodpovídejte na něj.<br /><br />'.$zprava;
	   send("error@netaction.cz",$zprava_full,$subject);
	}
	
    if($_SERVER['SERVER_NAME'] == 'localhost' OR $DEBUG == 1) {
       
	   echo $zprava_full;
	}
	else {
	    echo $zprava_mini;
	}

	$er_no = (int)mysql_errno();
    $line = (int)$line;
    $error = mysql_real_escape_string($zprava_full);
    $query = mysql_real_escape_string($query);
	// id 	datum 	line 	file 	er_no 	er_text 	query
	$ins_er = "INSERT INTO ".T_ERRORS." (`line`, `file`,  `er_no` , `er_text` , `query`)
	                                 VALUES ($line,  '$file', '$er_no', '$error', '$query')";

	@mysql_query($ins_er);
}
// *****************************************************************************
// osetreni chyb
// *****************************************************************************







// *****************************************************************************
// optimalizace tabulky - pouzivame pri odstraneni zaznamu
// *****************************************************************************
function my_OPTIMIZE_TABLE($table) {
	
	mysql_query("OPTIMIZE TABLE `$table`");

}
// *****************************************************************************
// optimalizace tabulky
// *****************************************************************************







// *****************************************************************************
// dotazy do DB
// *****************************************************************************
// select
$count = 0;
function my_DB_QUERY($query,$line,$file) {

	// pouziti:
	// $v = my_DB_QUERY($query,__LINE__,__FILE__);
	// while ($z = mysql_fetch_array($v)) {
	// ... atd ...
	
	// $query = "SELECT LAST_INSERT_ID()";
	// $v = my_DB_QUERY($query,__LINE__,__FILE__);
	// $id = mysql_result($v, 0, 0);

	$v = mysql_query($query) or die(my_DB_ERROR($query,mysql_error(),$line,$file));

  // DEBUG
  global $count;
  $count++;
	//echo $count . " " . $query ."<br/>";

	// $_SESSION['queries'] = $_SESSION['queries']."$query\n\n";
	
	return $v;

}
// *****************************************************************************
// dotazy do DB
// *****************************************************************************



if ($_SERVER['SERVER_NAME'] == "localhost") 
{
	$dbname = "nekvinda-obchod";
	$servername = "localhost";
	$username = "root";
	$pass = "";
}
else
{
	$dbname = "nekvinda-obchod";
	$servername = "mysql20.hostingsolutions.cz:3306";
	$username = "info__nekvinda";
	$pass = "obchod842";	
}

if(!isset($query)) $query="";

@$conn = mysql_connect($servername,$username,$pass) 
or die(my_DB_ERROR($query,mysql_error(),__LINE__,__FILE__));


$db = mysql_select_db($dbname) 
or die(my_DB_ERROR($query,mysql_error(),__LINE__,__FILE__));

mysql_query ("SET NAMES 'utf8';");






// *****************************************************************************
// kontrola existence tabulek
// *****************************************************************************
function tbl_exists($table,$q1,$line,$file) {

	if (!$e = mysql_query("SELECT 1 FROM $table LIMIT 0"))
	my_DB_QUERY($q1,$line,$file);

}


define('TBL', "fla_"); // identifikace tabulek pri pouziti jedne DB pro administrace 
// nekolika webu - pridava se pred nazvy vsech tabulek, pro kazdy web je jiny 
// retezec
define('T_ERRORS', TBL."errors"); // ukladani chybovych hlasek db
define('T_FOTO_KATEG', TBL."foto_kateg"); // Kategorie fotogalerie
define('T_INZERENTI', TBL."inzerenti"); // Reklamní banery
define('T_FOTO', TBL."foto"); //
define('T_FOTO_ZBOZI', TBL."shop_zbozi_x_foto"); //
define('T_CATEGORIES', TBL."shop_kategorie"); // kategorie
define('T_AKCE', TBL."shop_akce"); // kategorie
define('T_INQUIRIES_SHOP', TBL."shop_anketa_otazka"); // anketni otazky
define('T_INQUIRIES_ANS_SHOP', TBL."shop_anketa_odpovedi"); // odpovedi a pocty hlasu
define('T_GOODS', TBL."shop_zbozi"); // produkty
define('T_GOODS_X_CATEGORIES', TBL."shop_zbozi_x_kategorie"); // zarazeni zbozi do kategorii
define('T_GOODS_X_AKCE', TBL."shop_zbozi_x_akce"); // zarazeni zbozi do kategorii
define('T_GOODS_PRIBUZNE', TBL."shop_zbozi_pribuzne"); // pribuzne produkty
define('T_PRODS', TBL."shop_vyrobci"); // vyrobci
define('T_DOWNLOAD', TBL."download"); // soubory ke stazeni
define('T_GOODS_X_DOWNLOAD', TBL."shop_zbozi_x_download"); // prirazene soubory k produktum
define('T_ORDERS_PRODUCTS', TBL."shop_objednavky_x_zbozi"); // objednane polozky
define('T_ORDERS_ADDRESS', TBL."shop_objednavky_x_adresy"); // fakturacni a postovni adresy k objednavkam
define('T_CONT_PAGES', TBL."cont_pages"); // obsah stranek
define('T_FOTO_CONT_PAGES', TBL."cont_pages_x_foto"); // obsah stranek
define('T_DEALERS', TBL."dealeri"); // dealeri
define('T_REGIONS', TBL."regiony"); // regiony (napr. pro dealery)
define('T_DEALERS_X_REGIONS', TBL."dealeri_x_regiony"); // regiony (napr. pro dealery)
define('T_NEWS', TBL."novinky"); // regiony (napr. pro dealery)
define('T_PARAMETRY1', TBL."shop_parametry_1"); // karty parametru
define('T_PARAMETRY2', TBL."shop_parametry_2"); // nazvy/jednotky parametru
define('T_PARAMETRY3', TBL."shop_parametry_3"); // parametr/hodnota/karta-produkt
define('T_PARAMETRY4', TBL."shop_parametry_4"); // produkt/karta
define('T_ADRESY_F', TBL."adresy_fakturacni"); // fakturacni adresy
define('T_ADRESY_P', TBL."adresy_postovni"); // postovni adresy
define('T_SEO', TBL."seo_parametry"); // postovni adresy
define('T_CENY', TBL."shop_cena_cat"); //cenové kategorie
define('T_CENY_X_ADRESY', TBL."shop_cena_x_adresa"); //cenové kategorie
define('T_DODACI_LHUTA', TBL."shop_zbozi_dodaci_lhuta"); //dodaci lhuta 

define('T_DOPRAVA_ALTER', TBL."shop_objednavky_x_doprava_alternativni"); //alternativni doprava

define('T_DOPRAVA', TBL."shop_objednavky_x_doprava"); //doprava
define('T_PLATBA', TBL."shop_objednavky_x_platba"); //platba 
define('T_DOPRAVA_X_PLATBA', TBL."shop_objednavky_x_doprava_x_platba"); //spojovacka doprava x platba

define('T_DOTAZY', TBL."shop_dotazy"); //
define('T_INFO_NEWS', TBL."news_info");
define('T_INFO_NEWS_X_EMAIL', TBL."news_info_x_email");
define('T_INFO_IMPORTED', TBL."news_imported");
define('T_INFO_BLACKLISTED', TBL."news_blacklisted");

define('T_MAIL_ADRESAR', TBL."news_adresar"); // adresar emailu
define('T_MAIL_ADRESAR_X_EMAIL', TBL."news_adresar_x_email"); // emaily v adresari
define('T_MAIL_ADRESAR_X_MESSAGE', TBL."news_adresar_x_message"); // adresare kam byla zprava poslana

define('T_COMMENTS', TBL."shop_komentare"); //

define('T_SETTING',TBL.'setting'); //tabulka s nastavením SEO a GA

define('T_SLEVA_KATEGORIE_X_ADRESA',TBL.'sleva_kategorie_x_adresa'); // Sleva na kategorie pro registrované uživatele

define('T_KURZ',TBL.'kurz'); // Kurzy měn.

// Slider
define('T_SLIDER' , TBL.'slider');
define('T_SLIDER_FOTO' , TBL.'slider_foto');
define('IMG_HEADER' , '../UserFiles/slider/');

define('AKTUAL_PAGE' , 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']); // odkaz na aktualni stranku
define('SERVER_NAME' , 'http://'.$_SERVER['SERVER_NAME']);



// *****************************************************************************
// zpracovani cen - verejna i admin. cast
// *****************************************************************************
// pokud jsou vkladane ceny BEZ DPH
function ceny2($cena, $dph, $pocet = 1, $id_vyrobce = NULL, $id_produktu = NULL, $se_slevou = TRUE)
{
	// $cena JE CENA BEZ DPH!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// fce vrati pole s variantami cen pro vypocty a zobrazeni podle nar. zvyklosti

  /* Slevy a navýšení v kategoriích. */
  //unset($_SESSION["cache"]); // Vypnutí cache (pomalejší).
  cache_cat_info();

  $_SESSION["slevy_v_kategoriich"] = $_SESSION["cache"]["cat_info"]["sleva"];
  $_SESSION["navyseni_v_kategoriich"] = $_SESSION["cache"]["cat_info"]["navysit"];

  // Zařazení všech produktů v první nalezené kategorii pro potřeby slev a navášení.
  $_SESSION["produkty_x_kategorie"] = goods_x_cat();
  /* END Slevy a navýšení v kategoriích. */

  if($id_produktu AND $id_produktu > 0)
  { 
    $id_produktu = intval($id_produktu);

    if(isset($_SESSION["produkty_x_kategorie"][$id_produktu]) AND $_SESSION["produkty_x_kategorie"][$id_produktu] > 0)
    {
      $id_cat = $_SESSION["produkty_x_kategorie"][$id_produktu]; // ID kategorie, ve které je produkt zařazen.
    }
  }

  if(isset($id_cat) AND $id_cat > 0 AND isset($_SESSION["slevy_v_kategoriich"][$id_cat]) AND $_SESSION["slevy_v_kategoriich"][$id_cat] > 0)
  { // Sleva na produkt pro všechny (přez kategorii).
    $sleva_vsichni = $_SESSION["slevy_v_kategoriich"][$id_cat]; // Sleva na produkt přez kategorii v %.
  }

  if(isset($id_cat) AND $id_cat > 0 AND isset($_SESSION["navyseni_v_kategoriich"][$id_cat]) AND $_SESSION["navyseni_v_kategoriich"][$id_cat] > 0)
  { // Navýšení ceny produktu přez kategorii v %.
    $navyseni = $_SESSION["navyseni_v_kategoriich"][$id_cat]; 
  }

  if(isset($navyseni) AND $navyseni > 0)
  { // Nejprve provedu navýšení ceny pokud je zadána.
    $cena = $cena + ($cena * ($navyseni / 100));
  }

  if(isset($sleva_vsichni) AND $sleva_vsichni > 0 AND $sleva_vsichni < 100)
  { // Sleva pro všechny
    $cena = $cena - ($cena * ($sleva_vsichni / 100));
  }


  // Sleva pro registrované zákazníky.
  unset($sleva); // Reset slevy.

	if($id_vyrobce)
  { // Sleva na výrobce pro registrované zákazníky.
    $id_vyrobce = intval($id_vyrobce);

	  if(isset($_SESSION['user']['sleva_'.$id_vyrobce]) AND $_SESSION['user']['sleva_'.$id_vyrobce] > 0)
    {
      $sleva[] = $_SESSION['user']['sleva_'.$id_vyrobce];
		}
	}

	if(isset($id_cat) AND $id_cat > 0 AND isset($_SESSION["user"]["sleva_kategorie"]))
  { // Sleva na kategorii pro registrované zákazníky.
	  if(isset($_SESSION["user"]["sleva_kategorie"][$id_cat]) AND $_SESSION["user"]["sleva_kategorie"][$id_cat] > 0)
    {
      $sleva[] = $_SESSION["user"]["sleva_kategorie"][$id_cat];
		}
	}

  // Uplatnění slevy
  if($se_slevou === TRUE AND isset($sleva) AND count($sleva) > 0)
  { // Vybereme slevu na produkt (největší možnou).
    $sleva = max($sleva); // Největší nalezená sleva.

    if($sleva > 0 AND $sleva < 100)
    {
      $cena = $cena - ($cena * ($sleva / 100));
    }
  }

	// bez DPH za 1 ks, neprevedeno do narodniho zobrazeni
	$ceny[1] = $cena;
	// bez DPH za 1 ks, prevedeno do narodniho zobrazeni
	$ceny[10] = number_format($cena,2,","," ");
	
	// bez DPH za vsechny ks, neprevedeno do narodniho zobrazeni
	$ceny[2] = $pocet * $cena;
	// bez DPH za vsechny ks, prevedeno do narodniho zobrazeni
	$ceny[20] = number_format($ceny[2],2,","," ");
	
	// s DPH za 1 ks, neprevedeno do narodniho zobrazeni
	$ceny[3] = ($cena / 100) * (100 + $dph);// round(, 2)
	// s DPH za 1 ks, prevedeno do narodniho zobrazeni
	// $ceny[30] = number_format($ceny[3],2,","," ");
	$ceny[30] = number_format($ceny[3],2,","," ");
	$ceny[31] = number_format($ceny[3],2,","," ");
	
	// s DPH za vsechny ks, neprevedeno do narodniho zobrazeni
	$ceny[4] = $pocet * $ceny[3];// round(, 2)
	// s DPH za vsechny ks, prevedeno do narodniho zobrazeni
	$ceny[40] = number_format($ceny[4],2,","," ");
	
	// vyse DPH za 1 ks, neprevedeno do narodniho zobrazeni
	$ceny[5] = $ceny[3] - $ceny[1];
	// vyse DPH za 1 ks, prevedeno do narodniho zobrazeni
	$ceny[50] = number_format($ceny[5],2,","," ");
	
	// vyse DPH za vsechny ks, neprevedeno do narodniho zobrazeni
	$ceny[6] = $ceny[4] - $ceny[2];
	// vyse DPH za vsechny ks, prevedeno do narodniho zobrazeni
	$ceny[60] = number_format($ceny[6],2,","," ");
		
	
	// DPH prevedeno do narodniho zobrazeni
	$ceny[1000] = number_format($dph,0,","," ")."%";
	
	
	
	// indexy cen, abychom nemuseli prepisovat na vicero mistech - staci tak ucinit zde
	$ceny['K1'] = 1000;
	$ceny['K2'] = 31;
	$ceny['K3'] = 40;
	$ceny['K4'] = 1;
	
	$ceny["ks_bez_dph"] = 10;
  $ceny["celkem_bez_dph"] = 20;
	
	return $ceny;
}


// pokud jsou vkladane ceny S DPH
function ceny($cena,$dph,$pocet,$id_vyrobce=null) {
     
	if($id_vyrobce){       
	   	if(!empty($_SESSION['user']['sleva_'.$id_vyrobce])){
	   		$cena = $cena - ($cena*($_SESSION['user']['sleva_'.$id_vyrobce]/100));
		}
	}
	// $cena JE CENA S DPH!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// fce vrati pole s variantami cen pro vypocty a zobrazeni podle nar. zvyklosti
	
	$cena=round($cena, 2);
	
	// bez DPH za 1 ks, neprevedeno do narodniho zobrazeni
	$ceny[1] = ($cena / (100 + $dph)) * 100;
	// bez DPH za 1 ks, prevedeno do narodniho zobrazeni
	$ceny[10] = number_format($ceny[1],1,","," ").'0';
	
	// bez DPH za vsechny ks, neprevedeno do narodniho zobrazeni
	$ceny[2] = $pocet * $ceny[1];
	// bez DPH za vsechny ks, prevedeno do narodniho zobrazeni
	$ceny[20] = number_format($ceny[2],2,","," ");
	
	// s DPH za 1 ks, neprevedeno do narodniho zobrazeni
	$ceny[3] = $cena;
	// s DPH za 1 ks, prevedeno do narodniho zobrazeni
	// $ceny[30] = number_format($ceny[3],2,","," ");
	$ceny[30] = number_format($ceny[3],0,","," ").',-';
	$ceny[31] = number_format($ceny[3],2,","," ");
	
	// s DPH za vsechny ks, neprevedeno do narodniho zobrazeni
	$ceny[4] = $pocet * $ceny[3];// round(, 2)
	// s DPH za vsechny ks, prevedeno do narodniho zobrazeni
	$ceny[40] = number_format($ceny[4],2,","," ");
	
	// vyse DPH za 1 ks, neprevedeno do narodniho zobrazeni
	$ceny[5] = $ceny[3] - $ceny[1];
	// vyse DPH za 1 ks, prevedeno do narodniho zobrazeni
	$ceny[50] = number_format($ceny[5],2,","," ");
	
	// vyse DPH za vsechny ks, neprevedeno do narodniho zobrazeni
	$ceny[6] = $ceny[4] - $ceny[2];
	// vyse DPH za vsechny ks, prevedeno do narodniho zobrazeni
	$ceny[60] = number_format($ceny[6],2,","," ");
	
	
	
	// DPH prevedeno do narodniho zobrazeni
	$ceny[1000] = number_format($dph,0,","," ")."%";
	
	
	
	// indexy cen, abychom nemuseli prepisovat na vicero mistech - staci tak ucinit zde
	$ceny['K1'] = 1000;
	$ceny['K2'] = 31;
	$ceny['K3'] = 40;
	$ceny['K4'] = 3;
	
	
	
	return $ceny;

}



function ceny_soucty($soucty,$zaklad,$dph,$cenovka) {

	// $soucty je pole s jiz nascitanym zakladem a dph
	// pred zapocetim scitani je treba jej nastavit na 0
	// $zaklad a $dph jsou pricitane hodnoty
	$soucty['zaklad'] = $soucty['zaklad'] + $zaklad;
	$soucty['dph'] = $soucty['dph'] + $dph;
	$soucty['cenovka'] = $soucty['cenovka'] + $cenovka;
	return $soucty;

}


function ceny_total($zaklad,$dph,$cenovka) {

	// formatuje a generuje vysledne soucty pro verejnou i admin. cast
//    	$total['zaklad'] = number_format($zaklad,1,","," ").'0';
//    	$total['dph'] = number_format($dph,1,","," ").'0';
//    	$total['total'] = number_format($zaklad + $dph,1,","," ").'0';
  	$total['zaklad'] = number_format($cenovka - $dph,2,","," ");
	$total['dph'] = number_format($dph,2,","," ");
	$total['total'] = number_format(round($cenovka),2,","," ");
	
	return $total;

}
// *****************************************************************************
// zpracovani cen
// *****************************************************************************










// *****************************************************************************
// datumy
// *****************************************************************************
function timestamp_to_date($timestamp) {

	// prevede datum z time() do formatu DD.MM. RRRR
	
	$datum = date("d.m. Y",$timestamp);
	
	return $datum;

}
// *****************************************************************************
// datumy
// *****************************************************************************

/* funkce odstrani diakritiku */
function diakritika_utf ($text)
{
/* Tabulka pro nahrazeni diakritiky */
$prevodni_tabulka = Array(
  'ä'=>'a',
  'Ä'=>'A',
  'á'=>'a',
  'Á'=>'A',
  'à'=>'a',
  'À'=>'A',
  'ã'=>'a',
  'Ã'=>'A',
  'â'=>'a',
  'Â'=>'A',
  'č'=>'c',
  'Č'=>'C',
  'ć'=>'c',
  'Ć'=>'C',
  'ď'=>'d',
  'Ď'=>'D',
  'ě'=>'e',
  'Ě'=>'E',
  'é'=>'e',
  'É'=>'E',
  'ë'=>'e',
  'Ë'=>'E',
  'è'=>'e',
  'È'=>'E',
  'ê'=>'e',
  'Ê'=>'E',
  'í'=>'i',
  'Í'=>'I',
  'ï'=>'i',
  'Ï'=>'I',
  'ì'=>'i',
  'Ì'=>'I',
  'î'=>'i',
  'Î'=>'I',
  'ľ'=>'l',
  'Ľ'=>'L',
  'ĺ'=>'l',
  'Ĺ'=>'L',
  'ń'=>'n',
  'Ń'=>'N',
  'ň'=>'n',
  'Ň'=>'N',
  'ñ'=>'n',
  'Ñ'=>'N',
  'ó'=>'o',
  'Ó'=>'O',
  'ö'=>'o',
  'Ö'=>'O',
  'ô'=>'o',
  'Ô'=>'O',
  'ò'=>'o',
  'Ò'=>'O',
  'õ'=>'o',
  'Õ'=>'O',
  'ő'=>'o',
  'Ő'=>'O',
  'ř'=>'r',
  'Ř'=>'R',
  'ŕ'=>'r',
  'Ŕ'=>'R',
  'š'=>'s',
  'Š'=>'S',
  'ś'=>'s',
  'Ś'=>'S',
  'ť'=>'t',
  'Ť'=>'T',
  'ú'=>'u',
  'Ú'=>'U',
  'ů'=>'u',
  'Ů'=>'U',
  'ü'=>'u',
  'Ü'=>'U',
  'ù'=>'u',
  'Ù'=>'U',
  'ũ'=>'u',
  'Ũ'=>'U',
  'û'=>'u',
  'Û'=>'U',
  'ý'=>'y',
  'Ý'=>'Y',
  'ž'=>'z',
  'Ž'=>'Z',
  'ź'=>'z',
  'Ź'=>'Z'
);

  $text = strtr($text, $prevodni_tabulka);

  return $text;
}


/**
HTTP požadavek.
@param (string) url.
@return (string) Odpověď.
*/
function send_request($url)
{
  $parsed = parse_url($url);
  $fp = fsockopen($parsed['host'], 80, $errno, $errstr, 5);
  if(!$fp)
  {
    return $errstr . ' (' . $errno . ')';
  }
  else
  {
    $return = '';
    $out = "GET " . $parsed['path'] . "?" . $parsed['query'] . " HTTP/1.1\r\n" .
           "Host: " . $parsed['host'] . "\r\n" .
           "Connection: Close\r\n\r\n";

    fputs($fp, $out);
    while (!feof($fp))
    {
      $return .= fgets($fp, 128);
    }
    fclose($fp);

    $returnParsed = explode("\r\n\r\n", $return);

    return empty($returnParsed[1]) ? '' : $returnParsed[1];
  }
}



/**
Pole s informacemi o nadřazených kategoriích
@param (int) id_cat
@param (array) ($sleva_navysit) - Pomocný parametr, ukládají se do něj všechny předchozí hodnoty.
@return (array) sleva_navysit - print_r($sleva_navysit);
*/
function cat_info($id_cat, $cat_info = NULL)
{
	$query = "
  SELECT id_parent , sleva , navysit, nezobrazovat_ks
  FROM ".T_CATEGORIES."
	WHERE id = '".intval($id_cat)."'
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while($z = mysql_fetch_assoc($v))
  {
    if(!isset($cat_info["sleva"])) { $cat_info["sleva"] = 0; }
    if($cat_info["sleva"] == 0 AND $z["sleva"] > 0) { $cat_info["sleva"] = $z["sleva"]; }

    if(!isset($cat_info["navysit"])) { $cat_info["navysit"] = 0; }
    if($cat_info["navysit"] == 0 AND $z["navysit"] > 0) { $cat_info["navysit"] = $z["navysit"]; }

    if(!isset($cat_info["nezobrazovat_ks"])) { $cat_info["nezobrazovat_ks"] = 0; }
    if($cat_info["nezobrazovat_ks"] == 0 AND $z["nezobrazovat_ks"] > 0) { $cat_info["nezobrazovat_ks"] = $z["nezobrazovat_ks"]; }

		if($z["id_parent"] > 0) { return cat_info($z["id_parent"], $cat_info); }
    else { return $cat_info; }
	}
}


/**
Nakešovacé informace o navýšení a slevách v kategoriích
@return (array) $_SESSION["cache"]["cat_info"]
*/
function cache_cat_info()
{
  if(!isset($_SESSION["cache"]["cat_info"]) OR empty($_SESSION["cache"]["cat_info"]))
  {
    $query = "
    SELECT id
    FROM ".T_CATEGORIES."
    WHERE hidden = 0
    AND ".SQL_C_LANG."
    ";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);

    while($z = mysql_fetch_assoc($v))
    {
      $cat_info = cat_info($z["id"]);

      $_SESSION["cache"]["cat_info"]["sleva"][$z["id"]] = $cat_info["sleva"];
      $_SESSION["cache"]["cat_info"]["navysit"][$z["id"]] = $cat_info["navysit"];
      $_SESSION["cache"]["cat_info"]["nezobrazovat_ks"][$z["id"]] = $cat_info["nezobrazovat_ks"];
    }
  }

  return $_SESSION["cache"]["cat_info"];
}


/**
Zařadím pro účely slev produkt do první nalezené kategorie.
@return (array) PRODUKTY_X_KATEGORIE - id_produktu => id_kategorie
*/
function goods_x_cat()
{
  if(isset($_SESSION["cache"]["produkty_x_kategorie"]) AND !empty($_SESSION["cache"]["produkty_x_kategorie"])) $PRODUKTY_X_KATEGORIE = $_SESSION["cache"]["produkty_x_kategorie"];

  if(!isset($PRODUKTY_X_KATEGORIE) OR empty($PRODUKTY_X_KATEGORIE))
  {
    $query = "
    SELECT GOODS.id AS id, CATEGORIES.id AS id_cat
    FROM ".T_GOODS." AS GOODS
    JOIN ".T_GOODS_X_CATEGORIES." AS GOODS_X_CATEGORIES ON GOODS_X_CATEGORIES.id_good = GOODS.id
    JOIN ".T_CATEGORIES." AS CATEGORIES ON CATEGORIES.id = GOODS_X_CATEGORIES.id_cat
    WHERE GOODS.hidden = 0
    AND CATEGORIES.hidden = 0
    AND GOODS.".SQL_C_LANG."
    GROUP BY GOODS.id
    ";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);

    while($z = mysql_fetch_assoc($v))
    {
      $PRODUKTY_X_KATEGORIE[$z["id"]] = $z["id_cat"];
    }

    if(isset($PRODUKTY_X_KATEGORIE)) $_SESSION["cache"]["produkty_x_kategorie"] = $PRODUKTY_X_KATEGORIE;
  }

  return $PRODUKTY_X_KATEGORIE;
}

/**
@param (string) table_name - Jméno tabulky.
@param (string) column_name - Název sloupce.
@return (array) enum_list - Pole s povolenými hodnotami.
*/
function enum_values($table_name, $column_name)
{
  $query = "
  SELECT COLUMN_TYPE
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = '" . mysql_real_escape_string($table_name) . "'
  AND COLUMN_NAME = '" . mysql_real_escape_string($column_name) . "'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);
  $enum_list = explode(",", str_replace("'", "", substr($z['COLUMN_TYPE'], 5, (strlen($z['COLUMN_TYPE'])-6))));

  return $enum_list;
}

function cislo_db($cislo)
{
  $cislo = (string) $cislo;
  $prevodni_tabulka = Array(' ' => '', ',' => '.');
  $cislo = strtr($cislo, $prevodni_tabulka);
  $cislo = trim($cislo);

  return $cislo;
}

/*
Funkce pro ziskani obrazku k produktu navraci cesty k obrazku produktu v male stredni a originalni velikosti.
Kontroluje i dostupnost fotek.
@param (int) ID - id produktu
@param (string) name - nepovinny parametr - vyjme pouze konkretni fotku. Nazev zadavame i s priponou

return (array) foto - pole s cestamy k obrazkum ve tvaru:
$foto[poradi] = array(id , title , small , middle, original)
return 0 - zadne fotky
*/
function get_product_fotos($ID , $all = FALSE , $name = '' , $limit = '')
{
  $ID = intval($ID);

  if(isset($name) AND !empty($name))
  { // hledame konkretni fotku
    $where_name = "AND name = '".$name."'";
  }
  else
  {
    $where_name = '';
  }

  if(isset($limit) AND !empty($limit) AND $limit > 0)
  {
    $limit = "LIMIT ".intval($limit);
  }
  else
  {
    $limit = "";
  }

  // id 	id_good 	name 	position   title
  $query = "
  SELECT id , name , title , time
  FROM ".T_FOTO_ZBOZI."
  WHERE id_good = '".$ID."'
  ORDER BY position
  ".$limit;

  
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  

  $foto = array();
  $index = 0;

  while($z = mysql_fetch_assoc($v))
  {
    $small_absolut = '';
    $middle_absolut = '';
    $original_absolut = '';

    $small_relative = '';
    $middle_relative = '';
    $original_relative = '';
    
    if(empty($z['name'])) $z['name'] = $ID.".jpg"; //náhrada převodu mezi starým a novým systémem
    if(!empty($z['name']))
    { // kontrola existence zaznamu v DB
      // absolutni cesta
      $small_absolut = IMG_P_S . $z['name'];
      //echo "<br />small_absolut: ".$small_absolut;
      $middle_absolut = IMG_P_M . $z['name'];
      // echo "<br />middle_absolut: ".$middle_absolut;
      $original_absolut = IMG_P_O . $z['name'];
      // echo "<br />original_absolut: ".$original_absolut;

      // relativni cesta
      $small_relative = IMG_P_S_RELATIV . $z['name'];
      // echo "<br />small_relative: ".$small_relative;
      $middle_relative = IMG_P_M_RELATIV . $z['name'];
      // echo "<br />middle_relative: ".$middle_relative;
      $original_relative = IMG_P_O_RELATIV . $z['name'];
      // echo "<br />original_relative: ".$original_relative;

      if($all === TRUE)
      {
        $foto[$index] = array('id' => $z['id'] , 'title' => $z['title'] , 'small' => $small_absolut , 'middle' => $middle_absolut , 'original' => $original_absolut , 'time' => $z['time']);
        $index++;
      }
      else
      {
        if(file_exists($small_relative) AND file_exists($middle_relative) AND file_exists($original_relative))
        { // kontrola existence fotek
          $foto[$index] = array('id' => $z['id'] , 'name' => $z['name'],'title' => $z['title'] , 'small' => $small_absolut , 'middle' => $middle_absolut , 'original' => $original_absolut , 'time' => $z['time']);
          $index++;
        }
      }
    }
  }

  if(empty($foto))
  {
    return 0;
  }

  return $foto;
}

/**
Info hláška po najetí myší na ikonu.
@param (string) text - hláška kterou chceme zobrazit po najetí myší.
@param (string) style - css
@return (string) info img HTML - HTML obrázek s titulkem "text".
*/
function get_info($text , $style = "")
{
  if(!empty($text))
  {
    $text = trim(strip_tags($text));
    return '<img onclick="alert(\''.$text.'\');" style="cursor:pointer; '.$style.'" src="/admin/icons/info_small.png" alt="info" title="'.strip_tags($text).'">';
  }
}
?>
