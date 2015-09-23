<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


include_once($_SERVER['DOCUMENT_ROOT']."/admin/_mysql.php");


/**
Kurzeura oproti koruně.
@return (decimal) euro
*/
function get_euro()
{
  //unset($_SESSION["cache"]["get_euro"]); // Při každém volání načteme euro.
  if(isset($_SESSION["cache"]["get_euro"]) AND !empty($_SESSION["cache"]["get_euro"]))
  { // Při prvním volání načteme euro při dalším používáme hodnotu uloženou v cache paměti.
    return $_SESSION["cache"]["get_euro"];
  }

	$query = "SELECT euro FROM ".T_KURZ;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);

  if(isset($z["euro"]))
  {
    $euro = strtr($z["euro"] , array("," => "."));
    $_SESSION["cache"]["get_euro"] = $euro;

    return $euro;
  }
  else
  {
    return 0;
  }
}


/**
Převod koruny na euro.
@param(decimal) kc
@return (decimal) euro
*/
function kc_na_eura($kc)
{
  $kc = strtr(trim($kc) , array("," => ".", " " => ""));
  $euro = get_euro(); // Kurz eura

  if($euro <= 0) return 0;

  return $kc / $euro;
}

?>