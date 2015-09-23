<?php

// TODO vyhledavani resit GETem kvuli presunu back (napr u detailu produktu 
// otevreneho z vysledku vyhledavani)

// TODO pri odstranovani zaznamu (vyrobce, soubory ke stazeni, kategorie atd) je 
// treba zjistit zda na odstranovany zaznam neukazuje jiny zaznam - priklad: 
// odstraneni vyrobce - zjistit u kterych produktu je tento vyrobce uveden 
// a neco s tim udelat 

// TODO mailist, odchytavani emailu z objednavek

// $PRE_path = $_SERVER['DOCUMENT_ROOT'].'';
$WWW_root = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
$PRE_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'..';
$PRE_path = '';



// *****************************************************************************
// NASTAVENI A ZABEZPECENI
// *****************************************************************************

// skryt/zobrazit vyber jazyk. verze webu
$show_C_lang_selector = true; // false/true






// *****************************************************************************
// cesty - !!! vzdy musi koncit /
// *****************************************************************************
// fotky
define('IMG_P_O' , SERVER_NAME.'/UserFiles/products/original/'); // cesta pro original produktu (musi koncit '/')
define('IMG_P_S' , SERVER_NAME.'/UserFiles/products/small/'); // cesta pro nahled produktu (musi koncit '/')
define('IMG_P_M' , SERVER_NAME.'/UserFiles/products/middle/'); // cesta pro detail produktu (musi koncit '/')

define('IMG_P_S_RELATIV' , $_SERVER['DOCUMENT_ROOT'].'/UserFiles/products/small/');
define('IMG_P_M_RELATIV' , $_SERVER['DOCUMENT_ROOT'].'/UserFiles/products/middle/');
define('IMG_P_O_RELATIV' , $_SERVER['DOCUMENT_ROOT'].'/UserFiles/products/original/');

define('IMG_F_S_RELATIV' , $_SERVER['DOCUMENT_ROOT'].'/UserFiles/fotogalerie/small/');
define('IMG_F_M_RELATIV' , $_SERVER['DOCUMENT_ROOT'].'/UserFiles/fotogalerie/middle/');
define('IMG_F_O_RELATIV' , $_SERVER['DOCUMENT_ROOT'].'/UserFiles/fotogalerie/original/');

// fotky
define('IMG_C_O',"../UserFiles/categories/original/"); // cesta pro original
define('IMG_C_S',"../UserFiles/categories/small/"); // cesta pro nahled - musi koncit /
define('IMG_C_M',"../UserFiles/categories/middle/"); // cesta pro detail - musi koncit /

define('IMG_I_O',"../UserFiles/Icons_parameters/original/"); // cesta pro original
define('IMG_I_S',"../UserFiles/Icons_parameters/small/"); // cesta pro nahled - musi koncit /
// soubory
// if($_SERVER['SERVER_NAME'] == "localhost")
// 	$_SESSION['UserFilesPath'] = "/flava/UserFiles/";// pro ukladani souboru pomoci filemanageru v FCKeditoru
// else if($_SERVER['SERVER_NAME'] == "www.ms-studio.cz")
// 	$_SESSION['UserFilesPath'] = "/test/flava/UserFiles/";// pro ukladani souboru pomoci filemanageru v FCKeditoru
define('FILES_UPL',"../UserFiles/download/"); // cesta pro upload souboru k produktum a strankam
define('FILES_UPL_VIDEO',"../UserFiles/download/video/"); // cesta pro upload souboru k produktum a strankam

// FOTOGALERIE:

define('IMG_F_S',$PRE_path."/UserFiles/fotogalerie/small/"); // cesta pro nahled - musi koncit / - ukladani (absolutni)
define('IMG_F_S2',"UserFiles/fotogalerie/small/"); // cesta pro nahled - musi koncit / - cteni (relativni)

define('IMG_F_M',$PRE_path."/UserFiles/fotogalerie/middle/"); // cesta pro detail - musi koncit / - ukladani (absolutni)
define('IMG_F_M2',"UserFiles/fotogalerie/middle/"); // cesta pro detail - musi koncit / - cteni (relativni)

define('IMG_F_O',$PRE_path."/UserFiles/fotogalerie/original/"); // cesta pro original - ukladani (absolutni)
define('IMG_F_O2',"UserFiles/fotogalerie/original/"); // cesta pro original - cteni (relativni)


define('INZERENTI',"UserFiles/Inzerenti/"); // cesta pro upload banneru - ukladani (absolutni)
define('INZERENTI2',"../UserFiles/Inzerenti/"); // cesta pro cteni banneru - cteni (relativni)

// *****************************************************************************
// nastaveni jazykove verze obsahu - je zobrazen a spravovan obsah pouze te 
// jazykove verze ktera je nastavena - VYHRADNE PRO POTREBY APLIKACE (ADMINISTRACE)
// *****************************************************************************
// nebudeme ukladat do SESSION, muze nastat situace ze pri otevreni 
// nekolika oken dojde k nechtenemu prepnuti verze obsahu a admin 
// si toho nemusi vzdy vsimnout, takze verzi upravovaneho obsahu budeme 
// predavat GETem

define('C_LOGIN',"http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?C_lang=" . C_LANG);


// panel pro vyber jazyk. verze administrace
if($show_C_lang_selector === true) {	
	$C_lang_selector = "
	<script language=\"JavaScript\" type=\"text/javascript\">
	<!--
	function go() {
	
		if (document.selecter.select1.options[document.selecter.select1.selectedIndex].value != \"none\") {
			location = document.selecter.select1.options[document.selecter.select1.selectedIndex].value
		}
	
	}
	//-->
	</script>
	
	
	<form name=\"selecter\" id=\"versions\">
	
	<b>verze obsahu</b><br />
	
	<select name=\"select1\" size=\"1\" onchange=\"go()\" style=\"font-size: 10px; width: 230px;\">";
	
	switch(C_LANG){
	  case 1: $select1='selected="selected"';break;
    default: $select1='selected="selected"';
  }
    $C_lang_selector .= "<option value=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?C_lang=1\" $select1>ČESKY</option>
	</select>
	</form>";
 
}
// *****************************************************************************
// nastaveni jazykove verze obsahu - VYHRADNE PRO POTREBY APLIKACE (ADMINISTRACE)
// *****************************************************************************






// slovnik aplikace
if(empty($_SESSION['S_lang']))$_SESSION['S_lang']="cz";
if(!empty($_GET['app']))$slovnik = $_GET['app']."/lang/".$_SESSION['S_lang'].".php";
if(!empty($slovnik))include_once $slovnik;





