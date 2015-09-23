<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');


//xml.nekvinda-obchod.cz

$xml_file='http://xml.nekvinda-obchod.cz/ZB_shop.xml';

if($_SERVER['SERVER_NAME'] == "localhost") 
{
  $xml_file='ZB_shop_test.xml';
}

$zbozi_array=array();

$product_keys['nazev']='*SHOP*SHOPITEM*NAZEV_ZBOZI';
$product_keys['cislo']='*SHOP*SHOPITEM*CISLO_ZBOZI';
$product_keys['dcena']='*SHOP*SHOPITEM*DOPORUC_CENA';
$product_keys['cena']='*SHOP*SHOPITEM*NAB_CENAA';
$product_keys['mj']='*SHOP*SHOPITEM*MJ';
$product_keys['smj']='*SHOP*SHOPITEM*STAV_MJ';
$product_keys['szbozi']='*SHOP*SHOPITEM*SKUPINA_ZBOZI';
$product_keys['klasifikace1']='*SHOP*SHOPITEM*KLASIFIKACE1';
$product_keys['klasifikace3']='*SHOP*SHOPITEM*KLASIFIKACE3';
$product_keys['klasifikace6']='*SHOP*SHOPITEM*KLASIFIKACE6';
$product_keys['cislozbozidod']='*SHOP*SHOPITEM*CISLO_ZBOZI_DOD';
$product_keys['cislovyrobce']='*SHOP*SHOPITEM*CISLO_VYROBCE';
$product_keys['vahamj']='*SHOP*SHOPITEM*VAHA_MJ';
$product_keys['rozmervyska']='*SHOP*SHOPITEM*ROZMER_VYSKA';
$product_keys['rozmersirka']='*SHOP*SHOPITEM*ROZMER_SIRKA';
$product_keys['rozmerhloubka']='*SHOP*SHOPITEM*ROZMER_HLOUBKA';


function contents($parser, $data){
    global $current_tag, $counter, $zbozi_array;
    global $product_keys;   
     
         
    switch($current_tag){
        case $product_keys['nazev']: 
        	  if(empty($zbozi_array[$counter]['nazev']))$zbozi_array[$counter]['nazev']='';
            $zbozi_array[$counter]['nazev'] .= $data;
            break;	
        case $product_keys['cislo']: 
        	  if(empty($zbozi_array[$counter]['cislo']))$zbozi_array[$counter]['cislo']='';
            $zbozi_array[$counter]['cislo'] .= $data;
            break;
        case $product_keys['dcena']: 
        	  if(empty($zbozi_array[$counter]['dcena']))$zbozi_array[$counter]['dcena']='';
            $zbozi_array[$counter]['dcena'] .= $data;
            break;	
        case $product_keys['cena']: 
        	  if(empty($zbozi_array[$counter]['cena']))$zbozi_array[$counter]['cena']='';
            $zbozi_array[$counter]['cena'] .= $data;
            break;
        case $product_keys['mj']: 
        	  if(empty($zbozi_array[$counter]['mj']))$zbozi_array[$counter]['mj']='';
            $zbozi_array[$counter]['mj'] .= $data;
            break;
        case $product_keys['smj']: 
        	  if(empty($zbozi_array[$counter]['smj']))$zbozi_array[$counter]['smj']='';
            $zbozi_array[$counter]['smj'] .= $data;
            break;	
        case $product_keys['szbozi']: 
        	  if(empty($zbozi_array[$counter]['szbozi']))$zbozi_array[$counter]['szbozi']='';
            $zbozi_array[$counter]['szbozi'] .= $data;
            break;
        case $product_keys['klasifikace1']: 
        	  if(empty($zbozi_array[$counter]['klasifikace1']))$zbozi_array[$counter]['klasifikace1']='';
            $zbozi_array[$counter]['klasifikace1'] .= $data;
            break;	
        case $product_keys['klasifikace3']: 
        	  if(empty($zbozi_array[$counter]['klasifikace3']))$zbozi_array[$counter]['klasifikace3']='';
            $zbozi_array[$counter]['klasifikace3'] .= $data;
            break;
        case $product_keys['klasifikace6']: 
        	  if(empty($zbozi_array[$counter]['klasifikace6']))$zbozi_array[$counter]['klasifikace6']='';
            $zbozi_array[$counter]['klasifikace6'] .= $data;
            break;
        case $product_keys['cislozbozidod']: 
        	  if(empty($zbozi_array[$counter]['cislozbozidod']))$zbozi_array[$counter]['cislozbozidod']='';
            $zbozi_array[$counter]['cislozbozidod'] .= $data;
            break;	
        case $product_keys['cislovyrobce']: 
        	  if(empty($zbozi_array[$counter]['cislovyrobce']))$zbozi_array[$counter]['cislovyrobce']='';
            $zbozi_array[$counter]['cislovyrobce'] .= $data;
            break;
        case $product_keys['vahamj']: 
        	  if(empty($zbozi_array[$counter]['vahamj']))$zbozi_array[$counter]['vahamj']='';
            $zbozi_array[$counter]['vahamj'] .= $data;
            break;	
        case $product_keys['rozmervyska']: 
        	  if(empty($zbozi_array[$counter]['rozmervyska']))$zbozi_array[$counter]['rozmervyska']='';
            $zbozi_array[$counter]['rozmervyska'] .= $data;
            break;
        case $product_keys['rozmersirka']: 
        	  if(empty($zbozi_array[$counter]['rozmersirka']))$zbozi_array[$counter]['rozmersirka']='';
            $zbozi_array[$counter]['rozmersirka'] .= $data;
            break;
	   case $product_keys['rozmerhloubka']: 
        	  if(empty($zbozi_array[$counter]['rozmerhloubka']))$zbozi_array[$counter]['rozmerhloubka']='';
            $zbozi_array[$counter]['rozmerhloubka'] .= $data;
            break;		  		  		  		  			  	  			  	  		  		  		  	  		  		  			  		  		  
    }                                                 
}


$xml_parser = xml_parser_create();

xml_set_element_handler($xml_parser, "startTag", "endTag");

xml_set_character_data_handler($xml_parser, "contents");


$data=file_get_contents($xml_file);

if(!(xml_parse($xml_parser, $data))){ 
    die("Error on line " . xml_get_current_line_number($xml_parser));
}

xml_parser_free($xml_parser);




echo '
  <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table border="1">
';



$vypnuto=$updatovano=$obrazky=$celkem=$chybnych=0;
$nezaraditelnych=null;
$zarazenych=null;

if ($_SERVER['SERVER_NAME'] == "localhost") {

	$db = "nekvinda-obchod";
	$host = "localhost";
	$user = "root";
	$psswd = "";

}
else{
	
	$db = "nekvinda-obchod";
	$host = "mysql20.hostingsolutions.cz";
	$user = "info__nekvinda";
	$psswd = "obchod842";	
		
}


$conn=new Mysqli($host,$user,$psswd,$db);
$conn->query("set names utf8");	


$query="select kod from fla_shop_zbozi where xml = 1";
$v=$conn->query($query);

while($row=$v->fetch_object())
{
  $xml_produkty_db[] = $row->kod;
}


