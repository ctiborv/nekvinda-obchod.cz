<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


include_once($_SERVER['DOCUMENT_ROOT'].'/admin/shop/doprava/doprava_functions.php');


$addRecord = ico_add(MAIN_LINK.'&f=doprava&a=add' , "Přidat záznam");


function form($form_data = NULL)
{
  global $dct;
  global $JS_NUMBER_FORMAT;
  
  //select se státy
  $arrayStaty = unserialize(STATY);
  $optionsStaty = null;
  foreach($arrayStaty as $id=>$value){
		if($form_data['id_stat']==$id){
		     $selected='selected="selected"';
		}else{
			$selected='';
		}

		$optionsStaty.='<option '.$selected.' value="'.$id.'">'.$value.'</option>';
	}
  $selectStaty = '<select style="width:230px" name="id_stat">
			'.$optionsStaty.'
		</select>';
		
		
  //select s dopravou		
  
  $optionsPlatba = null;
  $query = 'SELECT * FROM '.T_PLATBA.' WHERE hidden = 0';
  $v=my_DB_QUERY($query,__LINE__,__FILE__);
  while($z = mysql_fetch_array($v)) {
		if($form_data['id_platba']==$z['id']){
		     $selected='selected="selected"';
		}else{
			$selected='';
		}
		
		$nazev = $z['nazev']." ".$z['poznamka'];

		$optionsPlatba.='<option '.$selected.' value="'.$z['id'].'">'.$nazev.'</option>';
	}
  $selectPlatby = '<select style="width:230px" name="platba">
			'.$optionsPlatba.'
		</select>'; 

  if(isset($_POST) AND !empty($_POST))
  { // Uložení dat.
    $_SESSION["admin"]["alert_js"] = '';
    $zpet = $_SERVER['HTTP_REFERER'];

    if(empty($_POST["nazev"])) $_SESSION["admin"]["alert_js"] .= 'Vyplňte název!\n';
    if(empty($_POST["cena"]) AND $_POST["cena"] > 0) $_SESSION["admin"]["alert_js"] .= 'Vyplňte cenu!\n';
    


    if(empty($_SESSION["admin"]["alert_js"]))
    { // data jsou v ok
      // Společná data prouložení.
      $query_set = "
      nazev = '".mysql_real_escape_string(trim($_POST["nazev"]))."',
      cena = '".cislo_db($_POST["cena"])."',
      vaha_od = '".intval($_POST["vaha_od"])."',
      vaha_do = '".intval($_POST["vaha_do"])."',
      id_stat = '".intval($_POST["id_stat"])."',
      neuctovano_od = '".cislo_db($_POST["neuctovat_od"])."',
      poznamka = '".mysql_real_escape_string(trim($_POST["poznamka"]))."',
      lang = '".C_LANG."'
      ";

      if(empty($_POST["id"]))
      { // novy
        // zaradime na konec
        $query = "SELECT MAX(poradi) FROM ".T_DOPRAVA." WHERE id_stat = ".$_POST['id_stat'];
        $v = my_DB_QUERY($query,__LINE__,__FILE__);
        $konec = mysql_result($v , 0 , 0) + 1;

        $query = "
        INSERT INTO ".T_DOPRAVA."
        SET
        ".$query_set.",
        poradi = ".$konec."
        
        ";
        my_DB_QUERY($query,__LINE__,__FILE__);
        
        
        $v = my_DB_QUERY("SELECT LAST_INSERT_ID()",__LINE__,__FILE__);
        $ID = mysql_result($v, 0, 0);

        $query2 = "INSERT INTO ".T_DOPRAVA_X_PLATBA." SET id_platba = ".$_POST["platba"].", id_doprava =".$ID."";
        my_DB_QUERY($query2,__LINE__,__FILE__);
        
        
        $zpet = MAIN_LINK.'&f=doprava&a=list';
      }
      else
      {  // aktualizace
        $id = intval($_POST["id"]);

//         if(!isset($_POST["pro_vybrane"]))
//         { // pri zruseni dopravy pouze pro vybrane ji zrusime u vsech uzivatelu
//           include_once($_SERVER['DOCUMENT_ROOT'].'/admin/shop/doprava/doprava_functions.php');
//           delete_doprava_user($_POST["id"]);
//         }

        $query = "
        UPDATE ".T_DOPRAVA."
        SET
        ".$query_set."
        WHERE id = '".$id."'";
        my_DB_QUERY($query,__LINE__,__FILE__);
        
        $query2 = "UPDATE ".T_DOPRAVA_X_PLATBA." SET id_platba = ".$_POST["platba"]." WHERE id_doprava =".$id."";
        my_DB_QUERY($query2,__LINE__,__FILE__);
      }
    }

	  Header("Location: $zpet");
	  exit;
  }

  $max255 = '<br><span class="f10i">'.$dct['cat_f_max_255'].'</span><br>';
  $tlacitka = '<br><br>'.SAVE_BUTTON;

  if(!empty($form_data))
  {  // aktualizace
    $tlacitka .= ' '.DELETE_BUTTON;
  }
  
  if(empty($form_data["id"])) $form_data["id"] = '';
  if(empty($form_data["nazev"])) $form_data["nazev"] = '';
  if(empty($form_data["cena"])) $form_data["cena"] = 0;
  if(empty($form_data["neuctovano_od"])) $form_data["neuctovano_od"] = 0;
  if(empty($form_data["id_dph"])) $form_data["id_dph"] = 0;
  if(empty($form_data["poznamka"])) $form_data["poznamka"] = '';
  if(empty($form_data["hidden"])) $form_data["hidden"] = 0;
  if(empty($form_data["vaha_od"])) $form_data["vaha_od"] = 0;
  if(empty($form_data["vaha_do"])) $form_data["vaha_do"] = 0;
  if(empty($form_data["poradi"])) $form_data["poradi"] = -1;
  
  //if(empty($form_data["pro_vybrane"])) $form_data["pro_vybrane"] = 0;

  $skryte = '';
  if($form_data["hidden"] == 1)
  {
    $skryte = 'checked="checked"';
  }
//   $pro_vybrane = '';
//   if($form_data["pro_vybrane"] == 1)
//   {
//     $pro_vybrane = 'checked="checked"';
//   }
  
  //$dph = select_dph($form_data["id_dph"] , "ceny();");
  
  // Select pro nastavení typu platby.
  //$enum_list = enum_values(T_DOPRAVA, "platba");
//   $pay_select = "";
//   foreach($enum_list as $pay)
//   {
//     $selected = "";
//     if(isset($form_data["platba"]) AND $form_data["platba"] == $pay)
//     { // Označená platba.
//       $selected = 'selected="selected"';
//     }
// 
//     $pay_select .= '
//     <option value="'.$pay.'" '.$selected.'>'.$pay.'</option>
//     ';
//   }
// 
//   if(!empty($pay_select))
//   {
//     $pay_select = '
//     <select name="platba">
//       '.$pay_select.'
//     </select>
//     ';
//   }
  // END

  $cena_bez_dph = 0;
  $cena_s_dph = 0;

  if(!empty($form_data["cena"]) OR !isset($_POST["cena"]))
  {
    //$cena_s_dph = $form_data["cena"];
    $cena_bez_dph = $form_data["cena"];;
    //$cena_bez_dph = cena_bez_dph($cena_s_dph , $dph["dph"]);
  }
  
  $js = '
  <script language="JavaScript">  
  '.$JS_NUMBER_FORMAT.'
  function ceny()
  { 
    var cena = parseFloat(document.doprava.cena.value.replace(" " , "").replace("," , "."));   
    var dph = parseFloat(document.doprava.id_dph.options[document.doprava.id_dph.selectedIndex].text);

    var cena_s_dph = Math.round(cena * ((100 + dph) / 100));        
     
    document.getElementById("cena_s_dph").innerHTML = number_format(cena_s_dph , 0 , "," , " ");
  }
  
  function del() 
  { // pred odstraneni zaznamu ze zeptame
	  if(confirm("'.$dct['opravdu_odstranit'].'"))
		{
      location = "'.MAIN_LINK.'&f=doprava&delete='.$form_data['id'].'"; 
    }
	}
	
	function validate(form)
	{ // kontrola povinnych hodnot
    if(form.nazev.value == "")
    {
      alert("Vyplňte název");
      return false;
    }
    
    if(form.cena.value == "")
    {
      alert("Vyplňte cenu");
      return false;
    }
    
    return true;
  }
	
  </script>
  ';
  
  $form = '
  '.$js.'
  <form name="doprava" method="post" action="" onsubmit="return validate(this);">
    <input type="hidden" name="id" value="'.$form_data["id"].'" />
    <input type="hidden" name="poradi" value="'.$form_data["poradi"].'" />
    <table class="admintable nobg" cellspacing="5" cellpadding="0">
      <!--<tr>
        <td class="tdleft">
          Skrýt
        </td>
        <td><input type="checkbox" name="hidden" '.$skryte.' /></td>
      </tr>-->
      <tr>
        <td class="tdleft">
          Název
          '.$max255.'
        </td>
        <td><input type="text" name="nazev" style="width:230px;" value="'.$form_data["nazev"].'" /></td>
      </tr>
      <tr>
        <td class="tdleft">
          Stát
        </td>
        <td>'.$selectStaty.'</td>
      </tr>
      <tr>
        <td class="tdleft">
          Platba 
        </td>
        <td>'.$selectPlatby.'</td>
      </tr>
      <tr>
        <td>Váhový limit <strong>od - do</strong></td>
        <td><input style="width:100px;" type="text" name="vaha_od" value="'.$form_data["vaha_od"].'" />&nbsp;g&nbsp;&nbsp;-&nbsp;&nbsp;<input style="width:100px;" type="text" name="vaha_do" value="'.$form_data["vaha_do"].'" />&nbsp;g  </td>
      </tr>
      <tr>
        <td></td>
        <td>Váhu zadávejte v gramech.<br /> Doprava musí mít vyplněný váhový rozsah. <br />V případě nulové horní váhy nebude doprava dostupná pro výběr v nákupním košíku. (u objednávek ve kterých se počítá s váhovým limitem)</td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td>Cena bez DPH</td>
        <td><input style="width:100px;" type="text" name="cena" value="'.(($cena_bez_dph == 0) ? "" : number_format($cena_bez_dph , 0 , "," , " ")).'" /> Kč</td>
      </tr>
      <tr>
        <td>Neúčtovat od</td>
        <td><input style="width:100px;" type="text" name="neuctovat_od" value="'.(($cena_bez_dph == 0) ? "" :number_format($form_data["neuctovano_od"] , 0 , "," , " ")).'" /> Kč</td>
      </tr>
      <tr>
        <td colspan="2">
          Poznámka
          '.$max255.'
          <textarea name="poznamka" style="width:440px;" rows="6">'.$form_data["poznamka"].'</textarea>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          '.$tlacitka.' 
        </td>  
      </tr>           
    </table>
  </form>';

return $form;
}


