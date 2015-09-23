<?php

// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto ködu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.
/*
include_once "./_mysql.php";
include_once './login.php';
include_once './_functions.php';
include_once './_nastaveni.php';
*/


$addRecord = "<a href=\"".MAIN_LINK."&amp;f=inzerenti&amp;a=add\">".$dct['mn_inzerenti_add']."</a><br /><br />";

$nadpisy['list'] = $dct['mn_inzerenti_list'];
$nadpisy['edit'] = 'Upravit reklamu';
$nadpisy['add'] = $dct['mn_inzerenti_add'];

$nadpis = $nadpisy[$_GET['a']];


// T_INZERENTI
// souvisla rada
function souvisla_rada($start_pos) {

			$iiii = 1;
// 			echo "<br><br>".$pru['value']."<br>";
			// id title content anotace in_menu pozice homepage hidden in_menu vlozeno upraveno upravil spustit ukoncit lang
			$q1 = "SELECT id, poradi FROM ".T_INZERENTI." WHERE ".SQL_C_LANG." ORDER BY poradi";
			$v1 = my_DB_QUERY($q1,__LINE__,__FILE__);
			while ($z1 = mysql_fetch_array($v1)) {
			
				
// 				echo $z1['pozice'].")". $z1['id']." - $iiii<br>";
				$q2 = "UPDATE ".T_INZERENTI." SET poradi = ".$iiii." WHERE id = ".$z1['id']."";
				$v2 = my_DB_QUERY($q2,__LINE__,__FILE__);
				
				$iiii++;
			
			}
		
		}
	

// posun pozic nahoru
function move_up($poradi) {
$poradi = (int)$poradi;
	// id title content anotace in_menu pozice homepage hidden rubrika vlozeno upraveno upravil spustit ukoncit lang
	$query = "UPDATE ".T_INZERENTI." SET 
	poradi = poradi + 1 WHERE (poradi = $poradi or poradi > $poradi)";
	my_DB_QUERY($query,__LINE__,__FILE__);

}


// posun pozic dolu
function move_down($poradi) {
$poradi = (int)$poradi;
	// id title content anotace in_menu pozice homepage hidden rubrika vlozeno upraveno upravil spustit ukoncit lang
	$query = "UPDATE ".T_INZERENTI." SET 
	poradi = poradi - 1 WHERE (poradi = $poradi or poradi < $poradi)";
	my_DB_QUERY($query,__LINE__,__FILE__);

}



