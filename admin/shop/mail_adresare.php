<?php


include_once($_SERVER["DOCUMENT_ROOT"]."/admin/_mysql.php");

/*
ulozi adresare na ktere se ma sprava rozeslat

@param (int) id_zpravy = id zpravy
@param (array) vybrane_adresare = pole[id_adresare] = 1 nebo 0 (1 = vybran, 0 = nevybran) 
*/
function oznac_adresare($id_zpravy , $vybrane_adresare)
{
  // smazani dosavadniho vyberu
  $query = "
  DELETE
  FROM ".T_MAIL_ADRESAR_X_MESSAGE."
  WHERE id_message = '".$id_zpravy."'
  ";  
  my_DB_QUERY($query,__LINE__,__FILE__);
  
  // oznaceni adresaru
  foreach($vybrane_adresare as $id_adresare => $oznac)
  {
    if($oznac == 1)
    { // adresar byl oznacen
      $query = "
      INSERT 
      INTO ".T_MAIL_ADRESAR_X_MESSAGE."
      SET
      id_message = '".$id_zpravy."',
      id_adresar = '".$id_adresare."'     
      ";
      my_DB_QUERY($query,__LINE__,__FILE__);
    }
  }
}


/* 
vrati chceckboxy adresaru a oznaci ty vybrane k dane zprave
@param $id_zpravy (int) - id zpravy 

return chcesboxy s oznacenymi adresari 
*/
function adresare_input($id_zpravy = "")
{
  // adresare na ktere se bude posilat
  $adresare_oznacene = array();
  if(isset($id_zpravy) AND !empty($id_zpravy))
  {
    $query = "
    SELECT id_adresar
    FROM ".T_MAIL_ADRESAR_X_MESSAGE."
    WHERE id_message = '".$id_zpravy."'
    ";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);
      
    while($z = mysql_fetch_array($v)) 
    { // oznacene adresare
      $adresare_oznacene[] = $z["id_adresar"];
    }
  } 
     
  // seznam vsech adresaru
  $query = "
  SELECT id , nazev
  FROM ".T_MAIL_ADRESAR."
  WHERE ".SQL_C_LANG."
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  $seznam = NULL;
  while($z = mysql_fetch_array($v)) 
  { // vypiseme vsechny adresare
    $oznac = NULL;
    if(in_array($z["id"] , $adresare_oznacene))
    { // oznaceno
      $oznac = 'checked="checked"';
    }
    
    $seznam .= '<input type="checkbox" name="adresare['.$z["id"].']" value="1" '.$oznac.' />'.$z["nazev"].'<br />';
  }
  
  return $seznam;
}


// inicializace globalnich promennych
$nadpis = NULL;
$data = NULL;


if($_GET["a"] == "list")
{  // seznam adresaru
  if(isset($_POST["nazev"]) AND !empty($_POST["nazev"]))
  { // pridani adresare
    $nazev = mysql_real_escape_string(strip_tags(trim($_POST["nazev"]))); // odstraneni mezer a HTML tagu
    
    $query = "
    INSERT INTO ".T_MAIL_ADRESAR." SET
    nazev = '".$nazev."',
    ".SQL_C_LANG."
    ";
    my_DB_QUERY($query,__LINE__,__FILE__);
  }  
  
  $nadpis = "Adresáře";

  // seznam vsech adresaru
  $query = "
  SELECT id , nazev
  FROM ".T_MAIL_ADRESAR."
  WHERE ".SQL_C_LANG."
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  $seznam = NULL;
  while($z = mysql_fetch_array($v)) 
  { // vypiseme vsechny adresare    
    $edit_link = ico_edit(MAIN_LINK."&f=mail_adresare&a=edit&id=".$z["id"] , "Editovat");
    
    $seznam .= '
    <tr '.TABLE_ROW.'>
      <td>
        '.$z["nazev"].'
      </td>
      <td class="td2" style="width:35px;">
        '.$edit_link.'
      </td>
    </tr>
    '; 
  }
  
  if(!empty($seznam))
  {
    $seznam = '
    <table class="admintable">
      '.$seznam.'
    </table>
    ';
  }
  
  $seznam = "
  <h4>Seznam adresářů</h4>
  ".$seznam;  
  // end seznam vsech adresaru
  
  // nezarazene emaily
  $nezarazene = ico_edit(MAIN_LINK."&f=mail_adresare&a=nezarazene" , "Nezařazené emaily");
  
  $nezarazene = '
  <h4>Nezařazené emaily</h4>
  <table class="admintable">
    <tr '.TABLE_ROW.'>
      <td>
        Nezařazené
      </td>
      <td class="td2" style="width:35px;">
        '.$nezarazene.'
      </td>
    </tr>
  </table>  
  ';
  // end nezarazene emaily  
  
  // formular pro pridani adresare
  $form = '
  <h4>Přidat adresář</h4>
  <form method="post" action="">
    <input type="text" name="nazev" value="" />
    <input type="submit" name="ulozit" value="Přidat" />
  </form>
  ';
  // end formular pro pridani adresare
  
  $data .= $form . $seznam . $nezarazene; 
}