foreach($zbozi_array as $zbozi){
  
  $xml_produkty[] = $zbozi['cislo'];
  
	if(empty($zbozi['klasifikace1']))$zbozi['klasifikace1']='';
  if(empty($zbozi['klasifikace3']))$zbozi['klasifikace3']='';
  if(empty($zbozi['klasifikace6']))$zbozi['klasifikace6']='';

  $id_kategorie_1 = substr($zbozi['klasifikace1'],0,2);
  //01 - pneumatiky
  //02 - duse a vlozky
  //10 - nahradni dily
    
	$query="select id from fla_shop_kategorie where code='".$zbozi['klasifikace1']."'";	
	$v=$conn->query($query);
	
	if($v->num_rows>0 && !empty($zbozi['klasifikace1'])){
		$catid=$v->fetch_object()->id;
				
		$id=addUpZbozi($zbozi,$conn);
		
		if(!empty($id)){
			addZboziToCategory($id,$catid,$conn);
		}
		
		$zarazenych[$id]=$id;
	}else{
		$id=addUpZbozi($zbozi,$conn);
		$nezaraditelnych.='<a href="http://'.$_SERVER['HTTP_HOST'].'/admin/index.php?C_lang=1&app=shop&f=products&id='.$id.'&a=edit">'.getZboziInfo($conn,'name',$id).'</a><br />';

		$query_update="update fla_shop_zbozi set hidden=1 where id=$id";	
	  $v_update=$conn->query($query_update);  		
	}

  if($zbozi['klasifikace3'] != "" AND $zbozi['klasifikace1'] != "")
  {
    $id_kategorie_1 = substr($zbozi['klasifikace1'],0,2);
    $id_kategorie_3 = substr($zbozi['klasifikace3'],0,2);

    if($id_kategorie_3 == $id_kategorie_1 AND $id_kategorie_3 == 10)
    {  // náhradní díly zarazujeme do kategorie klasifikace3
	    $query="select id from fla_shop_kategorie where code='".$zbozi['klasifikace3']."'";	
	    $v=$conn->query($query);
	    
	    if($v->num_rows>0)
	    {
        $catid=$v->fetch_object()->id;

		    if(!empty($id))
        {
			    addZboziToCategory($id,$catid,$conn);
		    }
      }
    }
  }

  if($zbozi['klasifikace6'] != "" AND $zbozi['klasifikace1'] != "")
  { //nahradni dily
    $id_kategorie_1 = substr($zbozi['klasifikace1'],0,2);
    $id_kategorie_6 = substr($zbozi['klasifikace6'],0,2);

    if($id_kategorie_6 == $id_kategorie_1 AND $id_kategorie_6 == 10)
    {  // náhradní díly zarazujeme do kategorie klasifikace6
	    $query="select id from fla_shop_kategorie where code='".$zbozi['klasifikace6']."'";	
	    $v=$conn->query($query);
	    
	    if($v->num_rows>0)
	    {  
        $catid=$v->fetch_object()->id;

		    if(!empty($id))
        {
			    addZboziToCategory($id,$catid,$conn);
		    }
      }
    }
  }
  
  if($zbozi['klasifikace3'] != "" AND $zbozi['klasifikace1'] != "")
  { // pneumatiky 
    $id_kategorie_1 = substr($zbozi['klasifikace1'],0,2);   

    if($id_kategorie_1 == 01)
    {  
      // ID produktu
 	    $query="select id from fla_shop_zbozi where kod='".$zbozi["cislo"]."'";
	    $v=$conn->query($query);
      $id_good = 0;
      if($v->num_rows>0) $id_good=intval($v->fetch_object()->id);

      // Klasifikaci 6 použijeme jako kód pro vyhledávání, odstraníme z něj nečíselné znaky.
      $klasifikace6 = $zbozi['klasifikace6'];
      $kod = '';
      for($i = 0; $i != strlen($klasifikace6); $i++)
      {
        if(is_numeric($klasifikace6[$i]) === TRUE) $kod .= $klasifikace6[$i];
      }
      $kod = intval($kod);

      if($id_good>0 AND $kod>0)
      {
		    $query = "UPDATE fla_shop_zbozi SET kod2 = $kod WHERE id = $id_good";
		    $v=$conn->query($query);
      }


      $galerie = $zbozi['klasifikace3'];  // pneumatikam pridelujeme podle klasifikace6 galerii vzorku pneu

      $query = "select id from fla_foto_kateg where name='$galerie'";	
	    $v = $conn->query($query); 
      
	    if($v->num_rows>0)
	    {  
        $id_galerie=$v->fetch_object()->id;

	      if($id_good>0)
	      {
   	      $query="delete from fla_shop_zbozi_x_foto where id_good='$id_good' and id_kateg='$id_galerie'";
	        $v=$conn->query($query); 
       
 	        $query="INSERT INTO fla_shop_zbozi_x_foto (id_good , id_kateg) VALUES ($id_good , $id_galerie)";
          $v=$conn->query($query);
            
          $query = "SELECT fla_foto.id , fla_foto.img
                    FROM fla_foto_kateg , fla_foto
                    WHERE fla_foto_kateg.id = '$id_galerie'
                    AND fla_foto.id_kateg = fla_foto_kateg.id
                    AND fla_foto.pos = 1
                    LIMIT 1";
          $v = $conn->query($query);
          
          if($v->num_rows > 0)
          {   
            $z = $v->fetch_object();  
           
            $id_foto = $z->id;
            $koncovka = $z->img;
          
            $jmeno = $id_foto.'.'.$koncovka;
            $obr = '../UserFiles/fotogalerie/original/'.$jmeno;

            if(file_exists($obr))
            { // nahrajeme obrazek k produktu pouze pokud existuje
              $img = img_upload($jmeno , $obr , $id_good);

		          $query = "UPDATE fla_shop_zbozi SET img = '$img' WHERE id = $id_good";
		          $v=$conn->query($query);
            }
          }
          else
          {
		        $query = "UPDATE fla_shop_zbozi SET img = '' WHERE id = $id_good";
		        $v=$conn->query($query);
          }                       
        }         
      }            
    }
  }
	
	/*
	if(isset($id_kategorie_1) AND ($id_kategorie_1 == "05" OR $id_kategorie_1 == "11" OR $id_kategorie_1 == "12" OR $id_kategorie_1 == "13"))
	{ // odstraneni parametru u dane kategorie
    $query = "SELECT id FROM fla_shop_parametry_4 WHERE id_produkt = ".$id;
    $v = $conn->query($query);
    $row=$v->fetch_object();
    if(isset($row->id))
    {
      $PxK = $row->id;
    }

    if(isset($PxK) AND !empty($PxK))
    {
		  $query = "DELETE FROM fla_shop_parametry_3 WHERE id_kp = $PxK";
		  $conn->query($query);
    }
		
    $query = "DELETE FROM fla_shop_parametry_4 WHERE id_produkt = $id LIMIT 1";
		$conn->query($query);
  }
	*/
	
	if(isset($id_kategorie_1) AND ($id_kategorie_1 == "01" OR $id_kategorie_1 == "02"))
  { // parametry vkladame pouze u dusi, vlozek a pneu
	  ulozProduktovyList($id,$zbozi,$conn);
  }
}


