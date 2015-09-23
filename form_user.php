<?php

$p_poznamka = "(vyplňte pokud se liší od fakturační)";

if(empty($buttonUID))$buttonUID='';


$arrayStaty=unserialize(STATY);

$statF='';
$statP='';
if(!isset($dopravne)) $dopravne = null;


if($disabledUID){

	$statF=$arrayStaty[$_SESSION[$PFX]['f_stat']]."<input type='hidden' name='f_stat' value='".$_SESSION[$PFX]['f_stat']."' />";
	
	if(empty($_SESSION[$PFX]['p_stat'])){
		$_SESSION[$PFX]['p_stat']=0;
		$arrayStaty[$_SESSION[$PFX]['p_stat']]='nevyplněn';		
	}
	
	$statP=$arrayStaty[$_SESSION[$PFX]['p_stat']]."<input type='hidden' name='p_stat' value='".$_SESSION[$PFX]['p_stat']."' />";	

}else{

	$optionsFStaty='';
	$optionsPStaty='';

	foreach($arrayStaty as $id=>$value){
		if($_SESSION[$PFX]['p_stat']==$id){
		     $selectedP='selected="selected"';
		}else{
			$selectedP='';
		}
		if($_SESSION[$PFX]['f_stat']==$id){
		     $selectedF='selected="selected"';
		}else{
			$selectedF='';
		}
		$optionsFStaty.='<option '.$selectedF.' value="'.$id.'">'.$value.'</option>';
		$optionsPStaty.='<option '.$selectedP.' value="'.$id.'">'.$value.'</option>';
	}

	$statP='
		<select name="p_stat">
			<option value="">nevyplněno</option>						
			'.$optionsPStaty.'
		</select>';
		
	$statF='
		<select name="f_stat" '.$disabledUID.'>
			'.$optionsFStaty.'
		</select>';		

}





$form_user = '
				<div class="info">
					Údaje označené <span class="red">*</span> jsou povinné.			
				</div>
				
				<table cellpadding="0" cellspacing="1" class="usertable">
				
				<tr>
					<th colspan="2">Fakturační adresa</th>
				</tr>
				
				<tr>
					<td class="first">Firma (jméno a příjmení) <span class="red">*</span></td>
					<td>'.$f_jmeno.'</td>
				</tr>

				<tr>
					<td class="first">Kontaktní osoba</td>
					<td>'.$f_kontakt.'</td>
				</tr>				
				
				<tr>
					<td class="first">Adresa <span class="red">*</span></td>
					<td>'.$f_adresa.'</td>
				</tr>
				
				<tr>
					<td class="first"> Město <span class="red">*</span></td>
					<td>
						'.$f_mesto.'
					</td>
				</tr>

				<tr>
					<td class="first">PSČ<span class="red">*</span></td>
					<td>
						'.$f_psc.'
					</td>
				</tr>
				
				<tr>
					<td class="first">Stát <span class="red">*</span></td>
					<td>
					'.$statF.'
					</td>
				</tr>				
				
				<tr>
					<td class="first">IČO</td>
					<td>'.$f_ico.'</td>
				</tr>
				
				<tr>
					<td class="first">DIČ</td>
					<td>'.$f_dic.'</td>
				</tr>
				
				<tr>
					<td class="first">E-mail <span class="red">*</span></td>
					<td>'.$f_mail.'</td>
				</tr>
				
				<tr>                          
					<td class="first">Telefon <span class="red">*</span></td>
					<td>'.$f_tel.'</td>
				</tr>
				
				</table>
				
				
				'.$buttonUID.'
				
				
				<table cellpadding="0" cellspacing="1" class="usertable">
				
				<tr>
					<th colspan="2">
						Adresa místa dodání zboží '.$p_poznamka.'</th>
				</tr>
				
				<tr>
					<td class="first">Firma (jméno a příjmení)</td>
					<td>'.$p_jmeno.'</td>
				</tr>
				
				<tr>
					<td class="first">Kontaktní osoba</td>
					<td>'.$p_kontakt.'</td>
				</tr>				
				
				<tr>
					<td class="first">Adresa</td>
					<td>'.$p_adresa.'</td>
				</tr>
				
				<tr>
					<td class="first">Město</td>
					<td>
						'.$p_mesto.'
					</td>
				</tr>

				<tr>
					<td class="first">PSČ</td>
					<td>
						'.$p_psc.'
					</td>
				</tr>
				
				<tr>
					<td class="first">Stát</td>
					<td>
					'.$statP.'
					</td>
				</tr>				
				
				
				</table>';




// '.$_SESSION[$PFX]['login_pass'].'
// '.$_SESSION[$PFX]['login_pass2'].'


$pristup = '
				
				<table cellpadding="0" cellspacing="1" class="usertable">
				
				<tr>
					<th colspan="2">
						Přihlašovací údaje</th>
				</tr>
				
				<tr>
					<td colspan="2"><strong>Jako přihlašovací jméno slouží Vámi uvedený e-mail.</strong></td>
				</tr>
				
				<tr>
					<td class="first">Heslo</td>
					<td>
						<input type="password" name="login_pass" value="" />
					</td>
				</tr>
				
				<tr>
					<td class="first">Heslo pro kontrolu</td>
					<td>
						<input type="password" name="login_pass2" value="" />
					</td>
				</tr>
				
				
				</table>';

$posli = NULL;

if(!isset($_SESSION['user']['UID']) AND $_GET["go"] == "basket")
{ // pouze pro neregistrovane a v kosiku
  $posli .= '
  <div class="newsletter_add">
    <input class="newsletter_add" type="checkbox" value="1" name="newsletter_add" checked="checked" /> <label for="newsletter_add">Přihlásit k odběru novinek</label>
  </div>
  ';
}

$posli .= '
<div class="buttons">
  <!--<label class="antis">ochrana<input type="text" value="" name="antis" class="antis" /></label>-->
   '.$dopravne.'
	<input type="submit" value="'.$bt_text.'" class="button_green" />
</div>
';

?>