if($_GET["a"] == "nezarazene")
{  // nezarazene emaily
  $nadpis = "Nezařazené emaily";
  
  $query = "
  SELECT email 
  FROM ".T_INFO_IMPORTED." 
  WHERE fla_news_imported.id NOT IN (SELECT id_email FROM ".T_MAIL_ADRESAR_X_EMAIL." GROUP BY id_email)
  AND 
  ".SQL_C_LANG."
  "; 
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  $seznam = NULL;
  while($z = mysql_fetch_array($v)) 
  { // vsechny nezarazene emaily
  
    // je na blacklistu?
    $query_blacklist = "
    SELECT email 
    FROM ".T_INFO_BLACKLISTED."
    WHERE email = '".$z["email"]."'
    "; 
    $v_blacklist = my_DB_QUERY($query_blacklist,__LINE__,__FILE__);
    $z_blacklist = mysql_fetch_array($v_blacklist);
    $blacklist = $z_blacklist["email"];
    
    if(isset($blacklist) AND !empty($blacklist))
    { // je na blacklistu
      $seznam .= '<span class="blacklist">' . $z["email"] ."</span><br />\n";    
    }
    else
    {
      $seznam .= $z["email"] ."<br />\n";
    } 
  }
  
  $data .= '
  '.$seznam.'
  ';    
}


