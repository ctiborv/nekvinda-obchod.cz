<?php


include_once($_SERVER['DOCUMENT_ROOT']."/admin/_mysql.php");
include_once($_SERVER['DOCUMENT_ROOT']."/functions/functions_shortcode.php");


/**
Funkce vrati slider
@param id_slider (int) - id slideru, ktery chceme promitat, pokud neni vyplneno vracime defaultni slider
@return - div s fotkami pokud je jinak nic
*/
function get_slider($id_slider = NULL)
{
  global $jazyk;

  if($id_slider < 1)
  {
    $query = "SELECT id FROM ".T_SLIDER." WHERE default_slider = 1";
    $v = my_DB_QUERY($query , __LINE__ , __FILE__);
    $z = mysql_fetch_assoc($v);
    $id_slider = $z['id'];
  }

  $query = "SELECT name , link , title , text FROM ".T_SLIDER_FOTO." WHERE id_slider='".$id_slider."' ORDER BY poradi";
  $v = my_DB_QUERY($query , __LINE__ , __FILE__);

  $slider = '';
  $slider_ul = '';
  $pocet_slidu = 0;
  while($z = mysql_fetch_assoc($v))
  { // vytvoreni jednotlivych slidu ve slideru
    $name = $z["name"];
    $slide = "";

    $cesta_k_obrazku = "http://".$_SERVER["SERVER_NAME"].'/UserFiles/slider/'.$name; // url obrazku

    // zjistime velikost obrazku
    $velikost_obrazku = getimagesize($cesta_k_obrazku);
    $sirka_obrazku = $velikost_obrazku[0];
    $vyska_obrazku = $velikost_obrazku[1];

    // obrazek v html forme (pro vlozeni do stranky)
    $html_obrazek = '<img src="'.$cesta_k_obrazku.'" alt="'.$name.'" style="width:'.$sirka_obrazku.'px; height:'.$vyska_obrazku.'px;" />';

    $title = '';
    if(!empty($z["title"]))
    { // Nadpis slideru.
      $title = '
      <h2 class="title">
        '.$z["title"].'
      </h2>
      ';
    }

    $text = '';
    if(!empty($z["text"]))
    { // Krátký text ke slideru.
      $text = '
      <div class="text">
        '.nl2br($z["text"]).'
      </div>
      ';
    }

    $link = '';
/* Tlačítko je v této šabloně vypnuté.
    if(!empty($z["link"]))
    { // Button.
      $link = '
      <div class="link">
        <div>'.$_SESSION[$jazyk]['Vice_informaci'].'<span></span></div>
      </div>
      ';
    }
*/
    // Sestavení slidu.
    $slide = $title.$text.$link;

    // obaleni slidu (aby fungovalo stridani slidu)
    $slide = '
    <div class="slide" style="width:'.$sirka_obrazku.'px; height:'.$vyska_obrazku.'px; background-image:url(\''.$cesta_k_obrazku.'\'); background-repeat:no-repeat;">
      '.$slide.'
    </div>
    ';

    if(!empty($z["link"]))
    { // Slide s odkazem.
      $slide = '
        <a href="'.$z["link"].'" title="">
          '.$slide.'
        </a>
      ';
    }

    $slide_li = '
    <li>
      '.$slide.'
    </li>
    ';

    // pridani slidu do slideru
    $slider .= $slide;
    $slider_ul .= $slide_li;

    $pocet_slidu++;
  }

  $class = " bxslider ";
  if($pocet_slidu <= 1) $class = ""; // U jednoho snímku nezapínáme slidování.

  if(!empty($slider))
  { // uzavreni vsech slidu do slideru
    $slider = '
    <div class="slider_bg">
      <div class="slider">
        '.$slider.'
      </div>
    </div>
    ';

    $slider_ul = '
    <div class="slider_bg">
      <div class="slider cycle-slideshow"
           data-cycle-slides="li"
           data-cycle-fx="scrollHorz"
           data-cycle-speed="400"
           data-cycle-timeout="5000"
           data-cycle-pause-on-hover="true"
      >
        <!-- empty element for pager links -->
        <div class="cycle-pager"></div>

        <ul class="'.$class.'">
          '.$slider_ul.'
        </ul>

      </div>
    </div>
    ';
  }

  return $slider_ul;
}


/**
Funkce vymění v textu shortcode za slidery
@param text (string) - předává se jako reference.
*/
function shortcode_slider(&$text)
{
	if(!empty($text) AND strpos($text , "@@slider"))
  {
    $shortcodes = get_shortcode("slider" , $text);

    foreach($shortcodes as $shortcode_slider)
    {
      $parametry = get_shortcode_param($shortcode_slider);
      $id_slider = NULL;

      foreach($parametry as $parametr => $hodnota)
      {
        switch($parametr)
        {
          case 'id':
          { // id slideru
            $id_slider = $hodnota;
            break;
          }
        }
      }

      $text = strtr($text, array ($shortcode_slider => get_slider($id_slider)));
    }
	}
}


?>