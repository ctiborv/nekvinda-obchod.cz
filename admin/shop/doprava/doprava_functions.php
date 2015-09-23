<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_security.php');



// tlacitko odstraneni zaznamu
function ico_add($link , $text) 
{
	$ico = '<a class="add_button" href="'.$link.'" title="'.$text.'">  '.$text.'</a>';
    	
	return $ico;
}



/*
Odstrani urcitou dopravu u vsech uzivatelu

@param id_doprava (int) - id doprava
*/
function delete_doprava_user($id_doprava)
{
  $query = "
  DELETE
  FROM ".T_DOPRAVA_X_UZIVATEL."
  WHERE id_doprava = '".$id_doprava."'
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);
  my_OPTIMIZE_TABLE(T_DOPRAVA_X_UZIVATEL);
}


/*
Odstrani dopravu u urciteho uzivatele

@param id_user (int) - id uzivatele
*/
function delete_user_doprava($id_user)
{
  $query = "
  DELETE
  FROM ".T_DOPRAVA_X_UZIVATEL."
  WHERE id_uzivatel = '".$id_user."'
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);
  my_OPTIMIZE_TABLE(T_DOPRAVA_X_UZIVATEL);
}


/*
@param id (int) - id uzivatele

return doprava (array) - pole s id povolenych dopravcu
return 0 - nenalezeny zadni povoleni dopravci
*/
function get_uzivatel_doprava($id)
{
  $query = "
  SELECT id_doprava
  FROM ".T_DOPRAVA_X_UZIVATEL."
  WHERE id_uzivatel = '".$id."'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  $doprava = array();
  
  while ($z = mysql_fetch_array($v))
  {
    $doprava[] = $z['id_doprava'];
  }
  
  if(count($doprava) > 0)
  { // pole s id povolenych doprav
    return $doprava;
  }
  else
  { // doprava neni uzivately pridelena
    return 0;
  }
}


/*
@param id (int) - id uzivatele, nepovinny, oznaci co ma uzivatel povolen

return check (string) - chceckbox + nazev dopravy
*/
function check_doprava_vybrane($id = '')
{
  if(!empty($id))
  { // uzivately povolene dopravy
    $doprava = get_uzivatel_doprava($id);
  }

  $query = "
  SELECT id , nazev
  FROM ".T_DOPRAVA_NEW."
  WHERE pro_vybrane = 1
  AND hidden = 0
  AND lang = '".C_LANG."'
  ORDER BY poradi
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  $check = '';

  while($z = mysql_fetch_array($v))
  {
     $oznac = '';
     if($doprava != 0 AND in_array($z["id"] , $doprava))
     {
        $oznac = 'checked="checked"';
     }
     
     $check .= ' <input type="checkbox" name="doprava[]" value="'.$z["id"].'" '.$oznac.' /> '.$z["nazev"].'<br>';
  }
  
  return $check;
}


/*
@param id_user (int) - id uzivatele
@param id_doprava (array) - id dopravy ktere ma uzivatel povolene

return 0 - ok
*/
function save_doprava_vybrane($id_user , $id_doprava)
{
  // odstraneni stavajicich dopravcu
  delete_user_doprava($id_user);
  
  if(count($id_doprava) > 0)
  { // jsou prideleny dopravci vyrvorime insert
    $insert = '';
    $celkem = count($id_doprava);
    $index = 1;
    
    foreach($id_doprava as $id)
    { // vytvorime inser pro vlozeni pridelenych dopravdu
      $insert .= '( ' . $id_user . ' , ' . $id . ' )';

      if($index < $celkem)
      { // krome posledniho prvku oddelujeme vkladane hodnoty carkou
        $insert .= ', ';
      }
      
      $index++;
    }

    if(!empty($insert))
    { // vlozeni povolenych dopravcu
      $query = "
      INSERT INTO ".T_DOPRAVA_X_UZIVATEL."
      (id_uzivatel , id_doprava)
      VALUES
      ".$insert."
      ";
      my_DB_QUERY($query,__LINE__,__FILE__);
    }
  }
  
  return 0;
}


/**
Select dopravců
@param (int) id_selected = id které se má označit
@return (string) select
*/
function get_courier_select($id_selected = NULL)
{
  $query_instal = "
  CREATE TABLE IF NOT EXISTS `".T_COURIER."` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
    `index` varchar(25) COLLATE utf8_czech_ci NOT NULL,
    `lang` int(3) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `lang` (`lang`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Tabulka dopravců' AUTO_INCREMENT=1 ;
  ";
  my_DB_QUERY($query_instal,__LINE__,__FILE__);

  global $dct; // Slovník.

  $id_selected = intval($id_selected);

  $query = "
  SELECT id, name
  FROM ".T_COURIER."
  WHERE ".SQL_C_LANG."
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  $select = "";
  while($z = mysql_fetch_assoc($v))
  {
    if($id_selected == $z["id"]) $selected = 'selected="selected"';
    else $selected = "";

    $select .= '<option value="'.$z["id"].'" '.$selected.'>'.$z["name"].'</option>
';
  }

  return '
  <select name="id_courier">
    <option value="">'.$dct['Vyberte_dopravce'].'</option>
    '.$select.'
  </select>
  ';
}

?>
