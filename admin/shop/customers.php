<?php


function categories_sale($cat_array, $id_adresa)
{
  $id_adresa = intval($id_adresa);
  $categories = '';
	$js = '';
	$cur_level = 0;

	if(!empty($cat_array))
  {
    // Načtení slev ke kategoriím
    $query = "
    SELECT id_kategorie, sleva
    FROM ".T_SLEVA_KATEGORIE_X_ADRESA."
    WHERE id_adresa = '".$id_adresa."'
    ";
    $v = my_DB_QUERY($query,__LINE__,__FILE__);

    $sleva = array();
    while($z = mysql_fetch_assoc($v))
    {
      $sleva[$z["id_kategorie"]] = $z["sleva"];
    }

		reset($cat_array);
		while($p = each($cat_array))
    {
			list($level,$position,$par_id,$name,$hidden,$lang,$id) = explode ("|", $p['value']);

			// odsazeni podle levelu
			$indent = "";
			for ($i = 0; $i < $level; $i++)
      {
				$indent .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}

      if(isset($cat_array[$i+1]))
      {
			  list($level2,$position2,$par_id2,$name2,$hidden2,$lang2,$id2) = explode ("|", $cat_array[$i+1]);
			}

			if($level > $cur_level)
      {
			  $categories .= '<div id="m'.$par_id.'">';
			  $js .= "document.getElementById('m$par_id').style.display='none';";
			}

			while($level < $cur_level)
      {
				$categories .="</div>";
				$cur_level--;
			}

      $sleva_value = "";
      if(isset($sleva[$id])) $sleva_value = $sleva[$id];

			$categories .= '
      <div style="padding:2px;">
        '.$indent.'
        <input style="width:30px;" type="text" name="sale_cat['.$id.']" value="'.$sleva_value.'" />&nbsp;%&nbsp;&nbsp;<a style="cursor:pointer;" onclick="s(\'m'.$id.'\'); return false;">'.$name.'</a>
      </div>
      ';

      $cur_level = $level;
		}
	}

	if(!empty($js))
  {
		$javascript = "
    <script type=\"text/javascript\">
		<!--
	   	".$js."
		-->
		</script>";
	}

  $adddiv = "";
  for($i=0; $i < $cur_level; $i++)
  {
    $adddiv.="</div>";
  }

	return $categories.$adddiv.$javascript;
}


