<?php


include_once($_SERVER['DOCUMENT_ROOT']."/kurz.php");


// TODO: zjistit vsechny produkty, kategorie a vyrobce, ktere maji nastaveno 
// hidden = 1 (u kategorii take vnorene kategorie), sestavit z nich where 
// a pracovat s nim v cele verejne casti

// TODO: zajistit, ze pri pristupu na stranku produktu ktery je skryty 
// nebo patri k vyrobci ci kategorii ktere jsou skryte, nebude skutecne 
// zobrazen

// TODO: sprava zaregistrovanych uzivatelu, vyresit slevy bud skupinami 
// nebo individualne



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

define('IMG_C_O',"./UserFiles/categories/original/"); // cesta pro original 
define('IMG_C_S',"./UserFiles/categories/small/"); // cesta pro nahled - musi koncit /
define('IMG_C_M',"./UserFiles/categories/middle/"); // cesta pro detail - musi koncit /

define('IMG_I_O',"./UserFiles/Icons_parameters/original/"); // cesta pro original
define('IMG_I_S',"./UserFiles/Icons_parameters/small/"); // cesta pro nahled - musi koncit /

// fotogalerie
define('IMG_F_O2',"./UserFiles/fotogalerie/original/"); // cesta pro original 
define('IMG_F_S2',"./UserFiles/fotogalerie/small/"); // cesta pro nahled - musi koncit /
define('IMG_F_M2',"./UserFiles/fotogalerie/middle/"); // cesta pro detail - musi koncit / 



// soubory
// $_SESSION['UserFilesPath'] = "/flava/UserFiles/";// pro ukladani souboru pomoci filemanageru v FCKeditoru
define('FILES_UPL',"./UserFiles/download/"); // cesta pro upload souboru k produktum a strankam VZTAZENO K ADRESARI ADMIN!!!

// pocet produktu na strance - zobrazujeme ve 2 sloupcich, takze PRODUCTS_ON_PAGE 
// by melo byt sude cislo
define('PRODUCTS_ON_PAGE',36);
// počet nejprodávanějších produktů
define('POCET_TOP',3);
// počet nejprodávanějších produktů
define('POCET_DOP',3);
// odkaz na domenu
/*
$http_root = $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/";
$trans = array ("//" => "/");
define('HTTP_ROOT',"http://".strtr($http_root, $trans));
*/

// $http_root = $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "/";
//define('HTTP_ROOT',"http://".$_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
define('HTTP_ROOT',"http://".$_SERVER['SERVER_NAME']);

// aktualni stranka/dokument
define('THIS_PAGE',HTTP_ROOT."?".$_SERVER['QUERY_STRING']);

define('EMAIL_REKLAMA_NETACTION' ,
'Vyrobilo: <a title="e-shopy, redakční systémy a propagace napříč internetem" href="http://www.netaction.cz?utm_source='.$_SERVER['SERVER_NAME'].'&utm_medium=email_copy&utm_campaign=client_mail_foot">NetAction.cz, s.r.o.</a>'
);  // Pro emailovou komunikaci malí reklama netaction.



if(empty($_GET['go'])){
  $_GET['go']='';
}


if(!empty($_GET['kategorie']))
{
  $_GET['kategorie'] = intval($_GET['kategorie']);

  $query2 = "SELECT name FROM ".T_CATEGORIES." WHERE id = '".$_GET['kategorie']."' LIMIT 1";
  $vcatname = my_DB_QUERY($query2,__LINE__,__FILE__);
	$vcategorie_nazev = @mysql_result($vcatname, 0, 0);
  $_SESSION['kategorie']=$aktualni_stranka=$_GET['kategorie'].'-'.text_in_url($vcategorie_nazev);
}
elseif($_GET['go'] == "akcni-nabidka")
{
    if(!empty($_GET['akce'])) $_SESSION['kategorie']='akcni-nabidka/'.$_GET['akce'].'-'.$_GET['nazevakce'].'/';
    else $_SESSION['kategorie']=str_replace('http://'.$_SERVER['SERVER_NAME'].'/','', $_GET['reload']);
}
elseif($_GET['go'] == "doporucujeme") $_SESSION['kategorie']=str_replace('http://'.$_SERVER['SERVER_NAME'].'/','', $_SERVER['HTTP_REFERER']);
elseif(!empty($_SERVER['HTTP_REFERER'])) $_SESSION['kategorie']=substr(str_replace('http://'.$_SERVER['SERVER_NAME'].'/','', $_SERVER['HTTP_REFERER']),0,-1);
else $_SESSION['kategorie']='';

 $kontrola=$_SESSION['kontrola_reloadu'].' * '.$_SESSION['kategorie'];
// *****************************************************************************
// jen kvuli testovani na locale
// *****************************************************************************



define('MAX_TXT_1',80); // max. pocet znaku v kratkem popisu produktu
define('MAX_TXT_2',100); // max. pocet znaku v novinkach

// *****************************************************************************
// CV : Vraci string se spravnym tvarem kus/kusy/kusu
// *****************************************************************************
function strkusy($ks) {

  if($ks == 1) $kus = "kus";
  else if($ks < 5) $kus = "kusy";
  else $kus = "kusů";
  return $kus; 


}


// *****************************************************************************
// skryte produkty, vyrobci, kategorie - ty nesmi byt zobrazeny ve verejne casti
// *****************************************************************************

// VYROBCI
// id name hidden lang
function skryti_vyrobci() {

	$query = "SELECT id FROM ".T_PRODS." 
	WHERE hidden = 1 AND ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		$x .= "id_vyrobce != ".$z['id']." OR ";
	
	}
	
	
	if(!empty($x)) $x = substr($x,0,-4);
	else $x='';
	
	return $x;

}
// VYROBCI

//trideni vyrobců
//trideni vyrobců
function trideni_vyrobcu($omezeni, $reload) {
    //unset($_SESSION['omezit_vyrobce']);
    $query='SELECT name FROM '.T_PRODS.' WHERE '.SQL_C_LANG.' '.$omezeni.' ORDER BY name ';
    $sql= my_DB_QUERY($query,__LINE__,__FILE__);
    $pocet=0;
    $trideni_vyrobce='';
    $pocet=mysql_num_rows($sql);
    if($pocet>1) {
    while($z=mysql_fetch_array($sql)) {
      $vyrobce=$z['name'];
      
      $vyrobce_nazev = $vyrobce;  // nazev vyrobce neprelozeny
      
      $trans_vyrobce = array(" " => "_", "." => "-");  // preklad problematickych znaku v nazvu vyrobce
      $vyrobce = strtr($vyrobce, $trans_vyrobce);
				
      if (isset($_SESSION['where_'.$vyrobce]) AND $_SESSION['where_'.$vyrobce] == 'on') {
        $t_hodnota='off';
        switch(TRIDENI_VYROBCE){
        	case 1: {
			$vyrobce_checked[$vyrobce] = ' checked="checked"';   
        		break;
        		}
        	case 2: {
			$vyrobce_checked[$vyrobce] = ' selected="selected"';		   	
		   	break;
		}
		default: $vyrobce_checked[$vyrobce] = ' checked="checked"';
        }
      }else {                                                                
        $vyrobce_checked[$vyrobce]='';
        $t_hodnota='on';
      }
      
      
	switch(TRIDENI_VYROBCE){
     	case 1: {
			$trideni_vyrobce.='<label><input type="checkbox" name="'.$vyrobce.'" onclick="order_shop(\''.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;where_'.$vyrobce.'='.$t_hodnota.'&amp;znacky=on\')" value="" '.$vyrobce_checked[$vyrobce].'/> <span>'.$vyrobce_nazev.'</span></label>';
			break;     	
     	}
     	case 2: {
     	     $trideni_vyrobce.='<option name="'.$vyrobce.'" value="'.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;where_'.$vyrobce.'='.$t_hodnota.'&amp;znacky=on" '.$vyrobce_checked[$vyrobce].'>'.$vyrobce.'</option>';
     	     break;
     	}     
		default: $trideni_vyrobce.='<label><input type="checkbox" name="'.$vyrobce.'" onclick="order_shop(\''.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;where_'.$vyrobce.'='.$t_hodnota.'&amp;znacky=on\')" value="" '.$vyrobce_checked[$vyrobce].'/> <span>'.$vyrobce.'</span></label>';;     	
     }                                      
                        
    }
    




	switch(TRIDENI_VYROBCE){
     	case 1: {
			$trideni_vyrobce='
	          <div class="tridit">                                                                                       
	          <form method="post" action="">                                                             
	            <div>
	              <span class="nadpis">Výběr výrobce:</span>
	            </div>
	            <div class="znacky">
	              '.$trideni_vyrobce.'
	            </div>
	          </form>
	        </div>
	        <div class="clear">
		   </div>';
			break;     	
     	}
     	case 2: {
	     	$trideni_vyrobce='
	     	<div class="tridit">
	          <form method="post" action="">
			  <div>
	              <span>Výběr výrobce:</span>
			    <select id="vyrobciselect" name="vyrobce" onchange="order_shop(this.value)">
			    	<option value="'.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;znacky=on">Všechny</option>'.$trideni_vyrobce.'
			    </select>
	            </div>
	          </form>
			</div>';
     	     break;
     	}     
		default: $trideni_vyrobce.='<label><input type="checkbox" name="'.$vyrobce.'" onclick="order_shop(\''.HTTP_ROOT.'?go='.$_GET['go'].'&amp;reload='.$reload.'&amp;where_'.$vyrobce.'='.$t_hodnota.'&amp;znacky=on\')" value="" '.$vyrobce_checked[$vyrobce].'/> <span>'.$vyrobce.'</span></label>';;     	
     }       
    

     
    }


    return   $trideni_vyrobce;

}



// KATEGORIE
// id name hidden descr lang products id_parent position
function skryte_kategorie() {

	global $ch_cat;
	
	$query = "SELECT id FROM ".T_CATEGORIES." 
	WHERE hidden = 1 AND ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
	
		children_in_category($z['id'],$ch_cat); // mame vnorene kategorie
		
		if(!empty($ch_cat)) {
		
			reset($ch_cat);
			while ($pH = each($ch_cat)) {
			
				// ID kategorii ktere nezahrnujeme do zobrazeni na strance - jsou skryte
				$hidden_list[$pH['value']] = $pH['value'];
			
			}
		
		}
	
	}
	
	
	// where pro tab. T_GOODS_X_CATEGORIES
	// id_good id_cat lang 
	$x='';
	if(!empty($hidden_list)) {
	  
		reset($hidden_list);
		while ($p = each($hidden_list)) {
		
			$x .= "id_cat != ".$p['value']." OR ";
		
		}
	
	}
	
	
	
	if(!empty($x)) $x = substr($x,0,-4);
	
	return $x;

}
// KATEGORIE




// PRODUKTY
// id id_cat name img text hidden akce cena dph lang kod id_vyrobce
function skryte_produkty() {

	

}
// PRODUKTY




define('SQL_HIDDEN_VYROBCI', skryti_vyrobci());
define('SQL_HIDDEN_KATEGORIE', skryte_kategorie());
unset($ch_cat);
// *****************************************************************************
// skryte produkty, vyrobci, kategorie
// *****************************************************************************









// *****************************************************************************
// kategorie shopu - od ID nahoru
// *****************************************************************************
function parent_categories($id_parent) {//,$akt_page

	// projde kategorie od zadaneho ID nahoru, zobrazi seznam nadrazenych kategorii
	// slouzi jako zpetny ukazatel cesty napr: Home > E-Shop > Bile zbozi
	
	global $parents; // kompletni cesta
	global $parents2; // id nadrazenych kategorii
	
	
	if(ZALOZKY){
		$and=' and id_parent!=0';	
	}else{
		$and='';
	}
	
	
	$query = "SELECT id, id_parent, name FROM ".T_CATEGORIES." 
	WHERE id = $id_parent $and AND ".SQL_C_LANG." AND hidden = 0 ";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$parents2[$z['id']] = $z['id'];
		
		if($z['id'] != $_GET['kategorie'] && empty($_GET['produkt'])){
			$parents = "<a href=\"".HTTP_ROOT."/".$z['id']."-".text_in_url($z['name'])."/\" title=\"Zařazeno v kategorii - ".uvozovky($z['name'])."\">".$z['name']."</a> ".' / '.$parents;
		}else if(!empty($_GET['produkt'])){
			$parents = "<a href=\"".HTTP_ROOT."/".$z['id']."-".text_in_url($z['name'])."/\" title=\"Zařazeno v kategorii - ".uvozovky($z['name'])."\">".$z['name']."</a> ".' / '.$parents;
		}else{
			$parents = "<strong>".$z['name']."</strong> ".$parents;
		}

		parent_categories($z['id_parent']);//,$akt_page
	
	}

}
// *****************************************************************************
// kategorie shopu - od ID nahoru
// *****************************************************************************









// *****************************************************************************
// kategorie shopu - od ID dolu
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
	WHERE id_parent = $id AND ".SQL_C_LANG."";// AND hidden = 0
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		children_in_category($z['id'],$ch_cat);
	}

}
// *****************************************************************************
// kategorie shopu - od ID dolu
// *****************************************************************************









/*
// *****************************************************************************
// skryte kategorie od ID dolu
// *****************************************************************************
function hidden_in_category($id,$h_cat) {

	// vyuziti napr. k zamezeni zarazeni kategorie shopu sama do 
	// sebe, sama sebe do sobe podrizene kategorie atd ...
	// projde kategorie od zadaneho ID dolu, vyhleda vsechny 
	// podrizene urovne a ulozi do pole pro dalsi zpracovani
	// v poradi od hornich urovni smerem dolu
	global $h_cat;
	
	$h_cat[] = $id;
	
	$query = "SELECT id FROM ".T_CATEGORIES." 
	WHERE id_parent = $id AND ".SQL_C_LANG." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
		hidden_in_category($z['id'],$h_cat);
	}

}
// *****************************************************************************
// skryte kategorie od ID dolu
// *****************************************************************************
*/








