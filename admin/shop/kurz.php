<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


include_once($_SERVER['DOCUMENT_ROOT']."/admin/_mysql.php");
include_once($_SERVER['DOCUMENT_ROOT']."/admin/_functions.php");


if(!empty($_POST))
{ // Ulozeni 
	$query = "SELECT euro FROM ".T_KURZ;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);

  $euro = strtr($_POST["euro"] , array("," => "."));
  $query_set = "
  euro = '".trim($euro)."'
  ";

  if(!isset($z["euro"]))
  {
    $query = "INSERT INTO ".T_KURZ." SET ".$query_set;
  }
  else
  {
    $query = "UPDATE ".T_KURZ." SET ".$query_set;
  }
	my_DB_QUERY($query,__LINE__,__FILE__);


	$_SESSION['alert_js'] = "Záznam uložen";
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}


$query = "SELECT euro FROM ".T_KURZ;
$v = my_DB_QUERY($query,__LINE__,__FILE__);
$z = mysql_fetch_assoc($v);

$nadpis = 'Nastavení kurzů';

$data = '
<form action="" method="post" onSubmit="">
  <table width="650" border="0" cellspacing="5" cellpadding="0">
    <tr>
  		<td width="160">
  			EURO (&euro;)
      </td>
  		<td>
        <input type="text" name="euro" value="'.((isset($z["euro"])) ? $z["euro"] : "").'"> Kč</td>
  		</td>
  	</tr>

  	<tr>
  		<td colspan="2">
        <br>
        <br>
  			'.SAVE_BUTTON.'
  		</td>
  	</tr>
	</table>
</form>
';
	

?>