<?php
// autor: Svatoslav Blaževský - info@netaction.cz
// změnou, kopírováním, prodejem či jiným rozmnožováním tohoto kódu bez souhlasu
// autora se dopouštíte porušování zákonů ČR dle autorského zákona a zákonů
// o duševním vlastnictví a vystavujete se možnosti soudního stíhání.


include_once($_SERVER['DOCUMENT_ROOT'].'/admin/_mysql.php');


// pole pro preklad nazvu sloupcu v DB na srozumitelne hodnoty pro uzivatele
$SLOUPCE_X_POPIS = array
(
  "name" => "Název",
  "kod" => "Kód",
  "hidden" => "Skrýt",
  "id_dodani" => "Dodací lhůta",
  "cena" => "Cena",
  "text" => "Dlouhý popis",
  "anotace" => "Krátký popis",
  "id_vyrobce" => "Výrobce",
  "ks" => "Počet",
  "sleva" => "Sleva",
  "EAN" => "EAN",
  "id_dph" => "DPH",
  "stitky" => "Štítky",
  "zarazeni_v_kategorii" => "Zařazení v kategorii",
  "id_variant" => "Varianty",
  "obrazek" => "Fotky u produktu",
  "souvisejici_produkty" => "Související produkty",
  "hmotnost" => "Hmotnost",
  "id_jednotka" => "Měrná jednotka",
  "anotace" => "Krátký popis"
);


global $JS_CENY;

$js_ceny = '
<script language="javascript">

'.$JS_CENY.'

function ceny_dph(typ)
{
  // ziskani hodnot z formulare
  var cena_bez_dph = parseFloat(document.produkt.cena_bez_dph.value.replace(" " , "").replace("," , "."));
  var cena_s_dph = parseFloat(document.produkt.cena_s_dph.value.replace(" " , "").replace("," , "."));
  var dph = document.produkt.id_dph[id_dph.selectedIndex].text;

  //Math.round(); // Zaokrouhlení
  if(typ == "bez_dph")
  { // zmena DPH nebo ceny bez DPH
    cena_s_dph = (cena_bez_dph * (dph / 100 + 1)); // zaokrouhlime na cele cislo
  }

  if(typ == "dph" || typ == "s_dph")
  { // zmena ceny s DPH
    cena_bez_dph = (cena_s_dph / (dph / 100 + 1)); // zaokrouhlime na cele cislo
  }

  // naformatovani vystupnich hodnot (999 999 999)
  document.forms["produkt"].cena_bez_dph.value = number_format(cena_bez_dph , 2 , "," , " "); //
  document.forms["produkt"].cena_s_dph.value = number_format(cena_s_dph , 2 , "," , " ");
}
</script>';


function get_good($id)
{
  $query = "
  SELECT * FROM ".T_GOODS."
  WHERE id = '".$id."';
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
	$z = mysql_fetch_array($v);
	
	return $z;
}


