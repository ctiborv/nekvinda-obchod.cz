<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


// INFO O FEEDECH pro jednotlive servery na konci dokumentu
// sablony pro jednotlive servery se vyberou podle promenne v url

error_reporting(0);


include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');
// zjistime pro jakou domenu je feed pozadovan - viz _functions.php
include_once($_SERVER['DOCUMENT_ROOT'].'/_functions.php');


/*
<SHOPITEM>
<PRODUCT>ACER P225HQ</PRODUCT>
<DESCRIPTION>LCD monitor s Full HD rozlišením</DESCRIPTION>
<URL>http://www.obchod.cz/acer-p225hq/</URL>
<ITEM_TYPE>new</ITEM_TYPE>
<DELIVERY_DATE>1</DELIVERY_DATE>
<IMGURL>http://obchod.cz/obrazky/acer-p225hq.jpg</IMGURL>
<PRICE>2500</PRICE>
<PRICE_VAT>3000</PRICE_VAT>
</SHOPITEM>

<SHOPITEM>
<PRODUCT>Podložka pod myš - kočka</PRODUCT>
<DESCRIPTION>Ergonomická podložka pod myš, potisk s .</DESCRIPTION>
<DUES>20</DUES>
<ITEM_TYPE>bazaar</ITEM_TYPE>
<DELIVERY_DATE>0</DELIVERY_DATE>
<SHOP_DEPOTS>111</SHOP_DEPOTS>
<IMGURL>http://obchod.cz/obrazky/podlozky-pod-mys/kocka.jpg</IMGURL>
<PRICE>420</PRICE>
<PRICE_VAT>512</PRICE_VAT>
<UNFEATURED>1</UNFEATURED>
</SHOPITEM>
*/


header ("Content-Type:text/xml"); 


echo "<";
echo "?xml version=\"1.0\" encoding=\"utf-8\"?";
echo ">
<SHOP>";


$q = "
SELECT 
g.*,
d.zbozi,
p.name AS vyrobce, p.dodani AS vyrobce_dodani,
c.id AS id_cat, c.name AS name_cat
from ".T_GOODS." g
LEFT JOIN ".T_DODACI_LHUTA." d on g.id_dodani=d.id
JOIN ".T_GOODS_X_CATEGORIES." gc on gc.id_good=g.id
JOIN ".T_CATEGORIES." c on c.id=gc.id_cat
LEFT JOIN ".T_PRODS." p on g.id_vyrobce=p.id
WHERE (p.hidden=0 or p.hidden is NULL)
AND g.hidden = 0
AND g.cena > 0
AND c.hidden = 0
AND c.export_zbozi = 0
GROUP BY g.id
";
$v = my_DB_QUERY($q,__LINE__,__FILE__);

// Problematické znaky převedeme na správné.
$trans = array("Ö" => "O", "&#216;" => "pr.", " & " => "&amp;", chr(0x0) => "", chr(0x96) => chr(0x2D), "：" => ":" , "（" => "(" , "）" => ")" , "，" => "," , "-" => "-" , "-﻿" => "-"); // Problematické znaky předěláme na normální.
$trans_cislo = array("," => ".", " " => "");