// *****************************************************************************
// adresare,nazvy aplikaci, aplikace
// $apps['jazyk_administrace']['adresar_aplikace'] = "nazev_aplikace";
// *****************************************************************************
$apps['cz']['shop'] = "Katalog";
$apps['en']['shop'] = "Catalog";
// $apps['cz']['psys'] = "Www obsah";
// $apps['en']['psys'] = "Www management";
// $apps['cz']['counter'] = "Statistiky přístupů";
// $apps['en']['counter'] = "Statistics";
// $apps['cz']['mail'] = "E-mail centrum";
// $apps['en']['mail'] = "E-mail centrum";
// $apps['cz']['admins'] = "Administrátoři";
// $apps['en']['admins'] = "Administrators";

$apps = $apps[$_SESSION['S_lang']];

// kod pro pro zobrazeni samostatnych aplikaci (nejsou nacitany do rozhrani 
// administrace). Tento kod je generovan nahodne, ulozen do session a predan 
// GETem, je jiny pro kazdou session a spolecne se session_id() tvori autorizaci. 
// Tzn. ze aplikace je pristupna pouze z administrace.
// *****************************************************************************
// adresare,nazvy aplikaci, aplikace
// *****************************************************************************







if(!empty($_GET['app']) && is_dir($_GET['app'])) {

	define('APP', $_GET['app']); // adresar s aktualne natazenou aplikaci
	define('MAIN_LINK', C_LOGIN."&app=".APP); // preddefinovana cast odkazu

}else{
  define('APP', '');
}

// *****************************************************************************
// NASTAVENI A ZABEZPECENI
// *****************************************************************************















// *****************************************************************************
// zkraceni retezce
// *****************************************************************************
function lenght_of_string($max,$in) {

	// orizne retezec za mezerou pred max. pocet znaku
	
	if (strlen($in) > $max) {
	 
		$in = substr($in,0,$max); // orizeneme na max pocet
		$pos = strrpos($in," "); // najdeme posledni mezeru ve zbytku textu
		$in = substr($in,0,$pos)." ..."; // odrizneme k posledni mezere
	
	}
	
	return $in;

}
// *****************************************************************************
// zkraceni retezce
// *****************************************************************************














// *****************************************************************************
// pole s kategoriemi obchodu
// *****************************************************************************
function categories_array($parent_id,$cat_array,$level) {

	// vygenerujeme pole s udaji kategorii
	global $cat_array;
	
	// id name hidden descr lang products id_parent position
	$query = "SELECT code, id, name, hidden, lang, position 
	FROM ".T_CATEGORIES." WHERE ".SQL_C_LANG." 
	AND id_parent = $parent_id 
	ORDER BY id_parent, position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$cat_array[] = $level."|".$z['position']."|".$parent_id."|".$z['name']."|".$z['hidden']."|".$z['lang']."|".$z['id']."|".$z['code'];
		
		categories_array($z['id'],$cat_array,$level+1);
	
	}

}
// *****************************************************************************
// pole s kategoriemi obchodu
// *****************************************************************************

// *****************************************************************************
// pole s kategoriemi obchodu
// *****************************************************************************
function akce_array($parent_id,$akce_array,$level) {

	// vygenerujeme pole s udaji kategorii
	global $akce_array;
	
	// id name hidden descr lang products id_parent position
	$query = "SELECT id, name, hidden, lang, position 
	FROM ".T_AKCE." WHERE ".SQL_C_LANG." 
	AND id_parent = $parent_id 
	ORDER BY id_parent, position";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$akce_array[] = $level."|".$z['position']."|".$parent_id."|".$z['name']."|".$z['hidden']."|".$z['lang']."|".$z['id'];
		
		akce_array($z['id'],$akce_array,$level+1);
	
	}

}
// *****************************************************************************
// pole s kategoriemi obchodu
// *****************************************************************************








// *****************************************************************************
// pruchod kategoriemi smerem do hloubky
// *****************************************************************************
function children_in_category($id,$ch_cat) {

	// vyuziti napr. k zamezeni zarazeni kategorie shopu sama do 
	// sebe, sama sebe do sobe podrizene kategorie atd ...
	// projde kategorie od zadaneho ID dolu, vyhleda vsechny 
	// podrizene urovne a ulozi do pole pro dalsi zpracovani
	// v poradi od hornich urovni smerem dolu
	global $ch_cat;
	
	$ch_cat[] = $id;
	
	$query = "SELECT id FROM ".T_CATEGORIES." 
	WHERE id_parent = $id AND ".SQL_C_LANG." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		children_in_category($z['id'],$ch_cat);
	}

}
// *****************************************************************************
// pruchod kategoriemi smerem do hloubky
// *****************************************************************************


// *****************************************************************************
// pruchod kategoriemi smerem do hloubky
// *****************************************************************************
function children_in_akce($id,$ch_akce) {

	// vyuziti napr. k zamezeni zarazeni kategorie shopu sama do 
	// sebe, sama sebe do sobe podrizene kategorie atd ...
	// projde kategorie od zadaneho ID dolu, vyhleda vsechny 
	// podrizene urovne a ulozi do pole pro dalsi zpracovani
	// v poradi od hornich urovni smerem dolu
	global $ch_akce;
	
	$ch_akce[] = $id;
	
	$query = "SELECT id FROM ".T_AKCE." 
	WHERE id_parent = $id AND ".SQL_C_LANG." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		children_in_akce($z['id'],$ch_akce);
	}

}
// *****************************************************************************
// pruchod kategoriemi smerem do hloubky
// *****************************************************************************





// *****************************************************************************
// roleta vyrobcu
// *****************************************************************************
function producers_select($current_id,$dct) {

	// vygeneruje select s vyrobci, oznaci jako vybranou polozku podle $current_id
	// $dct = slovnik
	
	
	$query = "SELECT id, name FROM ".T_PRODS." WHERE ".SQL_C_LANG." ORDER BY name ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$res='';
	
	while ($z = mysql_fetch_array($v)) {
		
		$id = $z['id'];
		$name = $z['name'];
		
		if ($current_id == $id) $selected = "selected";
		else if ($current_id == 0) $selected = "";
		else $selected = "";
		
		
		$res .= "
		<option value=\"$id\" $selected>$name</option>";
	
	}
	
	
	
	
	$select = "
		<select name=\"id_vyrobce\" class=\"f10\" style=\"width: 100%;\">
			<option value=\"0\">neuveden</option>
			$res
		</select>";
	
	
	return $select;

}
// *****************************************************************************
// roleta vyrobcu
// *****************************************************************************