// *****************************************************************************
// formular pro editaci
// *****************************************************************************
function product_form($form_data , $dct)
{
	if(empty($form_data['text']))$form_data['text']='';

	$editor = "
      <textarea name='text'>".$form_data['text']."</textarea>

      <script type='text/javascript'>
                //<![CDATA[
                CKEDITOR.replace('text', {
                	height: '400px'
                });
                //]]>
      </script>
  ";

	if(empty($form_data['id']))
  { // novy zaznam

	}
  else
  { // editace existujiciho
		// soubory ke stazeni
		// id_good id_file lang
		$query = "SELECT id_file FROM ".T_GOODS_X_DOWNLOAD."
		WHERE id_good = ".$form_data['id']." AND ".SQL_C_LANG."";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v))
    {
			$files_selected[$z['id_file']] = "selected";
		}

		//dodaci lhuta
		$query = "SELECT id_dodani FROM ".T_GOODS." where id=".$form_data['id'];
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while ($z = mysql_fetch_array($v))
    {
			$dodaci_lhuta_selected[$z['id_dodani']] = "selected";
		}
	}

  	$dodaniOptions="";

  	// seznam dodacich lhut
  	$query="select * from ".T_DODACI_LHUTA." where hidden=0 and ".SQL_C_LANG." order by position";
  	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  	while ($z = mysql_fetch_array($v)) {
      if(!empty($dodaci_lhuta_selected[$z['id']])){
        $dodaniOptions.='<option value="'.$z['id'].'" '.$dodaci_lhuta_selected[$z['id']].'>'.$z['nazev'].'</option>';
      }else{
        $dodaniOptions.='<option value="'.$z['id'].'">'.$z['nazev'].'</option>';
      }
  	}

  	if(!empty($dodaniOptions))
    {
      $dodaniOptions="<select name='dodaci_lhuta'><!--<option value='0'>neuvedena</option>-->".$dodaniOptions."</select>";
   	}


	// **********************************************************
	// seznam se soubory ktere lze priradit k produktu ke stazeni
	$query = "SELECT id, odkaz, mime FROM ".T_DOWNLOAD."
	WHERE ".SQL_C_LANG." ORDER BY odkaz";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	$select = "<option value=\"NO\">žádný</option>";

	while ($z = mysql_fetch_array($v))
  {
    $mime = $z['mime'];
    $fid = $z['id'];

    if($mime=='video/x-ms-wmv' OR $mime=='application/octet-stream' OR $mime=='video/mpeg')
    {
      $selectVideo .= "<option value=\"$fid\" ".$files_selected[$fid].">".$z['odkaz']."</option>";
    }
    else
    {
      if(isset($files_selected[$fid]))
      {
    	  $select .= "<option value=\"$fid\" ".$files_selected[$fid].">".$z['odkaz']."</option>";
   	  }
   	  else
   	  {
        $select .= "<option value=\"$fid\">".$z['odkaz']."</option>";
      }
   	}
	}


	if (!empty($select)) {

		$select = "
		<select name=\"files[]\" class=\"adminselect\" size=\"6\" multiple=\"multiple\">
		$select
		</select>

		<span class=\"f10i\">
			více souborů lze označit přidr·ením klávesy Shift nebo Ctrl a zároveň
			kliknutím myši na název souboru.
		</span>";

	}
	else $select = "žádné soubory nebyly v databázi nalezeny.";

	if (!empty($selectVideo)) {

		$selectVideo = "
		<select name=\"videofiles[]\" class=\"adminselect\" size=\"6\" multiple=\"multiple\">
		$selectVideo
		</select>

		<span class=\"f10i\">
			více video souborů lze označit přidr·ením klávesy Shift nebo Ctrl a zároveň
			kliknutím myši na název souboru.
		</span>";

	}
	else $selectVideo = "žádné video soubory nebyly v databázi nalezeny.";
	// seznam se soubory ktere lze priradit k produktu ke stazeni
	// **********************************************************


// seznam variant 
if(!empty($form_data['id']))
{
  $form_data['id'] = intval($form_data['id']);

	$query = "
  SELECT id , name , hidden FROM ".T_GOODS."
	WHERE id_variant = '".$form_data['id']."'
  ORDER BY name
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	$varianty = '';

	while ($z = mysql_fetch_array($v))
  {
    if($z['hidden'])
    {
      $class = "hidden";
    }
    else
    {
      $class = "";
    }

    $varianty .= '<a class="fancybox_foto '.$class.'" href="/admin/shop/products/products_variants.php?id='.$z['id'].'">'.$z['name'].'</a><br>';
  }

  $varianty = '
  <div id="varianty_produktu" style="display:none;">
    <a class="fancybox_foto" href="/admin/shop/products/products_variants.php?id_parent='.$form_data['id'].'">Přidat variantu</a><br>
    <br>
    <b>Varianty produktu</b><br>
    '.$varianty.'<br>
  </div>';
}
else
{ // novy produkt - nepridavame varianty (neni z ceho)
  $varianty = '';
}


	// Příbuzné produkty
  $id_good = intval($form_data['id']);

	$query = "
  SELECT id_pribuzne
  FROM ".T_GOODS_PRIBUZNE."
  WHERE id_good = '".$id_good."'
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  $selected_pribuzne = array();
	while($z = mysql_fetch_array($v))
  {
		$selected_pribuzne[$z['id_pribuzne']] = 'checked="checked"';
	}

	$query = "
  SELECT id, name, kod, hidden
  FROM ".T_GOODS."
	WHERE id != '".$id_good."'
  AND id_variant = 0
  AND ".SQL_C_LANG."
  ORDER BY id DESC
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  $select_pribuzne = "";
  $select_pribuzne_check = "";
	while($z = mysql_fetch_assoc($v))
  {
    $class = "";
    if($z["hidden"] == 1)
    {
      $class .= "hidden";
    }

    if(isset($selected_pribuzne[$z['id']]))
    {
      $select_pribuzne_check .= '
      <input type="checkbox" name="pribuzne[]" value="'.$z['id'].'" '.$selected_pribuzne[$z['id']].' />
      <span class="'.$class.'">'.$z['name'].' '.$z['kod'].'</span><br />';
    }
    else
    {
      $select_pribuzne .= '
      <input type="checkbox" name="pribuzne[]" value="'.$z['id'].'" />
      <span class="'.$class.'">'.$z['name'].' '.$z['kod'].'</span><br />';
    }
	}

  $select_pribuzne = $select_pribuzne_check.'<br />'.$select_pribuzne; // Prvně označené příbuzné.


	/* Ceny */
  if(!isset($form_data["id_dph"])) $form_data["id_dph"] = 0;
  $dph = select_dph($form_data["id_dph"] , 'ceny_dph(\'dph\');');

	$select_dph = $dph["select"];
	$dph_procenta = $dph["dph"];

  if(!isset($form_data["cena"])) $form_data["cena"] = 0;
	$cena_s_dph = $form_data["cena"];
	$cena_bez_dph = cena_bez_dph($cena_s_dph , $dph_procenta);

  if(!isset($form_data["cena1"])) $form_data["cena1"] = 0;
	$cena_s_dph1 = $form_data["cena1"];
	$cena_bez_dph1 = cena_bez_dph($cena_s_dph1 , $dph_procenta);

  if(!isset($form_data["sleva"])) $form_data["sleva"] = 0;
	$sleva_s_dph = $form_data["sleva"];
	$sleva_bez_dph = cena_bez_dph($sleva_s_dph , $dph_procenta);

  if(!isset($form_data["sleva1"])) $form_data["sleva1"] = 0;
	$sleva_s_dph1 = $form_data["sleva1"];
	$sleva_bez_dph1 = cena_bez_dph($sleva_s_dph1 , $dph_procenta);

  global $JS_NUMBER_FORMAT;
  global $js_ceny;
  /* END Ceny */


	// seznam produktu pro prirazeni jako pribuzne
	// **********************************************************
	if($_GET['a'] == "copy")
  { // kopie
		$form_data['id'] = '';//-1000
		$form_data['copy_button'] = '';
		$form_data['deletebutton'] = '';
	}
  else if ($_GET['a'] == "add")
  { // novy
		$form_data['copy_button'] = '';
	}
  else
  { // editace
		$editList = '
			<a href="'.MAIN_LINK.'&f=products_parameters&a=parameters&Pid='.$form_data['id'].'" class="">
			Vystavit/upravit produktový list</a><br>';// '.$form_data['name'].'

		$form_data['copy_button'] = button('button','Vytvořit kopii','class="butt_ostatni" onclick="location=\''.MAIN_LINK.'&f=products&id='.$form_data['id'].'&a=copy&cat ='.$_GET['cat'].' \'"');
		//$form_data['copy_button'] = "";
	}

	if(empty($form_data['deletebutton']))$form_data['deletebutton']='';
	if(empty($form_data['copy_button']))$form_data['copy_button']='';
	if(empty($_GET['cat']))$_GET['cat']='';
	if(empty($editList))$editList='';

	$buttony = "".SAVE_BUTTON." ".$form_data['deletebutton']." ".$form_data['copy_button']."";

	//vložení SEO
  $SEO=form_seo($form_data,1);

	if(empty($form_data['id']))$form_data['id']='';
	if(empty($form_data['add_del']))$form_data['add_del']='';
	if(empty($form_data['link']))$form_data['link']='';
	if(empty($form_data['hidden']))$form_data['hidden']='';
  if(empty($form_data['hidden1']))$form_data['hidden1']='';
	if(empty($form_data['name']))$form_data['name']='';
  if(empty($form_data['fakt_nazev']))$form_data['fakt_nazev']='';
	if(empty($form_data['kod']))$form_data['kod']='';
	if(empty($form_data['id_vyrobce']))$form_data['id_vyrobce']='';
	if(empty($form_data['cena']))$form_data['cena'] = '';
  if(empty($form_data['cena1']))$form_data['cena1'] = '';
	if(empty($form_data['sleva']))$form_data['sleva'] = '';
  if(empty($form_data['sleva1']))$form_data['sleva1'] = '';
	if(empty($form_data['imgdata']))$form_data['imgdata']='';
	if(empty($form_data['img']))$form_data['img']='';
	if(empty($form_data['anotace']))$form_data['anotace']='';
	if(empty($form_data['ks']))$form_data['ks'] = 0;
	if(empty($form_data['EAN']))$form_data['EAN'] = '';
	if(empty($pid))$pid='';
	if(empty($js))$js='';

  
  if(isset($form_data['id']) AND !empty($form_data['id']) AND $form_data['id'] > 0)
  {
    // fotky k produktu do noveho okna
    $foto_produktu = '<a class="fancybox_foto" href="/admin/shop/products/products_foto.php?id='.$form_data['id'].'">Fotky produktu</a>';

    // prava prepisovani do noveho okna
    $prava_prepisovani = '<a class="fancybox_foto" href="/admin/shop/products/products_i_e_setting.php?id_produkt='.$form_data['id'].'">Práva přepisování</a>';

    // Nastavení feedu
    $modul_feed_setting = $_SERVER['DOCUMENT_ROOT'].'/admin/shop/feed/feed_setting.php';
    if(file_exists($modul_feed_setting))
    { // Modul sklad musí být nainstalovaný
      include_once($modul_feed_setting);
      $feed_setting = '<a class="fancybox_foto" href="/admin/shop/feed/feed_setting.php?id_product='.$form_data['id'].'">Nastavení feedu</a>';
    }

    // Příbuzné kategorie.
    $modul_pribuzne_kategorie = $_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_related_cat.php';
    if(file_exists($modul_pribuzne_kategorie))
    { // Modul sklad musí být nainstalovaný
      $pribuzne_kategorie = '<a class="fancybox_foto" href="/admin/shop/products/products_related_cat.php?id_produkt='.$form_data['id'].'">Související kategorie</a>';
    }

    // Nákup produktu od
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_price_condition.php'))
    { // pokud je nainstalovan modul scenové podmínky a pouze u jiz existujicich produktu
      $price_condition = '<a class="fancybox_foto" href="/admin/shop/products/products_price_condition.php?id_product='.$form_data['id'].'">Podmínky Nákupu</a>';
    }

    // modul cenove skupiny
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/shop/price_group.php'))
    { // pokud je nainstalovan modul cenove skupiny
      $price_group = '<a class="fancybox_foto" href="/admin/shop/price_group.php?id_product='.$form_data['id'].'">'.$dct["price_group"].'</a>';
    }

    $modul_editace_objednavek = $_SERVER['DOCUMENT_ROOT'].'/admin/shop/order/order_edit.php';
    if(file_exists($modul_editace_objednavek))
    { // pokud je nainstalovan modul editace objednavek a pouze u jiz existujicich produktu
      include_once($modul_editace_objednavek);

      global $FIXED_BOTTOM_BAR;

      $add_to_order = edit_order_a("Přidat do objednávky" , "?id_produkt=".intval($form_data['id']));

      $FIXED_BOTTOM_BAR .= $add_to_order;
      $buttony .= $add_to_order;
    }
  }

  // modul sklad
  $modul_sklad = $_SERVER['DOCUMENT_ROOT'].'/admin/shop/products/products_store.php';
  if(file_exists($modul_sklad))
  { // pokud je nainstalovan modul sklad a pouze u jiz existujicich produktu
    if(isset($form_data['id']) AND !empty($form_data['id']))
    {
      $store = '<a class="fancybox_foto" href="/admin/shop/products/products_store.php?id_product='.$form_data['id'].'">Sklad</a>';
    }
  }
  else
  {
    $store = '<input type="text" id="pocet" name="ks" value="' . $form_data["ks"] . '" style="width: 100px;" />';
  }


	$form = "
  <script language=\"javascript\" type=\"text/javascript\">
	function validate(form)
  {
		var cena = form.cena_s_dph.value;
		cena = cena.replace(/,/g,'.'); // nahrada carky za tecku
		cena = cena.replace(/ /g,'');  // odstraneni mezer
		var cena1 = form.cena_s_dph1.value;
		cena1 = cena1.replace(/,/g,'.');
		cena1 = cena1.replace(/ /g,'');

    var prvky = form.elements;
    var pocet = prvky.length;
    var i;
    var kontrola_kategorie = 0;

    for (i=0;i<pocet;i++)
    {
      if(prvky[i].type == \"checkbox\" && prvky[i].name == \"id_parent[]\" && prvky[i].checked)
      {
        kontrola_kategorie = 1;
        break;
      }
    }

		if(form.name.value == \"\")
    {
      alert(\"".$dct['Vyplnte_nazev']."\");
      form.name.focus();
      return false;
    }
 		if(!(cena > 0))
    {
      alert(\"".$dct['Vyplnte_cenu']."\");
      form.cena_s_dph.focus();
      return false;
    }
 		if(!(cena1 > 0))
    {
      alert(\"".$dct['Vyplnte_cenu']."\");
      form.cena_s_dph1.focus();
      return false;
    }

    return true;
	}


	// odstraneni zaznamu
	function del()
  {
		if(confirm(\"Opravdu odstranit?\"))
		{
      location = \"".$form_data['link']."&delete=".$form_data['id']."&cat=".$_GET['cat'].$form_data['add_del']."\";
    }
	}
	</script>

	".$js_ceny."

	<form name=\"produkt\" action=\"\" method=\"post\" enctype=\"multipart/form-data\" onSubmit=\"return validate(this);\">

  <div>
	  ".$buttony."
  </div>

	<br>

  <div>
	  <input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	  <input type=\"hidden\" name=\"oldParams\" value=\"".$pid."\">
	  <input type=\"hidden\" name=\"action\" value=\"".$_GET['a']."\">
  </div>

	<table class='admintable nobg' border=\"0\" cellspacing=\"2\" cellpadding=\"0\">

	<tr>
		<td class=\"tdleft\"></td>
		<td class=\"tdright\">
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" ".$form_data['hidden']."> ".$dct["skryt"]." RO<br>
      <input type=\"checkbox\" name=\"hidden1\" value=\"1\" ".$form_data['hidden1']."> ".$dct["skryt"]." RR<br>
		</td>
	</tr>
  ";

	if(STITKY == 1)
  {
    $add_stitek = stitky_pro_form_tab($form_data['id']);
  }

	$form .= "
  <tr><td colspan=\"2\">&nbsp;</td></tr>
	<tr>
		<td class=\"tdleft\">Název</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"name\" value=\"".$form_data['name']."\"  />
    </td>
	</tr>

	<tr>
		<td class=\"tdleft\">Fakturační název</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"fakt_nazev\" value=\"".$form_data['fakt_nazev']."\"  />
    </td>
	</tr>

	<tr>
		<td class=\"tdleft\">Kód</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"kod\" value=\"".$form_data['kod']."\" />
    </td>
	</tr>

	<tr>
		<td class=\"tdleft\">EAN</td>
		<td class=\"tdright\">
			<input type=\"text\" name=\"EAN\" value=\"".$form_data['EAN']."\" />
    </td>
	</tr>

	<tr>
		<td class=\"tdleft\">Výrobce</td>
		<td class=\"tdright\">".$form_data['id_vyrobce']."</td>
	</tr>

	<tr>
		<td class=\"tdleft\">
    Dodací lhůta
    ".get_info('Použije se pokud je počet kusů menší nebo roven 0. Jinak je dodací lhůta skladem.')."
    </td>
	  <td class=\"tdright\">".$dodaniOptions."</td>
	</tr>

	<tr><td colspan=\"2\">&nbsp;</td></tr>

	<tr>
		<td>
			Cena RO
    </td>
		<td>
			<table cellspacing=\"0\" cellpadding=\"0\" style=\"margin-bottom:0;\">
        <tr><td>bez DPH</td><td>s DPH</td><td>DPH</td><td></td></tr>
        <tr>
          <td>
            <input type=\"text\" id=\"cena_bez_dph\" name=\"cena_bez_dph\" value=\"" . number_format($cena_bez_dph , 2 , ',' , ' ') . "\" style=\"width: 100px;\" onChange=\"ceny_dph('bez_dph');\" />
          </td>
          <td>
            <input type=\"text\" id=\"cena_s_dph\" name=\"cena_s_dph\" value=\"" . number_format($cena_s_dph , 2 , ',' , ' ') . "\" style=\"width: 100px;\" onChange=\"ceny_dph('s_dph');\" />
          </td>
          <td>
            ".$select_dph."
          </td>
          <td>
            <input type=\"button\" name=\"prepocitat\" value=\"Přepočítat\" />
          </td>
        </tr>
        <tr>
      </table>
		</td>
	</tr>

	<tr>
		<td>
			Cena RR
    </td>
		<td>
			<table cellspacing=\"0\" cellpadding=\"0\" style=\"margin-bottom:0;\">
        <tr>
        <td>
          <input
            id=\"cena_bez_dph1\"
            style=\"width: 100px;\"
            onchange=\"ceny('bez_dph' , 'cena_bez_dph1' , 'cena_s_dph1' , ".$dph_procenta.")\"
            type=\"text\" name=\"cena_bez_dph1\"
            value=\"".(($form_data["cena1"] > 0) ? number_format($cena_bez_dph1 , 2 , ',' , ' ') : "")."\" />
        </td>
        <td>
          <input
            id=\"cena_s_dph1\"
            style=\"width: 100px;\"
            onchange=\"ceny('s_dph' , 'cena_bez_dph1' , 'cena_s_dph1' , ".$dph_procenta.")\"
            type=\"text\" name=\"cena_s_dph1\"
            value=\"".(($form_data["cena1"] > 0) ? number_format($cena_s_dph1 , 2 , ',' , ' ') : "")."\" />
        </td>
        <td style=\"padding-left:10px; width:90px;\">

        </td>
        <td>
        </td>
        </tr>
      </table>
		</td>
	</tr>

	<tr>
		<td>
			Cena po slevě RO
    </td>
		<td>
			<table cellspacing=\"0\" cellpadding=\"0\" style=\"margin-bottom:0;\">
        <tr>
        <td>
          <input
            id=\"".$form_data['id']."_bez_dph\"
            style=\"width: 100px;\"
            onchange=\"ceny('bez_dph' , '".$form_data['id']."_bez_dph' , '".$form_data['id']."_s_dph' , ".$dph_procenta.")\"
            type=\"text\" name=\"sleva_bez_dph\"
            value=\"".(($form_data["sleva"] > 0) ? number_format($sleva_bez_dph , 2 , ',' , ' ') : "")."\" />
        </td>
        <td>
          <input
            id=\"".$form_data['id']."_s_dph\"
            style=\"width: 100px;\"
            onchange=\"ceny('s_dph' , '".$form_data['id']."_bez_dph' , '".$form_data['id']."_s_dph' , ".$dph_procenta.")\"
            type=\"text\" name=\"sleva_s_dph\"
            value=\"".(($form_data["sleva"] > 0) ? number_format($sleva_s_dph , 2 , ',' , ' ') : "")."\" />
        </td>
        <td style=\"padding-left:10px; width:90px;\">
          
        </td>
        <td>
        </td>
        </tr>
      </table>
		</td>
	</tr>

	<tr>
		<td>
			Cena po slevě RR
    </td>
		<td>
			<table cellspacing=\"0\" cellpadding=\"0\" style=\"margin-bottom:0;\">
        <tr>
        <td>
          <input
            id=\"".$form_data['id']."_bez_dph1\"
            style=\"width: 100px;\"
            onchange=\"ceny('bez_dph' , '".$form_data['id']."_bez_dph1' , '".$form_data['id']."_s_dph1' , ".$dph_procenta.")\"
            type=\"text\" name=\"sleva_bez_dph1\"
            value=\"".(($form_data["sleva1"] > 0) ? number_format($sleva_bez_dph1 , 2 , ',' , ' ') : "")."\" />
        </td>
        <td>
          <input
            id=\"".$form_data['id']."_s_dph1\"
            style=\"width: 100px;\"
            onchange=\"ceny('s_dph' , '".$form_data['id']."_bez_dph1' , '".$form_data['id']."_s_dph1' , ".$dph_procenta.")\"
            type=\"text\" name=\"sleva_s_dph1\"
            value=\"".(($form_data["sleva1"] > 0) ? number_format($sleva_s_dph1 , 2 , ',' , ' ') : "")."\" />
        </td>
        <td style=\"padding-left:10px; width:90px;\">

        </td>
        <td>
        </td>
        </tr>
      </table>
		</td>
	</tr>

	<tr><td colspan=\"2\">&nbsp;</td></tr>

  ".(
  (!isset($store) OR empty($store))
  ? ""
  : "<tr><td>Počet</td><td>".$store."</td></tr>
     <tr><td colspan=\"2\">&nbsp;</td></tr>"
  )."

  ".(
  (!isset($foto_produktu) OR empty($foto_produktu))
  ? ""
  : "<tr><td colspan=\"2\">".$foto_produktu."</td></tr>"
  )."

  ".(
  (!isset($prava_prepisovani) OR empty($prava_prepisovani))
  ? ""
  : "<tr><td colspan=\"2\">".$prava_prepisovani."</td></tr>"
  )."

  ".(
  (!isset($price_group) OR empty($price_group))
  ? ""
  : "<tr><td colspan=\"2\">".$price_group."</td></tr>"
  )."

  ".(
  (!isset($price_condition) OR empty($price_condition))
  ? ""
  : "<tr><td colspan=\"2\">".$price_condition."</td></tr>"
  )."

  ".(
  (!isset($feed_setting) OR empty($feed_setting))
  ? ""
  : "<tr><td colspan=\"2\">".$feed_setting."</td></tr>"
  )."

<!--
  <tr>
		<td class=\"tdleft\" valign=\"top\">
      <a href=\"\" class=\"click\" onclick=\"s('varianty_produktu'); return false;\">Varianty produktu</a> &raquo;
    </td>
		<td class=\"tdright\">
      ".$varianty."
    </td>
  </tr>
-->

  ".$add_stitek ."

	<tr>
		<td class=\"tdleft\" valign=\"top\">
      <a href=\"\" class=\"click\" onclick=\"s('divfiles'); return false;\">Připojit soubory ke stažení</a> &raquo;
    </td>
		<td class=\"tdright\">
      <div id=\"divfiles\">
			".$select."
			</div>
		</td>
	</tr>

  ".(
  (!isset($pribuzne_kategorie) OR empty($pribuzne_kategorie))
  ? ""
  : "<tr><td colspan=\"2\">".$pribuzne_kategorie."</td></tr>"
  )."

	<tr>
		<td class=\"tdleft\" valign=\"top\">
      <a href=\"\" class=\"click\" onclick=\"s('div_souvisejici'); return false;\">Související produkty</a> &raquo;
    </td>
		<td class=\"tdright\">
      <div id=\"div_souvisejici\">
        ".$select_pribuzne."
      </div>
    </td>
	</tr>

  <!--
  <tr>
		<td class=\"tdleft\" valign=\"top\">Produktový list</td>
		<td class=\"tdright\">$editList</td>
	</tr>
  -->

  <tr><td colspan=\"2\">&nbsp;</td></tr>

	<tr>
		<td colspan=\"2\">
		Krátký popis<br>
		<textarea name=\"anotace\" style=\"height: 60px;\">".$form_data['anotace']."</textarea></td>
	</tr>

	<tr>
		<td colspan=\"2\">
			<br>Dlouhý popis<br>
			$editor
		</td>
	</tr>

	<tr>
		<td colspan=\"2\">
      <br><br>
			".$buttony."
		</td>
	</tr>

	</table>



	<div class=\"kategorie_zarazeni\">
		<b>Zařadit do kategorie</b><br>
    <br>
		".$form_data['id_parent']."
	</div><br><br>
  $SEO
	</form>

  <script type=\"text/javascript\">
   if(divfotocat = document.getElementById('divfotocat'))
   {
     divfotocat.style.display='none';
   }

   if(divfiles = document.getElementById('divfiles'))
   {
     divfiles.style.display='none';
   }

   if(divvideofiles = document.getElementById('divvideofiles'))
   {
     divvideofiles.style.display='none';
   }

   if(div_varianty = document.getElementById('div_varianty'))
   {
     div_varianty.style.display='none';
   }

   if(div_souvisejici = document.getElementById('div_souvisejici'))
   {
     div_souvisejici.style.display='none';
   }

   ".$js."
	</script>
  ";

	return $form;
}
// *****************************************************************************
// formular pro editaci
// *****************************************************************************


