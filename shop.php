<?php


// *****************************************************************************
// seznam s jmeny vyrobcu
// *****************************************************************************
$nazvy_vyrobcu[0] = "";

$query = "SELECT id , name , dodani FROM ".T_PRODS." WHERE ".SQL_C_LANG;
$v = my_DB_QUERY($query,__LINE__,__FILE__);
while($z = mysql_fetch_assoc($v))
{
  $nazvy_vyrobcu[$z['id']] = $z['name'];
  $dodani[$z['id']] = $z['dodani'];
}
// *****************************************************************************
// seznam s jmeny vyrobcu
// *****************************************************************************


$query = "SELECT id , nazev FROM ".T_DODACI_LHUTA." WHERE ".SQL_C_LANG;
$v = my_DB_QUERY($query,__LINE__,__FILE__);
while($z = mysql_fetch_assoc($v))
{
  $dodaniZbozi[$z['id']] = $z['nazev'];
}









// *****************************************************************************
// vyhledavani
// *****************************************************************************
if(isset($_GET['sWord']))
{
  $catWhere=null;
	$prodWhere=null;
  $found_points=null;
  $found_names=null;
	$search = trim($_GET['sWord']);
	$_SESSION['search'] = urldecode(trim($_GET['sWord']));


	// ***************************************************************************
	// ***************************************************************************
	// projdeme KATEGORIE a zjistime ktere jsou skryte a nebudou zahrnuty do vyhledavani
	// id name hidden descr lang products id_parent position
	$ch_cat = null;
	
	$query = "SELECT id FROM ".T_CATEGORIES." WHERE hidden = 1 AND ".SQL_C_LANG;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while ($z = mysql_fetch_assoc($v))
  {
		children_in_category($z['id'],$ch_cat);
	}

  $ANDcatWhere = "";
	if(!empty($ch_cat))
  { // mame pole s id skrytych kategorii, slozime query
    $ANDcatWhere = " AND ".T_GOODS_X_CATEGORIES.".id_cat NOT IN (".implode(", ", $ch_cat).") ";
	}
	// ***************************************************************************
	// ***************************************************************************

	
	// ***************************************************************************
	// ***************************************************************************
	// projdeme VYROBCE a zjistime kteri jsou skryte a nebudou zahrnuty do vyhledavani
	// id name hidden lang
	unset($ch_cat);
	
	$query = "SELECT id FROM ".T_PRODS." WHERE hidden = 1 AND ".SQL_C_LANG;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

	while($z = mysql_fetch_assoc($v))
  {
		$ch_cat[$z['id']] = $z['id'];
	}
	
  $ANDprodWhere = "";
	if(!empty($ch_cat))
  { // mame pole s id skrytych produktu, slozime query
		$ANDprodWhere = " AND id_vyrobce NOT IN (".implode(", ", $ch_cat).") ";
	}
	// ***************************************************************************
	// ***************************************************************************
	
	
	// pridame ke query uvnitr fce
	$addWhere = $ANDcatWhere.$ANDprodWhere;


  $t = str_replace(" ", "", $search);
	$poc_znaku = strlen($t);

	if($poc_znaku > 2)
  {
		search2($search,$found_points,$found_names,$addWhere);
		
		// byly nalezeny polozky
		if(count($found_points) > 0)
    {
			// seradime podle poctu bodu ktere byly prirazeny pri hledani
			@asort($found_points); // cisele serazeni
			@reset($found_points);

			// seradime a prevedeme na vysledne pole $found
			krsort($found_names);
			reset($found_names);
			while ($p = each($found_names))
      {
				$n = $p['key'];
				$h = $p['value'];
			
				if(!empty($found_names[$n]))
        {
					natcasesort($found_names[$n]);
					reset($found_names[$n]);
					while ($p2 = each($found_names[$n]))
          {
						$n2 = $p2['key'];
						$h2 = $p2['value'];
						$found[] = $n2;
					}
				}
			}

			reset($found);

			if(empty($_GET['p'])) $p = 1;
	    else $p = $_GET['p'];
	  	$zobrazit_od = ($p - 1) * $_SESSION['products_on_page'];
      $zobrazit_do = $p * $_SESSION['products_on_page'];

			$count_records = count($found);
			$pages_search = strankovani($count_records,'vyhledavani/');

// 		$query = "SELECT ".T_GOODS.".* ,(SELECT ".T_FOTO_ZBOZI.".name 
//              FROM ".T_FOTO_ZBOZI." 
//              WHERE ".T_FOTO_ZBOZI.".id_good = ".T_GOODS.".id
//              ORDER BY ".T_FOTO_ZBOZI.".position 
//              LIMIT 1) as img2
//              FROM ".T_GOODS."            
//              WHERE ".T_GOODS.".id IN (".implode(", ", $found).") LIMIT ".$zobrazit_od.", ".$zobrazit_do;
             
             
             $query = "SELECT ".T_GOODS.".id AS id,
			".T_GOODS.".name AS name, ".T_GOODS.".kod AS kod,
            ".T_GOODS.".akce AS akce, ".T_GOODS.".novinka AS novinka, ".T_GOODS.".doporucujeme AS doporucujeme,".T_GOODS.".prednost AS prednost,
            ".T_GOODS.".id_dodani,
            (SELECT ".T_FOTO_ZBOZI.".name 
             FROM ".T_FOTO_ZBOZI." 
             WHERE ".T_FOTO_ZBOZI.".id_good = ".T_GOODS.".id 
             ORDER BY ".T_FOTO_ZBOZI.".position 
             LIMIT 1) as img, 
			".T_GOODS.".id_vyrobce AS id_vyrobce,  
			".T_GOODS.".cena AS cena, ".T_GOODS.".cena_eshop AS cena_eshop, ".T_GOODS.".dop_cena AS dop_cena, ".T_GOODS.".dph AS dph,
            ".T_GOODS.".anotace AS anotace
             FROM ".T_GOODS."            
             WHERE ".T_GOODS.".id IN (".implode(", ", $found).") LIMIT ".$zobrazit_od.", ".$zobrazit_do;
             
             //echo $query;
        
//         $query = "SELECT ".T_GOODS_X_CATEGORIES.".id_good AS id, 
// 			".T_GOODS.".id AS id,
// 			".T_GOODS.".name AS name, ".T_GOODS.".kod AS kod,
//             ".T_GOODS.".akce AS akce, ".T_GOODS.".novinka AS novinka, ".T_GOODS.".doporucujeme AS doporucujeme,".T_GOODS.".prednost AS prednost,
//             ".T_GOODS.".id_dodani,
//             (SELECT ".T_FOTO_ZBOZI.".name 
//              FROM ".T_FOTO_ZBOZI." 
//              WHERE ".T_FOTO_ZBOZI.".id_good = ".T_GOODS_X_CATEGORIES.".id_good 
//              ORDER BY ".T_FOTO_ZBOZI.".position 
//              LIMIT 1) as img, 
// 			".T_GOODS.".id_vyrobce AS id_vyrobce,  
// 			".T_GOODS.".cena AS cena, ".T_GOODS.".cena_eshop AS cena_eshop, ".T_GOODS.".dop_cena AS dop_cena, ".T_GOODS.".dph AS dph,
//             ".T_GOODS.".anotace AS anotace
// 			FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES."
// 			$where $podminka_vyrobci 
// 			GROUP BY ".T_GOODS.".id 
// 			ORDER BY ".T_GOODS.".".$_SESSION['order_shop']." ".$_SESSION['smer_trideni']." ".$limit;
             
  		$v = my_DB_QUERY($query,__LINE__,__FILE__);
  		$count_records = mysql_num_rows($v);

  		$PRODUCTS .= good_box_in_table($v,$count_records);
		}
	}
	else $TEXT = "<div class=\"clanek\">Prosíme upravte hledanou frázi tak, aby obsahovala nejméně 3 znaky.</div>";
	
	
	if(empty($PRODUCTS)) $TEXT = "<div class=\"clanek\">Hledanému výrazu neodpovídá žádný záznam.</div>";
	
	
	$H1 = "<span class='search'>Výsledky hledání výrazu: \"".$_SESSION['search']."\"</span>";
	
	if(isset($pages_search) AND !empty($pages_search))
	{
	  $PAGES=$pages_search;
  }
	
  unset($_SESSION['search']);
}
// *****************************************************************************
// vyhledavani
// *****************************************************************************









