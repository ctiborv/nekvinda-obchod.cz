<?php
include_once 'admin/_mysql.php';
include_once '_functions.php';




// *****************************************************************************
// NASTAVENI
// *****************************************************************************
define('DEFAULT_ORDER','akce desc, poradi, cena');      			//nazev sloupce v db, dle ktereho se bude defaultne radit sortiment
define('REFERER_COOKIE_NAME','refererNEKV');		//index cookie, která se tvoří jako referer
define('ZALOZKY',false);                          //nulté kategorie jako založky true/false
define('ZALOZKY_VYROBKY',true);                   //zobrazovat nebo nezobrazovat vyrobky v hlavnich kategoriich -> zajima nas hlavne kdyz pouzivame zalozky
define('SDPH',true);                   			//jako hlavní jsou ceny s DPH true/false
define('TRIDENI_VYROBCE',1);					// 1 - checkbox, 2 - selectbox      
define('ZOBRAZ_VYHLEDAVANI',29);                  // id kategorie ve ktere zobrazime vyhledavani podle produktovych listu





// *****************************************************************************
//  schvaleni prispevku
// *****************************************************************************
if(isset($_GET['id'],$_GET['go'],$_GET['hash']) && $_GET['go']=='schvaleniprispevku'){
		
	$query='update '.T_COMMENTS." set hidden=0 where hash='".$_GET['hash']."' and id=".$_GET['id'];
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$query="select ".T_GOODS.".id,".T_GOODS.".name from ".T_GOODS.",".T_COMMENTS." where ".T_COMMENTS.".hash='".$_GET['hash']."' and ".T_COMMENTS.".id=".$_GET['id']." and ".T_GOODS.".id=".T_COMMENTS.".id_produkt";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$produkt = mysql_fetch_array($v);	
	
	$_SESSION['alert_js1']='Komentář k výrobku byl schválen.';
	
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/produkt/'.$produkt['id'].'-'.text_in_url($produkt['name']).'/');
	exit;	

}






// *****************************************************************************
//  schvaleni prispevku
// *****************************************************************************
if(!empty($_POST['addPorovnat'])){
	$_SESSION['porovnaniVyrobku'][$_POST['addPorovnat']]=$_POST['addPorovnat'];
	
	$_SESSION['alert_js1']='Výrobek přidán k porovnání.';
	
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit;	
}

if(!empty($_SESSION['porovnaniVyrobku']))
{
  $POROVNANI = '<div id="compare"><a href="/porovnani/" onclick="window.open(this.href); return false;">Porovnat vybrané ('.count($_SESSION['porovnaniVyrobku']).')</a></div>';
}
else 
{ 
  $POROVNANI = '';
}





// *****************************************************************************
// DEFAULTNI SEO
// *****************************************************************************
//$titleX[C_LANG] = 'Nekvinda - Autobaterie, nabíječky autobaterií, zemědělská technika';         //defaultni title
//$keywordsX[C_LANG] = 'nekvinda, autobaterie, nabíječka autobaterií, nabíječky autobaterií, autolékárnička pro rok 2011, autolékárnička 2011, zemědělská technika, zetor';      //defaultni keywords
//$descriptionX[C_LANG] = 'E-shop Nekvinda-Obchod.cz nabízí autobaterie, nabíječky autobaterií, autolékárničky pro rok 2011 a další. Nabízíme také náhradní díly na traktory Zetor a další zemědělskou techniku.';   //defaultni description

	$query = "SELECT title , keywords , description , foot , overovaci , gaas , gatr FROM ".T_SETTING." WHERE lang = ".C_LANG;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	$titleX[C_LANG] = '';
  $keywordsX[C_LANG] = '';
  $descriptionX[C_LANG] = '';
  $footX[C_LANG] = '';
  $OVEROVACI_KOD = '';
  $GAAS = '';
  $GATR = '';
	
	while($z = mysql_fetch_assoc($v))
  {
		$titleX[C_LANG] = $z['title'];
    $keywordsX[C_LANG] = $z['keywords'];
    $descriptionX[C_LANG] = $z['description'];
    $footX[C_LANG] = $z['foot'];
    $OVEROVACI_KOD = $z['overovaci'];
    $GAAS = $z['gaas'];
    $GATR = $z['gatr'];
  }





if(isset($_GET['go']) && $_GET['go']=='porovnani'){
	
	if(!empty($_GET['del'])){
		if(is_numeric($_GET['del'])){
			unset($_SESSION['porovnaniVyrobku'][$_GET['del']]);
			Header('Location: '.$_SERVER['HTTP_REFERER']);
			exit;
			
		}elseif($_GET['del']=='all'){
			unset($_SESSION['porovnaniVyrobku']);
			
			$_SESSION['alert_js1']='Z porovnávání byly odstraněny všechny výrobky.';
			
			Header('Location: http://'.$_SERVER['HTTP_HOST']);
			exit;
			
		}
	}
	
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
		<head> 
			<title>'.uvozovky($titleX[C_LANG]).'</title> 
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
			<meta name="Description" content="'.uvozovky($descriptionX[C_LANG]).'" /> 
			<meta name="Keywords" content="'.uvozovky($keywordsX[C_LANG]).'" /> 
			<meta name="robots" content="index, follow" /> 
			<meta name="author" content="www.netaction.cz" /> 
			<meta name="google-site-verification" content="QHDWjikPVcDSuMSEXB40qRrmcIVpmPcJCIILIxtVnNM" />
			<base href="http://'.$_SERVER['SERVER_NAME'].'" /> 
			<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> 
			<link rel="stylesheet" href="/css/style.css" type="text/css" />
		</head>
		<body>
			<div class="porovnani_big">
			'.getPorovnaniTable().'	
			</div>
		</body>
	</html>     
		';
	exit;	
}