// *****************************************************************************
// roleta kategorii
// *****************************************************************************
function categories_select($cat_array,$current_id_parent,$dct) {

	// vygeneruje select s kategoriemi, oznaci jako vybranou 
	// polozku podle $current_id_parent
	// $cat_array - pole s udaji o kategoriich
	// $current_id_parent - nadrazena kategorie editovane kategorie
	// $dct = slovnik
	
	if(!empty($cat_array)) {
	
	$res='';
		reset ($cat_array);
		while ($p = each($cat_array)) {
		
			list($level,$position,$par_id,$name,$hidden,$lang,$id) = explode ("|", $p['value']);
			
			
			// odsazeni podle levelu
			$indent = "";//&gt; 
			for ($i = 0; $i < $level; $i++) {
				$indent .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";//__
			}
			
			// if ($level > 0) $indent = $indent;
			// else $indent = "";
			// odsazeni podle levelu
			
			
			if ($current_id_parent == $id) $selected = "selected";
			else if ($current_id_parent == 0) $selected = "";
			else $selected = "";
			
			$res .= "
			<option value=\"$id\" $selected>".$indent."".$name."</option>";
		
		}
	
	}
	
	
	$select = "
		<select name=\"id_parent\" class=\"f10\" style=\"width: 100%;\">
			<option value=\"0\">".$dct['cat_nejvyssi_uroven']."</option>
			$res
		</select>";
	
	
	return $select;

}
// *****************************************************************************
// roleta kategorii
// *****************************************************************************








// *****************************************************************************
// checkboxy kategorii
// *****************************************************************************
function categories_checkbox($cat_array,$par_array,$dct,$parrents=array(),$id_good) {
global $id_nezmenit;
if (count($id_nezmenit)==0){
  $id_nezmenit = array();
}
  $checked=$readonly=$checkbox='';
  
	// $par_array = pole s kategoriemi do kterych produkt nalezi
	// $cat_array - pole s udaji o kategoriich
	// $dct = slovnik
	
	$cur_level=0;
	$js='';
	
	
	if(!empty($cat_array)) {
	
		reset ($cat_array);
		while ($p = each($cat_array)) {
		
			list($level,$position,$par_id,$name,$hidden,$lang,$id) = explode ("|", $p['value']);
			
			
			$title_atr = $name;
			

        if (!in_array($id,$id_nezmenit))
        {
         $name = lenght_of_string(40,$name); // max pocet znaku, string
  			}

			
			// odsazeni podle levelu
			$indent = "";//&gt; 
			for ($i = 0; $i < $level; $i++) {
				$indent .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";//__
			}
			
			// if ($level > 0) $indent = $indent;
			// else $indent = "";
			// odsazeni podle levelu
			
			
      if(isset($cat_array[$i+1])){		
			   list ($level2,$position2,$par_id2,$name2,$hidden2,$lang2,$id2) = explode ("|", $cat_array[$i+1]);
			}
			
			/*if($id == $par_id2) $readonly = "disabled";
			else $readonly = "";*/
			
			
			if (!empty($par_array[$id])) {
				$checked = "checked";
				$name = '<strong>'.$name.'</strong>';
			}
			else {
				$checked = "";
				$name = $name;
			}
  
      //CV:zjistíme zda je produkt upřednostněn v kategorii      
      if ($checked) {
      $query = "SELECT uprednostnit FROM ".T_GOODS_X_CATEGORIES." WHERE id_good=$id_good and id_cat=$id";
   		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		  $z = mysql_fetch_assoc($v);
        if ($z["uprednostnit"]==1) $checkedu="checked";
      }
      else $checkedu=""; 
			
			//jen zvyraznit rodice az do rootu
			if (!empty($parrents[$id])) {
				$name = '<strong>'.$name.'</strong>';
        //pouze kategorie kde výrobek patří můžem upřednostnit  
			}
			
			
			
			// vyrobek muzeme zaradit jen do jedne kategorie
// 			if(C_LANG == 3 OR C_LANG == 4) {
// 			
//   			$checkbox .= "$indent<input type=\"radio\" name=\"id_parent\" 
//   			value=\"$id\" title=\"$title_atr\" $checked $readonly/> <span title=\"$title_atr\">$name</span><br />";
// 			
// 			} else {
// 			
//   			if($level > 0 OR in_array($id,$id_nezmenit)) $checkbox .= "$indent<input type=\"radio\" name=\"id_parent\" 
//   			value=\"$id\" title=\"$title_atr\" $checked $readonly/> <span title=\"$title_atr\">$name</span><br />";
//   			else $checkbox .= "<br />$indent $name<br />";
// 			
// 			}
               $onclick="";

			if($level>$cur_level){
			$checkbox .="<div id='m$par_id'>";
			$js.="document.getElementById('m$par_id').style.display='none';
			";
			}
			
			while($level<$cur_level){
				$checkbox .="</div>";
				$cur_level--;
			}
							
			// vyrobek muzeme zarazovat do vice kategorii soucasne
      //CV:pokud jsme již u kategorie, zobrazíme checkbox i pro upřednostnění
      if ($id_good && $checked) {
  			$checkbox .= "$indent<input type=\"checkbox\" name=\"id_parent[]\" 
  			value=\"$id\" title=\"$title_atr\" $checked $readonly/>";
  			$checkbox .= "<a title=\"$title_atr\" onclick=\"s('m$id'); return false;\">  $name </a><br/>$indent&nbsp;&nbsp;<input type=\"checkbox\" name=\"id_upr[]\" 
  			value=\"$id\" title=\"$title_atru\" $checkedu $readonlyu/><b> Upřednostnit v této kategorii</b><br/>";
      }
      else {
      //checkbox bez upřednostnění
			$checkbox .= "$indent<input type=\"checkbox\" name=\"id_parent[]\" 
			value=\"$id\" title=\"$title_atr\" $checked $readonly/><a title=\"$title_atr\" onclick=\"s('m$id'); return false;\">  $name </a><br/>";
      }
      
			
			
               $cur_level=$level;
		
		}
	
	}
	if(!empty($js)){
		$javascript="<script type=\"text/javascript\">
		<!--
	   	".$js."
		-->
		</script>";
	}
// 	echo $cur_level;exit;
  $adddiv="";
  for($i=0;$i<$cur_level;$i++){
    $adddiv.="</div>";
  }
  
	
	// return "<span class=\"f9\">$checkbox</span>";
	return "".$checkbox.$adddiv.$javascript;

}
// *****************************************************************************
// checkboxy kategorii
// *****************************************************************************








