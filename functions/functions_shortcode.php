<?php


/**
Funkce pro praci s shotcode.
@param (string) shotcode = retezec shotcode
@return (array) param = pole s parametry nazev => hodnota
*/
function get_shortcode_param($shotcode)
{
  // odstranime prebytecne znaky
  $trans = array("&nbsp;" => "" , "@@@" => "");
  $shotcode = strtr($shotcode , $trans);

  $parametry = explode(" ", $shotcode); // parametry jsou oddeleny mezerou

  $nazev_stitku = $parametry[0]; // nazev stitku musi byt na prvnim miste
  unset($parametry[0]); // tento parametr uz neprochazime

  $param["nazev_stitku"] = $nazev_stitku; // pole s parametry stitku

  foreach($parametry as $parametr)
  { // projdeme vsechny parametry
    if(empty($parametr)) continue; // pouze neprazdne parametry

    if(strpos($parametr , "="))
    { // parametr musi obsahovat nazev = hodnota
      list($nazev_parametru , $hodnota) = explode("=" , $parametr);
      $param[$nazev_parametru] = $hodnota;
    }
  }

  return $param;
}


/**
Odstraním z textu všechny shortcody
@param (string) text - předává se referencí
*/
function delete_shortcode(&$text)
{
  $text = preg_replace('/@@@.*@@@/', '', $text);
}


/**
Nahradím shortcode z editoru validním HMTL.
@param (string) text - předává se referencí
*/
function valid_shortcode(&$text)
{
  $trans = array("<p>@@@" => "@@@" , "@@@</p>" => "@@@"); // Editor obaluje shortcode do <p>. Nahrazujeme kvůli validitě.
  $text = strtr($text, $trans);
}


/**
Hledá zadaý shortcode v textu.
@param (string) shortcode
@param (string) text
@return (array) shortcodes - všechny nalezené shortcody.
*/
function get_shortcode($shortcode , $text)
{
  preg_match_all('/@@@'.$shortcode.'.*@@@/i' , strip_tags($text) , $shortcodes); // vypreparovani shortcode (preg_match_all - vsechny, preg_match - prvni)

  return $shortcodes[0];
}


?>