if(empty($_SERVER['HTTP_REFERER']) && empty($_COOKIE[REFERER_COOKIE_NAME])){
  //echo "pristup naprimo";
  setcookie(REFERER_COOKIE_NAME,"přímý přístup",time()+(60*60*10));
}elseif(empty($_COOKIE[REFERER_COOKIE_NAME])){
  //echo "pristup odnekud poprve, nastavime cookie";
  setcookie(REFERER_COOKIE_NAME,"".$_SERVER['HTTP_REFERER'],time()+(60*60*10));
}
// echo $_COOKIE[REFERER_COOKIE_NAME];




$onload=null;
$ADD_STYLE=null;
$PRODUCTS=null;
$PAGES=null;
$TEXT=null;
$tridit=null;
$id_home=null;
$ZALOZKY=null;
$podminka_vyrobci=null;
$prodWhereVyrobci=null;
$Qcat=null;
$COMMENTS=null;
$H1=null;
$POROVNAVAC=null;
$FORM=null;


if(empty($_SESSION['kontrola_reloadu']))$_SESSION['kontrola_reloadu']='';
if(empty($_SESSION['predchozi_server']))$_SESSION['predchozi_server']='';
if(empty($_SESSION['omezit_vyrobce']))$_SESSION['omezit_vyrobce']='';


// *****************************************************************************
//  PREDNASTAVENI PROMENNYCH
// *****************************************************************************
if(!empty($_GET['akce']))$url_akce_id = $_GET['akce'];
else $url_akce_id='';

if(!empty($_GET['idp']))$url_clanek_id = $_GET['idp'];
else $url_clanek_id='';

if(!empty($_GET['kategorie']))$url_cat_id = $_GET['kategorie'];
else $url_cat_id='';

if(!empty($_GET['produkt']))$url_produkt_id = $_GET['produkt'];
else $url_produkt_id='';

if(empty($_SESSION['basket_total'])) { 
  $_SESSION['basket_total'] = '0';
  $_SESSION['basket_suma'] = '0';
}

if(empty($_GET['sWord'])) //$sWord = 'Hledaný výraz...';
    $sWord = '';
else $sWord = $_GET['sWord'];
// *****************************************************************************
//  // PREDNASTAVENI PROMENNYCH
// *****************************************************************************






// *****************************************************************************
//  ZJISTETNI EXISTENCE POZADOVANEHO PRVKU V POZADOVANEM TVARU
// *****************************************************************************    
if(!empty($url_cat_id))
{
  $query = "SELECT name, descr FROM ".T_CATEGORIES." WHERE hidden=0 AND id =".$url_cat_id."";
  $sql = my_DB_QUERY($query,__LINE__,__FILE__);
  $url_cat_nazev = @mysql_result($sql, 0, 0);
  if (empty($url_cat_nazev ))
  { 
    $_SESSION['alert_js1']='Požadovaná kategorie byla odstraněna, nebo přesunuta, pokračujte prosím výběrem položky menu.\n\nDěkujeme za pochopení.';
    Header("HTTP/1.1 301 Moved permanently"); 
    Header("Location: http://".$_SERVER['SERVER_NAME']."/"); 
    exit;
  }
  else
  {
    if(!empty($url_produkt_id))
    {
      $query = "SELECT name FROM ".T_GOODS." WHERE hidden=0 AND id =".$url_produkt_id.""; 
      $sql = my_DB_QUERY($query,__LINE__,__FILE__); 
      $url_produkt_nazev = @mysql_result($sql, 0, 0); 
      if (empty($url_produkt_nazev ))
      {
        $_SESSION['alert_js1']='Požadovaný produkt byla odstraněn, nebo přesunut, pokračujte prosím výběrem položky menu.\n\nDěkujeme za pochopení.';
        Header("HTTP/1.1 301 Moved permanently"); 
        Header("Location: http://".$_SERVER['SERVER_NAME']."/".$url_cat_id."-".text_in_url($url_cat_nazev)."/"); 
        exit;
      }
    }
  }
}
elseif(!empty($url_clanek_id))
{
  $query = "SELECT title FROM ".T_CONT_PAGES." WHERE hidden=0 AND id =".$url_clanek_id.""; 
  $sql= my_DB_QUERY($query,__LINE__,__FILE__);
  $url_clanek_nazev = @mysql_result($sql, 0, 0);
  if (empty($url_clanek_nazev ))
  {
    $_SESSION['alert_js1']='Požadovaná stránka byla odstraněna, nebo přesunuta, pokračujte prosím výběrem položky menu.\n\nDěkujeme za pochopení.';
    Header("HTTP/1.1 301 Moved permanently"); 
    Header("Location: http://".$_SERVER['SERVER_NAME']."/"); 
    exit;
  }     
}
elseif(!empty($url_akce_id))
{
  $query = "SELECT name FROM ".T_AKCE." WHERE hidden=0 AND id =".$url_akce_id.""; 
  $sql= my_DB_QUERY($query,__LINE__,__FILE__);
  $url_akce_nazev = @mysql_result($sql, 0, 0);
  if (empty($url_akce_nazev ))
  {
    $_SESSION['alert_js1']='Požadovaná akce byla odstraněna, nebo přesunuta, pokračujte prosím výběrem položky menu.\n\nDěkujeme za pochopení.';
    Header("HTTP/1.1 301 Moved permanently"); 
    Header("Location: http://".$_SERVER['SERVER_NAME']."/"); 
    exit;
  }     
}
// *****************************************************************************
//  ZJISTETNI EXISTENCE POZADOVANEHO PRVKU V POZADOVANEM TVARU
// *****************************************************************************  