// *****************************************************************************
// generovani stromu v menu
// *****************************************************************************
function odsazeni($level_tree) {
  $ods='';
	// ve zdrojaku zajisti prehlednejsi formatovani, lze vypustit
	for ($i = 0; $i <= $level_tree; $i++) {
		$ods .=  "	";
	}
	return $ods;

}






function get_menu($level_tree,$array_menu,$m_poradi,$mn,$np) {

	// generujeme menu - strom polozek na zaklade _menu.php z adresare kazde aplikace
	// $level_tree = uroven vnoreni polozky menu
	// $array_menu = pole menu - vnorene polozky
	// $m_poradi = poraadi polozek pro js
	// $np = startovaci pozice, od ktere jdeme smerem do hloubky stromu
	
	if(empty($_SESSION['menu_tree']))$_SESSION['menu_tree']='';
	
	global $m_poradi;//, $meu
	
	reset ($array_menu);
	while ($p = each($array_menu)) {
	
		$key = $p['key'];
		$val = $p['value'];
		
		$m_poradi++;
		if($m_poradi == 1) $np++;
		
		$level_tmp = $level_tree + 1;
		
		
		unset($ods);
		$ods = odsazeni($level_tree);
		
		
		// d.add(3,2,'Vyhledávání','http://...','','','img','img');
		// $meu .= "$ods $m_poradi-$np $key<br />";// - $level_tree - $val
		$_SESSION['menu_tree'] .= "\t".$ods."d.add($m_poradi,$np,'$key',$val);\n";
		
		
		
		// vnorena uroven
		if(!empty($mn[$key])) {
		
			$np_tmp = $m_poradi;
			get_menu($level_tmp,$mn[$key],$m_poradi,$mn,$np_tmp);
		
		}
	
	}

}
// *****************************************************************************
// generovani stromu v menu
// *****************************************************************************









// *****************************************************************************
// generujeme tag obrazku
// *****************************************************************************
function imgTag($img,$width,$height,$border,$title,$next_params,$timestamp) {

	// vygeneruje tag obrazku se zakladnimi parametry, vsechny ostatni 
	// je mozno v pripade potreby umistit do $next_params
	 // echo $img;
  if(file_exists($img)) {
	
		@$rozmery = getimagesize($img);
		
		if(empty($w)) $width = $rozmery[0];
		if(empty($h)) $height = $rozmery[1];
		
		if(!($border > 0)) $border = "0";
		
		// slouzi pro pripadne zajisteni refreshe obrazku, v nekterych situacich 
		// nejsou obrazky natazeny vzdy korektne
		
		// generujeme "unikatni" timestamp pri kazdem pouziti fce
		if($timestamp == -1) $timestamp = $timestamp = time().microtime();
		
		if(!empty($timestamp)) $timestamp = "?t=$timestamp";
		
		return "
		<img src=\"$img".$timestamp."\" width=\"$width\" height=\"$height\" 
		title=\"$title\" border=\"$border\" $next_params>";
	
	}

}
// *****************************************************************************
// generujeme tag obrazku
// *****************************************************************************





// *****************************************************************************
// nahledy obrazku, s odkazem na velky obrazek (pokud existuje)
// *****************************************************************************
function showimg($img1,$img2,$width,$height,$border,$title,$next_params,$timestamp) {

	// generujeme tag pro nahled obrazku, pokud k nemu existuje velky obrazek, 
	// vygeneruje se take odkaz pro otevreni velkeho obrazku do noveho okna
	
	
	if(file_exists($img2)) {
	
		@$rozmery2 = getimagesize("".$img2);
		
		
		
		$next_params = " style=\"cursor: pointer;\"";
		$next_params .= " onclick=\"window.open('show.php?i=$img2','','resizable=0,scrollbars=0,top=0,left=0,menubar=0,width=".$rozmery2[0].",height=".$rozmery2[1]."');\"";
		
		$title = "".$rozmery2[0]."x".$rozmery2[1]." px, ".file_size($img2);
	
	}
	
	
	return imgTag($img1,$width,$height,$border,$title,$next_params,$timestamp);

}
// *****************************************************************************
// nahledy obrazku, s odkazem na velky obrazek (pokud existuje)
// *****************************************************************************









// *****************************************************************************
// velikost souboru - prevod jednotek
// *****************************************************************************
function file_size($file) {

	$kb = 1024;         // Kilobyte
	$mb = 1048576;      // Megabyte
	$gb = 1073741824;   // Gigabyte
	$tb = 1099511627776;// Terabyte
	
	@$size = filesize($file);
	
	if($size < $kb) $size = $size." B";
	else if($size < $mb) $size = round($size/$kb,1)." kB";
	else if($size < $gb) $size = round($size/$mb,1)." MB";
	else if($size < $tb) $size = round($size/$gb,1)." GB";
	else $size = round($size/$tb,2)." TB";
	
	return $size;
}
// *****************************************************************************
// velikost souboru - prevod jednotek
// *****************************************************************************