$skryt_produkty = array_diff($xml_produkty_db, $xml_produkty); 
$skryte = null;


if(count($skryt_produkty) > 0)
{
  foreach($skryt_produkty as $skryt)
  {    
    $query = "select id from fla_shop_zbozi where kod = '$skryt'";
    $v = $conn->query($query);
    $row=$v->fetch_object();
    $id = $row->id;
    
    $query = "update fla_shop_zbozi set hidden = 1 where id = $id";
    $v = $conn->query($query);    

    $skryte .= '<a href="http://'.$_SERVER['HTTP_HOST'].'/admin/index.php?C_lang=1&app=shop&f=products&id='.$id.'&a=edit">'.getZboziInfo($conn,'name',$id).'</a><br />'; 		  
  }
}


if(!empty($nezaraditelnych) OR !empty($skryte)) 
{
	$nezaraditelnych="
  Skryté:<br />
  ".$skryte."<br />
  <br />
  Nezařaditelné výrobky:<br />
	".$nezaraditelnych."<br />
  <br />";
	
	send('pneu.nekvinda@quick.cz',$nezaraditelnych,'nezaraditelne vyrobky','info@netaction.cz');  // krejci - pneu a duse
	//send('sou-pe@seznam.cz',$nezaraditelnych,'nezaraditelne vyrobky','info@netaction.cz');  // sourek - nahradni dily
  send('servis@nekvinda.cz',$nezaraditelnych,'nezaraditelne vyrobky','info@netaction.cz');  // nahradni dily
  send('monitor@netaction.cz',$nezaraditelnych,'nezaraditelne vyrobky','info@netaction.cz');
}


echo "	</table>
<br /><br />
".$nezaraditelnych."   
</body>
</html>";

  
  
  
  
function ulozProduktovyList($id,$zbozi,$conn){
	

	$query='select * from fla_shop_parametry_4 where id_produkt='.$id.'';
	$v=$conn->query($query);
	
	if($v->num_rows==0){
	
			
			
			//return array('znacka'=>'','dezen'=>'')
			if(empty($zbozi['klasifikace3']))$zbozi['klasifikace3']='';
			$info=getInfoPneu($zbozi['klasifikace3']);
			

			//return array('sirka'=>'','profil'=>'','polomer'=>'')
			if(empty($zbozi['klasifikace6']))$zbozi['klasifikace6']='';
			$rozmer=getRozmerPneu($zbozi['klasifikace6']);

			
				//uloz novej
		//  	    id_karta = 3
		// 	params	12	3	Šířka	1	mm	1
		// 	params	13	3	Profil	1	%	2
		// 	params	14	3	Ráfek	1	'	3
		// 	params	15	3	Konstrukce	1	 	4
		// 	params	16	3	Druh pneu	1	 	5
		// 	params	17	3	Značka	1	 	6
		// 	params	18	3	Dezén	1	 	7
				$query="insert into fla_shop_parametry_4(id_produkt,id_karta) values($id,3)";
		          $v=$conn->query($query);
		          
		          $id_kp=$conn->insert_id;
		          
		          foreach($rozmer as $key=>$value){
		          	$id_param='';
		          	switch($key){
						case 'sirka': $id_param=12;break;
						case 'profil': $id_param=13;break;
						case 'rafek': $id_param=14;break;
						case 'konstrukce': $id_param=15;break;
					}
		               if(!empty($id_param)){
			          	$query="insert into fla_shop_parametry_3(id_parametr,hodnota,id_kp) values($id_param,'".trim($value)."',$id_kp)";
			          	$conn->query($query);
		          	}
		          }
		          
		          foreach($info as $key=>$value){
		          	$id_param='';
		          	switch($key){
						case 'znacka': $id_param=17;break;
						case 'dezen': $id_param=18;break;
						case 'trideni': $id_param=16;break;
					}
		               if(!empty($id_param)){
			          	$query="insert into fla_shop_parametry_3(id_parametr,hodnota,id_kp) values($id_param,'".trim($value)."',$id_kp)";
			          	$conn->query($query);
		          	}
		          }          
          
          
	}else{
	
// 			$row=$v->fetch_object();
// 			
// 			
// 			$info=array();
// 			//return array('znacka'=>'','dezen'=>'')
// 			if(empty($zbozi['klasifikace3']))$zbozi['klasifikace3']='';
// 			$info=getInfoPneu($zbozi['klasifikace3']);
// 			
// 			$rozmer=array();
// 			//return array('sirka'=>'','profil'=>'','polomer'=>'')
// 			if(empty($zbozi['klasifikace6']))$zbozi['klasifikace6']='';
// 			$rozmer=getRozmerPneu($zbozi['klasifikace6']);
// 			
// 			
// 			
// 			
// 				//zkontroluj, zda je nektera hodnota prazdna, kdyz jo, uloz novou
// 				
// 				$query="SELECT id_kp,id_parametr,id_produkt FROM  fla_shop_parametry_3,fla_shop_parametry_4 WHERE fla_shop_parametry_4.id=fla_shop_parametry_3.id_kp and hodnota = '' AND id_parametr IN (12,13,14,15,16,17,18) and fla_shop_parametry_3.id_kp=".$row->id;		
// 				
// 				$v=$conn->query($query);
// 				
// 				while($row=$v->fetch_object()){
// 					$value='';
// 					switch($row->id_parametr){
// 						
// 						 case 12:{$value=$rozmer['sirka'];break;}
// 						 case 13:{$value=$rozmer['profil'];break;}
// 						 case 14:{$value=$rozmer['rafek'];break;}
// 						 case 15:{$value=$rozmer['konstrukce'];break;}
// 						 case 16:{$value=$info['trideni'];break;}
// 						 case 17:{$value=$info['znacka'];break;}
// 						 case 18:{$value=$info['dezen'];break;}
// 						 			
// 					}
// 					
// 					if(!empty($value)){
// 					$query="update fla_shop_parametry_3 set hodnota='".trim($value)."' where id_kp=".$row->id_kp." and id_parametr=".$row->id_parametr."";
// 					$conn->query($query);
// 					}
// 				}
	
	}
	
} 





function getRozmerPneu($string){

	$konstrukce='';
	$sirka='';
	$profil='';
	$rafek='';

	if(strpos($string,'-')){
		$string=str_replace('-','##',$string);
		$konstrukce='diagonální';	
	}elseif(strpos($string,'r')){
		$string=str_replace('r','##',$string);
		$konstrukce='radiální';	
	}
	
	if(strpos($string,'x')){
		$string=str_replace('x','##',$string);
	     $rozmery=explode('##',$string);
	     
	     if(count($rozmery)==3){
			$sirka=$rozmery[0];
			$profil=$rozmery[1];
			$rafek=$rozmery[2];
		}		
			
	}elseif(strpos($string,'/')){
		$string=str_replace('/','##',$string);
          $rozmery=explode('##',$string);
	     
	     if(count($rozmery)==3){
			$sirka=$rozmery[0];
			$profil=$rozmery[1];
			$rafek=$rozmery[2];
		}		
			
	}else{
	     $rozmery=explode('##',$string);
	     
	     if(count($rozmery)==2){
			$sirka=$rozmery[0];
			$profil='';
			$rafek=$rozmery[1];
		}
	     
	}
	
	return array('konstrukce'=>$konstrukce,'sirka'=>$sirka,'profil'=>$profil,'rafek'=>$rafek);

}

 