// *****************************************************************************
// vyhledavani parametry
// *****************************************************************************
if(isset($_GET['sWordP'])) {

	$H1="Vyhledávání podle parametrů";
	
	$arraySubCat=getSubCats(ZOBRAZ_VYHLEDAVANI);   	
	$POROVNAVAC='<div class="text">
	<p>
	<a href="/clanek/5-virtualni-prohlidky.html"><img alt="" src="/UserFiles/Image/bannery/pneumatiky-skladem.png" style="width: 497px; height: 116px; "><br>
	</a>
	</p>
	</div>
	<div class="text porovnavac">'.getPorovnavac($arraySubCat).'</div>';
	
	$PRODUCTS=getPorovnavacProducts();
	
	$FORM="<div class='text'>".formDOTAZNIK("Nenašli jste co jste potřebovali? Neváhejte nás kontaktovat.")."</div>";	

}
// *****************************************************************************
// vyhledavani parametry
// *****************************************************************************











// *****************************************************************************
// obchod - produkty v kategorii
// *****************************************************************************
// id name hidden descr lang products id_parent position


if((ZALOZKY_VYROBKY || isSuperParent($_GET['kategorie'])==false) && empty($_GET['produkt']) && !empty($_GET['kategorie'])) {
     
     
     //kdyz jsme v pneumatikach, tak zobraz vyhledavani pro pneumatiky
     
	$superParent=getSuperParent($_GET['kategorie']);     
     if($superParent['id']==ZOBRAZ_VYHLEDAVANI){
     	$arraySubCat=getSubCats(ZOBRAZ_VYHLEDAVANI);   	
		$POROVNAVAC='<div class="text porovnavac">'.getPorovnavac($arraySubCat).'</div>';	
	}
     
     //kdyz jsme v pneumatikach, tak zobraz vyhledavani pro pneumatiky

     
     
	// projdeme VYROBCE a zjistime kteri jsou skryti a nebudou 
	// zahrnuty do vyhledavani
	// id name hidden lang
	$query = "SELECT id FROM ".T_PRODS." 
	WHERE hidden = 1 AND ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	while ($z = mysql_fetch_array($v)) {
		$prodWhereVyrobci .= " AND id_vyrobce != ".$z['id']."";
	}
	
	// nazev aktualni kategorie
	$query = "SELECT name, descr, view FROM ".T_CATEGORIES." 
	WHERE ".SQL_C_LANG." AND id = ".$_GET['kategorie'];
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$title = $akt_page = $H1 = mysql_result($v, 0, 0);
	$TEXT = trim(@mysql_result($v, 0, 1));
	$view = trim(@mysql_result($v, 0, 2));
	
	
	// TODO: kontrola zda se nezobrazuji podkategorie ktere jsou skryte
	// seznam zbozi v kategorii (vcetne podrizenych)
	
	$ch_cat=null; // prednastavime $ch_cat
	children_in_category($_GET['kategorie'],$ch_cat); // mame vnorene kategorie
	
	
	
	// vygenerujeme dotaz na vnorene kategorie + skryte a do nich vnorene
	if(!empty($ch_cat))
  {
		$cat_list = $ch_cat;
		$ch_cat=null; // prednastavime $ch_cat
		
		reset($cat_list);

    // Skryté kategorie
		$query = "
    SELECT id
    FROM ".T_CATEGORIES."
		WHERE hidden = 1
    AND ".SQL_C_LANG;
		$v = my_DB_QUERY($query,__LINE__,__FILE__);

    while($z = mysql_fetch_assoc($v))
    {
      $hidden_list[$z["id"]] = $z["id"];
    }
		

		// znovu projdeme seznam kategorii a upresnime WHERE
		if(!empty($cat_list))
    {		
			reset($cat_list);
			while($p = each($cat_list))
      {			
				if(empty($hidden_list[$p['value']]))
        {
          $Qcat[] = $p['value'];
        }
			}		
		}

    if(isset($Qcat) AND !empty($Qcat))
    {
      $Qcat = " ".T_GOODS_X_CATEGORIES.".id_cat IN (".implode("," , $Qcat).") ";
    }
    else
    {
      $Qcat = "";
    }

    $prednost_razeni="prednost desc,";

    //CV: uprednostnime zbozi pouze kdyz se divame na jednotlivou kategorii    
/*    if (count($cat_list)==1) $prednost_razeni="prednost desc,";
    else $prednost_razeni=""; 
*/
		
		// ******* // vyřazení skupinových se skupino neexistujících id
		$chybaskupiny='';
		$querysk = "SELECT id, name, kod, dph, cena, id_vyrobce, skupina, anotace
    					FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES." WHERE $Qcat AND not skupina='' AND cena=0 AND ".T_GOODS.".id = ".T_GOODS_X_CATEGORIES.".id_good ";
    		$sqlsk=my_DB_QUERY($querysk,__LINE__,__FILE__);
		$i=0;
		$chyba='';
		while($zsk=mysql_fetch_array($sqlsk)) {
			$i++;
			$delic = '|';

			$tok = strtok($zsk['skupina'],$delic);
			$podminka='';
			while($tok) {
				if ($podminka=='')  $podminka.=' id='.$tok;
				else  $podminka.=' OR id='.$tok;
				$tok = strtok($delic);
			}

			if(!empty($podminka)){
				$podm='WHERE '.$podminka;
			}else{
				$podm='';
			}

			$querysk2='SELECT id, name, kod, cena FROM '.T_GOODS.' '.$podm.' ORDER BY id, name, kod ' ;
			$sqlsk2=my_DB_QUERY($querysk2,__LINE__,__FILE__);
			$pocet=0;
			$nasel='';
			$pocet=mysql_num_rows($sqlsk2);
			if($pocet==0) {
				$chybaskupiny .= " AND id != ".$zsk['id']."";
			}
		}
		
		$where = "WHERE $Qcat 
		AND ".T_GOODS.".id = ".T_GOODS_X_CATEGORIES.".id_good 
		AND ".T_GOODS.".hidden = 0 
		$prodWhereVyrobci $chybaskupiny
		AND ".T_GOODS_X_CATEGORIES.".".SQL_C_LANG;
		//AND ".T_PRODS.".id = ".T_GOODS.".id_vyrobce 
		
		
		$query2 = "SELECT DISTINCT ".T_GOODS.".id_vyrobce
		FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES."  $where";

		$v2 = my_DB_QUERY($query2,__LINE__,__FILE__);
		$omezeni='';
		while($z2=mysql_fetch_array($v2)) {
      		if($omezeni=='') $omezeni= ' id='.$z2['id_vyrobce'];
      		else $omezeni.= ' OR id='.$z2['id_vyrobce'];
    		}
    		if($omezeni!=='')  $omezeni='AND ('.$omezeni.')'; 
    
    		// pocet zaznamu pro strankovani a podminka pro top v kategorii
    
		$query = "SELECT ".T_GOODS_X_CATEGORIES.".id_good
		FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES."  $where $podminka_vyrobci group by ".T_GOODS_X_CATEGORIES.".id_good";

		$v = my_DB_QUERY($query,__LINE__,__FILE__);
    		$count_records = mysql_num_rows($v);
    		$limit = records_limit();
    
		$where_top='';
    		$TOP10='';
    		$query = "SELECT DISTINCT ".T_GOODS.".id 
			FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES."  $where $podminka_vyrobci";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		while($zp=mysql_fetch_array($v)) {
      		if($where_top=='') $where_top=' z.id='.$zp['id'];
      		else $where_top.=' OR z.id='.$zp['id'];
    		}
		
		if($where_top!=='') $where_top=' AND ('.$where_top.')';

		
// *****************************************************************************
// TOP                                                                          
// *****************************************************************************
/*
$query = "
SELECT z.id AS id, z.name AS name, z.id_vyrobce, z.img, z.cena, z.cena_eshop, z.dph, z.dop_cena, COUNT(id_produkt) AS pocet
FROM ".T_ORDERS_PRODUCTS." o
INNER JOIN ".T_GOODS." z ON z.id=o.id_produkt AND z.lang=".C_LANG." AND z.cena>0
WHERE o.id_produkt>0  $where_top GROUP BY o.id_produkt
ORDER BY pocet DESC, z.id DESC
LIMIT ".POCET_TOP;
$v = my_DB_QUERY($query,__LINE__,__FILE__);

$kolik_nasel=mysql_num_rows($v);
if($kolik_nasel > 0) {
	$rozdil=POCET_TOP-$kolik_nasel;
	if($rozdil>0) $TOP['dopln']=$rozdil;
	$i = 1;   
	//$TOP10 ='';
	$TOP['vylouceni']='';
	while ($z = mysql_fetch_array($v))  {   
		$id = $z['id'];
		$TOP['vylouceni']='';
		if($rozdil>0) {
			if($TOP['vylouceni']=='') $TOP['vylouceni']=' z.id='.$id;
			else $TOP['vylouceni']=' OR z.id='.$id;
		}
		if($TOP['vylouceni']!=='') $TOP['vylouceni']= ' AND  NOT ('.$TOP['vylouceni'].')';
		$query2 = "SELECT id,name FROM  ".T_CATEGORIES.", ".T_GOODS_X_CATEGORIES."  WHERE ".T_GOODS_X_CATEGORIES.".id_good = $id AND ".T_CATEGORIES.".id=".T_GOODS_X_CATEGORIES.".id_cat ";
		
		$v2 = my_DB_QUERY($query2,__LINE__,__FILE__);
		
		while ($z2 = mysql_fetch_assoc($v2)) {
			$kat_id=$z2['id'];
			$kat_name=$z2['name'];
		}
		$name = $z['name'];//." //".$z['id_vyrobce']." "
		$dph = $z['dph'];
		$cena = $z['cena'];
    if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $cena = $z['cena_eshop'];
		$dop_cena = $z['dop_cena'];
		$img = $z['img'];
		$id_vyrobce = $z['id_vyrobce'];
		$img1 = IMG_P_S.$id.".".$img;
		$img2 = IMG_P_O.$id.".".$img;
		$title2 = uvozovky($name)."";
		
		$ceny = ceny2($cena, $dph, $pocet=1, $id_vyrobce, $id);

		$url = HTTP_ROOT.'/'.$kat_id.'-'.text_in_url($kat_name).'/'.$id.'-'.text_in_url($name).'/';
		$nahled='<a href="'.$url.'" title="'.$title2.'" ><img alt="'.$title2.'" src="'.$img1.'" /></a>';
		
		if($TOP10=='') $TOP10= '
			<div class="boxik_prvni">
			'.$nahled.'
			<div>
			<h2><a href="'.$url.'" title="'.$title2.'">'.$name.'</a></h2>
			<span class="cena">'.$ceny[10].' Kč bez DPH</span>
			<a href="'.$url.'" title="detail" class="detail"><span class="vice">více info</span></a>
			</div>
			<div class="nowrap">&nbsp;</div>
			</div>';
		else $TOP10 .= '
			<div class="boxik">
			'.$nahled.'
			<div>
			<h2><a href="'.$url.'" title="'.$title2.'">'.$name.'</a></h2>
			<span class="cena">'.$ceny[10].' Kč bez DPH</span>
			<a href="'.$url.'" title="detail" class="detail"><span class="vice">více info</span></a>
			</div>
			<div class="nowrap">&nbsp;</div>
			</div>';
		
		$i++;
	}

}else $TOP['status']='nenasel';
*/
// *****************************************************************************
// TOP
// *****************************************************************************

      

		if($count_records > 0) {
			// jednotlive produkty
			// id id_cat name img text hidden akce cena dph lang kod id_vyrobce
			$query = "SELECT DISTINCT ".T_GOODS_X_CATEGORIES.".id_good AS id, 
			".T_GOODS.".id AS id,
			".T_GOODS.".name AS name, ".T_GOODS.".kod AS kod,
            ".T_GOODS.".akce AS akce, ".T_GOODS.".novinka AS novinka, ".T_GOODS.".doporucujeme AS doporucujeme,".T_GOODS_X_CATEGORIES.".uprednostnit AS prednost,
            ".T_GOODS.".id_dodani,
            (SELECT ".T_FOTO_ZBOZI.".name 
             FROM ".T_FOTO_ZBOZI." 
             WHERE ".T_FOTO_ZBOZI.".id_good = ".T_GOODS_X_CATEGORIES.".id_good 
             ORDER BY ".T_FOTO_ZBOZI.".position 
             LIMIT 1) as img, 
			".T_GOODS.".id_vyrobce AS id_vyrobce,  
			".T_GOODS.".cena AS cena, ".T_GOODS.".cena_eshop AS cena_eshop, ".T_GOODS.".dop_cena AS dop_cena, ".T_GOODS.".dph AS dph,
            ".T_GOODS.".anotace AS anotace
			FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES."
			$where $podminka_vyrobci 
			ORDER BY $prednost_razeni".$_SESSION['order_shop']." ".$_SESSION['smer_trideni']." ".$limit;
			//exit;
			
      
			$v = my_DB_QUERY($query,__LINE__,__FILE__);

			switch($view){
				case 1: {
					$PRODUCTS .= good_box_in_table($v,$count_records);
				}
				
				case 2: {
					$PRODUCTS .= good_row_in_table($v,$count_records);
				}
				
				default:{
					$PRODUCTS .= good_box_in_table($v,$count_records);
				}
			}




					
			$PAGES=$pages.'<div class="clear">
						</div>';
			
			
		
		}elseif(!SHOWING_CATEGORIES) $TEXT .= '<div class="clanek">Požadovaná data nebyla nalezena.</div>';
	
	}
}elseif(!empty($_GET['kategorie'])){
 	$query='select name,descr from '.T_CATEGORIES.' where id='.$_GET['kategorie'];
 	$v = my_DB_QUERY($query,__LINE__,__FILE__);
 	$z=mysql_fetch_array($v);
 	
	$title=$H1=$z['name'];
	$TEXT=$z['descr'];
}
if(empty($_GET['kategorie']) AND !isset($_GET['go'])) $TEXT .= '<div class="clanek">Požadovaná data nebyla nalezena.</div>';
// *****************************************************************************
// obchod - produkty v kategorii
// *****************************************************************************