// *****************************************************************************
// good box v pravem sloupci
// *****************************************************************************
function good_box_right($z)
{
	$good_box=null;
	$start_cena = $cena = $z['cena'];
  if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $start_cena = $cena = $z['cena_eshop'];
	$id=$z['id'];
	$dop_cena=$z['dop_cena'];
	$novinka=$z['novinka'];
	$dph=$z['dph'];
	$url =HTTP_ROOT.'/produkt/'.$z['id'].'-'.text_in_url($z['name']).'/';	
	$name=$z['name'].' '.$z['kod'].'';	
	$nameh2=$z['name'].'<br /><span>'.$z['kod'].'</span>';
	$catimg=null;
	$cati=0;
	$idimg=$id;
	$img = $z['img'];
	
  /*
	if(empty($img))
  { // Obrázek převezmu z kategorie.
		$catimg=getCatImg($id);
		$cati=1;
		$idimg=$catimg['id'];
		$img=$catimg['img'];
	}
  */
	

  // generujeme ceny - fce vraci pole s ruznymi tvary cen
	$ceny = ceny2($cena, $dph, $pocet = 1, $z["id_vyrobce"], $z["id"]);

	
	$proc=0;
	$sleva=0;
	$orig_cena=null;
	
	if($dop_cena > $ceny[3])
  {
  	$proc = round(((100 * $ceny[3]) / $dop_cena) );
  	
  	if(!SDPH)
    {
  		$dop_cena=$dop_cena/(100+$dph)*100;
  	}
	    	
	  $orig_cena='<span class="price old">Původní cena: '.number_format($dop_cena,2,","," ").' Kč</span>';
		$sleva=100-$proc;
	}
	
	if($proc==0 AND $cena > $ceny[3])
  {
		$proc = round(((100 * $ceny[3]) / $cena) );
		$sleva=100-$proc;
	}
	
	$info_img='';
	if($novinka==1)
  {
		$info_img='
			<div class="info"><img src="/img/novinka.png" alt="Novinka v nabídce" /></div>
		';
	}
	
	if($sleva>0)
  {
		$info_img = '
		<span class="sleva">'.$sleva.'%</span>
		';	
	}	
	

	$co_ted = '
			<a href="'.$url.'" title="Další informace o '.$name.'" class="detail" >detail</a>
			<a rel="nofollow" href="'.HTTP_ROOT.'?go=basket&amp;addId='.$z['id'].'" title="Přidat do košíku '.$name.'" class="basket">do košíku</a>';

		
// 	if (empty($img))
//   {
// 		$nahled ='<img alt="'.$name.'" src="/img/nfoto.jpg" />';
// 	}
//   else
//   {
// 		$img1 ='/image.php?file='.$idimg.'&amp;e='.$img.'&amp;size=small&amp;cati='.$cati;
// 		$nahled='<img alt="'.$name.'" src="'.$img1.'" />';
// 	}
	
	
	$img_array = get_product_fotos($id);

    if($img_array != 0)
    {
		  $img_small = $img_array[0]['name'];
          // zjistime priponu
          $x1 = explode ('.' , $img_small); // roztrhame nazev souboru - delicem je tecka
          $x2 = count($x1) - 1; // index posledniho prvku pole
          $e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
          $pripona = strtolower($e); // pripona souboru (typ)
		
      		// titulek
    	  	if(!empty($img_array[0]['title']))
      		{
            $img_title = uvozovky($img_array[0]['title']);
            }

            $img1 ='/image_new.php?file='.$img_small.'&amp;e='.$pripona.'&amp;size=small&amp;cati='.$cati;
		}
		else
		{ // obrazek nenalezen

		  $img1 ='/img/nfoto.jpg';
    }
	$nahled='<img alt="'.$name.'" src="'.$img1.'" />';
	
	

	if(!empty($z['anotace']))
  {
		$anotace = '<p>'.lenght_of_string(MAX_TXT_1,$z['anotace'],'').'</p>';
	}
  else
  {
  $anotace='';
	}

  // Eura.
  $cena_eura = kc_na_eura($ceny[1]); // Přepočetna eura.
  $ceny_eura = ceny2($cena_eura, $dph, $pocet = 1);

  if(SDPH){
		$cena = $ceny[30];
    $cena_eura = $ceny_eura[30];
    $cena_bez_DPH = $ceny[10];
    $cena_eura_bez_DPH = $ceny_eura[10];
	}else{
		$cena = $ceny[10];
    $cena_eura = $ceny_eura[10];
	}

	return '
	<div class="goodbox">
		<h2>
      <a href="'.$url.'" title="'.$name.'">'.$nameh2.'</a>
    </h2>

		<div class="image">
     <a href="'.$url.'" title="'.$name.'">'.$nahled.$info_img.'</a>
    </div>

		<div class="price">
      <span>bez DPH:</span>
      <div class="cena big">
        '.$cena_bez_DPH.' Kč<br />
        '.$cena_eura_bez_DPH.' &euro;
      </div>
    </div>

		<div class="price">
      <span>s DPH:</span>
      <div class="cena">
        '.$cena.' Kč<br />
        '.$cena_eura.' &euro;
      </div>
    </div>
		
    <div class="buttons">
			'.$co_ted.'
		</div>
	</div>
	';	
}
// *****************************************************************************
// good box v pravem sloupci
// *****************************************************************************









// *****************************************************************************
// produkty na strance - vypis produktu kategorie, akcni nabidka
// *****************************************************************************
function good_box($z) {
	$id = null;
	$name = null;
	$id_vyrobce = null;
	$dph = null;
	$start_cena = null;
	$img = null;
	$dop_cena = null;
	$kod = null;
	$novinka = null;
	$doporucujeme = null;           
	$akce = null;
	$prednost = null;

	$id = $z['id'];
	$name = $nameh2 = $z['name'];//." //".$z['id_vyrobce']." "
	$id_vyrobce = $z['id_vyrobce'];
	$dph = $z['dph'];
	$start_cena = $cena = $z['cena'];
  if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $start_cena = $cena = $z['cena_eshop'];
	$dop_cena = $z['dop_cena'];
	$kod = $z['kod'];
	$novinka = $z['novinka'];
	$doporucujeme = $z['doporucujeme'];
	$akce = $z['akce'];
//	$prednost = $z['prednost'];
	
	$name = $name.' '.$kod.'';
	$nameh2 = '<span class="name">'.$nameh2.'</span><span class="kod">'.$kod.'</span>';
	
	$catimg=null;
	$cati=0;
	$idimg=$id;
	$img = $z['img'];

  /*
	if(empty($img))
  { // Obrázek převezmu z kategorie.
		$catimg=getCatImg($id);
		$cati=1;
		$idimg=$catimg['id'];
		$img=$catimg['img'];
	}	
  */

  if($z["id_dodani"] == 13)
  {
    $dodani = '<div class="dodani skladem">skladem</div>';
  }
  else if($z["id_dodani"] == 12)
  {
    $dodani = '<div class="dodani dotaz">na dotaz</div>';
  }
  else
  {
    $dodani = '<div class="dodani">&nbsp;</div>';
  }
	
  if(!empty($_GET['kategorie']))$cat='?kategorie='.$_GET['kategorie'];
  else $cat='';
  
	$url = HTTP_ROOT.'/produkt/'.$id.'-'.text_in_url($name).'/'.$cat;   

  // Krátký popis
  $anotace = "";
	if(!empty($z['anotace']))
  {
		$anotace = '
    <div class="note">
      <div class="note_text">
        '.nl2br($z['anotace']).'
      </div>
      <a class="button" href="'.$url.'">detail</a>
    </div>
    ';
	}
	
// 	if (empty($img)) {
// 		$img1 ='/img/nfoto.jpg';
// 	}else{
// 		$img1 ='/image.php?file='.$idimg.'&amp;e='.$img.'&amp;size=small&amp;cati='.$cati;
// 	}
		
	$title2 = uvozovky($name);
	$next_params = "";
	//$img_array = get_product_fotos($id); - omezíme počet dotazů do db ... už v datech posíláme první foto
	
	if(!empty($img)) {
    $img_array[0]['name'] = $img;
	$img_array[0]['title'] = "";
    }
    
    if($img_array != 0)
    {
		  $img_small = $img_array[0]['name'];
          // zjistime priponu
          $x1 = explode ('.' , $img_small); // roztrhame nazev souboru - delicem je tecka
          $x2 = count($x1) - 1; // index posledniho prvku pole
          $e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
          $pripona = strtolower($e); // pripona souboru (typ)
		
      		// titulek
    	  	if(!empty($img_array[0]['title']))
      		{
            $img_title = uvozovky($img_array[0]['title']);
            }

            $img1 ='/image_new.php?file='.$img_small.'&amp;e='.$pripona.'&amp;size=small&amp;cati='.$cati;
		}
		else
		{ // obrazek nenalezen

		  $img1 ='/img/nfoto.jpg';
        }
        
	$nahled='<img alt="'.$name.'" src="'.$img1.'" />';
		

	// generujeme ceny - fce vraci pole s ruznymi tvary cen
  $ceny = ceny2($cena, $dph, $pocet = 1, $id_vyrobce, $id);
  $dop_ceny = ceny2($dop_cena,$dph,$pocet=1);

	
	$proc=0;
	$sleva=0;
	$orig_cena=null;
	
	if($dop_ceny[3] > $ceny[3])
  {
	  $proc = round(((100 * $ceny[3]) / $dop_ceny[3]) );

    // Eura.
    $dop_cena = kc_na_eura($dop_ceny[1]); // Přepočetna eura.
    $dop_ceny = ceny2($dop_cena, $dph, $pocet = 1);
    	
	  $orig_cena='<span class="price old">Původní cena: '.$dop_ceny[30].' Kč / '.$dop_ceny[30].'</span>';
		$sleva=100-$proc;
	}
	
  $beznaCena = ceny2($cena, $dph, $pocet = 1, NULL, $id , $se_slevou = FALSE);
	if($proc==0 AND $beznaCena[3] > $ceny[3])
  {
		$proc = round(((100 * $ceny[3]) / $beznaCena[3]));
		$sleva=100-$proc;
	}
	
	
	
	$info_img='';
	
	if($novinka==1){
		$info_img='
			<div class="info"><img src="/img/novinka.png" alt="Novinka v nabídce" /></div>
		';
	}
	
	if($sleva>0){
		$info_img = '
			<span class="sleva">'.$sleva.'%</span>
		';	
	}
	
	
	
	
	$basket_alt = "Přidat do košíku ". uvozovky($name)."";
	$info_alt = "Další informace o ". uvozovky($name)."";

	$url = HTTP_ROOT.'/produkt/'.$id.'-'.text_in_url($name).'/'.$cat;

    if($z["id_dodani"] == 13)
    {
        $co_ted = '
    	<a href="'.$url.'" title="'.$info_alt.'" class="detail" >detail</a>
    	<a rel="nofollow" href="'.HTTP_ROOT.'?go=basket&amp;addId='.$id.'" title="'.$basket_alt.'" class="basket"><span>do košíku</span></a>
      ';
    }
    else 
    {
    	$co_ted = '
    	<a href="'.$url.'" title="'.$info_alt.'" class="detail" >detail</a>
    	
      ';
    }
	$nahled = '<a href="'.$url.'" title="'.$info_alt.'">'.$nahled.'</a>';
	
	$good_box = '
	<div class="box">	               
   	<h2>
      <a href="'.$url.'" title="'.$info_alt.'">'.$nameh2.'</a>
    </h2>

    '.$anotace.'

   	<div class="image">
   		'.$nahled.'
   		'.$info_img.'             		
   	</div>
         	
   	<div class="compare_form">
      <form method="post" action="">
        <div>
        <input type="hidden" name="addPorovnat" value="'.$id.'" />
        <input type="image" name="porovnat" alt="Porovnat" src="/img/compare.png" title="Porovnat" />
        </div>										
      </form>
   	</div>

    '.$dodani.'

    <!--
    <div class="orig_price">
		  '.$orig_cena.'
	  </div>
    -->
	';

  // Eura.
  $cena_eura = kc_na_eura($ceny[1]); // Přepočetna eura.
  $ceny_eura = ceny2($cena_eura, $dph, $pocet = 1);

  if(SDPH){
		$cena = $ceny[30];
    $cena_eura = $ceny_eura[30];
    $cena_bez_DPH = $ceny[10];
    $cena_eura_bez_DPH = $ceny_eura[10];
	}else{
		$cena = $ceny[10];
    $cena_eura = $ceny_eura[10];
	}	
						
  $good_box .= '
	<div class="price VAT">
	  <span class="text">bez DPH:</span>
    <span class="cena">
      <span class="kc">'.$cena_bez_DPH.' Kč</span>
      <span class="euro">'.$cena_eura_bez_DPH.' &euro;</span>
    </span>
    <div class="clear"> </div>
  </div>
      
	<div class="price">
	  <span class="text">s DPH:</span>
    <span class="cena">
      <span class="kc">'.$cena.' Kč</span>
      <span class="euro">'.$cena_eura.' &euro;</span>
    </span>
    <div class="clear"> </div>
  </div>
  ';

	$good_box .= '<div class="buttons">'.$co_ted.'</div>';						
	$good_box .= '</div>';
	
	
	return $good_box;
}
// *****************************************************************************
// produkty na strance - kategorie, akcni nabidka
// *****************************************************************************









// ***************************************************************************
// strankovani
// ***************************************************************************
function records_limit() {

	// pracuje spolecne se strankovanim, urcuje pocet zobrazenych zaznamu
	
	if(empty($_GET['p'])) $p = 1;
	else $p = $_GET['p'];
	
	$limit = "LIMIT ".($p - 1) * $_SESSION['products_on_page'].",".$_SESSION['products_on_page']."";
	
	return $limit;

}


function strankovani($count_records,$link) 
{
	$pages='';
	
	if(isset($_SESSION['search']))$moje_search='?sWord='.urlencode($_SESSION['search']); 
	else $moje_search='';
	
	if(isset($_GET['vyrobce']))$vyrobce = $_GET['vyrobce']."/";
	else $vyrobce="";
	
	if($count_records > $_SESSION['products_on_page']) 
  {	
		if(empty($_GET['p'])) $p = 1;
		else $p = $_GET['p'];
		
		$count_pages = ceil($count_records / $_SESSION['products_on_page']);

    $offset_left = 5;
    $offset_right = 5;
    $prvni_str = 1;
    $posledni_str = 1;
    
    if(($p - $offset_left) <= 1)
    {
      $offset_left = $offset_left - ($offset_left - $p + 1);
      $prvni_str = 0;
    }

    if(($p + $offset_right) >= $count_pages)
    {
      $offset_right = $offset_right - (($p + $offset_right) - $count_pages);
      $posledni_str = 0;
    }
    
		for ($x = $p - $offset_left; $x <= $p + $offset_right; $x++) 
    {
			if($p == $x) $pages .= "<span class=\"pages_active\">$x</span>";
			else if($x == 1) $pages .= '<a href="'.$link.$vyrobce.$moje_search.'" >'.$x.'</a>';//THIS_PAGE
			else $pages .= "<a href=\"".$link."".$vyrobce."strana-$x/$moje_search\">$x</a>";//THIS_PAGE
		}

// 		$pages = substr($pages, 0, -1);
	}
	
	if(isset($prvni_str) AND $prvni_str)
	{
     $pages = '<a href="'.$link.$vyrobce.$moje_search.'" >1</a><span>...</span>'.$pages;
  }

	if(isset($posledni_str) AND $posledni_str)
	{
     $pages = $pages."<span>...</span><a href=\"".$link."".$vyrobce."strana-$count_pages/$moje_search\">$count_pages</a>";
  }
	
	if(!empty($pages)) $pages='<div class="strankovani">'.$pages.'</div>';
	else $pages='';
	
	return $pages;
}
// ***************************************************************************
// strankovani
// ***************************************************************************