// *****************************************************************************
// upload a vytvareni kopii obrazku
// *****************************************************************************
function img_resize($orig,$kopie,$width_max,$height_max,$kompr,$sledovany_rozm) {

	// ze vstupniho souboru $orig ulozi kopii $kopie (zmensenou) tak, 
	// aby rozmery kopie nepresahly max. povolene rozmery $width_max a $height_max
	
	// hodnota $sledovany_rozm urci ktery z rozmeru je sledovany a je rozhodujici 
	// pro vypocet rozmeru kopie
	// w = sirka
	// h = vyska
	// b = oba - rozmery kopie nepresahnou $width_max ANI $height_max
	
	// lze nastavit kompresi u jpg - pri prazdne hodnote je kolem 75, coz u muze 
	// byt u vetsich rozmeru poznat - rozmazane
	
	// pracuje s jpg a png formaty
	
	if(empty($kompr)) $kompr = 75;
	
	if(empty($sledovany_rozm)) $sledovany_rozm = "b";
	
	
	// pokud soubor existuje odstranime jej
	@unlink($kopie);
	
	
	
	// zjistime rozmery originalu a typ souboru
	$rozm = getimagesize($orig);
	
	// 2 = JPG, 3 = PNG
	if($rozm[2] == 2) $in = imagecreatefromjpeg($orig); // jpg
	if($rozm[2] == 3) $in = imagecreatefrompng($orig); // png
	
	
	// echo $rozm[3] . "<br /><br />";
	
	// kontroluje max. sirku
	if($sledovany_rozm == "w") {
	
		if($width_max < $rozm[0]) $k = $width_max/$rozm[0];
		if($width_max >= $rozm[0]) $k = 1;
		
		$width = $rozm[0] * $k;
		$height = $rozm[1] * $k;
	
	}
	
	// kontroluje max. vysku
	if($sledovany_rozm == "h") {
	
		if($height_max < $rozm[1]) $k = $height_max/$rozm[1];
		if($height_max >= $rozm[1]) $k = 1;
		
		$width = $rozm[0] * $k;
		$height = $rozm[1] * $k;
	
	}
	
	
	
	
// 	echo $rozm[0].": $sledovany_rozm: $width_max: $width x $height";exit;
	
	
	
	
	// kontroluje max. sirku i vysku, neni prekrocen zadny z techto rozmeru
	if($sledovany_rozm == "b") {
	
		if($width_max > $height_max) {
		
			// max. sirka > max. vyska
			$k_width = $width_max / $height_max;
			$k_height = 1;
			// echo __LINE__ . " ** $k_width<br />";
		
		} elseif($height_max > $width_max) {
		
			// max. vyska > max. sirka
			$k_width = 1;
			$k_height = $height_max / $width_max;
			// echo __LINE__ . "<br />";
		
		} else {
		
			$k_width = 1;
			$k_height = 1;
			// echo __LINE__ . "<br />";
		}
		
		
		if($rozm[0] < $width_max && $rozm[1] < $height_max) {
		
			// sirka a vyska orig. jsou mensi nez max. hodnoty - nechame puvodni rozmery
			$width = $rozm[0];
			$height = $rozm[1];
			// echo __LINE__ . "<br />";
		
		} elseif($rozm[0] / $k_width > $rozm[1] / $k_height) {
		
			// šířka orig. je větší než max. výška
			$width = $width_max;
			$k = $rozm[0] / $width_max;
			$height = ceil($rozm[1] / $k);
			// echo __LINE__ . "<br />";
		
		} elseif($rozm[0] / $k_width < $rozm[1] / $k_height) { 
		
			// pokud je výška větší než max. šířka
			$height = $height_max;
			$k = $rozm[1] / $height_max;
			$width = ceil($rozm[0] / $k);
			// echo __LINE__ . "<br />";
		
		} else {
		
			if($width_max > $height_max) {
			
				$width = $height_max * $k_width;
				$height = $height_max * $k_height;
				// echo __LINE__ . "<br />";
			
			} else {
			
				$width = $width_max * $k_width;
				$height = $width_max * $k_height;
				// echo __LINE__ . "<br />";
			
			}
		
		}
	
	}
	
	// echo "<br /><br />$width / $height<br /><br />";
	
	$out = imagecreatetruecolor($width,$height);
	
	
	imagecopyresampled($out,$in,0,0,0,0,$width,$height,$rozm[0],$rozm[1]);
	
	// 2 = JPG, 3 = PNG
	if($rozm[2] == 2) imagejpeg($out,$kopie,$kompr); // jpg
	if($rozm[2] == 3) imagepng($out,$kopie); // png
	
	imagedestroy($in);
	imagedestroy($out);
	
	// exit;

}


function img_cat_upload($nm){ // pro vkladani obrazku kategorie

	// uploadovany obrazek (original) je umisten do adresare $dir_orig
	// jsou vytvoreny kopie o max. povolenych rozmerech
	// potrebujeme-li zmenit nazev (napr. podle ID produktu, time() ...), uvedeme 
	// jej do promenne $nm, jinak zustane stejny
	
	
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	$original_dir = IMG_C_O; // cesta pro original (zaloha?)
	
	$kopie_dir[] = IMG_C_S; // cesta pro nahled
	$kopie_dir[] = IMG_C_M; // cesta pro detailni obrazek
	
	$w_max[] = 150; // max. sirka nahledu
	$w_max[] = 240; // max. sirka detailu
	
	$h_max[] = 140; // max. vyska nahledu
	$h_max[] = 300; // max. vyska detailu
	
	$komprese[] = ""; // komprese nahledu
	$komprese[] = ""; // komprese detailu
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	
	
	
	
	// zjistime rozmery originalu a typ souboru
	$r = getimagesize($_FILES['foto']['tmp_name']);
	
	$w_orig = $r[0]; // sirka originalu
	$h_orig = $r[1]; // sirka originalu
	$typ = $r[2]; // typ souboru

	
	
	// kontrola typu souboru - povolime jen jpg, png
	if($typ != 2 && $typ != 3) {
	
		$_SESSION['alert_js'] = "Nesprávný formát obrázku. ";
		$_SESSION['alert_js'] .= "Použijte formát JPG nebo PNG.\\n\\n";
		$_SESSION['alert_js'] .= "Nic nebylo uloženo.";
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	} else { // zjistime priponu
	
		$x1 = explode (".", $_FILES['foto']['name']); // roztrhame nazev souboru - delicem je tecka
		$x2 = count($x1) - 1; // index posledniho prvku pole
		$e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
	
	}
	
	
	if(!empty($nm)) $file_name = $nm.".".$e; // menime nazev obrazku
	else $file_name = $_FILES['foto']['name']; // nazev obrazku zustava stejny
	
	
	$original = $original_dir.$file_name;
	
	
	
	// umistime original souboru
	move_uploaded_file($_FILES['foto']['tmp_name'],$original);
// 	copy($_FILES['foto']['tmp_name'],$original);
	
	
	
	for($x = 0; $x < count($kopie_dir); $x++) {
	
		// kontrola rozmeru:
		// b = oba, w = sirka, h = vyska
		if($x == 0) $sledovany_rozm = "b"; // nahled
		else $sledovany_rozm = "w"; // detail
		
		img_resize($original,$kopie_dir[$x].$file_name,$w_max[$x],$h_max[$x],$komprese[$x],$sledovany_rozm);
	
	}
	
	return $e;


}