if(!empty($_GET["delete"])) 
{
  $id = $_GET['delete'];
	
  $query = "DELETE FROM ".T_DOPRAVA."
	          WHERE id = '$id'";
	my_DB_QUERY($query,__LINE__,__FILE__);	
	my_OPTIMIZE_TABLE(T_DOPRAVA);
	
  $query = "DELETE FROM ".T_DOPRAVA_X_PLATBA."
	          WHERE id_doprava = '$id'";
	my_DB_QUERY($query,__LINE__,__FILE__);	
	my_OPTIMIZE_TABLE(T_DOPRAVA_X_PLATBA);
	
	$zpet = MAIN_LINK.'&f=doprava&a=list';
	Header("Location: $zpet");
	exit; 	
}

if($_GET['a'] == "hidden" AND !empty($_GET['id'])) 
{
	$hidden = $_GET['hidden'];
	$id = $_GET['id'];
	
	if ($hidden != 1) 
  {
    $hidden = 0;
  }

	$query = "UPDATE ".T_DOPRAVA." SET
            hidden = '$hidden'
	         	WHERE id = '$id'";
           
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}

if($_GET["a"] == "list")
{
  $nadpis = "Způsoby dopravy";
  $data = $addRecord."<br /><br />";  

  $query="SELECT max(poradi) as maximum 
          FROM ".T_DOPRAVA."
          WHERE lang = '".C_LANG."' AND id_stat=1";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $maximum = mysql_fetch_assoc($v);
  $maximum1 = $maximum["maximum"];
  
  $query="SELECT max(poradi) as maximum 
          FROM ".T_DOPRAVA."
          WHERE lang = '".C_LANG."' AND id_stat=2";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $maximum = mysql_fetch_assoc($v);
  $maximum2 = $maximum["maximum"];

  $query = "SELECT id, nazev, hidden , poradi , neuctovano_od, id_stat
            FROM ".T_DOPRAVA."
            WHERE lang = '".C_LANG."'
            ORDER BY id_stat, poradi";           
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  
  $table = '';
  
  while ($z = mysql_fetch_assoc($v))
  {
		$id = $z["id"];
		$nazev = $z["nazev"];
		$hidden = $z["hidden"];
		$poradi = $z["poradi"];
		$neuctovano_od = $z["neuctovano_od"];
		$id_stat = $z["id_stat"];
	//	$pro_vybrane = $z["pro_vybrane"];
		
		if($hidden == 1) 
    {
      $hidden_class = " hidden";
      $set_hidden = 0;
      $title = "Odkýt";
    }
		else 
    {
      $hidden_class = "";
      $set_hidden = 1;
      $title = "Skrýt";
    }
		$oznac = '';
		
// 		if($pro_vybrane == 1)
// 		{
//       $oznac .= '<b>(pro vybrané)<b>';
//     }

    $sipka = '';
      
    if(($id_stat == 1 AND ($poradi == $maximum1 && $maximum1 == 1)))
    {
      $sipka = '';
    }
    else if (($id_stat == 2 AND ($poradi == $maximum2 && $maximum2 == 1)))
    { 
      $sipka = '';
    }
    else {   
      switch($poradi)
      {
        case 1: 
        {
          $sipka='<a href="'.MAIN_LINK.'&f=doprava&a=changepos&ord=down&id='.$id.'" title="níže"><img style="border: 0;" src="/admin/img/arrow_down.png" alt="šipka dolů"></a>&nbsp;<img src="/admin/img/arrow_no.gif">';
          break;
        }
        case $maximum1:
        {
          $sipka='<img src="/admin/img/arrow_no.gif" />&nbsp;<a href="'.MAIN_LINK.'&f=doprava&a=changepos&ord=upp&id='.$id.'" title="výše"><img style="border: 0;" src="/admin/img/arrow_up.png" alt="šipka nahoru"></a>';
          break;
        }
        case $maximum2:
        {
          $sipka='<img src="/admin/img/arrow_no.gif" />&nbsp;<a href="'.MAIN_LINK.'&f=doprava&a=changepos&ord=upp&id='.$id.'" title="výše"><img style="border: 0;" src="/admin/img/arrow_up.png" alt="šipka nahoru"></a>';
          break;
        }
        default:
        {
          $sipka='<a href="'.MAIN_LINK.'&f=doprava&a=changepos&ord=down&id='.$id.'" title="níže"><img style="border: 0;" src="/admin/img/arrow_down.png" alt="šipka dolů"></a>&nbsp;<a href="'.MAIN_LINK.'&f=doprava&a=changepos&ord=upp&id='.$id.'" title="výše"><img style="border: 0;" src="/admin/img/arrow_up.png" alt="šipka nahoru"></a>';
          break;
        }
      }
    }
		
		$table .= '
		<tr '.TABLE_ROW.'>
			<td class="td1 '.$hidden_class.'">
			  <a href="'.MAIN_LINK.'&f=doprava&a=hidden&id='.$id.'&hidden='.$set_hidden.'" title="'.$title.'" alt="'.$title.'"><img align="absmiddle" src="icons/hidden_'.$hidden.'.gif"></a>
        '.$nazev.' '.$oznac.'
          </td>
          <td class="stat">
            '.$arrayStaty[$id_stat].'
          </td>
          <td class="list_razeni">
            '.$sipka.'
          </td>
			<td class="td2">
				'.ico_edit(MAIN_LINK.'&f=doprava&a=edit&id='.$id , 'Upravit').'
      </td>
		</tr>';    
  }
  
  if(!empty($table))
  {
    $table = '<table class="list">
               '.$table.'
              </table>';
    $data .= $table;
  }
}