// *****************************************************************************
// boxy s produktem umistime do tabulky - vypis v kategorii a akcni nabidce
// *****************************************************************************
function good_box_in_table($v,$count_records)
{
  $data = null;

  while($z = mysql_fetch_assoc($v))
  {
  	$data .= good_box($z);
  }

  global $pages;
  $pages = strankovani($count_records,$link=HTTP_ROOT."/".$_SESSION['kategorie']."/");
  if ($_GET['go']=="akcni-nabidka") $pages = strankovani($count_records,$link=HTTP_ROOT."/akcni-nabidka/".$_GET['akce']."-".$_GET['nazevakce']."/");
  if ($_GET['go']=="doporucujeme") $pages = strankovani($count_records,$link=HTTP_ROOT."/doporucujeme/");

  return $data;
}
// *****************************************************************************
// boxy s produktem umistime do tabulky - vypis v kategorii a akcni nabidce
// *****************************************************************************




// *****************************************************************************
// generujeme tag obrazku
// *****************************************************************************
function imgtag($img,$width,$height,$border,$title,$next_params,$timestamp) {

	// vygeneruje tag obrazku se zakladnimi parametry, vsechny ostatni 
	// je mozno v pripade potreby umistit do $next_params
	if(file_exists($img)) {
	
		@$rozmery = getimagesize($img);
		
		if(empty($width)) $width = $rozmery[0];
		if(empty($height)) $height = $rozmery[1];
		
		if(!($border > 0)) $border = "0";
		
		// slouzi pro pripadne zajisteni refreshe obrazku, v nekterych situacich 
		// nejsou obrazky natazeny vzdy korektne
		
		// generujeme "unikatni" timestamp pri kazdem pouziti fce
		if($timestamp == -1) $timestamp = $timestamp = time().microtime();
		
		if(!empty($timestamp)) $timestamp = "?t=$timestamp";
		
		return "
		<img src=\"$img".$timestamp."\" width=\"$width\" height=\"$height\" 
		title=\"$title\" alt=\"$title\" border=\"$border\" $next_params />";
	
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
		
		$next_params .= " style=\"cursor: pointer;\"";
		$next_params .= " onclick=\"window.open('/show.php?i=$img2','','resizable=0,scrollbars=0,top=0,left=0,menubar=0,width=".$rozmery2[0].",height=".$rozmery2[1]."');\"";
		
		$title = "".$title." - zvětšit";//.$rozmery2[0]."x".$rozmery2[1]." px, ".file_size($img2)
	
	}
	
	
	return imgtag($img1,$width,$height,$border,$title,$next_params,$timestamp);

}
// *****************************************************************************
// nahledy obrazku, s odkazem na velky obrazek (pokud existuje)
// *****************************************************************************






// *****************************************************************************
// kategorie shopu - vypis menu
// *****************************************************************************

function over_podrizene($id,$level_vybrane_kategorie){
	$vypsat=0;

	$q2 = "SELECT COUNT(id_good) 
	FROM ".T_GOODS_X_CATEGORIES.", ".T_GOODS." 
	WHERE ".T_GOODS_X_CATEGORIES.".id_cat = ".$id." 
	AND ".T_GOODS_X_CATEGORIES.".id_good = ".T_GOODS.".id 
	AND ".T_GOODS.".hidden = 0";//position, name GROUP BY id_parent 
	$v2 = my_DB_QUERY($q2,__LINE__,__FILE__);
	
	if(mysql_result($v2, 0, 0) > 0){
		$vypsat=1;
	}else{
		$q3 = "SELECT id, name, id_parent 
		FROM ".T_CATEGORIES." 
		WHERE ".SQL_C_LANG." 
		AND id_parent = ".$id."
		AND hidden = 0 
		ORDER BY position";//position, name GROUP BY id_parent 
    		$v3 = my_DB_QUERY($q3,__LINE__,__FILE__);
      	while ($z2 = mysql_fetch_array($v3)) {
	          $vypsat2=over_podrizene($z2['id'],$level_vybrane_kategorie);
	          if ($vypsat2==1) {
		          $vypsat=1;
		          break;
	          }
      	}
	}
	
	if(SHOWING_CATEGORIES){
		$query = "SELECT name,descr FROM ".T_CATEGORIES."
		WHERE id = ".$id;

		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$row=mysql_fetch_array($v);

		if(strlen($row['name'])>0 && strlen(strip_tags($row['descr']))>0){
			$vypsat=1;
		}
	}			

	return $vypsat;
}


function posloupnost_k_obsahu($id_parent) {
  if(empty($urovni))$urovni=0;
  $maximum_urovni=100; // ochrana proti zacykleni
  do {
    $dotaz_na_id_parent = "SELECT id,id_parent FROM ".T_CATEGORIES."  WHERE ".SQL_C_LANG." AND hidden=0 AND id=$id_parent LIMIT 1"; //info o prvku
    $nacti_id_parent = mysql_query($dotaz_na_id_parent);
    if(mysql_num_rows($nacti_id_parent)>0) {
        $id_parent = MySQL_Fetch_Array($nacti_id_parent);
        $id = $id_parent["id"];
        $id_parent = $id_parent["id_parent"]; //nadrazeny prvek,neboli id_parent
    }
    else $id = 0;
    if ($id > 0) $stromecek[]=$id; // pridame prvek do stromu
    $urovni++;
  }
  while ( ($id_parent != 0) and ($urovni < $maximum_urovni));
  //to neni nejvyssi prvek, coz znamena ze id_parent by byl roven nule
  //anebo jsme se nezacyklili, muzeme pokracovat

  $stromecek[]=0; //pridame na konec nulty prvek, id_parent vsech id_parent :-)
  return array_reverse($stromecek); //a otocime
}


function css($velikost,$id_p) {
  global $rozbaleni;

  if(ZALOZKY){
  	$velikost=($velikost*1)-1;
  }      
  
  $css=' class="level'.$velikost;
  if((isset($_GET['kategorie']) && $_GET['kategorie']==$id_p) OR  $rozbaleni==1) $css = $css."_selected";
  return $css.'"';
}



function vypis($kdo,$stromecek) { //potrebujem strom, ktery urci cestu k aktualni kategorii
  global $rozbaleni;
  global $vetev;
  
  if(empty($vypis))$vypis='';

  if ( count($stromecek)==($kdo+1) ) {//je to posledni prvek, tedy budeme jej rozbalovat a skoncime
    $posledni = count($stromecek)-1;
    $dotaz_na_polozku[$kdo] = "SELECT id , name , menu_name , id , id_parent FROM `fla_shop_kategorie` WHERE hidden=0 AND id_parent=$stromecek[$posledni] ORDER BY position";
  }
  else {
    $dotaz_na_polozku[$kdo] = "SELECT id , name , menu_name , id , id_parent FROM `fla_shop_kategorie` WHERE hidden=0 AND id_parent=$stromecek[$kdo] ORDER BY position";
    }
  // bezny prvek, vypiseme vsechny prvky z teto urovne
  $nacti_polozku[$kdo] = mysql_query($dotaz_na_polozku[$kdo]);
  while ($polozka[$kdo] = MySQL_Fetch_Array($nacti_polozku[$kdo])) {
    $nazev_p = $polozka[$kdo]["name"];
    $nazev_menu = $polozka[$kdo]["menu_name"];
    $id_p = $polozka[$kdo]["id"];
    $id_parent_p = $polozka[$kdo]["id_parent"];
    
    //zjistíme zda budeme vypisovat, pokud ano, pokračujeme
    //$vypsat=over_podrizene($id_p,$level_vybrane_kategorie=$kdo);
    $vypsat = 1;
    if($vypsat==1)
    {
      
      //zjištění zda rozbalujeme menu - nutné pro function css
      reset($stromecek);
      $rozbaleni=0;
      while(list($key,$val)=each($stromecek)) {
        if( $val == $id_p) $rozbaleni=1;
      }
      $styl = css($kdo,$id_p);
    
      //vložení mezery po výpisu menu level2
//       if($_SESSION['predchozi_kdo'] > $kdo AND count($stromecek)>=3  AND $_SESSION['mezera']==0) {
//         $vetev .= '<br />';
//         $vypis .= '<br />';
//         $_SESSION['mezera']=1;
//       }
    
    	 if(strlen($nazev_menu)>0)$nadpis=$nazev_menu;
    	 else $nadpis=$nazev_p;
      
    
    
    	 if(strpos($styl,'level1'))$odrazka='- ';
    	 else $odrazka='';
    
      //skládání menu
      if (strlen($nazev_p)>0) $vypis .= '<a '.$styl.' href="'.HTTP_ROOT.'/'.$id_p.'-'.text_in_url($nazev_p).'/" title="'. uvozovky($nazev_p).'">'.$nadpis.'</a>';
		
		  //vytvoření kopie rozbalené vetve, pro pozdejší vyjmutí z menu a zařazení na jeho začátek
      if($kdo==0 AND $rozbaleni==1) {
        $vetev = '<a '.$styl.' href="'.HTTP_ROOT.'/'.$id_p.'-'.text_in_url($nazev_p).'/" title="'. uvozovky($nazev_p).'">'.$nadpis.'</a>';
      }
      if($kdo>0) $vetev .= '<a '.$styl.' href="'.HTTP_ROOT.'/'.$id_p.'-'.text_in_url($nazev_p).'/" title="'. uvozovky($nazev_p).'">'.$nadpis.'</a>';
    }
    
    //vnoření  			
    if (isset($stromecek[$kdo+1]) && (count($stromecek)>=($kdo+1)) && ($stromecek[$kdo+1]==$id_p)) $vypis.=vypis($kdo+1,$stromecek);
    // pokud nejsme na konci stromu AND
    // zrovna tento prvek je uzel, ktery se tyka prvku, ktery vykreslujeme
    // potom posuneme se o prvek dal a vykreslime dalsi cast stromu
    // je to rekurze, a tudiz se vratime zpet a dokreslime zbytek
  }
  $_SESSION['predchozi_kdo']=$kdo;

  return $vypis;
}

// *****************************************************************************
// kategorie shopu - vypis menu
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
// odesilani emailu
// *****************************************************************************
function send($to,$message,$subject,$from='') {

	$headers = "MIME-Version: 1.0\n";
	
	
	//$headers .= "Content-type: text/html; charset=windows-1250\n";
	$headers .= "Content-type: text/html; charset=utf-8\n";
	// $headers .= "Content-Type: text/html; charset=iso-8859-2\n";
	
	// $headers .= "To: $to <$to>\r\n";
	if(empty($from))$headers .= "From: ".S_WEB." <".S_MAIL_SHOP.">\n";
	else $headers .= "From: ".$from."\n";
	
	
	// vyhazeme diakritiku z predmetu - nektere servery jinak oznaci zpravu jako spam
	$subject = strtr($subject, "áäčďéěëíňóöřą»úůüýľÁÄČĎÉĚËÍŇÓÖŘ©«ÚŮÜÝ®", "aacdeeeinoorstuuuyzAACDEEEINOORSTUUUYZ");
	
	// převedeme na text iso-8859-2
	// $emailBody = strtr($message, "ĽĽ¦©«¬®ąľ¶Ąą»Ľľ", "ĄĽ¦©«¬®ąľ¶·ą»Ľľ");
	
	$odeslano=@mail($to,$subject,$message,$headers);
	
	$pos = strpos($subject, 'KOPIE');
  $reg = strpos($subject, 'registrace');
  
	if($pos === false AND $reg === false AND S_MAIL_SHOP != $to) 
  {
		$odeslano=@mail('monitor@netaction.cz','KOPIE - '.$subject,$message,$headers);
	}
	
	
	return $odeslano;
}     

function send_ladeni($to,$message,$subject) {

	$headers .= "MIME-Version: 1.0\n";
	
	
	//$headers .= "Content-type: text/html; charset=windows-1250\n";
	$headers .= "Content-type: text/html; charset=utf-8\n";
	// $headers .= "Content-Type: text/html; charset=iso-8859-2\n";
	
	// $headers .= "To: $to <$to>\r\n";
	$headers .= "From: ".S_WEB." <".S_MAIL_SHOP.">\n";
	
	
	// vyhazeme diakritiku z predmetu - nektere servery jinak oznaci zpravu jako spam
	$subject = strtr($subject, "áäčďéěëíňóöřą»úůüýľÁÄČĎÉĚËÍŇÓÖŘ©«ÚŮÜÝ®", "aacdeeeinoorstuuuyzAACDEEEINOORSTUUUYZ");
	
	// převedeme na text iso-8859-2
	// $emailBody = strtr($message, "ĽĽ¦©«¬®ąľ¶Ąą»Ľľ", "ĄĽ¦©«¬®ąľ¶·ą»Ľľ");
	
	$odeslano=@mail($to,$subject,$message,$headers);
  return $odeslano;
}

