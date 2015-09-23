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
<SHOP>
  <SHOPITEM>
    <ITEM_ID>AB123</ITEM_ID>
    <PRODUCTNAME>Nokia 5800 XpressMusic</PRODUCTNAME>
    <PRODUCT>Nokia 5800 XpressMusic + pouzdro zdarma</PRODUCT>
    <DESCRIPTION>Klasický s plným dotykovým uživatelským rozhraním</DESCRIPTION>
    <URL>http://obchod.cz/mobily/nokia-5800-xpressmusic</URL>
    <IMGURL>http://obchod.cz/mobily/nokia-5800-xpressmusic/obrazek.jpg</IMGURL>
    <IMGURL_ALTERNATIVE>http://obchod.cz/mobily/nokia-5800-xpressmusic/obrazek2.jpg</IMGURL_ALTERNATIVE>
    <VIDEO_URL>http://www.youtube.com/watch?v=KjR759oWF7w</VIDEO_URL>
    <PRICE_VAT>6000</PRICE_VAT>
    <HEUREKA_CPC>5,8</HEUREKA_CPC>
    <MANUFACTURER>NOKIA</MANUFACTURER>
    <CATEGORYTEXT>Elektronika | Mobilní telefony</CATEGORYTEXT>
    <EAN>6417182041488</EAN>
    <PRODUCTNO>RM-559394</PRODUCTNO>
    <PARAM>
      <PARAM_NAME>Barva</PARAM_NAME>
      <VAL>černá</VAL>
    </PARAM>
    <DELIVERY_DATE>2</DELIVERY_DATE>
    <DELIVERY>
      <DELIVERY_ID>CESKA_POSTA</DELIVERY_ID>
      <DELIVERY_PRICE>120</DELIVERY_PRICE>
      <DELIVERY_PRICE_COD>120</DELIVERY_PRICE_COD>
    </DELIVERY>
    <DELIVERY>
      <DELIVERY_ID>PPL</DELIVERY_ID>
      <DELIVERY_PRICE>90</DELIVERY_PRICE>
      <DELIVERY_PRICE_COD>120</DELIVERY_PRICE_COD>
    </DELIVERY>
    <ITEMGROUP_ID>EF789</ITEMGROUP_ID>
    <ACCESSORY>CD456</ACCESSORY>
  </SHOPITEM>
  <SHOPITEM>
  ...
  </SHOPITEM>
</SHOP>
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
FROM ".T_GOODS." g
LEFT JOIN ".T_DODACI_LHUTA." d on g.id_dodani = d.id
JOIN ".T_GOODS_X_CATEGORIES." gc on gc.id_good = g.id
JOIN ".T_CATEGORIES." c on c.id = gc.id_cat
LEFT JOIN ".T_PRODS." p on g.id_vyrobce = p.id
WHERE (p.hidden = 0 OR p.hidden IS NULL)
AND g.hidden = 0
AND g.cena > 0
AND c.hidden = 0
AND c.export_heureka = 0
GROUP BY g.id
";
$v = my_DB_QUERY($q,__LINE__,__FILE__);

// Problematické znaky převedeme na správné.
$trans = array("Ö" => "O", "&#216;" => "pr.", " & " => "&amp;", chr(0x0) => "", chr(0x96) => chr(0x2D), "：" => ":" , "（" => "(" , "）" => ")" , "，" => "," , "-" => "-" , "-﻿" => "-"); // Problematické znaky předěláme na normální.
$trans_cislo = array("," => ".", " " => "");