// *****************************************************************************
//  RAZENI PRODUKTU
// *****************************************************************************
if(empty($order_select['poradi']))$order_select['poradi'] = "";
if(empty($order_select['name']))$order_select['name'] = "";
if(empty($order_select['cena']))$order_select['cena'] = "";
if(empty($order_checked['poradi']))$order_checked['poradi'] = "";
if(empty($order_checked['name']))$order_checked['name'] = "";
if(empty($order_checked['cena']))$order_checked['cena'] = "";

$pocet1=PRODUCTS_ON_PAGE;
$pocet2=PRODUCTS_ON_PAGE*2;
$pocet3=PRODUCTS_ON_PAGE*3;
$pocet4=PRODUCTS_ON_PAGE*4;

if(empty($products_on_page_select[$pocet1]))$products_on_page_select[$pocet1] = "";
if(empty($products_on_page_select[$pocet2]))$products_on_page_select[$pocet2] = "";
if(empty($products_on_page_select[$pocet3]))$products_on_page_select[$pocet3] = "";
if(empty($products_on_page_select[$pocet4]))$products_on_page_select[$pocet4] = "";

if(empty($_SESSION['order_shop'])) $_SESSION['order_shop'] = DEFAULT_ORDER;
if(empty($_SESSION['products_on_page'])) $_SESSION['products_on_page'] = PRODUCTS_ON_PAGE;
if(empty($_SESSION['smer_trideni'])) $_SESSION['smer_trideni'] = "asc";

$order_checked[$_SESSION['order_shop']] = 'checked="checked"';
$order_select[$_SESSION['order_shop']] = ' selected="selected"';
$products_on_page_select[$_SESSION['products_on_page']] = ' selected="selected"';

if((!empty($_SESSION['kategorie']) && $_SESSION['kategorie']=='doporucujeme') OR (!empty($_SESSION['kontrola_reloadu']) && $_SESSION['kontrola_reloadu']=='doporucujeme')) $_SESSION['kategorie2']=$_SESSION['kategorie'];
else unset($_SESSION['kategorie2']);



if($_SESSION['kontrola_reloadu']!==$_SESSION['kategorie']  OR  $_SERVER['SERVER_NAME']!==$_SESSION['predchozi_server']){
	if(empty($_SESSION['kategorie2']))$_SESSION['kategorie2']=null;
	if($_GET['go']!='doporucujeme' AND $_SESSION['kategorie2']!='doporucujeme') {
		unset($_SESSION['vyrobci_podminka']);
		$query='SELECT name, id FROM '.T_PRODS.' WHERE '.SQL_C_LANG.' AND hidden=0 ORDER BY name ';
		$sql= my_DB_QUERY($query,__LINE__,__FILE__);
		while($z=mysql_fetch_array($sql)) {
			$vyrobce=$z['name'];
			$_SESSION['where_'.$vyrobce] = "off";
		}
	}
}


$query='SELECT name, id FROM '.T_PRODS.' WHERE '.SQL_C_LANG.' AND hidden=0 ORDER BY name ';
$sql= my_DB_QUERY($query,__LINE__,__FILE__);

if(isset($_GET['znacky']) && $_GET['znacky']=='on') {
	
	$_SESSION['omezit_vyrobce']=null;


	switch(TRIDENI_VYROBCE){
	
		default:
		case 1:{	
			while($z=mysql_fetch_array($sql)) {
				$vyrobce=$z['name'];
				
				$trans_vyrobce = array(" " => "_", "." => "-");  // preklad problematickych znaku v nazvu vyrobce
				$vyrobce = strtr($vyrobce, $trans_vyrobce);
				
				$id_vyrobce=$z['id'];
				if(empty($_SESSION['where_'.$vyrobce])) { 
					$_SESSION['where_'.$vyrobce] = "off";
				}
				if(!empty($_GET['where_'.$vyrobce])){ 
				     $_SESSION['where_'.$vyrobce] = $_GET['where_'.$vyrobce];
				     if($_GET['where_'.$vyrobce]=='on') $_SESSION['vyrobci_podminka'][$id_vyrobce]='1';
				     else  $_SESSION['vyrobci_podminka'][$id_vyrobce]='0';    
				}
				$_SESSION['omezit_vyrobce'].='&amp;where_'.$vyrobce.'='.$_SESSION['where_'.$vyrobce];
			}
			break;	
		}
		case 2:{
			while($z=mysql_fetch_array($sql)) {
				$vyrobce=$z['name'];
				$id_vyrobce=$z['id'];
		 		$_SESSION['vyrobci_podminka'][$id_vyrobce]='0';
				if(empty($_SESSION['where_'.$vyrobce])) { 
					$_SESSION['where_'.$vyrobce] = "off";	
				}else{ 
					if(empty($_GET['where_'.$vyrobce]))$_GET['where_'.$vyrobce]='off';
				     $_SESSION['where_'.$vyrobce] = $_GET['where_'.$vyrobce];
				     if($_GET['where_'.$vyrobce]=='on') $_SESSION['vyrobci_podminka'][$id_vyrobce]='1';   
				}
				$_SESSION['omezit_vyrobce'].='&amp;where_'.$vyrobce.'='.$_SESSION['where_'.$vyrobce];
			}
			break;
		}
	
	}
	
	
	
	$_SESSION['omezit_vyrobce'].='&amp;znacky=on';
	
	
	razeni();
	Header("Location: ".$_GET['reload']."");                                    
	exit;                                                                                               
}


