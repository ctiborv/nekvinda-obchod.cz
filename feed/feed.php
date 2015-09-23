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


header ("Content-Type:text/xml"); 


echo "<";
echo "?xml version=\"1.0\" encoding=\"utf-8\"?";
echo ">
<SHOP>";


$q = "
SELECT 
g.*,d.zbozi,p.name as vyrobce,p.dodani as vyrobce_dodani
from ".T_GOODS." g
LEFT JOIN ".T_DODACI_LHUTA." d on g.id_dodani=d.id
JOIN ".T_GOODS_X_CATEGORIES." gc on gc.id_good=g.id
JOIN ".T_CATEGORIES." c on c.id=gc.id_cat
LEFT JOIN ".T_PRODS." p on g.id_vyrobce=p.id
WHERE (p.hidden=0 or p.hidden is NULL)
AND c.hidden=0
AND g.hidden=0 
GROUP BY g.id
";




$v = my_DB_QUERY($q,__LINE__,__FILE__);

// Problematické znaky převedeme na správné.
$trans = array("Ö" => "O", "&#216;" => "pr.", " & " => "&amp;", chr(0x0) => "", chr(0x96) => chr(0x2D), "：" => ":" , "（" => "(" , "）" => ")" , "，" => "," , "-" => "-" , "-﻿" => "-"); // Problematické znaky předěláme na normální.

while ($z = mysql_fetch_assoc($v))
{
    if($z['cena'] > 0 and $z['hidden'] == 0)
    {
      $CENA = trim($z['cena']);
      if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $CENA = trim($z['cena_eshop']);
      
     
	    if(empty($z['zboziname']))$PRODUCT = strtr($z['name'], $trans);
	    else $PRODUCT = strtr($z['zboziname'], $trans);
      
      
      if(!empty($z['anotace'])) $DESCRIPTION = $z['anotace'];
      else $DESCRIPTION = $z['text'];
      
    	$DESCRIPTION = strtr($DESCRIPTION, $trans);
    	$DESCRIPTION = strip_tags($DESCRIPTION);
      
      if(!empty($z['kod'])) $KOD = ' Obj. '.$z['kod'].''; // zbozi.cz hlasi duplicitni polozky, kdyz se lisi jenom kodem zbozi
      else $KOD = '';
      
      $DESCRIPTION = strip_tags($DESCRIPTION);
      $DESCRIPTION = lenght_of_string(500,$DESCRIPTION,'');
      
      $URL = HTTP_ROOT.'/produkt/'.$z['id'].'-'.text_in_url($PRODUCT).'/';
      
      if($z['img'] != '' && file_exists('./UserFiles/products/original/'.$z['id'].'.'.$z['img'])){
	 	    $IMGURL = HTTP_ROOT.'/UserFiles/products/original/'.$z['id'].'.'.$z['img'];
      }else $IMGURL = '';
      
      
	    $TOLLFREE=1;
      if($z['zboziplaceny']==1)$TOLLFREE=0;
	 
      
	    $PRICE_VAT = round($CENA , 2); // cena s DPH
      $VAT = $z['dph']/100; // 0.19, 0.09 ...
      $PRICE = ceny($PRICE_VAT,$z['dph'],1); // cena bez DPH
      
      $MANUFACTURER = $z['vyrobce'];
      
      if($z['zbozi']==0 or !empty($z['zbozi'])){
	 	    $DODANI=$z['zbozi'];
	    }else{
        $DODANI = $z['vyrobce_dodani'];
      }
	 


	    if(empty($DODANI))$DODANI=0;
      
      // podle promenne v url zvolime sablonu
      echo '
	<SHOPITEM>
		<PRODUCT>'.$PRODUCT.' '.$z['kod'].'</PRODUCT>
		<DESCRIPTION>'.$DESCRIPTION.'</DESCRIPTION>
		<URL>'.$URL.'</URL>
		<IMGURL>'.$IMGURL.'</IMGURL>
		<PRICE>'.$PRICE[3].'</PRICE>
		<VAT>'.$VAT.'</VAT>
		<DELIVERY_DATE>'.$DODANI.'</DELIVERY_DATE>
		<MANUFACTURER>'.strtr($MANUFACTURER, $trans).'</MANUFACTURER>
		<TOLLFREE>1</TOLLFREE>
	</SHOPITEM>';

    
    } 
}

echo '
</SHOP>';

?>