// *****************************************************************************
// odstraneni obrazku produktu
// *****************************************************************************
/* odstrani fotku produktu
@param id_foto - id fotky ktera se ma odstranit

return 0 - OK
*/
function delete_product_foto($id_foto)
{
  $id_foto = intval($id_foto);

  $query = "SELECT name FROM ".T_FOTO_ZBOZI." WHERE id = '".$id_foto."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);
  $foto_name = $z["name"];

  $query = "DELETE FROM ".T_FOTO_ZBOZI." WHERE id = '".$id_foto."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  // Zjistíme zda fotka není používána jinde.
  $query = "SELECT count(name) AS count_foto FROM ".T_FOTO_ZBOZI." WHERE name = '".$foto_name."' AND id != '".$id_foto."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);
  if(isset($z["count_foto"]) AND $z["count_foto"] > 0)
  {
  }
  else
  { // Foto se používá ještě jinde.
    unlink(IMG_P_S_RELATIV . $foto_name);
    unlink(IMG_P_M_RELATIV . $foto_name);
    unlink(IMG_P_O_RELATIV . $foto_name);
  }

  return 0;
}

/*
odstrani vsechny obrazky u produktu

@param (int) good - id produktu
*/
function delete_img_good($good)
{
  // zjistime id vsech fotek patrici k produktu
  $query = "
  SELECT id
  FROM ".T_FOTO_ZBOZI."
	WHERE id_good = '".$good."'
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while ($z = mysql_fetch_array($v))
  { // projdeme vsechny obrazky prirazene k produktu a odstranime je
    delete_product_foto($z["id"]);
	}
}
// *****************************************************************************
// odstraneni obrazku produktu
// *****************************************************************************