razeni2();
if(empty($_GET['kategorie'])) $id_parent = 0;
elseif($parent=getParent($_GET['kategorie'])==0) $id_parent = 0;
else $id_parent = $_GET['kategorie'];

// *****************************************************************************
//  // RAZENI PRODUKTU
// *****************************************************************************






// *****************************************************************************
//  PODMINKA ZOBRAZENI VYROBCU
// *****************************************************************************
if(isset($_SESSION['vyrobci_podminka'])) {
reset($_SESSION['vyrobci_podminka']);
$podminka_vyrobci='';
while(list($key,$val)=each($_SESSION['vyrobci_podminka'])) {
  if($val==1) {
    if($podminka_vyrobci=='') $podminka_vyrobci='(id_vyrobce='.$key;
    else $podminka_vyrobci.=' OR id_vyrobce='.$key;
  }
}
if($podminka_vyrobci!=='') $podminka_vyrobci=' AND '.$podminka_vyrobci.' ) ';
}
// *****************************************************************************
//  // PODMINKA ZOBRAZENI VYROBCU
// *****************************************************************************








// *****************************************************************************
// INCLUDE SCRIPTU
// *****************************************************************************
$inc_file='';

if(empty($_GET['go']) && !isset($_GET['sWord'])) {
	$query = "SELECT id,title,content FROM ".T_CONT_PAGES." 
	WHERE homepage = 1 AND hidden = 0 AND ".SQL_C_LANG." LIMIT 0,1"; // AND (in_menu = 1 OR in_menu = 2)
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
		$id_home = $z['id'];
	}
}else if($_GET['go'] == "akcni-nabidka" || $_GET['go'] == "doporucujeme" || isset($_GET['sWord'])){
	$id_home=null;
	$inc_file = "shop";
}else{
	$id_home=null;
	$inc_file = $_GET['go'];
}


$inc_file = $inc_file.".php";


if(file_exists($inc_file)) include_once $inc_file;
else $data = '<div class="clanek">Hledaný dokument na serveru neexistuje.</div>';
// *****************************************************************************
//  // INCLUDE SCRIPTU
// *****************************************************************************








// *****************************************************************************
// DOPORUCUJEME BOX
// *****************************************************************************
$DOPORUCUJEME = '';

/*
$query = '
select g.id as id, g.name as name, g.cena as cena, g.cena_eshop as cena_eshop, g.id_vyrobce as id_vyrobce, g.dph as dph, g.kod as kod, g.dop_cena AS dop_cena, g.novinka AS novinka,
g.anotace as anotace,g.img as img,c.name as cname,c.id as cid from '.T_GOODS.' g
inner join '.T_GOODS_X_CATEGORIES.' gc on g.id=gc.id_good
inner join '.T_CATEGORIES.' c on c.id=gc.id_cat
where g.hidden=0 and c.hidden=0 and g.doporucujeme=1 group by g.id';
$v = my_DB_QUERY($query,__LINE__,__FILE__);

$counter = 0;
while(($z = mysql_fetch_assoc($v)) && $counter<POCET_DOP)
{
	$DOPORUCUJEME .= good_box_right($z);
	$counter++;		
}

if(!empty($DOPORUCUJEME)){
	$DOPORUCUJEME = '
	<div class="box">
		<span class="nadpis">Doporučujeme</span>
		<div class="bcont">
  		'.$DOPORUCUJEME.'
      <div class="clear"></div>
      <br />
      <a href="'.HTTP_ROOT.'/doporucujeme/" title="Zobrazit všechny doporučované produkty">Zobrazit všechny doporučené</a>
		</div>
		<div class="clear"></div>
	</div>
	';
}
*/
// *****************************************************************************
// DOPORUCUJEME BOX
// *****************************************************************************    




// *****************************************************************************
// AKCNI NABIDKA BOX
// *****************************************************************************
$AKCE = "";
//$AKCE = getAkcniNabidka(2);
if(!empty($AKCE)
){
	$AKCE = '
	<div class="akce">
		'.$AKCE.'
	</div>
	';
}
// *****************************************************************************
// AKCNI NABIDKA BOX
// *****************************************************************************




// *****************************************************************************
// BANNER
// *****************************************************************************
$BANER = '';
$query = "
SELECT id , nazev , img , odkaz , text , new_window FROM ".T_INZERENTI."
WHERE hidden = 0
AND ".SQL_C_LANG."
ORDER BY poradi
";
$v = my_DB_QUERY($query,__LINE__,__FILE__);

while($z = mysql_fetch_assoc($v))
{
	$nazev=$z['nazev'];
	$id=$z['id'];
	$img=$z['img'];
	$odkaz=$z['odkaz'];
	$text_obsah=$z['text'];
	$nove_okno=$z['new_window'];
	
	if($nove_okno==1)
  {
	  $new_window='onclick="window.open(this.href);return false;"';
	}
  else
  {
	  $new_window='';                                                                
	}
	  
	if(!empty($img))
  {
    $BANER .= '
    <a '.$new_window.' title="'.$nazev.'" href="'.$odkaz.'">
	    <img alt="" src="UserFiles/Inzerenti/'.$id.'.'.$img.'" />
	  </a>
    ';
  }
	if(!empty($text_obsah))
  {
    $BANER .= '
    <a '.$new_window.' title="'.$nazev.'" href="'.$odkaz.'">
	    '.strip_tags($text_obsah).'
	  </a>
    ';
  }
}

