<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.

function nactiSEO() 
{
	$query = "SELECT * FROM ".T_SETTING." where lang=".C_LANG." LIMIT 0 , 1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
  if($z = mysql_fetch_array($v))
  {
		$form_data['title'] = $z['title'];
    $form_data['keywords'] = $z['keywords'];
    $form_data['description'] = $z['description'];
    $form_data['overovaci'] = $z['overovaci'];
    $form_data['gaas'] = $z['gaas'];
    $form_data['gatr'] = $z['gatr'];
    $form_data['foot'] = $z['foot'];

    return $form_data;
  } 
  else
  {
    return 0;
  } 
}

// *****************************************************************************
// formular pro editaci
// *****************************************************************************
function form() {

  $form_data = nactiSEO();

  if($form_data == 0)
  {  // Pokud pro danou jazykovou mutaci dosud nebyly nastaveny zadne parametry
    $query = "INSERT INTO ".T_SETTING_SEO." (lang,title,keywords,description,overovaci,gaas,gatr,foot) VALUES (".C_LANG.",'','','','','','','')";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);
    
    $form_data = nactiSEO();
  }
 	
	$form = '
		
<p style="color:red; font-weight:bold; font-size:110%; margin-top:0px; margin-left:5px; padding-bottom:10px;">Nenastavujte, neovládáte-li SEO a GA!</p>

      <script type="text/javascript"> 
        //<![CDATA[	
       function nastaveni(formular) {
        return window.confirm("Opravdu chcete provést změny?");       
       }
      // ]]> 
      </script>
	
	<form action="" method="post" onSubmit="return nastaveni(this);">
	
	<table width="650" border="0" cellspacing="5" cellpadding="0">
	
  <tr>
		<td width="160">
			<b>Výchozí hodnoty</b></td>
		<td width="">
    &nbsp;
		</td>
	</tr>
	
  <tr>
		<td width="160">
			TITLE </td>
		<td width="">
			<input type="text" name="title" value="'.$form_data['title'].'" 
			style="width: 100%;" class="f10"></td>
	</tr>	
  	
  <tr>
		<td width="160">
			KEYWORDS </td>
		<td width="">
			<input type="text" name="keywords" value="'.$form_data['keywords'].'" 
			style="width: 100%;" class="f10"></td>
	</tr>	
	
	<tr>
		<td width="160">
			DESCRIPTION </td>
		<td width="">
			<input type="text" name="description" value="'.$form_data['description'].'" 
			style="width: 100%;" class="f10"></td>
	</tr>

 <tr>
		<td width="160">
			Foot </td>
		<td width="">
			<textarea name="foot" style="width:100%" rows="6">'.$form_data['foot'].'</textarea></td>
	</tr>

 <tr>
		<td width="160">
			Ověřovací kód </td>
		<td width="">
			<textarea name="overovaci" style="width:100%" rows="2">'.$form_data['overovaci'].'</textarea></td>
	</tr>
	
	<tr>
		<td width="160">&nbsp;</td>
		<td width="0">&nbsp;</td>
	</tr>
	
	<tr>
		<td width="160">
			<b>Google analytics</b></td>
		<td width="">
    &nbsp;
		</td>
	</tr>
		
	<tr>
		<td width="160">
			GA-asynchronní </td>
		<td width="">
			<textarea name="gaas" style="width:100%" rows="9">'.$form_data['gaas'].'</textarea></td>
	</tr>
	
	<tr>
		<td width="160">
			 </td>
		<td>
		<table>
<tr><td style="vertical-align:top;">Poznámka: </td><td>Kód asynchronní je generován bezprostředně před koncovou značku &lt;/head&gt;.</td></tr>
<tr><td></td><td>Pouze jeden skript.</td></tr>
		</table>
  </td>
  </tr>
  
	<tr>
		<td width="160">
			GA-tradiční </td>
		<td width="">
			<textarea name="gatr" style="width:100%" rows="9">'.$form_data['gatr'].'</textarea></td>
	</tr>

	<tr>
		<td width="160">
			</td>
		<td>
		<table>
<tr><td style="vertical-align:top;">Poznámka: </td><td>Kód tradiční je generován bezprostředně před koncovou značku &lt;/body&gt;.</td></tr>
<tr><td></td><td>Rozdělen do dvou skriptů. Konči řetězcem "} catch(err) {}".</td></tr>
		</table>
  </td>
  </tr>

	<tr>
		<td colspan="2"><br /><br /><br />
			
			'.SAVE_BUTTON.'
		
		</td>
	</tr>
	
	</table>

	</form>';
	
	return $form;

}
// *****************************************************************************
// formular pro editaci
// *****************************************************************************

if(!empty($_POST))
{ // Ulozeni nastaveni SEO
		$query = "UPDATE ".T_SETTING." SET  
		title = '".trim($_POST['title'])."',
    keywords = '".trim($_POST['keywords'])."', 
		description = '".trim($_POST['description'])."',
		overovaci = '".mysql_escape_string(trim($_POST['overovaci']))."',
    gaas = '".mysql_escape_string(trim($_POST['gaas']))."', 
    gatr = '".mysql_escape_string(trim($_POST['gatr']))."',
    foot = '".mysql_escape_string(trim($_POST['foot']))."'
		WHERE lang = ".C_LANG."";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$_SESSION['alert_js'] = "Záznam uložen";
}

if($_GET['a'] == "setting")
{
  $data = form();
}

?>