function form($form_data,$dct) {

$js='';

/*
$query= 'SELECT * FROM '.T_AKCE.' WHERE '.SQL_C_LANG;
$v = my_DB_QUERY($query,__LINE__,__FILE__);
while ($z=mysql_fetch_array($v)) {
 $name=$z['name'];
 $id=$z['id'];
 $akce .= '
  <tr><td><b>'.$z["name"].'</b></td><td>'.URL_LANG.'/'.$_SESSION[URL_LANG]['akce_url'].'/'.text_in_url($z["name"]).'-'.$z["id"].'.html</td></tr>';
}
if(!empty($akce)) {

  $akce_url = '
  <tr>
		<td colspan="2">&nbsp;</td>
	</tr>
  <tr>
    <td colspan="2">
			<a href="" class="click" onclick="s(\'divakce\'); return false;">Odkazy na Akční nabídky</a> &raquo;
			<div id="divakce">
      <div class="f9"><br /><table style="width: 100%;">'.$akce.'</table></div></div>
    </td>
  </tr>
  <tr>
		<td colspan="2">&nbsp;</td>
	</tr>';
	
	$js .= '
  document.getElementById(\'divakce\').style.display=\'none\';';

}
*/
   $addvar = "";
   $addJS = "";
   // pridavame zaznam, musime vybrat obrazek
   if($_GET['a']=='add') {
   //$form_data['text']='';
   $addJS = '
      else if (xtext == "" && xsoubor == "") {alert("Vyberte banner nebo doplňte text"); form1.text.focus(); return false;}';
   $addJS = '';   
   $addvar = '
      var xsoubor = form1.banner.value;
      xsoubor = xsoubor.replace(/ /g,""); // nahrada (zde jen odstraneni) mezer
      
      var xtext = form1.text.value;
      xtext = xtext.replace(/ /g,\"\"); // nahrada (zde jen odstraneni) mezer';
      }   
 	
	$editor="
      <textarea name='text'>".$form_data['text']."</textarea>
          
      <script type='text/javascript'>
                //<![CDATA[
                CKEDITOR.replace('text', {
                	height: '400px'
                });
                //]]>
      </script>
  ";


   
 
// if(empty($form_data['nazev'])) {
// 
//   $query = "SELECT nazev FROM ".T_INZERENTI." WHERE ".SQL_C_LANG." ORDER BY id DESC LIMIT 1";
//   $v = my_DB_QUERY($query,__LINE__,__FILE__);
//   $z = mysql_fetch_array($v);
//   
//   $form_data['nazev'] = $z['nazev']; 
// 
// }   
   
//    <!--".$akce_url."-->
   
   
   $form = "
   
   <SCRIPT LANGUAGE=\"JavaScript\">
   <!--
   function validate(form1) {
    $addvar
    
    var xnazev = form1.nazev.value;
    xnazev = xnazev.replace(/ /g,\"\"); // nahrada (zde jen odstraneni) mezer
         
    var xodkaz = form1.odkaz.value;
    xodkaz = xodkaz.replace(/ /g,\"\"); // nahrada (zde jen odstraneni) mezer
    
    if (xnazev == \"\") {alert(\"Vyplňte název banneru\"); form1.nazev.focus(); return false;}
     else if (xodkaz == \"\") {alert(\"Vyplňte odkaz banneru\"); form1.odkaz.focus(); return false;}
      $addJS
      else return true;
   
   }
   
   
   function del() {
   
      if (confirm(\"".$dct['opravdu_odstranit']."\"))
         { location = \"".$form_data['link']."&delete=".$form_data['id']."\"; }
   
   }
   // -->
   </SCRIPT>
   
   
   
   
   <form action=\"\" method=\"post\" enctype=\"multipart/form-data\" onSubmit=\"return validate(this)\">
   
   <input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
   <!-- <input type=\"hidden\" name=\"poradi\" value=\"".$form_data['poradi']."\">-->
   
   <table width=\"650\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
   <tr>
      <td colspan=\"2\">
         
         ".SAVE_BUTTON."
         
         ".$form_data['deletebutton']."
      </td>
   </tr>
<tr>
      <td colspan=\"2\">&nbsp;</td>      
   </tr>
   <tr>
      <td width=\"160\">&nbsp;</td>
      <td width=\"340\"><input type=\"checkbox\" name=\"new_window\" value=\"1\" 
          id=\"new_window\" ".$form_data['new_window']."> 
         <label for=\"new_window\">Otevřít v novém okně</label>
         
         &nbsp;&nbsp;&nbsp;
         
         <input type=\"checkbox\" name=\"hidden\" value=\"1\" id=\"skryt\" ".$form_data['hidden']."> 
         <label for=\"skryt\">Nezobrazovat</label> <br />
         
         
         <!--
         <input type=\"checkbox\" name=\"typ\" value=\"1\" id=\"typ\" ".$form_data['typ']."> 
         <label for=\"typ\">Zobrazit v záhlaví stránky</label>--> 
      </td>
   </tr>
   

   <tr>
      <td>Název akce</td>
      <td><input type=\"text\" name=\"nazev\" value=\"".$form_data['nazev']."\" style=\"width: 100%;\" class=\"f10\"></td>
   </tr>

   <tr>
      <td colspan=\"2\">&nbsp;</td>      
   </tr>
   
   
   
   
   <tr>
      <td>Odkaz</td>
      <td><input type=\"text\" name=\"odkaz\" value=\"".$form_data['odkaz']."\" style=\"width: 100%;\" class=\"f10\"></td>
   </tr>
   
   
   
  <tr>
  	<td>Pořadí</td>
  	<td width=\"330\">
  		<input type=\"text\" name=\"poradi\" value=\"".$form_data['poradi']."\" class=\"f10\" size=\"3\"></td>
  </tr>


   
   <tr>
      <td colspan=\"2\">
         <br /><br />
         Banner <span class=\"f10\">(JPG, GIF, PNG) Minimální šířka obrázku 204 px, větší obrázek bude na tuto velikost upraven.</span><br />
         ".$form_data['img']."<br />
         Vložit nový banner (obrázek s textem)<br />
         <input type=\"file\" name=\"banner\" style=\"width: 100%;\" class=\"f10\" /><br />
         <span class=\"f10i\"></span>
      </td>
   </tr>
   <tr>
      <td colspan=\"2\">&nbsp;</td>      
   </tr>
   
   <tr>
      <td colspan=\"2\">
      Vytvoření vlastního baneru (vložení obrázku, doplnění textu).<br /><strong>Vlastní baner v okně editoru má přednost před obrázkovám banerem vloženým výše</strong>.
          <br />
          			".$editor."
          <!--			
          Text<br />
          <textarea name=\"text\" style=\"width: 100%; height: 60px;\" 
    			class=\"f11\">".$form_data['text']."</textarea>
          -->
          
          </td>
   </tr>
   
   
   
   <tr>
      <td colspan=\"2\"><br /><br />
         
         ".SAVE_BUTTON."
         
         ".$form_data['deletebutton']."
      
      </td>
   </tr>
   
   </table>
   
   </form>
   <script type=\"text/javascript\">
	<!--
   //document.getElementById('divfotocat').style.display='none';
   //document.getElementById('divfiles').style.display='none';
   //document.getElementById('divclankysel').style.display='none';
  ".$js."
	-->
	</script>";
   
   return $form;

}

// *****************************************************************************
// zmena poradi 
// *****************************************************************************
if (!empty($_POST["posUpdate"])) {

  reset($_POST["posUpdate"]);
  while ($p = each($_POST["posUpdate"])) {
  
  	$id = $p['key'];
  	$pos = $p['value'];
  	
  	$query="SELECT id, poradi FROM ".T_INZERENTI." WHERE poradi >= ".$pos." AND NOT id=".$id." ORDER BY poradi";
  	$sql=my_DB_QUERY($query,__LINE__,__FILE__);
    if(mysql_num_rows($sql)>0) {
			while ($z = mysql_fetch_array($sql)) {
        $zmena_pos=$z['poradi']+1;
        $ID=$z['id'];
        $q = "UPDATE ".T_INZERENTI." SET poradi = $zmena_pos WHERE id = $ID ";
		    my_DB_QUERY($q,__LINE__,__FILE__);
      }
    }
  	
  	
		// id title content anotace in_menu pozice homepage hidden rubrika vlozeno upraveno upravil spustit ukoncit lang
		$q = "UPDATE ".T_INZERENTI." SET poradi = $pos WHERE id = $id ";
		my_DB_QUERY($q,__LINE__,__FILE__);
  
  }
  
  
	
	souvisla_rada(1,$rubrika);
	
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// zmena poradi 
// *****************************************************************************

function hidden_inzerent($id,$hidden) {
  //echo 'hidden_obor';
  //exit;

	// projde kategorie od zadaneho ID dolu, vyhleda vsechny 
	// podrizene urovne a nastavi jim parametr skryti/neskryti
	
	$query = "UPDATE ".T_INZERENTI." SET hidden = $hidden 
	WHERE id = $id AND ".SQL_C_LANG." ";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	/*
	$query = "SELECT id FROM ".T_INZERENTI." 
	WHERE id_parent = $id AND ".SQL_C_LANG." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		hidden_inzerent($z['id'],$hidden);
	}
*/
}



// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST) AND $_GET['a']!='list') { // echo $_POST['id']."<br />";

   $hidden = $_POST['hidden'];
   if($hidden != 1) $hidden = 0;
   
   $typ = $_POST['typ'];
   if($typ != 1) $typ = 0;
   else {
   
      // id  vlozeno  nazev  hidden  poradi  lang  odkaz  img  typ
      $query = "UPDATE ".T_INZERENTI." SET typ = 0 "; // zrusime puvodni nastaveni
      my_DB_QUERY($query,__LINE__,__FILE__);
   
   }
   
   
   $new_window = $_POST['new_window'];
   if($new_window != 1) $new_window = 0;
   
   
   
   $nazev = trim($_POST['nazev']);
   $odkaz = trim($_POST['odkaz']);
   $text = trim($_POST['text']);
   
   
   // osetrime hodnoty poradi
	if (empty($_POST['poradi']) || $_POST['poradi'] < 1) {
	    // $poradi = '0';
    $q1 = 'SELECT (MAX(poradi)+1) FROM '.T_INZERENTI.' WHERE lang='.C_LANG;
  	// $v1 = my_DB_QUERY($q1,__LINE__,__FILE__);
    $poradi = mysql_result(mysql_query("$q1"), 0); 
    $poradi = (int)$poradi;
    if ($poradi <= 0) $poradi = 1;
   }
   else $poradi = $_POST['poradi'];
   
   
   if (!empty($_POST['id'])) { // editace existujiciho
	
		// id title content anotace in_menu pozice homepage hidden rubrika vlozeno upraveno upravil spustit ukoncit lang
		$query = "SELECT poradi FROM ".T_INZERENTI." 
		WHERE id = ".$_POST['id']."";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$pos = mysql_result($v, 0, 0);
		
		
		if ($poradi == $pos) move_up($poradi,$in_menu);
		if ($poradi > $pos) move_down($poradi,$in_menu);
		if ($poradi < $pos) move_up($poradi,$in_menu);
   
   
   
   
   
      $id = $_POST['id'];
      
      // id  vlozeno  nazev  hidden  poradi  lang  odkaz  img  typ
      $query = "UPDATE ".T_INZERENTI." SET 
      nazev = '$nazev', 
      hidden = $hidden, 
      poradi = $poradi, 
      odkaz = '$odkaz', 
      typ = $typ, 
      text = '$text', 
      new_window = '$new_window' 
      WHERE id = $id";
      my_DB_QUERY($query,__LINE__,__FILE__);


// OLPRAN

//       $query = "UPDATE ".T_INZERENTI." SET 
//       nazev = '$nazev' 
//       WHERE lang = ".C_LANG." ";
//       my_DB_QUERY($query,__LINE__,__FILE__);

// OLPRAN
      
      $back = $_SERVER['HTTP_REFERER'];//MAIN_LINK."&f=inzerenti&a=list";
   
   } else { // novy zaznam
   
   move_up($poradi);
   
      // id  vlozeno  nazev  hidden  poradi  lang  odkaz  img  typ
      $query = "INSERT INTO ".T_INZERENTI."  
      VALUES(NULL,".time().",'$nazev',$hidden,$poradi,'".C_LANG."','$odkaz','',$typ,'$text',$new_window)";
      my_DB_QUERY($query,__LINE__,__FILE__);
      
      $query = "SELECT LAST_INSERT_ID()";
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
      $id = mysql_result($v, 0, 0);
      
      
      $back = $_SERVER['HTTP_REFERER'];
   
   }
   
   
   
   
   // upload obrazku
   if(!empty($_FILES['banner']['name'])) {
   
      // zjistime puvodni obrazek inzerenta
      // id  vlozeno  nazev  hidden  poradi  lang  odkaz  img  typ
      $query = "SELECT img FROM ".T_INZERENTI." WHERE id = ".$id." LIMIT 0,1";
      //echo $query;
      //exit;
      $v = my_DB_QUERY($query,__LINE__,__FILE__);
      while ($z = mysql_fetch_array($v)) {
      
         //echo($_SERVER['DOCUMENT_ROOT'].'/'.INZERENTI.$id.'.'.$z['img'].'<br />');
         @unlink($WWW_root.INZERENTI.$id.'.'.$z['img']);
         
      }
      
      // upload
      if(filesize($_FILES['banner']['tmp_name']) > 0) {
      
         // povolene pripony
         $povolene_pripony = array('jpg','gif','png'); 
         
         // zjistime priponu
         $x1 = explode (".", $_FILES['banner']['name']); // roztrhame nazev souboru - delicem je tecka
         $x2 = count($x1) - 1; // index posledniho prvku pole
         $e = strtolower($x1[$x2]); // mame priponu, prevedenou na mala pismena
         
         // kontrola pripony
         if(!in_array($e,$povolene_pripony)) {
         
            $_SESSION['alert_js'] = 'Nepovolený typ souboru!';
            header("location: ".$_SERVER['HTTP_REFERER']);
            exit;
         
         }
         
         
         $destination_filename = $WWW_root.'/'.INZERENTI.$id.'.'.$e;
         //echo $destination_filename;
         //exit;
         img_resize($_FILES['banner']['tmp_name'],$destination_filename,204,500,98,'w');
         
//          move_uploaded_file($_FILES['banner']['tmp_name'], $destination_filename);
         chmod($destination_filename, 0777);
         
         $query = "UPDATE ".T_INZERENTI." SET img = '$e' WHERE id = $id";
         my_DB_QUERY($query,__LINE__,__FILE__);
      
      }
   
   }
   
   souvisla_rada(1);
   
   $_SESSION['alert_js'] = $dct['zaznam_ulozen'];
   
   Header("Location: ".$back);
   exit;
}
// *****************************************************************************
// ulozeni zanamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// seznam administratoru
// *****************************************************************************
if($_GET['a'] == "list") {
/*
   // id  vlozeno  nazev  hidden  poradi  lang  odkaz  img  typ
   $query = "SELECT id, nazev, hidden, poradi  FROM ".T_INZERENTI." WHERE ".SQL_C_LANG." ORDER BY poradi,nazev";
   $v = my_DB_QUERY($query,__LINE__,__FILE__);
   
   while ($z = mysql_fetch_array($v)) {
   
      $form_data['id'] = $id = $z['id'];
      $form_data['nazev'] = $z['nazev'];
      $form_data['poradi'] = $z['poradi'];
      
      $form_data['hidden'] = $z['hidden'];
      if($form_data['hidden'] == 1) $stop_style = HIDDEN_STYLE;
      else $form_data['hidden'] = $stop_style = "";
      */
      // vygenerujeme pole s kategoriemi
	if(empty($inzerenti_array)) {
	
		$inzerenti_array = array();
		inzerenti_array($parent_id=0,$inzerenti_array,$level=0);
	
	}
	
	
	
	
	if(!empty($inzerenti_array)) {
	  
    $res='';
		
    reset ($inzerenti_array);
		while ($p = each($inzerenti_array)) {
		
			list ($level,$poradi,$par_id,$nazev,$hidden,$lang,$id) = explode ("|", $p['value']);
			
			// pokud je nastavena kat. jako skryta, projdeme vnorene kategorie 
			// a nastavime je take jako skryte
// 			if ($hidden == 1) $hidden_array[$id] = $id;
			
      	
			
			// nasleduje indikace skrytych kategorii, vcetne podkategorii 
			// (u podkategorii bez ohledu na skutecne nastaveni)
			
			// styl pro skryte polozky
			$hidden_style = "style=\"color: #939393;\"";
			// prednastavena hodnota pro nastaveni zobrazovani primo 
			// ze seznamu kategorii
			$set_hidden = 0;
			
			// zacatek skrytych kategorii (prvni v hierarchii)
			if ($hidden == 1 && $par_id != $h_parent[$par_id]) {
				$h = 1;
				$h_parent[$id] = $id; // children are hidden
				$alt_h = $dct['akce_zobrazeni_nepovoleno']." - ".$dct['akce_povolit_zobrazeni'];
			}
			// dalsi (vnorene) skryte kategorie
			else if (isset($h_parent[$par_id]) && $par_id == $h_parent[$par_id]) {
				$h = 3;
				$h_parent[$id] = $id; // children are hidden
				$alt_h = $dct['akce_zobrazeni_nepovoleno'];
			}
			// neskryte kategorie
			else {
				$h = 0;
				$alt_h = $dct['akce_zobrazeni_povoleno'];
				$hidden_style = "";
				$set_hidden = 1;
			}
			
			
			
			$h_img = "<img src=\"./icons/hidden_$h.gif\" alt=\"$alt_h\" 
				title=\"$alt_h\" border=\"0\" height=\"10\" width=\"13\" align=\"absmiddle\">";
			
			
			if ($h == 1 || $h == 0) { // neni skryta
			
				/*$h_img = "
				<img src=\"./icons/hidden_$h.gif\" alt=\"$alt_h\" title=\"$alt_h\" 
				border=\"0\" height=\"10\" width=\"13\">";*/
				$h_img = "
				<a href=\"".MAIN_LINK."&f=inzerenti&a=hidden&id=$id&hidden=$set_hidden\">$h_img</a>";
			
			}
			else if ($h == 3) { // je skryta
			
				$h_img = $h_img;
			
			}
      
      $res .= "
      <tr ".TABLE_ROW.">
         <td width=\"20\"  $hidden_style><input type=\"text\" name=\"posUpdate[$id]\" value=\"".$poradi."\" style=\"width: 25px;\" class=\"f10\"></td>
         <td class=\"td1\" $hidden_style nowrap>".$h_img." ".$nazev."</td>
         <td width=\"15\" class=\"td2\">
            ".ico_edit(MAIN_LINK."&amp;f=inzerenti&amp;C_lang=".C_LANG."&amp;a=edit&amp;id=".$id."",'upravit')."</td>
      </tr>";
   
   }
   
   
   if (!empty($res)) {
   
      $data = "
      ".SEARCH_PANEL."
      
      $addRecord
      <form method=\"post\">
      <table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
      $res
      </table>
      <br />
      <input type=\"submit\" class=\"butt_green\" value=\"Uložit pořadí\" />
      </form>";
   
   }
   
   }
   
   if(empty($data)) $data = "<br /><br />$addRecord".$dct['zadny_zaznam'];

}
// *****************************************************************************
// seznam administratoru
// *****************************************************************************









// *****************************************************************************
// editace zaznamu (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

   // id  vlozeno  nazev  hidden  poradi  lang  odkaz  img  typ
   $query = "SELECT * FROM ".T_INZERENTI." WHERE id = ".$_GET['id']." LIMIT 0,1";
   $v = my_DB_QUERY($query,__LINE__,__FILE__);
   
   while ($z = mysql_fetch_array($v)) {
   
      $form_data['id'] = $_GET['id'];
      $form_data['vlozeno'] = $z['vlozeno'];
      $form_data['nazev'] = $z['nazev'];
      $form_data['text'] = $z['text'];
      
      $form_data['hidden'] = $z['hidden'];
      if($form_data['hidden'] == 1) $form_data['hidden'] = "checked";
      else $form_data['hidden'] = "";
      
      $form_data['poradi'] = $z['poradi'];
      $form_data['lang'] = $z['lang'];
      $form_data['odkaz'] = $z['odkaz'];
      
      $form_data['new_window'] = $z['new_window'];
      if($form_data['new_window'] == 1) $form_data['new_window'] = "checked";
      else $form_data['new_window'] = "";
      
      if(!empty($z['img'])) {
      
         $form_data['img'] = $img = INZERENTI2.$_GET['id'].".".$z['img'];
         //echo $img; exit;
         $form_data['img'] = '<br /><br />'.imgTag($img,'','','','','',-1).'<br />';
      
      }
      
      $form_data['typ'] = $z['typ'];
      if($form_data['typ'] == 1) $form_data['typ'] = 'checked';
      
      $form_data['link'] = MAIN_LINK."&f=inzerenti";
      $form_data['deletebutton'] = DELETE_BUTTON;
   
   }
   
   
   if(!empty($form_data)) $data = form($form_data,$dct);
   else $data = $dct['zaznam_nenalezen'];
   
   
   
   $data = $addRecord.$data;

}
// *****************************************************************************
// editace zanamu (form)
// *****************************************************************************










// *****************************************************************************
// pridat zaznam (form)
// *****************************************************************************
if($_GET['a'] == "add") {
   
   $form_data=null;
   
   $data = form($form_data,$dct);

}
// *****************************************************************************
// pridat zaznam (form)
// *****************************************************************************


// *****************************************************************************
// skryta/neskryta kategorie
// *****************************************************************************
if($_GET['a'] == "hidden") {

	$hidden = $_GET['hidden'];
	if ($hidden != 1) $hidden = 0;
	
 	hidden_inzerent($_GET['id'],$hidden);
	
	$_SESSION['alert_js'] = $dct['zaznam_upraven'];
	
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;

}
// *****************************************************************************
// skryta/neskryta kategorie
// *****************************************************************************






// *****************************************************************************
// odstranit zaznam
// *****************************************************************************
if(!empty($_GET['delete'])) {

   // odstranime obrazek
   // id  vlozeno  nazev  hidden  poradi  lang  odkaz  img  typ
   $query = "SELECT img FROM ".T_INZERENTI." WHERE id = ".$_GET['delete']." LIMIT 0,1";
   $v = my_DB_QUERY($query,__LINE__,__FILE__);
   while ($z = mysql_fetch_array($v)) {
   
      @unlink($WWW_root.INZERENTI.$_GET['delete'].'.'.$z['img']);
   
   }
   
   
   
   // id title content in_menu menu_pos homepage hidden rubrika
   $query = "DELETE FROM ".T_INZERENTI." WHERE id = ".$_GET['delete']."";
   $v = my_DB_QUERY($query,__LINE__,__FILE__);
   souvisla_rada(1);
   my_OPTIMIZE_TABLE(T_INZERENTI);
   
   
   
   $_SESSION['alert_js'] = $dct['zaznam_odstranen'];
   
   Header("Location: ".MAIN_LINK."&f=inzerenti&a=list&C_lang=".C_LANG."");
   exit;

}
// *****************************************************************************
// odstranit zaznam
// *****************************************************************************

//include_once './_template.php';
?>