if($_GET["a"] == "edit")
{  // editace adresaru
  if(isset($_POST["id_adresar"]) AND !empty($_POST["id_adresar"]))
  { // uprava adresare
    $ID_adresar = $_POST["id_adresar"];
    
    // uprava nazvu adresare
    $nazev = mysql_real_escape_string(strip_tags(trim($_POST["nazev"]))); // odstraneni mezer a HTML tagu
    
    $query = "
    UPDATE ".T_MAIL_ADRESAR." SET
    nazev = '".$nazev."'
    WHERE id = '".$ID_adresar."'
    ";
    my_DB_QUERY($query,__LINE__,__FILE__);
    
    // import adres do adresare
    {
  		$celkem = $duplikat = $novy = 0;
      
  		preg_match_all('/([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})/',strtolower($_POST['emaily']), $matches);

		  if(is_array($matches[0]))
      {
			  foreach($matches[0] as $mail)
        {
				  $query = "
          SELECT id 
          FROM ".T_INFO_IMPORTED." 
          WHERE email = '".$mail."' 
          AND ".SQL_C_LANG;
				  $v = my_DB_QUERY($query,__LINE__,__FILE__);
          
				  if(mysql_num_rows($v) > 0)
          { // email uz nainportovan byl
            $id_email = mysql_result($v, 0, 0);
            
					  $duplikat++;
				  }
          else
          { // novy kontakt
				    $query = "
            INSERT INTO ".T_INFO_IMPORTED." 
            SET
            email = '".$mail."',
            ".SQL_C_LANG."
            ";				     
				    my_DB_QUERY($query,__LINE__,__FILE__);
             
	        	// id posledniho vlozeneho emailu
            $query = "SELECT LAST_INSERT_ID()";
		        $v = my_DB_QUERY($query,__LINE__,__FILE__);
		        $id_email = mysql_result($v, 0, 0);           
                         
				    $novy++;
				  }
          
          // odstraneni emailu z adresare
				  $query="
          DELETE FROM ".T_MAIL_ADRESAR_X_EMAIL." 
          WHERE id_adresar = '".$ID_adresar."' 
          AND id_email = '".$id_email."'
          ";
				  my_DB_QUERY($query,__LINE__,__FILE__);          
          
          // prirazeni emailu do adresare
          $query = "
          INSERT INTO ".T_MAIL_ADRESAR_X_EMAIL." 
          SET 
          id_adresar = '".$ID_adresar."', 
          id_email = '".$id_email."'
          ";				     
				  my_DB_QUERY($query,__LINE__,__FILE__); 
          		
				  $celkem++;			
			  }
		  }        
    }
  }
  
  if(!isset($_GET["id"]) OR empty($_GET["id"]))
  {
    exit("Neni vybran zadny adresar.");
  }
  else
  {
    $ID_adresar = $_GET["id"];
  }
  
  $nadpis = "Úprava adresáře";
    
  $query = "
  SELECT nazev
  FROM ".T_MAIL_ADRESAR."
  WHERE id = '".$ID_adresar."'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v); 
  $nazev = $z["nazev"];

  // emaily v adresari
  $query = "
  SELECT 
  ".T_INFO_IMPORTED.".email as email,
  ".T_INFO_IMPORTED.".id as id
  FROM 
  ".T_INFO_IMPORTED.",
  ".T_MAIL_ADRESAR_X_EMAIL."
  WHERE ".T_INFO_IMPORTED.".id = ".T_MAIL_ADRESAR_X_EMAIL.".id_email
  AND ".T_MAIL_ADRESAR_X_EMAIL.".id_adresar = '".$ID_adresar."' 
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__); 
  
  $emaily = NULL;
  while($z = mysql_fetch_array($v)) 
  { // vypiseme vsechny adresare
    // odebrani z adresare
    $delete = '
    <span 
      style="cursor:pointer;" 
      onclick="del(\'Opravdu odstranit z adresáře?\' , \''.MAIN_LINK.'&f=mail_adresare&a=delete&id_email='.$z["id"].'&id_adresar='.$ID_adresar.'\');"
    >
      <img src="./icons/ico_delete.gif" alt="Odstranit z adresáře" title="Odstranit z adresáře" />
    </span>';

    // pridavani na blacklist
    $query_blacklist = "
    SELECT email 
    FROM ".T_INFO_BLACKLISTED."
    WHERE email = '".$z["email"]."'
    "; 
    $v_blacklist = my_DB_QUERY($query_blacklist,__LINE__,__FILE__);
    $z_blacklist = mysql_fetch_array($v_blacklist);
    $blacklist = $z_blacklist["email"]; 
    
    if(isset($blacklist) AND !empty($blacklist))
    { // je na blacklistu 
      $blacklist_button = '
      <span style="cursor:pointer;" onclick="del(\'Odebrat z blacklistu?\' , \''.MAIN_LINK.'&f=mail_adresare&a=blacklist_del&id_email='.$z["id"].'\');">
        <img src="./icons/hidden_1.gif" alt="Odebrat z blacklistu" title="Odebrat z blacklistu" />
      </span>
      ';
      
      $class = "blacklist";       
    }
    else
    { // není na blacklistu
      $blacklist_button = '
      <span style="cursor:pointer;" onclick="del(\'Přidat na blacklist?\' , \''.MAIN_LINK.'&f=mail_adresare&a=blacklist_add&id_email='.$z["id"].'\');">
        <img src="./icons/hidden_0.gif" alt="Přidat na blacklist" title="Přidat na blacklist" />
      </span>
      ';
      
      $class = "";  
    }    
     
    $emaily .= '
    <tr '.TABLE_ROW.'>
      <td class="'.$class.'">
        '.$blacklist_button.'
        '.$z["email"].'       
      </td>
      <td class="td2" style="width:30px;">
        '.$delete.'
      </td>  
    </tr> 
    ';  
  }  
  
  if(!empty($emaily))
  {
    $emaily = '       
    <table class="admintable">
      '.$emaily.'
    </table>
    ';
  }
  // end emaily v adresari
  
  $form = '
  <script>
  	function del(dotaz , link) 
    {	
	    if(confirm(dotaz))
	    { 
        location = link; 
      }	
	  } 
  </script> 
  
  <form method="post" action="">
    <div>
      <input type="hidden" name="id_adresar" value="'.$ID_adresar.'" />
    </div>
    
    Název: <input type="text" name="nazev" value="'.$nazev.'" /><br />
    <br />
    '.$emaily.'
    <br />
    Importovat emaily:<br />
    <textarea name="emaily" style="width: 100%; height: 400px;"></textarea><br />
    <br />
    <input class="butt_green" type="submit" name="ulozit" value="Uložit" />
    <input class="butt_red" type="button" name="odstranit" value="Odstranit" 
    onclick="del(\'Odstranit adresář?\' , \''.MAIN_LINK.'&f=mail_adresare&a=adresar_del&id_adresar='.$ID_adresar.'\')" 
    />
  </form>
  ';
  
  $data .= $form;
}