function getInfoPneu($string){
	
	switch($string){
		case '001001':{$znacka='Michelin';$dezen='';$trideni='zemědělské';break;}
		case '0010010501':{$znacka='Michelin';$dezen='XM 28';$trideni='zemědělské';break;}		
		case '0010010502':{$znacka='Michelin';$dezen='Machxbib';$trideni='zemědělské';break;}		
		case '0010010503':{$znacka='Michelin';$dezen='Multibib';$trideni='zemědělské';break;}		
		case '0010010504':{$znacka='Michelin';$dezen='Omnibib';$trideni='zemědělské';break;}
		case '0010010505':{$znacka='Michelin';$dezen='Agribib';$trideni='zemědělské';break;}		
		case '0010010510':{$znacka='Michelin';$dezen='Xeobib';$trideni='zemědělské';break;}
		case '0010010511':{$znacka='Michelin';$dezen='Axiobib';$trideni='zemědělské';break;}
		case '0010010520':{$znacka='Michelin';$dezen='Spraybib';$trideni='zemědělské';break;}
		case '0010010521':{$znacka='Michelin';$dezen='Agibib RC';$trideni='zemědělské';break;}
		case '0010010530':{$znacka='Michelin';$dezen='Megaxbib';$trideni='zemědělské';break;}
		case '0010010531':{$znacka='Michelin';$dezen='Cerexbib';$trideni='zemědělské';break;}
		case '0010010901':{$znacka='Michelin';$dezen='Cargoxbib';$trideni='zemědělské';break;}
		case '0010010902':{$znacka='Michelin';$dezen='XP 27';$trideni='zemědělské';break;}
		case '0010010801':{$znacka='Michelin';$dezen='XMCL';$trideni='zemědělské';break;}
		case '0010010802':{$znacka='Michelin';$dezen='XM 47';$trideni='zemědělské';break;}
		case '0010010803':{$znacka='Michelin';$dezen='XF';$trideni='zemědělské';break;}
		case '0010010891':{$znacka='Michelin';$dezen='XZSL';$trideni='zemědělské';break;}
		case '0010010892':{$znacka='Michelin';$dezen='Bibsteel All Terrain';$trideni='zemědělské';break;}
		case '0010010893':{$znacka='Michelin';$dezen='Bibsteel Hard Surface';$trideni='zemědělské';break;}
		case '0010010851':{$znacka='Michelin';$dezen='Power CL';$trideni='zemědělské';break;}
		
		case '001002':{$znacka='Kleber';$dezen='';$trideni='zemědělské';break;}
		case '0010020401':{$znacka='Kleber';$dezen='MA5';$trideni='zemědělské';break;}
		case '0010020402':{$znacka='Kleber';$dezen='MA4';$trideni='zemědělské';break;}
		case '0010020501':{$znacka='Kleber';$dezen='Fitker';$trideni='zemědělské';break;}
		case '0010020502':{$znacka='Kleber';$dezen='Topker';$trideni='zemědělské';break;}
		case '0010020503':{$znacka='Kleber';$dezen='Gripker';$trideni='zemědělské';break;}
		case '0010020504':{$znacka='Kleber';$dezen='Traker';$trideni='zemědělské';break;}
		case '0010020505':{$znacka='Kleber';$dezen='Super 8';$trideni='zemědělské';break;}
		case '0010020506':{$znacka='Kleber';$dezen='Super Vigne';$trideni='zemědělské';break;}
		case '0010020507':{$znacka='Kleber';$dezen='Super 3';$trideni='zemědělské';break;}
		
		case '001003':{$znacka='BF Goodrich';$dezen='';$trideni='zemědělské';break;}
		
		case '001004':{$znacka='Taurus';$dezen='';$trideni='zemědělské';break;}
		case '0010040507':{$znacka='Taurus';$dezen='Point 7';$trideni='zemědělské';break;}
		case '0010040508':{$znacka='Taurus';$dezen='Point 8';$trideni='zemědělské';break;}
		case '0010040565':{$znacka='Taurus';$dezen='Point 65';$trideni='zemědělské';break;}
		case '0010040570':{$znacka='Taurus';$dezen='Point 70';$trideni='zemědělské';break;}
		case '0010040595':{$znacka='Taurus';$dezen='RC 95';$trideni='zemědělské';break;}
		
		case '001005':{$znacka='Kormoran';$dezen='';$trideni='zemědělské';break;}
		case '0010050001':{$znacka='Kormoran';$dezen='Ekopro';$trideni='zemědělské';break;}
		case '0010050002':{$znacka='Kormoran';$dezen='AN 25';$trideni='zemědělské';break;}
		case '0010050003':{$znacka='Kormoran';$dezen='Landpro';$trideni='zemědělské';break;}
		case '0010050004':{$znacka='Kormoran';$dezen='AN 13';$trideni='zemědělské';break;}
		
		case '002001':{$znacka='Mitas';$dezen='';$trideni='zemědělské';break;}
		case '0020010401':{$znacka='Mitas';$dezen='TF 01';$trideni='zemědělské';break;}
		case '0020010403':{$znacka='Mitas';$dezen='TF 03';$trideni='zemědělské';break;}
		case '0020010404':{$znacka='Mitas';$dezen='TF 04';$trideni='zemědělské';break;}
		case '0020010405':{$znacka='Mitas';$dezen='TF 05';$trideni='zemědělské';break;}
		case '0020010406':{$znacka='Mitas';$dezen='TF 06';$trideni='zemědělské';break;}
		case '0020010501':{$znacka='Mitas';$dezen='TD 01';$trideni='zemědělské';break;}
		case '0020010502':{$znacka='Mitas';$dezen='TD 02';$trideni='zemědělské';break;}
		case '0020010505':{$znacka='Mitas';$dezen='TD 05';$trideni='zemědělské';break;}
		case '0020010513':{$znacka='Mitas';$dezen='TD 13';$trideni='zemědělské';break;}
		case '0020010517':{$znacka='Mitas';$dezen='TD 17';$trideni='zemědělské';break;}
		case '0020010519':{$znacka='Mitas';$dezen='TD 19';$trideni='zemědělské';break;}
		case '0020010520':{$znacka='Mitas';$dezen='TD 20';$trideni='zemědělské';break;}
		case '0020010530':{$znacka='Mitas';$dezen='TD 30';$trideni='zemědělské';break;}
		case '0020010540':{$znacka='Mitas';$dezen='TS 01';$trideni='zemědělské';break;}
		case '0020010541':{$znacka='Mitas';$dezen='TS 02';$trideni='zemědělské';break;}
		case '0020010542':{$znacka='Mitas';$dezen='TS 03';$trideni='zemědělské';break;}
		case '0020010543':{$znacka='Mitas';$dezen='TS 04';$trideni='zemědělské';break;}
		case '0020010544':{$znacka='Mitas';$dezen='TS 05';$trideni='zemědělské';break;}
		case '0020010545':{$znacka='Mitas';$dezen='TS 06';$trideni='zemědělské';break;}
		case '0020010546':{$znacka='Mitas';$dezen='TS 07';$trideni='zemědělské';break;}
		case '0020010551':{$znacka='Mitas';$dezen='RD 01';$trideni='zemědělské';break;}
		case '0020010552':{$znacka='Mitas';$dezen='RD 02';$trideni='zemědělské';break;}
		case '0020010553':{$znacka='Mitas';$dezen='RD 03';$trideni='zemědělské';break;}
		case '0020010555':{$znacka='Mitas';$dezen='RD 05';$trideni='zemědělské';break;}
		case '0020010591':{$znacka='Mitas';$dezen='AF 01';$trideni='zemědělské';break;}
		case '0020010601':{$znacka='Mitas';$dezen='TR 06';$trideni='zemědělské';break;}
		case '0020010602':{$znacka='Mitas';$dezen='TR 01';$trideni='zemědělské';break;}
		case '0020010603':{$znacka='Mitas';$dezen='TR 03';$trideni='zemědělské';break;}
		case '0020010604':{$znacka='Mitas';$dezen='TR 04';$trideni='zemědělské';break;}
		case '0020010606':{$znacka='Mitas';$dezen='TR 05';$trideni='zemědělské';break;}
		case '0020010607':{$znacka='Mitas';$dezen='TR 06';$trideni='zemědělské';break;}
		case '0020010608':{$znacka='Mitas';$dezen='TR 07';$trideni='zemědělské';break;}
		case '0020010609':{$znacka='Mitas';$dezen='TR 08';$trideni='zemědělské';break;}
		case '0020010610':{$znacka='Mitas';$dezen='TR 09';$trideni='zemědělské';break;}
		case '0020010611':{$znacka='Mitas';$dezen='TR 10';$trideni='zemědělské';break;}
		case '0020010612':{$znacka='Mitas';$dezen='TR 11';$trideni='zemědělské';break;}
		case '0020010613':{$znacka='Mitas';$dezen='TR 12';$trideni='zemědělské';break;}
		case '0020010614':{$znacka='Mitas';$dezen='TI 20';$trideni='zemědělské';break;}
		case '0020010615':{$znacka='Mitas';$dezen='TI 22';$trideni='zemědělské';break;}
		case '0020010616':{$znacka='Mitas';$dezen='TI 02';$trideni='zemědělské';break;}
		case '0020010617':{$znacka='Mitas';$dezen='TI 04';$trideni='zemědělské';break;}
		case '0020010618':{$znacka='Mitas';$dezen='TI 05';$trideni='zemědělské';break;}
		case '0020010619':{$znacka='Mitas';$dezen='TI 06';$trideni='zemědělské';break;}
		case '0020010620':{$znacka='Mitas';$dezen='TI 09';$trideni='zemědělské';break;}
		case '0020010621':{$znacka='Mitas';$dezen='TG 02';$trideni='zemědělské';break;}
		case '0020010622':{$znacka='Mitas';$dezen='SK 01';$trideni='zemědělské';break;}
		case '0020010623':{$znacka='Mitas';$dezen='SK 02';$trideni='zemědělské';break;}
		case '0020010630':{$znacka='Mitas';$dezen='EM 20';$trideni='zemědělské';break;}
		case '0020010631':{$znacka='Mitas';$dezen='EM 30';$trideni='zemědělské';break;}
		case '0020010632':{$znacka='Mitas';$dezen='EM 50';$trideni='zemědělské';break;}
		case '0020010633':{$znacka='Mitas';$dezen='EM 60';$trideni='zemědělské';break;}
		case '0020010634':{$znacka='Mitas';$dezen='EM 70';$trideni='zemědělské';break;}
		case '0020010635':{$znacka='Mitas';$dezen='EM 22';$trideni='zemědělské';break;}
		case '0020010636':{$znacka='Mitas';$dezen='EM 23';$trideni='zemědělské';break;}
		case '0020010637':{$znacka='Mitas';$dezen='EM 40';$trideni='zemědělské';break;}
		case '0020010640':{$znacka='Mitas';$dezen='NB 38 Extra';$trideni='zemědělské';break;}
		case '0020010641':{$znacka='Mitas';$dezen='NB 57';$trideni='zemědělské';break;}
		case '0020010642':{$znacka='Mitas';$dezen='NB 59';$trideni='zemědělské';break;}
		case '0020010650':{$znacka='Mitas';$dezen='MPT 20';$trideni='zemědělské';break;}
		case '0020010651':{$znacka='Mitas';$dezen='MPT 01';$trideni='zemědělské';break;}
		case '0020010652':{$znacka='Mitas';$dezen='MPT 02';$trideni='zemědělské';break;}
		case '0020010653':{$znacka='Mitas';$dezen='MPT 03';$trideni='zemědělské';break;}
		case '0020010654':{$znacka='Mitas';$dezen='MPT 04';$trideni='zemědělské';break;}
		case '0020010655':{$znacka='Mitas';$dezen='MPT 05';$trideni='zemědělské';break;}
		case '0020010656':{$znacka='Mitas';$dezen='MPT 06';$trideni='zemědělské';break;}
		case '0020010657':{$znacka='Mitas';$dezen='MPT 07';$trideni='zemědělské';break;}
		case '0020010658':{$znacka='Mitas';$dezen='MPT 08';$trideni='zemědělské';break;}
		case '0020010670':{$znacka='Mitas';$dezen='MPT 20';$trideni='zemědělské';break;}
		case '0020010671':{$znacka='Mitas';$dezen='MPT 21';$trideni='zemědělské';break;}
		case '0020010672':{$znacka='Mitas';$dezen='MPT 22';$trideni='zemědělské';break;}
		case '0020010680':{$znacka='Mitas';$dezen='ERL 20';$trideni='zemědělské';break;}
		case '0020010681':{$znacka='Mitas';$dezen='ERL 30';$trideni='zemědělské';break;}
		case '0020010682':{$znacka='Mitas';$dezen='ERD 20';$trideni='zemědělské';break;}
		case '0020010683':{$znacka='Mitas';$dezen='ERD 30';$trideni='zemědělské';break;}
		case '0020010685':{$znacka='Mitas';$dezen='CR 01';$trideni='zemědělské';break;}
		case '0020010690':{$znacka='Mitas';$dezen='EM 01';$trideni='zemědělské';break;}
		case '0020010691':{$znacka='Mitas';$dezen='EM 02';$trideni='zemědělské';break;}
		case '0020010701':{$znacka='Mitas';$dezen='IM 03';$trideni='zemědělské';break;}
		case '0020010702':{$znacka='Mitas';$dezen='IM 01';$trideni='zemědělské';break;}
		case '0020010703':{$znacka='Mitas';$dezen='IM 07';$trideni='zemědělské';break;}
		case '0020010704':{$znacka='Mitas';$dezen='IM 04';$trideni='zemědělské';break;}
		case '0020010705':{$znacka='Mitas';$dezen='IM 09';$trideni='zemědělské';break;}
		case '0020010706':{$znacka='Mitas';$dezen='IM 02';$trideni='zemědělské';break;}
		case '0020010720':{$znacka='Mitas';$dezen='B 1';$trideni='zemědělské';break;}
		case '0020010721':{$znacka='Mitas';$dezen='S 03';$trideni='zemědělské';break;}
		case '0020010722':{$znacka='Mitas';$dezen='B 5';$trideni='zemědělské';break;}
		case '0020010751':{$znacka='Mitas';$dezen='AR 01';$trideni='zemědělské';break;}
		case '0020010752':{$znacka='Mitas';$dezen='Agriterra';$trideni='zemědělské';break;}
		case '0020010801':{$znacka='Mitas';$dezen='FL 01';$trideni='zemědělské';break;}
		case '0020010802':{$znacka='Mitas';$dezen='FL 02';$trideni='zemědělské';break;}
		case '0020010803':{$znacka='Mitas';$dezen='FL 03';$trideni='zemědělské';break;}
		case '0020010804':{$znacka='Mitas';$dezen='FL 04';$trideni='zemědělské';break;}
		case '0020010805':{$znacka='Mitas';$dezen='FL 05';$trideni='zemědělské';break;}
		case '0020010806':{$znacka='Mitas';$dezen='FL 06';$trideni='zemědělské';break;}
		case '0020010807':{$znacka='Mitas';$dezen='FL 07';$trideni='zemědělské';break;}
		case '0020010808':{$znacka='Mitas';$dezen='FL 08';$trideni='zemědělské';break;}
		
		case '002002':{$znacka='Conti';$dezen='';$trideni='zemědělské';break;}
		case '0020020001':{$znacka='Conti';$dezen='AC 85';$trideni='zemědělské';break;}
		case '0020020002':{$znacka='Conti';$dezen='AC 70G';$trideni='zemědělské';break;}
		case '0020020003':{$znacka='Conti';$dezen='AC 70T';$trideni='zemědělské';break;}
		case '0020020004':{$znacka='Conti';$dezen='AC 65';$trideni='zemědělské';break;}
		case '0020020005':{$znacka='Conti';$dezen='AC 90 C';$trideni='zemědělské';break;}
		case '0020020050':{$znacka='Conti';$dezen='AS Farmer';$trideni='zemědělské';break;}
		case '0020020051':{$znacka='Conti';$dezen='SVT';$trideni='zemědělské';break;}
		case '0020020052':{$znacka='Conti';$dezen='SST';$trideni='zemědělské';break;}
		
		case '002003':{$znacka='Cultor';$dezen='';$trideni='zemědělské';break;}
		case '0020030001':{$znacka='Cultor';$dezen='AS Agri 13';$trideni='zemědělské';break;}
		case '0020030002':{$znacka='Cultor';$dezen='AS Agri 19';$trideni='zemědělské';break;}
		case '0020030007':{$znacka='Cultor';$dezen='AS Agri 7';$trideni='zemědělské';break;}
		case '0020030010':{$znacka='Cultor';$dezen='AS Agri 10';$trideni='zemědělské';break;}
		case '0020030501':{$znacka='Cultor';$dezen='Radial-S';$trideni='zemědělské';break;}
		case '0020030502':{$znacka='Cultor';$dezen='Radial-70';$trideni='zemědělské';break;}
		
		case '003001':{$znacka='Trelleborg';$dezen='';$trideni='zemědělské';break;}
		case '0030010401':{$znacka='Trelleborg';$dezen='T 410 AGF';$trideni='zemědělské';break;}
		case '0030010501':{$znacka='Trelleborg';$dezen='tm 700';$trideni='zemědělské';break;}
		case '0030010502':{$znacka='Trelleborg';$dezen='tm 700 HS';$trideni='zemědělské';break;}
		case '0030010503':{$znacka='Trelleborg';$dezen='tm 600';$trideni='zemědělské';break;}
		case '0030010504':{$znacka='Trelleborg';$dezen='tm 800';$trideni='zemědělské';break;}
		case '0030010505':{$znacka='Trelleborg';$dezen='tm 800 HS';$trideni='zemědělské';break;}
		case '0030010506':{$znacka='Trelleborg';$dezen='tm 900 HP';$trideni='zemědělské';break;}
		case '0030010701':{$znacka='Trelleborg';$dezen='AW 305';$trideni='zemědělské';break;}
		case '0030010702':{$znacka='Trelleborg';$dezen='T510';$trideni='zemědělské';break;}
		case '0030010703':{$znacka='Trelleborg';$dezen='T539 Grip';$trideni='zemědělské';break;}
		case '0030010704':{$znacka='Trelleborg';$dezen='T404';$trideni='zemědělské';break;}
		
		case '004001':{$znacka='Firestone';$dezen='';$trideni='zemědělské';break;}
		case '0040010001':{$znacka='Firestone';$dezen='Maxi Traction';$trideni='zemědělské';break;}
		case '0040010002':{$znacka='Firestone';$dezen='RATDT';$trideni='zemědělské';break;}
		case '0040010004':{$znacka='Firestone';$dezen='R 4000';$trideni='zemědělské';break;}
		case '0040010006':{$znacka='Firestone';$dezen='R 6000';$trideni='zemědělské';break;}
		case '0040010007':{$znacka='Firestone';$dezen='R 7000';$trideni='zemědělské';break;}
		case '0040010008':{$znacka='Firestone';$dezen='R 8000 ';$trideni='zemědělské';break;}
		case '0040010009':{$znacka='Firestone';$dezen='R 9000 EVO';$trideni='zemědělské';break;}
		case '0040010010':{$znacka='Firestone';$dezen='Performer 85';$trideni='zemědělské';break;}
		case '0040010011':{$znacka='Firestone';$dezen='Performer 70';$trideni='zemědělské';break;}
		case '0040010012':{$znacka='Firestone';$dezen='R 1085';$trideni='zemědělské';break;}
		case '0040010013':{$znacka='Firestone';$dezen='R 1070';$trideni='zemědělské';break;}
		case '0040010801':{$znacka='Firestone';$dezen='Dura UT';$trideni='zemědělské';break;}
		case '0040010802':{$znacka='Firestone';$dezen='R 8000 UT';$trideni='zemědělské';break;}
		case '0040010851':{$znacka='Firestone';$dezen='STL';$trideni='zemědělské';break;}
		case '0040010852':{$znacka='Firestone';$dezen='Dura FHD';$trideni='zemědělské';break;}
		case '0040010853':{$znacka='Firestone';$dezen='Dura FDT';$trideni='zemědělské';break;}
		
		case '005001':{$znacka='Nokian';$dezen='';$trideni='zemědělské';break;}
		case '00500100001':{$znacka='Nokian';$dezen='Forest TR FS';$trideni='zemědělské';break;}
		case '0050010901':{$znacka='Nokian';$dezen='TRI 2';$trideni='zemědělské';break;}
		
		case '009001':{$znacka='Good Year';$dezen='';$trideni='zemědělské';break;}
		case '0090010001':{$znacka='Good Year';$dezen='SUP TG 2';$trideni='zemědělské';break;}		
		default: $znacka='';$dezen='';$trideni='';						
	}
	
	if(empty($znacka)){
			switch(substr($string,0,7)){
				case '0010011':{$znacka='Michelin';$dezen='';$trideni='osobní letní';break;}
				case '0010012':{$znacka='Michelin';$dezen='';$trideni='osobní zimní';break;}
				case '0010013':{$znacka='Michelin';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0010015':{$znacka='Michelin';$dezen='';$trideni='nákladní';break;}
				case '0010019':{$znacka='Michelin';$dezen='';$trideni='stavební';break;}
				
				case '0010021':{$znacka='Kleber';$dezen='';$trideni='osobní letní';break;}
				case '0010022':{$znacka='Kleber';$dezen='';$trideni='osobní zimní';break;}
				case '0010023':{$znacka='Kleber';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0010025':{$znacka='Kleber';$dezen='';$trideni='nákladní';break;}
				case '0010029':{$znacka='Kleber';$dezen='';$trideni='stavební';break;}
				            
				case '0010031':{$znacka='BF Goodrich';$dezen='';$trideni='osobní letní';break;}
				case '0010032':{$znacka='BF Goodrich';$dezen='';$trideni='osobní zimní';break;}
				case '0010033':{$znacka='BF Goodrich';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0010035':{$znacka='BF Goodrich';$dezen='';$trideni='nákladní';break;}
				case '0010039':{$znacka='BF Goodrich';$dezen='';$trideni='stavební';break;}
				
				case '0010041':{$znacka='Taurus';$dezen='';$trideni='osobní letní';break;}
				case '0010042':{$znacka='Taurus';$dezen='';$trideni='osobní zimní';break;}
				case '0010043':{$znacka='Taurus';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0010045':{$znacka='Taurus';$dezen='';$trideni='nákladní';break;}
				case '0010049':{$znacka='Taurus';$dezen='';$trideni='stavební';break;}
				
				case '0010051':{$znacka='Kormoran';$dezen='';$trideni='osobní letní';break;}
				case '0010052':{$znacka='Kormoran';$dezen='';$trideni='osobní zimní';break;}
				case '0010053':{$znacka='Kormoran';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0010055':{$znacka='Kormoran';$dezen='';$trideni='nákladní';break;}
				case '0010059':{$znacka='Kormoran';$dezen='';$trideni='stavební';break;}
				
				case '0020011':{$znacka='Mitas';$dezen='';$trideni='osobní letní';break;}
				case '0020012':{$znacka='Mitas';$dezen='';$trideni='osobní zimní';break;}
				case '0020013':{$znacka='Mitas';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0020015':{$znacka='Mitas';$dezen='';$trideni='nákladní';break;}
				case '0020019':{$znacka='Mitas';$dezen='';$trideni='stavební';break;}
				
				case '0020021':{$znacka='Conti';$dezen='';$trideni='osobní letní';break;}
				case '0020022':{$znacka='Conti';$dezen='';$trideni='osobní zimní';break;}
				case '0020023':{$znacka='Conti';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0020025':{$znacka='Conti';$dezen='';$trideni='nákladní';break;}
				case '0020029':{$znacka='Conti';$dezen='';$trideni='stavební';break;}
				
				case '0020031':{$znacka='Cultor';$dezen='';$trideni='osobní letní';break;}
				case '0020032':{$znacka='Cultor';$dezen='';$trideni='osobní zimní';break;}
				case '0020033':{$znacka='Cultor';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0020035':{$znacka='Cultor';$dezen='';$trideni='nákladní';break;}
				case '0020039':{$znacka='Cultor';$dezen='';$trideni='stavební';break;}
				
				case '0030011':{$znacka='Trelleborg';$dezen='';$trideni='osobní letní';break;}
				case '0030012':{$znacka='Trelleborg';$dezen='';$trideni='osobní zimní';break;}
				case '0030013':{$znacka='Trelleborg';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0030015':{$znacka='Trelleborg';$dezen='';$trideni='nákladní';break;}
				case '0030019':{$znacka='Trelleborg';$dezen='';$trideni='stavební';break;}
				
				case '0040011':{$znacka='Firestone';$dezen='';$trideni='osobní letní';break;}
				case '0040012':{$znacka='Firestone';$dezen='';$trideni='osobní zimní';break;}
				case '0040013':{$znacka='Firestone';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0040015':{$znacka='Firestone';$dezen='';$trideni='nákladní';break;}
				case '0040019':{$znacka='Firestone';$dezen='';$trideni='stavební';break;}
				
				case '0050011':{$znacka='Nokian';$dezen='';$trideni='osobní letní';break;}
				case '0050012':{$znacka='Nokian';$dezen='';$trideni='osobní zimní';break;}
				case '0050013':{$znacka='Nokian';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0050015':{$znacka='Nokian';$dezen='';$trideni='nákladní';break;}
				case '0050019':{$znacka='Nokian';$dezen='';$trideni='stavební';break;}
				
				case '0090011':{$znacka='Good Year';$dezen='';$trideni='osobní letní';break;}
				case '0090012':{$znacka='Good Year';$dezen='';$trideni='osobní zimní';break;}
				case '0090013':{$znacka='Good Year';$dezen='';$trideni='4x4 a celoroční';break;}
				case '0090015':{$znacka='Good Year';$dezen='';$trideni='nákladní';break;}
				case '0090019':{$znacka='Good Year';$dezen='';$trideni='stavební';break;}
			}	
	}
	
	return array('znacka'=>$znacka,'dezen'=>$dezen,'trideni'=>$trideni);
	
} 


 
  
  
function send($to,$message,$subject,$from) {

	$headers = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=utf-8\n";

	$headers .= "From: ".$from."\n";
	
	
	// vyhazeme diakritiku z predmetu - nektere servery jinak oznaci zpravu jako spam
	$subject = strtr($subject, "áäčďéěëíňóöřą»úůüýľÁÄČĎÉĚËÍŇÓÖŘ©«ÚŮÜÝ®", "aacdeeeinoorstuuuyzAACDEEEINOORSTUUUYZ");
	
	$odeslano=@mail($to,$subject,$message,$headers);
	
	return $odeslano;
}    




function getZboziInfo($conn,$column,$id){
	$query='select '.$column.' from fla_shop_zbozi where id='.$id;
	
	$v=$conn->query($query);
	
	$column=$v->fetch_object()->$column;
	
	return $column;
}






function addUpZbozi($zbozi,$conn){
	$zbozi['vahamj']=$zbozi['vahamj']*1000;
	
	if($zbozi['smj']>0){
		$id_dodani='13';
	}else{
		$id_dodani='12';	
	}
	
	$v=$conn->query("select id from fla_shop_zbozi where kod='".$zbozi['cislo']."'");
	
		$id_vyrobce='';
		
		if(!empty($zbozi['klasifikace3'])){
		
			switch(substr($zbozi['klasifikace3'],0,3)){
				case '001':{
						$id_vyrobce=4;			
						break;
						}
				case '002':{
						$id_vyrobce=11;			
						break;
						}
				case '003':{
						$id_vyrobce=7;			
						break;
						}
				case '004':{
						$id_vyrobce=20;			
						break;
						}
				case '005':{
						$id_vyrobce=12;			
						break;
						}
				case '009':{
						$id_vyrobce=19;			
						break;
						}																														
			}
		
// 001            Michelin
// 002            Mitas
// 003            Trelleborg
// 004            Bridgestone
// 005            Nokian
// 009           Good Year	
		}
	
	if($v->num_rows>0){
		
		$id=$v->fetch_object()->id;
		$query="update fla_shop_zbozi set name='".$zbozi['nazev']."',dop_cena='".$zbozi['dcena']."',
			cena='".$zbozi['cena']."',id_vyrobce='$id_vyrobce',id_dodani='$id_dodani',hmotnost='".$zbozi['vahamj']."', pocet_kusu='".$zbozi['smj']."' ,hidden=0,xml=1 where id=$id";
	 	          
		$conn->query($query);
		
		return $id;
		
	}else{     
	  $hidden = 0;
		$query="insert into fla_shop_zbozi(kod,name,dop_cena,cena,dph,hmotnost,lang,hidden,id_vyrobce,id_dodani,xml,pocet_kusu) 
				values('".$zbozi['cislo']."','".$zbozi['nazev']."','".$zbozi['dcena']."','".$zbozi['cena']."',21,'".$zbozi['vahamj']."',1,$hidden,'$id_vyrobce','$id_dodani',1,'".$zbozi['smj']."')";
	 	$conn->query($query);
	 	
	 	return $conn->insert_id; 
	}
	
	return null;	
	
}


function addZboziToCategory($id,$catid,$conn)
{
  $_SESSION["i_e"]["addZboziToCategory"][$id][] = $catid; // Pamatuju si do kterých kategorií jsem produkt přiřazoval.
  // Ostatní zařazení do kategorie smažu.
  $conn->query("delete from fla_shop_zbozi_x_kategorie where id_cat not in (".implode(",", $_SESSION["i_e"]["addZboziToCategory"][$id]).") and id_good = $id");

	if($conn->query("select * from fla_shop_zbozi_x_kategorie where id_cat=$catid and id_good=$id")->num_rows>0) return null;
	else return $conn->query("insert into fla_shop_zbozi_x_kategorie(id_good,id_cat,lang) values($id,$catid,1)");	
}




function startTag($parser, $data, $atribs){
    global $current_tag, $counter, $zbozi_array;
    $current_tag .= "*$data";     
}

function endTag($parser, $data){
    global $current_tag, $counter;
    $tag_key = strrpos($current_tag, '*');
    $current_tag = substr($current_tag, 0, $tag_key);
    if($current_tag=="*SHOP"){
      $counter++;
    }
}


//******************************************************************************
// upload a vytvareni kopii obrazku
// *****************************************************************************
function img_resize($orig,$kopie,$width_max,$height_max,$kompr,$sledovany_rozm) 
{
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
		
	// kontroluje max. sirku i vysku, neni prekrocen zadny z techto rozmeru
	if($sledovany_rozm == "b") {
	
		if($width_max > $height_max) {
		
			// max. sirka > max. vyska
			$k_width = $width_max / $height_max;
			$k_height = 1;

		} elseif($height_max > $width_max) {
		
			// max. vyska > max. sirka
			$k_width = 1;
			$k_height = $height_max / $width_max;

		} else {
		
			$k_width = 1;
			$k_height = 1;
		}
		
		
		if($rozm[0] < $width_max && $rozm[1] < $height_max) {
		
			// sirka a vyska orig. jsou mensi nez max. hodnoty - nechame puvodni rozmery
			$width = $rozm[0];
			$height = $rozm[1];
		
		} elseif($rozm[0] / $k_width > $rozm[1] / $k_height) {
		
			// šířka orig. je větší než max. výška
			$width = $width_max;
			$k = $rozm[0] / $width_max;
			$height = ceil($rozm[1] / $k);
		
		} elseif($rozm[0] / $k_width < $rozm[1] / $k_height) { 
		
			// pokud je výška větší než max. šířka
			$height = $height_max;
			$k = $rozm[1] / $height_max;
			$width = ceil($rozm[0] / $k);
		
		} else {
		
			if($width_max > $height_max) {
			
				$width = $height_max * $k_width;
				$height = $height_max * $k_height;
			
			} else {
			
				$width = $width_max * $k_width;
				$height = $width_max * $k_height;
			
			}
		
		}
	
	}
	

	$out = imagecreatetruecolor($width,$height);
	
	imagecopyresampled($out,$in,0,0,0,0,$width,$height,$rozm[0],$rozm[1]);

	// 2 = JPG, 3 = PNG
	if($rozm[2] == 2) imagejpeg($out,$kopie,$kompr); // jpg
	if($rozm[2] == 3) imagepng($out,$kopie); // png
	
	imagedestroy($in);
	imagedestroy($out);
}


function img_upload($jmeno_puvodni , $obr , $jmeno_nove) 
{ // pro vkladani obrazku vyrobku
	// uploadovany obrazek (original) je umisten do adresare $dir_orig
	// jsou vytvoreny kopie o max. povolenych rozmerech
	// potrebujeme-li zmenit nazev (napr. podle ID produktu, time() ...), uvedeme 
	// jej do promenne $nm, jinak zustane stejny
	
	
	// ***************************************************************************
	// nastaveni pocet kopii, cesty, max. rozmery
	// ***************************************************************************
	$original_dir = "../UserFiles/products/original/"; // cesta pro original (zaloha?)
	
	$kopie_dir[] = "../UserFiles/products/small/"; // cesta pro nahled
	$kopie_dir[] = "../UserFiles/products/middle/"; // cesta pro detailni obrazek
	
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
	$r = getimagesize($obr);
	
	$w_orig = $r[0]; // sirka originalu
	$h_orig = $r[1]; // sirka originalu
	$typ = $r[2]; // typ souboru

	// kontrola typu souboru - povolime jen jpg, png
	if($typ != 2 && $typ != 3) 
  {
		/*$_SESSION['alert_js'] = "Nesprávný formát obrázku. ";
		$_SESSION['alert_js'] .= "Použijte formát JPG nebo PNG.\\n\\n";
		$_SESSION['alert_js'] .= "Nic nebylo uloženo.";
		Header("Location: ".$_SERVER['HTTP_REFERER']);*/
		echo "Nesprávný formát obrázku.";
		exit;
	} 
  else 
  { // zjistime priponu
		$x1 = explode (".", $jmeno_puvodni); // roztrhame nazev souboru - delicem je tecka
		$x2 = count($x1) - 1; // index posledniho prvku pole
		$e = strtolower($x1[$x2]); // mame priponu (vkladame take do DB, proto return)
	}

  $file_name = $jmeno_nove.".".$e; // menime nazev obrazku
	$original = $original_dir.$file_name;

	// umistime original souboru
  //move_uploaded_file($obr , $original);
  //copy($obr , $original);
	
	for($x = 0; $x < count($kopie_dir); $x++) 
  {	
		// kontrola rozmeru:
		// b = oba, w = sirka, h = vyska
		if($x == 0) $sledovany_rozm = "b"; // nahled
		else $sledovany_rozm = "w"; // detail
		
		img_resize($obr,$kopie_dir[$x].$file_name,$w_max[$x],$h_max[$x],$komprese[$x],$sledovany_rozm);
	}
	
	return $e;
}


unset($_SESSION["i_e"]); // Sessin pomocná proměná při importu. Pro další import musí být čistá.


?>