if(!empty($BANER))
{
	$BANER = '
	<div class="akce">
		'.$BANER.'
	</div>
	';
}
// *****************************************************************************
// BANNER
// *****************************************************************************



// *****************************************************************************
// NOVINKY
// *****************************************************************************
$NOVINKY='';
/*
$query = "SELECT id, txt, vlozeno FROM ".T_NEWS." 
WHERE hidden = 0 AND ".SQL_C_LANG." ORDER BY poradi, vlozeno DESC LIMIT 0,4";

$v = my_DB_QUERY($query,__LINE__,__FILE__);
$counter=0;
while (($z = mysql_fetch_array($v)) && $counter<3) {
  
	$dat = timestamp_to_date($z['vlozeno']);

	$link = ' <a href="'.HTTP_ROOT.'/novinky/'.$z['id'].'.html">celý článek zde</a>';

	$txt = lenght_of_string(MAX_TXT_2,strip_tags($z['txt']),$link);
	
	$NOVINKY .= "<strong>$dat</strong> - ".$txt."<br /><br />";
	$counter++;
}

if(mysql_num_rows($v)>3)$NOVINKY .= "<br /><a href='/novinky/'>Všechny novinky</a>";


if(!empty($NOVINKY)) {
    $NOVINKY = '
    			<div class="box">
	    			<span class="nadpis">Novinky</span>
	    			<div class="bcont">
				'.$NOVINKY.'
				</div>
			</div>
			';
}
*/
// *****************************************************************************
//  NOVINKY
// *****************************************************************************







// *****************************************************************************
// DOWNLOAD
// *****************************************************************************
$DOWNLOAD = '';
/*
$query= "SELECT id,title,in_menu,menu_pos,homepage,hidden FROM ".T_CONT_PAGES." 
	WHERE ".SQL_C_LANG." AND hidden = 0 AND in_menu = 3 ORDER BY menu_pos";
$v = my_DB_QUERY($query,__LINE__,__FILE__);

while ($z = mysql_fetch_array($v)) {
  $id=$z['id'];
  $naz=$z['title'];
  $DOWNLOAD .= "<a href=\"".HTTP_ROOT."/clanek/".$id."-".text_in_url($naz).".html\">$naz</a><br />";
}
                                                                                              
if(!empty($DOWNLOAD)) {
    $DOWNLOAD = '
			<div class="box">
	    			<span class="nadpis">Download</span>
	    			<div class="bcont">
				'.$DOWNLOAD.'
				</div>
			</div>
			';
}
*/
// *****************************************************************************
// DOWNLOAD
// *****************************************************************************








// *****************************************************************************
// MENU - generovani textovych menu + obsah stranky
// *****************************************************************************
$TOP_MENU='';

$query = "SELECT id,title,in_menu,menu_pos,homepage,content FROM ".T_CONT_PAGES." 
WHERE ".SQL_C_LANG." AND hidden = 0 ORDER BY menu_pos, title";// AND (in_menu = 1 OR in_menu = 2)
$v = my_DB_QUERY($query,__LINE__,__FILE__);

while ($z = mysql_fetch_array($v)) {
	$id = $z['id'];
	$naz = $z['title'];
	
	if((isset($_GET['idp']) && $id == $_GET['idp']) || (isset($id_home) && $id == $id_home)) {
		$style = ' class="selected"';
		$title = $z['title'];
		
		$H1 = $z['title'];

		$galerie=pripojene_fotogalerie($id);
		
		$text=$z['content'];
		
		if(strpos($text,"@@@formular@@@")){
			$text=str_replace('@@@formular@@@','',$text);
			$DOTAZNIK=formDOTAZNIK('Zeptejte se nás');
		}else{
			$DOTAZNIK='';
		}
			
		$PRODUCTS='';		
		$TEXT = $text.$galerie.$DOTAZNIK;
	
	}else{
		$style = "";
	}

	
	if($z['homepage'] != 1) {
		$link = '<a href="'.HTTP_ROOT.'/clanek/'.$id.'-'.text_in_url($naz).'.html" '.$style.'>'.$naz.'</a><span class="tilde"></span>';
		switch($z['in_menu']){
			case 1: $TOP_MENU .= $link; break;
			case 2: $MENU .= $link; break;
		}
    }

} 

if(!empty($id_home)) $style = ' class="selected"'; 
else $style='';

$TOP_MENU='<a href="/" title="Úvodní strana" '.$style.'>Úvod</a><span class="tilde"></span>'.$TOP_MENU;
$TOP_MENU=substr($TOP_MENU,0,-27);
// *****************************************************************************
// MENU
// *****************************************************************************




