<?php

// tento skript je includovan do dalsich skriptu, ktere chceme ochranit pred 
// zneuzitim a jejich funkce jsou podminene pouzitim spravneho jmena a hesla. 
// obsahuje prihlasovaci formular, mechanismus prihlaseni, odhlaseni, 
// automatickeho odhlaseni (2x jisteno - pomoci php a javaskriptu) pri 
// prekroceni povoleneho casu neaktivity, dale fci pro ochranu includovanych 
// skriptu pred samostatnym otevrenim


// pro spravnou fci musi byt nastaveno pripojeni do DB, neobsahuje-li DB potrebne 
// tabulky, budou vytvoreny a bude prednastaveno jmeno a heslo pro prvotni 
// prihlaseni admina

// heslo je ukladano jako md5($pass);

// skript obsahuje FORMULAR PRO EDITACI PRIHLASENEHO UZIVATELE, ten je treba 
// upravit pro potreby aplikace kde je tento skript pouzivan. Formular je umisten 
// az na konci tohoto skriptu, aby nejprve byly provedeny vsechny potrebne 
// kontroly prihlaseneho uzivatele

// ZAPOMENUTE HESLO je odeslano na email uvedeny do pole formu, pokud je tento email 
// nalezen v DB

// je provadena kontrola parametru uzivatele behem session a pri 
// zmene nektereho z nich je uzivatel odhlasen

// sledujeme: 
// S_TIMEOUT = povoleny cas necinnosti uzivatele - po prekroceni je 
// uzivatel odhlasen. Ubihajici cas je indikovan pomoci javaskriptu v $user_data. 

// $S_sess = identifikator session
// $S_ip = IP uzivatele
// $S_pc = nazev pocitace uzivatele
// $S_agent = prohlizec uzivatele
// $S_sig = md5($S_ip."_".$S_pc."_".$S_agent); // zakodujeme
// dale je pri kazdem nacteni stranky generovan nahodny  kod, ktery je porovnavan

// vsechny tyto parametry jsou ukladany do tabulky S_TABLE_LOGGED a porovnavany 
// pri kazdem nacteni skriptu do nejz je tento skript includovan

// prihlas. form vyuziva vlastni css, neni treba zasahovat do css hlidane aplikace
// echo md5('_msie');exit;



// reset ($_SESSION);
// while ($pole = each($_SESSION))	{
// 	$nazev = $pole['key']; //nazev promenne ($nazev)
// 	$hodnota = $pole['value'];
// 	$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
// 	echo "<br>SESSION $nazev = ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
// }
// 
// 
// echo "<br />";
// 
// 
// 
// reset ($_POST);
// while ($pole = each($_POST)) {
// 	$nazev = $pole['key']; //nazev promenne ($nazev)
// 	$hodnota = $pole['value'];
// 	$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
// 	echo "<br>POST $nazev ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
// }
// 
// echo "<br />";
// 
// 
// 
// 
// reset ($_GET);
// while ($pole = each($_GET))	{
// 	$nazev = $pole['key']; //nazev promenne ($nazev)
// 	$hodnota = $pole['value'];
// 	$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
// 	echo "<br>GET $nazev = ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
// }



// *****************************************************************************
// setup
// *****************************************************************************
$hide_S_LANG_PANEL = true; // skryt/zobrazit volbu jazyka administrace
$hide_S_COPYRIGHT = false; // skryt/zobrazit copyright pro prihlasovaci form


define('S_TABLE_ADMINS',"_".TBL."security_administrators"); // nazev tabulky administratoru
define('S_TABLE_LOGGED',"_".TBL."security_logged"); // nazev tabulky prihlaseni
define('S_TIMEOUT',5400); // cas necinnosti