/*
odkaz na produkt v administraci
@param (int) id - id produktu

return (string) odkaz - URL odkaz na produkt v administraci
*/
function odkaz_na_produkt_admin($id)
{
  $odkaz = 'http://' . $_SERVER["SERVER_NAME"] . '/admin/index.php?C_lang=' . $_SESSION["admin"]["C_LANG"] . '&app=shop&f=products&id='.$id.'&a=edit';

  return $odkaz;
}


/***********************************************************
/ funkce pro nastaveni importu z externich systemu
/**********************************************************/

/*
@param (int) id_produkt - id produktu z eshopu

@return (array) priznaky co prepsat a co ne (0 - prepis povolen, 1 - neprepisovat)
*/
function get_setting_rewriting_import($id_produkt)
{
  $query = "
  SELECT
  name,
  text,
  hidden,
  id_dodani,
  cena,
  id_dph,
  kod,
  id_vyrobce,
  ks,
  sleva,
  EAN,
  id_jednotka,
  obrazek,
  souvisejici_produkty,
  zarazeni_v_kategorii,
  stitky,
  id_variant,
  hmotnost,
  anotace
  FROM ".T_I_E_ZBOZI_PREPISOVAN."
  WHERE
  id_eshop = '".intval($id_produkt)."'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);

  $flag = array
  (
    "name" => !isset($z["name"]) ? 0 : $z["name"],
    "kod" => !isset($z["kod"]) ? 0 : $z["kod"],
    "EAN" => !isset($z["EAN"]) ? 0 : $z["EAN"],
    "id_vyrobce" => !isset($z["id_vyrobce"]) ? 0 : $z["id_vyrobce"],
    "id_dodani" => !isset($z["id_dodani"]) ? 0 : $z["id_dodani"],
    "cena" => !isset($z["cena"]) ? 0 : $z["cena"],
    "id_dph" => !isset($z["id_dph"]) ? 0 : $z["id_dph"],
    "sleva" => !isset($z["sleva"]) ? 0 : $z["sleva"],
    "ks" => !isset($z["ks"]) ? 0 : $z["ks"],
    "anotace" => !isset($z["name"]) ? 0 : $z["anotace"],
    "text" => !isset($z["text"]) ? 0 : $z["text"],
    "obrazek" => !isset($z["obrazek"]) ? 0 : $z["obrazek"],
    "stitky" => !isset($z["stitky"]) ? 0 : $z["stitky"],
    "souvisejici_produkty" => !isset($z["souvisejici_produkty"]) ? 0 : $z["souvisejici_produkty"],
    "zarazeni_v_kategorii" => !isset($z["zarazeni_v_kategorii"]) ? 0 : $z["zarazeni_v_kategorii"],
    "hidden" => !isset($z["hidden"]) ? 0 : $z["hidden"],
    // vypnuto dodelat do prototypu
    //"id_variant" => !isset($z["id_variant"]) ? 0 : $z["id_variant"], 
    //"id_jednotka" => !isset($z["id_jednotka"]) ? 0 : $z["id_jednotka"],
    //"hmotnost" => !isset($z["hmotnost"]) ? 0 : $z["hmotnost"]
  );

  return $flag;
}