// *****************************************************************************
// detail produktu
// *****************************************************************************
if(!empty($_GET['produkt'])){

	// ***************************************************************************
	// soubory ke stazeni prirazene k produktu
	// ***************************************************************************
	$query = "SELECT 
	".T_GOODS_X_DOWNLOAD.".id_file AS id_file, 
	".T_DOWNLOAD.".odkaz AS odkaz,
  	".T_DOWNLOAD.".mime AS mime,  
	".T_DOWNLOAD.".soubor AS soubor, 
	".T_DOWNLOAD.".text AS text 
	FROM ".T_GOODS_X_DOWNLOAD.", ".T_DOWNLOAD." 
	WHERE ".T_GOODS_X_DOWNLOAD.".id_good = ".$_GET['produkt']." AND 
	".T_DOWNLOAD.".id = ".T_GOODS_X_DOWNLOAD.".id_file";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$pocet=-1;
	$pocet=mysql_num_rows($v);
	
	$cislo_videa=0;
	
	$dwn_dir = str_replace("../", "", FILES_UPL);
	$root_dir = str_replace("/index.php", "", HTTP_ROOT);
	
	define('VDIR',$dwn_dir);
  	define('SCDIR',$dwn_dir.'screen/');
  	
  	$downloads='';
  	
	while ($z = mysql_fetch_array($v)) {
    
		if($z['mime']=='application/octet-stream' OR $z['mime']=='application/x-shockwave-flash' OR $z['mime']=='video/x-ms-wmv') {
			$cislo_videa++;
			$z['soubor']=str_replace('[','',$z['soubor']);
			$z['soubor']=str_replace(']','',$z['soubor']);
			$video_file[$cislo_videa] = $z['soubor'];
	     	$video_file_nazev[$cislo_videa] = $z['odkaz'];
	     	$mime[$cislo_videa]=$z['mime'];		         
     	}else{
			$file = "".$dwn_dir."".$z['soubor'];
		  	$size = file_size($file);
		
		  	if(file_exists($file) && $size > 0) {
				$x1 = explode (".", $file); // roztrhame - delicem je tecka
				$x2 = count($x1) - 1; // index posledniho prvku pole
				$ext = strtoupper($x1[$x2]); // mame priponu

				$downloads .= "<a href=\"".$root_dir."/".$dwn_dir."".$z['soubor']."\">".$z['odkaz']."</a> ($size, $ext)<br />";

				if(!empty($z['text']))$downloads .= "<span>".$z['text']."</span><br /><br />";
			}
		}
	}
	
	$url=explode('?',$_SERVER['REQUEST_URI']);
	
	$odkaz=$url[0].'?video=';
	include_once ('video.php');
	
	
  	$data="<p class=\"nenalezeno\">Hledaná položka na serveru neexistuje.</p>";
	
	if(!empty($downloads)) $downloads = "
	<div class=\"downloads\">
	<strong>Soubory ke stažení</strong>:<br />	
	$downloads                                		
	</div>
	<div class='clear'>
	</div>";
	// ***************************************************************************
	// soubory ke stazeni prirazene k produktu
	// ***************************************************************************

	
	$javascript = '
	<script type="text/javascript">
	
	function val01(form1) {
	
		var x = form1.addKs.value;
		x = x.replace(/,/g,\'.\'); // nahrada carky za tecku
		x = x.replace(/ /g,\'\'); // nahrada (zde jen odstraneni) mezery
		
		if (!(x > 0)) { alert("Počet musí být vyšší než 0"); form1.addKs.focus(); return false; }
		'.((isset($addjavascript)) ? $addjavascript : "").'
		else return true;
	
	}
	</script>';
	


	$parametry = parametry($_GET['produkt'],1);
	
	
	
	
	
	// ***************************************************************************
	// pribuzne
	// ***************************************************************************
	$query = "SELECT * FROM ".T_GOODS_PRIBUZNE.", ".T_GOODS." 
	WHERE id_good = ".$_GET['produkt']." AND id = id_pribuzne AND hidden = 0
	ORDER BY ".T_GOODS.".".$_SESSION['order_shop']." ".$_SESSION['smer_trideni']." ";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	$count_records=mysql_num_rows($v);
	if($count_records>0) $pribuzne = good_box_in_table($v,$count_records);
	// ***************************************************************************
	// pribuzne
	// ***************************************************************************
	
	
	// ***************************************************************************
	// Fotogalerie
	// ***************************************************************************
	      
      $q = "SELECT  ".T_FOTO_KATEG.".id as cat_id, ".T_FOTO_KATEG.".name as cat_name, ".T_FOTO_ZBOZI.".id_good, ".T_FOTO.".id, ".T_FOTO.".name, ".T_FOTO.".img  
      FROM ".T_FOTO_ZBOZI.",".T_FOTO_KATEG.",".T_FOTO."   
      WHERE ".T_FOTO_ZBOZI.".id_good = ".$_GET['produkt']." 
      AND ".T_FOTO_KATEG.".id = ".T_FOTO_ZBOZI.".id_kateg
      AND ".T_FOTO_KATEG.".hidden =0
      AND ".T_FOTO.".id_kateg = ".T_FOTO_KATEG.".id
      AND ".T_FOTO_KATEG.".".SQL_C_LANG."  ORDER BY ".T_FOTO_KATEG.".position, ".T_FOTO.".pos  ";
      $vq = my_DB_QUERY($q,__LINE__,__FILE__);
      
	 $obr_galerie2='';
	 
      while ($zvg = mysql_fetch_array($vq)) {
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
			$obr_galerie2.= '<a href="'.$img2.'" title="'.$name.'" class="fancybox" rel="group"><span class="obrazek"><img alt="'.$name.'" src="'.$img1.'" /></span><span>'.$foto_name.'</span></a>';
		}
      }
      if($obr_galerie2!='') $obr_galerie2='
      							<h2>###nadpis### - fotogalerie</h2>
								<div class="fotogalerie">'.$obr_galerie2.'</div>
								<div class="clear">
								</div>';
	

	// id id_cat name img text hidden akce cena dph lang kod id_vyrobce
	$query = "
  SELECT
  ".T_GOODS.".name, ".T_GOODS.".img, ".T_GOODS.".text, ".T_GOODS.".akce, ".T_GOODS.".cena, ".T_GOODS.".cena_eshop, ".T_GOODS.".dph,
  ".T_GOODS.".kod, kod3, kod4, kod5, kod6, kod7, kod8,
  ".T_GOODS.".id_dodani, ".T_GOODS.".id_vyrobce, ".T_GOODS.".anotace, ".T_GOODS.".dop_cena,
  ".T_GOODS.".hidden, ".T_GOODS.".pocet_kusu, ".T_GOODS.".zobraz_pocet_kusu
	FROM ".T_GOODS."
	WHERE ".T_GOODS.".id = '".intval($_GET['produkt'])."'
	AND ".T_GOODS.".".SQL_C_LANG." 
  LIMIT 1
	";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$dodani = 0;
	while($z = mysql_fetch_assoc($v))
  {
		$id = $id_produktu = intval($_GET['produkt']);
		$odkaz_na_clanek = "";

    if($z['id_vyrobce'] == 2) 
    { 
      $odkaz_na_clanek = 'Pro více informací o <strong>autobateriích Yuasa</strong> přejděte na naši informační stránku: <a href="http://www.nekvinda-obchod.cz/clanek/10-autobaterie-yuasa.html" title="Autobaterie Yuasa">Autobaterie Yuasa</a><br /><br />'; 
    }
    if($z['id_vyrobce'] == 1) 
    { 
      $odkaz_na_clanek = 'Pro více informací o <strong>autobateriích Sznajder</strong> přejděte na naši informační stránku: <a href="http://www.nekvinda-obchod.cz/clanek/9-autobaterie.html" title="Autobaterie Sznajder">Autobaterie Sznajder</a><br /><br />'; 
    }
   
    if($odkaz_na_clanek != "")
    {
      $odkaz_na_clanek .= '
      <h4>Přečtěte si také naše informační články o autobateriích:</h4>

      <ul>
        <li>
               <a href="http://www.nekvinda-obchod.cz/clanek/12-zarucni-podminky-autobaterii.html" title="Záruční podmínky autobaterií">Záruční podmínky autobaterií</a></li>
        <li>
               <a href="http://www.nekvinda-obchod.cz/clanek/13-znaky-plneho-nabiti-autobaterie.html" title="Znaky plného nabití autobaterie">Znaky plného nabití autobaterie</a></li>
        <li>
               <a href="http://www.nekvinda-obchod.cz/clanek/14-zasady-bezpecnosti-pri-manipulaci-s-autobaterii.html" title="Zásady bezpečnosti při manipulaci s autobaterií">Zásady bezpečnosti při manipulaci s autobaterií</a></li>
        <li>
               <a href="http://www.nekvinda-obchod.cz/clanek/15-vyber-a-instalace-nove-autobaterie.html" title="Výběr a instalace nové autobaterie">Výběr a instalace nové autobaterie</a></li>
        <li>
               <a href="http://www.nekvinda-obchod.cz/clanek/16-udrzba-skladovani-a-likvidace-autobaterii.html" title="Údržba, skladování a likvidace autobaterií">Údržba, skladování a likvidace autobaterií</a></li>
      </ul>
      ';
    }

    if(isset($_GET["kategorie"]) AND $_GET["kategorie"] == 17)
    {
      $odkaz_na_clanek = '
      <h2>Nabíječky autobaterií - naše nabídka</h2>
      <p>
      Kompletní nabídku najdete zde: <a href="http://www.nekvinda-obchod.cz/17-nabijecky-autobaterii/" title="Nabíječky autobaterií">Nabíječky autobaterií</a>.</p>

      <h2>Přečtěte si také naše informační články o autobateriích:</h2>
      <p>
      <a href="http://www.nekvinda-obchod.cz/clanek/12-zarucni-podminky-autobaterii.html" title="Záruční podmínky autobaterií">Záruční podmínky autobaterií</a><br />
      <a href="http://www.nekvinda-obchod.cz/clanek/13-znaky-plneho-nabiti-autobaterie.html" title="Znaky plného nabití autobaterie">Znaky plného nabití autobaterie</a><br />
      <a href="http://www.nekvinda-obchod.cz/clanek/14-zasady-bezpecnosti-pri-manipulaci-s-autobaterii.html" title="Zásady bezpečnosti při manipulaci s autobaterií">Zásady bezpečnosti při manipulaci s autobaterií</a><br />
      <a href="http://www.nekvinda-obchod.cz/clanek/15-vyber-a-instalace-nove-autobaterie.html" title="Výběr a instalace nové autobaterie">Výběr a instalace nové autobaterie</a><br />
      <a href="http://www.nekvinda-obchod.cz/clanek/16-udrzba-skladovani-a-likvidace-autobaterii.html" title="Údržba, skladování a likvidace autobaterií">Údržba, skladování a likvidace autobaterií</a><br />
      <a href="http://www.nekvinda-obchod.cz/clanek/9-autobaterie.html" title="Autobaterie Sznajder - Informace o značce">Autobaterie Sznajder</a><br />
      <a href="http://www.nekvinda-obchod.cz/clanek/10-autobaterie-yuasa.html" title="Autobaterie Yuasa - Informace o značce">Autobaterie Yuasa</a>.</p>
      ';
    }
		
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
				

    // Varianty kódů
    if(!empty($z["kod3"])) $varianty_kodu[] = $z["kod3"];
    if(!empty($z["kod4"])) $varianty_kodu[] = $z["kod4"];
    if(!empty($z["kod5"])) $varianty_kodu[] = $z["kod5"];
    if(!empty($z["kod6"])) $varianty_kodu[] = $z["kod6"];
    if(!empty($z["kod7"])) $varianty_kodu[] = $z["kod7"];
    if(!empty($z["kod8"])) $varianty_kodu[]= $z["kod8"];

    if(isset($varianty_kodu) AND !empty($varianty_kodu))
    {
      $varianty_kodu = '
      <span class="varianty_kodu">
        Alternativní kat. č. 
        '.implode(", ", $varianty_kodu).'
      </span>
      ';
    }
    else
    {
      $varianty_kodu = "";
    }
    // END Varianty kódů

		
    // Dostupnost
		if(!empty($dodaniZbozi[$z['id_dodani']]))
    {
      if($z['id_dodani'] == 13)
      {
        $dodani='<span class="text">Dostupnost:</span> <span class="dostupnost" style="color:green;">'.$dodaniZbozi[$z['id_dodani']]."</span>";
      }
      else if($z['id_dodani'] == 12)
      {
        $dodani='<span class="text">Dostupnost:</span> <span class="dostupnost" style="color:red;">'.$dodaniZbozi[$z['id_dodani']]."</span>";
      }
      else
      {
        $dodani='<span class="text">Dostupnost:</span> <span class="dostupnost">'.$dodaniZbozi[$z['id_dodani']]."</span>";
      }
    }
		elseif(!empty($dodani[$z['id_vyrobce']]))$dodani = '<span class="dostupnost">'.$dodani[$z['id_vyrobce']]."</span>";
		else $dodani='<span class="dostupnost">'.$dodaniZbozi[2]."</span>";
	
    
    

    // Výrobce
		if(empty($nazvy_vyrobcu[$z['id_vyrobce']]))$vyrobce = "";
		else
    {
      $vyrobce = '
      <div class="vyrobce">
        <span class="text">Výrobce:</span> <strong>'.$nazvy_vyrobcu[$z['id_vyrobce']].'</strong>
      </div>
      ';
    }


    // Počet kusů
    // -1 => neni generovan istockem => nezobrazime nic
    if($z["pocet_kusu"] == 0 AND $z["zobraz_pocet_kusu"] != 1) $pocet_kusu = "<strong style='color:red;'>".$z["pocet_kusu"]."</strong>";
    else if($z["pocet_kusu"] != -1 AND $z["zobraz_pocet_kusu"] != 1) $pocet_kusu = "<strong>".$z["pocet_kusu"]."</strong>";
    else $pocet_kusu = "";

    if(!empty($pocet_kusu))
    {
      $pocet_kusu = '
      <div class="ks">
        <span class="text">Počet kusů skladem:</span> '.$pocet_kusu.'
      </div>';
    }

    
    //poznámka při dodání != skladem nebo pocet kusu =0
    //info_dostupnost_pocet_kusu
    $info_dostupnost_pocet_kusu = null;
    if($z['id_dodani'] != 13 ) {
        $info_dostupnost_pocet_kusu = '
      <div class="info_neni_skladem" style="color:red; font-weight:bold;">
        V TUTO CHVÍLI NENÍ SKLADEM <br />
        <br />
        Prosíme informujte se na tel. 461 534 404<br />
        o termínu naskladnění. Děkujeme za pochopení.<br /><br />
      </div>';
    }


    //unset($_SESSION["cache"]); // Vypnutí cache (pomalejší).
    cache_cat_info();
    $_SESSION["nezobrazovat_ks"] = $_SESSION["cache"]["cat_info"]["nezobrazovat_ks"];

    // Zařazení všech produktů v první nalezené kategorii.
    $_SESSION["produkty_x_kategorie"] = goods_x_cat();

    if(isset($_SESSION["produkty_x_kategorie"][$id]) AND $_SESSION["produkty_x_kategorie"][$id] > 0)
    { // Počet kusů je zakázáno zobrazovat na kategorii.
      $id_cat = $_SESSION["produkty_x_kategorie"][$id]; // ID kategorie, ze které bereme slevu u produktu.

      if(isset($_SESSION["nezobrazovat_ks"][$id_cat]) AND $_SESSION["nezobrazovat_ks"][$id_cat] > 0)
      {
        $pocet_kusu = "";
      }
    }
		 
		$id_vyrobce=$z['id_vyrobce'];
		$H1 = $z['name'].' <br /><span>kat. č. '.$z['kod']."</span> ".$varianty_kodu;
		$nazev = $z['name'].' '.$z['kod']."";
    $title = $nazev;
    $title2=uvozovky($nazev);

	    // hlavni obrazek produktu
	    
// 	    if (empty($img)) {
// 			$img ='<img alt="'.$title2.'" src="/img/nfoto.jpg" />';
// 		}else{
// 			$img ='/image.php?file='.$idimg.'&amp;e='.$img.'&amp;size=middle&amp;cati='.$cati;
// 			$img2 ='/image.php?file='.$idimg.'&amp;e='.$img.'&amp;size=original&amp;cati='.$cati;
// 			$img ='<a href="'.$img2.'" title="'.$title2.'" rel="prettyPhoto" ><img alt="'.$title2.'" src="'.$img.'" /></a>';
// 		}
	    
    $img_title = uvozovky($nazev);
    $img_array = get_product_fotos($id_produktu);

    if($img_array != 0)
    {
// 		  $img_middle = $img_array[0]['middle'];
// 		  $img_original = $img_array[0]['original'];
		  $img_middle = $img_array[0]['name'];
		  $img_original = $img_array[0]['name'];
          // zjistime priponu
          $x1 = explode ('.' , $img_original); // roztrhame nazev souboru - delicem je tecka
          $x2 = count($x1) - 1; // index posledniho prvku pole
          $e = $x1[$x2]; // mame priponu (vkladame take do DB, proto return)
          $pripona = strtolower($e); // pripona souboru (typ)
          //echo "<br />".$img_middle;
          //echo "<br />".$img_original;
         // print_r($x1);
		
  		// titulek
	  	if(!empty($img_array[0]['title']))
  		{
        $img_title = uvozovky($img_array[0]['title']);
      }

  
            $img ='/image_new.php?file='.$img_middle.'&amp;e='.$pripona.'&amp;size=middle&amp;cati='.$cati;
 			$img2 ='/image_new.php?file='.$img_original.'&amp;e='.$pripona.'&amp;size=original&amp;cati='.$cati;
            
// 			$img = '
//           <a href="'.$img_original.'" title="'.$img_title.'" rel="prettyPhoto['.$id_produktu.']">
//             <img src="'.$img_middle.'" alt="'.$img_title.'" title="'.$img_title.'" />
//           </a>';
          
      	if(empty($cati)){
      	  $cesta='./UserFiles/products/original/';
      	}else{
      	  $cesta='./UserFiles/categories/original/';		
      	}
          $img = '
          <a href="'.$cesta.$img_original.'" title="'.$img_title.'" rel="group" class="fancybox" >
            <img src="'.$img.'" alt="'.$img_title.'" title="'.$img_title.'" />
          </a>';
		}
		else
		{ // obrazek nenalezen
		  //$img = '<img src="/images/schema_'.FOLDER.'/nfoto.png" title="'.$img_title.'" alt="'.$img_title.'" />';
		  $img ='<img alt="'.$title2.'" src="/img/nfoto.jpg" />';
    }
    
    // dalsi fotky produktu
    $fotky = '';
    if(count($img_array) > 1)
    {
      for($index = 1; $index < count($img_array); $index++)
      {
		    $img_small = $img_array[$index]['small'];
		    $img_original = $img_array[$index]['original'];

		    // titulek
		    if(!empty($img_array[$index]['title']))
		    {
          $img_title = uvozovky($img_array[$index]['title']);
        }
        else
        {
		      $img_title = uvozovky($nazev);
        }

        if($index <= 3)
        { // dalsi 3 nahledy maly -> original
				  $fotky .= '
          <a href="'.$img_original.'" title="'.$img_title.'" class="fancybox" rel="group">
            <span>
              <img src="'.$img_small.'" alt="'.$img_title.'" title="'.$img_title.'" />
            </span>
          </a>';
        }
        else
        { // zbyle fotky pouze odkazem
          if(!isset($dalsi_foto_odkaz))
          { // prvni skryta fotka
            $dalsi_foto_pocet = count($img_array) - $index;

            $dalsi_foto_odkaz = '<a id="dalsi_foto" href="'.$img_original.'" class="fancybox" rel="group">+ '.$dalsi_foto_pocet.'<br />další</a>';
          }
          else
          { // dalsi skryte fotky
            $dalsi_foto_odkaz .= '<a class="dalsi_foto_skryte fancybox" href="'.$img_original.'" rel="group" ></a>';
          }
        }
      }
    }

    if(!empty($fotky))
    {
      $fotky = '
      <div id="product_galery">
        '.$fotky.'
      </div>
      ';

      if(isset($dalsi_foto_odkaz) AND !empty($dalsi_foto_odkaz))
      {
        $fotky .= $dalsi_foto_odkaz;
      }
    }
    // end obrazek

					     
		
    // Krátký popis
    $anotace = '';
		if(!empty($z['anotace']))
    {
			$anotace = '
			<div class="anotace">
        '.$z['anotace'].'
      </div>
			';
		}


		
		if(!empty($z['text'])) {
				$text = "
				<h2>$nazev - popis</h2>
				<div class=\"popis\">".$z['text']."</div>
				";
		}else{
			$text='';	
		}
		
		if($z['akce'] == 1) $akce = "<span id=\"akce\">AKČNÍ NABÍDKA</span>";
		else $akce = "";

		$cena = $z['cena'];
    if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $cena = $z['cena_eshop'];
		$dph = $z['dph'];

		// generujeme ceny - fce vraci pole s ruznymi tvary cen
		$ceny = ceny2($cena, $dph, $pocet = 1, $id_vyrobce, $id);

		
		if(empty($procenta))$procenta='';
			
		$kod = $z['kod'];
		if(empty($kod)) $kod = "&nbsp;";
		
		$basket_alt = "Přidat do košíku - ".uvozovky($nazev);
		
		
		if(empty($beznaCenaText))$beznaCenaText='';
		if(empty($VIDEO))$VIDEO='';
		if(empty($SEZNAM))$SEZNAM='';
		if(empty($downloads))$downloads='';
		if(empty($pribuzne))$pribuzne='';

    $dop_cena = $z['dop_cena'];
    $dop_ceny = ceny2($dop_cena,$dph,$pocet=1);
		$proc=0;
		$beznaCena = ceny2($cena, $dph, $pocet = 1, NULL, $id , $se_slevou = FALSE);

    // Eura.
    $dop_cena_euro = kc_na_eura($dop_ceny[1]); // Přepočetna eura.
    $dop_ceny_euro = ceny2($dop_cena_euro, $dph, $pocet = 1);

		if($dop_ceny[3] > $ceny[3])
    {
										    					
  					$beznaCenaText = '
					  		<div id="detail_cena2" >
									<span class="text">Původní cena s DPH:</span>
									<span class="cena kc" style="text-decoration:line-through;"> '.$dop_ceny[30].' Kč</span>
                  <span class="cena euro" style="text-decoration:line-through;"> '.$dop_ceny_euro[30].' &euro;</span>
								</div>';
  					$proc = round((100 * $ceny[3]) / $dop_ceny[3]);
  					$procenta='<span class="procenta">SLEVA '.(100-$proc).'%</span>';
          
          }
          elseif($beznaCena[3] > $ceny[3])
          {

  					$beznaCenaText = '
					  			<div id="detail_cena2">
					  				<span class="text">Internetová cena s DPH:</span>
									<span class="cena kc">'.$beznaCena[30].' Kč</span>
								</div>';
  					$proc = round((100 * $ceny[3]) / $beznaCena[3]);
  					$procenta='<span class="procenta">SLEVA '.(100-$proc).'%</span>';

          }

// 		$data='
//             <div class="image">
//                 <div id="detail_image">
//                 '.$img.'
//                 </div>
//             '.$fotky.'
//             </div>
//             <div class="right">'.$procenta.''.$anotace;
            
            
		$data='
            <div id="detail_left">
                <div id="detail_image">
                '.$img.'
                </div>
            '.$fotky.'
            </div>
            <div class="right">'.$procenta.''.$anotace;

    // Eura.
   $cena_eura = kc_na_eura($ceny[1]); // Přepočetna eura.
   $ceny_eura = ceny2($cena_eura, $dph, $pocet = 1);

	  if(SDPH){
      $cenaHlavni_euro = $ceny_eura[30];
			$cenaHlavni=$ceny[30];
			$text_k_ceneHlavni = 'Naše cena s DPH: ';
			$cenaVedlejsi=$ceny[10];
      $cenaVedlejsi_euro=$ceny_eura[10];
			$text_k_ceneVedlejsi = 'Naše cena bez DPH: ';
		}else{
			$cenaHlavni=$ceny[10];
      $cenaHlavni_euro=$ceny_eura[10];
			$text_k_ceneHlavni = 'Naše cena bez DPH: ';
			$cenaVedlejsi=$ceny[30];
      $cenaVedlejsi_eura=$ceny_eura[30];
			$text_k_ceneVedlejsi = 'Naše cena s DPH: ';
		}		

    $button_prodej = '<input class="basket_button" type="submit" title="Vložit do košíku produkt '.$nazev.'" alt="Vložit do košíku produkt '.$nazev.'" value="Vložit do košíku"/>';

    if($z['hidden']==1)
    {
      $button_prodej = '<span class="basket_button" style="cursor: default;">Prodej ukončen</span>';
    }
    
    
    $basket_form = '
                                <form action="'.HTTP_ROOT.'?go=basket" method="post" onsubmit="return val01(this)" class="basket">
									<div class="basketform">
										<input type="hidden" name="addId" value="'.$_GET['produkt'].'" />
										<input type="text" name="addKs" class="input_ks" value="1" /><span>ks</span>'.$button_prodej.'
									</div>
								</form>';
    if($info_dostupnost_pocet_kusu != null) {
        $basket_form = null;
    }
    

		if($dop_cena > 0){ 
 
									$data .= '		
									<div id="detail_cena">
					          <span class="text">'.$text_k_ceneVedlejsi.'</span>
					          <span class="cena kc">'.$cenaVedlejsi.' Kč</span>
                    <span class="cena euro">'.$cenaVedlejsi_euro.' &euro;</span>
                  </div>
                  <div id="detail_cena_VAT">
									  <span class="text">'.$text_k_ceneHlavni.'</span>
									  <span class="cena kc">'.$cenaHlavni.' Kč</span>
                    <span class="cena euro">'.$cenaHlavni_euro.' &euro;</span>
                  </div>
									'.$beznaCenaText.'
									
									'.$dodani.'
									'.$pocet_kusu.'
									'.$vyrobce.'
									'.$info_dostupnost_pocet_kusu.'
									'.$basket_form.'
									<div class="clear">
									</div>
									<a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'#formular" title="Zaslat dotaz k produktu '.$nazev.'" class="dotaz">zaslat dotaz k produktu</a>
									<!--<a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'#comments" title="Komentáře k produktu '.$nazev.'" class="comments">komentáře k produktu</a>-->
									</div>';						
		}elseif($cena > 0){
									$data .= '
									<div id="detail_cena">
									  <span class="text">'.$text_k_ceneVedlejsi.'</span>
					          <span class="cena kc">'.$cenaVedlejsi.' Kč</span>
                    <span class="cena euro">'.$cenaVedlejsi_euro.' &euro;</span>
                  </div>
                  <div id="detail_cena_VAT">
  									<span class="text">'.$text_k_ceneHlavni.'</span>
  									<span class="cena kc">'.$cenaHlavni.' Kč</span>
                    <span class="cena euro">'.$cenaHlavni_euro.' &euro;</span>
                  </div>
									'.$beznaCenaText.'
									
									'.$dodani.'
									'.$pocet_kusu.'
									'.$vyrobce.'
									'.$info_dostupnost_pocet_kusu.'
								    '.$basket_form.'
									<div class="clear">
									</div>									
									<a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'#formular" title="Zaslat dotaz k produktu '.$nazev.'" class="dotaz">zaslat dotaz k produktu</a>
									<!--<a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'#comments" title="Komentáře k produktu '.$nazev.'" class="comments">komentáře k produktu</a>-->
									</div>';
		}else{
			$data.='</div>';
		}
		
		if(!empty($MEDIA_WMP) OR !empty($MEDIA_WMP)) $MEDIA_WMP='<p><br />Pokud vám váš prohlížeč neumožňuje přehrát soubor přímo, využijte odkazu „Stáhnout video“ k jeho stažení do vašeho PC.<br /><br /></p>'.$MEDIA_WMP;
		
		$zarazeni=zarazeniProduktu($_GET['produkt']);
		
		$FORMULAR = '
    <!--
    <div class="info">
      Zašlete dotaz našemu pracovníkovi pomocí následujícího formuláře.<br />
      Na Váš dotaz bude odpovězeno v nejbližším možném termínu.
    </div>
    -->
    '.formDOTAZNIK('Zeptejte se na produkt ###nadpis###');
		
		if(empty($VIDEO))
    {
		  $TEXT = '
      <div class="product">
       '.$data.'
				<div class="clear"></div>

				<div class="texty">
  				'.$zarazeni.'
  				'.$text.'
  				'.$parametry.'
  				'.$obr_galerie2.'
  				'.$downloads.'
  				'.$odkaz_na_clanek.'
  				'.$FORMULAR.'
  				'.$VIDEO.'
  				'.$SEZNAM.'
				</div>
      ';
				
				if(!empty($pribuzne)){
					$TEXT .= '
					<h2>Příbuzné produkty '.$nazev.'</h2>
					';
					$PRODUCTS=$pribuzne;
				}
				
				$TEXT .='</div>';
				
        /* Komentáře k produktu */
        $COMMENTS = "";
        //$COMMENTS=getComments($id);
        
				
				if(!empty($COMMENTS)){
					$COMMENTS='<div class="text"><div class="info">Pod komentáři k výrobku můžete přidat vlastní komentář, po vyplnění formuláře<br />se Váš příspěvek odešle ke schválení administrátorovi.</div>'.str_replace('###nadpis###',$nazev,$COMMENTS).'</div>';					
				}
				
				$TEXT=str_replace('###nadpis###',$nazev,$TEXT);
		}else{
		$TEXT = '<div class="product">'.$VIDEO.'
              		'.$SEZNAM.'
				<div class="nowrap">&nbsp;</div>';
				$kdeje=strpos($_SERVER['REQUEST_URI'],'?');
		$TEXT .='
              		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.substr($_SERVER['REQUEST_URI'],0,$kdeje).'" title="'.$H1.'">Zpět >> '.$H1.'</a></p>                            
              		</div>';
          }
	}   
	
	
	// nazev aktualni kategorie
	$nadpis = "$nazev";

}
// *****************************************************************************
// detail produktu
// *****************************************************************************





// *****************************************************************************
// doporucujeme
// *****************************************************************************
if($_GET['go']=="doporucujeme"){

	$where=' where doporucujeme=1 and hidden=0';
	
	
	$query = "SELECT DISTINCT ".T_GOODS.".id_vyrobce FROM ".T_GOODS.", ".T_GOODS_X_CATEGORIES."  $where";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$omezeni='';
	while($z=mysql_fetch_array($v)) {
     	if($omezeni=='') $omezeni= ' id='.$z['id_vyrobce'];
      	else $omezeni.= ' OR id='.$z['id_vyrobce'];
    	}
    	
	if($omezeni!=='')  $omezeni='AND ('.$omezeni.')'; 
		
	$query = "SELECT COUNT(".T_GOODS.".id) FROM ".T_GOODS." $where";
	
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
    	$count_records = mysql_result($v, 0, 0);
    	
	
	$limit = records_limit();
	
	$query="select * from ".T_GOODS." $where ORDER BY ".T_GOODS.".".$_SESSION['order_shop']." ".$_SESSION['smer_trideni']." ".$limit;
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$H1='Doporučujeme';
	
	while($z=mysql_fetch_array($v))
  {
		$PRODUCTS .= good_box($z);
	}
		
	$pages=strankovani(mysql_num_rows($v),'doporucujeme/');
}
// *****************************************************************************
// doporucujeme
// *****************************************************************************







// *****************************************************************************
// akcni-nabidka
// *****************************************************************************
if($_GET['go']=="akcni-nabidka"){

	if(!empty($_GET['akce'])){
		$id_akce=$_GET['akce'];
		$query = "SELECT * FROM ".T_AKCE." WHERE hidden = 0 and id=".$id_akce." AND ".SQL_C_LANG;
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$akceData=mysql_fetch_array($v);
		
		$H1=$akceData['name'];
		$TEXT=$akceData['descr'];
	
		$where="where ".T_GOODS.".id=".T_GOODS_X_AKCE.".id_good and ".T_GOODS.".hidden=0 and ".T_GOODS_X_AKCE.".id_cat=$id_akce";
		
		$query = "SELECT DISTINCT ".T_GOODS.".id_vyrobce FROM ".T_GOODS.", ".T_GOODS_X_AKCE."  $where";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		$omezeni='';
		while($z=mysql_fetch_array($v)) {
	     	if($omezeni=='') $omezeni= ' id='.$z['id_vyrobce'];
	      	else $omezeni.= ' OR id='.$z['id_vyrobce'];
	    	}
	    	
		if($omezeni!=='')  $omezeni='AND ('.$omezeni.')'; 		
		
		$query = "SELECT COUNT(".T_GOODS_X_AKCE.".id_good) FROM ".T_GOODS.",".T_GOODS_X_AKCE." $where";
		
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
	    	$count_records = mysql_result($v, 0, 0);
		
		$PAGES=strankovani($count_records,'akcni-nabidka/'.$akceData['id'].'-'.text_in_url($akceData['name']).'/');
		
		$limit = records_limit();
		
		$query="select * from ".T_GOODS.",".T_GOODS_X_AKCE." $where ORDER BY ".T_GOODS.".".$_SESSION['order_shop']." ".$_SESSION['smer_trideni']." ".$limit;
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		
		                         
		if(mysql_num_rows($v)>0){
			while($z=mysql_fetch_array($v))
      {
				$PRODUCTS .= good_box($z);
			}		
		}elseif(!empty($_GET['p'])){
		    Header('HTTP/1.1 404 Not Found'); 
		    Header('Location: http://'.$_SERVER['SERVER_NAME'].'/akcni-nabidka/'.$akceData['id'].'-'.text_in_url($akceData['name']).'/');
		}
		
	}	
}
// *****************************************************************************
// akcni-nabidka
// *****************************************************************************


// texty ke kategoriim nahradnich dilu
// URI
$nahradni_dily[118] = 'Zetor Z 25 A, Z 25 K, Z50 Super';
$nahradni_dily[68] = 'Zetor 2011, 3011, 4011';
$nahradni_dily[69] = 'Zetor 5511, 5545, 5611, 5645, 5711, 5745, 6711, 6745';
$nahradni_dily[70] = 'Zetor Major 4911, 5911, 5945, 6911, 6945';
$nahradni_dily[71] = 'Zetor Major 5011, 6011, 6045, 7011, 7045, 7045H';
$nahradni_dily[72] = 'Zetor Major 5211, 5245, 6211, 6245, 7211, 7245, 7245H, 7711, 7745, 7711 Turbo, 7745 Turbo';
$nahradni_dily[73] = 'Zetor Major 3320, 3340, 4320, 4340, 5320, 5340, 6320, 6340, 7320 Turbo, 7340 Turbo';
$nahradni_dily[74] = 'Zetor Super 3321, 3341, 4321, 4341, 5321, 5341, 6321, 6341, 7321 Turbo, 7341 Turbo';
$nahradni_dily[75] = 'Zetor Proxima 6421, 6441, 7421, 7441, 8421, 8441';
// URII
$nahradni_dily[77] = 'Zetor 8011, 8045, 12011, 12045, 16045, 8111, 8145, 9111, 9145, 10111, 10145, 12111, 12145, 14145, 16145, 8211, 8245, 9211, 9245, 10211, 10245, 11211, 11245, 12211, 12245, 14245, 16245';
// URIII
$nahradni_dily[79] = 'Zetor 7520, 7540, 8520 Turbo, 8540 Turbo, 9520 Turbo, 9540 Turbo, 10540 Turbo';
$nahradni_dily[80] = 'Zetor Forterra 8641, 9641, 10641, 11441, 11741.4C, 12441';

if(isset($_GET["kategorie"]) AND !empty($_GET["kategorie"]) AND !isset($_GET["produkt"]))
{
  parent_categories($_GET["kategorie"]); // zjistime nadrazene kategorie

  $pridat_text = false;
  foreach($parents2 as $nad_kategorie)
  {
    if(isset($nahradni_dily[$nad_kategorie]) AND !empty($nahradni_dily[$nad_kategorie]))
    { // pokud nektera z nadkategorii je obsazena v poli ulozime si jeji id
      $pridat_text = $nad_kategorie;
      break;
    }
  }
  
  if($pridat_text != false)
  {
    $TEXT = '
    <p>
      Kategorie obsahuje náhradní díly pro <strong>' . $nahradni_dily[$pridat_text] . '</strong>.
    </p>
    <p>
      <a href="http://www.nekvinda-obchod.cz/clanek/6-kontakt.html" title="">
        <img alt="Přes 50 000 náhradních dílů skladem, odesíláme do 24 hodin! Nenašli jste? Zavolejte nám na 461 534  404 nebo pište - Klikněte zde" src="/UserFiles/Image/nd-zetor-volejte.png" style="width: 496px; height: 43px;" />
      </a>
    </p>'
    . $TEXT;
  }
}

?>