if(!empty($_SESSION['user']['UID']))
{
	$USER = '
	<div id="userbox">
    <div id="userbox_right">
			<a href="'.HTTP_ROOT.'/?go=user&amp;orders='.$_SESSION['user']['sha'].'">Moje objednávky</a>
			<a href="'.HTTP_ROOT.'/?go=user&amp;edit='.$_SESSION['user']['sha'].'">Změnit údaje</a>
			<a href="'.HTTP_ROOT.'?go=user&amp;logout">Odhlásit se</a>
    </div>
	</div>
  ';
}
else
{
	$USER = '
	<div id="userbox">
    <div id="userbox_right">
			<a href="#" onclick="document.getElementById(\'userbox_login\').style.display=\'block\'; return false;">Přihlášení</a>
      <a href="'.HTTP_ROOT.'/registrace/">Nová registrace</a>
    </div>

		<form id="userbox_login" action="index.php?go=user" method="post">
		  <div>
        <span id="userbox_login_close" onclick="document.getElementById(\'userbox_login\').style.display=\'none\'; return false;">X</span>
			  <strong>E-mail:</strong><br />
        <input type="text" name="login1" value="" /><br />
			  <strong>Heslo:</strong><br />
        <input type="password" name="pass1" value="" /><br />
			  <input type="submit" value="Přihlásit se" /><br />
			  <a href="'.HTTP_ROOT.'/zapomenute-heslo/">Zapomněli jste heslo?</a>
		  </div>
		</form>
	</div>
  ';
}




// *****************************************************************************
//  SEO
// *****************************************************************************
if(empty($title)) $TITLE = $titleX[C_LANG];
else $TITLE = strip_tags($title) . ' - ' . $titleX[C_LANG];

if(empty($description)) $DESCRIPTION = strip_tags($H1).' - '.$descriptionX[C_LANG];
else $DESCRIPTION = $description;

if(empty($keywords)) $KEYWORDS = $keywordsX[C_LANG];
else $KEYWORDS = $keywords;

if(empty($foot)) $FOOT = $footX[C_LANG];
else $FOOT = $foot;
// *****************************************************************************
//  // SEO
// *****************************************************************************









// *****************************************************************************
// DROBECKOVA NAVIGACE
// *****************************************************************************
$NAVIGATION='';



if(!empty($url_akce_id)){
	$id=$url_akce_id;
	$switch='akce';
}elseif(!empty($url_clanek_id)){
	$id=$url_clanek_id;
	$switch='clanek';	
}elseif(!empty($url_cat_id)){
	$id=$url_cat_id;
	$switch='kategorie';	
}elseif(!empty($url_produkt_id)){

	if(!empty($_GET['kategorie'])){
		$id=$_GET['kategorie'];
		$switch='kategorie';
	}else{
		$id=null;
		$switch=null;	
	}
		
}elseif($_GET['go']=='doporucujeme'){
	$id=null;
	$switch='doporucujeme';
}elseif($_GET['go']=='basket'){
	$id=null;
	$switch='basket';
}else{
	$id=null;
	$switch=null;
}

$image=' / ';

$NAVIGATION="<a href=\"".HTTP_ROOT."\">Domů</a> ".getNavigation($id,$switch,$image)."<br />";

// *****************************************************************************
// // DROBECKOVA NAVIGACE
// *****************************************************************************








// *****************************************************************************
// MENU KATEGORII 
// *****************************************************************************
 
  
  // *********** MENU ***********************
  global $vetev;
  $vetev=-1;
  $_SESSION['mezera']=0;              
  
  
  if(ZALOZKY){
     if(isset($_GET['kategorie']))$aktualni_kat=$_GET['kategorie']; //nacteni promenne
  	elseif(empty($_SESSION['lastSuperParentId'])) $aktualni_kat=283;
  	else $aktualni_kat=$_SESSION['lastSuperParentId'];
  }else{
  	if(isset($_GET['kategorie']))$aktualni_kat=$_GET['kategorie']; //nacteni promenne
  	else $aktualni_kat=0;
  }

  $stromecek = posloupnost_k_obsahu($aktualni_kat);  
    
    
  $odkud=0;    
  if(ZALOZKY){
  	$ZALOZKY=getSuperParents($aktualni_kat);
  	$odkud=1;
  }
                                
  $shop_menu=''.vypis($odkud,$stromecek).''; //vypsani
//   $vyrez=str_replace($vetev,'', $shop_menu);
//   $shop_menu=$vetev.''.$vyrez;
    
 // *********** MENU *********************** 
  

  //strom kategorii
  if(!empty($strom)) reset($strom);
  else $strom=null;
  
  $pocet_vet=count($strom);
  $pocet_radku=$pocet_vet / 3;

  $krok=0;
  $stromek='';
  for($i=$pocet_vet;$i>0;$i=$i-1) {
    $krok++;
    $stromek.='<td>'.$strom[$krok-1].'</td>
    ';
    if($krok % 3 == 0 AND $krok>0) $stromek.='
      </tr>
      <tr>';
     
    
  }
  if($stromek !='') $stromek='<table id="vnorene_kategorie" summary="">
              <tr>
                '.$stromek.'
              </tr>
            </table>';
// *****************************************************************************
// MENU KATEGORII
// *****************************************************************************



// *****************************************************************************
// NEJPRODÁVANĚJŠÍ
// *****************************************************************************
$TOP10='';