/*
@param (int) id_produkt - id produktu z eshopu
@param (array) - priznaky co prepsat a co ne (0 - prepis povolen, 1 - neprepisovat)

@return 0 = vse je ok
*/
function set_rewriting_import($id_produkt , $flag)
{
  $id_produkt = intval($id_produkt); // osetreni pro pouziti v DB

  $query = "
  SELECT COUNT(id_eshop) AS pocet
  FROM ".T_I_E_ZBOZI_PREPISOVAN."
  WHERE
  id_eshop = '".$id_produkt."'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);
  $pocet = $z["pocet"]; // pokud je vice zaznamu jde o duplicity

  $ulozit_priznaky = array_sum($flag); // pokud je soucet hodnot 0 nemusime ukladat priznaky

  if($pocet > 1 OR ($ulozit_priznaky == 0 AND $pocet == 1))
  { // duplicity nebo neni treba ukladat priznaky (jsou nulove) a dosavadni muzeme smazat
    $query = "
    DELETE FROM ".T_I_E_ZBOZI_PREPISOVAN."
    WHERE
    id_eshop = '".intval($id_produkt)."'
    ";
    my_DB_QUERY($query,__LINE__,__FILE__);

    $pocet = 0; // v DB uz nejsou zadne zaznamy
  }

  if($ulozit_priznaky > 0)
  {
    // spolecne sloupce pro aktualizaci i vkladani ( povolene hodnoty pouze 0 a 1)
    $query = "
    name = '".((isset($flag["name"]) AND !empty($flag["name"])) ? 1 : 0)."',
    text = '".((isset($flag["text"]) AND !empty($flag["text"])) ? 1 : 0)."',
    anotace = '".((isset($flag["anotace"]) AND !empty($flag["anotace"])) ? 1 : 0)."',
    hidden = '".((isset($flag["hidden"]) AND !empty($flag["hidden"])) ? 1 : 0)."',
    id_dodani = '".((isset($flag["id_dodani"]) AND !empty($flag["id_dodani"])) ? 1 : 0)."',
    cena = '".((isset($flag["cena"]) AND !empty($flag["cena"])) ? 1 : 0)."',
    id_dph = '".((isset($flag["id_dph"]) AND !empty($flag["id_dph"])) ? 1 : 0)."',
    kod = '".((isset($flag["kod"]) AND !empty($flag["kod"])) ? 1 : 0)."',
    id_vyrobce = '".((isset($flag["id_vyrobce"]) AND !empty($flag["id_vyrobce"])) ? 1 : 0)."',
    ks = '".((isset($flag["ks"]) AND !empty($flag["ks"])) ? 1 : 0)."',
    sleva = '".((isset($flag["sleva"]) AND !empty($flag["sleva"])) ? 1 : 0)."',
    EAN = '".((isset($flag["EAN"]) AND !empty($flag["EAN"])) ? 1 : 0)."',
    id_variant  = '".((isset($flag["id_variant"]) AND !empty($flag["id_variant"])) ? 1 : 0)."',
    stitky  = '".((isset($flag["stitky"]) AND !empty($flag["stitky"])) ? 1 : 0)."',
    souvisejici_produkty  = '".((isset($flag["souvisejici_produkty"]) AND !empty($flag["souvisejici_produkty"])) ? 1 : 0)."',
    obrazek  = '".((isset($flag["obrazek"]) AND !empty($flag["obrazek"])) ? 1 : 0)."',
    zarazeni_v_kategorii  = '".((isset($flag["zarazeni_v_kategorii"]) AND !empty($flag["zarazeni_v_kategorii"])) ? 1 : 0)."',
    id_jednotka  = '".((isset($flag["id_jednotka"]) AND !empty($flag["id_jednotka"])) ? 1 : 0)."',
    hmotnost  = '".((isset($flag["hmotnost"]) AND !empty($flag["hmotnost"])) ? 1 : 0)."'
    ";


    if($pocet == 1)
    { // uprava priznaku
      $query = "
      UPDATE ".T_I_E_ZBOZI_PREPISOVAN."
      SET
      ".$query."
      WHERE
      id_eshop = '".$id_produkt."'
      ";
    }
    else
    { // vkladani priznaku
      $query = "
      INSERT INTO ".T_I_E_ZBOZI_PREPISOVAN."
      SET
      id_eshop = '".$id_produkt."',
      ".$query."
      ";
    }

    my_DB_QUERY($query,__LINE__,__FILE__); // provedeni dotazu (update nebo insert)
  }

  return 0;
}
/***********************************************************
/ end funkce pro nastaveni importu z externich systemu
/**********************************************************/