function img_upload($nm) { // pro vkladani obrazku vyrobku

	// uploadovany obrazek (original) je umisten do adresare $dir_orig
	// jsou vytvoreny kopie o max. povolenych rozmerech
	// potrebujeme-li zmenit nazev (napr. podle ID produktu, time() ...), uvedeme 
	// jej do promenne $nm, jinak zustane stejny
	
	
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	$original_dir = IMG_P_O; // cesta pro original (zaloha?)
	
	$kopie_dir[] = IMG_P_S; // cesta pro nahled
	$kopie_dir[] = IMG_P_M; // cesta pro detailni obrazek
	
	$w_max[] = 150; // max. sirka nahledu
	$w_max[] = 240; // max. sirka detailu
	
	$h_max[] = 140; // max. vyska nahledu
	$h_max[] = 300; // max. vyska detailu
	
	$komprese[] = ""; // komprese nahledu
	$komprese[] = ""; // komprese detailu
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	
	
	
	
	// zjistime rozmery originalu a typ souboru
	$r = getimagesize($_FILES['foto']['tmp_name']);
	
	$w_orig = $r[0]; // sirka originalu
	$h_orig = $r[1]; // sirka originalu
	$typ = $r[2]; // typ souboru

	
	
	// kontrola typu souboru - povolime jen jpg, png
	if($typ != 2 && $typ != 3) {
	
		$_SESSION['alert_js'] = "Nesprávný formát obrázku. ";
		$_SESSION['alert_js'] .= "Použijte formát JPG nebo PNG.\\n\\n";
		$_SESSION['alert_js'] .= "Nic nebylo uloženo.";
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	} else { // zjistime priponu
	
		$x1 = explode (".", $_FILES['foto']['name']); // roztrhame nazev souboru - delicem je tecka
		$x2 = count($x1) - 1; // index posledniho prvku pole
		$e = strtolower($x1[$x2]); // mame priponu (vkladame take do DB, proto return)
	
	}
	
	
	if(!empty($nm)) $file_name = $nm.".".$e; // menime nazev obrazku
	else $file_name = $_FILES['foto']['name']; // nazev obrazku zustava stejny
	
	
	$original = $original_dir.$file_name;
	
	
	
	// umistime original souboru
	move_uploaded_file($_FILES['foto']['tmp_name'],$original);
// 	copy($_FILES['foto']['tmp_name'],$original);
	
	
	
	for($x = 0; $x < count($kopie_dir); $x++) {
	
		// kontrola rozmeru:
		// b = oba, w = sirka, h = vyska
		if($x == 0) $sledovany_rozm = "b"; // nahled
		else $sledovany_rozm = "w"; // detail
		
		img_resize($original,$kopie_dir[$x].$file_name,$w_max[$x],$h_max[$x],$komprese[$x],$sledovany_rozm);
	
	}
	
	return $e;

}

function img_upload_new($soubor , $nm = '')
{
  // Povinné parametry.
  if(empty($soubor)) return -1;

  // pro vkladani obrazku vyrobku
	// uploadovany obrazek (original) je umisten do adresare $dir_orig
	// jsou vytvoreny kopie o max. povolenych rozmerech
	// potrebujeme-li zmenit nazev (napr. podle ID produktu, time() ...), uvedeme
	// jej do promenne $nm, jinak zustane stejny

	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	$original_dir = IMG_P_O_RELATIV; // cesta pro original (zaloha?)
	$kopie_dir[] = IMG_P_S_RELATIV; // cesta pro nahled
	$kopie_dir[] = IMG_P_M_RELATIV; // cesta pro detailni obrazek

	$w_max[] = 150; // max. sirka nahledu
	$w_max[] = 240; // max. sirka detailu
	
	$h_max[] = 140; // max. vyska nahledu
	$h_max[] = 300; // max. vyska detailu

	$komprese[] = ""; // komprese nahledu
	$komprese[] = ""; // komprese detailu
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************

	// zjistime rozmery originalu a typ souboru
	if(!is_array($soubor))
	{ // soubor je predavan prez URL
    $soubor = trim($soubor);
    $r = getimagesize($soubor);
  }
  else
  { // soubor je predavan prez formular
    $r = getimagesize($soubor['tmp_name']);
  }

	$w_orig = $r[0]; // sirka originalu
	$h_orig = $r[1]; // sirka originalu
	$typ = $r[2]; // typ souboru

	// kontrola typu souboru - povolime jen jpg, png
	if($typ != 2 && $typ != 3)
  {
    return -1;
	}
  else
  { // zjistime priponu
    if(!is_array($soubor))
    {
      $x1 = explode ('.' , $soubor); // roztrhame nazev souboru - delicem je tecka
    }
    else
    {
		  $x1 = explode ('.' , $soubor['name']); // roztrhame nazev souboru - delicem je tecka
    }

		$x2 = count($x1) - 1; // index posledniho prvku pole
		$e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
	}

  $pripona = '.'.strtolower($e); // pripona souboru (typ)

	if(!empty($nm))
  { // uzivatelelem zadany nazev soubotu
    $file_name = $nm; // menime nazev obrazku
  }
	else
  { // jenou souboru zustane stejne, ale upravime ho pro url
    $jmeno_souboru = '';

    for($index = 0; $index < count($x1) - 1; $index++)
    { // pokud jsou v nazvu tecky nahradime je pomlckami '-'
      if($index != 0)
      {
        $jmeno_souboru .= '-' . $x1[$index];
      }
      else
      { // na prvni pozici tecku nedavame
        $jmeno_souboru .= $x1[$index];
      }
    }

    $jmeno_souboru = explode('/' , $jmeno_souboru);
    $jmeno_souboru = end($jmeno_souboru);

    $jmeno_souboru = text_in_url($jmeno_souboru); // odstranime nezadouci znaky

    $file_name = $jmeno_souboru; // nazev obrazku zustava stejny
  }

	$original = $original_dir.$file_name.$pripona; // cesta k originalnimu souboru

  while(file_exists($original)) // Zkusíme najít první volné jméno.
  { // pokud soubor existuje
    if(!isset($count)) $count = 1;
    //$file_name = $jmeno_souboru.'_('.$count.')'; //zavorky vadí prettyPhoto
    $file_name = $jmeno_souboru.'_'.$count.'_';
    $original = $original_dir.$file_name.$pripona;

    $count++;
  }

	// umistime original souboru
	if(!is_array($soubor))
	{
    if(!copy($soubor , $original))
    {
      return -1;
		}
  }
  else
  {
	  if(!move_uploaded_file($soubor['tmp_name'] , $original))
	  {
      return -1;
    }
  }

  // uprava souboru do pozadovanych velikosti
	for($x = 0; $x < count($kopie_dir); $x++)
  {
		// kontrola rozmeru: b = oba, w = sirka, h = vyska
		$sledovany_rozm = "b";

		img_resize($original , $kopie_dir[$x].$file_name.$pripona , $w_max[$x] , $h_max[$x] , $komprese[$x] , $sledovany_rozm);
	}

	return $file_name.$pripona; // vratime nazev ulozeneho souboru
}






