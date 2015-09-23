<?php

/**
Seznam shortcodů.
@return (string) seznam shortcodů
*/
function slider_shortcode()
{
  $query = "SELECT id , name FROM ".T_SLIDER;
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  $slider_shortcode = "";
  while($z = mysql_fetch_array($v))
  {
    $slider_shortcode .= '
    <strong>'.$z["name"].'</strong> - @@@slider id='.$z["id"].'@@@<br />
    ';
  }

  if(!empty($slider_shortcode))
  {
    return '
    <a href="'.MAIN_LINK.'&f=slider&a=list">Vytvořit slider</a><br />
    <br />
    <div class="slider_shortcode">
      '.$slider_shortcode.'
    </div>
    <br />
    Vložte kód do editoru.

    ';
  }
}

?>