function send2($to,$message,$subject) {

	$headers = "MIME-Version: 1.0\n";
	
	
	//$headers .= "Content-type: text/html; charset=windows-1250\n";
	$headers .= "Content-type: text/html; charset=utf-8\n";
	// $headers .= "Content-Type: text/html; charset=iso-8859-2\n";
	
	// $headers .= "To: $to <$to>\r\n";
	$headers .= "From: ".S_WEB." <".S_MAIL_SHOP.">\n";

$prevodni_tabulka = Array(
  'ä'=>'a',
  'Ä'=>'A',
  'á'=>'a',
  'Á'=>'A',
  'à'=>'a',
  'À'=>'A',
  'ã'=>'a',
  'Ã'=>'A',
  'â'=>'a',
  'Â'=>'A',
  'č'=>'c',
  'Č'=>'C',
  'ć'=>'c',
  'Ć'=>'C',
  'ď'=>'d',
  'Ď'=>'D',
  'ě'=>'e',
  'Ě'=>'E',
  'é'=>'e',
  'É'=>'E',
  'ë'=>'e',
  'Ë'=>'E',
  'è'=>'e',
  'È'=>'E',
  'ê'=>'e',
  'Ê'=>'E',
  'í'=>'i',
  'Í'=>'I',
  'ï'=>'i',
  'Ï'=>'I',
  'ì'=>'i',
  'Ì'=>'I',
  'î'=>'i',
  'Î'=>'I',
  'ľ'=>'l',
  'Ľ'=>'L',
  'ĺ'=>'l',
  'Ĺ'=>'L',
  'ń'=>'n',
  'Ń'=>'N',
  'ň'=>'n',
  'Ň'=>'N',
  'ñ'=>'n',
  'Ñ'=>'N',
  'ó'=>'o',
  'Ó'=>'O',
  'ö'=>'o',
  'Ö'=>'O',
  'ô'=>'o',
  'Ô'=>'O',
  'ò'=>'o',
  'Ò'=>'O',
  'õ'=>'o',
  'Õ'=>'O',
  'ő'=>'o',
  'Ő'=>'O',
  'ř'=>'r',
  'Ř'=>'R',
  'ŕ'=>'r',
  'Ŕ'=>'R',
  'š'=>'s',
  'Š'=>'S',
  'ś'=>'s',
  'Ś'=>'S',
  'ť'=>'t',
  'Ť'=>'T',
  'ú'=>'u',
  'Ú'=>'U',
  'ů'=>'u',
  'Ů'=>'U',
  'ü'=>'u',
  'Ü'=>'U',
  'ù'=>'u',
  'Ù'=>'U',
  'ũ'=>'u',
  'Ũ'=>'U',
  'û'=>'u',
  'Û'=>'U',
  'ý'=>'y',
  'Ý'=>'Y',
  'ž'=>'z',
  'Ž'=>'Z',
  'ź'=>'z',
  'Ź'=>'Z'
);

$subject = strtr($subject, $prevodni_tabulka);
$message = strtr($message, $prevodni_tabulka);
	
	// vyhazeme diakritiku z predmetu - nektere servery jinak oznaci zpravu jako spam
	//$subject = strtr($subject, "šžáäčďéěëíňóöřą»úůüýľŠŽÁÄČĎÉĚËÍŇÓÖŘ©«ÚŮÜÝ®Ťť", "szaacdeeeinoorstuuuyzSZAACDEEEINOORSTUUUYZTt");
	
  //$message = strtr($message, "šžáäčďéěëíňóöřą»úůüýľŠŽÁÄČĎÉĚËÍŇÓÖŘ©«ÚŮÜÝ®Ťť", "szaacdeeeinoorstuuuyzSZAACDEEEINOORSTUUUYZTt");
	
	// převedeme na text iso-8859-2
	// $emailBody = strtr($message, "ĽĽ¦©«¬®ąľ¶Ąą»Ľľ", "ĄĽ¦©«¬®ąľ¶·ą»Ľľ");
	
	@mail($to,$subject,$message,$headers);
	@mail('monitor@netaction.cz',$subject,$message,$headers);
	//@mail('stepanek.stanislav@centrum.cz',$subject,$message,$headers);
}


// *****************************************************************************
// odesilani emailu
// *****************************************************************************







// *****************************************************************************
// uprava textu
// *****************************************************************************


// *****************************************************************************
// zkraceni retezce
// *****************************************************************************
function lenght_of_string($max,$text,$link) {

	// orizne retezec za mezerou pred max. pocet znaku
	
	if (strlen($text) > $max) {
	 
		$text = substr($text,0,$max); // orizeneme na max pocet
		$pos = strrpos($text," "); // najdeme posledni mezeru ve zbytku textu
		$text = substr($text,0,$pos)." ...".$link; // odrizneme k posledni mezere
	
	}
	
	return $text;

}
// *****************************************************************************
// zkraceni retezce
// *****************************************************************************





// *****************************************************************************
// prevod na mala / velka pismena
// *****************************************************************************
// nektere servery neprevedou znaky s diakritikou beznymi strtoupper/strtolower
function strtoL($text)
{
 //$text = strtolower($text);
 //$text = strtr($text, "ĚŠČŘŽÝÁÍÉŤĎŇÚŮ", "ěščřžýáíéťďňúů");
 
 $text = mb_strtolower($text, 'UTF-8'); // zvladne i ceske znaky

	return $text;
}


function strtoU($text)
{
	//$text = strtoupper($text);
	//$text = strtr($in, "ěščřžýáíéťďňúů", "ĚŠČŘŽÝÁÍÉŤĎŇÚŮ");
	
	$text = mb_strtoupper($text , 'UTF-8'); // zvladne i ceske znaky
	
	return $text;
}




// *****************************************************************************
// uprava textu pro URL adresu
// *****************************************************************************
function text_in_url($t) {
  
  $t = diakritika($t);
	
	
	$t = znaky_do_latinky($t); // viz _dictionary.php
	
	
	$trade = array(' '=>'-','$'=>'-','@'=>'-','!'=>'-',
                 '#'=>'-','%'=>'-',
                 '^'=>'-','&'=>'-','*'=>'-',
                 '('=>'-',')'=>'-',
                 '+'=>'-','='=>'-',
                 '\\'=>'-','|'=>'-',
                 '`'=>'-','~'=>'-','/'=>'-',
                 '\"'=>'-','\''=>'-',
                 '<'=>'-','>'=>'-','?'=>'-',
                 ','=>'-', 

                 'ą'=>'a', 	'ć'=>'c', 	'ę'=>'e', 	'ł'=>'l', 	'ń'=>'n', 	'ó'=>'o', 	'ś'=>'s', 	'ź'=>'z', 	'ż'=>'z',
                 'Ą'=>'A', 	'Ć'=>'c', 	'Ę'=>'e', 	'Ł'=>'l', 	'Ń'=>'n', 	'Ó'=>'o', 	'Ś'=>'s', 	'Ź'=>'z', 	'Ż'=>'z',                 
              
                 'Ľ'=>'L', 	'ľ'=>'l',
                 
                 );

  $t = strtoL($t);               
	// OK
  $t = strtr($t,$trade);

  $t = preg_replace('~[^-a-z0-9_]+~', '', $t);
  $t = preg_replace('~[-]+~', '-', $t);
	$t = str_replace("-+", "-", $t);

// 	urlencode($t);
	
	return $t;

}

function uvozovky($text) {
  	$text = str_replace('"', '&quot;', $text);
    return $text;
}

function diakritika($t) {

	// vyhaze diakritiku z textu
	$tr = array('ą'=>'a','ä'=>'a','á'=>'a','Ą'=>'a','Ä'=>'a','Á'=>'a',
                 'ć'=>'c','Ć'=>'c','č'=>'c','Č'=>'c',
								 'ď'=>'d','Ď'=>'d',
								 'é'=>'e','ě'=>'e','ë'=>'e','É'=>'e','Ě'=>'e','Ë'=>'e',
								 'í'=>'i','Í'=>'i',
								 'ň'=>'n','Ň'=>'n',
								 'ó'=>'o','Ó'=>'o','ö'=>'o','Ö'=>'o',
								 'ř'=>'r','Ř'=>'r',
								 'š'=>'s','Š'=>'s','ß'=>'ss','ß'=>'ss',
								 'ť'=>'t','Ť'=>'t',
								 'ú'=>'u','Ú'=>'u','ů'=>'u','Ů'=>'u','ü'=>'u','Ü'=>'u',
								 'ý'=>'y','Ý'=>'y',
								 'ž'=>'z','Ž'=>'z');
	
	$t = strtr($t,$tr);
	
	return $t;

}

function znaky_do_latinky($t) {

	// nejdrive na mala pismena
// 	$t = strtolower($t);
	
	// prevede azbuku na ekvivalenty latinky
	$tr = array (
	
		'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ж'=>'z','з'=>'e',
		'и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p',
		'р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'ch','ц'=>'c','ч'=>'c',
		'ш'=>'s','щ'=>'sc','ъ'=>'','ы'=>'y','ь'=>'','э'=>'z','ю'=>'ju','я'=>'ja',
		
		'A'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ж'=>'z','З'=>'e',
		'И'=>'i','Й'=>'j','К'=>'k','Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p',
		'Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f','Х'=>'ch','Ц'=>'c','Ч'=>'c',
		'Ш'=>'s','Щ'=>'sc','Ъ'=>'','Ы'=>'y','Ь'=>'','Э'=>'z','Ю'=>'ju','Я'=>'ja',
		
		'Ñ'=>'n','ñ'=>'n','¡'=>'i'
	
		);
	
	$t = strtr($t,$tr);
	
	return $t;

}

// *****************************************************************************
// uprava textu
// *****************************************************************************








// *****************************************************************************
// vyhledavani
// *****************************************************************************
function search1($search,$column,$found_points,$line,$points,$found_names,$addWhere) {

	// $search - hledany vyraz
	// $column - prohledavany sloupec tabulky
	// $found_points - pole s ulozenymi body za nalez fraze v urcitem sloupci tabulky - kazdemu
	// sloupci lze priradit jinou vahu vyjadrenou prave poctem bodu $points
	// $points - pocet bodu za nalez - urcuje tak prioritu/vahu
	// zjistujeme pocet vyskytu v nalezenem zaznamu


	global $found_points,$found_names,$found_vyrobce;


	// prevedeme hledanou frazi na mala pismena
	$search = strtoL($search);


	// hledame ve sloupci $column, mimo zaznamy $addWhere
	// id_good id_cat lang
	// id id_cat name img text hidden cena id_dph lang kod id_vyrobce
  $query = "SELECT id AS id_good, name, id_vyrobce, ".$column."
	FROM ".T_GOODS."
  JOIN ".T_GOODS_X_CATEGORIES." ON ".T_GOODS.".id = ".T_GOODS_X_CATEGORIES.".id_good
	WHERE ".$column." != ''
  AND hidden = 0
  AND ".T_GOODS.".".SQL_C_LANG."
	".$addWhere."
	GROUP BY ".T_GOODS.".id
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while($z = mysql_fetch_assoc($v))
  {
		$sID = $z['id_good'];
		$c = $z[$column];
		$nazev = $z['name'];
		$id_vyrobce = $z['id_vyrobce'];

    $p = 0; // reset
    
    // kolikrat se v sloupci vyraz vyskytuje
		$p += substr_count(diakritika(strtoL($c)), diakritika($search)); // porovnavame hledane vyrazi bez diakritiky (najdeme tak vsechny shody)

		$add_points = $p * $points; // tolik pricteme bodu

		if($add_points > 0) {

			if(!empty($found_points[$sID])) {

				unset($found_names[$found_points[$sID]][$sID]);
				$found_points[$sID] = $found_points[$sID] + $add_points;

			}
			if(empty($found_points[$sID])) $found_points[$sID] = $add_points;

			$found_names[$found_points[$sID]][$sID] = $nazev;
			$found_vyrobce[$found_points[$sID]][$sID] = $id_vyrobce;
		}
	}
}



// kompletní hledání
function search2($search,$found_points,$found_names,$addWhere)
{
	global $found_points, $found_names, $found_vyrobce;

  if(is_numeric($search[0]))
  { // Pravděpodobně jde o kód, který kvůli rychlosi hledáme jen v číselném tvaru.
    $trans = array("-" => "", "." => "", " " => "");
    $search_kod = strtr($search , $trans);

    if(is_numeric($search_kod))
    { // Je to opravdu kód.
      search1($search_kod,"kod",$found_points,__LINE__,1000,$found_names,$addWhere);

      return;
    }
  }


	// zkousime najit celou zadanou frazi
	// id id_cat name img text hidden cena id_dph lang kod id_vyrobce
	search1($search,"name",$found_points,__LINE__,1000,$found_names,$addWhere);
	search1($search,"kod",$found_points,__LINE__,1000,$found_names,$addWhere);
  search1($search,"kod2",$found_points,__LINE__,1000,$found_names,$addWhere);


	// rozdelime na slova
	$slovo = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $search, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

	for($y = 0; $y < count($slovo); $y++)
  {
		if(strlen($slovo[$y]) > 2)
    { // vyrazy s poctem znaku < 3 nehledame
			search1($slovo[$y],"name",$found_points,__LINE__,1,$found_names,$addWhere);
			search1($slovo[$y],"kod",$found_points,__LINE__,1,$found_names,$addWhere);
			//search1($slovo[$y],"text",$found_points,__LINE__,1,$found_names,$addWhere);
			//search1($slovo[$y],"anotace",$found_points,__LINE__,1,$found_names,$addWhere);
		}
	}
}
// *****************************************************************************
// vyhledavani
// *****************************************************************************


function GetRealSize($file) {
        // Return size in Mb
        clearstatcache();
        $INT = 4294967295;//2147483647+2147483647+1;
        $size = filesize($file);
        $fp = fopen($file, 'r');
        fseek($fp, 0, SEEK_END);
        if (ftell($fp)==0) $size += $INT;
        fclose($fp);
        if ($size<0) $size += $INT;
        return round($size/1024/1024,2);
    }

// *****************************************************************************
// razeni produktu v eshopu
// *****************************************************************************
function razeni() {
if(!empty($_GET['order_shop'])) {
	$_SESSION['order_shop'] = $_GET['order_shop'];
}

if(!empty($_GET['smer'])) {
	$_SESSION['smer_trideni'] = $_GET['smer'];
}

if(!empty($_GET['products_on_page'])) {
	$_SESSION['products_on_page'] = $_GET['products_on_page'];
}
}

function razeni2() {
if(!empty($_GET['order_shop'])) {
	$_SESSION['order_shop'] = $_GET['order_shop'];
	Header("Location: ".$_GET['reload']."");                                    
	exit;
}

if(!empty($_GET['smer'])) {
	$_SESSION['smer_trideni'] = $_GET['smer'];
	Header("Location: ".$_GET['reload']."");                                    
	exit;
}

if(!empty($_GET['products_on_page'])) {
	$_SESSION['products_on_page'] = $_GET['products_on_page'];
	Header("Location: ".$_GET['reload']."");
	exit;
}
}



