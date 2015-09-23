<?php


// oznaceni pole - kvuli pouzivani stejneho formu pro registraci a objednavku bez registrace
$PFX = 'register';
$data='';



function Random_Password($delka_hesla) {
  $mozne_znaky = 'abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $vystup = '';
  $pocet_moznych_znaku = strlen($mozne_znaky);
  for ($i=0;$i<$delka_hesla;$i++) {
    $vystup .= $mozne_znaky[mt_rand(0,$pocet_moznych_znaku)];
  }
  return $vystup;
}


/* Slevy a navýšení v kategoriích. */
/**
Pole s informacemi o nadřazených kategoriích
@param (int) id_cat
@param (array) ($sleva_kategorie)
*/
function sleva_kategorie($id_cat, $sleva_kategorie = NULL)
{
  if(!isset($_SESSION['user']['UID']) OR empty($_SESSION['user']['UID']))
  {
    return 0;
  }

	$query = "
  SELECT id_parent
  FROM ".T_CATEGORIES." AS CATEGORIES
	WHERE CATEGORIES.id = '".intval($id_cat)."'
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$z1 = mysql_fetch_assoc($v);
  $z["id_parent"] = $z1["id_parent"];

	$query = "
  SELECT sleva
  FROM ".T_SLEVA_KATEGORIE_X_ADRESA." AS SLEVA_KATEGORIE_X_ADRESA
  WHERE SLEVA_KATEGORIE_X_ADRESA.id_kategorie = '".intval($id_cat)."'
	AND SLEVA_KATEGORIE_X_ADRESA.id_adresa = '".intval($_SESSION['user']['UID'])."'
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z2 = mysql_fetch_assoc($v);
  $z["sleva"] = $z2["sleva"];

  if(!isset($sleva_kategorie)) { $sleva_kategorie = 0; }
  if($sleva_kategorie == 0 AND $z["sleva"] > 0) { $sleva_kategorie = $z["sleva"]; }

	if($z["id_parent"] > 0) { return sleva_kategorie($z["id_parent"], $sleva_kategorie); }
  else { return $sleva_kategorie; }
}


function login($name,$pass)
{
	// provede se prihlaseni - jednak z prihlas. formu, druhak automaticky ihned po registraci
	
	// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
	$query = "SELECT id, nazev FROM ".T_ADRESY_F." 
	WHERE jmeno = '$name' 
	AND heslo = '".sha1($pass)."' 
	AND ".SQL_C_LANG."
	LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v))
  {
		$UID = $z['id'];
		$SHA = sha1($pass); // to se mozna hodi
	}
	
	if(!empty($UID))
  {
		unset($_SESSION['user']); // odhlasime pripadneho usera
		
		$_SESSION['alert_js1'] .= '\nJste přihlášen(a).';
		$_SESSION['user']['UID'] = $UID;
		$_SESSION['user']['sha'] = $SHA;
		
    // Sleva na výrobce.
		$query = "SELECT * FROM ".T_PRODS." WHERE ".SQL_C_LANG."";
	  $v = my_DB_QUERY($query,__LINE__,__FILE__);

		while ($z = mysql_fetch_array($v))
    {
		  $id_vyrobce=$z['id'];
	    $_SESSION['user']['sleva_'.$id_vyrobce]=0;
		}
		
		$query2 = "SELECT ".T_CENY.".sleva, ".T_CENY_X_ADRESY.".id_vyrobce FROM ".T_CENY.",".T_CENY_X_ADRESY." 
               WHERE ".T_CENY.".id = ".T_CENY_X_ADRESY.".id_cena_cat AND
                     ".T_CENY_X_ADRESY.".id_adresa = ".$UID." ";
	  $v2 = my_DB_QUERY($query2,__LINE__,__FILE__);

	  $_SESSION['user']['sleva']=0;
	  while ($z2 = mysql_fetch_array($v2))
    {
      $id_vyrobce=$z2['id_vyrobce'];
      $_SESSION['user']['sleva_'.$id_vyrobce]=$z2['sleva'];
      if($z2['sleva']>0) $_SESSION['user']['sleva']=1;
	  }

    // Sleva na kategorii
    $query = "
    SELECT id
    FROM ".T_CATEGORIES."
    WHERE hidden = 0
    AND ".SQL_C_LANG."
    ";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);

    while($z = mysql_fetch_assoc($v))
    {
      $_SESSION["user"]["sleva_kategorie"][$z["id"]] = sleva_kategorie($z["id"]);
    }
  }
  else $_SESSION['alert_js1'] = 'Uživatel nenalezen.';
}