/*
// Skutečně nejprodávanější.
if(isset($TOP['dopln']) && $TOP['dopln']>0) $pocet_vet=$TOP['dopln'];
else $pocet_vet=POCET_TOP;

if(isset($TOP['vylouceni']))$podminka_top=$TOP['vylouceni'];
else $podminka_top='';

$query = "
SELECT z.id AS id, z.name AS name, z.kod AS kod, z.dop_cena AS dop_cena, z.novinka AS novinka, z.id_vyrobce, z.img, z.cena, z.dph, sum(ks) AS pocet
FROM ".T_ORDERS_PRODUCTS." o
INNER JOIN ".T_GOODS." z ON z.id=o.id_produkt AND z.lang=".C_LANG." AND z.cena>0
WHERE o.id_produkt>0  $podminka_top GROUP BY o.id_produkt
ORDER BY pocet DESC, z.id DESC
LIMIT ".$pocet_vet;
$v = my_DB_QUERY($query,__LINE__,__FILE__);
   
while($z = mysql_fetch_assoc($v))
{
	$query2 = "
  SELECT id, name
  FROM  ".T_CATEGORIES.", ".T_GOODS_X_CATEGORIES."
  WHERE ".T_GOODS_X_CATEGORIES.".id_good = ".$z['id']."
  AND ".T_CATEGORIES.".id=".T_GOODS_X_CATEGORIES.".id_cat
  ";
	$v2 = my_DB_QUERY($query2,__LINE__,__FILE__);

	while($z2 = mysql_fetch_assoc($v2))
  { 
		$z['cid']=$z2['id'];
		$z['cname']=$z2['name'];
  }
	
	$TOP10 .= good_box_right($z);
	$i++;
}
*/

// Nejprodávanější dle výběru admina
/*
$query = "
SELECT
id , name , kod , dop_cena , novinka , id_vyrobce, img , cena , cena_eshop , dph
FROM ".T_GOODS."
WHERE nejprodavanejsi = 1
AND hidden = 0
AND ".SQL_C_LANG."
ORDER BY id DESC
";
$v = my_DB_QUERY($query,__LINE__,__FILE__);

while($z = mysql_fetch_assoc($v))
{
  $TOP10 .= good_box_right($z);
}


if($TOP10!='')
{
	$TOP10='
  <div class="box">
	  <span class="nadpis">Nejprodávanější</span>
	  <div class="bcont">
    	'.$TOP10.'
  	</div>
  	<div class="clear"></div>
	</div>
  ';
}
*/
// *****************************************************************************
// NEJPRODÁVANĚJŠÍ
// *****************************************************************************








// *****************************************************************************
//  AKCE DO LEVEHO MENU
// *****************************************************************************
$akce_menu='';
$query = "SELECT id, name FROM ".T_AKCE." WHERE hidden = 0 AND ".SQL_C_LANG." ORDER BY position";
$v = my_DB_QUERY($query,__LINE__,__FILE__);
while ($z = mysql_fetch_array($v)) {
$akceid=$z['id'];
$akcenazev=$z['name'];
	$query2= "SELECT id_good FROM ".T_GOODS_X_AKCE." WHERE id_cat = ".$akceid."";
	$v2 = my_DB_QUERY($query2,__LINE__,__FILE__);
	$pocet = mysql_num_rows($v2);
	if($pocet>0) {
		if(isset($_GET['akce']) && $_GET['akce'] == $akceid) {
	      	$css=' class="vybrano_akce"';
	      	$parents .= ' / <strong>'.$z['name'].'</strong>';/**/
	     }else $css='class="akce"';
	    	$akce_menu.='<a '.$css.' href="/akcni-nabidka/'.$akceid.'-'.text_in_url($akcenazev).'/" title="">'.$akcenazev.'</a>';
  	}
}        
// *****************************************************************************
// AKCE DO LEVEHO MENU
// *****************************************************************************







// *****************************************************************************
//  ODKAZ DOPORUCUJEME DO LEVEHO MENU
// *****************************************************************************
if($_GET['go']=='doporucujeme' ) $css=' class="vybrano_dop"';
else $css=' class="dop"';

$dop_menu = '<a '.$css.' href="/doporucujeme/" title="">Doporučujeme</a>';
// *****************************************************************************
//  ODKAZ DOPORUCUJEME DO LEVEHO MENU
// *****************************************************************************



$LEFT_MENU=$dop_menu.$akce_menu.$shop_menu;






// *****************************************************************************
// ŘAZENÍ
// *****************************************************************************

if ($_GET['go'] == "akcni-nabidka" OR $_GET['go'] == "doporucujeme" OR isset($_GET['sWord'])) {
	if($_GET['go'] == "akcni-nabidka"){
		$reload = HTTP_ROOT."/akcni-nabidka/".$_GET['akce']."-".$_GET['nazevakce']."/";
		$_SESSION['kontrola_reloadu']=$aktualni_stranka='akcni-nabidka/'.$_GET['akce'].'-'.$_GET['nazevakce'].'/';
	}elseif($_GET['go'] == "doporucujeme"){
		$reload = HTTP_ROOT."/doporucujeme/";
		$_SESSION['kontrola_reloadu']=$aktualni_stranka='doporucujeme/';
	}
	if(isset($_GET['sWord']))$reload = HTTP_ROOT."/vyhledavani/?sWord=$search";
	if($_SESSION['kontrola_reloadu']!== $_SESSION['kategorie']) $_SESSION['kategorie']=$_SESSION['kontrola_reloadu'];
}else{
	$reload = HTTP_ROOT."/".$_SESSION['kategorie']."/";
	$_SESSION['kontrola_reloadu']=$_SESSION['kategorie'];
}

$reload = urlencode($reload);
if(empty($omezeni))$omezeni=null;

