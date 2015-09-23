<?php

// *****************************************************************************
// kontrola vyplnenni udaju o registrovanem uzivateli, nebo o objednavajicim
// spolecne pro basket a registraci
// *****************************************************************************
while ($pbU = each($_POST)) {

	// vyhazeme mezery
	if($pbU['key'] == "f_psc" || $pbU['key'] == "p_psc" || 
	$pbU['key'] == "f_ico" || $pbU['key'] == "f_dic" || 
	$pbU['key'] == "f_tel" || $pbU['key'] == "p_tel") {
	
		$tb = array (" " => "");
		$pbU['value'] = strtr($pbU['value'], $tb);
	
	}
	
	$_SESSION[$PFX][$pbU['key']] = trim($pbU['value']);

}



// echo 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
// exit;


// povinne polozky
$_SESSION['error'] = "";

if(empty($_SESSION[$PFX]['f_jmeno']))
	$_SESSION['error'] .= "Vyplňte položku <strong>Firma (jméno a příjmení)</strong> (Fakturační adresa)<br />";


if(empty($_SESSION[$PFX]['f_adresa']))
	$_SESSION['error'] .= "Vyplňte položku <strong>Adresa</strong> (Fakturační adresa)<br />";

if(empty($_SESSION[$PFX]['f_psc']))
	$_SESSION['error'] .= "Vyplňte položku <strong>PSČ</strong> (Fakturační adresa)<br />";

if(empty($_SESSION[$PFX]['f_mesto']))
	$_SESSION['error'] .= "Vyplňte položku <strong>Město</strong> (Fakturační adresa)<br />";
	
if(empty($_SESSION[$PFX]['f_mail'])) 
	$_SESSION['error'] .= "Vyplňte položku <strong>E-mail</strong> (Fakturační adresa)<br />";
	
if(empty($_SESSION[$PFX]['f_tel'])) 
	$_SESSION['error'] .= "Vyplňte položku <strong>Telefon</strong> (Fakturační adresa)<br />";	

if(!empty($_SESSION[$PFX]['f_mail'])) kontrola_mailu($_SESSION[$PFX]['f_mail']);


if(!empty($_SESSION[$PFX]['p_jmeno']) || 
	!empty($_SESSION[$PFX]['p_kontakt']) || 
	!empty($_SESSION[$PFX]['p_adresa']) || 
	!empty($_SESSION[$PFX]['p_psc']) || 
	!empty($_SESSION[$PFX]['p_mesto'])|| 
	!empty($_SESSION[$PFX]['p_stat'])) {
	
	
	if(empty($_SESSION[$PFX]['p_jmeno']))
		$_SESSION['error'] .= "Vyplňte položku <strong>Firma (jméno a příjmení)</strong> (Doručovací adresa)<br />";
	
	if(empty($_SESSION[$PFX]['p_adresa']))
		$_SESSION['error'] .= "Vyplňte položku <strong>Adresa</strong> (Doručovací adresa)<br />";
	
	if(empty($_SESSION[$PFX]['p_psc']))
		$_SESSION['error'] .= "Vyplňte položku <strong>PSČ</strong> (Doručovací adresa)<br />";
	
	if(empty($_SESSION[$PFX]['p_mesto']))
		$_SESSION['error'] .= "Vyplňte položku <strong>Město</strong> (Doručovací adresa)<br />";
		
	if(empty($_SESSION[$PFX]['p_stat']))
		$_SESSION['error'] .= "Vyplňte položku <strong>Stát</strong> (Doručovací adresa)<br />";
			

}

if(!empty($_POST['order'])) //kontrola dopravy a státu - pouze při objednávce
{

   //print_r($_SESSION[$PFX]);

    //zjistíme ID státu podle zvolené dopravy 
    $query = 'SELECT id_stat FROM '.T_DOPRAVA.' WHERE id = '.$_SESSION[$PFX]['dopravne'].' LIMIT 1';
    $v = my_DB_QUERY($query,__LINE__,__FILE__);
    while($z = mysql_fetch_array($v))
    {
        $stat = $z['id_stat'];
    }
    
    if(!empty($_SESSION[$PFX]['p_stat'])){
        if($stat != $_SESSION[$PFX]['p_stat']) 
        $_SESSION['error'] .= "Neshoduje se <strong>Doprava a platba</strong> se státem doručení (Doručovací adresa) <br />";
    }
    else {
        if(!empty($_SESSION[$PFX]['f_stat']) AND $stat != $_SESSION[$PFX]['f_stat']) 
        $_SESSION['error'] .= "Neshoduje se <strong>Doprava a platba</strong> se státem doručení (Fakturační adresa) <br />";
    }
}




// registrace - jmeno a heslo
if(isset($_POST['login_name']) && empty($_POST['login_name'])) 
	$_SESSION['error'] .= "Vyplňte položku <strong>Přihlašovací jméno</strong> (Přihlašovací údaje)<br />";

if(isset($_POST['login_pass']) && strlen($_POST['login_pass']) < 5) 
	$_SESSION['error'] .= "<strong>Zadejte Heslo dlouhé min. 5 znaků</strong> (Přihlašovací údaje)<br />";

if(isset($_POST['login_pass']) && $_POST['login_pass'] != $_POST['login_pass2']) 
	$_SESSION['error'] .= "Nesouhlasí <strong>položky Heslo a Heslo pro kontrolu</strong> (Přihlašovací údaje)<br />";


// pobocka
if(isset($_POST['pobocka']) && empty($_POST['pobocka']) && $_POST['pobocka'] == 0) 
  $_SESSION['error'] .= "Vyberte pobočku<br />";



if(!empty($_SESSION['error'])) {
  $_SESSION['error']='<div class="error">'.$_SESSION['error'].'</div>';

	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// *****************************************************************************




?>