/*
ID souvisejicich produktu

@param (int) id_produktu

@return (array) - pole id pribuznych produktu
*/
function get_related($id_produkt)
{
  $related = array();

  $query = "
  SELECT id_pribuzne
  FROM ".T_GOODS_PRIBUZNE."
  WHERE
  id_good = '".intval($id_produkt)."'
  ";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  while($z = mysql_fetch_assoc($v))
  {
    $related[] = $z["id_pribuzne"];
  }

  return $related;
}


/** Odstrani produkt
@param (ind) id produktu
*/
function delete_produkt($id)
{
  $id = intval($id);

  // odstraneni variant
  $query = "SELECT id FROM ".T_GOODS." WHERE id_variant = '".$id."'";
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  while($z = mysql_fetch_assoc($v))
  {
    delete_produkt($z["id"]);
  }
  // odstraneni variant

  // Odstraneni zaznamu o prirazenych souborech.
  $query = "DELETE FROM ".T_GOODS_X_DOWNLOAD." WHERE id_good = '".$id."'";
 	my_DB_QUERY($query , __LINE__ , __FILE__);

  // Odstrani pribuzne.
	$query = "DELETE FROM ".T_GOODS_PRIBUZNE." WHERE id_good = '".$id."'";
	my_DB_QUERY($query,__LINE__,__FILE__);

  // Odstranění příbuzných kategorií.
  $query = "DELETE FROM ".T_GOODS_PRIBUZNE_KATEGORIE." WHERE id_produkt = '".$id."'";
  my_DB_QUERY($query,__LINE__,__FILE__);

	// Odstraneni z kategorii.
  $query = "DELETE FROM ".T_GOODS_X_CATEGORIES." WHERE id_good = '".$id."'";
 	my_DB_QUERY($query , __LINE__ , __FILE__);

	// Odstraneni u stitky-
  $query = "DELETE FROM ".T_GOOD_X_STITKY." WHERE id_good = '".$id."'";
 	my_DB_QUERY($query , __LINE__ , __FILE__);

  // Odstranění z externího systému.
  $query = "DELETE FROM ".T_I_E_ZBOZI." WHERE id_eshop = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

  // Nastaveni prepisovani z externich systemu.
  $query = "DELETE FROM ".T_I_E_ZBOZI_PREPISOVAN." WHERE id_eshop = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

  // Nastavení feedu.
  $query = "DELETE FROM ".T_FEED_SETTING." WHERE id_product = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

  // Parametry produktu
  $query = "DELETE FROM ".T_PARAMETRY4." WHERE id_produkt = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

  // Nastavení cenové skupiny.
  $query = "DELETE FROM ".T_PRICE_GROUP_X_PRODUCT." WHERE id_product = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

  // Nastavení cenových podmínek.
  $query = "DELETE FROM ".T_PRICE_CONDITION." WHERE id_product = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

  // Pořadí v kategorii.
  $query = "DELETE FROM ".T_GOODS_ORDER." WHERE id_product = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

	// smazani SEO
  delete_seo($id , 3);  // kcemu ... 1-clanek, 2-kategorie, 3-produkt
  // odstraneni obrazku
  delete_img_good($id);

  // Smaze samotny produkt.
  $query = "DELETE FROM ".T_GOODS." WHERE id = '".$id."'";
  my_DB_QUERY($query , __LINE__,__FILE__);

  unset($_SESSION["i_e"]["id_eshop_zbozi"]); // Proměnná by mohla obsahovat záznam o produktu.
}


?>