if(($_GET['go'] == "shop" || $_GET['go'] == "akcni-nabidka" || $_GET['go'] == "doporucujeme") && empty($_GET['produkt']) && empty($_GET['sWordP'])) {  
          $select='<select onchange="order_shop(\''.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.$_SESSION['omezit_vyrobce'].'&amp;order_shop=\'+this.options[this.selectedIndex].value)">
		<option value="'.DEFAULT_ORDER.'" '.$order_select['poradi'].'>původní nastavení</option> 
          <option value="name" '.$order_select['name'].'>názvu</option>
          <option value="cena" '.$order_select['cena'].'>ceny</option>                             
          </select>'; 
		
		  
          
          $pocet1=PRODUCTS_ON_PAGE;
          $pocet2=PRODUCTS_ON_PAGE*2;
          $pocet3=PRODUCTS_ON_PAGE*3;
          $pocet4=PRODUCTS_ON_PAGE*4;         
          
          $select_page='<select onchange="order_shop(\''.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;products_on_page=\'+this.options[this.selectedIndex].value)"> 
          <option value="'.$pocet1.'" '.$products_on_page_select[$pocet1].'>'.$pocet1.'</option>
          <option value="'.$pocet2.'" '.$products_on_page_select[$pocet2].'>'.$pocet2.'</option>
          <option value="'.$pocet3.'" '.$products_on_page_select[$pocet3].'>'.$pocet3.'</option>
          <option value="'.$pocet4.'" '.$products_on_page_select[$pocet4].'>'.$pocet4.'</option>
          </select>';

          $trideni_vyrobce = NULL;
                                                 
          if(empty($_GET['sWord'])) $trideni_vyrobce=trideni_vyrobcu($omezeni,$reload);     
		                                                                                                                                   
          $tridit = $trideni_vyrobce . '
          <div class="radit">
           <form action="" method="post">
             <div class="smer">';
           if(empty($_GET['sWord'])) $tridit.='
             <span>Řadit podle: </span>'.$select.'<a href="javascript:order_shop(\''.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;smer=asc'.$_SESSION['omezit_vyrobce'].'\')" title="Vzestupně" ><img src="/img/sort_up.png" alt="vzestupně" /></a>
		   <a href="javascript:order_shop(\''.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;smer=desc'.$_SESSION['omezit_vyrobce'].'\')" title="Sestupně" ><img src="/img/sort_down.png" alt="sestupně" /></a>';
		   
            $tridit.='
            </div>
            </form>
     	  <div class="clear">
     	  </div>
           </div>
           ';
          
        
		$razeni = "
		<div style=\"margin-top: 10px; \">
		
			řadit podle: 
			
			<input type=\"radio\" name=\"order\" value=\"name\" ".$order_checked['name']." 
				onclick=\"order_shop('".HTTP_ROOT."?go=".$_GET['go']."&amp;order_shop=name&amp;reload=".$reload.$_SESSION['omezit_vyrobce']."')\" 
				id=\"name\" /><label for=\"name\">&nbsp;názvu</label>
			
			<input type=\"radio\" name=\"order\" value=\"cena\" ".$order_checked['cena']." 
				onclick=\"order_shop('".HTTP_ROOT."?go=".$_GET['go']."&amp;order_shop=cena&amp;reload=".$reload.$_SESSION['omezit_vyrobce']."')\" 
				id=\"cena\" /><label for=\"cena\">&nbsp;ceny</label>
			
			<br /><br />
		</div>";
}

$_SESSION['predchozi_server']=$_SERVER['SERVER_NAME'];
// *****************************************************************************
// ŘAZENÍ
// *****************************************************************************






// *****************************************************************************
//  SEO
//  1-clanek,2-kategorie,3-produkt,4-akce
// *****************************************************************************
include('./admin/shop/seo.php');

if (!empty($_GET['idp'])) {
  $idneceho=$_GET['idp'];
  $ceho=1;
}elseif (!empty($_GET['produkt'])) {
  $idneceho=$_GET['produkt'];
  $ceho=3;
}elseif (!empty($_GET['kategorie'])) {
  $idneceho=$_GET['kategorie'];
  $ceho=2;
}elseif (empty($_GET['id']) && empty($_GET['kategorie']) && empty($_GET['idp']) ) {
  $idneceho=$id_home;
  $ceho=1;
}elseif (!empty($_GET['akce'])) {
  $idneceho=$_GET['akce'];
  $ceho=4;
}


if($idneceho>0) $SEO=nacti_seo($idneceho,$ceho);

if (!empty($SEO['seo_title'])) $TITLE=$SEO['seo_title'];
if (!empty($SEO['seo_keywords'])) $KEYWORDS=$SEO['seo_keywords'];
if (!empty($SEO['seo_description'])) $DESCRIPTION=$SEO['seo_description'];
if (!empty($SEO['seo_foot'])) $FOOT=$SEO['seo_foot'];
// *****************************************************************************
//  // SEO
// *****************************************************************************


if($_GET['go']=='404')
{
	$_SESSION['alert_js1']='Požadovaná stránka byla odstraněna, nebo přesunuta, pokračujte prosím výběrem položky menu.\n\nDěkujeme za pochopení.';

	Header("HTTP/1.1 301 Moved Permanently"); 
  Header("Location: http://".$_SERVER['SERVER_NAME']."/");
  exit;
}


// Vložení slideru místo shortcode.
include_once($_SERVER['DOCUMENT_ROOT'].'/functions/functions_slider.php');
valid_shortcode($TEXT);
shortcode_slider($TEXT); // Shortcode za slider.


Header("Pragma: no-cache");
Header("Expires: ".GMDate("D, d M Y H:i:s")." GMT");

include ("_template.php");

?>