function img_upload2($nm,$key) { // pro vkladani ikon u parametru vyrobku

	// uploadovany obrazek (original) je umisten do adresare $dir_orig
	// jsou vytvoreny kopie o max. povolenych rozmerech
	// potrebujeme-li zmenit nazev (napr. podle ID produktu, time() ...), uvedeme 
	// jej do promenne $nm, jinak zustane stejny
	
	
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	$original_dir = IMG_I_O; // cesta pro original (zaloha?)
	
	$kopie_dir[] = IMG_I_S; // cesta pro nahled
	
	$w_max[] = 30; // max. sirka nahledu
	
	$h_max[] = 30; // max. vyska nahledu
	
	$komprese[] = ""; // komprese nahledu
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	
// 	echo "***".$_FILES['newImg']['tmp_name'][$key]."***<br />";
	
	
	// zjistime rozmery originalu a typ souboru
	$r = getimagesize($_FILES['newImg']['tmp_name'][$key]);
	
	$w_orig = $r[0]; // sirka originalu
	$h_orig = $r[1]; // sirka originalu
	$typ = $r[2]; // typ souboru

	
	
	// kontrola typu souboru - povolime jen jpg, png
	if($typ != 2 && $typ != 3) {
	
		$_SESSION['alert_js'] = "Nesprávný formát obrázku. ";
		$_SESSION['alert_js'] .= "Použijte formát JPG nebo PNG.\\n\\n";
		$_SESSION['alert_js'] .= "Nic nebylo uloženo.";
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	} else { // zjistime priponu
	
		$x1 = explode (".", $_FILES['newImg']['name'][$key]); // roztrhame nazev souboru - delicem je tecka
		$x2 = count($x1) - 1; // index posledniho prvku pole
		$e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
	
	}
	
	
	if(!empty($nm)) $file_name = $nm.".".$e; // menime nazev obrazku
	else $file_name = $_FILES['newImg']['name'][$key]; // nazev obrazku zustava stejny
	
	
	$original = $original_dir.$file_name;
	
	
	
	// umistime original souboru
	// move_uploaded_file($_FILES['img']['tmp_name'][$key],$original);
	copy($_FILES['newImg']['tmp_name'][$key],$original);
	
	
	
	for($x = 0; $x < count($kopie_dir); $x++) {
	
		// kontrola rozmeru:
		// b = oba, w = sirka, h = vyska
		if($x == 0) $sledovany_rozm = "b"; // nahled
		else $sledovany_rozm = "w"; // detail
		
		img_resize($original,$kopie_dir[$x].$file_name,$w_max[$x],$h_max[$x],$komprese[$x],$sledovany_rozm);
	
	}
	
	return $e;

}
// *****************************************************************************
// upload a vytvareni kopii obrazku
// *****************************************************************************







// *****************************************************************************
// prevod znaku na VELKE/male
// *****************************************************************************

// nektere servery neprevedou znaky s diakritikou beznymi strtoupper/strtolower

function strtoL($in) {

	$in = strtolower($in);
	$in = strtr($in, "ĚŠČŘŽÝÁÍÉŤĎŇÚŮ", "ěščřžýáíéťďňúů");
	
	return $in;

}


function strtoU($in) {

	$in = strtoupper($in);
	$in = strtr($in, "ěščřžýáíéťďňúů", "ĚŠČŘŽÝÁÍÉŤĎŇÚŮ");
	
	return $in;

}
// *****************************************************************************
// prevod znaku na VELKE/male
// *****************************************************************************









// *****************************************************************************
// uprava textu pro URL adresu
// *****************************************************************************
function text_in_url($t) {

  $t = strtolower($t);
	$t = str_replace("&quot;", "", $t);
  $t = str_replace(",", "", $t);
  $t = strtr($t, "!#$%&'()*+,-./:;<=>?@[]^`{|}~€?‚?„…†‡?‰‹?‘’“”•?™› Ľ˘¤§¨­°˛ł´·¸", 
								 "----------------------------------------------------------------");

	$t = strtr($t, "áäčďéěëíňóöřšťúůüýžÁÄČĎÉĚËÍŇÓÖŘŠŤÚŮÜÝŽ ", "aacdeeeinoorstuuuyzAACDEEEINOORSTUUUYZ_");
	$t = str_replace("-+", "-", $t);
  $t = str_replace("__", "_", $t);
	return $t;

}
// *****************************************************************************
// uprava textu pro URL adresu
// *****************************************************************************










// *****************************************************************************
// buttony, ovladaci prvky
// *****************************************************************************
// button
function button($bt_type,$bt_value,$bt_next_params) {

	// $bt_type: button, submit, reset
	// $bt_type: text v buttonu
	// $bt_next_params: dalsi parametry
	if(empty($bt_type)) $bt_type = "submit";
	
	$button = "<input type=\"$bt_type\" value=\"$bt_value\" $bt_next_params>";
	
	return $button;

}

if(empty($dct['button_odstranit_zaznam']))$dct['button_odstranit_zaznam']='';
if(empty($dct['button_odstranit_zaznam']))$dct['button_odstranit_zaznam']='';
if(empty($dct['button_ulozit_zaznam']))$dct['button_ulozit_zaznam']='';
if(empty($dct['back']))$dct['back']='';



define('DELETE_BUTTON',button("button",
														$dct['button_odstranit_zaznam'],
														"class=\"butt_red\" onclick=\"return del()\""));

define('MOVE_BUTTON',button("button",
														$dct['button_odstranit_zaznam'],
														"class=\"butt_red\" onclick=\"return del()\""));



// tlacitko ulozeni zmen zaznamu
define('SAVE_BUTTON',button("submit",
													$dct['button_ulozit_zaznam'],
													"class=\"butt_green\""));



// odkaz back
define('BACK_BUTTON',"<div class=\"button_back\"><a href=\"javascript:history.go(-1);\" 
										title=\"".$dct['back']."\">".$dct['back']."</a></div>");


// obarveni radku tabulky pri najeti mysi
define('TABLE_ROW'," bgcolor=\"#FFFFFF\" onmouseout=\"this.style.backgroundColor='#FFFFFF'\" 
	   onmouseover=\"this.style.backgroundColor='#f4f4f4'\"");


// tlacitko odstraneni zaznamu
// img s odkazem na editaci zaznamu
function ico_edit($ei_link,$ei_text) {

	$ico = "<a href=\"$ei_link\" title=\"$ei_text\" class=\"f10\">
	<img src=\"icons/ico_edit.gif\" alt=\"$ei_text\" title=\"$ei_text\" 
	border=\"0\" height=\"15\" width=\"15\"></a>";
	
	return $ico;

}


// img s odkazem na odstraneni zaznamu
function ico_delete($di_link,$di_text,$di_on_action) {

	$ico = "<a href=\"$di_link\" title=\"$di_text\" class=\"f10\">
	<img src=\"icons/ico_delete.gif\" alt=\"$di_text\" title=\"$di_text\" 
	border=\"0\" height=\"15\" width=\"15\" $di_on_action></a>";
	
	return $ico;

}