if($_GET["a"] == "delete")
{ // odstraneni z adresare
  if(!isset($_GET["id_adresar"]) OR empty($_GET["id_adresar"]))
  {
    exit("Neni vybran zadny adresar.");
  }
  
  if(!isset($_GET["id_email"]) OR empty($_GET["id_email"]))
  {
    exit("Neni vybran zadny email.");
  }  
    
  $query = "
  DELETE 
  FROM ".T_MAIL_ADRESAR_X_EMAIL."
  WHERE id_email = '".$_GET["id_email"]."'
  AND id_adresar = '".$_GET["id_adresar"]."'
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);
  
  // zpet na seznam emailu
  header("location: ".$_SERVER['HTTP_REFERER']);
  exit;   
}


if($_GET["a"] == "blacklist_add")
{ // pridat na blacklist

  if(!isset($_GET["id_email"]) OR empty($_GET["id_email"]))
  {
    exit("Neni vybran zadny email.");
  }  
  
  $query = "
  SELECT email 
  FROM ".T_INFO_IMPORTED."
  WHERE id = '".$_GET["id_email"]."'
  ";  
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $email = mysql_result($v, 0, 0);
      
  $query = "
  INSERT 
  INTO ".T_INFO_BLACKLISTED."
  SET
  email = '".$email."'
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);

  // zpet na seznam emailu
  header("location: ".$_SERVER['HTTP_REFERER']);
  exit;  
}

if($_GET["a"] == "blacklist_del")
{ // odstranit z blacklistu
  if(!isset($_GET["id_email"]) OR empty($_GET["id_email"]))
  {
    exit("Neni vybran zadny email.");
  }  
  
  $query = "
  SELECT email 
  FROM ".T_INFO_IMPORTED."
  WHERE id = '".$_GET["id_email"]."'
  ";  
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $email = mysql_result($v, 0, 0);
      
  $query = "
  DELETE 
  FROM ".T_INFO_BLACKLISTED."
  WHERE
  email = '".$email."'
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);

  // zpet na seznam emailu
  header("location: ".$_SERVER['HTTP_REFERER']);
  exit;  
}


if($_GET["a"] == "adresar_del")
{ // odstraneni adresare
  if(!isset($_GET["id_adresar"]) OR empty($_GET["id_adresar"]))
  {
    exit("Neni vybran zadny adresar.");
  }  
  
  // odstraneni emailu z adresare     
  $query = "
  DELETE 
  FROM ".T_MAIL_ADRESAR_X_EMAIL."
  WHERE
  id_adresar = '".$_GET["id_adresar"]."'
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);
  
  // odstraneni adresare
  $query = "
  DELETE 
  FROM ".T_MAIL_ADRESAR."
  WHERE
  id = '".$_GET["id_adresar"]."'
  ";
  my_DB_QUERY($query,__LINE__,__FILE__);  

  // zpet na seznam adresaru
  header("location: ".MAIN_LINK.'&f=mail_adresare&a=list');
  exit;  
}


?>