if($_GET["a"] == "edit" AND !empty($_GET["id"]))
{
  $nadpis = "Upravit způsob dopravy";

  $id = intval($_GET["id"]);

  // id 	nazev 	poznamka 	cena 	id_dph 	neuctovat_od 	poradi 	hidden 	lang 
  $query = "SELECT ".T_DOPRAVA.".*, ".T_DOPRAVA_X_PLATBA.".*
            FROM ".T_DOPRAVA.", ".T_DOPRAVA_X_PLATBA."
            WHERE ".T_DOPRAVA.".id = '".$id."'
            AND ".T_DOPRAVA_X_PLATBA.".id_doprava = ".T_DOPRAVA.".id
            ";
//   echo $query;
//   exit;

            
            
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);
  
  $data = form($z);  
}

if($_GET["a"] == "add")
{  
  $nadpis = "Přidat způsob dopravy";  
  
  $data = form();
}

if($_GET["a"] == "changepos" AND !empty($_GET["id"]) AND !empty($_GET["ord"]))
{
  $id = intval($_GET['id']);
  
  $query="SELECT poradi, id_stat 
          FROM ".T_DOPRAVA."
          WHERE id ='".$id."'";
  $v = my_DB_QUERY($query , __LINE__ , __FILE__);
  while($z = mysql_fetch_array($v)) {
    $poradi = $z['poradi'];
    $id_stat = $z['id_stat'];
  }
  
  switch($_GET['ord'])
  {
    case "down": 
    {  // zmena pozice dolů
      $query = "UPDATE ".T_DOPRAVA." SET
                poradi = ".($poradi + 1)."
                WHERE id =".$id.""; 
      $v = my_DB_QUERY($query , __LINE__ , __FILE__);  
      $query = "UPDATE ".T_DOPRAVA." SET
                poradi = '$poradi'
                WHERE id !=".$id." AND id_stat = ".$id_stat."
                AND poradi = ".($poradi + 1)."";
      $v = my_DB_QUERY($query , __LINE__ , __FILE__);   
      break;
    }
    case "upp":
    {  // zmena pozice nahoru
      $query = "UPDATE ".T_DOPRAVA." SET
                poradi = '".($poradi - 1)."'
                WHERE id =".$id.""; 
      $v = my_DB_QUERY($query , __LINE__ , __FILE__);  
      $query = "UPDATE ".T_DOPRAVA." SET
               poradi = $poradi
               WHERE id !=".$id." AND id_stat = ".$id_stat."
               AND poradi = ".($poradi - 1)."";
      $v = my_DB_QUERY($query , __LINE__ , __FILE__);          
      break;
    } 
  }  

  Header("Location: ".$_SERVER['HTTP_REFERER']);
  exit;
}
?>