// odhlaseni
if(isset($_GET['logout'])) { // editace zaznamu

	unset($_SESSION['user']);
	unset($_SESSION['sbaskets']);
	unset($_SESSION['basket_total']);
	unset($_SESSION['basket_doprava']);
	unset($_SESSION['basket_doprava_alter']);
  	unset($_SESSION['basket_platba']);
  	unset($_SESSION['basket_suma']);
	
// 	unset($_SESSION['basket_total']);
	
	$_SESSION['alert_js1'] = 'Jste odhlášen(a).';
	
	header("location: ".$_SERVER['HTTP_REFERER']);//.
	exit;

}



// prihlaseni
if(isset($_POST['login1']) || isset($_POST['pass1'])) { // editace zaznamu

	login($_POST['login1'],$_POST['pass1']);
	
	header("location: ".$_SERVER['HTTP_REFERER']);//.
	exit;

}



if(!empty($_GET['action']) && $_GET['action']=='zapomenute-heslo'){
	$H1='Zapomenuté heslo';
	if(!empty($_POST['lostpass'])){
		$mail=trim($_POST['lostpass']);
		
		$_SESSION['error']='';
		
		kontrola_mailu($mail);
		
		
		if(!empty($_SESSION['error'])){
		
			$_SESSION['error']='<div class="error">'.$_SESSION['error'].'</div>';
			Header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
						
		}else{			
			
			$query="select * from ".T_ADRESY_F." where jmeno='$mail' and ".SQL_C_LANG;
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			
			$zakaznik=mysql_fetch_array($v);
			
			if(mysql_num_rows($v)>0){
				$newpass=Random_Password(6);	
				
				$query="update ".T_ADRESY_F." set heslo='".sha1($newpass)."' where jmeno='$mail' and ".SQL_C_LANG;
				my_DB_QUERY($query,__LINE__,__FILE__);
				
				$message="Požádali jste o nové heslo k Vašemu účtu na našem e-shopu:
						
						Přihlašovací e-mail: $mail
						Přihlašovací heslo: $newpass
						
						Děkujeme za Vaší přízeň
						
						".NAZEV_SHOP;
				
				$subject = NAZEV_SHOP.' - nove pristupove udaje';
				
				send($mail,nl2br($message),$subject);
				send2(S_MAIL_SHOP,$message,'KOPIE - '.$subject);
				
				$data='
				<div class="info">
					Vaše nové heslo bylo odesláno na uvedený e-mail. Můžete se přihlásit<br /> a změnit své údaje.				
				</div>
				';
			}else{
				$_SESSION['error']='<div class="error">Takový e-mail v naší databázi neexistuje.</div>';
				Header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;		
			}	
			
		}	

	}elseif(empty($_SESSION['user']['UID'])){
		$data='
		<div class="info">
			Zadejte prosím Váš e-mail uvedený při registraci.				
		</div>
		<form method="post" action="">
		<table cellpadding="0" cellspacing="1" class="usertable">
				
				<tr>
					<th colspan="2">Zapomenuté heslo</th>
				</tr>
				
				<tr>
					<td class="first">Váš e-mail uvedený při registraci:</td>
					<td><input type="text" name="lostpass" /></td>
				</tr>
		</table>
		<div class="buttons">
				<input type="submit" value="Odeslat nové heslo" class="button_green" />
		</div>			
		</form>
		';
	}else{
		Header("Location: http://".$_SERVER['SERVER_NAME']);
		exit;
	}
		
}elseif(empty($_GET['orders'])){
if(!empty($_POST)) {

	include_once 'form_user_control.php';
	
	
	if(!empty($_POST['UID'])) { // editace zaznamu
	
		$whereUID = 'AND id != '.$_POST['UID'];
	
	}else{
		$whereUID='';
	}
	
	
	
	// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
	$query = "SELECT COUNT(id) FROM ".T_ADRESY_F." 
	WHERE jmeno = '".$_SESSION[$PFX]['f_mail']."' AND ".SQL_C_LANG." $whereUID";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$count = mysql_result($v, 0, 0);
	
	//echo $query;
	//echo 'pocet - '.$count;
	//exit;
	if($count > 0) {
	
		$_SESSION['error'] = "<div class=\"error\">E-mail byl již registrován - pokud jste zapomněli přístupové údaje, vyžádejte si je v sekci <strong><a href='/zapomenute-heslo/'>zapomenuté heslo</a></strong>.</div>";
		
		header("location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	}
	
	
	if(!empty($_POST['UID'])) { // editace zaznamu
	
		// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
		$query = "UPDATE ".T_ADRESY_F." SET 
		nazev = '".$_SESSION[$PFX]['f_jmeno']."',
		kontakt = '".$_SESSION[$PFX]['f_kontakt']."', 
		adresa = '".$_SESSION[$PFX]['f_adresa']."', 
		psc = '".$_SESSION[$PFX]['f_psc']."', 
		mesto = '".$_SESSION[$PFX]['f_mesto']."', 
		stat = '".$_SESSION[$PFX]['f_stat']."', 
		ico = '".$_SESSION[$PFX]['f_ico']."', 
		dic = '".$_SESSION[$PFX]['f_dic']."', 
		email = '".$_SESSION[$PFX]['f_mail']."', 
		telefon = '".$_SESSION[$PFX]['f_tel']."', 
		jmeno = '".$_SESSION[$PFX]['f_mail']."', 
		heslo = '".sha1($_SESSION[$PFX]['login_pass'])."' 
		WHERE id = ".$_POST['UID']." 
		LIMIT 1";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		
		// pokud je postovni adresa
		if(!empty($_SESSION[$PFX]['p_jmeno'])) {
		
			// id  id_f  nazev  adresa  psc  mesto  email  telefon
			$query = "SELECT COUNT(id) FROM ".T_ADRESY_P." 
			WHERE id_f = ".$_POST['UID']."";
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			$count2 = mysql_result($v, 0, 0);
			
			if($count > 0) {
			
				// id  id_f  nazev  adresa  psc  mesto  email  telefon
				$query = "UPDATE ".T_ADRESY_P." SET 
				nazev = '".$_SESSION[$PFX]['p_jmeno']."',
				kontakt = '".$_SESSION[$PFX]['p_kontakt']."', 
				adresa = '".$_SESSION[$PFX]['p_adresa']."', 
				psc = '".$_SESSION[$PFX]['p_psc']."', 
				mesto = '".$_SESSION[$PFX]['p_mesto']."', 
				stat = '".$_SESSION[$PFX]['p_stat']."', 
				email = '".$_SESSION[$PFX]['p_email']."', 
				telefon = '".$_SESSION[$PFX]['p_telefon']."' 
				WHERE id_f = ".$_POST['UID']."
				LIMIT 1";
				my_DB_QUERY($query,__LINE__,__FILE__);
			
			} else {
			
        
				// id  id_f  nazev  adresa  psc  mesto  email  telefon
				$query = "INSERT INTO ".T_ADRESY_P." 
				VALUES(NULL,'".$_POST['UID']."','".$_SESSION[$PFX]['p_jmeno']."',
				'".$_SESSION[$PFX]['p_kontakt']."','".$_SESSION[$PFX]['p_adresa']."',
				'".$_SESSION[$PFX]['p_psc']."','".$_SESSION[$PFX]['p_mesto']."',
				'".$_SESSION[$PFX]['p_stat']."','".$_SESSION[$PFX]['p_email']."',
				'".$_SESSION[$PFX]['p_telefon']."')";
				my_DB_QUERY($query,__LINE__,__FILE__);
			
			}
		
		} else {
		
			// id  id_f  nazev  adresa  psc  mesto  email  telefon
			$query = "DELETE FROM ".T_ADRESY_P." 
			WHERE id_f = ".$_POST['UID']." 
			LIMIT 1";
			my_DB_QUERY($query,__LINE__,__FILE__);
		
		}
		
		$_SESSION['alert_js1'] = 'Změny byly uloženy.'; // za text bude dodana zprava z fce login()
	
	} else { // novy zaznam
	  //antispam ochrana
    if($_POST['antis']=='') {
		// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
		$query = "INSERT INTO ".T_ADRESY_F." 
		VALUES(NULL,
		'".$_SESSION[$PFX]['f_jmeno']."','".$_SESSION[$PFX]['f_kontakt']."',
		'".$_SESSION[$PFX]['f_adresa']."','".$_SESSION[$PFX]['f_psc']."',
		'".$_SESSION[$PFX]['f_mesto']."','".$_SESSION[$PFX]['f_stat']."',
		'".$_SESSION[$PFX]['f_ico']."','".$_SESSION[$PFX]['f_dic']."',
		'".$_SESSION[$PFX]['f_mail']."','".$_SESSION[$PFX]['f_tel']."',
		'".$_SESSION[$PFX]['f_mail']."','".sha1($_SESSION[$PFX]['login_pass'])."','".C_LANG."')";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		// ID noveho uzivatele
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$uid = mysql_result($v, 0, 0);

// ******************************************************************************		
// 		// **** startovací slevy - zatím workaround
// 		//
// 		//MAKITA ... id 7
// 		//sleva 10% pro všechny nové  ... id 6
// 		//
// 		$id_vyrobce=7;
// 		$cena_cat=6;
// 		if(C_LANG==2) { //pro nářadí-obchod
//       $query3= 'INSERT INTO '.T_CENY_X_ADRESY.' VALUES( '.$uid.', '.$cena_cat.', '.$id_vyrobce.') ';
//       my_DB_QUERY($query3,__LINE__,__FILE__);
//     }
// ******************************************************************************
		
		// pokud je postovni adresa
		if(!empty($_SESSION[$PFX]['p_jmeno'])) {
		
			// id  id_f  nazev  adresa  psc  mesto  email  telefon
			$query = "INSERT INTO ".T_ADRESY_P." 
			VALUES(NULL,'$uid',
			'".$_SESSION[$PFX]['p_jmeno']."','".$_SESSION[$PFX]['p_kontakt']."',
			'".$_SESSION[$PFX]['p_adresa']."','".$_SESSION[$PFX]['p_psc']."',
			'".$_SESSION[$PFX]['p_mesto']."','".$_SESSION[$PFX]['p_stat']."',
			'".$_SESSION[$PFX]['p_email']."','".$_SESSION[$PFX]['p_telefon']."')";
			my_DB_QUERY($query,__LINE__,__FILE__);
		
		}

		$_SESSION['alert_js1'] = 'Vaše registrace proběhla úspěšně.'; // za text bude dodana zprava z fce login()
		
		$arrayStaty=unserialize(STATY);		
		
		// ******** poslání oznámení mailem)
		$subject = NAZEV_SHOP.' - registrace';
		$delic = '=========================';
		$message = '
		
		'.date("d.m.Y v H:i:s").' jste uložil(a) následující údaje do našeho e-shopu
		
		
		'.$delic.'
		Fakturační adresa
		'.$delic.'
		'.$_SESSION[$PFX]['f_jmeno'].' - '.$_SESSION[$PFX]['f_kontakt'].'
		'.$_SESSION[$PFX]['f_adresa'].'
		'.$_SESSION[$PFX]['f_psc'].' '.$_SESSION[$PFX]['f_mesto'].' / '.$arrayStaty[$_SESSION[$PFX]['f_stat']].'
		IČO: '.$_SESSION[$PFX]['f_ico'].'
		DIČ: '.$_SESSION[$PFX]['f_dic'].'
		E-mail: '.$_SESSION[$PFX]['f_mail'].'
		Telefon: '.$_SESSION[$PFX]['f_tel'].'
		
		'.$delic.'
		Poštovní adresa
		'.$delic.'
		'.$_SESSION[$PFX]['p_jmeno'].' - '.$_SESSION[$PFX]['p_kontakt'].'
		'.$_SESSION[$PFX]['p_adresa'].'
		'.$_SESSION[$PFX]['p_psc'].' '.$_SESSION[$PFX]['p_mesto'].' / '.$arrayStaty[$_SESSION[$PFX]['p_stat']].'
		
		'.$delic.'
		Přihlašovací údaje
		'.$delic.'
		Přihlašovací e-mail: '.$_SESSION[$PFX]['f_mail'].'
		Heslo: '.$_SESSION[$PFX]['login_pass'].'';
		
		$message = nl2br($message);
		// *************************************************
		
 	  // pokud zadal e-mail, posleme udaje
	  if(!empty($_SESSION[$PFX]['f_mail'])) send($_SESSION[$PFX]['f_mail'],$message,$subject);
	  // kopie majiteli 
	  send2(S_MAIL_SHOP,$message,'KOPIE - '.$subject);
		
		// prihlasime ho
		login($_SESSION[$PFX]['f_mail'],$_SESSION[$PFX]['login_pass']);
    }
    else $_SESSION['alert_js1'] = 'Vaši registraci zastavila antispamová ochrana.';
	}
		
	// odstranime docasna data pro form
	unset($_SESSION[$PFX]);
	
	header("location: ".HTTP_ROOT."?go=user&edit=".$_SESSION['user']['sha']);//.$_SERVER['HTTP_REFERER']
	exit;

}



if(!empty($_SESSION['user']['UID']) && isset($_GET['edit']) && $_GET['edit'] == $_SESSION['user']['sha']) { //

	$title = "Změna údajů uživatele";
	
	
	//T_ADRESY_F - id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
	$query = "SELECT * 
	FROM ".T_ADRESY_F." 
	WHERE id = ".$_SESSION['user']['UID']." 
	LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		$_SESSION[$PFX]['f_jmeno'] = $z['nazev'];
		$_SESSION[$PFX]['f_kontakt'] = $z['kontakt'];
		$_SESSION[$PFX]['f_adresa'] = $z['adresa'];
		$_SESSION[$PFX]['f_psc'] = $z['psc'];
		$_SESSION[$PFX]['f_mesto'] = $z['mesto'];
		$_SESSION[$PFX]['f_stat'] = $z['stat'];
		$_SESSION[$PFX]['f_ico'] = $z['ico'];
		$_SESSION[$PFX]['f_dic'] = $z['dic'];
		$_SESSION[$PFX]['f_mail'] = $z['email'];
		$_SESSION[$PFX]['f_tel'] = $z['telefon'];
		$_SESSION[$PFX]['login_name'] = $z['jmeno'];
	
	}
	
	
	//T_ADRESY_P - id  id_f  nazev  adresa  psc  mesto  email  telefon
	$query = "SELECT * FROM ".T_ADRESY_P." 
	WHERE id_f = ".$_SESSION['user']['UID']."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		$p_id = $z['id'];
		$_SESSION[$PFX]['p_jmeno'] = $z['nazev'];
		$_SESSION[$PFX]['p_kontakt'] = $z['kontakt'];
		$_SESSION[$PFX]['p_adresa'] = $z['adresa'];
		$_SESSION[$PFX]['p_psc'] = $z['psc'];
		$_SESSION[$PFX]['p_mesto'] = $z['mesto'];
		$_SESSION[$PFX]['p_stat'] = $z['stat'];
		$_SESSION[$PFX]['p_email'] = $z['email'];
		$_SESSION[$PFX]['p_telefon'] = $z['telefon'];
	
	}
	
	if(empty($_SESSION[$PFX]['p_jmeno']))$_SESSION[$PFX]['p_jmeno'] = '';
	if(empty($_SESSION[$PFX]['p_kontakt']))$_SESSION[$PFX]['p_kontakt'] = '';
	if(empty($_SESSION[$PFX]['p_adresa']))$_SESSION[$PFX]['p_adresa'] = '';
	if(empty($_SESSION[$PFX]['p_psc']))$_SESSION[$PFX]['p_psc'] = '';
	if(empty($_SESSION[$PFX]['p_mesto']))$_SESSION[$PFX]['p_mesto'] = '';
	if(empty($_SESSION[$PFX]['p_stat']))$_SESSION[$PFX]['p_stat'] = '';
	if(empty($_SESSION[$PFX]['p_email']))$_SESSION[$PFX]['p_email'] = '';
	if(empty($_SESSION[$PFX]['p_telefon']))$_SESSION[$PFX]['p_telefon'] = '';
	

} else {

	if(empty($_SESSION[$PFX]['f_jmeno']))$_SESSION[$PFX]['f_jmeno'] = '';
	if(empty($_SESSION[$PFX]['f_kontakt']))$_SESSION[$PFX]['f_kontakt'] = '';
	if(empty($_SESSION[$PFX]['f_adresa']))$_SESSION[$PFX]['f_adresa'] = '';
	if(empty($_SESSION[$PFX]['f_psc']))$_SESSION[$PFX]['f_psc'] = '';
	if(empty($_SESSION[$PFX]['f_mesto']))$_SESSION[$PFX]['f_mesto'] = '';
	if(empty($_SESSION[$PFX]['f_stat']))$_SESSION[$PFX]['f_stat'] = '';
	if(empty($_SESSION[$PFX]['f_ico']))$_SESSION[$PFX]['f_ico'] = '';
	if(empty($_SESSION[$PFX]['f_dic']))$_SESSION[$PFX]['f_dic'] = '';
	if(empty($_SESSION[$PFX]['f_mail']))$_SESSION[$PFX]['f_mail'] = '';
	if(empty($_SESSION[$PFX]['f_tel']))$_SESSION[$PFX]['f_tel'] = '';

	if(empty($_SESSION[$PFX]['p_jmeno']))$_SESSION[$PFX]['p_jmeno'] = '';
	if(empty($_SESSION[$PFX]['p_kontakt']))$_SESSION[$PFX]['p_kontakt'] = '';
	if(empty($_SESSION[$PFX]['p_adresa']))$_SESSION[$PFX]['p_adresa'] = '';
	if(empty($_SESSION[$PFX]['p_psc']))$_SESSION[$PFX]['p_psc'] = '';
	if(empty($_SESSION[$PFX]['p_mesto']))$_SESSION[$PFX]['p_mesto'] = '';
	if(empty($_SESSION[$PFX]['p_stat']))$_SESSION[$PFX]['p_stat'] = '';
	if(empty($_SESSION[$PFX]['p_email']))$_SESSION[$PFX]['p_email'] = '';
	if(empty($_SESSION[$PFX]['p_telefon']))$_SESSION[$PFX]['p_telefon'] = '';

	$title = "Registrace nového uživatele";

}




$H1 = $title;



$disabledUID='';

$f_jmeno = "<input type=\"text\" name=\"f_jmeno\" value=\"".$_SESSION[$PFX]['f_jmeno']."\" />";
$f_kontakt = "<input type=\"text\" name=\"f_kontakt\" value=\"".$_SESSION[$PFX]['f_kontakt']."\" />";
$f_adresa = "<input type=\"text\" name=\"f_adresa\" value=\"".$_SESSION[$PFX]['f_adresa']."\" />";
$f_psc = "<input class='w50' type=\"text\" name=\"f_psc\" value=\"".$_SESSION[$PFX]['f_psc']."\" />";
$f_mesto = "<input class='' type=\"text\" name=\"f_mesto\" value=\"".$_SESSION[$PFX]['f_mesto']."\" />";
$f_ico = "<input type=\"text\" name=\"f_ico\" value=\"".$_SESSION[$PFX]['f_ico']."\" />";
$f_dic = "<input type=\"text\" name=\"f_dic\" value=\"".$_SESSION[$PFX]['f_dic']."\" />";
$f_mail = "<input type=\"text\" name=\"f_mail\" value=\"".$_SESSION[$PFX]['f_mail']."\" />";
$f_tel = "<input type=\"text\" name=\"f_tel\" value=\"".$_SESSION[$PFX]['f_tel']."\" />";


$p_jmeno = "<input type=\"text\" name=\"p_jmeno\" value=\"".$_SESSION[$PFX]['p_jmeno']."\"  />";
$p_kontakt = "<input type=\"text\" name=\"p_kontakt\" value=\"".$_SESSION[$PFX]['p_kontakt']."\"  />";
$p_adresa = "<input type=\"text\" name=\"p_adresa\" value=\"".$_SESSION[$PFX]['p_adresa']."\" />";
$p_psc = "<input class='w50' type=\"text\" name=\"p_psc\" value=\"".$_SESSION[$PFX]['p_psc']."\" />";
$p_mesto = "<input class='' type=\"text\" name=\"p_mesto\" value=\"".$_SESSION[$PFX]['p_mesto']."\" />";






$bt_text = 'Uložit data'; 
include_once 'form_user.php';




if(!empty($_GET['edit']))$hidden1 = "<input type=\"hidden\" name=\"UID\" value=\"".$_SESSION['user']['UID']."\" />";
else $hidden1='';




$data .= "
<div id=\"user2\">
<form action=\"".THIS_PAGE."\" method=\"post\">

$hidden1

$form_user

$pristup

$posli

</form>
</div>";

unset($_SESSION[$PFX]);
}