while($z = mysql_fetch_assoc($v))
{
  $id_cat = intval($z['id_cat']);

  if(isset($_SESSION["FEED"]["HEUREKA"][$id_cat]) AND !empty($_SESSION["FEED"]["HEUREKA"][$id_cat]))
  { // Data jsou v cache paměti.
    $CATEGORYTEXT = $_SESSION["FEED"]["HEUREKA"][$id_cat]["CATEGORYTEXT"];
    $HEUREKA_CPC = $_SESSION["FEED"]["HEUREKA"][$id_cat]["HEUREKA_CPC"];
    $EXPORT = $_SESSION["FEED"]["HEUREKA"][$id_cat]["EXPORT"];
  }
  else
  {
    $heureka = heureka($id_cat);

    // Uložení dat do cache paměti.
    $CATEGORYTEXT = $_SESSION["FEED"]["HEUREKA"][$id_cat]["CATEGORYTEXT"] = trim(strtr($heureka["categorytext"], $trans));
    $HEUREKA_CPC = $_SESSION["FEED"]["HEUREKA"][$id_cat]["HEUREKA_CPC"] = trim($heureka["cpc_heureka"]);
    $EXPORT = $_SESSION["FEED"]["HEUREKA"][$id_cat]["EXPORT"] = trim($heureka["export_heureka"]);
  }

  if($EXPORT == 1) continue; // Produkt se na heuréku neexportuje, některá z nadřazených kategorií nemá povolená přenos.

  $CATEGORYTEXT = strtr($CATEGORYTEXT, $trans);

  if(isset($z["cpc_heureka"]) AND $z["cpc_heureka"] > 0) $HEUREKA_CPC = $z["cpc_heureka"]; // CPC je nastavené přímo u produktu.
  if($HEUREKA_CPC > 100) $HEUREKA_CPC == 100; // Maximální cena za proklik podle specifikace na http://sluzby.heureka.cz/napoveda/xml-feed/
  $HEUREKA_CPC = strtr($HEUREKA_CPC, $trans_cislo); //Převedu číslo na správný formát.
  if($HEUREKA_CPC == 0) $HEUREKA_CPC = "";

	if(empty($z['zboziname']))$PRODUCT = strtr($z['name'], $trans);
	else $PRODUCT = strtr($z['zboziname'], $trans);
      
  if(!empty($z['kod'])) $KOD = ' Obj. '.$z['kod'].''; // zbozi.cz hlasi duplicitni polozky, kdyz se lisi jenom kodem zbozi
  else $KOD = '';

  if(!empty($z['anotace'])) $DESCRIPTION = $z['anotace'];
  else $DESCRIPTION = $z['text'];
  $DESCRIPTION = strip_tags($DESCRIPTION);
  $DESCRIPTION = strtr($DESCRIPTION, $trans);
  $DESCRIPTION = lenght_of_string(500,$DESCRIPTION,'');
      
  $URL = HTTP_ROOT.'/produkt/'.$z['id'].'-'.text_in_url($PRODUCT).'/';
      
  if($z['img'] != '' AND file_exists($_SERVER['DOCUMENT_ROOT'].'/UserFiles/products/original/'.$z['id'].'.'.$z['img']))
  {
	  $IMGURL = HTTP_ROOT.'/UserFiles/products/original/'.$z['id'].'.'.$z['img'];
  }
  else $IMGURL = '';
      
  $CENA = trim($z['cena']); // Cena bez DPH
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
    <ITEM_ID>'.$z["id"].'</ITEM_ID>
		<PRODUCT>'.trim($PRODUCT.' '.$z['kod']).'</PRODUCT>
		<DESCRIPTION>'.$DESCRIPTION.'</DESCRIPTION>
		<URL>'.$URL.'</URL>
		<IMGURL>'.$IMGURL.'</IMGURL>
		<PRICE>'.$PRICE_WITHOUT_VAT.'</PRICE>
		<VAT>'.$VAT.'</VAT>
		<DELIVERY_DATE>'.$DODANI.'</DELIVERY_DATE>
    <HEUREKA_CPC>'.$HEUREKA_CPC.'</HEUREKA_CPC>
		<MANUFACTURER>'.strtr($MANUFACTURER, $trans).'</MANUFACTURER>
    <CATEGORYTEXT>'.$CATEGORYTEXT.'</CATEGORYTEXT>
	</SHOPITEM>';
}

echo '</SHOP>';

unset($_SESSION["FEED"]["HEUREKA"]);


/**
Pole s informacemi o nadřazených kategoriích
@param (int) id_cat
@param (string) (cat)
@return (array) heureka - print_r($heureka);
*/
function heureka($id_cat, $heureka = "")
{
	$query = "
  SELECT name , id_parent , export_heureka , cpc_heureka
  FROM ".T_CATEGORIES."
	WHERE id = '".intval($id_cat)."'
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while($z = mysql_fetch_assoc($v))
  {
    if(!empty($heureka["categorytext"])) { $categorytext = trim($z["name"])." | ".trim($heureka["categorytext"]); }
    else { $categorytext = trim($z['name']); }
    $heureka["categorytext"] = $categorytext;

    if(!isset($heureka["export_heureka"])) { $heureka["export_heureka"] = 0; }
    if($heureka["export_heureka"] == 0 AND $z["export_heureka"] == 1) { $heureka["export_heureka"] = $z["export_heureka"]; }

    if(!isset($heureka["cpc_heureka"])) { $heureka["cpc_heureka"] = 0; }
    if($heureka["cpc_heureka"] == 0 AND $z["cpc_heureka"] > 0) { $heureka["cpc_heureka"] = $z["cpc_heureka"]; }
    

		if($z["id_parent"] > 0) { return heureka($z["id_parent"], $heureka); }
    else { return $heureka; }
	}
}

?>