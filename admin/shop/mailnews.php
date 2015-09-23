<?php
// *****************************************************************************
// formular pro editaci
// *****************************************************************************


/*
@param (int) id - id newsletteru

@return (string) url newsletteru
*/
function get_newsletter_url($id)
{
  if(isset($id) AND !empty($id))
  {
    $newsletter_url = 'http://'.$_SERVER["SERVER_NAME"].'/newsletter-content/'.$id.'.html';
  }
  else
  {
    $newsletter_url = NULL;
  }

  return $newsletter_url;
}


function form($form_data,$dct) {
	
	if(empty($form_data['text']))$form_data['text']='';
	if(empty($form_data['subject']))$form_data['subject']='';
	if(empty($form_data['title']))$form_data['title']='';
	if(empty($form_data['datetime']))$form_data['datetime']='';
	
	if(empty($form_data['l1']))$form_data['l1']='';            //registroval se
	if(empty($form_data['l2']))$form_data['l2']='';              //nakoupil
	if(empty($form_data['l3']))$form_data['l3']='';              //odeslal dotaz
	if(empty($form_data['l4']))$form_data['l4']='';              //naimportovan v administraci
	
	if(empty($form_data['deletebutton']))$form_data['deletebutton']='';


  if(isset($form_data['id']) AND !empty($form_data['id']))
  {
    $newsletter_url = get_newsletter_url($form_data['id']);

    $newsletter_odkaz = '
	  <tr>
  		<td>
      </td>
      <td>
        <br>
        <a target="_blank" href="'.$newsletter_url.'">Odkaz na newsletter</a><br>
      </td>
	  </tr>
    ';
  }
  else
  {
    $form_data['id'] = NULL;
    $newsletter_odkaz = NULL;
  }

	
	$editor="
      <textarea name='text'>".$form_data['text']."</textarea>
          
      <script type='text/javascript'>
                //<![CDATA[
                CKEDITOR.replace('text', {
                	height: '600px'
                });
                //]]>
      </script>
  	";

  include_once($_SERVER["DOCUMENT_ROOT"]."/admin/shop/mail_adresare.php");
  $adresare = adresare_input($form_data['id']);	

	$form = "
	
	<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	function validate(form1) {
	
		if (form1.title.value == \"\") { alert(\"Vyplňte název stránky\"); form1.title.focus(); return false; }
		else if (form1.title.value.length > 255) {
			alert(\"Název newsletteru je dlouhý (\" + form1.title.value.length + \") - upravte jej na max. 255 znaky.\"); form1.title.focus(); return false;
		}
		else return true;
	
	}
	
	
	// odstraneni zaznamu
	function del() {
	
		if (confirm(\"Opravdu odstranit?\"))
			{ location = \"".$form_data['link']."&delete=".$form_data['id']."\"; }
	
	}
	// -->
	</SCRIPT>
	
	
	<br /><br />
	
	
	<form action=\"\" method=\"post\" enctype=\"multipart/form-data\" onSubmit=\"return validate(this)\">
	
	<input type=\"hidden\" name=\"id\" value=\"".$form_data['id']."\">
	
	
	<table width=\"650\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
	
	
	<tr>
		<td width=\"160\">
			Název <span class=\"f10i\">(max. 255 znaků)</span><br /><br /></td>
		<td width=\"340\">
			<input type=\"text\" name=\"title\" value=\"".$form_data['title']."\"	style=\"width: 100%;\" class=\"f10\"><br />
			Pouze pro potřebu administrace
			</td>
	</tr>

	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	
	<tr>
		<td width=\"160\">
			Předmět zprávy <span class=\"f10i\">(max. 255 znaků)</span><br /><br /></td>
		<td width=\"340\">
			<input type=\"text\" name=\"subject\" value=\"".$form_data['subject']."\"	style=\"width: 100%;\" class=\"f10\"><br />
			</td>
	</tr>	
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>	

	<tr>
		<td>Zaslat na e-maily</td>
		<td width=\"330\">
			<input type='checkbox' name='l1' value=\"1\" ".$form_data['l1']." />Z registrací<br />
			<input type='checkbox' name='l2' value=\"1\" ".$form_data['l2']." />Z objednávek<br />
			<input type='checkbox' name='l3' value=\"1\" ".$form_data['l3']." />Z dotazů<br />
			<input type='checkbox' name='l4' value=\"1\" ".$form_data['l4']." />Ze všech naimportovaných adres<br />
      ".$adresare." 
		</td>
	</tr>

  ".$newsletter_odkaz."
	
	<tr>
		<td colspan=\"2\">
			<br /><br />Obsah e-mailu:<br />
			$editor
		</td>
	</tr>
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>	
	
	<tr>
		<td>Zaslat zkušební zprávu na:<br />(více e-mailů oddělte mezerou)</td>
		<td><input type=\"text\" name=\"testing\" style=\"width: 100%;\" class=\"f10\" /><br />
		".button("submit","Zaslat zkušební zprávu","class=\"butt_green\" name=\"testbutt\" style=\"margin-top: 5px;\"")."</td>
	</tr>	
	
	<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>	
	
	<tr>
		<td colspan=\"2\"><br /><br /><br />
			
			".button("submit",$dct['button_ulozit_zaznam'],"class=\"butt_green\" name=\"save\"")."
			
			".button("submit","Uložit a odeslat zprávu","class=\"butt_green\" name=\"submit\"")."
			
			".$form_data['deletebutton']."
		
		</td>
	</tr>
	
	</table>
	
	</form>
  
";
	
	return $form;//

}
// *****************************************************************************
// formular pro editaci
// *****************************************************************************









// *****************************************************************************
// odstraneni zaznamu
// *****************************************************************************
if (!empty($_GET['delete'])) {

	// id  title  content  in_menu  menu_pos 
	$query = "DELETE FROM ".T_INFO_NEWS." WHERE id = " . $_GET['delete'];
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_INFO_NEWS);
	
	
	
	$query = "DELETE FROM ".T_INFO_NEWS_X_EMAIL." WHERE id_message = " . $_GET['delete'];
	my_DB_QUERY($query,__LINE__,__FILE__);
	
	my_OPTIMIZE_TABLE(T_INFO_NEWS_X_EMAIL);
	
	
	
	
	Header("Location: ".MAIN_LINK."&f=mailnews&a=list");
	exit;

}
// *****************************************************************************
// odstraneni zaznamu
// *****************************************************************************








// *****************************************************************************
// editace / pridani zaznamu
// *****************************************************************************
if (!empty($_POST['save']) || !empty($_POST['submit']) || (!empty($_POST['testbutt']) && !empty($_POST['testing']))) {

	$l1 = trim($_POST['l1']);
	$l2 = trim($_POST['l2']);
	$l3 = trim($_POST['l3']);
	$l4 = trim($_POST['l4']);
	  
	if(empty($l1))$l1=0;
	if(empty($l2))$l2=0;
	if(empty($l3))$l3=0;
	if(empty($l4))$l4=0;
	

	$subject = trim($_POST['subject']);
	$title = trim($_POST['title']);
	$text = trim($_POST['text']);
	
	
	
	// id  title  content  in_menu  menu_pos  homepage  hidden
	if (!empty($_POST['id'])) { // editace existujiciho
     	
     	$text=addslashes($text);

		$query = "UPDATE ".T_INFO_NEWS." SET 
		title='$title',	 	 	 	 	 	 	 
		subject='$subject',	 	 	 	 	 	 	 
		text='$text',	 	 	 				 	 	 	 	 	
		l1='$l1',	 	 	 	 	 	 	
		l2='$l2',	 	 	 	 	 	 	
		l3='$l3',
		l4='$l4'		 	 	 	 	 	 			
		WHERE id = ".$_POST['id'];
		
		my_DB_QUERY($query,__LINE__,__FILE__);
	
		$id_msg=$_POST['id'];
	
	}else{ // novy zaznam
	

// 	id	 	 	 	 	
// 	title	 	 	 	 	 	 	 
// 	subject	 	 	 	 	 	 	 
// 	text	 	 	 				 
// 	datetime	 	 	 	 	
// 	l1		 	 	 	 	 	 	
// 	l2		 	 	 	 	 	 	
// 	l3		 	 	 	 	 	 	
// 	lang		 	 	 	 	 	 	
		
		$text=addslashes($text);

		$query = "INSERT INTO ".T_INFO_NEWS." VALUES(NULL,'$title','$subject','$text',".time().",$l1,$l2,$l3,$l4,".C_LANG.")";
		
		my_DB_QUERY($query,__LINE__,__FILE__);
		
		$id_msg=mysql_insert_id();
		
	}


  // oznacime vybrane adresare
  include_once($_SERVER["DOCUMENT_ROOT"]."/admin/shop/mail_adresare.php");  
  oznac_adresare($id_msg , $_POST["adresare"]);	
	
	
	$text=stripslashes($text);   
	$text=str_replace('src="/','src="http://'.$_SERVER['HTTP_HOST'].'/',$text);
	
	
	if (!empty($_POST['submit'])) 
  { // odeslani zpravy
  
    foreach($_POST["adresare"] as $id_adresare => $odeslat)
    {
      if($odeslat == 1)
      { // adresar byl oznacen
        // emaily v adresari
        $query = "
        SELECT 
        ".T_INFO_IMPORTED.".email as email,
        ".T_INFO_IMPORTED.".id as id
        FROM 
        ".T_INFO_IMPORTED.",
        ".T_MAIL_ADRESAR_X_EMAIL."
        WHERE ".T_INFO_IMPORTED.".id = ".T_MAIL_ADRESAR_X_EMAIL.".id_email
        AND ".T_MAIL_ADRESAR_X_EMAIL.".id_adresar = '".$id_adresare."' 
        ";
        $v = my_DB_QUERY($query,__LINE__,__FILE__); 
  
        while($z = mysql_fetch_array($v))
        {
          send_news($id_msg,$z['email'],$title,$subject,$text);
        }       
      }    
    }

    

      
     	if(!empty($l1)){
			//z registraci vybrat maily a predat je funkci send_news();
			
			$query='select email from '.T_ADRESY_F.' where '.SQL_C_LANG;
			$v=my_DB_QUERY($query,__LINE__,__FILE__);
			
			while($row=mysql_fetch_array($v)){
// 				echo $row['email']."<br />";		
				send_news($id_msg,$row['email'],$title,$subject,$text);	
			}			                                                                                                  
			
		}
		
     	if(!empty($l2)){
			//z registraci vybrat maily a predat je funkci send_news();
			
			$query='select f_mail as email from '.T_ORDERS_ADDRESS.' where '.SQL_C_LANG.' GROUP BY email';
			$v=my_DB_QUERY($query,__LINE__,__FILE__);
			
			while($row=mysql_fetch_array($v)){
// 				echo $row['email']."<br />";
				send_news($id_msg,$row['email'],$title,$subject,$text);			
			}			
			
		}
		
     	if(!empty($l3)){
			//z registraci vybrat maily a predat je funkci send_news();
			
			$query='select email from '.T_DOTAZY.' GROUP BY email';
			$v=my_DB_QUERY($query,__LINE__,__FILE__);
			
			while($row=mysql_fetch_array($v)){
// 				echo $row['email']."<br />";
				send_news($id_msg,$row['email'],$title,$subject,$text);			
			}			
			
		}
		
     	if(!empty($l4)){
			//z registraci vybrat maily a predat je funkci send_news();
			
			$query='select email from '.T_INFO_IMPORTED.' where '.SQL_C_LANG;
			$v=my_DB_QUERY($query,__LINE__,__FILE__);
			
			while($row=mysql_fetch_array($v)){
// 				echo $row['email']."<br />";	
				send_news($id_msg,$row['email'],$title,$subject,$text);		
			}			
			
		}						
     	
	}elseif(!empty($_POST['testbutt'])){
	     
		if(empty($_POST['testing'])){
		
			$_SESSION['alert_js'] = "Zadejte zkušební e-mail!";
	
			$back = $_SERVER['HTTP_REFERER'];
			Header("Location: ".$back);
			exit;
		
		}else{
			
			$array=explode(' ',$_POST['testing']);
				
			foreach($array as $value){
				
// 				echo $value;exit;
				
				send_news($id_msg,$value,$title,$subject,$text,1);	
							
			}				
			     
		}		
	}
	
	
	
	
	
// 	exit;
	
	
	$_SESSION['alert_js'] = "Záznam uložen";
	
	$back = $_SERVER['HTTP_REFERER'];
	Header("Location: ".$back);
	exit;

}
// *****************************************************************************
// editace / pridani zaznamu
// *****************************************************************************






// *****************************************************************************
// fce pro generovani tabulek se seznamem stranek
// *****************************************************************************
function table($query) {

  // id  title  content  in_menu  menu_pos  homepage
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	$hidden_style=$tbl=$menu_pos=$title=$in_menu_new='';
	
	while ($z = mysql_fetch_array($v)) {
	
		$id = $z['id'];
		$title = $z['title'];
		
		$link = "index.php?p=$id";
		
		$datetime = $z['datetime'];
			
		$tbl .= "
		<tr ".TABLE_ROW.">
	    	<td class=\"td1\" $hidden_style>$title</td>
	    	<td width=\"150\" class=\"td1\">".date("d.m.Y H:i:s",$datetime)."</td>
	    	<td width=\"15\" class=\"td1\">
				".ico_edit(MAIN_LINK."&f=mailnews&a=edit&id=$id",'Upravit zprávu')."</td>
    		</tr>";
		//".ico_edit("prew&id=$id",$dct['cont_page_edit'])."
		
		
		$in_menu_old = $in_menu_new;
	
	}
	
	
	return $tbl;

}
// *****************************************************************************
// fce pro generovani tabulek se seznamem stranek
// *****************************************************************************







// *****************************************************************************
// seznam stranek
// *****************************************************************************
if ($_GET['a'] == "list") {

	$nadpis = "Seznam zpráv";
	
	$tbl='';

	
	// stranky uvedene v menu 2 - lista v prave casti stranky
	$tbl = table("SELECT id,title,datetime FROM ".T_INFO_NEWS." 
	WHERE ".SQL_C_LANG." ORDER BY datetime desc");
	

	
	if(!empty($tbl)) {
			$data = "
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
			
			<tr>
				<td class=\"td1\"><b>název zprávy</b></td>
				<td width=\"150\" class=\"td1\"><b>datum vytvoření</b></td>
				<td width=\"15\" class=\"td2\">&nbsp;</td>
			</tr>
			
			</table>
			<br />
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#c6c6c6\">
			
			$tbl
			
			</table>";
	
	}else{
			$data = "
			<table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\">
			
			<tr>
				<td colspan=\"3\">žádný záznam</td>
			</tr>
			
			</table>";
			
	}

}
// *****************************************************************************
// seznam stranek
// *****************************************************************************





// *****************************************************************************
// editace (form)
// *****************************************************************************
if($_GET['a'] == "edit") {

	$nadpis = "Editace zprávy";
	$form_data['link'] = MAIN_LINK."&f=mailnews";
	$form_data['deletebutton'] = DELETE_BUTTON;
	
	// id  title  content  in_menu  menu_pos 
	$query = "SELECT * FROM ".T_INFO_NEWS." 
	WHERE id = ".$_GET['id']." LIMIT 0,1";
	$v = my_DB_QUERY($query,__LINE__,__FILE__);
	
	while ($z = mysql_fetch_array($v)) {

		$form_data['text']=stripslashes($z['text']);
		$form_data['subject']=$z['subject'];
		$form_data['title']=$z['title'];
		$form_data['datetime']=$z['datetime'];
		
		if(empty($z['l1']))$form_data['l1']='';
		else $form_data['l1']=' checked="checked"';           
		if(empty($z['l2']))$form_data['l2']='';
		else $form_data['l2']=' checked="checked"';  
		if(empty($z['l3']))$form_data['l3']='';
		else $form_data['l3']=' checked="checked"';
		if(empty($z['l4']))$form_data['l4']='';
		else $form_data['l4']=' checked="checked"'; 
		
		$form_data['id']=$z['id'];   
    
	}
	$data = form($form_data,$dct);
}
// *****************************************************************************
// editace (form)
// *****************************************************************************










// *****************************************************************************
// pridani (form)
// *****************************************************************************
if($_GET['a'] == "add") {

	$nadpis = "Nová zpráva";
	
	
	$data = form($form_data=array(),$dct);
	
	unset($_SESSION['last_parent']);

}
// *****************************************************************************
// pridani (form)
// *****************************************************************************







// *****************************************************************************
// import emailu
// *****************************************************************************
if($_GET['a'] == "import") {
	
	
	$nadpis='Importovat e-maily';

	$data='
		<form method="post" style="width: 600px;">
		<textarea style="width: 100%; height: 400px;" name="emaily"></textarea>
		
		<br /><br /><br /><br />
		
		
		'.SAVE_BUTTON.'
		</form>';	
		
	if(!empty($_POST)){
  		$celkem=$duplikat=$novy=0;
  		preg_match_all('/([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})/',strtolower($_POST['emaily']), $matches);
// 		print_r($matches[0]);
		if(is_array($matches[0])){
			foreach($matches[0] as $mail){
				$query="select email from ".T_INFO_IMPORTED." where email='".$mail."' and ".SQL_C_LANG;
// 				echo $query;
				$v=my_DB_QUERY($query,__LINE__,__FILE__);
				if(mysql_num_rows($v)>0){
					$duplikat++;
				}else{
					$skupina=$_POST['skupina'];
				     $query="insert into ".T_INFO_IMPORTED." values('$mail',".C_LANG.")";				     
				     my_DB_QUERY($query,__LINE__,__FILE__);
				     $novy++;
				}		
				$celkem++;			
			}
		}
		$query="select count(*) as celkem from ".T_INFO_IMPORTED." where ".SQL_C_LANG;
		$v=my_DB_QUERY($query,__LINE__,__FILE__);
		$celkem_count=mysql_fetch_array($v);
		$celkem_count="<div style='float: left;'>
		Celkem e-mailů v databázi: ".$celkem_count['celkem']."<br />";
		
		$data="<p>".$celkem_count."
		celkem adres: $celkem<br />
		nových adres: $novy<br />
		duplikáítních adres: $duplikat<br /></p>			
		".$data;
	}
}
// *****************************************************************************
// // import emailu
// *****************************************************************************












function send_news($id_msg,$email,$title,$subject,$text,$test=0){

	$odeslano=null;

	//zjistime, zda na dany e-mail, pokud ne, zasleme mail, pokud ano, preskakujeme
	
	if($test==0){
	     
		$query="select * from ".T_INFO_NEWS_X_EMAIL." where email='$email' and id_message=$id_msg";
		$v=my_DB_QUERY($query,__LINE__,__FILE__);
		
		$query="select * from ".T_INFO_BLACKLISTED." where email='$email'";
		$v2=my_DB_QUERY($query,__LINE__,__FILE__);
	
	}
	
// 	echo "nevim jestli posilam<br />";
	
	if((mysql_num_rows($v)==0 && mysql_num_rows($v2)==0) || $test==1){
		echo $email;		
		$subject=diakritika($subject);
		
		$headers = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=utf-8\n";
		$headers .= "From: ".S_WEB." <".S_MAIL_SHOP.">\n";
		
    $newsletter_url = get_newsletter_url($id_msg);

    $text = 'Pokud se Vám zpráva nezobrazí klikněte <a href="'.$newsletter_url.'">zde</a>.
    <br /><br />
    ' . $text;

		$text.='
		<br /><br />Pro odhlášení použijte následující odkaz: <br /><a href="http://'.$_SERVER['HTTP_HOST'].'/newsletter/?id='.$id_msg.'&control='.md5($email.$id_msg).'">http://'.$_SERVER['HTTP_HOST'].'/newsletter/?id='.$id_msg.'&control='.md5($email.$id_msg).'</a> 
		';

		$odeslano=mail($email,$subject,$text,$headers);
		
		if($odeslano && $test==0){
			$query="insert into ".T_INFO_NEWS_X_EMAIL."(id_message,email,control) values($id_msg,'$email','".md5($email.$id_msg)."')";
			$v=my_DB_QUERY($query,__LINE__,__FILE__);	
		}
	  	
// 		echo "posilam $email<br />";	
	}
	
	return $odeslano;		
	                   
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
?>