//funkce pro pripojene fotogalerie
function pripojene_fotogalerie($ID){ 
    $q = "SELECT  ".T_FOTO_KATEG.".id as cat_id, ".T_FOTO_KATEG.".name as cat_name, ".T_FOTO_CONT_PAGES.".id_kateg, ".T_FOTO.".id, ".T_FOTO.".name, ".T_FOTO.".img  
          FROM ".T_FOTO_CONT_PAGES.",".T_FOTO_KATEG.",".T_FOTO."   
          WHERE ".T_FOTO_CONT_PAGES.".id_page = ".$ID." 
          AND ".T_FOTO_KATEG.".id = ".T_FOTO_CONT_PAGES.".id_kateg
          AND ".T_FOTO_KATEG.".hidden =0
          AND ".T_FOTO.".id_kateg = ".T_FOTO_KATEG.".id
          AND ".T_FOTO_KATEG.".".SQL_C_LANG."  ORDER BY ".T_FOTO_KATEG.".position, ".T_FOTO.".pos  ";
    
    $v = my_DB_QUERY($q,__LINE__,__FILE__);
    
    $obr_galerie2='';
    
	while ($zvg = mysql_fetch_array($v)) {
		if(empty($zvg['name'])){
			$name=$zvg['cat_name'];
			$foto_name='';
		}else{
			$name = $foto_name = $zvg['name'];
		}
		$foto_name=lenght_of_string(35,$foto_name,' ');
		$img1  = IMG_F_S2.$zvg['id'].'.'.$zvg['img'];
		$img2  = IMG_F_O2.$zvg['id'].'.'.$zvg['img'];
		$soubor_fs = IMG_F_O2.$zvg['id'].'.'.$zvg['img'];

		if(file_exists($soubor_fs)) {
			$obr_galerie2.= '<a href="'.$img2.'" title="'.$name.'" rel="prettyPhoto[r]" ><span class="obrazek"><img alt="'.$name.'" src="'.$img1.'" /></span><span>'.$foto_name.'</span></a>';
		}
      }
      if($obr_galerie2!='') $obr_galerie2='
								<div class="fotogalerie">'.$obr_galerie2.'</div>
								<div class="clear">
								</div>';
    
    return $obr_galerie2;
}



function kontrola_mailu($adresa) {
	if (!preg_match('~^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$~',$adresa)){
    		$_SESSION['error'] .= "<strong>E-mail</strong> je chybně vyplněn<br />";
	}
}




// ****************************************************************************
//  zarazeni produktu
// ****************************************************************************
function zarazeniProduktu($id)
{
  global $cat_info;
  $query="SELECT id, name, id_parent FROM  ".T_GOODS_X_CATEGORIES." gc
          INNER JOIN ".T_CATEGORIES." c on gc.id_cat=c.id
          WHERE gc.id_good=$id and c.hidden=0 and c.lang=".C_LANG;
  $v = my_DB_QUERY($query,__LINE__,__FILE__);

  $zarazeno='';
  while($row = mysql_fetch_assoc($v))
  {
   	$image=' / ';
   
    $maincategory="<a href='".HTTP_ROOT."/".$row['id']."-".text_in_url($row['name'])."/' title=\"Zařazeno v kategorii - ".uvozovky($row['name'])."\">".$row['name']."</a>";
      
    $zarazeno.="<a href=\"".HTTP_ROOT."\">Domů</a> $image ".getParents($row['id_parent'],$image)."".$maincategory."<br />";
  }   
  
  if(!empty($zarazeno))
  {
    $zarazeno="<h2>Produkt ###nadpis### je zařazen v kategoriích:</h2>
  						 <p class='zarazeni'>".$zarazeno."</p>";
  }
  else $zarazeno="";
   
  return $zarazeno;
}



function getParents($id_parent,$image,$drobecky="")
{
  global $cat_info;

	$query = "
  SELECT id, id_parent, name, nezobrazovat_ks
  FROM ".T_CATEGORIES."
	WHERE id = '".intval($id_parent)."'
  AND ".SQL_C_LANG."
  AND hidden = 0
  ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while($z = mysql_fetch_assoc($v))
  {
		$next = "<a href='".HTTP_ROOT."/".$z['id']."-".text_in_url($z['name'])."/' title=\"Zařazeno v kategorii - ".uvozovky($z['name'])."\">".$z['name']."</a> $image ";

		if($z['id_parent']>0){ 
			return getParents($z['id_parent'],$image,$next.$drobecky);
		}elseif(ZALOZKY){
			return $drobecky;
		}else{
			return $next.$drobecky;
		}
	}
}
// ****************************************************************************
// zarazeni produktu
// ****************************************************************************










// ****************************************************************************
//  drobeckova navigace
// ****************************************************************************
function getNavigation($id,$switch,$image){
	
	if(empty($id) && empty($switch)){
		return null;
	}else{
		
		switch($switch){
		
		     case 'akce':{
			          return $image.getAkceURL($id);
					break;
					}
			case 'clanek':{
			          return $image.getClanekURL($id);
					break;
					}
			case 'kategorie':{
					return getCategoriesURL($id,$image);
					break;			
					}
			case 'doporucujeme':{
					return $image.'<a href="/doporucujeme/" title="Doporučujeme">Doporučujeme</a>';
					break;			
					}
			case 'basket':{
					return $image.'<a href="/nakupni-kosik/" title="Nákupní košík">Nákupní košík</a>';
					break;			
					}															
												
		}	
	
	}
	
	return null;
} 



function getCategoriesURL($id,$image,$drobecky=''){

	$query = "SELECT id, id_parent, name FROM ".T_CATEGORIES." 
	WHERE id = $id and ".SQL_C_LANG." AND hidden = 0 ";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {

		$next = "$image <a href='".HTTP_ROOT."/".$z['id']."-".text_in_url($z['name'])."/' title=\"Zařazeno v kategorii - ".uvozovky($z['name'])."\">".$z['name']."</a>";
		
	
		if($z['id_parent']>0){ 
			return getCategoriesURL($z['id_parent'],$image,$next.$drobecky);
		}elseif(ZALOZKY){
			return $drobecky;
		}else{
			return $next.$drobecky;
		}
	}
	
	return $drobecky;	
}



function getAkceURL($id){
	$query = "SELECT name FROM ".T_AKCE." WHERE id=".$id;
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$z = mysql_fetch_array($v);
	
	$name=$z['name'];
	
	return '<a href="/akcni-nabidka/'.$id.'-'.text_in_url($name).'/" title="'.$name.'">'.$name.'</a>';
}



function getClanekURL($id){
	$query = "SELECT title FROM ".T_CONT_PAGES." WHERE id=".$id;
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$z = mysql_fetch_array($v);
	
	$name=$z['title'];
	
	return '<a href="/clanek/'.$id.'-'.text_in_url($name).'/" title="'.$name.'">'.$name.'</a>';
}
// ****************************************************************************
//  // drobeckova navigace
// ****************************************************************************











// ****************************************************************************
//  dotaznik
// ****************************************************************************
function formDOTAZNIK($nadpis)
{
  $ANTISPAM = 5;
  
  $datumForm = date('d-m-Y H:i'); // aktualni datum a cas
  $stranka = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'';//$_SERVER['SCRIPT_NAME']

  if(empty($_POST['dotaz']) || $_POST['kontrola'] != $ANTISPAM)
  {
      if(!empty($_POST['jmeno']))$jmeno=$_POST['jmeno'];
      else $jmeno='';
      if(!empty($_POST['email']))$email=$_POST['email'];
      else $email='';
      if(!empty($_POST['phone']))$phone=$_POST['phone'];
      else $phone='';
      if(!empty($_POST['text']))$text=$_POST['text'];
      else $text='';

      if(!empty($_POST['dotaz']) && $_POST['kontrola'] != $ANTISPAM)
      {
        $_SESSION['alert_js1']='Vyplňte správně antispamovou ochranu, nic nebylo odesláno!';
      }

      return '
        <script type="text/javascript"> 
        //<![CDATA[
        function isEmailAddress(EmailAddress) {
          var mail=/^.+@.+\..{2,4}$/
          return (mail.test(EmailAddress));
        }
        
        
       function objednavkaValid(formular) {
       
        if (formular.email.value=="" || !isEmailAddress(formular.email.value)) {
           window.alert("Prosíme uveďte správně Váš email.");
           formular.email.focus();
           return false;
        } else if (formular.phone.value=="") {
          window.alert("Prosíme uveďte Váš telefon.");
          formular.phone.focus();
          return false;
        } else if (formular.text.value=="") {
          window.alert("Prosíme napište Váš dotaz.");
          formular.text.focus();
          return false;
        } else return window.confirm("Váš dotaz bude odeslán.");
       
       }
      // ]]> 
      </script> 
       
        <a id="formular">
	   </a>
        <form action="" method="post" class="usertableform" onsubmit="return objednavkaValid(this);">
        
        
          <table class="usertable"> 
          
          <tr>
		  <th colspan="2">'.$nadpis.'</th>          
          </tr>
          
          <tr> 
            <td class="wL"></td> 
            <td class="wP">Položky označené <span class="red">*</span> jsou povinné.</td> 
          </tr> 
          
          <tr> 
            <td class="wL"> Váše jméno: </td> 
            <td class="wP"><input type="text" name="jmeno" value="'.$jmeno.'"/></td> 
          </tr> 
       
          <tr> 
            <td class="wL"><span class="red">*</span> Váš email: </td> 
            <td class="wP"><input type="text" name="email" value="'.$email.'" /></td> 
          </tr> 
       
          <tr> 
            <td class="wL"><span class="red">*</span> Váš telefon: </td> 
            <td class="wP"><input type="text" name="phone" value="'.$phone.'"  /></td> 
          </tr> 
          
          <tr> 
            <td class="wL"><span class="red">*</span> Váš dotaz: </td> 
            <td class="wP"><textarea name="text" rows="" cols="" >'.$text.'</textarea></td> 
          </tr> 
          
          <tr> 
            <td></td><td>Antispamová ochrana - prosíme vyplňte výsledek (číslo) </td>    
          </tr> 
          
          <tr> 
            <td class="wL"><span class="red">*</span>dvě plus tři je : </td> 
            <td class="wP"><input id="spam" type="text" name="kontrola" value=""/></td> 
          </tr> 

          <tr>
            <td class="wL"></td>
            <td class="wP" style="vertical-align:middle;"><input class="newsletter_add" type="checkbox" value="1" name="newsletter_add" checked="checked" /> <label for="newsletter_add">Přihlásit k odběru novinek</label></td>
          </tr>
          
          <tr> 
            <td class="wL"></td> 
            <td class="wP"><input type="submit" value="Odeslat" name="dotaz" class="submitDotaz" /><br /><br /></td> 
          </tr> 
          
          </table> 
          
        <div>  
        <input type="hidden" name="zestranky" value="'.$stranka.'" /> 
        <input type="hidden" name="jazyk" value="cs" /> 
        </div> 
        </form> 
	   <div class="clear">
	   </div>       
	';
  }else{   
      $dotazC = time();
      
      $jmeno = strip_tags(trim($_POST['jmeno']));
      $phone = strip_tags(trim($_POST['phone']));
      $email = strip_tags(trim($_POST['email']));
      $text = strip_tags(trim($_POST['text']));
      
      $zestranky = strip_tags($_POST['zestranky']);
      
      $predmet = "Nekvinda-obchod.cz - dotaz";
      
      $text = strip_tags($text);   
                                                                                                                                                                                
      $referer=$_COOKIE[REFERER_COOKIE_NAME];
      
      $query="insert into ".T_DOTAZY."(datetime,jmeno,email,telefon,dotaz,zestranky,referer) values(now(),'$jmeno','$email','$phone','$text','$zestranky','$referer')";
      $v=my_DB_QUERY($query,__LINE__,__FILE__);
      
      
      // zprava pro admina + prijemce dotazu
      $message = "   
      Jméno: $jmeno<br />
      Telefon: $phone<br />
      E-mail: <a href=\"mailto:$email\">$email</a><br /><br />
      
      $text<br /><br />
      
      datum odeslání: $datumForm<br />
      odesláno ze stránky: <a href=\"$zestranky\">$zestranky</a><br />
      ";
      
       
      
      $message = messageHtml($message);
      
      
	 send(S_MAIL_SHOP,$message,$predmet,$email);

      
      
      // zprava pro odesilatele
      if($email != '') {
        
        $message = "
        Jméno: $jmeno<br />
        Telefon: $phone<br />
        E-mail: <a href=\"mailto:$email\">$email</a><br /><br />
        
        $text<br /><br /><br />
        
        
        *************************************<br /><br />
            
        Toto je kopie dotazu, který jste odeslal(a) dne $datumForm ze stránky <a href=\"$zestranky\">$zestranky</a>.<br /> 
        Váš dotaz zpracujeme a budeme Vás kontaktovat.<br /><br /> 
        
        Děkujeme za Váš zájem a těšíme se na spolupráci.<br /><br />

        ".PODPIS_DOTAZ."
<br />
<br />
        ".EMAIL_REKLAMA_NETACTION;
        
        
        
        $message = messageHtml($message);
        
        send($email,$message,"KOPIE - ".$predmet);

        // pridani do odberu novinek
        if(isset($_POST["newsletter_add"]) AND $_POST["newsletter_add"] == 1)
        { // pridani do odberu novinek
          include_once("./newsletter_functions.php");

          delete_from_blacklist_newsletter($email); // pokud je na blacklistu tak email z blacklistu odstranime
        }
        else
        { // nepridavat do odberu novinek
          include_once("./newsletter_functions.php");

          blacklist_newsletter($email); // pridame email na blacklist
        }
      
      }
      return '<div  class="m20"><h2>Váš dotaz byl úspěšně odeslán.</h2><p>Děkujeme za Váš dotaz, odpověď dostanete na uvedený e-mail.</p></div>';   
  }
}

