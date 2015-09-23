<?php
// Nové řešení zpusobu dopravy a plateb - napojime vzdy (doprava - platba) bude jeden option u selectboxu. 

// klasicke reseni dopravy ano/ne - pokud ne, mame zapnutou alternativni dopravu - bere se z jiné tabulky, cena neznámá
define('DOPRAVNE_CLASSIC',true);


// VOLBY
// radio - vypis dopravy do radio buttonu
// select - vypis dopravy do selectboxu
define('DOPRAVA_STYLE','select'); 


//def doprava pri alternativnim postovnem
define('DOPRAVA_ALTER_DEF',1);


define('DISABLED_DOPRAVCE',serialize(array(1=>array(1,5,17,18,19),21=>1))); //asociativni pole kdy urcijeme, ze pokud zbozi je v kategorii nebo jeji podkategorii, vypneme zobrazeni urciteho dopravce
// id_kategorie=>id_dopravce


//def doprava pri klasickem postovnem
define('DOPRAVA_DEF',1);
//def platba pri klasickem postovnem
define('PLATBA_DEF',1);
//dph postovneho a dopravy pri klasickem postovnem
define('POSTOVNE_DPH',21);






// *****************************************************************************
// zpracovani obsahu kosiku a objednavky
// *****************************************************************************


// polozky v kosiku jsou ukladany prostrednictvim SESSION
// do kosiku je ulozeno zbozi predanim ID pomoci GET - je pripocten 1 ks pri 
// kazdem predani ID


// oznaceni pole - kvuli pouzivani stejneho formu pro registraci a objednavku bez registrace
$PFX = 'order_user';

$title = "Nákupní košík";
$H1 = $title;

	
$js = "
		<script language=\"javascript\">
		function resetorder(url) {
		
			if (confirm('Opravdu chcete vyprázdnit nákupní košík?')) location = url;
		
		}
		</script>";






// *****************************************************************************
// reset kosiku
// *****************************************************************************
if(isset($_GET['resetorder'])) {

	unset($_SESSION['sbaskets']);
	unset($_SESSION['hmotnost']);
	unset($_SESSION['basket_total']);
	unset($_SESSION['basket_doprava']);
	unset($_SESSION['basket_doprava_alter']);
  unset($_SESSION['basket_platba']);
  unset($_SESSION['basket_priplatek']);
 	unset($_SESSION['basket_suma']);
	
	Header("Location: ".HTTP_ROOT."/nakupni-kosik/");
	exit;
}
// *****************************************************************************
// reset kosiku
// *****************************************************************************







// *****************************************************************************
// pridani polozky do kosiku
// *****************************************************************************
$nelze=0;
if(!empty($_GET['addId']) || !empty($_POST['addId'])) {
  

	if(!empty($_POST['addVar'])) $var = $_POST['addVar'];
	else $var = 0;
	
	
	
	if(!empty($_GET['addId'])) {
		$addId = $_GET['addId'];
		$ks = 1;
	}
	
	if(!empty($_POST['addId'])) {
		$addId = $_POST['addId'];
		$ks = round($_POST['addKs']);
	}

  //CV:zjistime pocet kusu v databazi 
  $query = "SELECT pocet_kusu FROM ".T_GOODS."
		WHERE id = $addId AND ".SQL_C_LANG;//AND hidden = 0 
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
  $z = mysql_fetch_assoc($v);
  $dbpocetks=$z["pocet_kusu"];	
	
	$addId = $addId.'-'.$var;
	
	
	
	if(!empty($_SESSION['sbaskets'][$addId]))  $baskks = $_SESSION['sbaskets'][$addId];
	else $baskks = 0;

  $nbaskks=$baskks + $ks;
  if ($nbaskks<=$dbpocetks) {
    $_SESSION['sbaskets'][$addId] = $nbaskks;
    $alert="Produkt byl přidán do košíku.";
  }
  else {
    $strmaxks=$dbpocetks." ".strkusy($dbpocetks);
    $alert="Zboží nelze objednat! Požadovaný počet kusů není skladem.";
    if (intval($strmaxks)>0) $alert.="Objednat můžete maximálně $strmaxks zboží.";
    $nelze=1;
  } 

  $polozek=0;



	while($pb = each($_SESSION['sbaskets'])) {
	
		list($basket_id,$var_id) = explode ("-", $pb['key']);
		
		$pocet = $pb['value'];
    $polozek=$polozek+$pocet;
  }
    
	$_SESSION['basket_suma']=$polozek;
	$action = 1; // po prepocitani obsahu nasleduje reload
	
	$_SESSION['alert_js1'] = $alert;
  
}
// *****************************************************************************
// pridani polozky do kosiku
// *****************************************************************************



if ($nelze==1) {
			Header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;

}



// *****************************************************************************
// prepocitani cen v kosiku
// *****************************************************************************
if(!empty($_POST['ks'])) {
$polozek=0;
	while ($pb = each($_POST['ks'])) {
	
		$ks = round($pb['value']);
	  $polozek=$polozek+$ks;
    
		if($ks > 0) {
      $addId = $pb['key']; 
      $query = "SELECT pocet_kusu FROM ".T_GOODS."
    		WHERE id = $addId AND ".SQL_C_LANG;//AND hidden = 0 
    	$v = my_DB_QUERY($query,__LINE__,__FILE__);
      $z = mysql_fetch_assoc($v);
      $dbpocetks=$z["pocet_kusu"];
      
      if ($dbpocetks>=$ks) $_SESSION['sbaskets'][$addId] = $ks;
      else {
      $strmaxks=$dbpocetks." ".strkusy($dbpocetks);
      $alert="Košík nelze přepočítat! Požadovaný počet kusů není skladem.";
      $alert.="Objednat můžete maximálně $strmaxks zboží.";
      $_SESSION['alert_js1'] = $alert;
      }
      }
		else
			unset($_SESSION['sbaskets'][$pb['key']]);
	
	}
	
	$action = 2; // po prepocitani obsahu nasleduje reload
  $_SESSION['basket_suma']=$polozek;
}
// *****************************************************************************
// prepocitani cen v kosiku
// *****************************************************************************






// *****************************************************************************
// kontrola udaju pred odeslanim objednavky
// *****************************************************************************
if(!empty($_POST['order'])) {
   
	include_once 'form_user_control.php';

}
// *****************************************************************************
// kontrola udaju pred odeslanim objednavky
// *****************************************************************************