// panel pro vyhledavani
function search_panel($bt_type,$bt_next_params,$dct) {

	if(isset($_GET['f']) && $_GET['f'] == "inquiries") {
	
		$add_form = "&nbsp;
		<input type=\"checkbox\" value=\"in_ans\" /> ".$dct['inq_hledat_odpovedi']."";
	
	}
	
	
	if(isset($_GET['f']) && $_GET['f'] == "orders") {
	
	  if(empty($_POST['od']))$_POST['od']='';
	  if(empty($_POST['do']))$_POST['do']='';
	
		$add_form = "
		
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		
		od <input type=\"text\" name=\"od\" value=\"".$_POST['od']."\" class=\"f10\" style=\"width: 80px;\" />
		
		&nbsp;
		
		do <input type=\"text\" name=\"do\" value=\"".$_POST['do']."\" class=\"f10\" style=\"width: 80px;\" />";
	
	}else{
    $add_form='';
  }
	
	if(isset($_POST['search']))$hledano=trim($_POST['search']);
	else $hledano='';
	
	$form = "
<form action=\"\" method=\"post\">

	<input type=\"text\" name=\"search\" value=\"".$hledano."\" class=\"f10\" style=\"width: 200px;\" />
	
	".button($bt_type,$bt_value=$dct['button_najit_zaznam'],$bt_next_params)."
	
	$add_form

</form>";
	
	return $form;

}

if(isset($_GET['f']) && ($_GET['f'] == "products" || $_GET['f'] == "orders")) {

	define('SEARCH_PANEL',
					search_panel($bt_type="submit",
					$bt_next_params="class=\"butt_green\" style=\"margin-bottom: 1px;\"",
					$dct));

}
else define('SEARCH_PANEL',"");
// *****************************************************************************
// buttony, ovladaci prvky
// *****************************************************************************








// *****************************************************************************
// nacteni obsahu souboru
// *****************************************************************************
// set $trans if you want replace strings from file 
// (example: $trans = array ("#alt2" => $alt2, "XXX" => "YYY");) next in function body
function read_from_file($file, $trans) {
	
	$fp = fopen ($file, "r");
	$c = fread($fp, filesize($file));
	fclose($fp);
	
	if (!empty($trans))
		$c = strtr($c, $trans);
	
	return $c;

}
// *****************************************************************************
// nacteni obsahu souboru
// *****************************************************************************


function inzerenti_array($parent_id,$inzerenti_array,$level) 
{

	// vygenerujeme pole s udaji kategorii
	global $inzerenti_array;
	
	// id name hidden descr lang products id_parent position
	$query = "SELECT id, nazev, hidden, lang, poradi 
	FROM ".T_INZERENTI." WHERE ".SQL_C_LANG." 
	ORDER BY poradi";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) 
  {
		$inzerenti_array[] = $level."|".$z['poradi']."|".$parent_id."|".$z['nazev']."|".$z['hidden']."|".$z['lang']."|".$z['id'];
		akce_array($z['id'],$inzerenti_array,$level+1);
	}

}


function zarazeniProduktu($id){
  $zarazeno='';
  $query="SELECT * FROM  ".T_GOODS_X_CATEGORIES." gc 
          INNER JOIN ".T_CATEGORIES." c on gc.id_cat=c.id
          WHERE gc.id_good=$id and c.lang=".C_LANG;
          
  $v = my_DB_QUERY($query,__LINE__,__FILE__);
//   echo $query;
  $returned=array();
  
  while($row = mysql_fetch_array($v)){
      $pole=getParents($row['id_parent']);
      if(is_array($pole)){
	      foreach($pole as $key=>$value){   //key = id_kategorie
	         $returned[$key]=$value;
	      }
      }
  }   
   
  return $returned;
}


function getParents($id_parent,$drobecky=array()){
	
  $query = "SELECT id, id_parent, name FROM ".T_CATEGORIES." 
	WHERE id = $id_parent AND ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		$drobecky[$z['id']] = $z['id'];
		
    if($z['id_parent']>0){
      return getParents($z['id_parent'],$drobecky);
    }else{
      return $drobecky;
    }
	}
}


function strankovani($celkem,$naStrane,$fromPage){
  $stranky="";
//   echo $celkem."<br />";
//   echo $naStrane."<br />";
//   echo ceil($celkem/$naStrane)+1;
//   exit;
//   for($i=1;$i<ceil($celkem/$naStrane)+1;$i++){
//     if(empty($_GET['page']))$_GET['page']=1;
//     if($i==$_GET['page']){
//       $style=" style='font-weight:bold; text-decoration: none;'";
//     }else $style=" style='text-decoration: none;'";
//      
//     $stranky.= "<a href='$fromPage&page=$i' $style>".$i."</a> | ";
//     if($i%20==0){
//       $stranky=substr($stranky,0,-2);
//       $stranky.= "<br />";
//     }
//     
//   }	  
                                
  $celkemStran=ceil($celkem/$naStrane);

  if(empty($_GET['page']))$page=1;
  else $page=$_GET['page'];
  
  if($page<4){
    $frompage=1;
    if($celkemStran>$page+4){
      $topage=$page+4; 
    }else{
      $topage=$celkemStran+1;
    }    
  }elseif($page>$celkemStran-3){
    if($page-3>0){
      $frompage=$page-3;
    }else{
      $frompage=$page;
    }
    $topage=$celkemStran+1;  
  }else{
    $frompage=$page-3;
    $topage=$page+4;
  }
  
  for($i=$frompage;$i<$topage;$i++){
    if($i==$page){
      $style=" style='font-weight:bold; text-decoration: none;'";
    }else $style=" style='text-decoration: none;'";
    $stranky.= "<a href='$fromPage&page=$i' $style>".$i."</a> | ";
  }
  
  $selectbox='';
  for($i=1;$i<$celkemStran+1;$i++){
    if($i==$page){
      $selected=" selected='selected'";
    }else $selected="";
    
    $selectbox.= "<option value='$fromPage&page=$i' $selected>strana ".$i."</option>";
  }  
  
  $selectbox='
  <select onchange="window.location.href=this.value;" style="width: 100px; margin-top:10px;">
    '.$selectbox.'
  </select><br />
  ';

  return substr($stranky,0,-2)."<br />".$selectbox;
}

function notHaveChildren($category){
  $query='select id from '.T_CATEGORIES.' where id_parent='.$category;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	if(mysql_num_rows($v)>0)return false;
	else return true;
}	

?>
