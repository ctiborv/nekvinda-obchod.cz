<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


include_once($_SERVER['DOCUMENT_ROOT']."/admin/_mysql.php");


/**
Na požadovanou pozici vložím požadovaný řetězec.
@param (string) str
@param (int) pos
@param (string) insertstr
@return (string) str
*/
function stringInsert($str, $pos, $insertstr)
{
  if(!is_array($pos)) $pos = array($pos);

  $offset = -1;
  foreach($pos as $p)
  {
    $offset++;
    $str = substr($str, 0, $p+$offset) . $insertstr . substr($str, $p+$offset);
  }

  return $str;
}


$query = "
SELECT id , kod , kod3 , kod4 , kod5 , kod6 , kod7 , kod8
FROM ".T_GOODS."
WHERE kod != ''
AND ".SQL_C_LANG;
$v = my_DB_QUERY($query,__LINE__,__FILE__);

while($z = mysql_fetch_assoc($v))
{
  $kod = $z["kod"];
  $pos = strpos($kod, ".");

  if(is_numeric($kod) AND $pos === false)
  { // Vytvořím možné varianty všech číselných kódů.
    $kod3 = $kod4 = $kod5 = $kod6 = $kod7 = $kod8 = NULL;

    if(strlen($kod) == 6)
    { // XXXXXX = XX XXXX = XX-XXXX
      $kod3 = stringInsert($kod, 2, " ");
      $kod4 = stringInsert($kod, 2, "-");

      if(
        $kod3 == $z["kod3"] AND
        $kod4 == $z["kod4"]
      ) continue; // Stejné kódy neukládám.
    }
    else if(strlen($kod) == 8)
    {
      $kod3 = stringInsert($kod, 2, " "); // xx xxx xxx
      $kod3 = stringInsert($kod3, 6, " ");

      $kod4 = stringInsert($kod, 2, "-"); // xx-xxx-xxx
      $kod4 = stringInsert($kod4, 6, "-"); 

      $kod5 = stringInsert($kod, 2, "."); // xx.xxx.xxx
      $kod5 = stringInsert($kod5, 6, ".");

      $kod6 = stringInsert($kod, 4, " "); // xxxx xxxx
      $kod7 = stringInsert($kod, 4, "-"); // xxxx-xxxx
      $kod8 = stringInsert($kod, 4, "."); // xxxx.xxxx

      if(
        $kod3 == $z["kod3"] AND
        $kod4 == $z["kod4"] AND
        $kod5 == $z["kod5"] AND
        $kod6 == $z["kod6"] AND
        $kod7 == $z["kod7"] AND
        $kod8 == $z["kod8"]
      ) continue; // Stejné kódy neukládám.
    }
    else
    { // Upravuju jen kódy o délce 6 a 8.
      continue;
    }

    $kody[$z["id"]] = array( // kod a kod2 plní iSoft viz. \i_e\XMLvyrobky.php
      "kod3" => $kod3,
      "kod4" => $kod4,
      "kod5" => $kod5,
      "kod6" => $kod6,
      "kod7" => $kod7,
      "kod8" => $kod8
    );
  }
}


if(!empty($kody))
{ // Uložím vytvořené kódy.
  foreach($kody as $id => $kod)
  {
    $query = "
    UPDATE ".T_GOODS."
    SET
    kod3 = '".$kod["kod3"]."',
    kod4 = '".$kod["kod4"]."',
    kod5 = '".$kod["kod5"]."',
    kod6 = '".$kod["kod6"]."',
    kod7 = '".$kod["kod7"]."',
    kod8 = '".$kod["kod8"]."'
    WHERE id = '".$id."'
    ";
    my_DB_QUERY($query,__LINE__,__FILE__);
  }
}

exit("OK");

?>