// *****************************************************************************
// polozky v kosiku - pro kosik i objednavku, prepocitani obsahu po pridani ks do kosiku
// *****************************************************************************
if(!empty($_SESSION['sbaskets'])) {
	
	$soucty=array('zaklad'=>'','dph'=>'','cenovka'=>'');
	$polozky1=null;
	$polozky2=null;
	$hmotnost_cr = 0;
	$hmotnost = 0;
	$dopravciOmezeni='';
     $arrayDopravciVypnout=array();

	reset($_SESSION['sbaskets']);
	
	$doprava_zdarma = 1; 
	
	while($pb = each($_SESSION['sbaskets'])) {

		list($basket_id,$var_id) = explode ("-", $pb['key']);
		
		$pocet = $pb['value'];

		
		// id id_cat name img text hidden akce cena dph lang kod id_vyrobce
		$query = "SELECT id, name, hmotnost, kod, cena , cena_eshop, dph, id_vyrobce, pocet_kusu FROM ".T_GOODS."
		WHERE id = $basket_id 
		
		AND ".SQL_C_LANG." 
		LIMIT 0,1";//AND hidden = 0 
		
		/* kontrola zda jsou v kosiku jen produkty na ktere se doprava zdarma vztahuje */
		$id_kategorie = zjisti_kategorii($basket_id);
        $id_parent = nejvissi_kategorie($id_kategorie);

    if($id_parent != 29 ) // 29 = pneumatiky
    {  // v kosiku je i zbozi ktere na ktere se doprava zdarma nevstahuje (dopravu budeme pocitat)
      $doprava_zdarma = 0;
    }   
    /* konec */
       	 	
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		while($z = mysql_fetch_assoc($v))
    {
			$basket_name = $z['name'];
			$basket_kod = $z['kod'];
			$basket_cena = $z['cena'];
      if(isset($z['cena_eshop']) AND $z['cena_eshop'] > 0) $basket_cena = $z['cena_eshop'];
			$basket_dph = $z['dph'];
			$id_vyrobce = $z['id_vyrobce'];
			$pocet_kusu = $z["pocet_kusu"];
      			
      if($id_parent != 29 )
      { 
			  $hmotnost_cr=$hmotnost_cr+($pocet*$z['hmotnost']); //hmotnost pro čr
      }
      $hmotnost=$hmotnost+($pocet*$z['hmotnost']); //hmotnost pro slovensko nebo jinam
      
      
      
			// kod produktu
			if(!empty($z['kod'])) $Bkod = $z['kod'].'';
			else $Bkod = '';
			
			
			// generujeme ceny - fce vraci pole s ruznymi tvary cen
			$ceny = ceny2($basket_cena, $basket_dph, $pocet, $id_vyrobce, $basket_id);
			$soucty = ceny_soucty($soucty,$ceny[2],$ceny[6],$ceny[4]);

      $arrayDopravciVypnout = zakazatDopravce($basket_id,$arrayDopravciVypnout);

			if($pocet_kusu == 0)
			{
        $neni_skladem = '<p style="color:red; font-weight:bold; font-size:90%; padding-top:6px;">(není skladem o stavu vás budeme informovat)</p>';
      }
			else if($pocet_kusu != -1 AND $pocet > $pocet_kusu)
			{ 
        $chybi = $pocet - $pocet_kusu;
        
        if($chybi == 1) $kus = "kus není";
        else if($chybi < 5) $kus = "kusy nejsou";
        else $kus = "kusů není"; 
        
        $neni_skladem = '<p style="color:red; font-weight:bold; font-size:90%; padding-top:6px;">('.$chybi.' '.$kus.' skladem o stavu vás budeme informovat)</p>';
      }
      else
      {
        $neni_skladem = "";
      }
						
			// pro kosik
			$polozky1 .= "
					<tr>
						<td class='ks'><input type=\"text\" 
								name=\"ks[$basket_id-$var_id]\" value=\"$pocet\" /></td>
						<td><a href=\"/produkt/$basket_id-".text_in_url($basket_name." ".$basket_kod)."/\">
								$basket_name $basket_kod</a>$neni_skladem</td>
						<td class='dph'>".$ceny[$ceny['K1']]."</td>
						<td class='cena'>".$ceny[$ceny['K2']]."</td>
						<td class='cena'>".$ceny[$ceny['K3']]."</td>
						<td class='cena'>".number_format($ceny[$ceny['K4']] * $pocet, 2,","," ")."</td>
					</tr>";
			
			
			// pro objednavku
			$polozky2 .= "
					<tr>
						<td valign=\"top\">$pocet</td>
						<td valign=\"top\" style=\"white-space:nowrap\" nowrap>$Bkod&nbsp;</td>
						<td valign=\"top\" width=\"320px\">$basket_name&nbsp;</td>
						<td align=\"right\" valign=\"top\" style=\"white-space:nowrap\" nowrap>".$ceny[$ceny['K1']]."&nbsp;</td>
						<td align=\"right\" valign=\"top\" style=\"white-space:nowrap\" nowrap>".$ceny[$ceny['ks_bez_dph']]."&nbsp;</td>
						<td align=\"right\" valign=\"top\" style=\"white-space:nowrap\" nowrap>".$ceny[$ceny['K3']]."</td>
						<td align=\"right\" valign=\"top\">".number_format($ceny[$ceny['K4']] * $pocet, 2, ",", " ")."</td>
					</tr>";
			
			// id_obj  id_produkt  nazev_produkt  cena  dph  ks
			$INSERTS[] = "INSERT INTO ".T_ORDERS_PRODUCTS." 
			VALUES (#id_obj#,$basket_id,'$basket_name',".$ceny[$ceny['K4']].",$basket_dph,$pocet,'$Bkod',".C_LANG.")";

      // Doplnění informací o produktu pro Heuréku.
      $_SESSION["heureka"]["products"][$basket_id]["name"] = $basket_name;
      $_SESSION["heureka"]["products"][$basket_id]["ceny"] = $ceny;
      $_SESSION["heureka"]["products"][$basket_id]["ks"] = $pocet;
		
		}
	}
	
	
	 
	
	
	$sDPHx = $soucty['zaklad'] + $soucty['dph'];
	
	
	

if(DOPRAVNE_CLASSIC){	
	// RESIME POSTOVNE 
	
	foreach($arrayDopravciVypnout as $key=>$value){
		$dopravciOmezeni.=' and '.T_DOPRAVA.'.id!='.$key;
	}
	
	if(!empty($dopravciOmezeni)){
		$dopravciOmezeni = substr($dopravciOmezeni,4,strlen($dopravciOmezeni));
		$dopravciOmezeni='and ('.$dopravciOmezeni.')';
	}
	
		  
	//zkontrolujeme, zda se pri pridani/odebrani apod nezmenily dostupne typy dopravy.
	$zmenit=0;
	
	if(!empty($_SESSION['basket_doprava'])){

		$query='select '.T_DOPRAVA.'.id as id
					from '.T_DOPRAVA.'
					where '.T_DOPRAVA.'.hidden=0 and ((
					'.T_DOPRAVA.'.vaha_od<='.$hmotnost_cr.' and 
					('.T_DOPRAVA.'.vaha_do>='.$hmotnost_cr. ' or '.T_DOPRAVA.'.vaha_do=0) AND '.T_DOPRAVA.'.id_stat = 1) OR 
					(
					'.T_DOPRAVA.'.vaha_od<='.$hmotnost.' and 
					('.T_DOPRAVA.'.vaha_do>='.$hmotnost. ' or '.T_DOPRAVA.'.vaha_do=0) AND '.T_DOPRAVA.'.id_stat > 1))
					'.$dopravciOmezeni." 
					and id=".$_SESSION['basket_doprava'];
					
					
		$v=my_DB_QUERY($query,__LINE__,__FILE__);
		
		if(mysql_num_rows($v)==0)$zmenit=1;
			
	}
	
	if((isset($arrayDopravciVypnout[DOPRAVA_DEF]) && empty($_SESSION['basket_doprava'])) || (!empty($_SESSION['basket_doprava']) && isset($arrayDopravciVypnout[$_SESSION['basket_doprava']])) || $zmenit==1){
	//kdyz je defaultni dopravce zakazan vybranym zbozim, vybereme prvniho volneho dopravce	
		$query='select '.T_DOPRAVA.'.id as id, '.T_DOPRAVA.'.id_stat as id_stat
					from '.T_DOPRAVA.'
					where '.T_DOPRAVA.'.hidden=0 and (
					'.T_DOPRAVA.'.vaha_od<='.$hmotnost_cr.' and (
					('.T_DOPRAVA.'.vaha_do>='.$hmotnost_cr. ' or '.T_DOPRAVA.'.vaha_do=0) AND '.T_DOPRAVA.'.id_stat = 1) OR 
					(
					'.T_DOPRAVA.'.vaha_od<='.$hmotnost.' and 
					('.T_DOPRAVA.'.vaha_do>='.$hmotnost. ' or '.T_DOPRAVA.'.vaha_do=0) AND '.T_DOPRAVA.'.id_stat > 1))
					'.$dopravciOmezeni." 
          order by id_stat,poradi
					limit 0,1";		
			
		$v=my_DB_QUERY($query,__LINE__,__FILE__);
		
		$z=mysql_fetch_array($v);
				
		$_SESSION['basket_doprava']=$z['id'];

	}
  else
  {
		if(empty($_SESSION['basket_doprava'])) $_SESSION['basket_doprava']=DOPRAVA_DEF;
	}
	
	if(empty($_SESSION['basket_platba']))$_SESSION['basket_platba']=PLATBA_DEF;
	
	
	if(!empty($_POST['dopravne']))
  {
		$dopravne=explode('#',$_POST['dopravne']);
		
		$_SESSION['basket_doprava']=$dopravne[0];
		$_SESSION['basket_platba']=$dopravne[1];
        
        	
	}
	
	$aktstat = null;
	$query = 'SELECT id_stat FROM '.T_DOPRAVA.' WHERE id = '.$_SESSION['basket_doprava'].' LIMIT 1';
    $v = my_DB_QUERY($query,__LINE__,__FILE__);
    while($z = mysql_fetch_array($v))
    {
        $aktstat = $z['id_stat'];
    }
    $arrayStaty = unserialize(STATY);
    $aktStatNazev = $arrayStaty[$aktstat];
    
/*
	$query='select * from '.T_DOPRAVA.' where id='.$_SESSION['basket_doprava'];
	$v=my_DB_QUERY($query,__LINE__,__FILE__);
	$doprava=mysql_fetch_array($v);

	$query='select * from '.T_PLATBA.' where id='.$_SESSION['basket_platba'];
	$v=my_DB_QUERY($query,__LINE__,__FILE__);
	$platba=mysql_fetch_array($v);

	$query='select priplatek from '.T_DOPRAVA_X_PLATBA.' where id_doprava='.$_SESSION['basket_doprava'].' and id_platba='.$_SESSION['basket_platba'];
	$v=my_DB_QUERY($query,__LINE__,__FILE__);
	$priplatek=mysql_fetch_array($v);

	if($doprava['neuctovano_od']!=0 && $doprava['neuctovano_od']<$soucty['cenovka']){
		$doprava['cena']=0;
		$priplatek['priplatek']=0;
	}

	if($platba['neuctovano_od']!=0 && $platba['neuctovano_od']<$soucty['cenovka']){
		$platba['cena']=0;
		$priplatek['priplatek']=0;
	}

	$postovne=$doprava['cena']+$platba['cena']+$priplatek['priplatek'];

	if($doprava_zdarma)
	{
    $postovne = 0;
  }

	$ceny = ceny2($postovne,POSTOVNE_DPH,1);
*/

	$query='select '.T_DOPRAVA.'.id as d_id,'.T_DOPRAVA.'.nazev as d_nazev, '.T_DOPRAVA.'.id_stat as d_id_stat,
				'.T_DOPRAVA.'.poznamka as d_poznamka,'.T_DOPRAVA.'.cena as d_cena,
				'.T_DOPRAVA.'.neuctovano_od as d_neuctovano_od,'.T_DOPRAVA.'.hidden as d_hidden,
				'.T_PLATBA.'.id as p_id,'.T_PLATBA.'.typ as p_typ,'.T_PLATBA.'.nazev as p_nazev,
				'.T_PLATBA.'.poznamka as p_poznamka,'.T_PLATBA.'.cena as p_cena,
				'.T_PLATBA.'.neuctovano_od as p_neuctovano_od,'.T_PLATBA.'.hidden as p_hidden,
				'.T_DOPRAVA_X_PLATBA.'.priplatek as priplatek
				from '.T_DOPRAVA.','.T_PLATBA.','.T_DOPRAVA_X_PLATBA.'
				where '.T_DOPRAVA.'.id='.T_DOPRAVA_X_PLATBA.'.id_doprava and
				'.T_PLATBA.'.id='.T_DOPRAVA_X_PLATBA.'.id_platba and
				'.T_DOPRAVA.'.hidden=0 and
				'.T_PLATBA.'.hidden=0 and (
				'.T_DOPRAVA.'.vaha_od<='.$hmotnost_cr.' and 
				(('.T_DOPRAVA.'.vaha_do>='.$hmotnost_cr. ' or '.T_DOPRAVA.'.vaha_do=0) AND '.T_DOPRAVA.'.id_stat = 1) OR 
				(
				'.T_DOPRAVA.'.vaha_od<='.$hmotnost.' and 
				('.T_DOPRAVA.'.vaha_do>='.$hmotnost. ' or '.T_DOPRAVA.'.vaha_do=0) AND '.T_DOPRAVA.'.id_stat > 1))
                 '.$dopravciOmezeni.' order by '.T_DOPRAVA.'.id_stat, '.T_DOPRAVA.'.poradi';
                 
//                  echo $query;
//                  exit;
				
	$v=my_DB_QUERY($query,__LINE__,__FILE__);
	
	$optionsDopravne='';
     $arrayStaty = unserialize(STATY);
  $doprava = array();
  $platba = array();
  $stat_platby = null;
  
	if(mysql_num_rows($v) >= 1)
  {
		switch(DOPRAVA_STYLE)
    {
	   	case 'select':
      {
				while($row=mysql_fetch_array($v))
        {
                $stat = $arrayStaty[$row['d_id_stat']];
                if($stat_platby != $stat) {
                    if(!empty($optionsDopravne)) $optionsDopravne .="</optgroup>";
                    
                    $optionsDopravne .= '
                  <optgroup style="margin:5px 0px" label="'.$stat.'">';
                  $stat_platby = $stat;
                }

					if($row['d_neuctovano_od']!=0 && $row['d_neuctovano_od']<$soucty['cenovka']){
						$row['d_cena']=0;
						$row['priplatek']=0;
						$row['p_cena']=0;
						$row['priplatek']=0;
					}

					
// 					if($row['p_neuctovano_od']!=0 && $row['p_neuctovano_od']<$soucty['cenovka']){
// 						$row['p_cena']=0;
// 						$row['priplatek']=0;
// 					}

		if($row['d_id'] == $_SESSION['basket_doprava'] && $row['p_id'] == $_SESSION['basket_platba'])
          { // vybrana doprava a platba (uzivatelem ci defaultni)
            $ceny = ceny2($row['d_cena']+$row['p_cena']+$row['priplatek'],POSTOVNE_DPH,1); // ceny pro objednavku a kosik
            $doprava = array('id' => $row['d_id'] , 'nazev' => $row['d_nazev']);
            $platba = array('id' => $row['p_id'] , 'nazev' => $row['p_nazev']);
            $selected = 'selected="selected"';
            $doprava_uzivatel = 1;
            $cena_do_dopravy_zdarma = $row['d_neuctovano_od'];
            
		  }
          else
          {
            if(!isset($prvni_doprava))
            { // prvni moznost dopravy a platby
              $ceny_prvni = ceny2($row['d_cena']+$row['p_cena']+$row['priplatek'],POSTOVNE_DPH,1);
              $doprava_prvni = array('id' => $row['d_id'] , 'nazev' => $row['d_nazev']);
              $platba_prvni = array('id' => $row['p_id'] , 'nazev' => $row['p_nazev']);
              $prvni_doprava = "stop";
              $cena_do_dopravy_zdarma_prvni = $row['d_neuctovano_od'];
            }

			$selected = '';
		  }

					if(!isset($doprava_uzivatel))
          {  // uzivatel zatim nevybral dopravu a defaultni se neshoduje -> volime prvni moznost
             $ceny = $ceny_prvni; // ceny pro objednavku a kosik
             $doprava = $doprava_prvni;
             $platba = $platba_prvni;
             $cena_do_dopravy_zdarma = $cena_do_dopravy_zdarma_prvni;
          }

          $cenaRadek = ceny2($row['d_cena']+$row['p_cena']+$row['priplatek'],POSTOVNE_DPH,1); // ceny pro select

	        if($doprava_zdarma AND $row['d_id_stat'] == 1)
	        { // doprava je zdarma (pneumatiky) jen pro čr
            $cenaRadek = ceny2(0,POSTOVNE_DPH,1); // ceny pro select
            $ceny = ceny2(0,POSTOVNE_DPH,1); // ceny pro objednavku a kosik
          }
					
					$optionsDopravne.='<option '.$selected.' value="'.$row['d_id'].'#'.$row['p_id'].'">'.$row['d_nazev'].' '.$row['p_nazev'].' '.number_format($cenaRadek[3],2,","," ").' Kč</option>';
   	    }

				if(!empty($optionsDopravne))
        {
            $optionsDopravne .="</optgroup>";
            $dopravne='<select name="dopravne" onchange="submit();">'.$optionsDopravne.'</select>';
				}

        $TEXT_DOPRAVA_ZDARMA = null;
        if($cena_do_dopravy_zdarma > $soucty['cenovka']) {
            $cena_do_dopravy_zdarma = $cena_do_dopravy_zdarma - $soucty['cenovka'];
            $TEXT_DOPRAVA_ZDARMA = "<tr><td></td><td colspan=\"3\" style=\"color:#d02626\">Nakupte ještě za <strong>".number_format($cena_do_dopravy_zdarma,2,","," ")." Kč</strong> a MÁTE DOPRAVU ZDARMA!</td></tr>";
        }
        
        $pobocka = "";

	      if($doprava['id'] == 5)
	      {  // osobní odber na pobocce
          $pobocka = '
				<table cellpadding="0" cellspacing="1" class="usertable">
					<tr>
						<th>Vyberte místo vyzvednutí</th>
					</tr>
					<tr>
					  <td>
          <select class="pobocka" name="pobocka" onchange="">
            <option value="0">Vyberte pobočku</option>
            <option value="Božejov 394 61, Božejov 28">Božejov 394 61 , Božejov 28</option>
            <option value="Brno 620 00, Kaštanová 499/123">Brno 620 00, Kaštanová 499/123</option>
            <option value="Bystřice nad Pernštejnem 593 01, Průmyslová 993">Bystřice nad Pernštejnem 593 01, Průmyslová 993</option>
            <option value="Kyjov 697 01, Boršovská 2610">Kyjov 697 01, Boršovská 2610</option>
            <option value="Městec Králové 289 03, Tyršova 943">Městec Králové 289 03, Tyršova 943</option>
            <option value="Nechanice 503 15, Mžany">Nechanice 503 15, Mžany</option>
            <option value="Přeštice 334 11, Husova 1315">Přeštice 334 11, Husova 1315</option>
            <option value="Svitavy 568 02, Pražská 2133/36">Svitavy 568 02, Pražská 2133/36</option>
            <option value="Svitavy 568 02, Průmyslová 2157/4"> Svitavy 568 02, Průmyslová 2157/4</option>
            <option value="Štěpánovice 373 73, Libín 74">Štěpánovice 373 73, Libín 74</option>
            <option value="Zábřeh na Moravě 789 01, Na řádkách 17">Zábřeh na Moravě 789 01, Na řádkách 17</option>
          </select>
            </td>
          </tr>
        </table>
          ';
        }

				// pro kosik
				$polozky1 .= "
				        <tr>
				            <td></td><td colspan=\"3\"><br /><br />
                            Vyberte dopravu a platbu <strong>podle státu doručení.</strong><br />
                            Aktuálně je vybraná doprava pro stát <strong>".$aktStatNazev."</strong></td>
				        </tr>
				        ".$TEXT_DOPRAVA_ZDARMA."
						<tr>
							<td class='ks'><input type=\"text\" name=\"ppoo\" value=\"1\" readonly /></td>
							<td>".$dopravne."</td>
							<td class='dph'>".$ceny[$ceny['K1']]."</td>
							<td class='cena'>".$ceny[$ceny['K2']]."</td>
							<td class='cena'>".$ceny[$ceny['K3']]."</td>
						</tr>
            ";
				// pro objednavku
				$polozky2 .= "
						<tr>
							<td>1</td>
							<td>&nbsp;</td>
							<td>".$doprava['nazev']." ".$platba['nazev']."</td>
							<td align=\"right\">".$ceny[$ceny['K1']]."&nbsp;</td>
							<td align=\"right\">".$ceny[$ceny['K2']]."&nbsp;</td>
							<td align=\"right\">".$ceny[$ceny['K3']]."</td>
						</tr>";				

				break;	
	    }
	     	
	     	case 'radio':{
	     	     $stat_platby = null;
				while($row=mysql_fetch_array($v)){
				    $stat = $arrayStaty[$row['d_id_stat']];
				    if($stat_platby != $stat) {
                        $optionsDopravne .= '
                      <tr>
                        <td colspan="2"><h3 style="padding:5px 0px; font-size:12px">'.$stat.'</h3></td>
                      </tr>';
                      $stat_platby = $stat;
                    }
					if($row['d_id']==$_SESSION['basket_doprava'] && $row['p_id']==$_SESSION['basket_platba']){
					     $selected='checked="checked"';
					}else{
						$selected='';
					}
					



					if($row['d_neuctovano_od']!=0 && $row['d_neuctovano_od']<$soucty['cenovka']){
						$row['d_cena']=0;
						$row['priplatek']=0;
					}
					
					if($row['p_neuctovano_od']!=0 && $row['p_neuctovano_od']<$soucty['cenovka']){
						$row['p_cena']=0;
						$row['priplatek']=0;
					}
								

					$cenaRadek = ceny2($row['d_cena']+$row['p_cena']+$row['priplatek'],POSTOVNE_DPH,1);

					
					
					
					$optionsDopravne.='<tr><td><input class="radiobutt" type="radio" name="dopravne" onchange="submit();" '.$selected.' value="'.$row['d_id'].'#'.$row['p_id'].'" /></td><td class="f11" colspan="3">'.$row['d_nazev'].' '.$row['p_nazev'].'</td><td class="cena">'.$cenaRadek[$cenaRadek['K3']].'</td></tr>';
				}
		
				if(!empty($optionsDopravne)){
					$dopravne='<tr><td colspan="5">&nbsp;</td></tr>
							<tr><td colspan="4" class="cena total"><strong>Celkem s DPH:</strong></td><td class="cena total"><strong>'.number_format($sDPHx,2,","," ").'</strong></td></tr>
							<tr><td colspan="5">&nbsp;</td></tr>
							<tr><th colspan="5" class="l">Vyberte způsob dopravy a platby <strong>podle státu doručení</strong></th></tr>
							'.$optionsDopravne.'
							<tr><td colspan="5">&nbsp;</td></tr>
							<tr><th colspan="5" class="l">Souhrn</th></tr>';
				}	
				
				// pro kosik
				$polozky1 .= $dopravne;				
				// pro objednavku
				$polozky2 .= "
						<tr>
							<td>1</td>
							<td>&nbsp;</td>
							<td>".$doprava['nazev']." ".$platba['nazev']."</td>
							<td align=\"right\">".$ceny[$ceny['K1']]."&nbsp;</td>
							<td align=\"right\">".$ceny[$ceny['K2']]."&nbsp;</td>
							<td align=\"right\">".$ceny[$ceny['K3']]."</td>
						</tr>";							
				
				break;
			}

		
		}
		
		if(empty($optionsDopravne)){
					$dopravne='Žádný typ dopravy není k dispozici';
		}
		
	
	}elseif(mysql_num_rows($v)<1){
		$dopravne='Žádný typ dopravy není k dispozici';	
	}else{
		$dopravne=$doprava['nazev']." ".$platba['nazev'];
	}
	
	
	
	
	
	$soucty = ceny_soucty($soucty,$ceny[2],$ceny[6],$ceny[4]);
	
	
	
	
			
			
	// id_obj  id_produkt  nazev_produkt  cena  dph  ks
	$INSERTS[] = "INSERT INTO ".T_ORDERS_PRODUCTS." VALUES (#id_obj#,0,'".$doprava['nazev']." ".$platba['nazev']."',".$ceny[$ceny['K4']].",".POSTOVNE_DPH.",1,'".$doprava['id']."#".$platba['id']."',".C_LANG.")";
	// ***************************************************************************
	// 
	// ***************************************************************************


}else{
// ALTERNATIVNI RESENI POSTOVNEHO, KTERE SE NEPOCITA DO CENY, ALE RESI SE AZ PO ODESLANI OBJEDNAVKY A OBJEMU DANEHO ZBOZI

	if(empty($_SESSION['basket_doprava_alter']))$_SESSION['basket_doprava_alter']=DOPRAVA_ALTER_DEF;
	
	if(!empty($_POST['dopravne_alter'])){
		$_SESSION['basket_doprava_alter']=$_POST['dopravne_alter'];
	}
	                                                          
	$query='select * from '.T_DOPRAVA_ALTER.' where id='.$_SESSION['basket_doprava_alter'];
	
	$v=my_DB_QUERY($query,__LINE__,__FILE__);
	$doprava=mysql_fetch_array($v);
	
	
	$query='select * from '.T_DOPRAVA_ALTER.' where hidden=0';
	$v=my_DB_QUERY($query,__LINE__,__FILE__);
	
	$optionsDopravne='';
	$mistoceny='';
	
	if(mysql_num_rows($v)>1){
		while($row=mysql_fetch_array($v)){	
			if($row['id']==$_SESSION['basket_doprava_alter']){
			     $selected='selected="selected"';
				$mistoceny=$row['mistoceny'];			     
			}else{
				$selected='';
			}
			$optionsDopravne.='<option '.$selected.' value="'.$row['id'].'">'.$row['nazev'].'</option>';	
		}

		if(!empty($optionsDopravne)){
			$dopravne='<select name="dopravne_alter" onchange="submit();">'.$optionsDopravne.'</select>';
		}else{
			$dopravne='Žádný typ dopravy není k dispozici';
		}
	
	}elseif(mysql_num_rows($v)<1){
		$dopravne='Žádný typ dopravy není k dispozici';
		$mistoceny='0,00';		
	}else{
		$dopravne=$doprava['nazev'];
		$mistoceny=$doprava['mistoceny'];
	}	
	
	// ***************************************************************************
	//  doprava do objednavek
	// ***************************************************************************
	// pro kosik
	$polozky1 .= "
			<tr>
				<td class='ks'><input type=\"text\" name=\"ppoo\" value=\"1\" readonly /></td>
				<td>".$dopravne."</td>
				<td class='cena'></td>
				<td class='cena'>".$mistoceny."</td>
				<td class='cena'>".$mistoceny."</td>
			</tr>";
	// pro objednavku
	$polozky2 .= "
			<tr>
				<td>1</td>
				<td>&nbsp;</td>
				<td>".$doprava['nazev']."</td>
				<td align=\"right\"></td>
				<td align=\"right\">".$mistoceny."&nbsp;</td>
				<td align=\"right\">".$mistoceny."</td>
			</tr>";
	// id_obj  id_produkt  nazev_produkt  cena  dph  ks
	$INSERTS[] = "INSERT INTO ".T_ORDERS_PRODUCTS." VALUES (#id_obj#,0,'".$doprava['nazev']."',0,".POSTOVNE_DPH.",1,'".$doprava['id']."#ALT',".C_LANG.")";
	// ***************************************************************************
	// 
	// ***************************************************************************	

}


	
	
	//$total = ceny_total($soucty['zaklad'],$soucty['dph']);
	$total = ceny_total($soucty['zaklad'],$soucty['dph'],$soucty['cenovka']);
	
	$_SESSION['basket_total'] = $total['total'];
	
	if(!empty($action)){
		if($action == 1) {
		
			Header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		
		}
		
		if($action == 2) {
		
			Header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		
		}	
	}

}
// *****************************************************************************
// polozky v kosiku - pro kosik i objednavku
// *****************************************************************************











// *****************************************************************************
// odesleme objednavku
// *****************************************************************************
if(!empty($_POST['order'])) {

	if(empty($_SESSION[$PFX]['f_jmeno']))$_SESSION[$PFX]['f_jmeno']='';
	if(empty($_SESSION[$PFX]['f_kontakt']))$_SESSION[$PFX]['f_kontakt']='';
	if(empty($_SESSION[$PFX]['f_adresa']))$_SESSION[$PFX]['f_adresa']='';
	if(empty($_SESSION[$PFX]['f_psc']))$_SESSION[$PFX]['f_psc']='';
	if(empty($_SESSION[$PFX]['f_mesto']))$_SESSION[$PFX]['f_mesto']='';
	if(empty($_SESSION[$PFX]['f_stat']))$_SESSION[$PFX]['f_stat']='';
	if(empty($_SESSION[$PFX]['f_ico']))$_SESSION[$PFX]['f_ico']='';
	if(empty($_SESSION[$PFX]['f_dic']))$_SESSION[$PFX]['f_dic']='';
	if(empty($_SESSION[$PFX]['f_mail']))$_SESSION[$PFX]['f_mail']='';
	if(empty($_SESSION[$PFX]['f_tel']))$_SESSION[$PFX]['f_tel']='';

	if(empty($_SESSION[$PFX]['p_jmeno']))$_SESSION[$PFX]['p_jmeno']='';
	if(empty($_SESSION[$PFX]['p_kontakt']))$_SESSION[$PFX]['p_kontakt']='';
	if(empty($_SESSION[$PFX]['p_adresa']))$_SESSION[$PFX]['p_adresa']='';
	if(empty($_SESSION[$PFX]['p_psc']))$_SESSION[$PFX]['p_psc']='';
	if(empty($_SESSION[$PFX]['p_mesto']))$_SESSION[$PFX]['p_mesto']='';
	if(empty($_SESSION[$PFX]['p_stat']))$_SESSION[$PFX]['p_stat']='';

	
	$arrayStaty=unserialize(STATY);

	$f_jmeno = $_SESSION[$PFX]['f_jmeno'];
	$f_kontakt = $_SESSION[$PFX]['f_kontakt'];
	$f_adresa = $_SESSION[$PFX]['f_adresa'];
	$f_psc = $_SESSION[$PFX]['f_psc'];
	$f_mesto = $_SESSION[$PFX]['f_mesto'];
	$f_stat = $_SESSION[$PFX]['f_stat'];
	$f_ico = $_SESSION[$PFX]['f_ico'];
	$f_dic = $_SESSION[$PFX]['f_dic'];
	$f_mail = $_SESSION[$PFX]['f_mail'];
	$f_tel = $_SESSION[$PFX]['f_tel'];
	
	
	if(empty($_SESSION[$PFX]['p_jmeno']) &&
		empty($_SESSION[$PFX]['p_kontakt']) && 
		empty($_SESSION[$PFX]['p_adresa']) && 
		empty($_SESSION[$PFX]['p_psc']) && 
		empty($_SESSION[$PFX]['p_mesto']) && 
		empty($_SESSION[$PFX]['p_stat'])) {
	
		$p_jmeno = $f_jmeno;
		$p_kontakt = $f_kontakt;
		$p_adresa = $f_adresa;
		$p_psc = $f_psc;
		$p_mesto = $f_mesto;
		$p_stat = $f_stat;
	
	} else {
	
		$p_jmeno = $_SESSION[$PFX]['p_jmeno'];
		$p_kontakt = $_SESSION[$PFX]['p_kontakt'];
		$p_adresa = $_SESSION[$PFX]['p_adresa'];
		$p_psc = $_SESSION[$PFX]['p_psc'];
		$p_mesto = $_SESSION[$PFX]['p_mesto'];
		$p_stat = $_SESSION[$PFX]['p_stat'];
	
	}
	
	if(!empty($_POST["pobocka"]))
	{  // pokud je pozadovana pobocka pripiseme ji do poznamky
    $_SESSION[$PFX]['pozn'] .= "\n</ br>\n</ br><strong>Místo vyzvednutí:</strong>\n</ br>".$_POST["pobocka"]."";
  }
	

	
	$pozn = nl2br(stripslashes($_SESSION[$PFX]['pozn']));
		
	



     $time = time();
     $c_obj = 0;




	// id c_obj f_jmeno f_kontakt f_adresa f_psc f_mesto f_stat f_ico f_dic f_mail f_tel p_jmeno p_kontakt p_adresa p_psc p_mesto p_stat pozn lang
	$query = "INSERT INTO ".T_ORDERS_ADDRESS." 
	VALUES(NULL,
	'".$_SESSION['user']['UID']."',
	$c_obj,
	$time,
	'".$_SESSION[$PFX]['f_jmeno']."',
	'".$_SESSION[$PFX]['f_kontakt']."',
	'".$_SESSION[$PFX]['f_adresa']."',
	'".$_SESSION[$PFX]['f_psc']."',
	'".$_SESSION[$PFX]['f_mesto']."',
	'".$_SESSION[$PFX]['f_stat']."',
	'".$_SESSION[$PFX]['f_ico']."',
	'".$_SESSION[$PFX]['f_dic']."',
	'".$_SESSION[$PFX]['f_mail']."',
	'".$_SESSION[$PFX]['f_tel']."',
	'".$_SESSION[$PFX]['p_jmeno']."',
	'".$_SESSION[$PFX]['p_kontakt']."',
	'".$_SESSION[$PFX]['p_adresa']."',
	'".$_SESSION[$PFX]['p_psc']."',
	'".$_SESSION[$PFX]['p_mesto']."',
	'".$_SESSION[$PFX]['p_stat']."',
	'".$_SESSION[$PFX]['pozn']."',".C_LANG.")";
	my_DB_QUERY($query,__LINE__,__FILE__);
	

  // pridani do odberu novinek
  if(isset($_POST["newsletter_add"]) AND $_POST["newsletter_add"] == 1)
  { // pridani do odberu novinek
    include_once("./newsletter_functions.php");

    delete_from_blacklist_newsletter($_SESSION[$PFX]['f_mail']); // pokud je na blacklistu tak email z blacklistu odstranime
  }
  else
  { // nepridavat do odberu novinek
    include_once("./newsletter_functions.php");

    blacklist_newsletter($_SESSION[$PFX]['f_mail']); // pridame email na blacklist
  }

	
	// ID ulozene objednavky - PHP varianta je mysql_insert_id() 
	$v = mysql_query("SELECT LAST_INSERT_ID()")
	or die("ř.".__LINE__ .": ".mysql_error());
	$LID = mysql_result($v, 0, 0);
	
	$c_obj=$LID;
	
		
	while(strlen($c_obj)<6){
		$c_obj='0'.$c_obj;
	}
	
	mysql_query("UPDATE ".T_ORDERS_ADDRESS." set c_obj='$c_obj' where id=$LID");	
	
	
	$trans = array ("#id_obj#" => $LID);
	
	
	reset($INSERTS);
	while ($p = each($INSERTS)) {
	
		// objednane produkty
		mysql_query(strtr($p['value'], $trans))
		or die("ř.".__LINE__ .": ".mysql_error());
	
	}







	$tbl_params = "width=\"650\" cellpadding=\"0\" cellspacing=\"0\"";
	$vystaveno = date("d.m.Y H:i:s", $time);  
	
	
	
	
	$message = "
				
				<table $tbl_params>
				
				<tr>
					<td colspan=\"4\" class=\"f13\"><b>OBJEDNÁVKA č. $c_obj</b></td>
				</tr>
				
				<tr>
					<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
						Dodavatel:<br />
						".S_FIRMA."<br />
						".S_ULICE."<br />
						".S_PSC." ".S_MESTO."<br />
						IČO: ".S_ICO."<br />
						DIČ: ".S_DIC."
					</td>
					
					<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
						Fakturační adresa:<br />
						".$_SESSION[$PFX]['f_jmeno']." - ".$_SESSION[$PFX]['f_kontakt']."<br />
						".$_SESSION[$PFX]['f_adresa']."<br />
						".$_SESSION[$PFX]['f_psc']." ".$_SESSION[$PFX]['f_mesto']." / ".$arrayStaty[$_SESSION[$PFX]['f_stat']]."<br />
						IČO: ".$_SESSION[$PFX]['f_ico']." / DIČ: ".$_SESSION[$PFX]['f_dic']."<br />
						e-mail: ".$_SESSION[$PFX]['f_mail']." / tel.: ".$_SESSION[$PFX]['f_tel']."
					</td>
				</tr>
				
				
				
				
				
				
				
				
				
				<tr>
					<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
						<a href=\"http://".S_WEB."\" target=\"_blank\">".S_WEB."</a>, 
						<a href=\"mailto:".S_MAIL_SHOP."\">".S_MAIL_SHOP."</a>
					</td>
					
					<td width=\"50%\" valign=\"top\" class=\"box1\" colspan=\"2\">
						Doručovací adresa:<br />
						$p_jmeno - $p_kontakt<br />
						$p_adresa<br />
						$p_psc $p_mesto / ".$arrayStaty[$p_stat]."<br /><br /><br />
						
						Datum objednávky: $vystaveno
					</td>
				</tr>
				
				</table>
				
				
				<br /><br />
				
				
				<table $tbl_params>
				
				<tr>&nbsp;
					<td valign=\"top\" width=\"15\"><strong>ks</strong>&nbsp;</td>
					<td valign=\"top\"><strong>Kód</strong>&nbsp;</td>
					<td valign=\"top\"><strong>Položka</strong>&nbsp;</td>
					<td align=\"right\" valign=\"top\"><strong>DPH</strong>&nbsp;</td>
					<td align=\"right\" valign=\"top\"><strong>Cena/ks bez DPH</strong>&nbsp;</td>
					<td align=\"right\" valign=\"top\" width=\"85px\"><strong>Celkem s DPH</strong></td>
					<td align=\"right\" valign=\"top\" width=\"85px\"><strong>Celkem bez DPH</strong></td>
				</tr>
				
				
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align=\"right\">&nbsp;</td>
					<td align=\"right\">&nbsp;</td>
					<td align=\"right\">&nbsp;</td>
				</tr>
				
				
				$polozky2
				
				
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align=\"right\">&nbsp;</td>
					<td align=\"right\">&nbsp;</td>
					<td align=\"right\">&nbsp;</td>
				</tr>
				
				<tr>                
					<td colspan=\"6\">
						<strong>Základ</strong><br />
						<strong>DPH</strong><br />
						<strong>Celkem s DPH</strong></td>
					<td align=\"right\" width=\"75px\">
						<strong>".$total['zaklad']."</strong><br />
						<strong>".$total['dph']."</strong><br />
						<strong>".$total['total']."</strong></td>
				</tr>
				
				</table>
				
				
				
				<br /><br /><br />
				
				
				
				<table $tbl_params>
				
				<tr>
					<td>
						<strong>Poznámka k objednávce:</strong><br /><br />
						$pozn</td>
				</tr>
				
				</table>";
	
$message_client = "
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
    <center>
	".$message."
    </center>
    <br />
    <br />
    <br />
    <div style=\"margin-left:auto; margin-right:auto; width:650px; text-align:left; padding-bottom:10px;\">
      ".EMAIL_REKLAMA_NETACTION."
    </div>
  </body>
  </html>
  ";	
		
$message = "
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

	<center>
	
		$message
	
	</center>
</body>
</html>";

	$subject = "Nekvinda-obchod.cz - objednavka c. $c_obj";
	
	$_SESSION['order_sent_mail2']=$_SESSION['order_sent_mail1']=0;
	
	//$_SESSION['order_sent_mail1']=send($to=S_MAIL_SHOP,$message,$subject);
	$_SESSION['order_sent_mail1']=send("dobirky@nekvinda.cz",$message,$subject);
    //send("strediska@nekvinda.cz",$message,$subject); // posilani objednavek i na email strediska@nekvinda.cz (pozadavek pana Sourka ze dne 17.01.2013)
	
	
	if(!empty($_SESSION[$PFX]['f_mail'])) { // kopie na email objednavajiciho
	
			$_SESSION['order_sent_mail2']=send($to=$_SESSION[$PFX]['f_mail'],$message_client,$subject." - KOPIE");
	
	}


  // Heuréka
  reset($_SESSION["heureka"]);
  $itemId = '';
  $heureka_konverze = "_hrq.push(['setOrderId', '".$c_obj."']);
"; // Doplnění konverzního kódu pro heuréku. Bude vypsán na děkovací stránce.
  foreach($_SESSION["heureka"]["products"] as $id => $produkt)
  { // Produkty v objednávce
    $itemId .= '&itemId[]='.intval($id);
    $heureka_konverze .= "_hrq.push(['addProduct', '".$produkt["name"]."', '".$produkt["ceny"][3]."', '".$produkt["ks"]."']);
";
  }
  unset($_SESSION["heureka"]);

  if(isset($itemId) AND !empty($itemId))
  { // Url Heuréka.
    $heureka_tajny_klic = 'af1095474f735e159b5b96b607526bd7';
    $heureka_url = 'http://www.heureka.cz/direct/dotaznik/objednavka.php?id='.$heureka_tajny_klic.'&email='.urlencode($_SESSION[$PFX]['f_mail']).$itemId.'&orderid='.urlencode($c_obj);
    $heureka_odpoved = send_request($heureka_url);
  }

  if(isset($heureka_konverze) AND !empty($heureka_konverze))
  {
    $heureka_verejny_klic = '9C0D8AF98215F649D9BB87224CEFAFB5';
    /*
    $_SESSION["order"]["heureka"] = '
    <!-- Heuréka.cz -->
    <script type="text/javascript">
      var _hrq = _hrq || [];
      _hrq.push([\'setKey\', \''.$heureka_verejny_klic.'\']);
      '.$heureka_konverze.'
      _hrq.push([\'trackOrder\']);

      (function() {
        var ho = document.createElement(\'script\'); ho.type = \'text/javascript\'; ho.async = true;
        ho.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.heureka.cz/direct/js/cache/1-roi-async.js\';
        var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ho, s);
      })();
    </script>
    ';
    */
  }
  // END Heuréka

	
	unset($_SESSION[$PFX]);
	unset($_SESSION['sbaskets']);
	unset($_SESSION['hmotnost']);
	unset($_SESSION['basket_total']);
  unset($_SESSION['basket_doprava']);
  unset($_SESSION['basket_doprava_alter']);
  unset($_SESSION['basket_platba']);
	unset($_SESSION['basket_suma']);
// 	unset($INSERTS);
	
	
	$_SESSION['order_sent'] = "y";
	Header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}
// *****************************************************************************
// odesleme objednavku
// *****************************************************************************










// *****************************************************************************
// obsah pro stranku kde zobrazujeme kosik
// *****************************************************************************

if(empty($_SESSION['order_sent']))$_SESSION['order_sent']=null;




if(empty($polozky1) && $_SESSION['order_sent'] != "y")
{ // prazdny kosik
	$TEXT = "<div class='info'>Nákupní košík je prázdný</div>";
}
elseif(isset($_GET['send']) && $_GET['send']=='ok' && $_SESSION['order_sent'] == "y")
{
	$TEXT = '
  <div class="info">Vaše objednávka byla odeslána.</div>

	<!-- Měřicí kód Sklik.cz -->
	<iframe width="119" height="22" frameborder="0" scrolling="no" src="https://out.sklik.cz/conversion?c=25239163&color=ffffff&v=1"></iframe>


  <iframe src="http://www.zbozi.cz/action/46428/conversion?chsum=qKGZG8IzVrVHHgUXgS7nRg==" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="position:absolute; top:-3000px; left:-3000px; width:1px; height:1px; overflow:hidden;"></iframe>

    	
  <!-- Google Code for Odeslan&aacute; objedn&aacute;vka Conversion Page -->
  <script type="text/javascript">
  /* <![CDATA[ */
  var google_conversion_id = 992894661;
  var google_conversion_language = "cs";
  var google_conversion_format = "3";
  var google_conversion_color = "ffffff";
  var google_conversion_label = "fAD7CLuJkg0Qxb252QM";
  var google_conversion_value = 0;
  /* ]]> */
  </script>
  <script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
  </script>
  <noscript>
  <div style="display:inline;">
  <img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/992894661/?value=0&amp;label=fAD7CLuJkg0Qxb252QM&amp;guid=ON&amp;script=0"/>
  </div>
  </noscript>


	';

  if(isset($_SESSION["order"]["heureka"]) AND !empty($_SESSION["order"]["heureka"]))
  { // Měření konverzí na Heuréce.
    $TEXT .= $_SESSION["order"]["heureka"];
  }

  unset($_SESSION["order"]);
	unset($_SESSION['order_sent']); 	
}
elseif(empty($polozky1) && $_SESSION['order_sent'] == "y")
{ // prazdny kosik po odeslani objednavky
	Header('Location: http://'.$_SERVER['HTTP_HOST'].'/nakupni-kosik/?send=ok');
	exit;
}
else
{
			if(!empty($_SESSION['error'])) {
			
				//$data = "<div style=\"background-color: red; color: white; padding: 5px;\">".$_SESSION['error']."</div><br /><br />";
				$data = "<div >".$_SESSION['error']."</div>";
				
				unset($_SESSION['error']);
			
			}else{
				$data='';
			}
			
			
	
			
			if(empty($_POST['order'])) {
			
				$data .= "
				
				<div class='info'>
				<!--Pokud zvolíte způsob odběru <strong>osobně</strong>, uveďte prosím v poznámce některou <br />z našich <a href='/clanek/4-nase-prodejny.html'><strong>poboček</strong></a>.<br />-->
				Na stránce <a href='/clanek/3-obchodni-podminky.html'>obchodní podmínky</a> naleznete také informace o cenách dopravy.
				</div>
				
				$js
				
				<form action=\"".THIS_PAGE."\" method=\"post\">
				<table cellpadding=\"0\" cellspacing=\"0\" class='usertable smaller kosik'>
				
				<tr class='basket'>
					<th>ks</th>
					<th class='name'>Kód / Položka</th>
					<th class='dph'>DPH</th>
					<th class='cena' style='width:100px;'>Cena/ks</th>
					<th class='cena' style='width:150px;'>s DPH</th>
					<th class='cena' style='width:150px;'>bez DPH</th>
				</tr>
				
				
				$polozky1
				
				
				<tr>
					<td id=\"celkem_hmotnost\" colspan=\"2\">
						<span>Celková hmotnost zboží v objednávce: ".number_format(ceil($hmotnost/1000),null,""," ")." kg</span><br />	
						<span>Započítaná hmostnost pro zvýhodněnou dopravu po ČR: ".number_format(ceil($hmotnost_cr/1000),null,""," ")." kg</span>
					</td>
					<td colspan=\"4\" class='cena total'>
						<table id=\"total_sum\">
              <tr><td><strong>Základ:</strong></td><td><strong>".$total['zaklad']."</strong></td></tr>
						  <tr><td><strong>DPH:</strong></td><td><strong>".$total['dph']."</strong></td></tr>
						  <tr><td><strong>Celkem s DPH:</strong></td><td><strong>".$total['total']."</strong></td></tr>
					 </table>
					</td>
				</tr>
				
				</table>
				
				<div class='buttons'>
					
						<input type=\"button\" value=\"Vyprázdnit košík\" class=\"butt_blue\" 
							onclick=\"resetorder('".HTTP_ROOT."?go=".$_GET['go']."&amp;resetorder"."')\" >
						
						<input type=\"submit\" value=\"Přepočítat cenu\" class=\"butt_blue\" >
					
				</div>
				
				</form>
				<br />
						
				
				<form action=\"".THIS_PAGE."\" method=\"post\">
				

				      $pobocka

				
				<input type=\"hidden\" name=\"order\" value=\"1\" />";
				
				
				
				
				// doplnime udaje s adresami do formu
				if(!empty($_SESSION['user']['UID'])) { //
				
					$readonlyUID = 'readonly="readonly"';
					$disabledUID = true;
					$gray='gray';
									
					//T_ADRESY_F - id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
					$query = "SELECT * 
					FROM ".T_ADRESY_F." 
					WHERE id = ".$_SESSION['user']['UID']." 
					LIMIT 0,1";
					$v = my_DB_QUERY($query,__LINE__,__FILE__);
					while ($z = mysql_fetch_array($v)) {
					
						$_SESSION[$PFX]['f_jmeno'] = $z['nazev'];
						$_SESSION[$PFX]['f_kontakt'] = $z['kontakt'];
						$_SESSION[$PFX]['f_adresa'] = $z['adresa'];
						$_SESSION[$PFX]['f_psc'] = $z['psc'];
						$_SESSION[$PFX]['f_mesto'] = $z['mesto'];
						$_SESSION[$PFX]['f_stat'] = $z['stat'];
						$_SESSION[$PFX]['f_ico'] = $z['ico'];
						$_SESSION[$PFX]['f_dic'] = $z['dic'];
						$_SESSION[$PFX]['f_mail'] = $z['email'];
						$_SESSION[$PFX]['f_tel'] = $z['telefon'];
						$_SESSION[$PFX]['login_name'] = $z['jmeno'];
					
					}
					
					
					//T_ADRESY_P - id  id_f  nazev  adresa  psc  mesto  email  telefon
					$query = "SELECT * FROM ".T_ADRESY_P." 
					WHERE id_f = ".$_SESSION['user']['UID']."";
					$v = my_DB_QUERY($query,__LINE__,__FILE__);
					while ($z = mysql_fetch_array($v)) {
					
						$p_id = $z['id'];
						$_SESSION[$PFX]['p_jmeno'] = $z['nazev'];
						$_SESSION[$PFX]['p_kontakt'] = $z['kontakt'];
						$_SESSION[$PFX]['p_adresa'] = $z['adresa'];
						$_SESSION[$PFX]['p_psc'] = $z['psc'];
						$_SESSION[$PFX]['p_mesto'] = $z['mesto'];
						$_SESSION[$PFX]['p_email'] = $z['email'];
						$_SESSION[$PFX]['p_telefon'] = $z['telefon'];
						$_SESSION[$PFX]['p_stat'] = $z['stat'];
					
					}
					
					if(empty($_SESSION[$PFX]['f_jmeno']))$_SESSION[$PFX]['f_jmeno']='';
					if(empty($_SESSION[$PFX]['f_kontakt']))$_SESSION[$PFX]['f_kontakt']='';
					if(empty($_SESSION[$PFX]['f_adresa']))$_SESSION[$PFX]['f_adresa']='';
					if(empty($_SESSION[$PFX]['f_psc']))$_SESSION[$PFX]['f_psc']='';
					if(empty($_SESSION[$PFX]['f_mesto']))$_SESSION[$PFX]['f_mesto']='';
					if(empty($_SESSION[$PFX]['f_stat']))$_SESSION[$PFX]['f_stat']='';
					if(empty($_SESSION[$PFX]['f_ico']))$_SESSION[$PFX]['f_ico']='';
					if(empty($_SESSION[$PFX]['f_dic']))$_SESSION[$PFX]['f_dic']='';
					if(empty($_SESSION[$PFX]['f_mail']))$_SESSION[$PFX]['f_mail']='';
					if(empty($_SESSION[$PFX]['f_tel']))$_SESSION[$PFX]['f_tel']='';
				
					if(empty($_SESSION[$PFX]['p_jmeno']))$_SESSION[$PFX]['p_jmeno']='';
					if(empty($_SESSION[$PFX]['p_kontakt']))$_SESSION[$PFX]['p_kontakt']='';
					if(empty($_SESSION[$PFX]['p_adresa']))$_SESSION[$PFX]['p_adresa']='';
					if(empty($_SESSION[$PFX]['p_psc']))$_SESSION[$PFX]['p_psc']='';
					if(empty($_SESSION[$PFX]['p_mesto']))$_SESSION[$PFX]['p_mesto']='';
					if(empty($_SESSION[$PFX]['p_stat']))$_SESSION[$PFX]['p_stat']='';
										
					if(empty($_SESSION[$PFX]['pozn']))$_SESSION[$PFX]['pozn']='';
					
					
					$buttonUID = '
					<div class="buttons">
							<input type="button" value="Změnit údaje" class="butt_blue" onclick="location=\''.HTTP_ROOT.'?go=user&amp;edit='.$_SESSION['user']['sha'].'\';">
					</div>
					';
				
				}else{
						$readonlyUID='';
						$disabledUID = false;
						$gray='';
						
						if(empty($_SESSION[$PFX]['f_jmeno']))$_SESSION[$PFX]['f_jmeno'] = '';
						if(empty($_SESSION[$PFX]['f_kontakt']))$_SESSION[$PFX]['f_kontakt'] = '';
						if(empty($_SESSION[$PFX]['f_adresa']))$_SESSION[$PFX]['f_adresa'] = '';
						if(empty($_SESSION[$PFX]['f_psc']))$_SESSION[$PFX]['f_psc'] = '';
						if(empty($_SESSION[$PFX]['f_mesto']))$_SESSION[$PFX]['f_mesto'] = '';
						if(empty($_SESSION[$PFX]['f_stat']))$_SESSION[$PFX]['f_stat'] = '';
						if(empty($_SESSION[$PFX]['f_ico']))$_SESSION[$PFX]['f_ico'] = '';
						if(empty($_SESSION[$PFX]['f_dic']))$_SESSION[$PFX]['f_dic'] = '';
						if(empty($_SESSION[$PFX]['f_mail']))$_SESSION[$PFX]['f_mail'] = '';
						if(empty($_SESSION[$PFX]['f_tel']))$_SESSION[$PFX]['f_tel'] = '';
						if(empty($_SESSION[$PFX]['login_name']))$_SESSION[$PFX]['login_name'] = '';
					
									
						$p_id = $z['id'];
						if(empty($_SESSION[$PFX]['p_jmeno']))$_SESSION[$PFX]['p_jmeno'] = '';
						if(empty($_SESSION[$PFX]['p_kontakt']))$_SESSION[$PFX]['p_kontakt'] = '';
						if(empty($_SESSION[$PFX]['p_adresa']))$_SESSION[$PFX]['p_adresa'] = '';
						if(empty($_SESSION[$PFX]['p_psc']))$_SESSION[$PFX]['p_psc'] = '';
						if(empty($_SESSION[$PFX]['p_mesto']))$_SESSION[$PFX]['p_mesto'] = '';
						if(empty($_SESSION[$PFX]['p_email']))$_SESSION[$PFX]['p_email'] = '';
						if(empty($_SESSION[$PFX]['p_telefon']))$_SESSION[$PFX]['p_telefon'] = '';
						if(empty($_SESSION[$PFX]['p_stat']))$_SESSION[$PFX]['p_stat'] = '';
						
						if(empty($_SESSION[$PFX]['pozn']))$_SESSION[$PFX]['pozn']='';
				}
				
				
				
				
				$f_jmeno = "<input class='$gray' type=\"text\" name=\"f_jmeno\" value=\"".$_SESSION[$PFX]['f_jmeno']."\" $readonlyUID />";
				$f_kontakt = "<input class='$gray' type=\"text\" name=\"f_kontakt\" value=\"".$_SESSION[$PFX]['f_kontakt']."\" $readonlyUID />";				
				$f_adresa = "<input class='$gray' type=\"text\" name=\"f_adresa\" value=\"".$_SESSION[$PFX]['f_adresa']."\" $readonlyUID />";				
				$f_psc = "<input class='$gray w50' type=\"text\" name=\"f_psc\" value=\"".$_SESSION[$PFX]['f_psc']."\"  $readonlyUID />";				
				$f_mesto = "<input class='$gray' type=\"text\" name=\"f_mesto\" value=\"".$_SESSION[$PFX]['f_mesto']."\" $readonlyUID />";
				$f_ico = "<input class='$gray' type=\"text\" name=\"f_ico\" value=\"".$_SESSION[$PFX]['f_ico']."\" $readonlyUID />";				
				$f_dic = "<input class='$gray' type=\"text\" name=\"f_dic\" value=\"".$_SESSION[$PFX]['f_dic']."\" $readonlyUID />";			
				$f_mail = "<input class='$gray' type=\"text\" name=\"f_mail\" value=\"".$_SESSION[$PFX]['f_mail']."\" $readonlyUID />";				
				$f_tel = "<input class='$gray' type=\"text\" name=\"f_tel\" value=\"".$_SESSION[$PFX]['f_tel']."\" $readonlyUID />";
				
				
				$p_jmeno = "<input class='$gray' type=\"text\" name=\"p_jmeno\" value=\"".$_SESSION[$PFX]['p_jmeno']."\" $readonlyUID />";
				$p_kontakt = "<input class='$gray' type=\"text\" name=\"p_kontakt\" value=\"".$_SESSION[$PFX]['p_kontakt']."\" $readonlyUID />";
				$p_adresa = "<input class='$gray' type=\"text\" name=\"p_adresa\" value=\"".$_SESSION[$PFX]['p_adresa']."\" $readonlyUID />";
				$p_psc = "<input class='$gray w50' type=\"text\" name=\"p_psc\" value=\"".$_SESSION[$PFX]['p_psc']."\" $readonlyUID />";
				$p_mesto = "<input class='$gray' type=\"text\" name=\"p_mesto\" value=\"".$_SESSION[$PFX]['p_mesto']."\" $readonlyUID />";
				
				$dopravne = '<input type="hidden" name="dopravne" value="'.$_SESSION["basket_doprava"].'#'.$_SESSION["basket_platba"].'" />';
				
				
				$bt_text = 'Odeslat objednávku'; 
				include_once 'form_user.php';
				$data .= $form_user;


				$data .= "
				<br />
				
				<table cellpadding=\"0\" cellspacing=\"1\" class=\"usertable\">
					<tr>
						<th>Poznámka k objednávce</th>
					</tr>
					<tr>
						<td><textarea class=\"pozn\" name=\"pozn\">".stripslashes($_SESSION[$PFX]['pozn'])."</textarea></td>
					</tr>
				</table>	
					
				$posli
				</form>";
				
				unset($_SESSION[$PFX]);
			
			}

               $TEXT=$data;

}
// *****************************************************************************
// obsah pro stranku kde zobrazujeme kosik
// *****************************************************************************

?>