while($z = mysql_fetch_assoc($v))
{
  $id_cat = intval($z['id_cat']);

  if(isset($_SESSION["FEED"]["ZBOZI"][$id_cat]) AND !empty($_SESSION["FEED"]["ZBOZI"][$id_cat]))
  { // Data jsou v cache paměti.
    $CATEGORYTEXT = $_SESSION["FEED"]["ZBOZI"][$id_cat]["CATEGORYTEXT"];
    $ZBOZI_CPC = $_SESSION["FEED"]["ZBOZI"][$id_cat]["ZBOZI_CPC"];
    $EXPORT = $_SESSION["FEED"]["ZBOZI"][$id_cat]["EXPORT"];
  }
  else
  {
    $zbozi = zbozi($id_cat);

    // Uložení dat do cache paměti.
    $CATEGORYTEXT = $_SESSION["FEED"]["ZBOZI"][$id_cat]["CATEGORYTEXT"] = trim(strtr($zbozi["categorytext"], $trans));
    $ZBOZI_CPC = $_SESSION["FEED"]["ZBOZI"][$id_cat]["ZBOZI_CPC"] = trim($zbozi["cpc_zbozi"]);
    $EXPORT = $_SESSION["FEED"]["ZBOZI"][$id_cat]["EXPORT"] = trim($zbozi["export_zbozi"]);
  }

  if($EXPORT == 1) continue; // Produkt se na zboží neexportuje, některá z nadřazených kategorií nemá povolená přenos.

  $CATEGORYTEXT = strtr($CATEGORYTEXT, $trans);

  if(isset($z["cpc_zbozi"]) AND $z["cpc_zbozi"] > 0) $ZBOZI_CPC = $z["cpc_zbozi"]; // CPC je nastavené přímo u produktu.
  if($ZBOZI_CPC > 500) $ZBOZI_CPC == 500; // 1 až 500 Kč. na http://napoveda.seznam.cz/cz/specifikace-xml.html#MAX_CPC
  if($ZBOZI_CPC < 1) $ZBOZI_CPC == 0;
  $ZBOZI_CPC = strtr($ZBOZI_CPC, $trans_cislo); //Převedu číslo na správný formát.
  if($ZBOZI_CPC == 0) $ZBOZI_CPC = "";

	if(empty($z['zboziname']))$PRODUCT = strtr($z['name'], $trans);
  else $PRODUCT = strtr($z['zboziname'], $trans);
      
  if(!empty($z['anotace'])) $DESCRIPTION = $z['anotace'];
  else $DESCRIPTION = $z['text'];
  $DESCRIPTION = strtr($DESCRIPTION, $trans);
  $DESCRIPTION = strip_tags($DESCRIPTION);
  $DESCRIPTION = lenght_of_string(500,$DESCRIPTION,'');

  if(!empty($z['kod'])) $KOD = ' Obj. '.$z['kod'].''; // zbozi.cz hlasi duplicitni polozky, kdyz se lisi jenom kodem zbozi
  else $KOD = '';

  $URL = HTTP_ROOT.'/produkt/'.$z['id'].'-'.text_in_url($PRODUCT).'/';

  if($z['img'] != '' AND file_exists($_SERVER['DOCUMENT_ROOT'].'/UserFiles/products/original/'.$z['id'].'.'.$z['img']))
  {
	  $IMGURL = HTTP_ROOT.'/UserFiles/products/original/'.$z['id'].'.'.$z['img'];
  }
  else $IMGURL = '';

  $CENA = trim($z['cena']);
  if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $CENA = trim($z['cena_eshop']);
  $PRICE_WITHOUT_VAT = round($CENA , 2); // cena bez DPH
  $VAT = $z['dph']/100; // 0.19, 0.09 ...

  $MANUFACTURER = $z['vyrobce'];

  if($z['zbozi'] == 0 or !empty($z['zbozi'])) { $DODANI = $z['zbozi']; }
  else { $DODANI = $z['vyrobce_dodani']; }
  if($z["pocet_kusu"] > 0) { $DODANI = 0; } // Skutečné množství na skladě.
  if(empty($DODANI)) { $DODANI = 0; }

  // podle promenne v url zvolime sablonu
  echo '
	<SHOPITEM>
		<PRODUCT>'.trim($PRODUCT.' '.$z['kod']).'</PRODUCT>
		<DESCRIPTION>'.$DESCRIPTION.'</DESCRIPTION>
		<URL>'.$URL.'</URL>
		<IMGURL>'.$IMGURL.'</IMGURL>
		<PRICE>'.$PRICE_WITHOUT_VAT.'</PRICE>
		<VAT>'.$VAT.'</VAT>
		<DELIVERY_DATE>'.$DODANI.'</DELIVERY_DATE>
		<MANUFACTURER>'.strtr($MANUFACTURER, $trans).'</MANUFACTURER>
		<UNFEATURED>'.$z['uprednostnit_zbozi'].'</UNFEATURED>
    <CATEGORYTEXT>'.$CATEGORYTEXT.'</CATEGORYTEXT>
    <MAX_CPC>'.$ZBOZI_CPC.'</MAX_CPC>
	</SHOPITEM>';
}

echo '</SHOP>';

unset($_SESSION["FEED"]["ZBOZI"]);


/**
Pole s informacemi o nadřazených kategoriích
@param (int) id_cat
@param (string) (cat)
@return (array) zbozi - print_r($zbozi);
*/
function zbozi($id_cat, $zbozi = "")
{
	$query = "
  SELECT name , id_parent , export_zbozi , cpc_zbozi
  FROM ".T_CATEGORIES."
	WHERE id = '".intval($id_cat)."'
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while($z = mysql_fetch_assoc($v))
  {
    if(!empty($zbozi["categorytext"])) { $categorytext = trim($z["name"])." | ".trim($zbozi["categorytext"]); }
    else { $categorytext = trim($z['name']); }
    $zbozi["categorytext"] = $categorytext;

    if(!isset($zbozi["export_zbozi"])) { $zbozi["export_zbozi"] = 0; }
    if($zbozi["export_zbozi"] == 0 AND $z["export_zbozi"] == 1) { $zbozi["export_zbozi"] = $z["export_zbozi"]; }

    if(!isset($zbozi["cpc_zbozi"])) { $zbozi["cpc_zbozi"] = 0; }
    if($zbozi["cpc_zbozi"] == 0 AND $z["cpc_zbozi"] > 0) { $zbozi["cpc_zbozi"] = $z["cpc_zbozi"]; }

		if($z["id_parent"] > 0) { return zbozi($z["id_parent"], $zbozi); }
    else { return $zbozi; }
	}
}

?>