// cesta do administrace (login)
define('S_LOGIN',"http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']); // $_SERVER['SERVER_NAME']



// panel pro vyber jazyk. verze administrace
if($hide_S_LANG_PANEL === false) {

	define('S_LANG_PANEL',"
		<a href=\"".S_LOGIN."?S_lang=cz\" title=\"česky\">česky</a> | 
		<a href=\"".S_LOGIN."?S_lang=en\" title=\"english\">english</a>
		<!-- | 
		<a href=\"".S_LOGIN."?S_lang=de\" title=\"deutsch\">deutsch</a>
		-->
		
		<br /><br />");

}
else define('S_LANG_PANEL',"");



// copyright
if($hide_S_COPYRIGHT === false) {

	define('S_COPYRIGHT','
				<p>
					&copy;2000-' . Date ("Y") . '<br />
					<a href="http://www.netaction.cz"
          title="E-shop &amp; Redakční systém NetAction.cz">E-shop &amp; Redakční systém NetAction.cz</a>
				</p>');

}
else define('S_COPYRIGHT',"");
// *****************************************************************************
// setup
// *****************************************************************************






// *****************************************************************************
// nastaveni jazykove verze administrace, lze pouzit pro jazyky cele administrace 
// ulozit do cookies pro pristi login?
// *****************************************************************************
if (empty($_SESSION['S_lang'])) $_SESSION['S_lang'] = "cz";



if (!empty($_GET['S_lang'])) {
	$_SESSION['S_lang'] = $_GET['S_lang'];
	header("location: ".$_SERVER['HTTP_REFERER']);
	exit;
}

// echo $_SESSION['S_lang'];exit;
// *****************************************************************************
// nastaveni jazykove verze administrace
// *****************************************************************************

// echo S_LANG_PANEL;exit;








// *****************************************************************************
// chyby, texty
// pro univerzalnost pouziti ponechat vyrazy zde, nepresouvat jinam
// *****************************************************************************
$S_m['cz']['logout'] = "Jste odhlášen(a)";
$S_m['en']['logout'] = "Logout successful";
$S_m['cz']['not_user'] = "Uživatel nenalezen";
$S_m['en']['not_user'] = "User not found";
$S_m['cz']['access_denied'] = "Přístup odepřen";
$S_m['en']['access_denied'] = "Access denied";
$S_m['cz']['name'] = "Jméno";
$S_m['en']['name'] = "Name";
$S_m['cz']['password'] = "Heslo";
$S_m['en']['password'] = "Password";
$S_m['cz']['button'] = "Přihlásit";
$S_m['en']['button'] = "  Login  ";
$S_m['cz']['forg_email'] = "Váš e-mail";
$S_m['en']['forg_email'] = "Your e-mail";
$S_m['cz']['forg_pass'] = "Poslat heslo e-mailem";
$S_m['en']['forg_pass'] = "Send password";
$S_m['cz']['forg_button'] = "Zaslat heslo";
$S_m['en']['forg_button'] = "Send password";
$S_m['cz']['forg_send_ok'] = "Heslo bylo odesláno";
$S_m['en']['forg_send_ok'] = "Password sent";
$S_m['cz']['forg_not_email'] = "Uživatel s tímto jménem a e-mailem nenalezen";
$S_m['en']['forg_not_email'] = "User with this username and e-mail not found";
$S_m['cz']['forg_email_error'] = "Chyba při odesílání hesla, zkuste to později.";
$S_m['en']['forg_email_error'] = "Error during send password, try later.";
$S_m['cz']['info'] = "Pro správnou funkci rozhraní je nutné povolit cookies.";
$S_m['en']['info'] = "Enabled cookies required.";
$S_m['cz']['expired'] = "Přístup odepřen";
$S_m['en']['expired'] = "Access denied";
$S_m['cz']['signature'] = "Přístup odepřen";
$S_m['en']['signature'] = "Access denied";
$S_m['cz']['code'] = "Přístup odepřen";
$S_m['en']['code'] = "Access denied";
$S_m['cz']['session'] = "Přístup odepřen";
$S_m['en']['session'] = "Access denied";
$S_m['cz']['userid'] = "";//Přihlaste se do systému Přístup odepřen
$S_m['en']['userid'] = "";//Login Access denied
$S_m['cz']['unknown'] = "Neznámá chyba";
$S_m['en']['unknown'] = "Unknown error";
$S_m['cz']['logout_link'] = "Odhlásit";
$S_m['en']['logout_link'] = "Logout";
$S_m['cz']['edit_admin'] = "Upravit";
$S_m['en']['edit_admin'] = "Edit";
$S_m['cz']['aut_logout'] = "automat. odhlášení za";
$S_m['en']['aut_logout'] = "automat. logout: ";
$S_m['cz']['logout_auto'] = "Překročen čas nečinnosti";
$S_m['en']['logout_auto'] = "Time of inactivity expired";
$S_m['cz']['entered'] = "uživatel";
$S_m['en']['entered'] = "user";
$S_m['cz']['admin_edit'] = "Upravit záznam";
$S_m['en']['admin_edit'] = "Edit record";
$S_m['cz']['zaznam_upraven'] = "Záznam upraven";
$S_m['en']['zaznam_upraven'] = "Changes saved";
$S_m['cz']['login_link'] = "Přihlášení";
$S_m['en']['login_link'] = "Login";

$S_m['cz']['subject'] = "Přihlašovací údaje k administraci serveru";
$S_m['en']['subject'] = "";
$S_m['cz']['prihl_jmeno'] = "Přihl. jméno";
$S_m['en']['prihl_jmeno'] = "";
$S_m['cz']['heslo'] = "Heslo";
$S_m['en']['heslo'] = "";
$S_m['cz']['aut_generated'] = "Tento e-mail byl generován automaticky, neodpovídejte na něj";
$S_m['en']['aut_generated'] = "";
$S_m['cz']['alert_jmeno'] = "Uveďte Jméno";
$S_m['en']['alert_jmeno'] = "";
$S_m['cz']['alert_prihl_jmeno'] = "Uveďte Přihl. jméno";
$S_m['en']['alert_prihl_jmeno'] = "";
$S_m['cz']['alert_email'] = "Uveďte E-mail";
$S_m['en']['alert_email'] = "";
$S_m['cz']['alert_hesla'] = "Nesouhlasí heslo a heslo pro kontrolu";
$S_m['en']['alert_hesla'] = "";

$S_m['cz']['alert_dlouhe_jmeno'] = "Jméno je dlouhé, zkraťte jej na max. 64 znaků";
$S_m['en']['alert_dlouhe_jmeno'] = "";
$S_m['cz']['alert_kr_p_j'] = "Přihl. jméno je krátké, použijte 4 - 16 znaků";
$S_m['en']['alert_kr_p_j'] = "";
$S_m['cz']['alert_dl_p_j'] = "Přihl. jméno je dlouhé, použijte 4 - 16 znaků";
$S_m['en']['alert_dl_p_j'] = "";
$S_m['cz']['alert_email'] = "Uveďte E-mail, je důležitý pro komunikaci s tímto prostředím";
$S_m['en']['alert_email'] = "";
$S_m['cz']['alert_heslo'] = "Uveďte Heslo";
$S_m['en']['alert_heslo'] = "";
$S_m['cz']['alert_heslo2'] = "Uveďte Heslo pro kontrolu";
$S_m['en']['alert_heslo2'] = "";
$S_m['cz']['alert_hesla_nesouhl'] = "Nesouhlasí heslo a heslo pro kontrolu";
$S_m['en']['alert_hesla_nesouhl'] = "";
$S_m['cz']['alert_kr_heslo'] = "Heslo je krátké, použijte min. 4 znaky";
$S_m['en']['alert_kr_heslo'] = "";
$S_m['cz']['alert_kr_heslo2'] = "Heslo pro kontrolu je krátké, použijte min. 4 znaky";
$S_m['en']['alert_kr_heslo2'] = "";
$S_m['cz']['povinne_udaje'] = "povinné údaje";
$S_m['en']['povinne_udaje'] = "";
$S_m['cz']['jmeno'] = "Jméno";
$S_m['en']['jmeno'] = "";
$S_m['cz']['max64'] = "max. 64 znaků";
$S_m['en']['max64'] = "";
$S_m['cz']['4-16'] = "4 - 16 znaků";
$S_m['en']['4-16'] = "";
$S_m['cz']['email'] = "E-mail";
$S_m['en']['email'] = "";
$S_m['cz']['zmena_hesla'] = "Změna hesla (min. 4 znaky)";
$S_m['en']['zmena_hesla'] = "";
$S_m['cz']['heslo_znovu'] = "Heslo znovu";
$S_m['en']['heslo_znovu'] = "";
$S_m['cz']['ulozit_zaznam'] = "Uložit záznam";
$S_m['en']['ulozit_zaznam'] = "";





$_SESSION['S_mess'] = $S_m[$_SESSION['S_lang']];
unset($S_m); // jiz nepotrebujeme, zrusime
// *****************************************************************************
// chyby, texty
// *****************************************************************************





// fce pro generovani hesla
function ranpass($len = 6) {

	$pass = NULL;
	
	for($i = 0; $i < $len; $i++) {
		$char = chr(rand(48,122));
		
		while (!ereg("[a-zA-Z0-9]", $char)) {
			if($char == $lchar) continue;
				$char = chr(rand(48,90));
		}
		
		$pass .= $char;
		$lchar = $char;
	}
	
	return $pass;

}





// *****************************************************************************
// vytvoreni tabulek - v beznem provozu nebude treba
// *****************************************************************************

// definice vytvoreni tabulky administratoru

// ve sloupci rights ukladame ruzna dalsi nastaveni vyuzitelna 
// pro povoleni/zakazani nekterych akci, pristupu k sekcim administrace atd
// v nasem pripade jsou hodnoty ukladany podle vzoru
// 1|0|1|1, kde 1 znaci povoleno, 0 nepovoleno pro definovane akce - viz 
// vlastni kontrola uzivatele dale ve skriptu


/*
$S_query_admins = "CREATE TABLE ".S_TABLE_ADMINS." (
	  id int(10) unsigned NOT NULL auto_increment,
	  user varchar(64) NOT NULL default '',
	  name varchar(16) binary NOT NULL default '',
	  pass varchar(64) binary NOT NULL default '',
	  rights varchar(255) NOT NULL default '',
	  email varchar(255) binary NOT NULL default '',
	  UNIQUE KEY id (id),
	  KEY name (name),
	  KEY pass (pass)
) 	TYPE=MyISAM ;";

// definice vytvoreni tabulky prihlaseni
$S_query_logged = "CREATE TABLE ".S_TABLE_LOGGED." (
		id bigint(20) unsigned NOT NULL auto_increment,
		user bigint(20) unsigned NOT NULL default '0',
		session varchar(255) NOT NULL default '',
		expirace int(12) NOT NULL default '0',
		signatura varchar(255) NOT NULL,
		kod varchar(255) NOT NULL default '',
		UNIQUE KEY id (id)
) 	TYPE=MyISAM;";



// tabulka administratoru
if (!$S_exists = mysql_query("SELECT 1 FROM ".S_TABLE_ADMINS." LIMIT 0")) {
	mysql_query($S_query_admins);
	$S_info = "y";
}

// tabulka prihlasenych uzivatelu
if (!$S_exists = mysql_query("SELECT 1 FROM ".S_TABLE_LOGGED." LIMIT 0")) {
	mysql_query($S_query_logged);
	$S_info = "y";
}

unset($S_exists);
*/



if (isset($S_info) && $S_info == "y") { // byla vytvorena nektera z tabulek

	// vygenerujeme heslo pro admina
	$S_name = "admin";
	$S_pass = ranpass();
	$S_pass_md5 = md5($S_pass);
	
	
	// ulozime prihlasovaci udaje do DB
	mysql_query("INSERT INTO ".S_TABLE_ADMINS." 
	VALUES (NULL, 'Administrator', '$S_name', '$S_pass_md5', '0', '');");
	
	
	// vypiseme info
	
	// doplnime user a heslo
	
	$S_setup_info = "<center>
	<br /><br /><br />
	
	
	Tabulky byly vytvořeny. Zaznamenejte si následující přihlašovací údaje!!!<br /><br />
	jméno: <b>###S_name###</b><br />
	heslo: <b>###S_pass###</b><br /><br />
	Tyto údaje můžete změnit později.<br /><br />
	Přihlásit se můžete <a href=\"###S_login###\" title=\"Přihlásit\"><b>zde</b></a>.
	
	
	<br /><br /><br /><br /><br />
	
	
	Tables created. Save following login data!!!<br /><br />
	name: <b>###S_name###</b><br />
	password: <b>###S_pass###</b><br /><br />
	This data you can change later.<br /><br />
	Link for login <a href=\"###S_login###\" title=\"Login\"><b>is here</b></a>.
	
	<center>";
	
	
	$S_trans = array (
								"###S_name###" => $S_name, 
								"###S_pass###" => $S_pass, 
								"###S_login###" => S_LOGIN
					);
	echo strtr($S_setup_info, $S_trans);
	
	exit;

}

unset($S_info);
// *****************************************************************************
// vytvoreni tabulek - v beznem provozu nebude treba
// *****************************************************************************












// *****************************************************************************
// odhlaseni
// *****************************************************************************
function delete_user() {
	mysql_query("DELETE FROM ".S_TABLE_LOGGED." 
	WHERE id = ".$_SESSION['login_id']."");
}

if (isset($_GET['logout'])) {
		$_SESSION['S_error'] = "logout";
		login_form();
		delete_user();
}
// automaticke odhlaseni
if (isset($_GET['logout_auto'])) {
		$_SESSION['S_error'] = "logout_auto";
		login_form();
		delete_user();
}
// *****************************************************************************
// odhlaseni
// *****************************************************************************











// *****************************************************************************
// hodnoty potrebne pro login i kontrolu uzivatele
// *****************************************************************************
$S_now = time(); // cas ted
$S_expiration = $S_now + S_TIMEOUT; // cas odhlaseni
$S_sess = session_id(); // session
$S_ip = $_SERVER['REMOTE_ADDR']; // IP uzivatele
$S_pc = gethostbyaddr($_SERVER['REMOTE_ADDR']); // nazev pocitace uzivatele
$S_agent = $_SERVER['HTTP_USER_AGENT']; // prohlizec uzivatele
$S_sig = md5($S_ip."_".$S_pc."_".$S_agent); // zakodujeme - max 255 znaku v MySQL 
// kod - aktualizuje se v db az po overeni stareho na nasledujici strance
$S_new_code = mt_rand(0,999999999);
// *****************************************************************************
// hodnoty potrebne pro login i kontrolu uzivatele
// *****************************************************************************











// *****************************************************************************
// prihlaseni
// *****************************************************************************
if (isset($_POST['login_action']) && $_POST['login_action'] == "login") {

	// id  nazev  name  pass  prava  
	$q = 
  "SELECT id, user FROM ".S_TABLE_ADMINS." 
	WHERE name = '".$_POST[''.TBL.'login_name']."' 
	AND pass = '".md5($_POST[''.TBL.'login_pass'])."'";
	// echo $q;
  $v = mysql_query("$q");
	
	while ($z = mysql_fetch_array($v)) {
		$id = $z['id'];
		$S_user_db = $z['user'];
	}
	
	
	
	if (empty($id)) { // uzivatel nenalezen
	
		$_SESSION['S_error'] = "not_user";
		login_form();
	
	}
	
	else { // uzivatel nalezen
		
// 		$v1 = mysql_query("SELECT COUNT(id) FROM ".S_TABLE_LOGGED." 
// 		WHERE user = $id AND expirace >= $S_now");
		
		
// 		if (mysql_result($v1, 0, 0) == 0) { // user jeste neni prihlasen
		
			$_SESSION['S_user_id'] = $id;
			$_SESSION['S_user_name'] = $S_user_db;
			
			
 			// tab S_TABLE_LOGGED: id  user  session  expirace  signatura  kod
			mysql_query("INSERT INTO ".S_TABLE_LOGGED." VALUES(
			NULL,".$_SESSION['S_user_id'].",'$S_sess','$S_expiration','$S_sig','$S_new_code')");
			
			
			$_SESSION['login_id'] = mysql_result(mysql_query("SELECT LAST_INSERT_ID()"), 0, 0);
			$_SESSION['S_user_kod'] = $S_new_code;
			
			
			unset($_SESSION['S_error']);
			
			
 			header("Location: ".S_LOGIN.""); // prihlaseni ok
			exit;
		
// 		}
// 		
// 		else if (mysql_result($v1, 0, 0) > 0) { // pod stejnym uctem je jiz nekdo prihlasen
// 		
//  			$_SESSION['S_error'] = "access_denied";
// 			header("location: ".S_LOGIN."");
// 			exit;
// 		
// 		}
// 		
// 		else { // jina chyba
// 		
// 			$_SESSION['S_error'] = "access_denied";
// 			header("location: ".S_LOGIN."");
// 			exit;
// 		
// 		}
	}

}
// *****************************************************************************
// prihlaseni
// *****************************************************************************











// *****************************************************************************
// zapomenute heslo
// TODO: heslo a jmeno zasilat na zaklade zadane email. adresy - projit DB 
// a zaslat udaje na vsechny adresy (muze byt vic uzivatelskych uctu se stejnou 
// adresou).
// *****************************************************************************
if (isset($_POST['login_action']) && $_POST['login_action'] == "forg_pass") {

	$post_email = trim($_POST['login_email']);
	
	//  id  user  name  pass  rights  email 
	$v = mysql_query("SELECT id, name FROM ".S_TABLE_ADMINS." 
	WHERE name = '".$_POST[''.TBL.'login_name']."' AND email = '$post_email' ");
	
	while ($z = mysql_fetch_array($v)) {
	
		$S_id = $z['id'];
	
		$S_name = $z['name'];
		$S_pass = ranpass();
	
	}
	
	
	
	
	
	$subject = $_SESSION['S_mess']['subject'] . " " . $_SERVER['SERVER_NAME'] . "";
	
	
	$message = "".Date ("d.m.Y - H:i:s")."\n\n";
	
	$message .= $_SESSION['S_mess']['prihl_jmeno'] . ": $S_name\n";
	$message .= $_SESSION['S_mess']['heslo'] . ": $S_pass\n\n";
	
	$message .= $_SESSION['S_mess']['aut_generated'];
	
	
	
	
	$headers = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/text; charset=utf-8\r\n";
	$headers .= "From: <".$post_email.">\n";
	$headers .= "X-Sender: <".$post_email.">\n";
	$headers .= "X-Mailer: PHP\n"; // mailový klient
	$headers .= "X-Priority: 1\n"; // Urgentní vzkaz!
	$headers .= "Return-Path: <".$post_email.">\n";  // Návratová cesta pro chyby
	
	
	// vyhazeme diakritiku z predmetu
	$subject = strtr($subject, "áäčďéěëíňóöřšťúůüýžÁÄČĎÉĚËÍŇÓÖŘŠŤÚŮÜÝŽ", "aacdeeeinoorstuuuyzAACDEEEINOORSTUUUYZ");
	
	
	
	
	
	if(!empty($S_id)) {
	
		if($s = @mail($post_email,$subject,$message,$headers)) $sent = "y";
		else $sent = "n";
	
	}
	else $_SESSION['S_error'] = "forg_not_email";
	
	
	
	
	if($sent == "y") {
	
		$v2 = mysql_query("UPDATE ".S_TABLE_ADMINS." SET 
		pass = '".md5($S_pass)."' 
		WHERE id = $S_id")
		or die("ř.".__LINE__ .": ".mysql_error());
		
		$_SESSION['S_error'] = "forg_send_ok";
		
		header("location: ".S_LOGIN."");
		exit;
	
	}
	
	
	if($sent == "n") $_SESSION['S_error'] = "forg_email_error";
	
	
	
	header("location: ".S_LOGIN."?forgotten");
	exit;

}
// *****************************************************************************
// zapomenute heslo
// *****************************************************************************











// *****************************************************************************
// vlastni kontrola uzivatele
// *****************************************************************************


if(empty($_SESSION['login_id']))$_SESSION['login_id']='';

// vyhazeme uzivatele s proslou expiraci
// tab S_TABLE_LOGGED: id  user  session  expirace  signatura  kod
mysql_query("DELETE FROM ".S_TABLE_LOGGED." 
WHERE expirace < $S_now 
AND id != ".$_SESSION['login_id']."");



// optimalizace tabulky - jednou za seanci
if(isset($_SESSION['logged_optimized']) && $_SESSION['logged_optimized'] != "y") {

	mysql_query("OPTIMIZE TABLE `".S_TABLE_LOGGED."`");
	$_SESSION['logged_optimized'] = "y";

}

// kontrola uzivatele
if (!empty($_SESSION['login_id'])) {

	// tab S_TABLE_LOGGED: id  user  session  expirace  signatura  kod
	$v = mysql_query("SELECT * FROM ".S_TABLE_LOGGED." WHERE id = ".$_SESSION['login_id']);
	while ($z = mysql_fetch_array($v)) {
		$S_user_db = $z['user'];
		$S_sess_db = $z['session'];
		$S_exp_db = $z['expirace'];
		$S_sig_db = $z['signatura'];
		$S_code_db = $z['kod'];
	}
	
	
	// ***************************************************************************
	// PODLE POTREB APLIKACE!!! start
	// ***************************************************************************
	
	// prava uzivatele budeme kontrolovat vzdy z jeho zaznamu - hlavni admin 
	// mu je muze kdykoli zmenit, je treba tuto zmenu zachytit
	$v = mysql_query("SELECT rights FROM ".S_TABLE_ADMINS." 
	WHERE id = '".$_SESSION['S_user_id']."'");
	
	
	
  $rights=mysql_result($v, 0, 0);
	
	if(!empty($rights)){
  	list($rights['adm_edit'],$rights['settings']) = explode ("|", mysql_result($v, 0, 0));
  }	
	
	// $rights['adm_edit'] - pristup do sekce s administratory
	// $rights['settings'] - pristup k nastavenim
	
	
	// ***************************************************************************
	// PODLE POTREB APLIKACE!!! konec
	// ***************************************************************************
	
	
	if ($S_exp_db < $S_now) $_SESSION['S_error'] = "expired"; // prekrocena doba necinnosti
	else if ($S_sig_db != $S_sig) $_SESSION['S_error'] = "signature"; // signatura
	else if ($S_code_db != $_SESSION['S_user_kod']) $_SESSION['S_error'] = "code"; // kod
	else if ($S_sess_db != $S_sess) $_SESSION['S_error'] = "session"; // session
	else if ($S_user_db != $_SESSION['S_user_id']) $_SESSION['S_error'] = "userid"; // user_id
	else $_SESSION['S_error'] = ""; // vse ok
	
	
	
	
	if(!empty($_SESSION['S_error'])) login_form(); // neco neni ok
	else { // vse ok
	
		mysql_query("UPDATE ".S_TABLE_LOGGED." SET 
		expirace = $S_expiration, kod = $S_new_code 
		WHERE id = ".$_SESSION['login_id']);
		
		$_SESSION['S_user_kod'] = $S_new_code;
	
	}

}
else {

	if(empty($_SESSION['S_error'])) $_SESSION['S_error'] = "userid";
	
	login_form();

}
// *****************************************************************************
// vlastni kontrola uzivatele
// *****************************************************************************













// *****************************************************************************
// prihlasovaci form
// *****************************************************************************
function login_form() {

	// odstranime ID uzivatele z tabulky prihlasenych
	if (!empty($_SESSION['login_id']))
	mysql_query("DELETE FROM ".S_TABLE_LOGGED." WHERE id = ".$_SESSION['login_id']."");
	
	
	// vyprazdnime session
	while (list ($key, ) = each ($_SESSION)) {
	
		if ($key != "S_lang" && 
				$key != "S_error" && 
				$key != "S_mess") 
				
				unset($_SESSION[$key]);
	
	}
	
	
	
	echo "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">

<html>
<head>
	<title>Login - " . $_SERVER['SERVER_NAME'] . "</title>
	<meta http-equiv=\"pragma\" content=\"no-cache\">
	<meta http-equiv=\"cache-control\" content=\"no-cache, must-revalidate\">
	<meta http-equiv=\"expires\" content=\"0\">
	<meta http-equiv=\"last-modified\" content=\"\">
	<meta name=\"robots\" content=\"noindex,nofollow\">
	<meta name=\"robots\" content=\"noarchive\">
	<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">
	<meta name=\"author\" content=\"blazevsky, info@netaction.cz\" >
	
	<style type=\"text/css\">
	<!--
	body, td {
		font-family: Verdana, Tahoma, 'Arial CE', 'Helvetica CE', sans-serif;
		font-size: 10px;
	}
	.f10 {
		font-size: 10px;
	}
	.f9 {
		font-size: 9px;
	}
	.field {
		font-size: 10px;
		width: 130px;
		height: 20px;
	}
	//-->
	</style>
</head>



<body>

<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" 
cellpadding=\"0\" align=\"center\">


<tr>
	<td width=\"100%\" height=\"100%\" align=\"center\" valign=\"middle\">
		
		<table width=\"450\" border=\"0\" cellspacing=\"10\" cellpadding=\"0\">
		
		<form action=\"" . S_LOGIN . "\" method=\"post\">
		
		<tr>
			<td align=\"center\">";
	
	
	if(isset($_GET['forgotten'])) { // zaslat heslo emailem
	
		$mess = $_SESSION['S_mess']['forg_pass'];
		
		if(!empty($_SESSION['S_error']))
		$mess = $mess."<br /><br />".$_SESSION['S_mess'][$_SESSION['S_error']];
		
		
		$login_action = "forg_pass";
		
		
		$form = $_SESSION['S_mess']['name'] . "<br />
					<input type=\"text\" name=\"".TBL."login_name\" size=\"20\" class=\"field\">
					
					<br /><br />
					
					" . $_SESSION['S_mess']['forg_email'] . "<br />
					<input type=\"text\" name=\"".TBL."login_email\" size=\"20\" class=\"field\">";
		
		$button_txt = $_SESSION['S_mess']['forg_button'];
		
		$link2 = "<a href=\"" . S_LOGIN . "\">" . $_SESSION['S_mess']['login_link'] . "</a>";
		
		$_SESSION['S_error'] = "";
		unset($_SESSION['S_error']);
	
	}
	
	else { // prihlaseni
	
		$mess = $_SESSION['S_mess'][$_SESSION['S_error']];
		
		
		$login_action = "login";
		
		$form = $_SESSION['S_mess']['name'] . "<br />
					<input type=\"text\" name=\"".TBL."login_name\" size=\"20\" class=\"field\">
					
					<br /><br />
					
					" . $_SESSION['S_mess']['password'] . "<br />
					<input type=\"password\" name=\"".TBL."login_pass\" size=\"26\" class=\"field\">";
		
		$button_txt = $_SESSION['S_mess']['button'];
		
		$link2 = "<a href=\"" . S_LOGIN . "?forgotten\">" . $_SESSION['S_mess']['forg_pass'] . "</a>";
	
	}
	
	
	echo "
				<p style=\"width: 300px; font-size: 11px;\">
				
					<b>$mess</b>
					
					<br /><br /><br />
				
				</p>
				
				
				<p>
					
					$form
					
					<br /><br />
				
					( $link2 )
					
					<br /><br />
					
					<input type=\"submit\" value=\"$button_txt\" class=\"f10\">
					
					<br /><br /><br />
					
					" . S_LANG_PANEL . "
				
				</p>
				
				
				
				
				<p class=\"f9\">
					
					" . $_SESSION['S_mess']['info'] . "
					
					<br /><br /><br />
				
				</p>
				
				
				" . S_COPYRIGHT . "
			
			
			</td>
		</tr>
		
		<input type=\"hidden\" name=\"login_action\" value=\"$login_action\">
		
		</form>
		
		</table>
		
	</td>
</tr>


</table>

</body>
</html>";
	
	// zlikvidujeme hlasky a zpravy urcene pouze pro zabezpeceni
	unset($_SESSION['S_mess']);
	
	exit;

}
// *****************************************************************************
// prihlasovaci form
// *****************************************************************************














// *****************************************************************************
// formular pro editaci prihlaseneho uzivatele, aktualizace zaznamu uzivatele
// upravit pro potreby dane aplikace
// *****************************************************************************
if(isset($_POST['UEid']) && $_POST['UEid'] > 0 && $_POST['UEid'] == $_SESSION['S_user_id']) {

	// zpracovani hodnot z formulare
	$_SESSION['alert'] = "";
	
	
	if(!empty($_POST['UEuser'])) $UEuser = trim($_POST['UEuser']);
	else $_SESSION['alert'] .= $_SESSION['S_mess']['alert_jmeno']."<br />";
	
	if(!empty($_POST['UEname'])) $UEname = trim($_POST['UEname']);
	else $_SESSION['alert'] .= $_SESSION['S_mess']['alert_prihl_jmeno']."<br />";
	
	if(!empty($_POST['UEemail'])) $UEemail = trim($_POST['UEemail']);
	else $_SESSION['alert'] .= $_SESSION['S_mess']['alert_email']."<br />";
	
	
	
	if($_POST['UEpass1'] != $_POST['UEpass2'])
		$_SESSION['alert'] .= $_SESSION['S_mess']['alert_hesla']."<br />";
	
	
	
	
	// vyskytla se chyba - jeji zpracovani zalezi na dane aplikaci
	if(!empty($_SESSION['alert'])) {
	
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	}
	
	
	
	
	// ulozeni hesla
	if(!empty($_POST['UEpass1'])) {
	
		$UEpass = md5($_POST['UEpass1']);
		
		$pass_q = ", pass = '$UEpass' ";
	
	}
	
	
	
	// id  user  name  pass  rights  email
	mysql_query("update ".S_TABLE_ADMINS." set 
	user = '$UEuser', 
	name = '$UEname',  
	rights = '$UErights', 
	email = '$UEemail' $pass_q 
	where id = ".$_POST['UEid']);
	
	
	if(!empty($UEpass)) {
	
		$subject = $_SESSION['S_mess']['subject'] . " " . $_SERVER['SERVER_NAME'] . "";
		
		$message = "".Date ("d.m.Y - H:i:s")."\n\n";
		
		$message .= $_SESSION['S_mess']['prihl_jmeno'] . ": $UEname\n";
		$message .= $_SESSION['S_mess']['heslo'] . ": ".$_POST['UEpass1']."\n\n";
		
		// $message .= "Pokud změny provedl někdo jiný než vy, přihlaste se do administrace a přihlašovací údaje změňte.\n\n";
		
		$message .= $_SESSION['S_mess']['aut_generated'];
		
		
		
		$headers = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/text; charset=utf-8\r\n";
		$headers .= "From: <".$UEemail.">\n";
		$headers .= "X-Sender: <".$UEemail.">\n";
		$headers .= "X-Mailer: PHP\n"; // mailový klient
		$headers .= "X-Priority: 1\n"; // Urgentní vzkaz!
		$headers .= "Return-Path: <".$UEemail.">\n";  // Návratová cesta pro chyby
		
		
		// vyhazeme diakritiku z predmetu
		$subject = strtr($subject, "áäčďéěëíňóöřšťúůüýžÁÄČĎÉĚËÍŇÓÖŘŠŤÚŮÜÝŽ", "aacdeeeinoorstuuuyzAACDEEEINOORSTUUUYZ");
		
		
		mail($to=$UEemail, $subject, $message, $headers);
		
		//echo nl2br("$headers<br /><br /><br />$UEemail<br /><br />$subject<br /><br />$message");exit;
	
	}
	
	$_SESSION['alert_js'] .= $_SESSION['S_mess']['zaznam_upraven'];
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}




if(isset($_GET['admin']) && $_GET['admin'] == $_SESSION['S_user_id']) {

	$nadpis = $_SESSION['S_mess']['admin_edit'];
	
	// id  user  name  pass  rights  email
	$v = mysql_query("SELECT * FROM ".S_TABLE_ADMINS." 
	WHERE id = ".$_SESSION['S_user_id']."");
	
	
	while ($z = mysql_fetch_array($v)) {
	
		$UEuser = $z['user'];
		$UEname = $z['name'];
		$UErights = $z['rights'];
		$UEemail = $z['email'];
	
	}
	
	$_SESSION['S_user_name'] = $UEuser;
	
	
	$admin_form = "
	<SCRIPT LANGUAGE=\"javascript\">
	<!--
	function validate(form1) {
	
		if (form1.UEuser.value == \"\") {
			alert(\"".$_SESSION['S_mess']['alert_jmeno']."\"); form1.UEuser.focus(); return false;
		}
		else if (form1.UEuser.value.length > 64) {
			alert(\"".$_SESSION['S_mess']['alert_dlouhe_jmeno']."\"); form1.UEuser.focus(); return false;
		}
		else if (form1.UEname.value == \"\") {
			alert(\"".$_SESSION['S_mess']['alert_prihl_jmeno']."\"); form1.UEname.focus(); return false;
		}
		else if (form1.UEname.value.length < 4) {
			alert(\"".$_SESSION['S_mess']['alert_kr_p_j']."\"); form1.UEname.focus(); return false;
		}
		else if (form1.UEname.value.length > 16) {
			alert(\"".$_SESSION['S_mess']['alert_dl_p_j']."\"); form1.UEname.focus(); return false;
		}
		else if (form1.UEemail.value == \"\") {
			alert(\"".$_SESSION['S_mess']['alert_email']."\"); form1.UEemail.focus(); return false;
		}
		else if (form1.UEpass1.value != form1.UEpass2.value) {
		
			if (form1.UEpass1.value == \"\") { 
				alert(\"".$_SESSION['S_mess']['alert_heslo']."\"); form1.UEpass1.focus(); return false;
			}
			else if (form1.UEpass2.value == \"\") { 
				alert(\"".$_SESSION['S_mess']['alert_heslo2']."\"); form1.UEpass2.focus(); return false;
			}
			else alert(\"".$_SESSION['S_mess']['alert_hesla_nesouhl']."\"); form1.UEpass2.focus(); return false;
		
		}
		else if (form1.UEpass1.value != \"\" && form1.UEpass2.value != \"\") {
		
			if (form1.UEpass1.value.length < 4) {
				alert(\"".$_SESSION['S_mess']['alert_kr_heslo']."\"); form1.UEpass1.focus(); return false;
			}
			else if (form1.UEpass2.value.length < 4) {
				alert(\"".$_SESSION['S_mess']['alert_kr_heslo2']."\"); form1.UEpass2.focus(); return false;
			}
		
		}
		else return true;
	
	}
	// -->
	</SCRIPT>
	
	
	<form action=\"\" method=\"post\" onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"UEid\" value=\"".$_SESSION['S_user_id']."\">
	
	<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	
	<tr>
		<td colspan=\"3\" class=\"f10\">(*) ".$_SESSION['S_mess']['povinne_udaje']."<br /><br /></td>
	</tr>
	
	
	
	<tr>
		<td>".$_SESSION['S_mess']['jmeno']." (*)</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"UEuser\" value=\"$UEuser\" size=\"48\" class=\"f10\"> 
			<span class=\"f10i\">".$_SESSION['S_mess']['max64']."</span>
		</td>
	</tr>
	
	
	
	<tr>
		<td>".$_SESSION['S_mess']['prihl_jmeno']." (*)</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"UEname\" value=\"$UEname\" size=\"20\" class=\"f10\"> 
			<span class=\"f10i\">".$_SESSION['S_mess']['4-16']."</span>
		</td>
	</tr>
	
	
	
	<tr>
		<td>".$_SESSION['S_mess']['email']." (*)</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"text\" name=\"UEemail\" value=\"$UEemail\" size=\"20\" class=\"f10\"> 
			<span class=\"f10i\"></span>
		</td>
	</tr>
	
	
	
	<tr>
		<td colspan=\"3\">
		<br /><br />".$_SESSION['S_mess']['zmena_hesla']."<br /><br /></td>
	</tr>
	
	
	
	<tr>
		<td>".$_SESSION['S_mess']['heslo']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"password\" name=\"UEpass1\" value=\"\" size=\"25\" class=\"f10\"> 
			<span class=\"f10i\"></span>
		</td>
	</tr>
	
	
	
	<tr>
		<td>".$_SESSION['S_mess']['heslo_znovu']."</td>
		<td width=\"30\">&nbsp;</td>
		<td>
			<input type=\"password\" name=\"UEpass2\" value=\"\" size=\"25\" class=\"f10\"> 
			<span class=\"f10i\"></span>
		</td>
	</tr>
	
	
	
	<tr>
		<td colspan=\"3\">
		
			<br><br>
			
			<input type=\"submit\" title=\"".$_SESSION['S_mess']['ulozit_zaznam']."\" 
				value=\"".$_SESSION['S_mess']['ulozit_zaznam']."\" class=\"butt_green\">
		
		</td>
	</tr>
	
	</table>
	
	</form>";

}
// *****************************************************************************
// formular pro editaci prihlaseneho uzivatele
// upravit pro potreby dane aplikace
// *****************************************************************************













// *****************************************************************************
// udaje o uzivateli - aut. odhlaseni, username, ... zobrazena v zahlavi stranky
// zobrazujeme jmeno prihlaseneho uzivatele, zbyvajici cas do aut. odhlaseni 
// odkaz k odhlaseni. Pri prekroceni nastaveneho casu neaktivity dojde pomoci 
// javaskriptu k presmerovani na prihlasovaci stranku a tim k odhlaseni uzivatele
// *****************************************************************************

$S_user_data = "

<form name=\"redirect\">
" . $_SESSION['S_mess']['entered'] . ": <b>" . $_SESSION['S_user_name'] . "</b> 

[ <a href=\"".S_LOGIN."?admin=".$_SESSION['S_user_id']."\"><b>" . $_SESSION['S_mess']['edit_admin'] . "</b></a> / 

<a href=\"".S_LOGIN."?logout\"><b>" . $_SESSION['S_mess']['logout_link'] . "</b></a> ] <!-- - 

" . $_SESSION['S_mess']['aut_logout'] . " <input type=\"text\" size=\"5\" name=\"redirect2\" readonly style=\"width: 50px; text-align: center; border: 0px; font-size: 9px;\">-->  

</form>



<script>
<!--
// pocet sekund na zacatku odpocitavani
var countdownfrom = " . (S_TIMEOUT + 0) . "

var currentsecond=document.redirect.redirect2.value=countdownfrom+1

function countredirect() {
	if (currentsecond!=1) {
		currentsecond-=1
		
		secs = currentsecond % 3600 % 60
		mins = Math.floor(currentsecond / 60)
		hours = Math.floor(mins / 60)
		
		mins = mins % 60 //+ (currentsecond % 3600)
		
		
		if (secs < 10) secs = '0' + secs
		
		if (mins < 10) mins = '0' + mins
		if (mins < 1) mins = '00'
		
		if (hours < 10) hours = '0' + hours
		if (hours < 1) hours = '00'
		
		document.redirect.redirect2.value = hours + ':' + mins + ':' + secs + ''//
	}
	else {
		secs = '00'
		document.redirect.redirect2.value = hours + ':' + mins + ':' + secs + ''//hours + ':' + 
		top.location.replace ('".S_LOGIN."?logout_auto') // po vyprseni dojde k reloadu a odhlaseni
		return
	}
	
	setTimeout(\"countredirect()\",1000)
}
countredirect()
//-->
</script>";
// *****************************************************************************
// udaje o uzivateli - aut. odhlaseni, username, atd zobrazena v zahlavi stranky
// *****************************************************************************













// zlikvidujeme hlasky a zpravy urcene pouze pro zabezpeceni
unset($_SESSION['S_mess']);

// reset ($GLOBALS);
// while ($pole = each($GLOBALS)) {
// 	$nazev = $pole['key']; //nazev promenne ($nazev)
// 	$hodnota = $pole['value'];
// 	$hodnota = trim($hodnota); //vycisteni od prazdnych znaku na zacatku a na konci
// 	echo "<br>POST $nazev ".${$nazev} = $hodnota; //prirazeni hodnoty k nazvu, prevedeny na lokalni $
// }exit;
?>
