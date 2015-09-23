<?php


function form($form_data,$dct) {	
	
	if(empty($form_data['txt']))$form_data['txt']='';
	if(empty($form_data['link']))$form_data['link']='';
	if(empty($form_data['poradi']))$form_data['poradi']='';
	if(empty($form_data['hidden']))$form_data['hidden']='';
	if(empty($form_data['id']))$form_data['id']='';
	if(empty($form_data['deletebutton']))$form_data['deletebutton']='';

	if(empty($form_data['vlozeno']))$form_data['vlozeno']=time();
	
	$editor="
      <textarea name='txt'>".$form_data['txt']."</textarea>
          
      <script type='text/javascript'>
                //<![CDATA[
                CKEDITOR.replace('txt', {
                	height: '400px'
                });
                //]]>
      </script>
  ";

	
	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function del() {
	
		if (confirm(\"".$dct['opravdu_odstranit']."\"))
			{ location = \"".$form_data['link']."&delete=".$form_data['id']."\"; }
	
	}
	// -->
	</SCRIPT>
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\"><!-- onSubmit=\"return validate(this)\"-->
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	<input type=\"hidden\" name=\"vlozeno\" value=\"".$form_data['vlozeno']."\">
	
	<!--<input type=\"hidden\" name=\"lang\" value=\"".C_LANG."\">-->
	
	<table width=\"650\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	<tr>
		<td colspan=\"2\">$editor</td>
	</tr>
	
	<tr>
		<td colspan=\"2\">
		
			".$dct['news_poradi'].": 
			<input type=\"text\" name=\"poradi\" value=\"".$form_data['poradi']."\" 
			size=\"5\" class=\"f10\">
			
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			<input type=\"checkbox\" name=\"hidden\" value=\"1\" 
			".$form_data['hidden']."> ".$dct['news_skryta']."
			
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			".$dct['news_vlozeno'].": ".timestamp_to_date($form_data['vlozeno'])."
			
		</td>
	</tr>
	
	<tr>
		<td colspan=\"2\"><br><br>
			
			".SAVE_BUTTON."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	</form>";
	
	return $form;

}









// *****************************************************************************
// datumy
// *****************************************************************************
function date_to_timestamp($datum) {

	// pokud neni datum uveden, dosadi se, jinak prevede datum z formatu 
	// DD.MM. RRRR na time(), mezery mezi DD, MM, RRRR mohou byt a nemusi
	
	if(empty($datum)) $datum = date("d.m.Y");
	
	// vyhazeme vsechny mezery z datumu
	$trans = array (" " => "");
	$datum = strtr($datum, $trans);
	
	
	//list($datum['d'],$datum['m'],$datum['r']) = explode (".", $datum);
	$datum = explode(".", $datum);
	
	
	if(empty($datum[0]) || empty($datum[1]) || empty($datum[2])) {
	
		$_SESSION['alert'] = "Chybný formát datumu";
		
		Header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	
	}
	
	
	return $datum;

}
// *****************************************************************************
// datumy
// *****************************************************************************








// *****************************************************************************
// pozice, souvisla rada
// *****************************************************************************
// souvisla rada
function souvisla_rada() {

	$i = 1;
	
	// id  vlozeno  txt  hidden  poradi  lang
	$query = "SELECT id FROM ".T_NEWS." 
	ORDER BY poradi";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		// id  vlozeno  txt  hidden  poradi  lang
		$query = "UPDATE ".T_NEWS." SET poradi = $i 
		WHERE id = " . $z['id'];
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$i++;
	
	}

}




// posun pozic nahoru
function move_up($poradi) {

	// id  vlozeno  txt  hidden  poradi  lang
	$query = "UPDATE ".T_NEWS." SET 
	poradi = poradi + 1 WHERE (poradi = $poradi OR poradi > $poradi)";
	my_DB_QUERY($query,__LINE__,__FILE__);

}


// posun pozic dolu
function move_down($poradi) {

	// id  vlozeno  txt  hidden  poradi  lang
	$query = "UPDATE ".T_NEWS." SET 
	poradi = poradi - 1 WHERE poradi <= $poradi";
	my_DB_QUERY($query,__LINE__,__FILE__);

}
// *****************************************************************************
// pozice, souvisla rada
// *****************************************************************************








// *****************************************************************************
// ulozeni zaznamu (novy i upraveny)
// *****************************************************************************
if (!empty($_POST)) { // echo $_POST['id']."<br />";

	$txt = trim($_POST['txt']);
	

	
	// nastaveni skryti
	$hidden = $_POST['hidden'];
	if ($hidden != 1) $hidden = 0;
	
	
	// osetrime hodnoty poradi
	if (empty($_POST['poradi']) || $_POST['poradi'] < 1) $poradi = 100000;
	else $poradi = $_POST['poradi'];
	
	
	
	if (!empty($_POST['id'])) { // editace existujiciho
	
		$query = "SELECT poradi FROM ".T_NEWS." 
		WHERE id = ".$_POST['id']." LIMIT 0,1";
		$v = my_DB_QUERY($query,__LINE__,__FILE__);
		$pos = mysql_result($v, 0, 0);
		
		
		// presuneme potrebne zaznamy
		if ($poradi <= $pos) move_up($poradi);
		if ($poradi > $pos) move_down($poradi);
		
		// id  vlozeno  txt  hidden  poradi  lang
		$query = "UPDATE ".T_NEWS." SET 
		vlozeno = ".$_POST['vlozeno'].", 
		txt = '$txt', 
		hidden = $hidden, 
		poradi = $poradi, 
		lang = '".C_LANG."'
		WHERE id = ".$_POST['id'];
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$back = $_SERVER['HTTP_REFERER'];
	
	}
	else { // novy zaznam
	
		move_up($poradi);
		
		// id  vlozeno  txt  hidden  poradi  lang
		$query = "INSERT INTO ".T_NEWS." 
		VALUES('',".$_POST['vlozeno'].",'$txt',$hidden,$poradi,'".C_LANG."')";
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$back = "".MAIN_LINK."&f=news&a=list";
	
	}
	
	souvisla_rada();
	
	
	
	
	$_SESSION['alert_js'] = "Záznam uložen";
	
	Header("Location: $back");
	exit;

}
// *****************************************************************************
// ulozeni zanamu (novy i upraveny)
// *****************************************************************************









// *****************************************************************************
// seznam
// *****************************************************************************
if($_GET['a'] == "list") {

	$nadpis = $dct['mn_news_seznam'];
	
	// T_NEWS - id  vlozeno  txt  hidden  poradi
	$query = "SELECT * FROM ".T_NEWS." 
	WHERE ".SQL_C_LANG." ORDER BY poradi";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$res='';
	
	while ($z = mysql_fetch_array($v)) {
	
		if($z['hidden'] == 1) $hidden_style = "style=\"color: #939393;\"";//class=\"gray\"
		else $hidden_style = "";
		
		$poradi = $z['poradi'];
		
		
		// zkratime text
		$txt = $z['txt'];
		$maxtxt = 60;
		if (strlen($txt) > $maxtxt) {
		 
			$txt = substr($txt,0,$maxtxt); // orizeneme na max pocet
			$pos = strrpos($txt," "); // najdeme posledni mezeru ve zbytku textu
			$txt = substr($txt,0,$pos)." ..."; // odrizneme k posledni mezere
		
		}
		
		
		
		
		$res .= "
		<tr ".TABLE_ROW.">
			<td width=\"15\" class=\"td1\" $hidden_style nowrap>".$poradi."</td>
			<td class=\"td1\" $hidden_style nowrap>".timestamp_to_date($z['vlozeno'])."</td>
			<td class=\"td1\" $hidden_style nowrap>".$txt."</td>
			
			<td width=\"15\" class=\"td2\">
				".ico_edit(MAIN_LINK."&f=news&a=edit&id=".$z['id']."",$dct['news_edit'])."</td>
		</tr>";
	
	}
	
	
	if(!empty($seznam)) $data = SEARCH_PANEL.$seznam;//."$query<br /><br />"
	else $data = "<br /><br />".$dct['zadny_zaznam'];
	
	
	
	
	
	
	
	if (!empty($res)) {
	
		$data = "
		".SEARCH_PANEL."
		
		<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
		$res
		</table>";
	
	}
	
	
	
	if(empty($data)) $data = "<br /><br />".$dct['zadny_zaznam'];

}
// *****************************************************************************
// seznam
// *****************************************************************************









// *****************************************************************************
// editace (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = $dct['news_edit'];
	
	
	// T_NEWS - id  vlozeno  txt  hidden  poradi
	$query = "SELECT * FROM ".T_NEWS." 
	WHERE id = ".$_GET['id']." AND ".SQL_C_LANG." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {
	
		$form_data['vlozeno'] = $z['vlozeno'];
		$form_data['txt'] = $z['txt'];
		$form_data['poradi'] = $z['poradi'];
		$form_data['hidden'] = $z['hidden'];
		
		if($form_data['hidden'] == 1) $form_data['hidden'] = "checked";
		else $form_data['hidden'] = "";
	
	}
	
	
	if(!empty($form_data)) {
	
		$form_data['id'] = $_GET['id'];
		$form_data['link'] = MAIN_LINK."&f=news";
		$form_data['deletebutton'] = DELETE_BUTTON;
		
		$data = form($form_data,$dct);
	
	}
	else $data = $dct['zaznam_nenalezen'];

}
// *****************************************************************************
// editace (form)
// *****************************************************************************










// *****************************************************************************
// pridani (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = $dct['mn_news_add'];
  
  $form_data=null;
  	
	$data = form($form_data,$dct);

}
// *****************************************************************************
// pridani (form)
// *****************************************************************************









// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
if(!empty($_GET['delete'])) {

	// odstraneni vsech obrazku a produktu z kategorie probehlo ok, 
	// odstranime samotnou kategorii 
	$query = "DELETE FROM ".T_NEWS." 
	WHERE id = ".$_GET['delete']." AND ".SQL_C_LANG." LIMIT 1";
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_NEWS);
	
	Header("Location: ".MAIN_LINK."&f=news&a=list");
	exit;
	

}
// *****************************************************************************
// odstranit kategorii
// *****************************************************************************
?>