// *****************************************************************************
// editace
// *****************************************************************************
if($_GET['a'] == "edit")
{
  $nadpis = 'Nastavení cenové kategorie zákazníka';
  
  if(!empty($_POST))
  {
    // Sleva na výrobce
    $query = "SELECT * FROM ".T_PRODS." WHERE ".SQL_C_LANG."";
	  $v = my_DB_QUERY($query,__LINE__,__FILE__);
	
    $id_adresa = intval($_GET['id']);

  	while($z = mysql_fetch_assoc($v))
    {
  	  $id_vyrobce=$z['id'];

      $cena_cat = $_POST['cena_cat_vyrobce_'.$id_vyrobce];

      $query2 = 'SELECT * FROM '.T_CENY_X_ADRESY.' where id_adresa='.$id_adresa.' AND id_vyrobce='.$id_vyrobce.'';
      $v2 = my_DB_QUERY($query2,__LINE__,__FILE__);
  	  $pocet=0;
      $pocet=mysql_num_rows($v2);
      if($pocet>0)
        $query3= 'UPDATE '.T_CENY_X_ADRESY.' SET id_cena_cat = '.$cena_cat.' where id_adresa='.$id_adresa.' AND id_vyrobce='.$id_vyrobce.'';
      else
        $query3= 'INSERT INTO '.T_CENY_X_ADRESY.' VALUES( '.$id_adresa.', '.$cena_cat.', '.$id_vyrobce.') ';

      $v3 = my_DB_QUERY($query3,__LINE__,__FILE__);
    }

    // Sleva na kategorie
    $query = "DELETE FROM ".T_SLEVA_KATEGORIE_X_ADRESA." WHERE id_adresa = '".$id_adresa."'";
    my_DB_QUERY($query,__LINE__,__FILE__);

    foreach($_POST["sale_cat"] AS $id_cat => $sale)
    {
      if($sale > 0)
      {
        $query = "
        INSERT INTO ".T_SLEVA_KATEGORIE_X_ADRESA."
        SET
        id_adresa = '".$id_adresa."',
        id_kategorie = '".intval($id_cat)."',
        sleva = '".intval($sale)."'
        ";
        my_DB_QUERY($query,__LINE__,__FILE__);
      }
    }

    $_SESSION["alert_js"] = "Uloženo";
    header("location: ".$_SERVER['HTTP_REFERER']);
	  exit;
  }
  else
  {
  
	$nadpis = 'Nastavení cenové kategorie zákazníka';
	
	// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
	$query = "SELECT * 
	FROM ".T_ADRESY_F." where id = '".intval($_GET['id'])."'";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  $res = "";
	while($z = mysql_fetch_assoc($v))
  {
	  $ID=$z['id'];
		if(empty($z['email'])) $email = '';
		else $email = '<a href="mailto:'.$z['email'].'">'.$z['email'].'</a>';
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td class=\"td1\">".$z['nazev']."</td>
			<td class=\"td1\">
				<span class=\"f10\">
					".$z['psc']." ".$z['mesto']."<br />
					".$z['adresa']."<br />
					IČO: ".$z['ico']."<br />
					DIČ: ".$z['dic']."</span></td>
			<td class=\"td1\">
				<span class=\"f10\">
					login: ".$z['jmeno']."<br />
					e-mail: ".$email."<br />
					tel.: ".$z['telefon']."</span></td>
			";
	}
	
	$query = "SELECT * FROM ".T_PRODS." WHERE ".SQL_C_LANG."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$form_polozky='';
	while ($z = mysql_fetch_assoc($v))
  {
	$id_vyrobce=$z['id'];
	$name_vyrobce=$z['name'];
	
	$query2 = "SELECT * FROM ".T_CENY_X_ADRESY." where id_adresa=".$ID." AND id_vyrobce=".$id_vyrobce."";
  $v2 = my_DB_QUERY($query2,__LINE__,__FILE__);

  $selectCena='';
  while ($z2 = mysql_fetch_assoc($v2))
  {
    $selectCena[$z2['id_cena_cat']]='selected';
  }

  $query3 = 'SELECT * FROM '.T_CENY.' ORDER BY sleva';
	$v3 = my_DB_QUERY($query3,__LINE__,__FILE__);

	$polozkyCena='';
	while ($z3 = mysql_fetch_assoc($v3))
  {
    if(isset($selectCena[$z3['id']])) $polozkyCena.='<option value="'.$z3['id'].'" '.$selectCena[$z3['id']].' >'.$z3['nazev'].' ('.$z3['sleva'].'%)</option>';
    else $polozkyCena.='<option value="'.$z3['id'].'">'.$z3['nazev'].' ('.$z3['sleva'].'%)</option>';
	}

	if($polozkyCena!='')
  {
    $selectCena='
    <select name="cena_cat_vyrobce_'.$id_vyrobce.'">
      <option value="0">Bez cenového zvýhodnění</option>
      '.$polozkyCena.'
    </select>';
  }
	
	$form_polozky.='
	<tr>
  <td width="100">'.$name_vyrobce.'</td>
	<td>
	'.$selectCena.'
  </td>
  </tr>';
  }
  
  // kategorie
  $cat_array = array();
  categories_array($parent_id = 0, $cat_array, $level = 0);
	$kategorie = categories_sale($cat_array , $_GET["id"]);

  $form = '
  <form action="" method="post">
    <h3>Sleva na kategorie</h3>
    '.$kategorie.'
    <h3>Sleva na výrobce</h3>
    <table>
      '.$form_polozky.'
    </table><br />
    <br />
	  '.SAVE_BUTTON.' '.DELETE_BUTTON.'
	</form>
  ';
	
	
	if (!empty($res))
  {
		$data = "
		<SCRIPT LANGUAGE=\"JavaScript\">
		<!--
		function del() {
		
			if (!confirm(\"".$dct['opravdu_odstranit']."\")) {
				return false;
			}
		
		}
		// -->
		</SCRIPT>
		
		".SEARCH_PANEL."
		
		<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
		
    $res
		
		</table>
    
    <br /><br />
		$form
    ";
	
	} else $data = 'Žádný záznam';
	
	
	if(empty($data)) $data = "<br /><br />".$dct['zadny_zaznam'];
	
	}

}
// *****************************************************************************
// editace
// *****************************************************************************




// *****************************************************************************
// seznam
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_customers_list'];
	
	// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
	$query = "SELECT * 
	FROM ".T_ADRESY_F." WHERE ".SQL_C_LANG." ORDER BY id DESC";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);

  $res = "";
	while ($z = mysql_fetch_assoc($v))
  {
		if(empty($z['email'])) $email = '';
		else $email = '<a href="mailto:'.$z['email'].'">'.$z['email'].'</a>';
		
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td class=\"td1\">".$z['nazev']."</td>
			<td class=\"td1\">
				<span class=\"f10\">
					".$z['psc']." ".$z['mesto']."<br />
					".$z['adresa']."<br />
					IČO: ".$z['ico']."<br />
					DIČ: ".$z['dic']."</span></td>
			<td class=\"td1\">
				<span class=\"f10\">
					login: ".$z['jmeno']."<br />
					e-mail: ".$email."<br />
					tel.: ".$z['telefon']."</span></td>
			<td width=\"35\" class=\"td2\">
        ".ico_edit(MAIN_LINK."&f=customers&a=edit&id=".$z['id'],"")."<br />
        <br />
        ".ico_delete(MAIN_LINK."&f=customers&delete=".$z['id'],'Odstranit',"onclick=\"return del()\"")."<br />
      </td>
		</tr>";
	
	}
	
	
	if (!empty($res)) {
	
		$data = "
		<SCRIPT LANGUAGE=\"JavaScript\">
		<!--
		function del() {
		
			if (!confirm(\"".$dct['opravdu_odstranit']."\")) {
				return false;
			}
		
		}
		// -->
		</SCRIPT>
		
		".SEARCH_PANEL."
		
		<table class='admintable' border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
		
		$res
		
		</table>";
	
	} else $data = 'Žádný záznam';
	
	
	if(empty($data)) $data = "<br /><br />".$dct['zadny_zaznam'];

}
// *****************************************************************************
// seznam
// *****************************************************************************










// *****************************************************************************
// odstranit zaznam
// *****************************************************************************
if(!empty($_GET['delete'])) {

	// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
	$query = "DELETE FROM ".T_ADRESY_F." WHERE id = ".$_GET['delete']." LIMIT 1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	my_OPTIMIZE_TABLE(T_ADRESY_F);
	
	
	// id  nazev  adresa  psc  mesto  ico  dic  email  telefon  jmeno  heslo
	$query = "DELETE FROM ".T_ADRESY_P." WHERE id_f = ".$_GET['delete']."";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	my_OPTIMIZE_TABLE(T_ADRESY_P);
	
	
	
	$_SESSION['alert_js'] = "Záznam odstraněn";
	
	Header("Location: ".MAIN_LINK."&f=customers&a=list");
	exit;

}
// *****************************************************************************
// odstranit zaznam
// *****************************************************************************
?>