//*****************************************************************************
// OBJEDNAVKY DANEHO UZIVATELE
//*****************************************************************************

if(!empty($_SESSION['user']['UID']) && isset($_GET['orders']) && $_GET['orders'] == $_SESSION['user']['sha']) { 
	if(empty($_GET['order'])){
	     //vypis objednavek uzivatele
	     $H1='Seznam uskutečněných objednávek';
	     
	     $query='select * from '.T_ORDERS_ADDRESS.' where id_user='.$_SESSION['user']['UID'].' order by c_obj desc';
	     
	     $v = my_DB_QUERY($query,__LINE__,__FILE__);
	     
	     $data='';
	     
	     while($z=mysql_fetch_array($v)){
			$data.='
				<tr>
					<td class="first">'.timestamp_to_date($z['time']).'</td>
					<td>'.$z['c_obj'].'</td>
					<td class="orderright"><a target="_blank" href="?go=user&orders='.$_SESSION['user']['sha'].'&order='.$z['id'].'" class="butt_blue">Detail &gt;&gt;</a></td>
				</tr>';		
		}
		
		if(!empty($data)){
				$data='
				<table cellpadding="0" cellspacing="0" class="usertable orders">
				<tr>
					<th class="orderleft">Datum objednávky</th>
					<th class="orderleft">Číslo objednávky</th>
					<th class="orderright">Detail objednávky</th>
				</tr>
				'.$data.'
				</table>';
			     
		}else{
			$data='<p>Nebyly nalezeny žádné Vaše objednávky.</p>';
		}
	}elseif(isObjednavkaOfUser($_SESSION['user']['UID'],$_GET['order'])){
		  	$staty=unserialize(STATY);
		// 	print_r($staty);	
			
			$id=$_GET['order'];		
			
			// id c_obj f_jmeno f_adresa f_psc f_mesto f_ico f_dic f_mail f_tel p_jmeno p_adresa p_psc p_mesto pozn
			$query = "SELECT * FROM ".T_ORDERS_ADDRESS." 
			WHERE id = $id LIMIT 0,1";
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			
			while ($z = mysql_fetch_array($v)) {
			
				$c_obj=$z['c_obj'];
				$time=$z['time'];;
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
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['ks_bez_dph']]."</td>
								<td align=\"right\" valign=\"top\" nowrap>".$ceny[$ceny['K3']]."</td>
								<td align=\"right\" valign=\"top\" nowrap>".number_format(round($z['cena']*$ks),2,","," ")."</td>
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
							<td align=\"right\"><strong>Celkem bez DPH</strong></td>
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
							<td colspan=\"6\">
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
		<body>
		
			<center>
			
				$data
				
				<br /><br /><br />
				
			
			</center>
		
		</body>
		</html>";
			
			exit;	     
	}else{
	     $H1='Neexistující objednávka';
	     $data='<p>Taková Vaše objednávka neexistuje</p>';
	}	
}

//*****************************************************************************
// // OBJEDNAVKY DANEHO UZIVATELE
//*****************************************************************************











if(!empty($_SESSION['error'])) {
     $TEXT= $_SESSION['error'].$data;
	unset($_SESSION['error']);

}else{
	$TEXT=$data;
}
?>