function messageHtml($mess) {

  $mess = "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
	
	<STYLE>
		body, a, TD {font-size: 11px; color: #000000; font-family: Verdana, 'Arial CE', 'Helvetica CE', Arial, Helvetica, sans-serif;}
		.f11 {font-size: 11px;}
		.f12 {font-size: 12px;}
		.f13 {font-size: 13px;}
		.box1 {border: 1px solid #000000; padding: 7px;}
		.box2 {padding: 7px;}
		.box3 {border: 2px solid #000000; padding: 7px; font-size: 13px;}
	</STYLE>
	
</head>
<body>
".$mess."
</body>
</html>";
  
  return $mess;

}
// ****************************************************************************
//  // dotaznik 
// ****************************************************************************







// ****************************************************************************
//  zalozky
// ****************************************************************************

function getSuperParents($aktual){
	$activeSuperParent=getSuperParent($aktual);
	$_SESSION['lastSuperParentId']=$activeSuperParent['id'];
// 	print_r($activeSuperParent);
	$superParents=null;
	
	$query='select id,name,menu_name from '.T_CATEGORIES.' where hidden=0 and id_parent=0 and '.SQL_C_LANG;

	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while($z=mysql_fetch_array($v)){
		if($z['id']==$activeSuperParent['id']){
			$css=' class="selected"';	
		}else{
			$css='';
		}
		if(empty($z['menu_name']))$name=$z['name'];
		else $name=$z['menu_name'];
		
		$superParents.='<a'.$css.' href="/'.$z['id'].'-'.text_in_url($z['name']).'/">'.$name.'</a><span class="spacer">&nbsp;</span>';
	}
	
	if(!empty($superParents))$superParents=substr($superParents,0,-34);
	
	return $superParents;
}


function getSuperParent($id_cat){
  $query="select id,id_parent from ".T_CATEGORIES." where id=".$id_cat;

  $v=mysql_query($query);
  $row=mysql_fetch_array($v);

  if($row['id_parent']==0){
    return $row;            
  }elseif($row['id_parent']<0){
    return false;
  }else{
    return getSuperParent($row['id_parent']);
  }
}

// ****************************************************************************
//  // zalozky
// ****************************************************************************





function isSuperParent($id){
	$parent=null;
	
	$query='select id_parent from '.T_CATEGORIES.' where id='.$id;
	$v=mysql_query($query);
	$z=mysql_fetch_array($v);
	
	if(mysql_num_rows($v)>0){
		if ($z['id_parent']!=0) return false;
		else return true; 
	}
	
	return true;
}







function getParent($id){
	$parent=null;
	
	$query='select id_parent from '.T_CATEGORIES.' where id='.$id;
	$v=mysql_query($query);
	$z=mysql_fetch_array($v);
	
	if(mysql_num_rows($v)>0){
		$query='select id from '.T_CATEGORIES.' where id='.$z['id_parent'];
		$v=mysql_query($query);	
		if(mysql_num_rows($v)>0){
			$z=mysql_fetch_array($v);
			return $z['id']; 
		}
	}	
	
	return false;
}




function parametry($ID,$formatovani) {
	
      $ID = intval($ID);
			$nasel=0;
			$query = "SELECT ".T_PARAMETRY4.".id AS idPxK, ".T_PARAMETRY4.".id_karta AS ListID, 
			".T_GOODS.".name, ".T_GOODS.".cena, ".T_GOODS.".dph, ".T_GOODS.".id_vyrobce, ".T_GOODS.".dop_cena 
			FROM ".T_GOODS.", ".T_PARAMETRY1.", ".T_PARAMETRY4." 
			WHERE ".T_PARAMETRY4.".id_produkt = ".$ID." 
			AND ".T_PARAMETRY1.".id = ".T_PARAMETRY4.".id_karta 
			AND ".T_GOODS.".id = ".$ID." 
			LIMIT 0,1";

			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			while ($z = mysql_fetch_array($v)) {
			
				$idPxK = $z['idPxK'];
				$ListID = $z['ListID'];
				$name = $z['name'];
				$cena = $z['cena'];
				$dph = $z['dph'];
				$id_vyrobce = $z['id_vyrobce'];
				$dop_cena = $z['dop_cena'];
			  	$nasel=1;
			}
			
			
			if(!empty($idPxK)) {	
				$hodnoty2=null;
			
				// hodnoty parametru pro produkt
				$query = "SELECT id_parametr, hodnota, img FROM ".T_PARAMETRY3." WHERE id_kp = $idPxK";
				$v = my_DB_QUERY($query,__LINE__,__FILE__);
				while ($z = mysql_fetch_array($v)) {
				
					$hodnoty[$z['id_parametr']] = $z['hodnota'];
					
					if(!empty($z['img'])) {
					
						$img1 = IMG_I_S.$z['img'];
						$img2 = IMG_I_O.$z['img'];
						
						// $symbol = imgTag($img1,$width,$height,$border,$title,$next_params,$timestamp).'<br />';
						$symbol = showimg($img1,$img2,$width,$height,$border,'Detail',$next_params,$timestamp).'<br />';
						$symboly[$z['id_parametr']] = showimg($img1,$img2,$width,$height,$border,'Detail',$next_params,$timestamp).'';
					
					} else $symbol = '';
					
					$hodnoty2 .= '<td class="skupina_td1 f10" align="center">'.$symbol.''.$z['hodnota'].'</td>';
				
				}
				
				
				if(!empty($hodnoty2) && $formatovani == 2) { // parametry vyrobku s cenami a moznosti objednani kazdeho zvlast
				
					$ceny = ceny2($cena, $dph, $pocet = 1, $id_vyrobce, $ID);
					$proc=0;
					if($dop_cena > $ceny[3]){
	  					$beznaCena = ceny3($dop_cena,$dph,$pocet=1);
	  					$beznaCenaText = '<span class="f10">Běžná cena: '.$beznaCena[31].' Kč</span>';
	  					$proc = round((100 * $ceny[3]) / $dop_cena);
	  					$procenta='<span class="procenta_text">SLEVA '.(100-$proc).'%</span>';
                         }
                         
			          if($proc==0 AND $cena > $ceny[3]){
	  					$beznaCena = ceny3($cena,$dph,$pocet=1);
	  					$beznaCenaText = '<span class="f10">Internetová cena: '.$beznaCena[31].' Kč</span>';
	  					$proc = round((100 * $ceny[3]) / $cena);
	  					$procenta='<span class="procenta_text">SLEVA '.(100-$proc).'%</span>';
			          }
          
          
					
					$hodnoty2 = '
					<tr>
						'.$hodnoty2.'
					</tr>';
				}
				
				if(!empty($hodnoty2) && $formatovani == 3) { // parametry vyrobku bez cen a bez moznosti objednani kazdeho zvlast
					$hodnoty2 = '
					<tr>
						'.$hodnoty2.'
					</tr>';
				}
				
				// hodnoty
				// id  id_karta  nazev  jednotka  poradi
				$query = "SELECT id, nazev, jednotka, poradi 
				FROM ".T_PARAMETRY2." WHERE id_karta = ".$ListID." ORDER BY poradi";
				$v = my_DB_QUERY($query,__LINE__,__FILE__);
       
       			$parametry1='';
				$parametry2='';
       
				while ($z = mysql_fetch_array($v)) {
				
					$idParametr = $z['id'];
					$parametr = $z['nazev'];
					
					if(!empty($z['jednotka'])) $jednotka = $z['jednotka'];
					else $jednotka = '';
					
					$poradi = $z['poradi'];
					
					if(empty($symboly[$idParametr]))$symboly[$idParametr]=null;
					else $symboly[$idParametr]='<td>'.$symboly[$idParametr].'</td>';

					if(!empty($hodnoty[$idParametr])){
					
					$parametry1 .= '
					<tr>
						<td>'.$parametr.'</td>
						<td class="hodnota">'.$hodnoty[$idParametr].'</td>
						<td class="jednotka">'.$jednotka.'</td>
						'.$symboly[$idParametr].'
					</tr>';
					
					}
					
// 					if(!empty($jednotka)) $jednotka = ' ['.$jednotka.']';
					
					$parametry2 .= '
						<td class="skupina_td2 f10">'.$parametr.''.$jednotka.'</td>';//&nbsp;
				
				}
			
			}
			
			
			
			
			
			
			if($formatovani == 1)
      { // spolecne parametry skupiny
				if(!empty($parametry1)) $parametry = '
				<table class="tabulka">
					<tr>
						<th colspan="4">
						<h2>###nadpis### - parametry</h2>
						</th>						
					</tr>
					'.$parametry1.'
				</table>
				';
			}
			
			
			
// 			$url = ''.HTTP_ROOT.'?n='.text_in_url($name).'&amp;go=shop&amp;id='.$ID.'&amp;kategorie='.$_GET['kategorie'].'';
			
			
			if(!empty($parametry2)) $parametry2 = '
			<tr>
				'.$parametry2.'
			</tr>';
			
			
			
			
			
			if($formatovani == 2)
      { // parametry vyrobku s cenami a moznosti objednani kazdeho zvlast
					$co_ted = '
          <a rel="nofollow" href="'.HTTP_ROOT.'?go=basket&amp;addId='.$ID.'" class="p08">
            <img src="img/basket_button.gif" alt="Přidat do košíku" title="Přidat do košíku" />
          </a>
          ';

					$parametry = '
					<div class="tabulka">
						
						<table class="skupina_table2" summary="'.$name.' varianty">
						<tr>
							<td width="40%">
								<strong class="f16"><span class="f13">'.$name.'</span></strong>
							</td>
							<td align="right">
							'.$procenta.'<br />
				               <span class="cena1_f12">Naše cena bez DPH: '.$ceny[10].' Kč<br /></span>
				                '.$beznaCenaText.'
				               </td>
							<td align="right" width="28%">'.$co_ted.'</td>
						</tr>
						</table>
						
						<table class="skupina_table" summary="'.$name.' parametry">
						'.$parametry2.'
						'.$hodnoty2.'
						</table>
					
					</div>';
			
			}
			
			
			if($formatovani == 3) { // parametry vyrobku bez cen a bez moznosti objednani kazdeho zvlast
// 								<strong class="f16"><a href="'.$url.'" title="" class="f13">'.$name.'</a></strong>
			
					$parametry = '
					<div class="tabulka">
						
						<table class="skupina_table2" summary="'.$name.' parametry">
						<tr>
							<td>
								<strong class="f16"><span class="f13">'.$name.'</span></strong>
							</td>
						</tr>
						</table>
						
						<table class="skupina_table" summary="'.$name.' parametry">
						'.$parametry2.'
						'.$hodnoty2.'
						</table>
					
					</div>';
			
			}
			
			if($nasel==1) return $parametry;

	}
		

	// ***************************************************************************
	// parametry
	// ***************************************************************************
	
	
	
	
	

// *****************************************************************************
// akcni nabidka
// *****************************************************************************


function getAkcniNabidka($ANmax){
	$ANdata='';
	
	$ANpolozky=array();

	$query = 'SELECT '.T_GOODS.'.id,'.T_GOODS.'.name,'.T_GOODS.'.kod,'.T_GOODS.'.dph,'.T_GOODS.'.cena,
				'.T_GOODS.'.id_vyrobce,'.T_GOODS.'.img,'.T_GOODS.'.anotace,
				'.T_GOODS.'.dop_cena AS dop_cena, '.T_GOODS.'.novinka AS novinka
	           	FROM '.T_GOODS.','.T_GOODS_X_AKCE.' AS gxc, '.T_AKCE.' AS akce  
				WHERE '.T_GOODS.'.'.SQL_C_LANG.' AND 
	               gxc.'.SQL_C_LANG.' AND 
	               akce.id = gxc.id_cat AND
	               akce.hidden = 0 AND
	               '.T_GOODS.'.id = gxc.id_good 
	               ORDER BY '.T_GOODS.'.id '.$_SESSION['smer_trideni'].' ';  


	$v = my_DB_QUERY($query,__LINE__,__FILE__);



	while ($z = mysql_fetch_array($v)) {
		$ANpolozky[] = $z['id'];
	}
	$count_akce = count($ANpolozky);

	if($count_akce > 0) {		
		if($count_akce < $ANmax) $ANmax = $count_akce;
		
		$_SESSION['ANID']=null;
		$_SESSION['ANIDSQL']=null;
		
		$i = 0;
		$AN = akcniNabidka($i,$ANpolozky,$ANmax);

		
		if($AN['i'] < $ANmax) {
			$_SESSION['ANzobrazene']=null;
			$AN = akcniNabidka($AN['i'],$ANpolozky,$ANmax);
			$_SESSION['ANzobrazene'] = $AN['i'];
		}
	
		if(!empty($_SESSION['ANIDSQL'])) {
			$_SESSION['ANIDSQL'] = 'WHERE '.substr($_SESSION['ANIDSQL'], 0, -4);
			
			$query2 = "SELECT  id, name, kod, dph, cena, cena_eshop, dop_cena, novinka, id_vyrobce, img, anotace
			FROM ".T_GOODS." ".$_SESSION['ANIDSQL']."";
			$v2 = my_DB_QUERY($query2,__LINE__,__FILE__);
			
			$counter=0;
			while(($row=mysql_fetch_array($v2)) && $counter<$ANmax){ 			
				$ANdata .= good_box_right($row);
			}
		}
	}
	
	return $ANdata;
}

function akcniNabidka($i,$ANpolozky,$ANmax) {

	reset($ANpolozky);
	while ($pAN = each($ANpolozky)) {
	
		$n = $pAN['key'];
		$h = $pAN['value'];
		
		
		if($n >= $_SESSION['ANzobrazene']) {
		
			if($i == $ANmax) {
			
				$_SESSION['ANzobrazene'] = $n;
				break;
			
			}
			
			$_SESSION['ANID'][$h] = $h;
			$_SESSION['ANIDSQL'] .= ' id = '.$h.' OR ';
			
			
			$i++;
		}
	}
	
	$AN['i'] = $i;
	
	return $AN;

}


function isObjednavkaOfUser($id_user,$id_obj){
	$query='select id_user from '.T_ORDERS_ADDRESS.' where id='.$id_obj;
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$row=mysql_fetch_array($v);
	
	if($row['id_user']==$id_user && !empty($row['id_user']))return true;
	else return false;	
}

/* funkce podle id kategorie zjisti nevissi kategorii a vrati jeji id */
function nejvissi_kategorie($id_kategorie)
{
    $id_parent = getParent($id_kategorie);
    $id_parent_last = "";
            
    while($id_parent != false)
    {  // zjistime rodicovskou kategorii
      $id_parent_last = $id_parent;
      $id_parent = getParent($id_parent);          
    }
    
    // nastaveni rodicovske kategorie
    if($id_parent == false AND $id_parent_last == "")
    {
      $id_parent = $id_kategorie;
    }
    else
    {
      $id_parent = $id_parent_last;
    }
  
    return $id_parent;   
}


/* funkce zjisti podle id produktu kategorii do ktere produkt patri a vraci jeji id */
function zjisti_kategorii($id_produktu)
{
  $query='select id_cat from '.T_GOODS_X_CATEGORIES.' where id_good='.$id_produktu;
		
 	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_array($v);

  $id_kategorie = $z["id_cat"];

  return $id_kategorie;
}


function zakazatDopravce($zbozi,$jizVypnutiDopravci)
{
	$query='select id_cat from '.T_GOODS_X_CATEGORIES.' where id_good='.$zbozi;
		
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$arrayZakazaniDopravci=unserialize(DISABLED_DOPRAVCE);

	while($z=mysql_fetch_array($v))
  {
		if(!empty($arrayZakazaniDopravci[$z['id_cat']]))
    {
      if(is_array($arrayZakazaniDopravci[$z['id_cat']]))
      {
        foreach($arrayZakazaniDopravci[$z['id_cat']] as $value)
        {
          $jizVypnutiDopravci[$value] = $value;
        }
      }
      else
      {
			  $jizVypnutiDopravci[$arrayZakazaniDopravci[$z['id_cat']]]=$arrayZakazaniDopravci[$z['id_cat']];
      }
		}
    				
		$jizVypnutiDopravci=nadrazeneKategorieDopravci($z['id_cat'],$jizVypnutiDopravci,$arrayZakazaniDopravci); //predame id kategorie a zkontrolujeme smerem nahoru zda to neni podkategorie nektere kategorie ktera ma zakazane nektereho dopravce		
	}

	return $jizVypnutiDopravci; 		
}


function nadrazeneKategorieDopravci($id_cat,$jizVypnutiDopravci,$arrayZakazaniDopravci){
	
	$id_parent=getParent($id_cat);

	if(!empty($arrayZakazaniDopravci[$id_parent])){

      if(is_array($arrayZakazaniDopravci[$id_parent]))
      {
         foreach($arrayZakazaniDopravci[$id_parent] as $value)
         {
           $jizVypnutiDopravci[$value]=$value;
         }
      }
      else
      {
			  $jizVypnutiDopravci[$arrayZakazaniDopravci[$id_parent]]=$arrayZakazaniDopravci[$id_parent];
      }
	}
		
	while($id_parent!=0){
		$id_parent=getParent($id_parent);
		if(!empty($arrayZakazaniDopravci[$id_parent])){

      if(is_array($arrayZakazaniDopravci[$id_parent]))
      {
         foreach($arrayZakazaniDopravci[$id_parent] as $value)
         {
           $jizVypnutiDopravci[$value]=$value;
         }
      }
      else
      {
			  $jizVypnutiDopravci[$arrayZakazaniDopravci[$id_parent]]=$arrayZakazaniDopravci[$id_parent];
      }
		}			
	}

	return $jizVypnutiDopravci;
}













// *****************************************************************************
// produkty na strance v radcich - vypis produktu kategorie, akcni nabidka
// *****************************************************************************
function good_row($z)
{
	$id = null;
	$name = null;
	$id_vyrobce = null;
	$dph = null;
	$start_cena = null;
	$img = null;
	$anotace = null;
	$dop_cena = null;
	$kod = null;
	$novinka = null;
	$doporucujeme = null;           
	$akce = null;
	$prednost = null;

	$id = $z['id'];
	$name = $nameh2 = $z['name'];//." //".$z['id_vyrobce']." "
	$id_vyrobce = $z['id_vyrobce'];
	$dph = $z['dph'];
	$start_cena = $cena = $z['cena'];
  if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $start_cena = $cena = $z['cena_eshop'];
	$img = $z['img'];
	$anotace = nl2br($z['anotace']);
	$dop_cena = $z['dop_cena'];
	$kod = $z['kod'];
	$novinka = $z['novinka'];
	$doporucujeme = $z['doporucujeme'];
	$akce = $z['akce'];
//	$prednost = $z['prednost'];
	
	$name=$name.' '.$kod.'';
	$nameh2=$nameh2.' '.$kod;

	if(!empty($_GET['kategorie']))$cat='?kategorie='.$_GET['kategorie'];
	else $cat='';
  
	$url = HTTP_ROOT.'/produkt/'.$id.'-'.text_in_url($name).'/'.$cat;   

	if(!empty($anotace))
  {
		$anotace = '<p>'.lenght_of_string(MAX_TXT_1,$anotace,'').'</p>';
	}
	

	$title2 = uvozovky($name);
	$next_params = "";
		

	// generujeme ceny - fce vraci pole s ruznymi tvary cen
	$ceny = ceny2($cena, $dph, $pocet=1, $id_vyrobce, $id);
	$dop_ceny = ceny2($dop_cena,$dph,$pocet=1);

	
	$proc=0;
	$sleva=0;
	$orig_cena=null;
	
	if($dop_ceny[3] > $ceny[3])
  {
	  $proc = round(((100 * $ceny[3]) / $dop_ceny[3]) );

    // Eura.
    $dop_cena = kc_na_eura($dop_ceny[1]); // Přepočetna eura.
    $dop_ceny = ceny2($dop_cena, $dph, $pocet = 1);
      	
	  $orig_cena='<span class="price old">Původní cena: '.$dop_ceny[30].' Kč / '.$dop_ceny[30].' &euro;</span>';
		$sleva=100-$proc;
	}
	
	if($proc==0 AND $cena > $ceny[3]) {
		$proc = round(((100 * $ceny[3]) / $cena) );
		$sleva=100-$proc;
	}
	
	
	
	$info_img='';
	
	if($novinka==1){
		$info_img='
			<div class="info"><img src="/img/novinka.png" alt="Novinka v nabídce" /></div>
		';
	}
	
	if($sleva>0){
		$info_img='
			<span class="sleva">'.$sleva.'%</span>
		';	
	}
	
	
	
	
	$basket_alt = "Přidat do košíku ". uvozovky($name)."";
	$info_alt = "Další informace o ". uvozovky($name)."";

	$url = HTTP_ROOT.'/produkt/'.$id.'-'.text_in_url($name).'/'.$cat;

		$co_ted = '
							<form class="basketform" action="'.HTTP_ROOT.'?go=basket" method="post" onsubmit="return val01(this)">
							     <div>
								<input type="hidden" name="addId" value="'.$id.'" />
								<input type="text" name="addKs" class="ks" value="1" /><span>ks</span>
								<input type="submit" title="'.$basket_alt.'"  value="do košíku" class="basket" />
							     </div>
							</form>'; 

  // Eura.
  $cena_eura = kc_na_eura($ceny[1]); // Přepočetna eura.
  $ceny_eura = ceny2($cena_eura, $dph, $pocet = 1);

  if(SDPH){
		$cena = $ceny[30];
    $cena_eura = $ceny_eura[30];
    $cena_bez_DPH = $ceny[10];
    $cena_eura_bez_DPH = $ceny_eura[10];
	}else{
		$cena = $ceny[10];
    $cena_eura = $ceny_eura[10];
	}
							
  $cena = '
			<div class="price">
				<span class="text">bez DPH:</span> <span class="kc">'.$cena_bez_DPH.' Kč</span> / <span class="euro">'.$cena_eura_bez_DPH.' &euro;</span>
      </div>
      <div class="price VAT">
			  <span class="text">s DPH:</span> <span class="kc">'.$cena.' Kč</span> / <span class="euro">'.$cena_eura.' &euro;</span>
      </div>
    ';							
	
	$good_row = '
  <div class="row">
    <h2>
      <a href="'.$url.'" title="'.$info_alt.'">'.$nameh2.'</a>
    </h2>

    <!--
    <div class="note">
      '.$anotace.'
    </div>
    -->

    <div class="prices">
	    <div class="orig_price">
				'.$orig_cena.'
			</div>

			'.$cena.'
		</div>
          								
    <div class="buttons">
      '.$co_ted.'
    </div>

  	<div class="compare_form">
      <form method="post" action="">
        <div>
        <input type="hidden" name="addPorovnat" value="'.$id.'" />
        <input type="image" name="porovnat" alt="Porovnat" src="/img/compare.png" title="Porovnat" />
        </div>
      </form>
    </div>
  </div>
  ';
	
	
	return $good_row;

}
// *****************************************************************************
// produkty na strance v radcich - kategorie, akcni nabidka
// *****************************************************************************




// *****************************************************************************
// boxy s produktem umistime do tabulky - vypis v kategorii a akcni nabidce
// *****************************************************************************
function good_row_in_table($v,$count_records)
{
  $data = null;

  while ($z = mysql_fetch_assoc($v))
  {
  	$data .= good_row($z);
  }

  global $pages;
  $pages = strankovani($count_records,$link=HTTP_ROOT."/".$_SESSION['kategorie']."/");
  if ($_GET['go']=="akcni-nabidka") $pages = strankovani($count_records,$link=HTTP_ROOT."/akcni-nabidka/".$_GET['akce']."-".$_GET['nazevakce']."/");
  if ($_GET['go']=="doporucujeme") $pages = strankovani($count_records,$link=HTTP_ROOT."/doporucujeme/");

  return $data;
}
// *****************************************************************************
// boxy s produktem umistime do tabulky - vypis v kategorii a akcni nabidce
// *****************************************************************************




function getCatImg($id_good)
{
  $query="select ".T_CATEGORIES.".id,".T_CATEGORIES.".img from ".T_CATEGORIES.",".T_GOODS_X_CATEGORIES."
	where ".T_GOODS_X_CATEGORIES.".id_good=".$id_good." and ".T_CATEGORIES.".img!='' order by ".T_CATEGORIES.".img limit 0,1";
			
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	if(mysql_num_rows($v)>0) return mysql_fetch_array($v);
	else return null;			
}



function getComments($id)
{
  $ANTISPAM_COMMENT = 6;
  
  $datumForm = date('d-m-Y H:i'); // aktualni datum a cas
  $stranka = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'';//$_SERVER['SCRIPT_NAME']


  if(!empty($_POST['jmenoC']))$jmeno=$_POST['jmenoC'];
  else $jmeno='';
  if(!empty($_POST['emailC']))$email=$_POST['emailC'];
  else $email='';
  if(!empty($_POST['textC']))$text=$_POST['textC'];
  else $text='';

	
	if(empty($_POST['addComment']) || $_POST['kontrolaC'] != $ANTISPAM_COMMENT)
  {
    if(!empty($_POST['emailC']) && $_POST['kontrolaC'] != $ANTISPAM_COMMENT)
    {
      $_SESSION['alert_js1']='Vyplňte správně antispamovou ochranu, nic nebylo odesláno!';
    }

	  $prispevky='';
	     
	 	$query = "SELECT *,DATE_FORMAT(datetime,'%H:%i %d.%m.%Y') as dateform from ".T_COMMENTS." where id_produkt=".$id." and hidden=0 order by datetime";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		while($row=mysql_fetch_array($v)){
		
			if(!empty($row['jmeno']))$row['jmeno']=$row['jmeno'].',';
		
			$prispevky.='<tr><td class="dateform">'.$row['dateform'].'</td><td class="mailform">'.$row['jmeno'].' '.$row['email'].'</td></tr>
			<tr><td colspan="2" class="komentarform">'.$row['dotaz'].'</td></tr>';	
		}   
		
		if(!empty($prispevky))$prispevky='<tr><th colspan="2">Komentáře k výrobku ###nadpis###</th></tr>'.$prispevky.'<tr><td colspan="2"><br /></td></tr>';                                          
	 	else $prispevky='<tr><th colspan="2">Komentáře k výrobku ###nadpis###</th></tr><tr><td colspan="2">Nejsou zatím žádné komentáře k tomuto výrobku<br /><br /></td></tr>'; 
		
		$form='
		        <script type="text/javascript"> 
        //<![CDATA[
        function isEmailAddress(EmailAddress) {
          var mail=/^.+@.+\..{2,4}$/
          return (mail.test(EmailAddress));
        }
        
        
       function objednavkaValid(formular) {
       
        if (formular.emailC.value=="" || !isEmailAddress(formular.emailC.value)) {
           window.alert("Prosíme uveďte správně Váš email.");
           formular.emailC.focus();
           return false;
        } else if (formular.textC.value=="") {
          window.alert("Prosíme napište Váš dotaz.");
          formular.textC.focus();
          return false;
        } else return window.confirm("Váš dotaz bude odeslán.");
       
       }
      // ]]> 
      </script> 
        <a id="comments">
        </a>
        <form action="" method="post" class="usertableform" onsubmit="return objednavkaValid(this);"> 
        
        
          <table class="usertable" cellpadding="0" cellspacing="0"> 
          
          '.$prispevky.'                   
          
          <tr>
		  <th colspan="2">Přidejte komentář k ###nadpis###</th>          
          </tr>
          
          <tr> 
            <td class="wL"></td> 
            <td class="wP">Položky označené <span class="red">*</span> jsou povinné.</td> 
          </tr> 
          
          <tr> 
            <td class="wL"> Váše jméno: </td> 
            <td class="wP"><input type="text" name="jmenoC" value="'.$jmeno.'"/></td> 
          </tr> 
       
          <tr> 
            <td class="wL"><span class="red">*</span> Váš email: </td> 
            <td class="wP"><input type="text" name="emailC" value="'.$email.'" /></td> 
          </tr> 
                
          <tr> 
            <td class="wL"><span class="red">*</span> Váš dotaz: </td> 
            <td class="wP"><textarea name="textC" rows="" cols="" >'.$text.'</textarea></td> 
          </tr> 
          
          <tr> 
            <td></td><td>Antispamová ochrana - prosíme vyplňte výsledek (číslo) </td>    
          </tr> 
          
          <tr> 
            <td class="wL"><span class="red">*</span>tři plus tři je : </td> 
            <td class="wP"><input id="spamC" type="text" name="kontrolaC" value=""/></td> 
          </tr> 
          
          <tr> 
            <td class="wL"></td> 
            <td class="wP"><input type="submit" value="Odeslat" name="addComment" class="submitDotaz" /><br /><br /></td> 
          </tr> 
          
          </table> 
          
        <div>  
        <input type="hidden" name="zestranky" value="'.$stranka.'" /> 
        <input type="hidden" name="id_produkt" value="'.$id.'" /> 
        <input type="hidden" name="jazyk" value="cs" /> 
        </div> 
        </form> 
	   <div class="clear">
	   </div>       
		';
		
		return $form;
	
	}else{
	     
		$dotazC = time();
	     $zestranky = strip_tags($_POST['zestranky']);
	     $id_produkt = strip_tags($_POST['id_produkt']);
	     $predmet = "Nekvinda-obchod.cz - komentar k produktu";
	     $referer=$_COOKIE[REFERER_COOKIE_NAME];	
	
		$hash=md5($email.$dotazC);
		
		$query='insert into '.T_COMMENTS."(hidden,id_produkt,datetime,jmeno,email,dotaz,zestranky,referer,hash) values(1,$id_produkt,now(),'$jmeno','$email','$text','$zestranky','$referer','$hash')";		
		$v=my_DB_QUERY($query,__LINE__,__FILE__);
	
		$query = "SELECT LAST_INSERT_ID()";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$id = mysql_result($v, 0, 0);      
	      
	      
	      // zprava pro admina + prijemce dotazu
	      $message = "Pro schválení níže uvedeného komentáře k produktu ve Vašem e-shopu klikněte na následující odkaz:<br />
	      <a href='http://".$_SERVER['HTTP_HOST']."/?go=schvaleniprispevku&id=$id&hash=$hash'>http://".$_SERVER['HTTP_HOST']."/?go=schvaleniprispevku&id=$id&hash=$hash</a><br /><br />      
	      
	      ==============================================================================<br /><br />
	      Jméno: $jmeno<br />
	      Telefon: $phone<br />
	      E-mail: <a href=\"mailto:$email\">$email</a><br /><br />
	      
	      $text<br /><br />
	      
	      datum odeslání: $datumForm<br />
	      odesláno ze stránky: <a href=\"$zestranky\">$zestranky</a><br />
	      ";
	      
	      
	      $_SESSION['alert_js1']='Váš příspěvek byl odeslán ke schválení!';
	      
	      
	      $message = messageHtml($message);
	      
	           //
		 send(S_MAIL_SHOP,$message,$predmet,$email);		
		 
		 Header('Location: '.$_SERVER['HTTP_REFERER']);
		 exit;
			
	}
	
	
// 	$query="select ";
// 			
// 	$v=my_DB_QUERY($query,__LINE__,__FILE__);	
}






function getPorovnaniTable(){

	$zbozi=null;

	foreach($_SESSION['porovnaniVyrobku'] as $id=>$value){
			
      $id = intval($id);

			$query = "SELECT ".T_GOODS.".name, ".T_GOODS.".cena, ".T_GOODS.".dph, ".T_GOODS.".img,
			".T_PARAMETRY2.".nazev,".T_PARAMETRY2.".jednotka,".T_PARAMETRY2.".id,".T_PARAMETRY3.".hodnota 
			FROM ".T_GOODS.", ".T_PARAMETRY1.", ".T_PARAMETRY2.", ".T_PARAMETRY3.", ".T_PARAMETRY4." 
			WHERE ".T_PARAMETRY4.".id_produkt = ".T_GOODS.".id
			AND ".T_PARAMETRY4.".id = ".T_PARAMETRY3.".id_kp
			AND ".T_PARAMETRY4.".id_karta = ".T_PARAMETRY2.".id_karta
			AND ".T_PARAMETRY2.".id = ".T_PARAMETRY3.".id_parametr
			AND ".T_PARAMETRY1.".id = ".T_PARAMETRY4.".id_karta 
			AND ".T_GOODS.".id = ".$id;

			$v=my_DB_QUERY($query,__LINE__,__FILE__);				
			
			if(mysql_num_rows($v)>0){
				while($row=mysql_fetch_array($v)){
				
					$catimg=null;
					$cati=0;
					$idimg=$id;
					$img = $row['img'];
					
          /*
					if(empty($img))
          { // Obrázek převezmu z kategorie.
						$catimg=getCatImg($id);
						$cati=1;
						$idimg=$catimg['id'];
						$img=$catimg['img'];
					}	
          */
				
// 					if (empty($img)) {
// 						$nahled ='<img alt="'.$row['name'].'" src="/img/nfoto.jpg" />';
// 					}else{
// 						$img1 ='/image.php?file='.$idimg.'&amp;e='.$img.'&amp;size=small&amp;cati='.$cati;
// 						$nahled='<img alt="'.$row['name'].'" src="'.$img1.'" />';
// 					}	


                    $title2 = uvozovky($name);
                	$next_params = "";
                	$img_array = get_product_fotos($id);
                
                    if($img_array != 0)
                    {
                		  $img_small = $img_array[0]['name'];
                          // zjistime priponu
                          $x1 = explode ('.' , $img_small); // roztrhame nazev souboru - delicem je tecka
                          $x2 = count($x1) - 1; // index posledniho prvku pole
                          $e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
                          $pripona = strtolower($e); // pripona souboru (typ)
                		
                      		// titulek
                    	  	if(!empty($img_array[0]['title']))
                      		{
                            $img_title = uvozovky($img_array[0]['title']);
                            }
                
                            $img1 ='/image_new.php?file='.$img_small.'&amp;e='.$pripona.'&amp;size=small&amp;cati='.$cati;
                		}
                		else
                		{ // obrazek nenalezen
                
                		  $img1 ='/img/nfoto.jpg';
                    }
                	$nahled='<img alt="'.$name.'" src="'.$img1.'" />';
                    
                    
                    		
	
	               	if(empty($row['hodnota']))$hodnota='-';
	               	else $hodnota=$row['hodnota'].' '.$row['jednotka'];
	
	               	$params[$id][$row['id']]=$hodnota;
	               	
	               	$zbozi[$id]['popis']='<p class="name">'.$row['name']."</p><p class='image'>".$nahled."</p><p class=\"delete\"><a href='/porovnani/?del=$id'>odstranit</a></p>";
	               	
	               	$ceny=ceny2($row['cena'], $row['dph'], $pocet = 1, $vyrobce_id = NULL, $id);
					
					if(SDPH){
						$zbozi[$id]['cena']=$ceny[30];
					}else{
						$zbozi[$id]['cena']=$ceny[10];
					}
	               	
	               	$use_params[$row['id']]=$row['nazev'];
				
				}
			}else{

				$query = "SELECT ".T_GOODS.".name, ".T_GOODS.".cena, ".T_GOODS.".dph, ".T_GOODS.".img from ".T_GOODS." WHERE ".T_GOODS.".id = ".$id;
	
				$v=my_DB_QUERY($query,__LINE__,__FILE__);
				
				while($row=mysql_fetch_array($v))
        {
					$catimg=null;
					$cati=0;
					$idimg=$id;
					$img = $row['img'];
					
          /*
					if(empty($img))
          { // Obrázek převezmu z kategorie.
						$catimg=getCatImg($id);
						$cati=1;
						$idimg=$catimg['id'];
						$img=$catimg['img'];
					}
          */
				
// 					if (empty($img)) {
// 						$nahled ='<img alt="'.$row['name'].'" src="/img/nfoto.jpg" />';
// 					}else{
// 						$img1 ='/image.php?file='.$idimg.'&amp;e='.$img.'&amp;size=small&amp;cati='.$cati;
// 						$nahled='<img alt="'.$row['name'].'" src="'.$img1.'" />';
// 					}	

		            $title2 = uvozovky($name);
                	$next_params = "";
                	$img_array = get_product_fotos($id);
                
                    if($img_array != 0)
                    {
                		  $img_small = $img_array[0]['name'];
                          // zjistime priponu
                          $x1 = explode ('.' , $img_small); // roztrhame nazev souboru - delicem je tecka
                          $x2 = count($x1) - 1; // index posledniho prvku pole
                          $e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
                          $pripona = strtolower($e); // pripona souboru (typ)
                		
                      		// titulek
                    	  	if(!empty($img_array[0]['title']))
                      		{
                            $img_title = uvozovky($img_array[0]['title']);
                            }
                
                            $img1 ='/image_new.php?file='.$img_small.'&amp;e='.$pripona.'&amp;size=small&amp;cati='.$cati;
                		}
                		else
                		{ // obrazek nenalezen
                
                		  $img1 ='/img/nfoto.jpg';
                    }
                	$nahled='<img alt="'.$name.'" src="'.$img1.'" />';
	               	
	               	$zbozi[$id]['popis']='<p class="name">'.$row['name']."</p><p class='image'>".$nahled."</p><p class=\"delete\"><a href='/porovnani/?del=$id'>odstranit</a></p>";
	          
					$ceny=ceny2($row['cena'], $row['dph'], $pocet = 1, $vyrobce_id = NULL, $id);
					
					if(SDPH){
						$zbozi[$id]['cena']=$ceny[30];
					}else{
						$zbozi[$id]['cena']=$ceny[10];
					}
					
				     $params[$id]=array();
				}								
					
			}
	} 
	
	
			
			
			
			
	$head='<th style="vertical-align: middle;"><p><a href=\'/porovnani/?del=all\'>odstranit všechny produkty<br />z porovnávání</a></p></th>';
	$ceny='<td class="bolder price">CENA</td>';
	
	foreach($zbozi as $value){
		$head.='<th>'.$value['popis'].'</th>';
		$ceny.='<td class="bolder price">'.$value['cena'].'</td>';
	}
	
	
	
	$body='';
	
	foreach($use_params as $id_p=>$name_p){
		
		$body.='<tr><td class="bolder">'.$name_p.'</td>';
		
		foreach($params as $id_zbozi=>$array_p){
		
			if(isset($array_p[$id_p]))$body.='<td>'.$array_p[$id_p].'</td>';
			else $body.='<td>-</td>';
						
		}
		
		$body.='</tr>';
			
	}	

	
	
	return '<table cellspacing="1" cellpadding="0"><tr>'.$head.'</tr><tr>'.$ceny.'</tr>'.$body.'</table>';	

}






function getSubCats($id,$pole=null){

	$query='select id from fla_shop_kategorie where hidden=0 and id_parent='.$id;
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while($row=mysql_fetch_array($v)){
		$pole[$row['id']]=$row['id'];
		
		$pole=getSubCats($row['id'],$pole);		
	}
	
	return $pole;

}







function getPorovnavacProducts(){
	
	foreach($_GET['param'] as $key=>$value){
		
		if(!empty($_GET['param'][$key])){
			$param_id=$key;
			$param_value=$value;
			unset($_GET['param'][$key]);
			break;	
		}
		
	}
	
	if(empty($param_id)&&empty($param_value))return '<div class="produktyP">Vyberte alespoň jeden parametr pro vyhledávání.</div>';
	else{	
		//vybrat vse co odpovida prvnimu parametru a poslat dale na zuzeni dle dalsich parametru...
		
		$query="select ".T_GOODS.".id from ".T_GOODS.",".T_PARAMETRY3.",".T_PARAMETRY4."
			   where ".T_PARAMETRY4.".id_produkt=".T_GOODS.".id
			   and ".T_PARAMETRY3.".id_kp=".T_PARAMETRY4.".id
			   and ".T_PARAMETRY3.".id_parametr=".$param_id."
			   and ".T_PARAMETRY3.".hodnota='".$param_value."' 
			   and ".T_GOODS.".".SQL_C_LANG;
     	
		$v = my_DB_QUERY($query,__LINE__,__FILE__);

		while($row=mysql_fetch_array($v)){
			$pole[$row['id']]=$row['id'];	       //pole ID vyhovujici prvnimu zadanemu parametru
		}
		
		if(empty($pole))return '<div class="produktyP">Zadaným parametrům nevyhovuje žádný produkt.</div>';		
		else{
			foreach($_GET['param'] as $key=>$value){
				if(!empty($key) && !empty($value))$pole=zuzitVyberPorovnavacProducts($pole,$key,$value);
				
				if(empty($pole))return '<div class="produktyP">Zadaným parametrům nevyhovuje žádný produkt.</div>';	
			}
			
			$query="select * from ".T_GOODS." where ".T_GOODS.".id IN (".implode(',',$pole).")";
			
			$v = my_DB_QUERY($query,__LINE__,__FILE__);
			
			return good_box_in_table($v,mysql_num_rows($v));
		}
				   
	}
	
}





function zuzitVyberPorovnavacProducts($pole,$key,$value){

		$query="select ".T_GOODS.".id from ".T_GOODS.",".T_PARAMETRY3.",".T_PARAMETRY4."
			   where ".T_PARAMETRY4.".id_produkt=".T_GOODS.".id
			   and ".T_PARAMETRY3.".id_kp=".T_PARAMETRY4.".id
			   and ".T_PARAMETRY3.".id_parametr=".$key."
			   and ".T_PARAMETRY3.".hodnota='".$value."' 
			   and ".T_GOODS.".id IN (".implode(',',$pole).")";
     	
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$pole=null;

		while($row=mysql_fetch_array($v)){
			$pole[$row['id']]=$row['id'];	       //pole ID vyhovujici prvnimu zadanemu parametru
		}
		
		return $pole;
	
}






function getPorovnavac($cats){

	$selectboxes=null;
	$selectbox='';

 	$query='select '.T_PARAMETRY2.'.id as param_id, '.T_PARAMETRY2.'.nazev as param_nazev, '.T_PARAMETRY2.'.jednotka  as param_jednotka, '.T_PARAMETRY1.'.id as list_id, 
	 		'.T_PARAMETRY1.'.nazev as list_nazev, '.T_PARAMETRY3.'.hodnota as param_hodnota
			 from '.T_PARAMETRY1.','.T_PARAMETRY2.','.T_PARAMETRY3.','.T_PARAMETRY4.','.T_GOODS.','.T_GOODS_X_CATEGORIES.'
	 		where '.T_PARAMETRY1.'.id='.T_PARAMETRY2.'.id_karta and '.T_PARAMETRY3.'.id_kp='.T_PARAMETRY4.'.id
			and '.T_PARAMETRY4.'.id_karta='.T_PARAMETRY1.'.id and '.T_PARAMETRY3.'.id_parametr='.T_PARAMETRY2.'.id
			and '.T_PARAMETRY4.'.id_produkt='.T_GOODS.'.id and '.T_PARAMETRY2.'.hidden=1
			and '.T_GOODS.'.id='.T_GOODS_X_CATEGORIES.'.id_good and '.T_GOODS_X_CATEGORIES.'.id_cat IN('.implode(',',$cats).') 
			GROUP BY '.T_PARAMETRY3.'.id_parametr,'.T_PARAMETRY3.'.hodnota order by '.T_PARAMETRY2.'.poradi,'.T_PARAMETRY3.'.hodnota';
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while($row=mysql_fetch_array($v)){
	
		if(!empty($_GET['param'][$row['param_id']]) && $_GET['param'][$row['param_id']]==$row['param_hodnota']){
			$selected=' selected="selected"';	
			$background[$row['param_id']]=' selectedselect';
		}else{
			$selected='';
		}
	     if(!empty($row['param_hodnota'])){
			$selectboxes[$row['param_nazev'].'##'.$row['param_id']][$row['param_hodnota']]='<option '.$selected.' value="'.$row['param_hodnota'].'">'.$row['param_hodnota'].' '.$row['param_jednotka'].'</option>';
		}		
	}
	
// 	print_r($background);
	

	foreach($selectboxes as $key=>$value){
		
		list($param_nazev,$param_id)=explode('##',$key);
		
		if(isset($background[$param_id]))
		{
      $class_background = $background[$param_id];
    }
    else
    {
      $class_background = '';
    }
		
		$selectbox.='
			<div class="selectbox '.$class_background.'">
				<span class="nadpis">'.$param_nazev.'</span>	
				<select name="param['.$param_id.']">
					<option value="">nerozhoduje</option>
					'.implode($value).'			
				</select>	
			</div>
			';	
				
	}
	
	if(empty($selectbox))return null;
	else{
		return '
			<div class="nadpis">Vyhledávání ve všech pneumatikách podle parametrů:</div>
			<hr />
			<form action="/vyhledavani-parametry/" method="get">
			<div>
			'.$selectbox.'
			</div>
			<div class="clear">
			</div>
			<div class="buttondiv">
				<input type="hidden" name="sWordP" value="1" />
				<input type="submit"value="Vyhledat" />
			</div>
			</form>
		';
	}

}

?>
